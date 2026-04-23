<?php

namespace Modules\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Payment\Enums\PaymentMethod;
use Modules\Payment\Models\PaymentMethodItem;

/**
 * Seed inicial con los 9 payment methods comunes en Argentina.
 * Solo crea si la tabla está vacía — no sobrescribe configuración
 * manual del dueño.
 */
class PaymentMethodItemSeeder extends Seeder
{
    public function run(): void
    {
        if (PaymentMethodItem::query()->withoutGlobalActive()->exists()) {
            return;
        }

        $defaults = [
            ['name' => 'Efectivo',                     'type' => PaymentMethod::Cash,          'impacts_cash' => true,  'order' => 1],
            ['name' => 'Mercado Pago',                 'type' => PaymentMethod::MobileWallet,  'impacts_cash' => false, 'order' => 2],
            ['name' => 'Modo',                         'type' => PaymentMethod::MobileWallet,  'impacts_cash' => false, 'order' => 3],
            ['name' => 'Visa Débito',                  'type' => PaymentMethod::Card,          'impacts_cash' => false, 'order' => 4],
            ['name' => 'Visa Crédito',                 'type' => PaymentMethod::Card,          'impacts_cash' => false, 'order' => 5],
            ['name' => 'Mastercard Débito',            'type' => PaymentMethod::Card,          'impacts_cash' => false, 'order' => 6],
            ['name' => 'Mastercard Crédito',           'type' => PaymentMethod::Card,          'impacts_cash' => false, 'order' => 7],
            ['name' => 'Transferencia',                'type' => PaymentMethod::BankTransfer,  'impacts_cash' => false, 'order' => 8],
            ['name' => 'Cuenta corriente / Fiado',     'type' => PaymentMethod::CreditAccount, 'impacts_cash' => false, 'order' => 9],
        ];

        foreach ($defaults as $row) {
            PaymentMethodItem::create([
                'name' => $row['name'],
                'type' => $row['type']->value,
                'impacts_cash' => $row['impacts_cash'],
                'is_active' => true,
                'order' => $row['order'],
            ]);
        }
    }
}
