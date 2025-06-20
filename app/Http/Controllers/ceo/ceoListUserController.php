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
        $users = User::select('id', 'name', 'email', 'role', 'status')->paginate($perPage);
        return view('ceo.user_list', compact('users'));
    }
}

