<?php

return [
    "orders" => [
        "type" => "النوع",
        "table_id" => "الطاولة",
        "payment_methods" => "طرق الدفع",
        "payment_methods.*" => "طريقة الدفع",
        "payments" => "المدفوعات",
        "payments.*.method" => "طريقة الدفع",
        "payments.*.amount" => "المبلغ",
        "pos_register_id" => "سجل نقطة البيع",
        "products" => "المنتجات",
        "products.*.id" => "معرّف المنتج",
        "products.*.quantity" => "كمية المنتج",
        "products.*.options" => "الخيارات",
        "products.*.options.*.id" => "معرّف الخيار",
        "products.*.options.*.values" => "قيم الخيار",
        "products.*.options.*.values.*.id" => "معرّف قيمة الخيار",
        "products.*.options.*.values.*.value" => "قيمة الخيار",
        "notes" => "ملاحظات",
        "guest_count" => "عدد الضيوف",
        "payment_type" => "نوع الدفع",
        "amount_to_be_paid" => "المبلغ المراد دفعه",
        "car_plate" => "رقم لوحة السيارة",
        "car_description" => "وصف السيارة",
        "products.*.actions" => "الإجراءات",
        "products.*.actions.*.action" => "الإجراء",
        "products.*.actions.*.quantity" => "الكمية",
        "refund_payment_method" => "طريقة استرجاع الدفع",
        "products.*.order_product_id" => "منتج الطلب",
    ],

    "reasons" => [
        "name" => "الاسم",
        "type" => "النوع",
        "is_active" => "نشط",
    ],

    "cancel_or_refund" => [
        "register_id" => "سجل نقطة البيع",
        "reason_id" => "السبب",
        "note" => "ملاحظة",
        "session_id" => "الجلسة",
        "refund_payment_method" => "طريقة استرداد الدفع",
        "gift_card_code" => "رمز البطاقة الهدية",
    ],

    "payments" => [
        "register_id" => "سجل نقطة البيع",
        "payment_mode" => "طريقة الدفع",
        "amount_to_be_paid" => "المبلغ المطلوب دفعه",
        "customer_given_amount" => "المبلغ المدفوع من العميل",
        "change_return" => "المبلغ المرتجع",
        "payments" => "المدفوعات",
        "payments.*.method" => "طريقة الدفع",
        "payments.*.amount" => "المبلغ",
        "payments.*.transaction_id" => "رقم العملية",
        "payments.*.gift_card_code" => "رمز البطاقة الهدية",
    ],

    "print" => [
        "register_id" => "جهاز نقطة البيع",
        "branch_id" => "الفرع",
    ]

];
