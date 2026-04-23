<?php

namespace Modules\Order\Events;

use Modules\Order\Models\Order;

/**
 * Disparado cuando un admin/manager cambia el payment_method de una
 * orden ya paid. Es un evento critical — listeners:
 * - AuditLogger se llama dentro del service (duplicación
 *   innecesaria acá).
 * - MailOwnerAlert (Bloque 11) notifica al dueño por separado.
 */
class OrderPaymentMethodChanged
{
    public function __construct(
        public Order $order,
        public int $paymentId,
        public string $oldMethod,
        public string $newMethod,
        public string $reason,
        public int $approverUserId,
    ) {
    }
}
