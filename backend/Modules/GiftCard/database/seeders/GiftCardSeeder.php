<?php

namespace Modules\GiftCard\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Branch\Models\Branch;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Models\GiftCardBatch;

class GiftCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $primaryBranch = Branch::query()->withoutGlobalActive()->oldest('id')->first();

        $branchBatch = GiftCardBatch::query()->whereNotNull('branch_id')->inRandomOrder()->first()
            ?? GiftCardBatch::factory()->withBranch($primaryBranch?->id)->create();

        $globalBatch = GiftCardBatch::query()->whereNull('branch_id')->inRandomOrder()->first()
            ?? GiftCardBatch::factory()->global()->create();

        GiftCard::factory()
            ->count(6)
            ->active()
            ->branchScoped($primaryBranch?->id)
            ->create();

        GiftCard::factory()
            ->count(3)
            ->global()
            ->active()
            ->create();

        GiftCard::factory()
            ->global()
            ->forCustomer()
            ->noExpiry()
            ->withBalance(200, 200)
            ->state(['notes' => 'Case: global customer-linked card without expiry'])
            ->create();

        GiftCard::factory()
            ->linkedToBatch($branchBatch)
            ->forCustomer()
            ->withBalance(100, 100)
            ->state(['notes' => 'Case: sold from branch batch with assigned customer'])
            ->create();

        GiftCard::factory()
            ->linkedToBatch($globalBatch)
            ->withoutCustomer()
            ->withBalance(150, 150)
            ->state(['notes' => 'Case: global batch card ready for sale'])
            ->create();

        GiftCard::factory()
            ->used()
            ->state(['notes' => 'Case: fully redeemed card'])
            ->create();

        GiftCard::factory()
            ->branchScoped($primaryBranch?->id)
            ->expiringSoon()
            ->withBalance(80, 35)
            ->state(['notes' => 'Case: active card expiring soon'])
            ->create();

        GiftCard::factory()
            ->expired()
            ->state(['notes' => 'Case: expired card'])
            ->create();

        GiftCard::factory()
            ->disabled()
            ->state(['notes' => 'Case: disabled card'])
            ->create();

        GiftCard::factory()
            ->disabledWithBalance()
            ->forCustomer()
            ->state(['notes' => 'Case: disabled card with remaining balance'])
            ->create();
    }
}
