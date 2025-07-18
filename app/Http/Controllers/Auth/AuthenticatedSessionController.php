<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->role == 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user->role == 'ceo') {
            return redirect()->route('ceo.dashboard');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Check if the user's status is 'blocked'
        $user = Auth::user();

        if ($user->status === 'blocked') {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah diblokir.',
            ]);
        }

        $request->session()->regenerate();

        return $this->authenticated($request, Auth::user());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        // Cek jika request ini adalah request API
        // $request->is('api/*') adalah pengecekan yang andal untuk rute API
        if ($request->is('api/*')) {
            // Untuk API, kita hapus token yang sedang digunakan untuk otentikasi
            $request->user()->currentAccessToken()->delete();
            
            return response()->json(['message' => 'Logged out successfully']);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
