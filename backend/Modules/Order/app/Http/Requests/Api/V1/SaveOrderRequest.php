<?php

namespace Modules\Order\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Modules\Branch\Models\Branch;
use Modules\Cart\Facades\Cart;
use Modules\Core\Http\Requests\Request;
use Modules\Order\Enums\OrderType;
use Modules\Order\Services\Order\OrderServiceInterface;
use Modules\Payment\Enums\RefundPaymentMethod;
use Modules\Pos\Enums\PosSessionStatus;
use Modules\Pos\Enums\PosSubmitAction;
use Modules\SeatingPlan\Enums\TableStatus;
use Modules\User\Enums\DefaultRole;

class SaveOrderRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = auth()->user();

        if ($user->assignedToBranch()) {
            $this->offsetSet('branch_id', $user->branch_id);
        }

        $branchId = $this->input('branch_id');

        /** @var Branch $branch */
        $branch = Branch::withoutGlobalActive()->find($branchId);

        $orderTypes = $branch?->order_types ?: [];

        if ($user->hasRole(DefaultRole::Waiter->value) && in_array(OrderType::DineIn->value, $orderTypes)) {
            $orderTypes = [OrderType::DineIn->value];
        }

        $order = null;
        if ($this->method() == 'PUT') {
            $order = app(OrderServiceInterface::class)->findOrFail($this->route('orderId'));
        }

        return [
            "submit_action" => ["required", Rule::enum(PosSubmitAction::class)],
            "refund_payment_method" => ["nullable", Rule::in(RefundPaymentMethod::values())],
            "branch_id" => "bail|required|integer|exists:branches,id,deleted_at,NULL,is_active,1",
            "menu_id" => "bail|required|integer|exists:menus,id,deleted_at,NULL",
            "type" => ["required", Rule::in($orderTypes)],
            "table_id" => [
                "bail",
                "required_if:type,dine_in",
                "nullable",
                "integer",
                Rule::exists("tables", "id")
                    ->whereNull("deleted_at")
                    ->where('is_active', true)
                    ->where('branch_id', $branchId)
                    ->when(
                        is_null($order) || $order->table_id != $this->input("table_id"),
                        fn($query) => $query->where("status", TableStatus::Available->value)
                    )
            ],
            "register_id" => [
                "bail",
                "required",
                "integer",
                Rule::exists("pos_registers", "id")
                    ->whereNull("deleted_at")
                    ->where('is_active', true)
                    ->where('branch_id', $branchId)
            ],
            "session_id" => [
                "bail",
                "required",
                "integer",
                Rule::exists("pos_sessions", "id")
                    ->whereNull("deleted_at")
                    ->where('status', PosSessionStatus::Open->value)
                    ->where('branch_id', $branchId)
                    ->where('pos_register_id', $this->input('register_id'))
            ],
            "waiter_id" => [
                "bail",
                "nullable",
                "integer",
                Rule::exists("users", 'id')
                    ->whereNull("deleted_at")
                    ->where('branch_id', $branchId)
                    ->where('is_active', true)
            ],
            "notes" => "nullable|string|max:1000",
            "guest_count" => "nullable|integer|min:1",
            "car_plate" => "nullable|string|max:200",
            "car_description" => "nullable|string|max:200",
            "scheduled_at" => "required_if:type,pre_order|nullable|date|after_or_equal:today|date_format:Y-m-d H:i:s",
        ];

    }

    /** @inheritDoc */
    public function validationData(): array
    {
        return [
            ...parent::validationData(),
            'type' => Cart::orderType()->value(),
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "order::attributes.orders";
    }
}
