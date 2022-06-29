<?php

namespace Different\PatentOAuthClient;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PatentOAuthClient
{
    public function redirectToOAuthServer(Request $request)
    {
        if (Auth::user()) {
            return redirect(config('patent-oauth-client.redirect_after_login_uri'));
        }

        if (empty(config('patent-oauth-client.client_id'))) {
            return abort(500, "No 'client_id' found for the OAuth service.");
        }

        if (empty(config('patent-oauth-client.client_redirect_uri'))) {
            return abort(500, "No 'client_redirect_uri' found for the OAuth service.");
        }

        if (empty(config('patent-oauth-client.server_uri'))) {
            return abort(500, "No 'server_uri' found for the OAuth service.");
        }

        $request->session()->put('state', $state = Str::random(40));
    
        $query = http_build_query([
            'client_id' => config('patent-oauth-client.client_id'),
            'redirect_uri' => config('patent-oauth-client.client_redirect_uri'),
            'response_type' => 'code',
            'scope' => '*',
            'state' => $state,
        ]);
    
        return redirect(config('patent-oauth-client.server_uri') . '/oauth/authorize?' . $query);
    }

    public function callbackFromOAuthServer(Request $request)
    {
        if (empty(config('patent-oauth-client.client_id'))) {
            return abort(500, "No 'client_id' found for the OAuth service.");
        }

        if (empty(config('patent-oauth-client.client_redirect_uri'))) {
            return abort(500, "No 'client_redirect_uri' found for the OAuth service.");
        }

        if (empty(config('patent-oauth-client.server_uri'))) {
            return abort(500, "No 'server_uri' found for the OAuth service.");
        }

        if (empty(config('patent-oauth-client.client_secret'))) {
            return abort(500, "No 'client_secret' found for the OAuth service.");
        }

        $state = $request->session()->pull('state');
    
        if (strlen($state) === 0 || $state !== $request->state) {
            return abort(500, "Request timed out.");
        }

        $response = Http::asForm()->post(config('patent-oauth-client.server_uri') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('patent-oauth-client.client_id'),
            'client_secret' => config('patent-oauth-client.client_secret'),
            'redirect_uri' => config('patent-oauth-client.client_redirect_uri'),
            'code' => $request->code,
        ]);
    
