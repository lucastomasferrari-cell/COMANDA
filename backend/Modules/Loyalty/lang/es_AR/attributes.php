<?php

return [
    "loyalty_programs" => [
        "name" => "Nombre",
        "earning_rate" => "Tasa de acumulación",
        "redemption_rate" => "Tasa de canje",
        "min_redeem_points" => "Puntos mínimos para canje",
        "points_expire_after" => "Los puntos vencen después de",
        "is_active" => "Activo",
    ],

    "loyalty_tiers" => [
        "name" => 'Nombre',
        "loyalty_program_id" => "Programa",
        "min_spend" => "Consumo mínimo",
        "multiplier" => "Multiplicador",
        "benefits" => "Beneficios",
        "order" => "Orden",
        "is_active" => "Activo",
        "icon" => "Ícono",
    ],

    "loyalty_rewards" => [
        "name" => "Nombre",
        "description" => "Descripción",
        "loyalty_program_id" => "Programa",
        "loyalty_tier_id" => "Nivel",
        "type" => "Tipo",
        "points_cost" => "Costo en puntos",
        "value" => "Valor",
        "value_type" => "Tipo de valor",
        "max_redemptions_per_order" => "Canjes máximos por pedido",
        "usage_limit" => "Límite de uso",
        "per_customer_limit" => "Límite por cliente",
        "conditions" => "Condiciones",
        "conditions.min_spend" => "Consumo mínimo",
        "conditions.branch_ids" => "Sucursales",
        "conditions.branch_ids.*" => "Sucursal",
        "conditions.available_days" => "Días disponibles",
        "conditions.available_days.*" => "Día disponible",
        "starts_at" => "Inicia el",
        "ends_at" => "Finaliza el",
        "is_active" => "Activo",
        "files.icon" => "Ícono",
        "meta" => "Metadatos",
        "meta.discount_type" => "Tipo de descuento",
        "meta.value" => "Valor del descuento",
        "meta.min_order_total" => "Total mínimo del pedido",
        "meta.max_order_total" => "Total máximo del pedido",
        "meta.max_discount" => "Descuento máximo",
        "meta.expires_in_days" => "Vence en días",
        "meta.code_prefix" => "Prefijo del código",
        "meta.product_sku" => "Producto",
        "meta.quantity" => "Cantidad",
        "meta.target_tier" => "Nivel objetivo",
    ],

    "loyalty_promotions" => [
        "name" => "Nombre",
        "description" => "Descripción",
        "loyalty_program_id" => "Programa",
        "type" => "Tipo",
        "usage_limit" => "Límite de uso",
        "per_customer_limit" => "Límite por cliente",
        "conditions" => "Condiciones",
        "conditions.min_spend" => "Consumo mínimo",
        "conditions.branch_ids" => "Sucursales",
        "conditions.branch_ids.*" => "Sucursal",
        "conditions.available_days" => "Días disponibles",
        "conditions.available_days.*" => "Día disponible",
        "multiplier" => "Multiplicador",
        "bonus_points" => "Puntos bonus",
        "conditions.valid_days" => "Días válidos",
        "starts_at" => "Inicia el",
        "ends_at" => "Finaliza el",
        "is_active" => "Activo",
        'conditions.categories' => 'Categorías',
        'conditions.categories.*' => 'Categoría',
    ],

    "get_rewards" => [
        "customer_id" => "Cliente",
    ],

    "redeem_rewards" => [
        "customer_id" => "Cliente",
    ],

    "available_gifts" => [
        "customer_id" => "Cliente",
    ],

];
