<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Category\Models\Category;
use Modules\Menu\Models\Menu;
use Modules\Option\Models\Option;
use Modules\Option\Models\OptionValue;
use Modules\Pos\Enums\PosSessionStatus;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Models\PosSession;
use Modules\Product\Models\Product;
use Modules\SeatingPlan\Models\Floor;
use Modules\SeatingPlan\Models\Table;
use Modules\SeatingPlan\Models\Zone;
use Modules\Setting\Models\Setting;
use Modules\User\Models\User;

/**
 * Demo argentino para QA visual del POS.
 *
 * Idempotente: corre múltiples veces sin duplicar. Cada paso hace
 * firstOrCreate/updateOrCreate o check de existencia.
 *
 * Ejecutar: php artisan db:seed --class=DemoArSeeder
 */
class DemoArSeeder extends Seeder
{
    private int $adminId;
    private int $branchId = 1;
    private int $menuId;

    public function run(): void
    {
        // withOutGlobalBranchPermission bypasea el global scope de HasBranch
        // que filtra por branch del user autenticado — en CLI no hay auth,
        // el scope resuelve null y queries vuelven vacías. Esto me tiró un
        // seedPosSession con opened_by=admin (fallback) en vez de cajero.
        $admin = User::withOutGlobalBranchPermission()
            ->where('username', 'admin')
            ->firstOrFail();
        $this->adminId = $admin->id;

        // En single-branch mode, TODO user necesita branch_id=1 para que
        // assignedToBranch() devuelva true. Si no: PosViewerService cae en
        // Branch::list() (filtra por permission admin.branches.index que
        // super_admin NO tiene — está desactivado en single-branch) y
        // devuelve vacío → POS muestra empty state "no hay sucursales".
        // Fix post Sprint 2: forzar branch_id=1 en todos los users.
        User::withOutGlobalBranchPermission()
            ->whereNull('branch_id')
            ->update(['branch_id' => $this->branchId]);

        $menu = Menu::withOutGlobalBranchPermission()
            ->where('branch_id', $this->branchId)
            ->where('is_active', true)
            ->firstOrFail();
        $this->menuId = $menu->id;

        // Normalizar nombre del menú activo. En algunos ambientes quedó
        // con typo de mayúsculas ("Menu pRUEBA") — editado desde la UI
        // con el caps trabado o algo. Lo corregimos acá idempotente.
        if ($menu->getTranslation('name', 'es_AR', false) !== 'Menú Prueba') {
            $menu->setTranslation('name', 'es_AR', 'Menú Prueba');
            $menu->setTranslation('name', 'en', 'Test Menu');
            $menu->save();
        }

        $this->command->info('→ Usuarios cajero + mozo');
        $this->seedUsers();

        $this->command->info('→ Floor + Zones + 15 Tables');
        $this->seedSeatingPlan();

        $this->command->info('→ 12 Categorías');
        $categoryIds = $this->seedCategories();

        $this->command->info('→ ~50 Productos con precios AR 2026');
        $productIdsByCategory = $this->seedProducts($categoryIds);

        $this->command->info('→ Options (Cocción / Guarnición / Extras) + asociaciones');
        $this->seedOptions($productIdsByCategory);

        $this->command->info('→ Apertura de caja con $10.000');
        $this->seedPosSession();

        $this->command->info('→ Paleta coral marca (theme settings)');
        $this->seedThemeColors();

        $this->command->info('→ Feature flags modos del POS');
        $this->seedPosFeatureFlags();

        $this->command->info('✓ Demo AR listo.');
    }

    private function seedPosFeatureFlags(): void
    {
        // Sprint 3.A — MVP para mercado AR: dine_in (salón) + counter
        // (mostrador / barra) + takeout (retiro) activos. delivery off
        // hasta que tengamos integración con Rappi/PedidosYa y flujo de
        // motoquero propio. El switcher del POS viewer lee estos flags
        // del endpoint /pos/configuration.feature_flags.pos.
        $flags = [
            'pos.dine_in' => true,
            'pos.counter' => true,
            'pos.takeout' => true,
            'pos.delivery' => false,
        ];

        foreach ($flags as $key => $value) {
            Setting::set($key, $value ? '1' : '0');
        }
    }

