<?php

return [
    "session_already_opened" => "Esta caja ya tiene una sesión abierta. Cerrala antes de abrir una nueva.",
    "session_closed" => "Esta sesión de caja ya está cerrada.",
    "success_opened_session" => "Sesión de caja abierta correctamente. Ya podés empezar a procesar pedidos y pagos.",
    "success_closed_session" => "Sesión de caja cerrada correctamente. Todas las ventas y transacciones fueron registradas.",
    "amount_must_be_positive" => "El monto debe ser positivo.",
    "invalid_payment_method" => "Cada método de pago solo puede usarse una vez.",
    "no_active_session" => "No podés realizar :action porque no hay una sesión de caja activa.",
    "cash_payment" => "un pago en efectivo",
    "cash_over_short_note_over" => 'Sobrante: se declaró más efectivo del esperado',
    "cash_over_short_note_short" => 'Faltante: se declaró menos efectivo del esperado',
    "cannot_close_session_cash_difference" => "No se puede cerrar la sesión: Efectivo declarado (:declared) vs Esperado (:expected). Diferencia: :difference supera el umbral: :threshold.",
    "menu_is_not_active" => "No tenés ningún menú activo",
    "session_close_blocked_by_open_orders" => "No se puede cerrar la caja: hay {count} órdenes abiertas. Resolvé esas órdenes antes de cerrar.",
    "session_close_justification_required" => "La diferencia de caja ({diff}) requiere justificación (mínimo 20 caracteres).",
    "session_close_manager_required" => "La diferencia ({diff}) supera el umbral del encargado ({threshold}). Se requiere autorización.",
    "session_close_token_invalid" => "El token de autorización no es válido o expiró.",
    "session_close_approver_lacks_permission" => "El usuario que autorizó no tiene permiso para cerrar la caja con diferencia.",
];
