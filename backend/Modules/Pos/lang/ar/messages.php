<?php

return [
    "session_already_opened" => "هذا السجل يحتوي بالفعل على جلسة مفتوحة. يرجى إغلاقها قبل فتح جلسة جديدة.",
    "session_closed" => "تم إغلاق جلسة نقطة البيع بالفعل.",
    "success_opened_session" => "تم فتح جلسة نقطة البيع بنجاح. يمكنك الآن البدء في معالجة الطلبات والمدفوعات.",
    "success_closed_session" => "تم إغلاق جلسة نقطة البيع بنجاح. تم تسجيل جميع المبيعات والمعاملات.",
    "amount_must_be_positive" => "يجب أن يكون المبلغ رقماً موجباً.",
    "invalid_payment_method" => "يمكن استخدام كل طريقة دفع مرة واحدة فقط.",
    "no_active_session" => "لا يمكنك تنفيذ :action لأنه لا توجد جلسة نقطة بيع نشطة.",
    "cash_payment" => "دفعة نقدية",
    "cash_over_short_note_over" => "زيادة: تم التصريح بمبلغ نقدي أكبر من المتوقع",
    "cash_over_short_note_short" => "نقص: تم التصريح بمبلغ نقدي أقل من المتوقع",
    "cannot_close_session_cash_difference" => "لا يمكن إغلاق الجلسة: النقد المصرّح به (:declared) مقابل المتوقع (:expected). الفرق: :difference يتجاوز الحد المسموح به: :threshold.",
    "menu_is_not_active" => "ليس لديك أي قائمة نشطة",
    "session_close_blocked_by_open_orders" => "لا يمكن إغلاق الصندوق: يوجد {count} طلبات مفتوحة. حلّها قبل الإغلاق.",
    "session_close_justification_required" => "الفرق النقدي ({diff}) يتطلب تبريرًا (20 حرفًا كحد أدنى).",
    "session_close_manager_required" => "الفرق ({diff}) يتجاوز حد المدير ({threshold}). مطلوب تصريح.",
    "session_close_token_invalid" => "رمز التصريح غير صالح أو انتهت صلاحيته.",
    "session_close_approver_lacks_permission" => "المستخدم المصرّح لا يملك صلاحية إغلاق الصندوق مع فرق.",
];