    private function seedUsers(): void
    {
        // Los roles 'cashier' y 'waiter' los sembró el vendor RoleSeeder.
        User::withOutGlobalBranchPermission()->updateOrCreate(
            ['username' => 'cajero'],
            [
                'name' => 'Cajero de Prueba',
                'email' => 'cajero@forkiva.app',
                'password' => Hash::make('cajero123'),
                'gender' => 'male',
                'is_active' => true,
                'branch_id' => $this->branchId,
                'created_by' => $this->adminId,
                'phone_country_iso_code' => 'AR',
            ]
        )->syncRoles(['cashier']);

        User::withOutGlobalBranchPermission()->updateOrCreate(
            ['username' => 'mozo'],
            [
                'name' => 'Mozo de Prueba',
                'email' => 'mozo@forkiva.app',
                'password' => Hash::make('mozo123'),
                'gender' => 'male',
                'is_active' => true,
                'branch_id' => $this->branchId,
                'created_by' => $this->adminId,
                'phone_country_iso_code' => 'AR',
            ]
        )->syncRoles(['waiter']);
    }

    private function seedSeatingPlan(): void
    {
        $floor = Floor::withOutGlobalBranchPermission()->firstOrCreate(
            ['branch_id' => $this->branchId, 'order' => 1],
            [
                'name' => ['es_AR' => 'Planta Baja', 'en' => 'Main Floor'],
                'created_by' => $this->adminId,
                'is_active' => true,
            ]
        );

        $zones = [
            ['name' => 'Salón',   'count' => 8, 'order' => 1, 'start' => 1,  'color' => '#4CAF50'],
            ['name' => 'Terraza', 'count' => 4, 'order' => 2, 'start' => 9,  'color' => '#FF9800'],
            ['name' => 'Barra',   'count' => 3, 'order' => 3, 'start' => 13, 'color' => '#2196F3'],
        ];

        foreach ($zones as $zoneDef) {
            $zone = Zone::withOutGlobalBranchPermission()->firstOrCreate(
                [
                    'floor_id' => $floor->id,
                    'branch_id' => $this->branchId,
                    'order' => $zoneDef['order'],
                ],
                [
                    'name' => ['es_AR' => $zoneDef['name'], 'en' => $zoneDef['name']],
                    'color' => $zoneDef['color'],
                    'created_by' => $this->adminId,
                    'is_active' => true,
                ]
            );

            for ($i = 0; $i < $zoneDef['count']; $i++) {
                $tableNumber = $zoneDef['start'] + $i;
                Table::withOutGlobalBranchPermission()->firstOrCreate(
                    [
                        'zone_id' => $zone->id,
                        'order' => $tableNumber,
                    ],
                    [
                        'name' => ['es_AR' => "Mesa {$tableNumber}", 'en' => "Table {$tableNumber}"],
                        'floor_id' => $floor->id,
                        'branch_id' => $this->branchId,
                        'capacity' => $zoneDef['name'] === 'Barra' ? 2 : 4,
                        'status' => 'available',
                        'shape' => 'square',
                        'created_by' => $this->adminId,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * @return array<string, int>
     */
    private function seedCategories(): array
    {
        // Slugs con guión (no underscore) porque el boot de Category
        // normaliza vía Str::slug que convierte _ → -. Si acá usáramos
        // underscore, firstOrCreate no encontraría la fila existente (que
        // quedó con -) y choca con el unique (menu_id, slug) en re-run.
        //
        // Color hues Sprint 1.B: valores típicos gastronómicos AR.
        // [slug => [label, hue]].
        $defs = [
            'entradas' => ['Entradas', 30],               // mostaza cálida
            'principales' => ['Principales', 15],         // terracota (cerca del primary)
            'pastas' => ['Pastas', 40],                   // amarillo pasta
            'pizzas' => ['Pizzas', 10],                   // rojo tomate
            'hamburguesas' => ['Hamburguesas', 20],       // naranja tostado
            'ensaladas' => ['Ensaladas', 130],            // verde lechuga
            'postres' => ['Postres', 330],                // rosado dulce
            'bebidas-sin-alcohol' => ['Bebidas sin alcohol', 200], // azul fresco
            'cervezas' => ['Cervezas', 45],               // ámbar dorado
            'vinos' => ['Vinos', 350],                    // vino tinto
            'cocktails' => ['Cocktails', 300],            // magenta/aperol
            'cafe' => ['Café', 25],                       // marrón cálido
        ];

        $ids = [];
        $order = 1;
        foreach ($defs as $slug => [$label, $hue]) {
            // withOutGlobalBranchPermission evita que el scope de HasMenu
            // oculte categorías existentes en re-runs (sin auth el scope
            // devuelve vacío → firstOrCreate intenta insert → duplicate).
            $cat = Category::withOutGlobalBranchPermission()
                ->firstOrCreate(
                    ['slug' => $slug, 'menu_id' => $this->menuId],
                    [
                        'name' => ['es_AR' => $label, 'en' => $label],
                        'color_hue' => $hue,
                        'created_by' => $this->adminId,
                        'is_active' => true,
                        'order' => $order++,
                    ]
                );
            // Si la categoría ya existía pero sin hue (pre-sprint 1.B),
            // actualizamos para que el placeholder funcione.
            if ($cat->color_hue === null) {
                $cat->update(['color_hue' => $hue]);
            }
            $ids[$slug] = $cat->id;
        }

        return $ids;
    }

    /**
     * @param array<string, int> $categoryIds
     * @return array<string, int[]>
     */
    private function seedProducts(array $categoryIds): array
    {
        // [nombre, precio_ARS, slug_categoria]
        $catalog = [
            // Entradas
            ['Provoleta', 5500, 'entradas'],
            ['Empanadas de carne (x6)', 7200, 'entradas'],
            ['Rabas', 8500, 'entradas'],
            ['Tabla de fiambres', 9500, 'entradas'],

            // Principales
            ['Milanesa napolitana', 11500, 'principales'],
            ['Bife de chorizo', 14500, 'principales'],
            ['Suprema de pollo', 9800, 'principales'],
            ['Lomo a la pimienta', 15800, 'principales'],

            // Pastas
            ['Sorrentinos', 10500, 'pastas'],
            ['Ñoquis con salsa', 8800, 'pastas'],
            ['Ravioles', 9500, 'pastas'],
            ['Fideos con tuco', 7500, 'pastas'],

            // Pizzas
            ['Muzzarella', 8500, 'pizzas'],
            ['Napolitana', 9800, 'pizzas'],
            ['Fugazzeta', 10500, 'pizzas'],
            ['Calabresa', 10800, 'pizzas'],

            // Hamburguesas
            ['Hamburguesa clásica', 7500, 'hamburguesas'],
            ['Doble cheddar', 9800, 'hamburguesas'],
            ['Veggie', 8500, 'hamburguesas'],

            // Ensaladas
            ['Caesar', 7200, 'ensaladas'],
            ['Griega', 7500, 'ensaladas'],
            ['Mixta', 5500, 'ensaladas'],

            // Postres
            ['Flan mixto', 4500, 'postres'],
            ['Tiramisú', 5500, 'postres'],
            ['Helado 3 gustos', 4800, 'postres'],
            ['Vigilante', 5200, 'postres'],

            // Bebidas sin alcohol
            ['Coca 500ml', 2500, 'bebidas-sin-alcohol'],
            ['Sprite 500ml', 2500, 'bebidas-sin-alcohol'],
            ['Agua mineral', 1800, 'bebidas-sin-alcohol'],
            ['Agua saborizada', 2200, 'bebidas-sin-alcohol'],

            // Cervezas
            ['Quilmes (porrón)', 3500, 'cervezas'],
            ['Stella Artois', 4500, 'cervezas'],
            ['Heineken', 4200, 'cervezas'],
            ['Corona', 4800, 'cervezas'],

            // Vinos
            ['Malbec (copa)', 4500, 'vinos'],
            ['Cabernet (copa)', 4500, 'vinos'],
            ['Chardonnay (copa)', 4200, 'vinos'],

            // Cocktails
            ['Fernet con Coca', 5500, 'cocktails'],
            ['Gin tonic', 6500, 'cocktails'],
            ['Aperol Spritz', 6800, 'cocktails'],
            ['Caipirinha', 6500, 'cocktails'],

            // Café
            ['Espresso', 2200, 'cafe'],
            ['Cortado', 2500, 'cafe'],
            ['Café con leche', 3200, 'cafe'],
            ['Capuchino', 3500, 'cafe'],
        ];

        $productIdsByCategory = [];
        foreach ($catalog as [$name, $price, $catSlug]) {
            $sku = 'DEMO-' . strtoupper(str_replace(
                [' ', '(', ')', 'ñ', 'á', 'é', 'í', 'ó', 'ú'],
                ['-', '', '', 'n', 'a', 'e', 'i', 'o', 'u'],
                $name
            ));
            $product = Product::withOutGlobalBranchPermission()
                ->firstOrCreate(
                    ['sku' => $sku],
                    [
                        'menu_id' => $this->menuId,
                        'price' => $price,
                        'name' => ['es_AR' => $name, 'en' => $name],
                        'created_by' => $this->adminId,
                        'is_active' => true,
                    ]
                );

            $product->categories()->syncWithoutDetaching([$categoryIds[$catSlug]]);

            $productIdsByCategory[$catSlug] ??= [];
            $productIdsByCategory[$catSlug][] = $product->id;
        }

        return $productIdsByCategory;
    }

    /**
     * @param array<string, int[]> $productIdsByCategory
     */
    private function seedOptions(array $productIdsByCategory): void
    {
        // Cocción — radio required, aplica a bifes y milanesas.
        $coccion = Option::withOutGlobalBranchPermission()->firstOrCreate(
            ['sku' => 'DEMO-OPT-COCCION'],
            [
                'name' => ['es_AR' => 'Cocción', 'en' => 'Doneness'],
                'type' => 'radio',
                'is_required' => true,
                'is_global' => true,
                'branch_id' => $this->branchId,
                'created_by' => $this->adminId,
                'order' => 1,
            ]
        );
        $this->syncOptionValues($coccion->id, [
            ['Jugoso', 'Rare'],
            ['A punto', 'Medium'],
            ['Bien cocido', 'Well done'],
        ]);

        // Guarnición — radio required, aplica a principales (bife/lomo/milanesa).
        $guarnicion = Option::withOutGlobalBranchPermission()->firstOrCreate(
            ['sku' => 'DEMO-OPT-GUARNICION'],
            [
                'name' => ['es_AR' => 'Guarnición', 'en' => 'Side'],
                'type' => 'radio',
                'is_required' => true,
                'is_global' => true,
                'branch_id' => $this->branchId,
                'created_by' => $this->adminId,
                'order' => 2,
            ]
        );
        $this->syncOptionValues($guarnicion->id, [
            ['Papas fritas', 'French fries'],
            ['Puré', 'Mashed potatoes'],
            ['Ensalada', 'Salad'],
        ]);

        // Extras — checkbox opcional con precios, aplica a hamburguesas.
        $extras = Option::withOutGlobalBranchPermission()->firstOrCreate(
            ['sku' => 'DEMO-OPT-EXTRAS'],
            [
                'name' => ['es_AR' => 'Extras', 'en' => 'Extras'],
                'type' => 'checkbox',
                'is_required' => false,
                'is_global' => true,
                'branch_id' => $this->branchId,
                'created_by' => $this->adminId,
                'order' => 3,
            ]
        );
        $this->syncOptionValuesWithPrice($extras->id, [
            ['Extra queso', 'Extra cheese', 500],
            ['Huevo', 'Egg', 400],
            ['Panceta', 'Bacon', 600],
        ]);

        // Asociar options a products via pivot product_options.
        // - Cocción: solo los bifes y la milanesa (no todos los principales).
        $coccionTargets = $this->productIdsWhereNameIn($productIdsByCategory, ['Milanesa napolitana', 'Bife de chorizo', 'Lomo a la pimienta']);
        // - Guarnición: todos los principales.
        $guarnicionTargets = $productIdsByCategory['principales'] ?? [];
        // - Extras: todas las hamburguesas.
        $extrasTargets = $productIdsByCategory['hamburguesas'] ?? [];

        foreach ($coccionTargets as $pid) {
            Product::find($pid)?->options()->syncWithoutDetaching([$coccion->id]);
        }
        foreach ($guarnicionTargets as $pid) {
            Product::find($pid)?->options()->syncWithoutDetaching([$guarnicion->id]);
        }
        foreach ($extrasTargets as $pid) {
            Product::find($pid)?->options()->syncWithoutDetaching([$extras->id]);
        }
    }

    /**
     * Asegura que las option values existan para una option dada (sin precio).
     *
     * @param int $optionId
     * @param array<array{0:string,1:string}> $values [['es_AR_label', 'en_label'], ...]
     */
    private function syncOptionValues(int $optionId, array $values): void
    {
        foreach ($values as $idx => [$esLabel, $enLabel]) {
            OptionValue::withOutGlobalBranchPermission()->firstOrCreate(
                ['option_id' => $optionId, 'order' => $idx + 1],
                [
                    'label' => ['es_AR' => $esLabel, 'en' => $enLabel],
                    'price_type' => 'fixed',
                    'price' => null,
                    'branch_id' => $this->branchId,
                    'created_by' => $this->adminId,
                ]
            );
        }
    }

    /**
     * Asegura que las option values existan con precios fijos (+$N).
     *
     * @param int $optionId
     * @param array<array{0:string,1:string,2:int}> $values
     */
    private function syncOptionValuesWithPrice(int $optionId, array $values): void
    {
        foreach ($values as $idx => [$esLabel, $enLabel, $price]) {
            OptionValue::withOutGlobalBranchPermission()->firstOrCreate(
                ['option_id' => $optionId, 'order' => $idx + 1],
                [
                    'label' => ['es_AR' => $esLabel, 'en' => $enLabel],
                    'price_type' => 'fixed',
                    'price' => $price,
                    'branch_id' => $this->branchId,
                    'created_by' => $this->adminId,
                ]
            );
        }
    }

    /**
     * Dado el catálogo sembrado, devuelve los ids de productos cuyo nombre
     * (es_AR) coincida con alguno del filtro.
     *
     * @param array<string, int[]> $productIdsByCategory
     * @param string[] $names
     * @return int[]
     */
    private function productIdsWhereNameIn(array $productIdsByCategory, array $names): array
    {
        $allIds = array_merge(...array_values($productIdsByCategory));
        return Product::withOutGlobalBranchPermission()
            ->whereIn('id', $allIds)
            ->get()
            ->filter(fn ($p) => in_array($p->getTranslation('name', 'es_AR'), $names, true))
            ->pluck('id')
            ->all();
    }

    private function seedPosSession(): void
    {
        $register = PosRegister::withOutGlobalBranchPermission()
            ->where('branch_id', $this->branchId)
            ->firstOrFail();

        $cajero = User::withOutGlobalBranchPermission()
            ->where('username', 'cajero')
            ->first();
        $openedBy = $cajero?->id ?? $this->adminId;

        // updateOrCreate sobre la única sesión abierta del register: si ya
        // había una (de corridas anteriores o del vendor seeder), la
        // sobrescribimos con los valores que pide el QA ($10.000 + cajero).
        // Si no había, se crea limpia.
        $openSession = PosSession::withOutGlobalBranchPermission()
            ->where('pos_register_id', $register->id)
            ->where('status', PosSessionStatus::Open->value)
            ->first();

        $attrs = [
            'opened_by' => $openedBy,
            'opened_at' => now(),
            'opening_float' => 10000,
            'branch_id' => $this->branchId,
            'created_by' => $openedBy,
        ];

        if ($openSession) {
            $openSession->update($attrs);
            $session = $openSession;
        } else {
            $session = PosSession::create(array_merge($attrs, [
                'pos_register_id' => $register->id,
                'status' => PosSessionStatus::Open->value,
            ]));
        }

        DB::table('pos_registers')->where('id', $register->id)->update([
            'last_session_id' => $session->id,
        ]);
    }

    private function seedThemeColors(): void
    {
        // Sprint 1.A — paleta coral marca. Si no se setean acá el backend
        // devuelve los defaults vendor (#F57C00 naranja etc.) y
        // applyThemeSettings() del frontend los pisa sobre la paleta nueva
        // del theme.ts. Idempotente vía Setting::set (updateOrCreate).
        $colors = [
            'theme_primary_color' => '#E8735A',
            'theme_secondary_color' => '#6B6259',
            'theme_success_color' => '#0D9B6A',
            'theme_info_color' => '#3B82F6',
            'theme_warning_color' => '#F59E0B',
            'theme_error_color' => '#DC2626',
            'pwa_background_color' => '#FDFBF7',
            'pwa_theme_color' => '#FDFBF7',
        ];

        foreach ($colors as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
