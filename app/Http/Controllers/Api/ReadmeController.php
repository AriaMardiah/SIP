<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Readme;
use Illuminate\Http\Request;

class ReadmeController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Readme::all()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url'  => 'required|string', // Diubah dari 'url' menjadi 'string'
        ]);

        $readme = Readme::create($validated);
        return response()->json(['message' => 'Data berhasil disimpan', 'data' => $readme]);
    }

    public function update(Request $request, Readme $readme)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url'  => 'required|string',
        ]);

        $readme->update($validated);
        return response()->json(['message' => 'Data diperbarui']);
    }

    public function destroy(Readme $readme)
    {
        $readme->delete();
        return response()->json(['message' => 'Link dihapus']);
    }
}
