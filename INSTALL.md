# Install

- `composer require differentdevelopment/patent-oauth-client:dev-main`
- Felvenni egy új klienst a login.patentapp.eu-n: `php artisan passport:client`
- .env fájl frissítése:
```
PATENT_OAUTH_PREFIX=""
PATENT_OAUTH_SERVER_URI="https://login.patentapp.eu"
PATENT_OAUTH_CLIENT_ID="96a81c77-....-....-....-7cd9bd7becc6"
PATENT_OAUTH_CLIENT_SECRET="50Lps9UF6C.........HwWPcufVKtnX8vs1cOQ8J"
PATENT_OAUTH_REDIRECT_URI="https://redirect.patentapp.eu/callback"
PATENT_OAUTH_REDIRECT_AFTER_LOGIN_URI="/admin/dashboard"
PATENT_OAUTH_PGC_CLIENT_ID=""
PATENT_OAUTH_PGC_CLIENT_SECRET=""
```
- routes/backpack/custom.php módosítása:
```
Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array)config('backpack.base.web_middleware', 'web'),
        (array)config('backpack.base.middleware_key', 'admin'),
        [
            ...
            \Different\PatentOAuthClient\app\Middleware\PasCookieMiddleware::class,
            ....
        ],
    ),
],
```
- config/backpack/base.php - `'setup_auth_routes' => false,`
- config/backpack/base.php - `'guard' => 'web',`
- `php artisan migrate`
