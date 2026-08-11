<?php

declare(strict_types=1);

return [
    'env' => getenv('APP_ENV') ?: 'production',
    'url' => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Jakarta',
    'upload_max_size' => (int) (getenv('UPLOAD_MAX_SIZE') ?: 2097152),
    'default_per_page' => (int) (getenv('DEFAULT_PER_PAGE') ?: 5),
    'max_per_page' => (int) (getenv('MAX_PER_PAGE') ?: 50),
    'google_client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
    'google_client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
    'google_redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: '',
    'admin_emails' => getenv('ADMIN_EMAILS') ?: '',
];
