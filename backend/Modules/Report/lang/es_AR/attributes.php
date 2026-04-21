<?php
return [
    "sales" => [
        "period" => "Período",
        "total_orders" => "Pedidos totales",
        "total_products" => "Productos totales",
        "subtotal" => "Subtotal",
        "tax" => "Impuesto",
        "total" => "Total",
    ],

    "products_purchase" => [
        "period" => "Período",
        "product" => "Producto",
        "quantity" => "Cantidad",
        "total" => "Total",
    ],

    "tax" => [
        "period" => "Período",
        "tax_name" => "Nombre del impuesto",
        "total_orders" => "Pedidos totales",
        "total" => "Total",
    ],

    "branch_performance" => [
        "period" => "Período",
        "branch_name" => "Nombre de la sucursal",
        "total_orders" => "Pedidos totales",
        "total" => "Total",
    ],

    "payments" => [
        "period" => "Período",
        "payment_method" => 'Método de pago',
        "total_paid" => "Total pagado",
        "total" => "Total",
    ],

    "discounts_and_vouchers" => [
        "period" => "Período",
        "discount" => "Descuento / Cupón",
        "discount_type" => "Tipo",
        "total_orders" => "Pedidos totales",
        "total_discount" => "Descuento total",
    ],
    "gift_card_sales" => [
        "card_code" => "Código de tarjeta",
        "batch" => "Lote",
        "branch" => "Sucursal",
        "customer" => "Cliente",
        "initial_balance" => "Saldo inicial",
        "currency" => "Moneda",
        "sold_by" => "Vendido por",
        "sold_at" => "Vendido el",
    ],
    "gift_card_redemption" => [
        "card_code" => "Código de tarjeta",
        "order_number" => "Número de pedido",
        "branch" => "Sucursal",
        "amount_redeemed" => "Monto canjeado",
        "currency" => "Moneda",
        "order_currency" => "Moneda del pedido",
        "exchange_rate" => "Tipo de cambio",
        "amount_converted" => "Monto convertido",
        "redeemed_by" => "Canjeado por",
        "redeemed_at" => "Canjeado el",
    ],
    "gift_card_outstanding_balance" => [
        "card_code" => "Código de tarjeta",
        "branch" => "Sucursal",
        "customer" => "Cliente",
        "initial_balance" => "Saldo inicial",
        "current_balance" => "Saldo actual",
        "currency" => "Moneda",
        "status" => "Estado",
        "expiry_date" => "Fecha de vencimiento",
        "created_at" => "Creado el",
    ],
    "gift_card_liability" => [
        "period" => "Período",
        "currency" => "Moneda",
        "cards_sold" => "Tarjetas vendidas",
        "total_sold_value" => "Valor total vendido",
        "redeemed_value" => "Valor canjeado",
        "outstanding_balance" => "Saldo pendiente",
        "expired_balance" => "Saldo vencido",
    ],
    "gift_card_expiry" => [
        "currency" => "Moneda",
        "card_code" => "Código de tarjeta",
        "branch" => "Sucursal",
        "customer" => "Cliente",
        "initial_balance" => "Saldo inicial",
        "remaining_balance" => "Saldo restante",
        "expiry_date" => "Fecha de vencimiento",
        "status" => "Estado",
    ],
    "gift_card_transactions" => [
        "currency" => "Moneda",
        "transaction_id" => "ID de transacción",
        "card_code" => "Código de tarjeta",
        "transaction_type" => "Tipo de transacción",
        "amount" => "Monto",
        "balance_before" => "Saldo anterior",
        "balance_after" => "Saldo posterior",
        "branch" => "Sucursal",
        "order" => "Pedido",
        "created_by" => "Creado por",
        "date" => "Fecha",
    ],
    "gift_card_branch_performance" => [
        "period" => "Período",
        "currency" => "Moneda",
        "branch" => "Sucursal",
        "cards_sold" => "Tarjetas vendidas",
        "total_sold_value" => "Valor total vendido",
        "redeemed_value" => "Valor canjeado",
        "outstanding_balance" => "Saldo pendiente",
        "expired_balance" => "Saldo vencido",
    ],
    "gift_card_batch" => [
        "batch_name" => "Nombre del lote",
        "branch" => "Sucursal",
        "currency" => "Moneda",
        "cards_generated" => "Tarjetas generadas",
        "total_value" => "Valor total",
        "cards_used" => "Tarjetas usadas",
        "cards_remaining" => "Tarjetas restantes",
        "created_by" => "Creado por",
        "created_at" => "Creado el",
    ],

    "product_tax" => [
        "period" => "Período",
        "tax_name" => "Nombre del impuesto",
        "product_name" => "Nombre del producto",
        "total_products" => "Productos totales",
        "total" => "Total",
    ],

    "ingredient_usage" => [
        "period" => "Período",
        "ingredient_name" => "Nombre del ingrediente",
        "total_used" => "Total utilizado",
    ],

    "low_stock_alerts" => [
        "ingredient_name" => "Nombre del ingrediente",
        "current_stock" => "Stock actual",
        "alert_quantity" => "Cantidad de alerta",
    ],

    "register_summary" => [
        "date" => "Rango de fechas",
        'register_name' => "Nombre de la caja",
        'sessions_count' => "Cantidad de sesiones",
        'orders_count' => "Cantidad de pedidos",
        'system_cash_sales' => "Ventas en efectivo del sistema",
        'system_card_sales' => "Ventas con tarjeta del sistema",
        'system_other_sales' => "Otras ventas del sistema",
        'total_sales' => "Ventas totales",
        'total_refunds' => "Reintegros totales",
    ],

    "cash_movement" => [
        "period" => "Período",
        'register_name' => "Nombre de la caja",
        "user_name" => "Usuario",
        "reason" => "Motivo",
        "direction" => "Dirección",
        "amount" => "Monto",
    ],

    "sales_by_creator" => [
        "period" => "Período",
        "creator" => "Creador",
        "total_orders" => "Pedidos totales",
        "total_products" => "Productos totales",
        "subtotal" => "Subtotal",
        "tax" => "Impuesto",
        "total" => "Total",
    ],

    "categorized_products" => [
        "category" => "Categoría",
        "products_count" => "Cantidad de productos",
    ],

    "upcoming_orders" => [
        "period" => "Período",
        "total_orders" => "Pedidos totales",
        "total_products" => "Productos totales",
        "subtotal" => "Subtotal",
        "tax" => "Impuesto",
        "total" => "Total",
    ],

    "cost_and_revenue_by_order" => [
        "period" => "Período",
        "total_orders" => "Pedidos totales",
        "total_products" => "Productos totales",
        "total_cost" => "Costo total",
        "revenue" => "Ingresos",
    ],

    "cost_and_revenue_by_product" => [
        "period" => "Período",
        "product" => "Producto",
        "quantity" => "Cantidad",
        "total_cost" => "Costo total",
        "revenue" => "Ingresos",
    ],

    "loyalty_program_summary" => [
        "period" => "Período",
        "program_name" => "Nombre del programa",
        "total_customers" => "Clientes totales",
        "total_earned_points" => "Puntos ganados totales",
        "total_redeemed_points" => "Puntos canjeados totales",
        "total_expired_points" => "Puntos vencidos totales",
    ],

    "loyalty_total_earned_points" => [
        "period" => "Período",
        "program_name" => "Nombre del programa",
        "total_earned_points" => "Puntos ganados totales",
    ],

    "loyalty_total_redeemed_points" => [
        "period" => "Período",
        "program_name" => "Nombre del programa",
        "total_redeemed_points" => "Puntos canjeados totales",
    ],

    "loyalty_total_expired_points" => [
        "period" => "Período",
        "program_name" => "Nombre del programa",
        "total_expired_points" => "Puntos vencidos totales",
    ],

    "loyalty_system_points_balance" => [
        "period" => "Período",
        "program_name" => "Nombre del programa",
        "total_active_customers" => "Clientes activos totales",
        "total_points_balance" => "Saldo total de puntos",
    ],

    "loyalty_redemption_rate" => [
        "period" => "Período",
        "program_name" => "Nombre del programa",
        "earned_points" => "Puntos ganados",
        "redeemed_points" => "Puntos canjeados",
        "redemption_rate" => "Tasa de canje (%)",
    ],

    "loyalty_average_points_per_program" => [
        "period" => "Período",
        "program_name" => "Nombre del programa",
        "total_customers" => "Clientes totales",
        "average_points_balance" => "Saldo promedio de puntos",
    ],

    "loyalty_points_lifecycle_timeline" => [
        "date" => "Fecha",
        "earned_points" => "Puntos ganados",
        "redeemed_points" => "Puntos canjeados",
        "expired_points" => "Puntos vencidos",
    ],


    "loyalty_last_activity" => [
        "customer_name" => "Nombre del cliente",
        "last_earned_date" => "Última fecha de ganancia",
        "last_redeemed_date" => "Última fecha de canje",
        "last_transaction_type" => "Último tipo de transacción",
    ],

    "loyalty_inactive_customers" => [
        "customer_name" => "Nombre del cliente",
        "last_activity_date" => "Última fecha de actividad",
        "days_inactive" => "Días inactivo",
    ],

    "loyalty_no_redemptions" => [
        "customer_name" => "Nombre del cliente",
        "lifetime_points" => "Puntos acumulados",
        "total_redemptions" => "Canjes totales",
    ],

    "loyalty_top_customers_by_points" => [
        "customer_name" => "Nombre del cliente",
        "lifetime_points" => "Puntos acumulados",
        "points_balance" => "Saldo actual",
    ],

    "loyalty_top_customers_by_spend" => [
        "customer_name" => "Nombre del cliente",
        "total_spend" => "Gasto total",
        "total_orders" => "Pedidos totales",
    ],

    "loyalty_top_customers_by_orders" => [
        "customer_name" => "Nombre del cliente",
        "total_orders" => "Pedidos totales",
        "average_order_value" => "Valor promedio del pedido",
    ],

    "loyalty_tier_customer_distribution" => [
        "period" => "Período",
        "tier_name" => "Nombre del nivel",
        "customers_count" => "Cantidad de clientes",
    ],


    "loyalty_tier_redemption_rate" => [
        "period" => "Período",
        "tier_name" => "Nombre del nivel",
        "earned_points" => "Puntos ganados",
        "redeemed_points" => "Puntos canjeados",
        "redemption_rate" => "Tasa de canje (%)",
    ],

    "loyalty_most_redeemed_rewards" => [
        "period" => "Período",
        "reward_name" => "Nombre de la recompensa",
        "reward_type" => "Tipo de recompensa",
        "total_redemptions" => "Canjes totales",
        "total_points_spent" => "Puntos gastados totales",
    ],

    "loyalty_least_used_rewards" => [
        "period" => "Período",
        "reward_name" => "Nombre de la recompensa",
        "total_redemptions" => "Canjes totales",
    ],

    "loyalty_never_redeemed_rewards" => [
        "reward_name" => "Nombre de la recompensa",
        "points_cost" => "Costo en puntos",
        "created_date" => "Fecha de creación",
    ],

    "loyalty_rewards_by_type" => [
        "reward_type" => "Tipo de recompensa",
        "total_rewards" => "Recompensas totales",
        "total_redemptions" => "Canjes totales",
    ],

    "loyalty_rewards_by_tier" => [
        "period" => "Período",
        "tier_name" => "Nombre del nivel",
        "reward_name" => "Nombre de la recompensa",
        "points_cost" => "Costo en puntos",
    ],

    "loyalty_rewards_by_program" => [
        "period" => "Período",
        "program_name" => "Nombre del programa",
        "reward_name" => "Nombre de la recompensa",
        "reward_type" => "Tipo de recompensa",
    ],

    "loyalty_available_gifts" => [
        "gift_id" => "ID de regalo",
        "customer_name" => "Nombre del cliente",
        "reward_name" => "Nombre de la recompensa",
        "valid_until" => "Válido hasta",
        "valid_from" => "Válido desde",
    ],

    "loyalty_used_gifts" => [
        "gift_id" => "ID de regalo",
        "customer_name" => "Nombre del cliente",
        "reward_name" => "Nombre de la recompensa",
        "used_date" => "Fecha de uso",
    ],

    "loyalty_expired_gifts" => [
        "gift_id" => "ID de regalo",
        "customer_name" => "Nombre del cliente",
        "reward_name" => "Nombre de la recompensa",
        "expiration_date" => "Fecha de vencimiento",
    ],

    "loyalty_gift_usage_rate" => [
        "reward_name" => "Nombre de la recompensa",
        "issued_count" => "Cantidad emitida",
        "used_count" => "Cantidad usada",
        "usage_rate" => "Tasa de uso (%)",
    ],

    "loyalty_unused_gifts_per_customer" => [
        "customer_name" => "Nombre del cliente",
        "unused_gifts_count" => "Cantidad de regalos no usados",
    ],

    "loyalty_gifts_linked_to_orders" => [
        "order_id" => "ID de pedido",
        "gift_id" => "ID de regalo",
        "reward_name" => "Nombre de la recompensa",
        "discount_value" => "Valor del descuento",
    ],

    "loyalty_redemptions_by_status" => [
        "period" => "Período",
        "status" => "Estado",
        "total_redemptions" => "Canjes totales",
        "total_points" => "Puntos totales",
    ],

    "loyalty_redemptions_by_program" => [
        "period" => "Período",
        "program_name" => "Nombre del programa",
        "total_redemptions" => "Canjes totales",
        "total_points" => "Puntos totales",
    ],

    "loyalty_average_points_per_redemption" => [
        "period" => "Período",
        "program_name" => "Nombre del programa",
        "average_points" => "Puntos promedio",
    ],

    "loyalty_active_promotions" => [
        "promotion_name" => "Nombre de la promoción",
        "promotion_type" => "Tipo de promoción",
        "usage_count" => "Cantidad de usos",
        "start_date" => "Fecha de inicio",
        "end_date" => "Fecha de fin",
    ],

    "loyalty_expired_promotions" => [
        "promotion_name" => "Nombre de la promoción",
        "promotion_type" => "Tipo de promoción",
        "end_date" => "Fecha de fin",
    ],

    "loyalty_promotion_usage" => [
        "promotion_name" => "Nombre de la promoción",
        "total_usage" => "Uso total",
        "total_customers" => "Clientes totales",
    ],

    "loyalty_highest_impact_promotions" => [
        "promotion_name" => "Nombre de la promoción",
        "total_points_generated" => "Puntos totales generados",
    ],

    "loyalty_bonus_vs_multiplier_comparison" => [
        "promotion_type" => "Tipo de promoción",
        "total_promotions" => "Promociones totales",
        "total_usage" => "Uso total",
    ],

    "loyalty_category_boost_promotions" => [
        "promotion_name" => "Nombre de la promoción",
        "usage_count" => "Cantidad de usos",
    ],

    "loyalty_new_member_promotions" => [
        "promotion_name" => "Nombre de la promoción",
        "customers_joined" => "Clientes sumados",
        "bonus_points" => "Puntos bonus",
    ],

    "loyalty_free_items_cost" => [
        "period" => "Período",
        "product_name" => "Nombre del producto",
        "quantity" => "Cantidad",
        "cost_price" => "Precio de costo",
        "total_cost" => "Costo total",
    ],

    "loyalty_revenue_from_loyalty_customers" => [
        "period" => "Período",
        "revenue" => "Ingresos",
    ],

    "loyalty_revenue_before_after_loyalty" => [
        "period" => "Período",
        "revenue_before" => "Ingresos antes",
        "revenue_after" => "Ingresos después",
    ],

    "loyalty_average_order_value_loyalty_customers" => [
        "period" => "Período",
        "total_orders" => "Pedidos totales",
        "average_order_value" => "Valor promedio del pedido",
    ],

    "sales_by_cashier" => [
        "period" => "Período",
        "cashier" => "Cajero",
        "total_orders" => "Pedidos totales",
        "total_products" => "Productos totales",
        "subtotal" => "Subtotal",
        "tax" => "Impuesto",
        "total" => "Total",
    ],
];
