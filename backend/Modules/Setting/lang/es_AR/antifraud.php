<?php

return [
    "title" => "Anti-fraude",
    "subtitle" => "Umbrales, reporte al dueño y políticas de aprobación.",
    "test_sent" => "Reporte de prueba enviado.",
    "test_failed" => "No se pudo enviar el reporte de prueba.",
    "sections" => [
        "discount_thresholds" => "Umbrales de descuento",
        "open_item" => "Ítem suelto (open item)",
        "session_close" => "Arqueo de caja",
        "daily_report" => "Reporte diario por email",
        "pending_policy" => "Autorizaciones pendientes",
    ],
    "fields" => [
        "discount_cashier_max" => "% máximo para cajero",
        "discount_manager_max" => "% máximo para encargado",
        "discount_explainer" => "Descuentos mayores al % del encargado requieren autorización admin.",
        "open_item_max_per_shift" => "Cantidad máxima por turno",
        "open_item_max_amount_each" => "Monto máximo por ítem (ARS)",
        "open_item_max_total_per_shift" => "Monto acumulado máximo por turno (ARS)",
        "session_close_justification_threshold" => "Diferencia máxima sin justificación (ARS)",
        "session_close_manager_required_percent" => "Diferencia máxima sin aprobación manager (% del esperado)",
        "daily_report_enabled" => "Enviar mail diario al dueño",
        "owner_alert_email" => "Email destinatario",
        "daily_report_hour" => "Hora de envío (0-23)",
        "daily_report_hour_hint" => "Hora del día en 24hs. Default 6 AM.",
        "send_test_now" => "Enviar reporte de prueba ahora",
        "allow_pending_without_manager" => "Permitir ejecución sin aprobación (queda en registros pendientes)",
        "allow_pending_explainer" => "Si está desactivado (default), las acciones sensibles requieren PIN del manager O devuelven error. Si está activado, sin manager disponible la acción se ejecuta y queda flagueada para revisión del admin.",
    ],
];
