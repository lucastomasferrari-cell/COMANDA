<?php
return [
    "sales" => [
        "period" => "Period",
        "total_orders" => "Total Orders",
        "total_products" => "Total Products",
        "subtotal" => "Subtotal",
        "tax" => "Tax",
        "total" => "Total",
    ],

    "products_purchase" => [
        "period" => "Period",
        "product" => "Product",
        "quantity" => "Quantity",
        "total" => "Total",
    ],

    "tax" => [
        "period" => "Period",
        "tax_name" => "Tax Name",
        "total_orders" => "Total Orders",
        "total" => "Total",
    ],

    "branch_performance" => [
        "period" => "Period",
        "branch_name" => "Branch Name",
        "total_orders" => "Total Orders",
        "total" => "Total",
    ],

    "payments" => [
        "period" => "Period",
        "payment_method" => 'Payment Method',
        "total_paid" => "Total Paid",
        "total" => "Total",
    ],

    "discounts_and_vouchers" => [
        "period" => "Period",
        "discount" => "Discount / Voucher",
        "discount_type" => "Type",
        "total_orders" => "Total Orders",
        "total_discount" => "Total Discount",
    ],
    "gift_card_sales" => [
        "card_code" => "Card Code",
        "batch" => "Batch",
        "branch" => "Branch",
        "customer" => "Customer",
        "initial_balance" => "Initial Balance",
        "currency" => "Currency",
        "sold_by" => "Sold By",
        "sold_at" => "Sold At",
    ],
    "gift_card_redemption" => [
        "card_code" => "Card Code",
        "order_number" => "Order Number",
        "branch" => "Branch",
        "amount_redeemed" => "Amount Redeemed",
        "currency" => "Currency",
        "order_currency" => "Order Currency",
        "exchange_rate" => "Exchange Rate",
        "amount_converted" => "Amount Converted",
        "redeemed_by" => "Redeemed By",
        "redeemed_at" => "Redeemed At",
    ],
    "gift_card_outstanding_balance" => [
        "card_code" => "Card Code",
        "branch" => "Branch",
        "customer" => "Customer",
        "initial_balance" => "Initial Balance",
        "current_balance" => "Current Balance",
        "currency" => "Currency",
        "status" => "Status",
        "expiry_date" => "Expiry Date",
        "created_at" => "Created At",
    ],
    "gift_card_liability" => [
        "period" => "Period",
        "currency" => "Currency",
        "cards_sold" => "Cards Sold",
        "total_sold_value" => "Total Sold Value",
        "redeemed_value" => "Redeemed Value",
        "outstanding_balance" => "Outstanding Balance",
        "expired_balance" => "Expired Balance",
    ],
    "gift_card_expiry" => [
        "currency" => "Currency",
        "card_code" => "Card Code",
        "branch" => "Branch",
        "customer" => "Customer",
        "initial_balance" => "Initial Balance",
        "remaining_balance" => "Remaining Balance",
        "expiry_date" => "Expiry Date",
        "status" => "Status",
    ],
    "gift_card_transactions" => [
        "currency" => "Currency",
        "transaction_id" => "Transaction ID",
        "card_code" => "Card Code",
        "transaction_type" => "Transaction Type",
        "amount" => "Amount",
        "balance_before" => "Balance Before",
        "balance_after" => "Balance After",
        "branch" => "Branch",
        "order" => "Order",
        "created_by" => "Created By",
        "date" => "Date",
    ],
    "gift_card_branch_performance" => [
        "period" => "Period",
        "currency" => "Currency",
        "branch" => "Branch",
        "cards_sold" => "Cards Sold",
        "total_sold_value" => "Total Sold Value",
        "redeemed_value" => "Redeemed Value",
        "outstanding_balance" => "Outstanding Balance",
        "expired_balance" => "Expired Balance",
    ],
    "gift_card_batch" => [
        "batch_name" => "Batch Name",
        "branch" => "Branch",
        "currency" => "Currency",
        "cards_generated" => "Cards Generated",
        "total_value" => "Total Value",
        "cards_used" => "Cards Used",
        "cards_remaining" => "Cards Remaining",
        "created_by" => "Created By",
        "created_at" => "Created At",
    ],

    "product_tax" => [
        "period" => "Period",
        "tax_name" => "Tax Name",
        "product_name" => "Product Name",
        "total_products" => "Total Products",
        "total" => "Total",
    ],

    "ingredient_usage" => [
        "period" => "Period",
        "ingredient_name" => "Ingredient Name",
        "total_used" => "Total Used",
    ],

    "low_stock_alerts" => [
        "ingredient_name" => "Ingredient Name",
        "current_stock" => "Current Stock",
        "alert_quantity" => "Alert Quantity",
    ],

    "register_summary" => [
        "date" => "Date Range",
        'register_name' => "Register Name",
        'sessions_count' => "Sessions Count",
        'orders_count' => "Orders Count",
        'system_cash_sales' => "System Cash Sales",
        'system_card_sales' => "System Card Sales",
        'system_other_sales' => "System Other Sales",
        'total_sales' => "Total Sales",
        'total_refunds' => "Total Refunds",
    ],

    "cash_movement" => [
        "period" => "Period",
        'register_name' => "Register Name",
        "user_name" => "User",
        "reason" => "Reason",
        "direction" => "Direction",
        "amount" => "Amount",
    ],

    "sales_by_creator" => [
        "period" => "Period",
        "creator" => "Creator",
        "total_orders" => "Total Orders",
        "total_products" => "Total Products",
        "subtotal" => "Subtotal",
        "tax" => "Tax",
        "total" => "Total",
    ],

    "categorized_products" => [
        "category" => "Category",
        "products_count" => "Products Count",
    ],

    "upcoming_orders" => [
        "period" => "Period",
        "total_orders" => "Total Orders",
        "total_products" => "Total Products",
        "subtotal" => "Subtotal",
        "tax" => "Tax",
        "total" => "Total",
    ],

    "cost_and_revenue_by_order" => [
        "period" => "Period",
        "total_orders" => "Total Orders",
        "total_products" => "Total Products",
        "total_cost" => "Total Cost",
        "revenue" => "Revenue",
    ],

    "cost_and_revenue_by_product" => [
        "period" => "Period",
        "product" => "Product",
        "quantity" => "Quantity",
        "total_cost" => "Total Cost",
        "revenue" => "Revenue",
    ],

    "loyalty_program_summary" => [
        "period" => "Period",
        "program_name" => "Program Name",
        "total_customers" => "Total Customers",
        "total_earned_points" => "Total Earned Points",
        "total_redeemed_points" => "Total Redeemed Points",
        "total_expired_points" => "Total Expired Points",
    ],

    "loyalty_total_earned_points" => [
        "period" => "Period",
        "program_name" => "Program Name",
        "total_earned_points" => "Total Earned Points",
    ],

    "loyalty_total_redeemed_points" => [
        "period" => "Period",
        "program_name" => "Program Name",
        "total_redeemed_points" => "Total Redeemed Points",
    ],

    "loyalty_total_expired_points" => [
        "period" => "Period",
        "program_name" => "Program Name",
        "total_expired_points" => "Total Expired Points",
    ],

    "loyalty_system_points_balance" => [
        "period" => "Period",
        "program_name" => "Program Name",
        "total_active_customers" => "Total Active Customers",
        "total_points_balance" => "Total Points Balance",
    ],

    "loyalty_redemption_rate" => [
        "period" => "Period",
        "program_name" => "Program Name",
        "earned_points" => "Earned Points",
        "redeemed_points" => "Redeemed Points",
        "redemption_rate" => "Redemption Rate (%)",
    ],

    "loyalty_average_points_per_program" => [
        "period" => "Period",
        "program_name" => "Program Name",
        "total_customers" => "Total Customers",
        "average_points_balance" => "Average Points Balance",
    ],

    "loyalty_points_lifecycle_timeline" => [
        "date" => "Date",
        "earned_points" => "Earned Points",
        "redeemed_points" => "Redeemed Points",
        "expired_points" => "Expired Points",
    ],


    "loyalty_last_activity" => [
        "customer_name" => "Customer Name",
        "last_earned_date" => "Last Earned Date",
        "last_redeemed_date" => "Last Redeemed Date",
        "last_transaction_type" => "Last Transaction Type",
    ],

    "loyalty_inactive_customers" => [
        "customer_name" => "Customer Name",
        "last_activity_date" => "Last Activity Date",
        "days_inactive" => "Days Inactive",
    ],

    "loyalty_no_redemptions" => [
        "customer_name" => "Customer Name",
        "lifetime_points" => "Lifetime Points",
        "total_redemptions" => "Total Redemptions",
    ],

    "loyalty_top_customers_by_points" => [
        "customer_name" => "Customer Name",
        "lifetime_points" => "Lifetime Points",
        "points_balance" => "Current Balance",
    ],

    "loyalty_top_customers_by_spend" => [
        "customer_name" => "Customer Name",
        "total_spend" => "Total Spend",
        "total_orders" => "Total Orders",
    ],

    "loyalty_top_customers_by_orders" => [
        "customer_name" => "Customer Name",
        "total_orders" => "Total Orders",
        "average_order_value" => "Average Order Value",
    ],

    "loyalty_tier_customer_distribution" => [
        "period" => "Period",
        "tier_name" => "Tier Name",
        "customers_count" => "Customers Count",
    ],


    "loyalty_tier_redemption_rate" => [
        "period" => "Period",
        "tier_name" => "Tier Name",
        "earned_points" => "Earned Points",
        "redeemed_points" => "Redeemed Points",
        "redemption_rate" => "Redemption Rate (%)",
    ],

    "loyalty_most_redeemed_rewards" => [
        "period" => "Period",
        "reward_name" => "Reward Name",
        "reward_type" => "Reward Type",
        "total_redemptions" => "Total Redemptions",
        "total_points_spent" => "Total Points Spent",
    ],

    "loyalty_least_used_rewards" => [
        "period" => "Period",
        "reward_name" => "Reward Name",
        "total_redemptions" => "Total Redemptions",
    ],

    "loyalty_never_redeemed_rewards" => [
        "reward_name" => "Reward Name",
        "points_cost" => "Points Cost",
        "created_date" => "Created Date",
    ],

    "loyalty_rewards_by_type" => [
        "reward_type" => "Reward Type",
        "total_rewards" => "Total Rewards",
        "total_redemptions" => "Total Redemptions",
    ],

    "loyalty_rewards_by_tier" => [
        "period" => "Period",
        "tier_name" => "Tier Name",
        "reward_name" => "Reward Name",
        "points_cost" => "Points Cost",
    ],

    "loyalty_rewards_by_program" => [
        "period" => "Period",
        "program_name" => "Program Name",
        "reward_name" => "Reward Name",
        "reward_type" => "Reward Type",
    ],

    "loyalty_available_gifts" => [
        "gift_id" => "Gift ID",
        "customer_name" => "Customer Name",
        "reward_name" => "Reward Name",
        "valid_until" => "Valid Until",
        "valid_from" => "Valid From",
    ],

    "loyalty_used_gifts" => [
        "gift_id" => "Gift ID",
        "customer_name" => "Customer Name",
        "reward_name" => "Reward Name",
        "used_date" => "Used Date",
    ],

    "loyalty_expired_gifts" => [
        "gift_id" => "Gift ID",
        "customer_name" => "Customer Name",
        "reward_name" => "Reward Name",
        "expiration_date" => "Expiration Date",
    ],

    "loyalty_gift_usage_rate" => [
        "reward_name" => "Reward Name",
        "issued_count" => "Issued Count",
        "used_count" => "Used Count",
        "usage_rate" => "Usage Rate (%)",
    ],

    "loyalty_unused_gifts_per_customer" => [
        "customer_name" => "Customer Name",
        "unused_gifts_count" => "Unused Gifts Count",
    ],

    "loyalty_gifts_linked_to_orders" => [
        "order_id" => "Order ID",
        "gift_id" => "Gift ID",
        "reward_name" => "Reward Name",
        "discount_value" => "Discount Value",
    ],

    "loyalty_redemptions_by_status" => [
        "period" => "Period",
        "status" => "Status",
        "total_redemptions" => "Total Redemptions",
        "total_points" => "Total Points",
    ],

    "loyalty_redemptions_by_program" => [
        "period" => "Period",
        "program_name" => "Program Name",
        "total_redemptions" => "Total Redemptions",
        "total_points" => "Total Points",
    ],

    "loyalty_average_points_per_redemption" => [
        "period" => "Period",
        "program_name" => "Program Name",
        "average_points" => "Average Points",
    ],

    "loyalty_active_promotions" => [
        "promotion_name" => "Promotion Name",
        "promotion_type" => "Promotion Type",
        "usage_count" => "Usage Count",
        "start_date" => "Start Date",
        "end_date" => "End Date",
    ],

    "loyalty_expired_promotions" => [
        "promotion_name" => "Promotion Name",
        "promotion_type" => "Promotion Type",
        "end_date" => "End Date",
    ],

    "loyalty_promotion_usage" => [
        "promotion_name" => "Promotion Name",
        "total_usage" => "Total Usage",
        "total_customers" => "Total Customers",
    ],

    "loyalty_highest_impact_promotions" => [
        "promotion_name" => "Promotion Name",
        "total_points_generated" => "Total Points Generated",
    ],

    "loyalty_bonus_vs_multiplier_comparison" => [
        "promotion_type" => "Promotion Type",
        "total_promotions" => "Total Promotions",
        "total_usage" => "Total Usage",
    ],

    "loyalty_category_boost_promotions" => [
        "promotion_name" => "Promotion Name",
        "usage_count" => "Usage Count",
    ],

    "loyalty_new_member_promotions" => [
        "promotion_name" => "Promotion Name",
        "customers_joined" => "Customers Joined",
        "bonus_points" => "Bonus Points",
    ],

    "loyalty_free_items_cost" => [
        "period" => "Period",
        "product_name" => "Product Name",
        "quantity" => "Quantity",
        "cost_price" => "Cost Price",
        "total_cost" => "Total Cost",
    ],

    "loyalty_revenue_from_loyalty_customers" => [
        "period" => "Period",
        "revenue" => "Revenue",
    ],

    "loyalty_revenue_before_after_loyalty" => [
        "period" => "Period",
        "revenue_before" => "Revenue Before",
        "revenue_after" => "Revenue After",
    ],

    "loyalty_average_order_value_loyalty_customers" => [
        "period" => "Period",
        "total_orders" => "Total Orders",
        "average_order_value" => "Average Order Value",
    ],

    "sales_by_cashier" => [
        "period" => "Period",
        "cashier" => "Cashier",
        "total_orders" => "Total Orders",
        "total_products" => "Total Products",
        "subtotal" => "Subtotal",
        "tax" => "Tax",
        "total" => "Total",
    ],
];
