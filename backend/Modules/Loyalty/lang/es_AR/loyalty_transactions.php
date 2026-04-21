<?php

return [
    "loyalty_transactions" => "Transacciones de fidelización",
    "loyalty_transaction" => "Transacción de fidelización",

    "table" => [
        "customer" => "Cliente",
        "type" => "Tipo",
        "description" => "Descripción",
        "points" => "Puntos",
        "amount" => "Monto",
    ],

    "filters" => [
        "customer" => "Cliente",
        "type" => "Tipo",
    ],

    "type_descriptions" => [
        "default" => [
            "earn" => "Acumulaste :points puntos por el pedido #:order_id",
            "redeem" => "Canjeaste :points puntos en el pedido #:order_id",
            "adjust" => "Ajuste manual de :points puntos",
            "expire" => ":points puntos vencidos por inactividad",
            "bonus" => ":points puntos bonus por promoción"
        ]
    ]
];
