<?php

namespace Modules\GiftCard\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Branch\Models\Branch;
use Modules\GiftCard\Models\GiftCardBatch;

class GiftCardBatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $primaryBranch = Branch::query()->withoutGlobalActive()->oldest('id')->first();

        GiftCardBatch::factory()
            ->withBranch($primaryBranch?->id)
            ->smallBatch()
            ->state([
                'name' => ['en' => 'Branch Starter Batch', 'ar' => 'دفعة فرعية صغيرة'],
                'prefix' => 'BST',
            ])
            ->create();

        GiftCardBatch::factory()
            ->withBranch($primaryBranch?->id)
            ->largeBatch()
            ->highValue()
            ->state([
                'name' => ['en' => 'Branch Campaign Batch', 'ar' => 'دفعة حملة فرعية'],
                'prefix' => 'BCP',
            ])
            ->create();

        GiftCardBatch::factory()
            ->global()
            ->smallBatch()
            ->state([
                'name' => ['en' => 'Global Welcome Batch', 'ar' => 'دفعة ترحيبية عامة'],
                'prefix' => 'GLW',
            ])
            ->create();

        GiftCardBatch::factory()
            ->global()
            ->largeBatch()
            ->highValue()
            ->state([
                'name' => ['en' => 'Global Premium Batch', 'ar' => 'دفعة عامة مميزة'],
                'prefix' => 'GLP',
            ])
            ->create();
    }
}
