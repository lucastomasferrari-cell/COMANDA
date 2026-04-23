<?php

return [
    "title" => "Anti-fraud",
    "subtitle" => "Thresholds, owner reports and approval policy.",
    "test_sent" => "Test report sent.",
    "test_failed" => "Could not send test report.",
    "sections" => [
        "discount_thresholds" => "Discount thresholds",
        "open_item" => "Open item",
        "session_close" => "Cash drawer close",
        "daily_report" => "Daily email report",
        "pending_policy" => "Pending approvals policy",
    ],
    "fields" => [
        "discount_cashier_max" => "Cashier max %",
        "discount_manager_max" => "Manager max %",
        "discount_explainer" => "Discounts above manager max require admin approval.",
        "open_item_max_per_shift" => "Max count per shift",
        "open_item_max_amount_each" => "Max amount per item",
        "open_item_max_total_per_shift" => "Max accumulated per shift",
        "session_close_justification_threshold" => "Max difference without justification",
        "session_close_manager_required_percent" => "Max difference without manager (% of expected)",
        "daily_report_enabled" => "Send daily email to owner",
        "owner_alert_email" => "Recipient email",
        "daily_report_hour" => "Send hour (0-23)",
        "daily_report_hour_hint" => "Hour of day in 24h. Default 6 AM.",
        "send_test_now" => "Send test report now",
        "allow_pending_without_manager" => "Allow execution without approval (recorded as pending)",
        "allow_pending_explainer" => "If off (default), sensitive actions require manager PIN or return error. If on, when no manager available the action executes and is flagged for admin review.",
    ],
];
