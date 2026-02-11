<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MediaPelaporan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MediaPelaporanController extends Controller
{
    /**
     * GET: Ambil semua media pelaporan
     */
    public function index(): JsonResponse
    {
        $data = MediaPelaporan::get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * POST: Tambah media pelaporan
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'media' => 'required|string|max:100|unique:media_pelaporan,media'
        ]);

        $media = MediaPelaporan::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Media pelaporan berhasil ditambahkan',
            'data' => $media
        ], 201);
    }

    /**
     * PUT: Update media pelaporan
     */
    public function update(Request $request, $id): JsonResponse
    {
        $media = MediaPelaporan::findOrFail($id);

        $validated = $request->validate([
            'media' => 'required|string|max:100|unique:media_pelaporan,media,' . $id
        ]);

        $media->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Media pelaporan berhasil diperbarui',
            'data' => $media
        ]);
    }

    /**
     * DELETE: Hapus media pelaporan
     */
    public function destroy($id): JsonResponse
    {
        $media = MediaPelaporan::findOrFail($id);

        // otomatis hapus report_services jika onDelete cascade
        $media->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Media pelaporan berhasil dihapus'
        ]);
    }
}
