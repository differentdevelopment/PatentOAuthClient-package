<?php

namespace Different\PatentOAuthClient;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Different\PatentOAuthClient\Models\UserToken;

class PatentOAuthClient
{
    public function redirectToOAuthServer(Request $request)
    {
        // If the user is already logged in
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
            
            $user = User::query()->whereEmail($user_data["email"])->firstOrFail();
            
            // Csoda porta kód
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

            Auth::login($user);

            /*serToken::query()->create([
                'user_id' => $user->id,
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
                'expires_at' => $expires_at,
            ]);*/

            return redirect(config('patent-oauth-client.redirect_after_login_uri'));
        } else {
            return abort(500, "Hiba történt a bejelentkezés közben!");
        }
    }

    public function login()
    {
        return view("patent-oauth-client::login"); 
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if (isset($user) && !empty($user)) {
            /*$user_token = UserToken::query()->where('user_id', $user->id)->first();
            if (isset($user_token) && !empty($user_token)) {
                $user_token->logout();
            }*/
            Auth::logout();
        }

        return redirect(config('patent-oauth-client.redirect_after_login_uri')); 
    }
}
