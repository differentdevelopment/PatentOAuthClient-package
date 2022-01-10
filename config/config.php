<?php

return [
    'prefix' => config("backpack.base.route_prefix") ?? env('PATENT_OAUTH_PREFIX', ''),
    'server_uri' => env('PATENT_OAUTH_SERVER_URI'),
    'client_id' => env('PATENT_OAUTH_CLIENT_ID'),
    'client_secret' => env('PATENT_OAUTH_CLIENT_SECRET'),
    'client_redirect_uri' => env('PATENT_OAUTH_REDIRECT_URI'),
    'redirect_after_login_uri' => env('PATENT_OAUTH_REDIRECT_AFTER_LOGIN_URI'),
];
