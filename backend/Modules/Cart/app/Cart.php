<?php

namespace Modules\Cart;

use Darryldecode\Cart\Cart as DarryldecodeCart;
use Darryldecode\Cart\CartConditionCollection;
use Darryldecode\Cart\Exceptions\InvalidConditionException;
use Darryldecode\Cart\Exceptions\InvalidItemException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonException;
use JsonSerializable;
use Modules\Branch\Models\Branch;
use Modules\Cart\Traits\CartInitOrder;
use Modules\Discount\Models\Discount;
use Modules\Loyalty\Models\LoyaltyGift;
use Modules\Order\Enums\OrderProductStatus;
use Modules\Order\Enums\OrderType;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderProduct;
use Modules\Product\Models\Product;
use Modules\Product\Services\ChosenProductOptions;
use Modules\Support\Money;
use Modules\Tax\Enums\TaxType;
use Modules\Tax\Models\Tax;
use Modules\User\Models\User;
use Modules\Voucher\Models\Voucher;


class Cart extends DarryldecodeCart implements JsonSerializable, Arrayable
{
    use CartInitOrder;

    /**
     * Cart Branch
     *
     * @var CartBranch|null
     */
    protected ?CartBranch $branch = null;
    protected array $computedCache = [];

    /**
     * Get the current cart instance.
     *
     * @return static
     */
    public function instance(): static
    {
        return $this;
    }

    /**
     * Reset memoized cart state so the next read hydrates from storage again.
     */
    public function refresh(): static
    {
        $this->resetComputedState();

        return $this;
    }

    protected function resetComputedState(): void
    {
        $this->computedCache = [];
        $this->branch = null;
    }

    /**
     * Clear the cart and all attached conditions (e.g., taxes, discounts).
     *
     * @return void
     */
    public function clear(): void
    {
        parent::clear();
        $this->clearCartConditions();
        $this->resetComputedState();
    }

    /**
     * Add or store a product in the cart.
     *
     * @param int $productId
     * @param int $qty
     * @param array $options
     * @param OrderProduct|null $orderProduct
     * @param LoyaltyGift|null $gift
     * @return void
     *
     * @throws InvalidItemException
     */
    public function store(
        int           $productId,
        int           $qty,
        array         $options = [],
        ?OrderProduct $orderProduct = null,
        ?LoyaltyGift  $gift = null
    ): void
    {
        $this->resetComputedState();
        $options = array_filter($options);

        /** @var Product $product */
        $product = Product::with(['files', 'categories', 'taxes', 'menu'])->findOrFail($productId);

        $existingItems = $this->getContent();

        if ($existingItems->isNotEmpty()) {
            $existingBranchIds = $existingItems
                ->pluck('attributes.branch_id')
                ->unique()
                ->filter();

            abort_if(
                $existingBranchIds->count() > 1 || ($existingBranchIds->first() !== $product->menu->branch_id),
                400,
                __("cart::messages.mixed_branch_not_allowed")
            );

            if (!is_null($gift)) {
                $giftAlreadyAdded = $existingItems
                    ->pluck('attributes.loyalty_gift.id')
                    ->unique()
                    ->filter();

                abort_if(
                    $giftAlreadyAdded->count(),
                    400,
                    __("cart::messages.gift_already_added")
                );
            }
        }

        $attributes = [
            'product' => $product,
            'branch_id' => $product->branch->id,
            'item' => $product,
            'options' => (new ChosenProductOptions($product, $options))->getEntities(),
            'created_at' => now()->valueOf(),
        ];

        $sellingPrice = $product->selling_price->amount();
        $uniqueId = "product_id.$productId";

        if (!is_null($orderProduct)) {
            if ($orderProduct->status != OrderProductStatus::Pending) {
                $uniqueId .= "status:{$orderProduct->status->value}:order_product_id:$orderProduct->id";
            }
        }

        if (!is_null($gift)) {
            $uniqueId .= "loyalty_gift_id:$gift->id";
            $attributes['loyalty_gift'] = [
                'id' => $gift->id,
            ];
            $sellingPrice = 0;
        }

        $uniqueId = md5("$uniqueId:options." . serialize($options));

        if (!is_null($orderProduct)) {
            $attributes['order_product'] = [
                "id" => $orderProduct->id,
                "status" => $orderProduct->status
            ];
        }

        $this->add([
            'id' => $uniqueId,
            'name' => $product->name,
            'price' => $sellingPrice,
            'quantity' => $qty,
            'attributes' => $attributes,
        ]);
        $this->resetComputedState();
    }

    /**
     * Get total count of items (unique cart lines, not quantity sum).
     *
     * @return int
     */
    public function count(): int
    {
        return $this->items()->count();
    }

