<?php

namespace Modules\AuditLog\Console\Commands;

use Illuminate\Console\Command;
use Modules\AuditLog\Models\PendingApproval;

/**
 * Marca pending_approvals con status=pending que pasaron expires_at
 * como expired. El mail diario al dueño los incluye agregados.
 */
class ExpirePendingApprovals extends Command
{
    protected $signature = 'antifraud:expire-pending-approvals';
    protected $description = 'Marca pending_approvals vencidos como expired.';

    public function handle(): int
    {
        $expired = PendingApproval::query()
            ->where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update([
                'status' => 'expired',
                'reviewed_at' => now(),
            ]);

        $this->info("Expired {$expired} pending approvals.");
        return self::SUCCESS;
    }
}
