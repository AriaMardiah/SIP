<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        // MENGAMBIL JENIS UNIK DARI DATABASE (Bukan Hardcoded)
        $jenisList = Service::distinct()->pluck('jenis_pelayanan');

        $report = $jenisList->map(function ($jenis) use ($bulan, $tahun) {
            $countByDayRange = function ($start, $end) use ($jenis, $bulan, $tahun) {
                return Service::where('jenis_pelayanan', $jenis)
                    ->whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $bulan)
                    ->whereRaw("DAY(created_at) BETWEEN $start AND $end")
                    ->count();
            };

            return [
                'jenis' => $jenis,
                'minggu1' => $countByDayRange(1, 7),
                'minggu2' => $countByDayRange(8, 14),
                'minggu3' => $countByDayRange(15, 21),
                'minggu4' => Service::where('jenis_pelayanan', $jenis)
                    ->whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $bulan)
                    ->whereRaw("DAY(created_at) >= 22")
                    ->count(),
            ];
        });

        return response()->json(['status' => 'success', 'data' => $report]);
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
}
