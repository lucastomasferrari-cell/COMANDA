<?php
return [
    "print_agent" => "Print Agent",
    "print_agents" => "Print Agents",

    "table" => [
        "name" => "Name",
        "host" => "Host",
        "port" => "Port",
    ],

    "form" => [
        "cards" => [
            "print_agent_information" => "Print Agent information",
            "qintrix_integration" => "Qintrix Integration",
        ]
    ],
    "validation" => [
        "host_without_scheme_or_path" => "The host field must not include a scheme or path.",
        "host_without_port" => "The host field must not include a port.",
        "host_valid_hostname_or_ip" => "The host field must be a valid hostname or IP address without scheme or port.",
    ],
];
