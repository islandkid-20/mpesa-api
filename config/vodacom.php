<?php


return [
    "SHORT_CODE" => env("MPESA_SHORT_CODE"),
    "mpesa_auth" => [
        "API_KEY" => env("MPESA_API_KEY"),
        "PUBLIC_KEY" => env("MPESA_PUBLIC_KEY"),
        "SESSION_URL" => env("MPESA_SESSION_URL")
    ],
    "payments" => [
        "C2B_URL" => env("MPESA_C2B_URL"),
        
    ]
];