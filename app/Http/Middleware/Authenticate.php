<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request; // Pastikan use statement ini ada

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        // JANGAN redirect jika request adalah AJAX/API yang mengharapkan JSON.
        // Biarkan Laravel melempar AuthenticationException, yang akan
        // secara otomatis diubah menjadi response 401 Unauthorized.
        if ($request->expectsJson()) {
            return null;
        }

        // Jika request biasa (dari browser), redirect ke halaman login.
        return route('login');
    }
}