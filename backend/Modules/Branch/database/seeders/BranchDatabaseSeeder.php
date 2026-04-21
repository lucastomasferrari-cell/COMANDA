<?php

namespace Modules\Branch\Database\Seeders;

use App\Forkiva;
use Illuminate\Database\Seeder;
use Modules\Branch\Models\Branch;
use Modules\Order\Enums\OrderType;
use Modules\Payment\Enums\PaymentMethod;
use Modules\Setting\Models\Setting;

class BranchDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            "name" => "Forkiva",
            "legal_name" => "Forkiva",
            "registration_number" => "FORKIVA-13272232",
            "vat_tin" => "FOR-12928291",
            "country_code" => Setting::get('default_country'),
            "timezone" => Setting::get('default_timezone'),
            "currency" => Setting::get('default_currency'),
            "latitude" => 31.9539,
            "longitude" => 35.9106,
            "is_active" => true,
            "is_main" => true,
            "address_line1" => 'Amman, Amman, Jordan',
            "address_line2" => "Amman Jordan",
            "city" => "Amman",
            "state" => "Amman",
            "postal_code" => "123456",
            "phone" => '+962777777777',
            "email" => "info@forkiva.app",
            "order_types" => OrderType::values(),
            "payment_methods" => PaymentMethod::values(),
        ]);

        if (Forkiva::seedDemoData()) {
            Branch::factory()->count(2)->create();
        }
    }
}
