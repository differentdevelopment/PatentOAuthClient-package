<?php
if (!function_exists('pas_avatar_url')) {
    function pas_avatar_url($user)
    {
        if ($user->pas_id === null) {
            return "";
        }

        $response = Illuminate\Support\Facades\Http::asForm()
            ->get(config('patent-oauth-client.server_uri') . '/api/avatar/' . $user->pas_id);

        return $response->body();
    }
}
