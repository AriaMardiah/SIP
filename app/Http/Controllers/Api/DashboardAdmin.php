<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportService;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\TaskReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardAdmin extends Controller
{


    public function summary()
    {
        return response()->json([
            'total_semua' => ReportService::count(),
            'total_pelayanan' => ReportService::where('status', 'selesai')->count(),
            'total_progress' => ReportService::where('status', 'progress')->count(),
        ]);
    }
    public function index(Request $request): JsonResponse
    {
        $semester = $request->query('semester', 1);
        $year = $request->query('year', date('Y'));

        /* ================= PETUGAS ================= */
        $petugas = User::where('is_active', true)
            ->where('role', 'petugas')
            ->get();

        $totalPetugas = $petugas->count();

        /* ================= FILTER TANGGAL ================= */
        $filterTanggal = function ($query, $field) use ($semester, $year) {
            $query->whereYear($field, $year);

            if ($semester == 1) {
                $query->whereMonth($field, '>=', 1)
                    ->whereMonth($field, '<=', 6);
            } else {
                $query->whereMonth($field, '>=', 7)
                    ->whereMonth($field, '<=', 12);
            }
        };

        /* ================= TOTAL TASK ================= */
        $totalTask = TaskReport::where(function ($q) use ($filterTanggal) {
            $filterTanggal($q, 'date');
        })->count();

        $taskSelesai = TaskReport::where('status', 1)
            ->where(function ($q) use ($filterTanggal) {
                $filterTanggal($q, 'date');
            })->count();

        /* ================= TOTAL PELAYANAN ================= */
        $totalPelayanan = ReportService::where(function ($q) use ($filterTanggal) {
            $filterTanggal($q, 'created_at');
        })->count();

        $pelayananSelesai = ReportService::where('status', 'selesai')
            ->where(function ($q) use ($filterTanggal) {
                $filterTanggal($q, 'created_at');
            })->count();

        /* ================= RATA-RATA PERFORMA ================= */
        $totalAktivitas = $totalTask + $totalPelayanan;
        $totalSelesai = $taskSelesai + $pelayananSelesai;

        $rataPerforma = $totalAktivitas > 0
            ? round(($totalSelesai / $totalAktivitas) * 100)
            : 0;

        /* ================= RANKING PETUGAS ================= */
        $ranking = $petugas->map(function ($user) use ($filterTanggal) {

            $taskSelesai = TaskReport::where('user_id', $user->id)
                ->where('status', 1)
                ->where(function ($q) use ($filterTanggal) {
                    $filterTanggal($q, 'date');
                })->count();

            $totalTask = TaskReport::where('user_id', $user->id)
                ->where(function ($q) use ($filterTanggal) {
                    $filterTanggal($q, 'date');
                })->count();

            $pelayananSelesai = ReportService::selectRaw("
        COUNT(CASE WHEN penerima = ? THEN 1 END) +
        COUNT(CASE WHEN user_id = ? THEN 1 END)
        as total
    ", [$user->id, $user->id])
                ->where('status', 'selesai')
                ->where(function ($q) use ($filterTanggal) {
                    $filterTanggal($q, 'created_at');
                })
                ->value('total');




            return [
                'user_id' => $user->id,
                'nama' => $user->name,
                'tugas_selesai' => $taskSelesai,
                'total_tugas' => $totalTask,
                'pelayanan_selesai' => $pelayananSelesai,
                'total_performa' => $taskSelesai + $pelayananSelesai
            ];
        })
            ->sortByDesc('total_performa')
            ->values()
            ->take(3);

        /* ================= RESPONSE ================= */
        return response()->json([
            'summary' => [
                'total_petugas' => $totalPetugas,
                'total_pelayanan' => $pelayananSelesai,
                'total_semua_pelayanan' => $totalPelayanan,
                'tugas_selesai' => $taskSelesai,
                'total_tugas' => $totalTask,
                'rata_rata_performa' => $rataPerforma
            ],
            'ranking_petugas' => $ranking
        ]);
    }
}
