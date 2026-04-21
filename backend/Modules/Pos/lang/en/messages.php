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
];
