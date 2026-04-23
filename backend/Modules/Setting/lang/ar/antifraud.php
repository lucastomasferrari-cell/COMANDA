<?php

return [
    "title" => "مكافحة الاحتيال",
    "subtitle" => "الحدود، تقارير المالك وسياسة الموافقة.",
    "test_sent" => "تم إرسال تقرير الاختبار.",
    "test_failed" => "تعذّر إرسال تقرير الاختبار.",
    "sections" => [
        "discount_thresholds" => "حدود الخصم",
        "open_item" => "صنف حر",
        "session_close" => "إغلاق الصندوق",
        "daily_report" => "تقرير يومي بالبريد",
        "pending_policy" => "سياسة الموافقات المعلقة",
    ],
    "fields" => [
        "discount_cashier_max" => "الحد الأقصى للكاشير %",
        "discount_manager_max" => "الحد الأقصى للمدير %",
        "discount_explainer" => "الخصومات فوق حد المدير تتطلب موافقة الأدمن.",
        "open_item_max_per_shift" => "الحد الأقصى للعدد لكل وردية",
        "open_item_max_amount_each" => "الحد الأقصى لمبلغ الصنف",
        "open_item_max_total_per_shift" => "الحد الأقصى التراكمي لكل وردية",
        "session_close_justification_threshold" => "أقصى فرق بدون تبرير",
        "session_close_manager_required_percent" => "أقصى فرق بدون مدير (% من المتوقع)",
        "daily_report_enabled" => "إرسال بريد يومي للمالك",
        "owner_alert_email" => "بريد المستلم",
        "daily_report_hour" => "ساعة الإرسال (0-23)",
        "daily_report_hour_hint" => "ساعة اليوم بنظام 24. الافتراضي 6 صباحًا.",
        "send_test_now" => "إرسال تقرير اختبار الآن",
        "allow_pending_without_manager" => "السماح بالتنفيذ بدون موافقة (يسجَّل معلقًا)",
        "allow_pending_explainer" => "إن كانت معطلة (افتراضي)، الإجراءات الحساسة تتطلب رمز مدير أو ترجع خطأ. إن فُعِّلت وبدون مدير متاح، ينفَّذ الإجراء ويوسَم للمراجعة من الأدمن.",
    ],
];
