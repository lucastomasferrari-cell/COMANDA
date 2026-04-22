<?php
return [
    "reports" => "Reportes",
    "report" => "Reporte",

    "filters" => [
        "start_date" => "Fecha de inicio",
        "end_date" => "Fecha de fin",
        "order_status" => "Estado del pedido",
        "order_type" => "Canal de venta",
        "payment_status" => "Estado del pago",
        "product_name" => "Nombre del producto",
        "payment_method" => "Método de pago",
        "sold_by" => "Vendido por",
        "cashier" => "Cajero",
        "card_code" => "Código de tarjeta",
        "user" => "Usuario",
        "expiry_from" => "Vencimiento desde",
        "expiry_to" => "Vencimiento hasta",
        "reason" => "Motivo",
        "direction" => "Dirección",
        "menu" => "Menú",
        "discount_type" => "Tipo de descuento",
        "created_by" => "Creado por",
    ],

    "groups" => [
        "restaurant_sales_reports" => "Reportes de ventas del restaurante",
        "inventory_reports" => "Reportes de inventario",
        "pos_reports" => "Reportes de caja",
        "gift_card_reports" => "Reportes de tarjetas de regalo",
        "system_reports" => "Reportes del sistema",
        "loyalty_overview_reports" => "Reportes generales de fidelización",
        "loyalty_customer_reports" => "Reportes de clientes de fidelización",
        "loyalty_tier_reports" => "Reportes de niveles de fidelización",
        "loyalty_rewards_reports" => "Reportes de recompensas de fidelización",
        "loyalty_gifts_reports" => "Reportes de regalos de fidelización",
        "loyalty_redemptions_reports" => "Reportes de canjes de fidelización",
        "loyalty_promotions_reports" => "Reportes de promociones de fidelización",
        "loyalty_financial_roi_reports" => "Reportes financieros y ROI de fidelización"
    ],

    "definitions" => [
        "sales" => [
            "title" => "Reporte de ventas",
            "description" => "Detalle del rendimiento de ventas dentro de un rango de tiempo seleccionado.",
        ],

        "products_purchase" => [
            "title" => "Reporte de compras de productos",
            "description" => "Este reporte ayuda a analizar el rendimiento de la adquisición de productos en distintos períodos"
        ],

        "tax" => [
            "title" => "Reporte de impuestos",
            "description" => "Resume los impuestos recaudados por estado del pedido, tipo y pago durante un período seleccionado."
        ],

        "branch_performance" => [
            "title" => "Reporte de rendimiento por sucursal",
            "description" => "Muestra métricas clave de ventas y pedidos de cada sucursal para comparar el rendimiento a lo largo del tiempo."
        ],

        "payments" => [
            "title" => "Reporte de pagos",
            "description" => "Desglosa los pagos recibidos por método para hacer seguimiento del cobro de ingresos."
        ],

        "discounts_and_vouchers" => [
            "title" => "Reporte de descuentos y cupones",
            "description" => "Muestra cómo se canjearon los descuentos y cupones, incluidos los conteos de uso y los montos totales descontados.",
        ],
        "gift_card_sales" => [
            "title" => "Reporte de ventas de tarjetas de regalo",
            "description" => "Muestra todas las tarjetas de regalo vendidas dentro de un período seleccionado.",
        ],
        "gift_card_redemption" => [
            "title" => "Reporte de canje de tarjetas de regalo",
            "description" => "Muestra todas las tarjetas de regalo usadas como pago en pedidos.",
        ],
        "gift_card_outstanding_balance" => [
            "title" => "Reporte de saldo pendiente de tarjetas de regalo",
            "description" => "Muestra todas las tarjetas de regalo activas y sus saldos restantes.",
        ],
        "gift_card_liability" => [
            "title" => "Reporte de pasivo de tarjetas de regalo",
            "description" => "Reporte financiero que muestra el pasivo restante de las tarjetas de regalo.",
        ],
        "gift_card_expiry" => [
            "title" => "Reporte de vencimiento de tarjetas de regalo",
            "description" => "Lista las tarjetas de regalo próximas a vencer o ya vencidas.",
        ],
        "gift_card_transactions" => [
            "title" => "Reporte de transacciones de tarjetas de regalo",
            "description" => "Muestra todas las transacciones relacionadas con tarjetas de regalo.",
        ],
        "gift_card_branch_performance" => [
            "title" => "Reporte de rendimiento de tarjetas de regalo por sucursal",
            "description" => "Compara la actividad de tarjetas de regalo entre sucursales.",
        ],
        "gift_card_batch" => [
            "title" => "Reporte de lotes de tarjetas de regalo",
            "description" => "Muestra estadísticas de los lotes de tarjetas de regalo generados por el sistema.",
        ],

        "product_tax" => [
            "title" => "Reporte de impuestos por producto",
            "description" => "Muestra los montos totales de impuestos aplicados a productos específicos dentro de un período seleccionado."
        ],

        "ingredient_usage" => [
            "title" => "Reporte de uso de ingredientes",
            "description" => "Lleva el seguimiento de la cantidad total de cada ingrediente utilizado en base a los productos vendidos durante un período seleccionado"
        ],

        "low_stock_alerts" => [
            "title" => "Alertas de stock bajo",
            "description" => "Identifica los ingredientes próximos a agotarse o ya agotados para permitir una reposición oportuna."
        ],

        "register_summary" => [
            "title" => "Reporte de resumen de caja",
            "description" => "Brinda un resumen financiero de las cajas, incluyendo sesiones, ventas, movimientos de efectivo y saldos."
        ],

        "cash_movement" => [
            "title" => "Reporte de movimiento de efectivo",
            "description" => "Registra todas las transacciones de entrada y salida de efectivo en las cajas, incluyendo usuarios y motivos."
        ],

        "sales_by_creator" => [
            "title" => "Reporte de ventas por creador",
            "description" => "Muestra las ventas totales agrupadas por el usuario que creó cada pedido, ayudando a hacer seguimiento del rendimiento individual de los empleados."
        ],

        "sales_by_cashier" => [
            "title" => "Reporte de ventas por cajero",
            "description" => "Resume las ventas totales gestionadas por cada cajero, excluyendo pedidos cancelados y reintegrados."
        ],

        "categorized_products" => [
            "title" => "Reporte de productos categorizados",
            "description" => "Muestra cada categoría con su cantidad total de productos — ideal para revisar la distribución de productos."
        ],
        "upcoming_orders" => [
            "title" => "Reporte de pedidos próximos",
            "description" => "Muestra los pedidos programados para ser preparados o servidos en un momento futuro."
        ],

        "cost_and_revenue_by_order" => [
            "title" => "Reporte de costo e ingresos por pedido",
            "description" => "Muestra el costo total de cada pedido (en base a los costos de los productos), los ingresos totales (ventas) y la ganancia."
        ],

        "cost_and_revenue_by_product" => [
            "title" => "Reporte de costo e ingresos por producto",
            "description" => "Brinda un panorama del rendimiento de los productos en términos de cantidad vendida, costo e ingresos."
        ],

        "loyalty_program_summary" => [
            "title" => "Resumen del programa de fidelización",
            "description" => "Panorama de cada programa de fidelización con la cantidad de clientes y los totales del ciclo de vida de los puntos.",
        ],

        "loyalty_total_earned_points" => [
            "title" => "Puntos ganados totales",
            "description" => "Puntos de fidelización ganados totales dentro del período seleccionado.",
        ],

        "loyalty_total_redeemed_points" => [
            "title" => "Puntos canjeados totales",
            "description" => "Puntos de fidelización canjeados totales dentro del período seleccionado.",
        ],

        "loyalty_total_expired_points" => [
            "title" => "Puntos vencidos totales",
            "description" => "Puntos de fidelización totales que vencieron sin ser canjeados.",
        ],

        "loyalty_system_points_balance" => [
            "title" => "Saldo de puntos del sistema",
            "description" => "Saldo actual total de puntos entre los clientes activos de fidelización.",
        ],

        "loyalty_redemption_rate" => [
            "title" => "Tasa de canje",
            "description" => "Proporción de puntos canjeados respecto a puntos ganados por programa.",
        ],

        "loyalty_average_points_per_program" => [
            "title" => "Promedio de puntos por programa",
            "description" => "Saldo promedio de puntos para los clientes de cada programa.",
        ],

        "loyalty_points_lifecycle_timeline" => [
            "title" => "Línea de tiempo del ciclo de vida de los puntos",
            "description" => "Puntos ganados vs. canjeados vs. vencidos por día en los programas de fidelización.",
        ],

        "loyalty_last_activity" => [
            "title" => "Última actividad de fidelización",
            "description" => "Última actividad de fidelización por cliente con las fechas de la última ganancia/canje.",
        ],

        "loyalty_inactive_customers" => [
            "title" => "Clientes de fidelización inactivos",
            "description" => "Clientes sin actividad de fidelización reciente y su duración de inactividad.",
        ],

        "loyalty_no_redemptions" => [
            "title" => "Clientes sin canjes",
            "description" => "Clientes que nunca canjearon puntos de fidelización.",
        ],

        "loyalty_top_customers_by_points" => [
            "title" => "Mejores clientes por puntos",
            "description" => "Clientes rankeados por puntos acumulados y saldo actual.",
        ],

        "loyalty_top_customers_by_spend" => [
            "title" => "Mejores clientes por gasto",
            "description" => "Clientes de fidelización rankeados por gasto total y pedidos.",
        ],

        "loyalty_top_customers_by_orders" => [
            "title" => "Mejores clientes por pedidos",
            "description" => "Clientes de fidelización rankeados por pedidos totales y valor promedio del pedido.",
        ],

        "loyalty_tier_customer_distribution" => [
            "title" => "Distribución de clientes por nivel",
            "description" => "Cómo se distribuyen los clientes de fidelización entre los niveles.",
        ],

        "loyalty_tier_redemption_rate" => [
            "title" => "Tasa de canje por nivel",
            "description" => "Puntos ganados vs. canjeados segmentados por nivel.",
        ],

        "loyalty_most_redeemed_rewards" => [
            "title" => "Recompensas más canjeadas",
            "description" => "Recompensas con la mayor cantidad de canjes.",
        ],

        "loyalty_least_used_rewards" => [
            "title" => "Recompensas menos usadas",
            "description" => "Recompensas con cantidades mínimas de canje.",
        ],

        "loyalty_never_redeemed_rewards" => [
            "title" => "Recompensas nunca canjeadas",
            "description" => "Recompensas que nunca fueron canjeadas.",
        ],

        "loyalty_rewards_by_type" => [
            "title" => "Recompensas por tipo",
            "description" => "Recompensas agrupadas por tipo con totales de canje.",
        ],

        "loyalty_rewards_by_tier" => [
            "title" => "Recompensas por nivel",
            "description" => "Recompensas disponibles por nivel de fidelización.",
        ],

        "loyalty_rewards_by_program" => [
            "title" => "Recompensas por programa",
            "description" => "Recompensas agrupadas por programa de fidelización.",
        ],

        "loyalty_available_gifts" => [
            "title" => "Regalos disponibles",
            "description" => "Regalos que están disponibles para usar.",
        ],

        "loyalty_used_gifts" => [
            "title" => "Regalos usados",
            "description" => "Regalos que fueron canjeados.",
        ],

        "loyalty_expired_gifts" => [
            "title" => "Regalos vencidos",
            "description" => "Regalos que vencieron sin usarse.",
        ],

        "loyalty_gift_usage_rate" => [
            "title" => "Tasa de uso de regalos",
            "description" => "Tasa de uso de los regalos emitidos.",
        ],

        "loyalty_unused_gifts_per_customer" => [
            "title" => "Regalos no usados por cliente",
            "description" => "Regalos no usados agrupados por cliente.",
        ],

        "loyalty_redemptions_by_status" => [
            "title" => "Canjes por estado",
            "description" => "Canjes agrupados por estado.",
        ],

        "loyalty_redemptions_by_program" => [
            "title" => "Canjes por programa",
            "description" => "Canjes agrupados por programa de fidelización.",
        ],

        "loyalty_average_points_per_redemption" => [
            "title" => "Promedio de puntos por canje",
            "description" => "Promedio de puntos gastados por canje.",
        ],

        "loyalty_active_promotions" => [
            "title" => "Promociones activas",
            "description" => "Promociones actualmente activas.",
        ],

        "loyalty_expired_promotions" => [
            "title" => "Promociones vencidas",
            "description" => "Promociones que finalizaron.",
        ],

        "loyalty_promotion_usage" => [
            "title" => "Uso de promociones",
            "description" => "Cantidad de usos por promoción.",
        ],

        "loyalty_highest_impact_promotions" => [
            "title" => "Promociones de mayor impacto",
            "description" => "Promociones con mayor cantidad de puntos generados.",
        ],

        "loyalty_bonus_vs_multiplier_comparison" => [
            "title" => "Comparación bonus vs. multiplicador",
            "description" => "Compara promociones de bonus y multiplicador.",
        ],

        "loyalty_category_boost_promotions" => [
            "title" => "Promociones de boost por categoría",
            "description" => "Promociones que potencian categorías específicas.",
        ],

        "loyalty_new_member_promotions" => [
            "title" => "Promociones para miembros nuevos",
            "description" => "Promociones para nuevos miembros de fidelización.",
        ],

        "loyalty_free_items_cost" => [
            "title" => "Costo de ítems gratis",
            "description" => "Costo de los ítems gratis emitidos.",
        ],

        "loyalty_revenue_from_loyalty_customers" => [
            "title" => "Ingresos de clientes de fidelización",
            "description" => "Ingresos generados por los clientes de fidelización.",
        ],

        "loyalty_revenue_before_after_loyalty" => [
            "title" => "Ingresos antes vs. después de fidelización",
            "description" => "Comparación de ingresos entre pedidos con y sin fidelización.",
        ],

        "loyalty_average_order_value_loyalty_customers" => [
            "title" => "Valor promedio del pedido (clientes de fidelización)",
            "description" => "Valor promedio del pedido para los clientes de fidelización.",
        ],
    ],
    "export_failed" => "La exportación falló. Por favor intentalo nuevamente más tarde."
];
