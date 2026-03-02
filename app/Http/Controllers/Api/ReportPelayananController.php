<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportService;
use ZipArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportPelayananController extends Controller
{

    public function index(Request $request)
    {
        $query = ReportService::with([
            'user:id,name',
            'service:id,jenis_pelayanan',
            'penerima:id,name',
            'media:id,media'
        ]);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->id_media) {
            $query->where('id_media', $request->id_media);
        }

        if ($request->semester && $request->year) {

            $semester = $request->semester;
            $year = $request->year;

            $query->whereYear('created_at', $year);

            if ($semester == 1) {
                $query->whereMonth('created_at', '>=', 1)
                    ->whereMonth('created_at', '<=', 6);
            } elseif ($semester == 2) {
                $query->whereMonth('created_at', '>=', 7)
                    ->whereMonth('created_at', '<=', 12);
            }
        }


        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $data = $query
            ->orderByRaw("
            CASE
                WHEN status = 'progress' THEN 0
                WHEN status = 'selesai' THEN 1
                ELSE 2
            END
        ")
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $query
            ->orderByRaw("
        CASE
            WHEN status = 'progress' THEN 0
            WHEN status = 'selesai' THEN 1
            ELSE 2
        END
    ")
            ->orderBy('created_at', 'desc')
            ->get();

        // ======================
        // 🔥 HITUNG SUMMARY
        // ======================

        $totalSemua = $data->count();
        $totalSelesai = $data->where('status', 'selesai')->count();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'summary' => [
                'total_pelayanan' => $totalSelesai,
                'total_semua_pelayanan' => $totalSemua
            ]
        ]);

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
            'id_media' => 'required|exists:media_pelaporan,id',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        if ($request->hasFile('dokumentasi')) {

            $file = $request->file('dokumentasi');

            $extension = $file->getClientOriginalExtension();

            $fileName = $validated['nama_konsumen'] . '_' .
                now()->format('Y-m-d_H-i-s') .
                '.' . $extension;


            $validated['dokumentasi'] = $file->storeAs(
                'dokumentasi_pelayanan',
                $fileName,
                'public'
            );
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

    public function download($id)
    {
        $report = ReportService::findOrFail($id);

        $filePath = storage_path('app/public/' . $report->dokumentasi);

        if (!file_exists($filePath)) {
            return response()->json([
                'message' => 'File tidak ditemukan'
            ], 404);
        }

        // Ambil extension asli file
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        // Format tanggal (YYYYMMDD)
        $tanggal = \Carbon\Carbon::parse($report->created_at)
            ->format('Ymd');

        // Bersihkan nama konsumen dari spasi & karakter aneh
        $nama = preg_replace('/[^A-Za-z0-9\-]/', '_', $report->nama_konsumen);

        // Nama file baru
        $fileName = $nama . '_' . $tanggal . '.' . $extension;

        return response()->download($filePath, $fileName);
    }

    public function downloadZip(Request $request)
    {
        $query = ReportService::query();

        // ======================
        // 🔥 FILTER SAMA SEPERTI INDEX
        // ======================

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->id_media) {
            $query->where('id_media', $request->id_media);
        }

        if ($request->semester && $request->year) {
            $query->whereYear('created_at', $request->year);

            if ($request->semester == 1) {
                $query->whereBetween(\DB::raw('MONTH(created_at)'), [1, 6]);
            } elseif ($request->semester == 2) {
                $query->whereBetween(\DB::raw('MONTH(created_at)'), [7, 12]);
            }
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $reports = $query->get();

        if ($reports->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada dokumentasi untuk diunduh'
            ], 404);
        }

        // ======================
        // 🔥 BUAT FILE ZIP
        // ======================

        $zip = new ZipArchive();
        $zipFileName = 'Dokumentasi_Pelayanan_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            foreach ($reports as $report) {

                if (!$report->dokumentasi) continue;

                $filePath = storage_path('app/public/' . $report->dokumentasi);

                if (file_exists($filePath)) {

                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    $tanggal = \Carbon\Carbon::parse($report->created_at)
                        ->format('Ymd');

                    $nama = preg_replace('/[^A-Za-z0-9\-]/', '_', $report->nama_konsumen);

                    $fileName = $nama . '_' . $tanggal . '.' . $extension;

                    $zip->addFile($filePath, $fileName);
                }
            }

            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
