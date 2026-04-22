<?php

return [
    "orders" => [
        "type" => "Type",
        "table_id" => "Table",
        "pos_register_id" => "Pos Register",
        "products.*" => "Product",
        "notes" => "Notes",
        "guest_count" => "Guest count",
        "payment_type" => "Payment Type",
        "amount_to_be_paid" => "Amount to be paid",
        "car_plate" => "Car Plate",
        "car_description" => "Car Description",
        "scheduled_at" => "Scheduled At",
        "products.*.actions" => "Actions",
        "products.*.actions.*.action" => "Action",
        "products.*.actions.*.quantity" => "Quantity",
        "refund_payment_method" => "Refund Payment Method",
        "products.*.order_product_id" => "Order Product",
        "custom_product" => "open item",
        "custom_name" => "Item name",
        "custom_price" => "Price",
        "custom_description" => "Description",
        "quantity" => "Quantity",
    ],

    "reasons" => [
        "name" => "Name",
        "type" => "Type",
        "is_active" => "Active",
    ],

    "cancel_or_refund" => [
        "register_id" => "Pos Register",
        "session_id" => "Session",
        "reason_id" => "Reason",
        "note" => "Note",
        "refund_payment_method" => "Refund Payment Method",
        "gift_card_code" => "Gift Card Code",
    ],

    "payments" => [
        "register_id" => "Pos Register",
        "payment_mode" => "Payment Mode",
        "amount_to_be_paid" => "Amount to be paid",
        "customer_given_amount" => "Customer Given Amount",
        "change_return" => "Change Return",
        "payments" => "Payments",
        "payments.*.method" => "Payment Method",
        "payments.*.amount" => "Amount",
        "payments.*.transaction_id" => "Transaction ID",
        "payments.*.gift_card_code" => "Gift Card Code",
    ],
    
    "print" => [
        "register_id" => "Pos Register",
        "branch_id" => "Branch",
    ]
];
