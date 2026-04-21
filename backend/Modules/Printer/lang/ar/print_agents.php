<?php
return [
    "print_agent" => "وكيل الطباعة",
    "print_agents" => "وكلاء الطباعة",

    "table" => [
        "name" => "الاسم",
        "host" => "المضيف",
        "port" => "المنفذ",
    ],

    "form" => [
        "cards" => [
            "print_agent_information" => "معلومات وكيل الطباعة",
            "qintrix_integration" => "تكامل Qintrix",
        ]
    ],
    "validation" => [
        "host_without_scheme_or_path" => "يجب ألا يحتوي حقل المضيف على بروتوكول أو مسار.",
        "host_without_port" => "يجب ألا يحتوي حقل المضيف على منفذ.",
        "host_valid_hostname_or_ip" => "يجب أن يكون حقل المضيف اسم نطاق أو عنوان IP صالحًا بدون بروتوكول أو منفذ.",
    ],
];
