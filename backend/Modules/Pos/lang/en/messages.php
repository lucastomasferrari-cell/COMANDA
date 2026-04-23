<?php

return [
    "session_already_opened" => "This register already has an open session. Close it before opening a new one.",
    "session_closed" => "This POS session is already closed.",
    "success_opened_session" => "POS session opened successfully. You can now start processing orders and payments.",
    "success_closed_session" => "POS session closed successfully. All sales and transactions have been recorded.",
    "amount_must_be_positive" => "Amount must be positive.",
    "invalid_payment_method" => "Each payment method can only be used once.",
    "no_active_session" => "You cannot perform :action because there is no active POS session.",
    "cash_payment" => "a cash payment",
    "cash_over_short_note_over" => 'Over: declared more cash than expected',
    "cash_over_short_note_short" => 'Short: declared less cash than expected',
    "cannot_close_session_cash_difference" => "Cannot close session: Declared cash (:declared) vs Expected (:expected). Difference: :difference exceeds threshold: :threshold.",
    "menu_is_not_active" => "You no have any menu active",
    "session_close_blocked_by_open_orders" => "Cannot close drawer: {count} open orders remaining. Resolve them before closing.",
    "session_close_justification_required" => "Cash difference ({diff}) requires a justification (min 20 characters).",
    "session_close_manager_required" => "Difference ({diff}) exceeds manager threshold ({threshold}). Approval required.",
    "session_close_token_invalid" => "The approval token is invalid or expired.",
    "session_close_approver_lacks_permission" => "The approver lacks permission to close the drawer with a difference.",
];