        if ($response->ok()) {
            $json = $response->json();

            if (empty($json)) {
                return abort(500, "Hiba történt a bejelentkezés közben!");
            }

            $expires_at = Carbon::now()->addSeconds($json['expires_in']);
            $access_token = $json['access_token'];
            $refresh_token = $json['refresh_token'];
    
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ])->get(config('patent-oauth-client.server_uri') . '/api/user');
    
            $user_data = $response->json();

            if (empty($user_data)) {
                return abort(500, "Hiba történt a bejelentkezés közben!");
            }
            
            try {
                $user = User::query()->where('pas_id', $user_data['id'])->firstOrFail();
            } catch (ModelNotFoundException $ex) {
                return abort(500, "Felhasználó nem található!");
            }
            
            // Csoda porta kód
            if (config('backpack.base.project_name') === 'Patent Porta') {
                if (!$user->isSuperAdmin() && !$user->can('login without account')) {
                    if ($user->cannot('login backend')) {
                        return abort(500, __('admin::global.cannot_login_to_backend'));
                    }
    
                    if ($user->accounts()->count() == 0 && $user->cannot('change account')) {
                        return abort(500, __('admin::global.no_account_no_cry'));
                    }
    
                    if (!$user->sites()->count() && $user->cannot('change account')) {
                        return abort(500, __('admin::global.no_site_no_cry'));
                    }
                }
            }

            if ($user->email !== $user_data['email']) {
                $user->email = $user_data['email'];
            }

            if ($user->name !== $user_data['name']) {
                $user->name = $user_data['name'];
            }

            if ($request->session_id) {
                session(['pas_session_id' => $request->session_id]);
            }

            $user->save();

            Auth::login($user);
            
            return redirect(config('patent-oauth-client.redirect_after_login_uri'))
                ->withCookie(cookie(
                    'pas_logged_in_from', 
                    config('patent-oauth-client.client_id'),
                    0,
                    null,
                    ".patentapp.eu",
                ));

        } else {
            return abort(500, "Hiba történt a bejelentkezés közben!");
        }
    }

    public function login()
    {
        return redirect(route("patent-oauth-client.redirect"));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if (isset($user) && !empty($user)) {
            $response = Http::asForm()->post(config('patent-oauth-client.server_uri') . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => config('patent-oauth-client.client_id'),
                'client_secret' => config('patent-oauth-client.client_secret'),
                'scope' => '*',
            ]);

            if ($response->ok()) {
                $json = $response->json();
                if (!empty($json)) {
                    $session_id = session('pas_session_id');

                    Http::withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $json['access_token'],
                    ])->post(config('patent-oauth-client.server_uri') . '/api/logout', [
                        'pas_id' => $user->pas_id,
                        'session_id' => $session_id,
                    ]);
                }
            }

            session(['pas_session_id' => null]);
            Auth::logout();
        }

        return redirect(config('patent-oauth-client.redirect_after_login_uri'))->withoutCookie('pas_logged_in_from');
    }

    public static function handlePostUser(
        string|null $email,
        string|null $name,
        string|null $password,
        ?int $user_id = null
    ) {
        $user = null;
        if ($user_id !== null) {
            $user = User::query()->find($user_id);
        }

        $response = Http::asForm()->post(config('patent-oauth-client.server_uri') . '/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => config('patent-oauth-client.client_id'),
            'client_secret' => config('patent-oauth-client.client_secret'),
            'scope' => '*',
        ]);

        if ($response->ok()) {
            $json = $response->json();

            if (empty($json)) {
                return null;
            }

            $user_response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $json['access_token'],
            ])->post(config('patent-oauth-client.server_uri') . '/api/user', [
                'email' => $email,
                'name' => $name,
                'password' => $password,
                'pas_id' => $user?->pas_id??null,
            ]);

            if ($user_response->ok()) {
                return $user_response->json();
            }
            
            return null;
        }
        
        return null;
    }

    public static function passwordLogin(string $email, string $password) {
        if ($email === null || $password === null || $email === "" || $password === "") {
            return null;
        }

        $response = Http::asForm()->post(config('patent-oauth-client.server_uri') . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => config('patent-oauth-client.pgc_client_id'),
            'client_secret' => config('patent-oauth-client.pgc_client_secret'),
            'username' => $email,
            'password' => $password,
            'scope' => '*',
        ]);

        if ($response->ok()) {
            $json = $response->json();

            if (empty($json)) {
                return null;
            }

            if ($response->ok()) {
                $json = $response->json();
    
                if (empty($json)) {
                    return null;
                }
    
                $expires_at = Carbon::now()->addSeconds($json['expires_in']);
                $access_token = $json['access_token'];
                $refresh_token = $json['refresh_token'];
        
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token,
                ])->get(config('patent-oauth-client.server_uri') . '/api/user');
        
                $user_data = $response->json();
    
                if (empty($user_data)) {
                    return null;
                }
                
                try {
                    $user = User::query()->where('pas_id', $user_data['id'])->firstOrFail();
                } catch (ModelNotFoundException $ex) {
                    return null;
                }

                if ($user->email !== $user_data['email']) {
                    $user->email = $user_data['email'];
                }
    
                if ($user->name !== $user_data['name']) {
                    $user->name = $user_data['name'];
                }
    
                /*if ($request->session_id) {
                    session(['pas_session_id' => $request->session_id]);
                }*/

                $user->save();
    
                return $user;
            }

            return null;
        }
        
        return null;
    }
}
