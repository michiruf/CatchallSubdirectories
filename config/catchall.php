<?php

return [

    'hostname' => env('CATCHALL_HOSTNAME', 'mail.example.com'),
    'port' => env('CATCHALL_PORT', 993),
    'username' => env('CATCHALL_USERNAME', 'user@example.com'),
    'password' => env('CATCHALL_PASSWORD'),
    'validate_cert' => env('CATCHALL_VALIDATE_CERT', true),
    'inbox_name' => env('CATCHALL_INBOX_NAME', 'INBOX'),
    'mail_domain' => env('CATCHALL_MAIL_DOMAIN', 'example.com'),

];
