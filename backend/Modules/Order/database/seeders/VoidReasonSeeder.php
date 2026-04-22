<?php

namespace Modules\Order\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Razones de anulación pre-cargadas. El dueño puede editar / agregar /
 * deshabilitar desde Admin > Configuración > Anti-fraude.
 *
 * Nombres JSON para soportar es_AR + en + ar. El POS expone el nombre
 * por idioma activo al cargar el dropdown.
 */
class VoidReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            // Item, cajero solo
            [
                'code' => 'item_input_error',
                'name' => ['es_AR' => 'Error al cargar', 'en' => 'Input error', 'ar' => 'خطأ في الإدخال'],
                'applies_to' => 'item',
                'requires_manager_approval' => false,
                'order' => 10,
            ],
            [
                'code' => 'item_customer_change',
                'name' => ['es_AR' => 'Cliente cambió de opinión', 'en' => 'Customer changed mind', 'ar' => 'غير العميل رأيه'],
                'applies_to' => 'item',
                'requires_manager_approval' => false,
                'order' => 20,
            ],
            // Item, requiere manager
            [
                'code' => 'item_kitchen_error',
                'name' => ['es_AR' => 'Error de cocina', 'en' => 'Kitchen error', 'ar' => 'خطأ في المطبخ'],
                'applies_to' => 'item',
                'requires_manager_approval' => true,
                'order' => 30,
            ],
            [
                'code' => 'item_customer_return',
                'name' => ['es_AR' => 'Cliente devolvió', 'en' => 'Customer returned', 'ar' => 'أرجعه العميل'],
                'applies_to' => 'item',
                'requires_manager_approval' => true,
                'order' => 40,
            ],
            [
                'code' => 'item_out_of_stock',
                'name' => ['es_AR' => 'Producto agotado', 'en' => 'Out of stock', 'ar' => 'نفد المخزون'],
                'applies_to' => 'item',
                'requires_manager_approval' => true,
                'order' => 50,
            ],
            [
                'code' => 'item_other',
                'name' => ['es_AR' => 'Otro motivo', 'en' => 'Other', 'ar' => 'سبب آخر'],
                'applies_to' => 'item',
                'requires_manager_approval' => true,
                'order' => 90,
            ],
            // Order level
            [
                'code' => 'order_no_show',
                'name' => ['es_AR' => 'No se presentó el cliente', 'en' => 'Customer no-show', 'ar' => 'لم يحضر العميل'],
                'applies_to' => 'order',
                'requires_manager_approval' => true,
                'order' => 10,
            ],
            [
                'code' => 'order_test',
                'name' => ['es_AR' => 'Orden de prueba', 'en' => 'Test order', 'ar' => 'طلب تجريبي'],
                'applies_to' => 'order',
                'requires_manager_approval' => true,
                'order' => 20,
            ],
            [
                'code' => 'order_other',
                'name' => ['es_AR' => 'Otro motivo', 'en' => 'Other', 'ar' => 'سبب آخر'],
                'applies_to' => 'order',
                'requires_manager_approval' => true,
                'order' => 90,
            ],
        ];

        foreach ($reasons as $reason) {
            DB::table('void_reasons')->updateOrInsert(
                ['code' => $reason['code']],
                [
                    'name' => json_encode($reason['name']),
                    'applies_to' => $reason['applies_to'],
                    'requires_manager_approval' => $reason['requires_manager_approval'],
                    'is_active' => true,
                    'order' => $reason['order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
