# Új kliens

Digital Ocean -> PatentStuff -> patent-auth-server -> Jobb felső sarok `console` gomb -> login.patentapp.eu mappa megnyitása majd:
> php artisan passport:client

Amikor kérdezi, hogy melyik user-hez kösse a klienst akkor csak Enter-t kell nyomni üresen!

Fontos: a https://login.patentapp.eu/admin Kliensek oldalon állítsuk be elsődlegesre az új klienst!

# Projekt (kliens) beállítások
Első lépés: csomag feltelepítése composerrel

env:
```
PATENT_OAUTH_PREFIX=""
PATENT_OAUTH_SERVER_URI="https://login.patentapp.eu"
PATENT_OAUTH_CLIENT_ID="96a8203a-XXXX-XXXX-XXXX-c397fe0f3aec"
PATENT_OAUTH_CLIENT_SECRET="XXXXEt5UY2TtlKH5RyuEE53AWjC8sKV2pCsiXXXX"
PATENT_OAUTH_REDIRECT_URI="https://log.patentapp.eu/callback"
PATENT_OAUTH_REDIRECT_AFTER_LOGIN_URI="/admin/dashboard"
PATENT_OAUTH_PGC_CLIENT_ID=""
PATENT_OAUTH_PGC_CLIENT_SECRET=""
```

config/backpack.base.php:
```
'setup_auth_routes' => false,
```

> php artisan migrate
