<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Mengambil semua data user
    public function index()
    {
        $users = User::all();
        return response()->json(['status' => 'success', 'data' => $users]);
    }

    // Menambah user baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas',
        ]);

        $user = User::create($validated); // Password otomatis di-hash oleh Mutator di Model User

        return response()->json(['status' => 'success', 'message' => 'User berhasil ditambahkan', 'data' => $user]);
    }

    //Mengedit User
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $user->id,
            'role' => 'required|in:admin,petugas',
            'password' => 'nullable|string|min:6',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil diperbarui'
        ]);
    }

    // Menghapus user
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['status' => 'success', 'message' => 'User berhasil dihapus']);
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6',
        ]);

        // Update password saja
        $user->password = $validated['password']; // Mutator di Model User akan otomatis melakukan Hash::make
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password user ' . $user->name . ' berhasil direset.',
        ]);
    }

    public function toggleStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status user ' . $user->name . ' berhasil diubah.',
            'is_active' => $user->is_active
        ]);
    }
}
