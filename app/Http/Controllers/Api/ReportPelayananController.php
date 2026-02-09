<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportPelayananController extends Controller
{
    public function index()
    {
        $data = ReportService::with([
            'user:id,name',
            'service:id,jenis_pelayanan',
            'penerima:id,name'
        ])
            ->orderByRaw("
            CASE
                WHEN status = 'progress' THEN 0
                WHEN status = 'selesai' THEN 1
                ELSE 2
            END
        ")

            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_konsumen' => 'required|string',
            'instansi' => 'nullable|string',
            'email_konsumen' => 'nullable|email',
            'no_hp_konsumen' => 'nullable|string',
            'service_id' => 'required|exists:services,id',
            'media_pelaporan' => 'required|string',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        if ($request->hasFile('dokumentasi')) {
            $validated['dokumentasi'] = $request->file('dokumentasi')->store('dokumentasi_pelayanan', 'public');
        }

        $validated['user_id'] = auth()->id();
        $validated['penerima'] = auth()->id();
        $validated['status'] = 'progress';

        $report = ReportService::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan berhasil ditambahkan',
            'data' => $report
        ]);
    }

    public function destroy($id)
    {
        $service = ReportService::findOrFail($id);

        if ($service->dokumentasi) {
            Storage::disk('public')->delete($service->dokumentasi);
        }

        $service->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan berhasil dihapus'
        ]);
    }

    public function updateProgress(Request $request, $id)
    {
        $service = ReportService::findOrFail($id);

        $request->validate([
            'status' => 'required|in:progress,selesai',
            'tindak_lanjut' => 'nullable|string'
        ]);


        $service->update([
            'status' => $request->status,
            'tindak_lanjut' => $request->tindak_lanjut,
            'penerima' => auth()->id(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Tindak lanjut diperbarui']);
    }
}
