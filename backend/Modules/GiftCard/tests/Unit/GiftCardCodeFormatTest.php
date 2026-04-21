<?php

namespace Modules\GiftCard\Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\GiftCard\Services\GiftCardCode\GiftCardCodeServiceInterface;
use Tests\TestCase;

class GiftCardCodeFormatTest extends TestCase
{
    public function test_generated_code_uses_cashier_friendly_grouped_format(): void
    {
        $code = app(GiftCardCodeServiceInterface::class)->generate();

        $this->assertMatchesRegularExpression('/^GC-[2-9A-HJ-NP-Z]{4}-[2-9A-HJ-NP-Z]{4}-[2-9A-HJ-NP-Z]{4}$/', $code);
    }

    public function test_generated_code_can_include_a_short_safe_batch_prefix(): void
    {
        $code = app(GiftCardCodeServiceInterface::class)->generate('branch-01');

        $this->assertMatchesRegularExpression('/^GC-[2-9A-HJ-NP-Z]{2}-[2-9A-HJ-NP-Z]{4}-[2-9A-HJ-NP-Z]{4}-[2-9A-HJ-NP-Z]{4}$/', $code);
    }

    public function test_normalize_code_input_ignores_case_spaces_and_hyphens(): void
    {
        $normalized = app(GiftCardCodeServiceInterface::class)->normalize('gc-ab 7k9m-q4xt-2l8c');

        $this->assertSame('GCAB7K9MQ4XT2L8C', $normalized);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('gift_cards');
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->softDeletes();
        });
    }
}
