<?php

return [
    "pos_registers" => [
        "name" => "Nombre",
        "branch_id" => "Sucursal",
        "code" => "Código",
        "note" => "Nota",
        "is_active" => "Activo",
        "invoice_printer_id" => "Impresora de facturas",
        "bill_printer_id" => "Impresora de cuentas",
        "waiter_printer_id" => "Impresora del mozo",
        "delivery_printer_id" => "Impresora de envíos",
    ],

    "pos_sessions" => [
        "branch_id" => "Sucursal",
        "pos_register_id" => "Caja",
        "opening_float" => "Fondo de apertura",
        "notes" => "Notas",
        "declared_cash" => "Efectivo declarado",
    ],

    "pos_cash_movements" => [
        "pos_register_id" => "Caja",
        "reference" => "Referencia",
        "amount" => "Monto",
        "notes" => "Notas",
        "reason" => "Motivo",
        "direction" => "Dirección",
    ]
];
