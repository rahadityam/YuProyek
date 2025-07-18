<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    // Menampilkan daftar pengguna
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10); // Ambil parameter perPage dari request
        $users   = User::select('id', 'name', 'email', 'role', 'status')
                        ->paginate($perPage); // Ambil data pengguna dengan pagination

        // Tambahkan respons JSON untuk API
        if ($request->wantsJson()) {
            return response()->json($users);
        }
        
        return view('admin.users.user_admin', compact('users')); // Kirim data pengguna ke view
    }

    // Mengubah status pengguna (active / blocked)
    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id); // Cari pengguna berdasarkan ID

        // Toggle status pengguna
        $user->status = $user->status === 'active' ? 'blocked' : 'active';
        $user->save(); // Simpan perubahan

        // Pesan yang ditampilkan setelah status diubah
        $message = $user->status === 'blocked'
                 ? 'User has been blocked successfully.'
                 : 'User has been unblocked successfully.';

        // Return JSON response for API requests
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'user' => $user->fresh()]);
        }

        return redirect()
            ->route('admin.users.index') // Arahkan kembali ke halaman daftar pengguna
            ->with('status', $message); // Kirim pesan status
    }

    // Menampilkan form untuk membuat akun baru
    public function create()
    {
        return view('admin.users.create_user'); // Tampilkan form pembuatan akun baru
    }

    // Menyimpan akun baru
    public function store(Request $request)
    {
        // Validasi inputan
        $validatedData = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'role'                  => 'required|in:admin,project_owner,worker',
            'password'              => 'required|min:6|confirmed',
        ], [
            'name.required'         => 'Name is required.',
            'name.max'              => 'Name may not be greater than 255 characters.',
            'email.required'        => 'Email address is required.',
            'email.email'           => 'Please enter a valid email address.',
            'email.unique'          => 'This email address is already taken.',
            'role.required'         => 'Please select a role.',
            'role.in'               => 'The selected role is invalid.',
            'password.required'     => 'Password is required.',
            'password.min'          => 'Password must be at least 6 characters.',
            'password.confirmed'    => 'Password confirmation does not match.',
        ]);

        // Membuat pengguna baru dan menyimpannya ke database
        $user = User::create([
            'name'     => $validatedData['name'],
            'email'    => $validatedData['email'],
            'role'     => $validatedData['role'],
            'password' => Hash::make($validatedData['password']),
            'status'   => 'active',
        ]);

        // Return JSON response for API requests
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Account has been created successfully.',
                'user' => $user
            ], 201);
        }

        // Setelah berhasil membuat akun, arahkan ke halaman form pembuatan akun dan tampilkan pesan sukses
        return redirect()
            ->route('admin.users.create') // Kembali ke halaman form
            ->with('success', 'Account has been created successfully.'); // Pesan sukses
    }
}
