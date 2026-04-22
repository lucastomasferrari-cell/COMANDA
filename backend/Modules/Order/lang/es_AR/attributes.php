<?php

return [
    "orders" => [
        "type" => "Tipo",
        "table_id" => "Mesa",
        "pos_register_id" => "Caja",
        "products.*" => "Producto",
        "notes" => "Notas",
        "guest_count" => "Cantidad de comensales",
        "payment_type" => "Tipo de pago",
        "amount_to_be_paid" => "Monto a pagar",
        "car_plate" => "Patente del auto",
        "car_description" => "Descripción del auto",
        "scheduled_at" => "Programado para",
        "products.*.actions" => "Acciones",
        "products.*.actions.*.action" => "Acción",
        "products.*.actions.*.quantity" => "Cantidad",
        "refund_payment_method" => "Método de pago del reembolso",
        "products.*.order_product_id" => "Producto del pedido",
        "custom_product" => "ítem suelto",
        "custom_name" => "Nombre del ítem",
        "custom_price" => "Precio",
        "custom_description" => "Descripción",
        "quantity" => "Cantidad",
    ],

    "reasons" => [
        "name" => "Nombre",
        "type" => "Tipo",
        "is_active" => "Activo",
    ],

    "cancel_or_refund" => [
        "register_id" => "Caja",
        "session_id" => "Sesión",
        "reason_id" => "Motivo",
        "note" => "Nota",
        "refund_payment_method" => "Método de pago del reembolso",
        "gift_card_code" => "Código de tarjeta de regalo",
    ],

    "payments" => [
        "register_id" => "Caja",
        "payment_mode" => "Modalidad de pago",
        "amount_to_be_paid" => "Monto a pagar",
        "customer_given_amount" => "Monto entregado por el cliente",
        "change_return" => "Vuelto",
        "payments" => "Pagos",
        "payments.*.method" => "Método de pago",
        "payments.*.amount" => "Monto",
        "payments.*.transaction_id" => "ID de transacción",
        "payments.*.gift_card_code" => "Código de tarjeta de regalo",
    ],

    "print" => [
        "register_id" => "Caja",
        "branch_id" => "Sucursal",
    ]
];
