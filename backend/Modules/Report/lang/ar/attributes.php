<?php
return [
    "sales" => [
        "period" => "الفترة",
        "total_orders" => "إجمالي الطلبات",
        "total_products" => "إجمالي المنتجات",
        "subtotal" => "المجموع الفرعي",
        "tax" => "الضريبة",
        "total" => "الإجمالي",
    ],

    "products_purchase" => [
        "period" => "الفترة",
        "product" => "المنتج",
        "quantity" => "الكمية",
        "total" => "الإجمالي",
    ],

    "tax" => [
        "period" => "الفترة",
        "tax_name" => "اسم الضريبة",
        "total_orders" => "إجمالي الطلبات",
        "total" => "الإجمالي",
    ],

    "branch_performance" => [
        "period" => "الفترة",
        "branch_name" => "اسم الفرع",
        "total_orders" => "إجمالي الطلبات",
        "total" => "الإجمالي",
    ],

    "payments" => [
        "period" => "الفترة",
        "payment_method" => "طريقة الدفع",
        "total_paid" => "إجمالي المدفوع",
        "total" => "الإجمالي",
    ],

    "discounts_and_vouchers" => [
        "period" => "الفترة",
        "discount" => "الخصم / القسيمة",
        "discount_type" => "النوع",
        "total_orders" => "إجمالي الطلبات",
        "total_discount" => "إجمالي الخصم",
    ],
    "gift_card_sales" => [
        "card_code" => "رمز البطاقة",
        "batch" => "الدفعة",
        "branch" => "الفرع",
        "customer" => "العميل",
        "initial_balance" => "الرصيد الابتدائي",
        "currency" => "العملة",
        "sold_by" => "تم البيع بواسطة",
        "sold_at" => "تاريخ البيع",
    ],
    "gift_card_redemption" => [
        "card_code" => "رمز البطاقة",
        "order_number" => "رقم الطلب",
        "branch" => "الفرع",
        "amount_redeemed" => "المبلغ المستبدل",
        "currency" => "العملة",
        "order_currency" => "عملة الطلب",
        "exchange_rate" => "سعر الصرف",
        "amount_converted" => "المبلغ المحول",
        "redeemed_by" => "تم الاستبدال بواسطة",
        "redeemed_at" => "تاريخ الاستبدال",
    ],
    "gift_card_outstanding_balance" => [
        "card_code" => "رمز البطاقة",
        "branch" => "الفرع",
        "customer" => "العميل",
        "initial_balance" => "الرصيد الابتدائي",
        "current_balance" => "الرصيد الحالي",
        "currency" => "العملة",
        "status" => "الحالة",
        "expiry_date" => "تاريخ الانتهاء",
        "created_at" => "تاريخ الإنشاء",
    ],
    "gift_card_liability" => [
        "period" => "الفترة",
        "currency" => "العملة",
        "cards_sold" => "عدد البطاقات المباعة",
        "total_sold_value" => "إجمالي قيمة البيع",
        "redeemed_value" => "القيمة المستبدلة",
        "outstanding_balance" => "الرصيد المتبقي",
        "expired_balance" => "الرصيد المنتهي",
    ],
    "gift_card_expiry" => [
        "currency" => "العملة",
        "card_code" => "رمز البطاقة",
        "branch" => "الفرع",
        "customer" => "العميل",
        "initial_balance" => "الرصيد الابتدائي",
        "remaining_balance" => "الرصيد المتبقي",
        "expiry_date" => "تاريخ الانتهاء",
        "status" => "الحالة",
    ],
    "gift_card_transactions" => [
        "currency" => "العملة",
        "transaction_id" => "معرّف الحركة",
        "card_code" => "رمز البطاقة",
        "transaction_type" => "نوع الحركة",
        "amount" => "المبلغ",
        "balance_before" => "الرصيد قبل",
        "balance_after" => "الرصيد بعد",
        "branch" => "الفرع",
        "order" => "الطلب",
        "created_by" => "تم الإنشاء بواسطة",
        "date" => "التاريخ",
    ],
    "gift_card_branch_performance" => [
        "period" => "الفترة",
        "currency" => "العملة",
        "branch" => "الفرع",
        "cards_sold" => "عدد البطاقات المباعة",
        "total_sold_value" => "إجمالي قيمة البيع",
        "redeemed_value" => "القيمة المستبدلة",
        "outstanding_balance" => "الرصيد المتبقي",
        "expired_balance" => "الرصيد المنتهي",
    ],
    "gift_card_batch" => [
        "batch_name" => "اسم الدفعة",
        "branch" => "الفرع",
        "currency" => "العملة",
        "cards_generated" => "البطاقات المولدة",
        "total_value" => "إجمالي القيمة",
        "cards_used" => "البطاقات المستخدمة",
        "cards_remaining" => "البطاقات المتبقية",
        "created_by" => "أنشئت بواسطة",
        "created_at" => "تاريخ الإنشاء",
    ],

    "product_tax" => [
        "period" => "الفترة",
        "tax_name" => "اسم الضريبة",
        "product_name" => "اسم المنتج",
        "total_products" => "إجمالي المنتجات",
        "total" => "الإجمالي",
    ],

    "ingredient_usage" => [
        "period" => "الفترة",
        "ingredient_name" => "اسم المكون",
        "total_used" => "إجمالي الاستخدام",
    ],

    "low_stock_alerts" => [
        "ingredient_name" => "اسم المكون",
        "current_stock" => "المخزون الحالي",
        "alert_quantity" => "كمية التنبيه",
    ],

    "register_summary" => [
        "date" => "نطاق التاريخ",
        "register_name" => "اسم السجل",
        "sessions_count" => "عدد الجلسات",
        "orders_count" => "عدد الطلبات",
        "system_cash_sales" => "مبيعات النقد (النظام)",
        "system_card_sales" => "مبيعات البطاقة (النظام)",
        "system_other_sales" => "مبيعات أخرى (النظام)",
        "total_sales" => "إجمالي المبيعات",
        "total_refunds" => "إجمالي المرتجعات",
    ],

    "cash_movement" => [
        "period" => "الفترة",
        "register_name" => "اسم السجل",
        "user_name" => "المستخدم",
        "reason" => "السبب",
        "direction" => "الاتجاه",
        "amount" => "المبلغ",
    ],

    "sales_by_creator" => [
        "period" => "الفترة",
        "creator" => "المنشئ",
        "total_orders" => "إجمالي الطلبات",
        "total_products" => "إجمالي المنتجات",
        "subtotal" => "المجموع الفرعي",
        "tax" => "الضريبة",
        "total" => "الإجمالي",
    ],

    "sales_by_cashier" => [
        "period" => "الفترة",
        "cashier" => "أمين الصندوق",
        "total_orders" => "إجمالي الطلبات",
        "total_products" => "إجمالي المنتجات",
        "subtotal" => "المجموع الفرعي",
        "tax" => "الضريبة",
        "total" => "الإجمالي",
    ],

    "categorized_products" => [
        "category" => "الفئة",
        "products_count" => "عدد المنتجات",
    ],

    "upcoming_orders" => [
        "period" => "الفترة",
        "total_orders" => "إجمالي الطلبات",
        "total_products" => "إجمالي المنتجات",
        "subtotal" => "الإجمالي الفرعي",
        "tax" => "الضريبة",
        "total" => "الإجمالي",
    ],

    "cost_and_revenue_by_order" => [
        "period" => "الفترة",
        "total_orders" => "إجمالي الطلبات",
        "total_products" => "إجمالي المنتجات",
        "total_cost" => "إجمالي التكلفة",
        "revenue" => "الإيرادات",
    ],

    "cost_and_revenue_by_product" => [
        "period" => "الفترة",
        "product" => "المنتج",
        "quantity" => "الكمية",
        "total_cost" => "إجمالي التكلفة",
        "revenue" => "الإيرادات",
    ],

    "loyalty_program_summary" => [
        "period" => "الفترة",
        "program_name" => "اسم البرنامج",
        "total_customers" => "إجمالي العملاء",
        "total_earned_points" => "إجمالي النقاط المكتسبة",
        "total_redeemed_points" => "إجمالي النقاط المستبدلة",
        "total_expired_points" => "إجمالي النقاط المنتهية",
    ],

    "loyalty_total_earned_points" => [
        "period" => "الفترة",
        "program_name" => "اسم البرنامج",
        "total_earned_points" => "إجمالي النقاط المكتسبة",
    ],

    "loyalty_total_redeemed_points" => [
        "period" => "الفترة",
        "program_name" => "اسم البرنامج",
        "total_redeemed_points" => "إجمالي النقاط المستبدلة",
    ],

    "loyalty_total_expired_points" => [
        "period" => "الفترة",
        "program_name" => "اسم البرنامج",
        "total_expired_points" => "إجمالي النقاط المنتهية",
    ],

    "loyalty_system_points_balance" => [
        "period" => "الفترة",
        "program_name" => "اسم البرنامج",
        "total_active_customers" => "إجمالي العملاء النشطين",
        "total_points_balance" => "إجمالي رصيد النقاط",
    ],

    "loyalty_redemption_rate" => [
        "period" => "الفترة",
        "program_name" => "اسم البرنامج",
        "earned_points" => "النقاط المكتسبة",
        "redeemed_points" => "النقاط المستبدلة",
        "redemption_rate" => "معدل الاستبدال (%)",
    ],

    "loyalty_average_points_per_program" => [
        "period" => "الفترة",
        "program_name" => "اسم البرنامج",
        "total_customers" => "إجمالي العملاء",
        "average_points_balance" => "متوسط رصيد النقاط",
    ],

    "loyalty_points_lifecycle_timeline" => [
        "date" => "التاريخ",
        "earned_points" => "النقاط المكتسبة",
        "redeemed_points" => "النقاط المستبدلة",
        "expired_points" => "النقاط المنتهية",
    ],

    "loyalty_last_activity" => [
        "customer_name" => "اسم العميل",
        "last_earned_date" => "تاريخ آخر نقاط مكتسبة",
        "last_redeemed_date" => "تاريخ آخر استبدال",
        "last_transaction_type" => "نوع آخر معاملة",
    ],

    "loyalty_inactive_customers" => [
        "customer_name" => "اسم العميل",
        "last_activity_date" => "تاريخ آخر نشاط",
        "days_inactive" => "عدد أيام عدم النشاط",
    ],

    "loyalty_no_redemptions" => [
        "customer_name" => "اسم العميل",
        "lifetime_points" => "إجمالي النقاط المكتسبة",
        "total_redemptions" => "إجمالي مرات الاستبدال",
    ],

    "loyalty_top_customers_by_points" => [
        "customer_name" => "اسم العميل",
        "lifetime_points" => "إجمالي النقاط المكتسبة",
        "points_balance" => "الرصيد الحالي",
    ],

    "loyalty_top_customers_by_spend" => [
        "customer_name" => "اسم العميل",
        "total_spend" => "إجمالي الإنفاق",
        "total_orders" => "إجمالي الطلبات",
    ],

    "loyalty_top_customers_by_orders" => [
        "customer_name" => "اسم العميل",
        "total_orders" => "إجمالي الطلبات",
        "average_order_value" => "متوسط قيمة الطلب",
    ],

    "loyalty_tier_customer_distribution" => [
        "period" => "الفترة",
        "tier_name" => "اسم المستوى",
        "customers_count" => "عدد العملاء",
    ],


    "loyalty_tier_redemption_rate" => [
        "period" => "الفترة",
        "tier_name" => "اسم المستوى",
        "earned_points" => "النقاط المكتسبة",
        "redeemed_points" => "النقاط المستبدلة",
        "redemption_rate" => "معدل الاستبدال (%)",
    ],

    "loyalty_most_redeemed_rewards" => [
        "period" => "الفترة",
        "reward_name" => "اسم المكافأة",
        "reward_type" => "نوع المكافأة",
        "total_redemptions" => "إجمالي الاستبدالات",
        "total_points_spent" => "إجمالي النقاط المصروفة",
    ],

    "loyalty_least_used_rewards" => [
        "period" => "الفترة",
        "reward_name" => "اسم المكافأة",
        "total_redemptions" => "إجمالي الاستبدالات",
    ],

    "loyalty_never_redeemed_rewards" => [
        "reward_name" => "اسم المكافأة",
        "points_cost" => "تكلفة النقاط",
        "created_date" => "تاريخ الإنشاء",
    ],

    "loyalty_rewards_by_type" => [
        "reward_type" => "نوع المكافأة",
        "total_rewards" => "إجمالي المكافآت",
        "total_redemptions" => "إجمالي الاستبدالات",
    ],

    "loyalty_rewards_by_tier" => [
        "period" => "الفترة",
        "tier_name" => "اسم المستوى",
        "reward_name" => "اسم المكافأة",
        "points_cost" => "تكلفة النقاط",
    ],

    "loyalty_rewards_by_program" => [
        "period" => "الفترة",
        "program_name" => "اسم البرنامج",
        "reward_name" => "اسم المكافأة",
        "reward_type" => "نوع المكافأة",
    ],

    "loyalty_available_gifts" => [
        "gift_id" => "رقم الهدية",
        "customer_name" => "اسم العميل",
        "reward_name" => "اسم المكافأة",
        "valid_until" => "صالح حتى",
        "valid_from" => "ساري اعتبارًا من",
    ],

    "loyalty_used_gifts" => [
        "gift_id" => "رقم الهدية",
        "customer_name" => "اسم العميل",
        "reward_name" => "اسم المكافأة",
        "used_date" => "تاريخ الاستخدام",
    ],

    "loyalty_expired_gifts" => [
        "gift_id" => "رقم الهدية",
        "customer_name" => "اسم العميل",
        "reward_name" => "اسم المكافأة",
        "expiration_date" => "تاريخ الانتهاء",
    ],

    "loyalty_gift_usage_rate" => [
        "reward_name" => "اسم المكافأة",
        "issued_count" => "عدد الإصدارات",
        "used_count" => "عدد الاستخدامات",
        "usage_rate" => "معدل الاستخدام (%)",
    ],

    "loyalty_unused_gifts_per_customer" => [
        "customer_name" => "اسم العميل",
        "unused_gifts_count" => "عدد الهدايا غير المستخدمة",
    ],

    "loyalty_gifts_linked_to_orders" => [
        "order_id" => "رقم الطلب",
        "gift_id" => "رقم الهدية",
        "reward_name" => "اسم المكافأة",
        "discount_value" => "قيمة الخصم",
    ],

    "loyalty_redemptions_by_status" => [
        "period" => "الفترة",
        "status" => "الحالة",
        "total_redemptions" => "إجمالي الاستبدالات",
        "total_points" => "إجمالي النقاط",
    ],

    "loyalty_redemptions_by_program" => [
        "period" => "الفترة",
        "program_name" => "اسم البرنامج",
        "total_redemptions" => "إجمالي الاستبدالات",
        "total_points" => "إجمالي النقاط",
    ],

    "loyalty_average_points_per_redemption" => [
        "period" => "الفترة",
        "program_name" => "اسم البرنامج",
        "average_points" => "متوسط النقاط",
    ],

    "loyalty_active_promotions" => [
        "promotion_name" => "اسم الحملة",
        "promotion_type" => "نوع الحملة",
        "usage_count" => "عدد الاستخدام",
        "start_date" => "تاريخ البدء",
        "end_date" => "تاريخ الانتهاء",
    ],

    "loyalty_expired_promotions" => [
        "promotion_name" => "اسم الحملة",
        "promotion_type" => "نوع الحملة",
        "end_date" => "تاريخ الانتهاء",
    ],

    "loyalty_promotion_usage" => [
        "promotion_name" => "اسم الحملة",
        "total_usage" => "إجمالي الاستخدام",
        "total_customers" => "إجمالي العملاء",
    ],

    "loyalty_highest_impact_promotions" => [
        "promotion_name" => "اسم الحملة",
        "total_points_generated" => "إجمالي النقاط المتولدة",
    ],

    "loyalty_bonus_vs_multiplier_comparison" => [
        "promotion_type" => "نوع الحملة",
        "total_promotions" => "إجمالي الحملات",
        "total_usage" => "إجمالي الاستخدام",
    ],

    "loyalty_category_boost_promotions" => [
        "promotion_name" => "اسم الحملة",
        "usage_count" => "عدد الاستخدام",
    ],

    "loyalty_new_member_promotions" => [
        "promotion_name" => "اسم الحملة",
        "customers_joined" => "العملاء المنضمون",
        "bonus_points" => "نقاط المكافأة",
    ],

    "loyalty_free_items_cost" => [
        "period" => "الفترة",
        "product_name" => "اسم المنتج",
        "quantity" => "الكمية",
        "cost_price" => "تكلفة الوحدة",
        "total_cost" => "إجمالي التكلفة",
    ],

    "loyalty_revenue_from_loyalty_customers" => [
        "period" => "الفترة",
        "revenue" => "الإيراد",
    ],

    "loyalty_revenue_before_after_loyalty" => [
        "period" => "الفترة",
        "revenue_before" => "الإيراد قبل",
        "revenue_after" => "الإيراد بعد",
    ],

    "loyalty_average_order_value_loyalty_customers" => [
        "period" => "الفترة",
        "total_orders" => "إجمالي الطلبات",
        "average_order_value" => "متوسط قيمة الطلب",
    ],
];
