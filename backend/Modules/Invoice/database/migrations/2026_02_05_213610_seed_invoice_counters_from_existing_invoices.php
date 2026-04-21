<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $rows = DB::table('invoices')
            ->join('invoice_parties', 'invoice_parties.id', '=', 'invoices.seller_party_id')
            ->select(
                'invoice_parties.cr_number',
                DB::raw('MAX(invoices.invoice_counter) as last_counter')
            )
            ->whereNotNull('invoice_parties.cr_number')
            ->groupBy('invoice_parties.cr_number')
            ->get();

        foreach ($rows as $row) {
            DB::table('invoice_counters')
                ->updateOrInsert(
                    ['cr_number' => $row->cr_number],
                    ['last_counter' => $row->last_counter, 'created_at' => now(), 'updated_at' => now()]
                );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
