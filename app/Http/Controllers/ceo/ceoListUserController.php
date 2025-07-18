<?php

namespace App\Http\Controllers\Ceo;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ceoListUserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $query = User::select('id', 'name', 'email', 'role', 'status');

        // Dapatkan hasil paginasi
        $users = $query->paginate($perPage);

        // Jika request dari API, kembalikan data JSON
        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return view('ceo.user_list', compact('users'));
    }
}

