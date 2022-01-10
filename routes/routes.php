<?php

use Illuminate\Support\Facades\Route;
use Different\PatentOAuthClient\PatentOAuthClient;

Route::group([
    'prefix' => '',
    'as' => 'patent-oauth-client',
    'middleware' => [
        'web'
    ],
], function () {
    Route::get('/callback', [PatentOAuthClient::class, 'callbackFromOAuthServer'])->name('.callback');

    Route::group([
        'prefix' => config('patent-oauth-client.prefix', ""),
    ], function () {
        Route::get('/', [PatentOAuthClient::class, 'login'])->name('.index');
        Route::get('/login', [PatentOAuthClient::class, 'login'])->name('.login');
        Route::get('/redirect-to-login', [PatentOAuthClient::class, 'redirectToOAuthServer'])->name('.redirect');
        Route::get('/logout', [PatentOAuthClient::class, 'logout'])->name('.logout');
    });
});
