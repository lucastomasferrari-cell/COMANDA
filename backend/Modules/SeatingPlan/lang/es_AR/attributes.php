<?php

return [
    "floors" => [
        "name" => "Piso",
        "branch_id" => "Sucursal",
        "is_active" => "Activo",
    ],
    "zones" => [
        "name" => "Nombre",
        "branch_id" => "Sucursal",
        "floor_id" => "Piso",
        "is_active" => "Activo",
    ],
    "tables" => [
        "name" => "Nombre",
        "branch_id" => "Sucursal",
        "floor_id" => "Piso",
        "zone_id" => "Sector",
        "capacity" => "Capacidad",
        "shape" => "Forma",
        "is_active" => "Activo",
        "waiter_id" => "Mozo",
    ],

    "table_merges" => [
        "table_ids" => "Mesas",
        "table_ids.*" => "Mesa",
        "type" => "Tipo de unificación"
    ]
];