    /**
     * Retrieve all cart items sorted by creation time (latest first).
     *
     * @return Collection<CartItem>
     */
    public function items(): Collection
    {
        return $this->rememberComputed('items', fn() => $this->getContent()
            ->sortByDesc(fn($item) => $item->get('attributes')['created_at'])
            ->map(fn($item) => new CartItem($this, $item))
            ->values());
    }

    protected function rememberComputed(string $key, callable $resolver): mixed
    {
        if (!array_key_exists($key, $this->computedCache)) {
            $this->computedCache[$key] = $resolver();
        }

        return $this->computedCache[$key];
    }

    /**
     * Update the quantity of a specific cart item.
     *
     * @param string $id
     * @param int $qty
     * @return void
     */
    public function updateQuantity(string $id, int $qty): void
    {
        $this->resetComputedState();
        $this->update($id, [
            'quantity' => [
                'relative' => false,
                'value' => $qty,
            ],
        ]);
    }

    public function update($id, $data)
    {
        $this->resetComputedState();

        return parent::update($id, $data);
    }

    public function remove($id)
    {
        $this->resetComputedState();

        return parent::remove($id);
    }

    /**
     * Convert the cart to JSON string.
     *
     * @return string
     * @throws JsonException
     */
    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items(),
            'quantity' => $this->getTotalQuantity(),
            'subTotal' => $this->subTotal(),
            'orderType' => $this->orderType(),
            'taxes' => $this->taxes(),
            'discount' => $this->discount(),
            'customer' => $this->customer(),
            'order' => $this->order(),
            'total' => $this->total(),
        ];
    }

    /**
     * Get the subtotal including option prices.
     *
     * @return Money
     */
    public function subTotal(): Money
    {
        return $this->rememberComputed('sub_total', fn() => (new Money($this->getSubTotal(), $this->branch()->currency()))
            ->add($this->optionsPrice())
            ->add($this->productsTaxesPrice()));
    }

    /**
     * Get branch for this cart
     *
     * @return CartBranch|null
     */
    public function branch(): ?CartBranch
    {
        return $this->rememberComputed('branch', function () {
            if (isset($this->branch)) {
                return $this->branch;
            }

            return $this->branch = new CartBranch($this->getConditionsByType('branch')->first());
        });
    }

    /**
     * Calculate the total price of all option values.
     *
     * @return Money
     */
    private function optionsPrice(): Money
    {
        return $this->rememberComputed('options_price', fn() => new Money($this->calculateOptionsPrice(), $this->branch()->currency()));
    }

    /**
     * Compute the numeric total of all options.
     *
     * @return float
     */
    private function calculateOptionsPrice(): float
    {
        return $this->items()->sum(fn(CartItem $item) => $item->optionsPrice()->multiply($item->qty)->amount());
    }

    /**
     * Calculate the total price of all product's taxes price.
     *
     * @return Money
     */
    private function productsTaxesPrice(): Money
    {
        return $this->rememberComputed('products_taxes_price', fn() => new Money($this->calculateProductsTaxesPrice(), $this->branch()->currency()));
    }

    /**
     * Compute the numeric total of all products taxes.
     *
     * @return float
     */
    private function calculateProductsTaxesPrice(): float
    {
        return $this->items()->sum(fn(CartItem $item) => $item->taxTotal()->amount());
    }

    public function orderType(): CartOrderType
    {
        return $this->rememberComputed('order_type', fn() => new CartOrderType($this->getConditionsByType('order_type')->first()));
    }

    /**
     * Retrieve all applied tax details as CartTax objects.
     *
     * @return Collection<CartTax>
     */
    public function taxes(): Collection
    {
        return $this->rememberComputed('taxes', function () {
            if (!$this->hasTax()) {
                return collect();
            }

            $taxConditions = $this->getConditionsByType('tax');

            $branch = $this->branch();

            /** @var Tax $taxes */
            $taxes = Tax::query()
                ->whereIn('id', $this->getTaxIds($taxConditions))
                ->withOutGlobalBranchPermission()
                ->where(function ($q) use ($branch) {
                    $q->where('branch_id', $branch->id())
                        ->orWhereNull('branch_id');
                })
                ->orderBy("compound")
                ->global()
                ->get();

            $base = $this->subTotal()->amount();
            $result = collect();

            foreach ($taxes as $tax) {
                if ($tax->type === TaxType::Inclusive) {
                    $result->push(
                        new CartTax(
                            cart: $this,
                            tax: $tax,
                            currency: $branch->currency(),
                            preCalculatedAmount: 0.0
                        )
                    );
                    continue;
                }

                $rate = (float)($tax->rate ?? 0);
                $amount = $base * ($rate / 100);

                $result->push(
                    new CartTax(
                        cart: $this,
                        tax: $tax,
                        currency: $branch->currency(),
                        preCalculatedAmount: $amount
                    )
                );

                if ($tax->compound) {
                    $base += $amount;
                }
            }

            return $result->values();
        });
    }

    /**
     * Check if any tax condition exists in the cart.
     *
     * @return bool
     */
    public function hasTax(): bool
    {
        return $this->getConditionsByType('tax')->isNotEmpty();
    }

    /**
     * Extract tax  IDs from the given conditions.
     *
     * @param CartConditionCollection<CartCondition> $taxConditions
     * @return Collection<int>
     */
    private function getTaxIds(CartConditionCollection $taxConditions): Collection
    {
        return $taxConditions->map(fn(CartCondition $c) => $c->getAttribute('tax_id'));
    }

    /**
     * Get discount
     *
     * @return CartDiscount
     */
    public function discount(): CartDiscount
    {
        return $this->rememberComputed('discount', function () {
            if (!$this->hasDiscount()) {
                return new CartDiscount();
            }

            /** @var CartCondition $discountCondition */
            $discountCondition = $this->getConditionsByType('discount')->first();

            $cartDiscount = new CartDiscount(
                $this,
                resolve($discountCondition->getAttribute('model'))::query()
                    ->find($discountCondition->getAttribute('discount_id')),
                $discountCondition
            );

            if (!$cartDiscount->isAvailable()) {
                $this->removeDiscount();
                return new CartDiscount();
            }

            return $cartDiscount;
        });
    }

    /**
     * Determine if cart has discount or not
     *
     * @return bool
     */
    public function hasDiscount(): bool
    {
        if ($this->getConditionsByType('discount')->isEmpty()) {
            return false;
        }

        /** @var CartCondition $discount */
        $discount = $this->getConditionsByType('discount')
            ->first();

        return resolve($discount->getAttribute('model'))::query()
            ->where('id', $discount->getAttribute('discount_id'))
            ->exists();
    }

    /**
     * Determine if the cart is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }

    /**
     * Remove Discount from cart
     * @return void
     */
    public function removeDiscount(): void
    {
        $this->removeConditionsByType('discount');
        $this->resetComputedState();
    }

    public function removeConditionsByType($type)
    {
        $this->resetComputedState();

        return parent::removeConditionsByType($type);
    }

    public function customer(): ?CartCustomer
    {
        return $this->rememberComputed('customer', fn() => $this->hasCustomer()
            ? new CartCustomer($this->getConditionsByType('customer')->first())
            : null);
    }

    public function hasCustomer(): bool
    {
        return $this->getConditionsByType('customer')->isNotEmpty();
    }

    /**
     * Get the total including taxes.
     *
     * @return Money
     */
    public function total(): Money
    {
        return $this->rememberComputed('total', fn() => $this->subTotal()
            ->subtract(subtrahend: $this->discount()->value())
            ->add($this->tax()));
    }

    /**
     * Calculate total tax value for the cart.
     *
     * @return Money
     */
    public function tax(): Money
    {
        return $this->rememberComputed('tax', fn() => new Money($this->calculateTax(), $this->branch()->currency()));
    }

    /**
     * Compute numeric tax amount.
     *
     * @return float
     */
    private function calculateTax(): float
    {
        return $this->taxes()->sum(fn(CartTax $cartTax) => $cartTax->amount()->amount());
    }

    /**
     * Determine if cart has branch or not
     *
     * @return bool
     */
    public function hasBranch(): bool
    {
        return $this->getConditionsByType('branch')->isNotEmpty();
    }

    /**
     * Determine if cart has order type or not
     *
     * @return bool
     */
    public function hasOrderType(): bool
    {
        return $this->getConditionsByType('order_type')->isNotEmpty();
    }

    /**
     * @throws InvalidConditionException
     */
    public function addBranch(Branch $branch): CartBranch
    {
        $this->removeBranch();

        $this->condition(
            new CartCondition([
                'name' => $branch->name,
                'type' => 'branch',
                'value' => $branch->id,
                'attributes' => [
                    'currency' => $branch->currency,
                ],
            ]),
        );

        $this->resetComputedState();
        return $this->branch();
    }

    public function removeBranch(): void
    {
        $this->removeConditionsByType('branch');
        $this->resetComputedState();
    }

    public function condition($condition)
    {
        $this->resetComputedState();

        return parent::condition($condition);
    }

    /**
     * @throws InvalidConditionException
     */
    public function addOrderType(OrderType $type): CartOrderType
    {
        $this->removeOrderType();

        $this->condition(
            new CartCondition([
                'name' => $type->trans(),
                'type' => 'order_type',
                'value' => $type->value,
            ]),
        );

        $this->addTaxes();
        $this->resetComputedState();
        return $this->orderType();
    }

    public function removeOrderType(): void
    {
        $this->removeConditionsByType('order_type');
        $this->resetComputedState();
    }

    /**
     * Add tax conditions to the cart.
     *
     * @return void
     *
     * @throws InvalidConditionException
     */
    public function addTaxes(): void
    {
        $this->removeTaxes();

        $this->findTaxes()
            ->each(function (Tax $tax) {
                $this->condition(
                    new CartCondition([
                        'name' => $tax->name,
                        'type' => 'tax',
                        'target' => 'total',
                        'value' => ($tax->type === TaxType::Inclusive ? '-' : '+') . "$tax->rate%",
                        'order' => $tax->compound ? 5 : 3,
                        'attributes' => [
                            'tax_id' => $tax->id,
                            'type' => $tax->type,
                            'compound' => $tax->compound,
                            'is_global' => $tax->is_global,
                        ],
                    ])
                );
            });
        $this->resetComputedState();
    }

    /**
     * Remove all tax conditions from the cart.
     *
     * @return void
     */
    public function removeTaxes(): void
    {
        $this->removeConditionsByType('tax');
        $this->resetComputedState();
    }

    /**
     * Find applicable taxes for all cart items.
     *
     * @return Collection<Tax>
     */
    private function findTaxes(): Collection
    {
        $branch = $this->branch();

        return Tax::query()
            ->withOutGlobalBranchPermission()
            ->where(function ($q) use ($branch) {
                $q->where('branch_id', $branch?->id())
                    ->orWhereNull('branch_id');
            })
            ->global()
            ->get()
            ->filter(fn(Tax $tax) => $this->isTaxApplicable($tax))
            ->values();
    }

    /**
     * Check if a given tax should apply based on type/order type/status.
     *
     * @param Tax $tax
     * @return bool
     */
    public function isTaxApplicable(Tax $tax): bool
    {
        $orderType = $this->orderType();

        if (empty($tax->order_types)) {
            return true;
        }

        if (is_null($orderType->value())) {
            return false;
        }

        return in_array($orderType->value(), $tax->order_types);
    }

    /**
     * Determine if discount already applied
     *
     * @param Discount|Voucher $discount
     * @return bool
     */
    public function discountAlreadyApplied(Discount|Voucher $discount): bool
    {
        $cartDiscount = $this->discount();

        return $cartDiscount->id() === $discount->id && $cartDiscount->model()->getMorphClass() == $discount->getMorphClass();
    }

    /**
     * @throws InvalidConditionException
     */
    public function applyDiscount(Discount|Voucher $discount, ?LoyaltyGift $gift = null): void
    {
        $this->removeDiscount();

        $morphClass = $discount->getMorphClass();
        $attributes = [
            'discount_id' => $discount->id,
            "model" => $morphClass,
        ];

        if (!is_null($gift)) {
            $attributes['loyalty_gift'] = [
                'id' => $gift->id,
            ];
        }

        $this->condition(
            new CartCondition([
                'name' => $morphClass == Voucher::class ? $discount->code : $discount->name,
                'type' => 'discount',
                'target' => 'total',
                'value' => $this->getDiscountValue($discount),
                'order' => 2,
                'attributes' => $attributes,
            ]),
        );
        $this->resetComputedState();
    }

    /**
     * Get discount
     *
     * @param Discount|Voucher $discount
     * @return string
     */
    private function getDiscountValue(Discount|Voucher $discount): string
    {
        if ($discount->type->isPercent()) {
            return "-$discount->value%";
        }

        return "-{$discount->value->convert($this->branch()->currency())->amount()}";
    }

    /**
     * @throws InvalidConditionException
     */
    public function addCustomer(User $customer): CartCustomer
    {
        $this->removeCustomer();

        $this->condition(
            new CartCondition([
                'name' => "$customer->name ($customer->phone)",
                'type' => 'customer',
                'target' => 'total',
                'value' => 0,
                'attributes' => [
                    'customer' => $customer,
                ],
            ]),
        );

        $this->resetComputedState();
        return $this->customer();
    }

    public function removeCustomer(): void
    {
        $this->removeConditionsByType('customer');
        $this->resetComputedState();
    }

    /**
     * @throws InvalidConditionException
     */
    public function addCurrentOrder(Order $order): CartCurrentOrder
    {
        $this->removeCurrentOrder();

        $this->condition(
            new CartCondition([
                'name' => $order->reference_no,
                'type' => 'current_order',
                'value' => $order->id,
            ]),
        );

        $this->resetComputedState();
        return $this->currentOrder();
    }

    public function removeCurrentOrder(): void
    {
        $this->removeConditionsByType('current_order');
        $this->resetComputedState();
    }

    public function currentOrder(): ?CartCurrentOrder
    {
        return $this->rememberComputed('current_order', fn() => $this->hasCurrentOrder()
            ? new CartCurrentOrder($this->getConditionsByType('current_order')->first())
            : null);
    }

    public function hasCurrentOrder(): bool
    {
        return $this->getConditionsByType('current_order')->isNotEmpty();
    }
}
