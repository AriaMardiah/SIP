<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatTemplateController extends Controller
{
    public function index()
    {
        // Mengambil semua template chat
        $templates = ChatTemplate::orderBy('request', 'asc')->get();
        return response()->json(['data' => $templates]);
    }

    public function store(Request $request)
    {
        // Hanya Admin yang diizinkan menambah data
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'request' => 'required|string|max:255',
            'response' => 'required|string',
        ]);

        $template = ChatTemplate::create($validated);
        return response()->json(['message' => 'Template berhasil disimpan', 'data' => $template]);
    }

    public function update(Request $request, $id)
    {
        // 1. Pastikan Model ChatTemplate sudah di-import di atas: use App\Models\ChatTemplate;
        $template = \App\Models\ChatTemplate::find($id);

        if (!$template) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // 2. Validasi kolom 'request' dan 'response'
        $validated = $request->validate([
            'request' => 'required|string',
            'response' => 'required|string',
        ]);

        // 3. Simpan perubahan
        $template->update($validated);

        return response()->json(['message' => 'Update berhasil', 'data' => $template]);
    }

    public function destroy($id)
    {
        // Pastikan model ChatTemplate sudah menggunakan 'protected $table = "template_chats"'
        $template = \App\Models\ChatTemplate::find($id);

        if (!$template) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $template->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}
