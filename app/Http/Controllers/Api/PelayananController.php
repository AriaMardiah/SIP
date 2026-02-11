<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MediaPelaporan;
use App\Models\ReportService;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PelayananController extends Controller
{
    /**
     * Mengambil rekapitulasi bulanan.
     * Jika database kosong, ini akan mengembalikan array kosong.
     */

public function getMonthlyReport(Request $request)
{
    $bulan = $request->query('bulan', date('n'));
    $tahun = $request->query('tahun', date('Y'));

    $services = Service::all();
    $medias   = MediaPelaporan::all();

    // 🔥 Ambil semua data sekaligus (lebih cepat)
    $raw = ReportService::select(
            'service_id',
            'id_media',
            DB::raw('COUNT(*) as total')
        )
        ->whereYear('created_at', $tahun)
        ->whereMonth('created_at', $bulan)
        ->groupBy('service_id', 'id_media')
        ->get();

    $report = $services->map(function ($service) use ($medias, $raw) {

        $mediaData = $medias->map(function ($media) use ($service, $raw) {

            $found = $raw->first(function ($r) use ($service, $media) {
                return $r->service_id == $service->id &&
                       $r->id_media == $media->id;
            });

            return [
                'id_media' => $media->id,
                'media'    => $media->media,
                'total'    => $found ? (int) $found->total : 0
            ];
        });

        return [
            'service_id' => $service->id,
            'jenis'      => $service->jenis_pelayanan,
            'medias'     => $mediaData,
            'total'      => $mediaData->sum('total')
        ];
    });

    return response()->json([
        'status' => 'success',
        'bulan'  => $bulan,
        'tahun'  => $tahun,
        'media'  => $medias,
        'data'   => $report
    ]);
}


    // 1. TAMBAH JENIS BARU (Initial Record)
    public function initService(Request $request)
    {
        $request->validate(['jenis' => 'required|string']);

        Service::create([
            'jenis_pelayanan' => $request->jenis,
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Jenis pelayanan baru ditambahkan.']);
    }

    // 2. RENAME JENIS PELAYANAN (Mass Update)
    public function renameService(Request $request)
    {
        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string'
        ]);

        Service::where('jenis_pelayanan', $request->old_name)
            ->update(['jenis_pelayanan' => $request->new_name]);

        return response()->json(['message' => 'Nama jenis pelayanan berhasil diperbarui.']);
    }

    // 3. REMOVE JENIS PELAYANAN (Mass Delete)
    public function removeService(Request $request)
    {
        $request->validate(['jenis' => 'required|string']);

        Service::where('jenis_pelayanan', $request->jenis)->delete();

        return response()->json(['message' => 'Semua data jenis pelayanan tersebut telah dihapus.']);
    }
    public function index()
    {
        $service = Service::all();
        return response()->json(['status' => 'success', 'data' => $service]);
    }
}
