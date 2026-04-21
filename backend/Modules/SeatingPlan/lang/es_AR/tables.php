<?php

return [
    "tables" => "Mesas",
    "table" => "Mesa",

    "table_columns" => [
        "name" => "Nombre",
        "floor" => "Piso",
        "zone" => "Sector",
        "capacity" => "Capacidad",
        "status" => "Estado",
        "shape" => "Forma",
        "qrcode" => "Código QR",
        "waiter" => "Mozo",
    ],

    "form" => [
        "cards" => [
            "table_information" => "Información de la mesa",
            "location" => "Ubicación"
        ]
    ],

    "filters" => [
        "floor" => "Piso",
        "zone" => "Sector",
        "shape" => "Forma",
        "status" => "Estado",
    ],

    "view_qrcode" => "Ver código QR",
    "unassigned" => "Sin asignar",
    "assign_waiter" => "Asignar mozo",
    "reassign_waiter" => "Reasignar mozo",
    "create_order" => "Crear pedido",
    "edit_order" => "Editar pedido",
    "make_available" => "Marcar como disponible",
    "merge_table" => "Unificar mesa",
    "split_table" => "Dividir mesa",
    "merge_details" => "Detalles de unificación",
    "primary" => "Principal",
    "primary_table" => "Mesa principal",

    "split_confirmation" => [
        "title" => "Confirmar división",
        "message" => "¿Estás seguro de que querés dividir esta mesa? Se van a eliminar los ajustes de unificación.",
        "confirm_button_text" => "Sí, dividir mesa"
    ],

    "order_details" => "Detalles del pedido",
    "orders_details" => "Detalles de los pedidos",

    "show" => [
        "cards" => [
            "table_details" => "Detalles de la mesa",
            "qrcode" => "Código QR",
            "status_history" => "Historial de estados",
        ],
        "qrcode" => "Código QR",
        "name" => "Nombre",
        "branch" => "Sucursal",
        "floor" => "Piso",
        "zone" => "Sector",
        "capacity" => "Capacidad",
        "status" => "Estado",
        "shape" => "Forma",
        "activated" => "Activación",
        "yes" => "Sí",
        "no" => "No",
    ]
];
