<?php

return [
    "suppliers" => [
        "name" => "Nombre",
        "phone" => "Teléfono",
        "email" => "Email",
        "address" => "Dirección",
        "branch_id" => "Sucursal",
    ],

    "units" => [
        "name" => "Nombre",
        "symbol" => "Símbolo",
        "type" => "Tipo",
    ],

    "ingredients" => [
        "name" => "Nombre",
        "branch_id" => "Sucursal",
        "unit_id" => "Unidad",
        "cost_per_unit" => "Costo por unidad",
        "alert_quantity" => "Cantidad de alerta",
        "is_returnable" => "Retornable",
    ],

    "stock_movements" => [
        "branch_id" => "Sucursal",
        "ingredient_id" => 'Ingrediente',
        "supplier_id" => "Proveedor",
        "type" => "Tipo",
        "quantity" => "Cantidad",
        "note" => "Nota",
    ],

    "purchases" => [
        "branch_id" => "Sucursal",
        "supplier_id" => "Proveedor",
        "notes" => "Notas",
        "expected_at" => "Fecha esperada",
        "discount" => "Descuento",
        "tax" => "Impuesto",
        "items" => "Ítems",
        "items.*" => "Ítem",
        "items.*.ingredient_id" => "Ingrediente",
        "items.*.quantity" => "Cantidad",
        "items.*.received_quantity" => "Cantidad recibida",
        "items.*.unit_cost" => "Costo unitario",
        "items.*.id" => "ID",
        "reference" => "N.° de referencia",
    ]
];
