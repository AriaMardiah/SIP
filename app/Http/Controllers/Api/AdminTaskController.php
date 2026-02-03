<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskReport;
use App\Models\User;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    /**
     * MENGAMBIL REKAPAN AKUMULATIF (Untuk Tabel Admin)
     */
    // app/Http/Controllers/Api/AdminTaskController.php

    // app/Http/Controllers/Api/AdminTaskController.php

    public function getCumulativeReport(Request $request)
    {
        $allTasks = Task::all();
        $allPetugas = User::where('role', 'petugas')->get();

        // Ambil parameter semester (1 atau 2) dan tahun
        $semester = $request->query('semester', 1);
        $year = $request->query('year', date('Y'));

        $reportData = $allPetugas->map(function ($user) use ($allTasks, $semester, $year) {
            $taskScores = [];

            foreach ($allTasks as $task) {
                $query = TaskReport::where('user_id', $user->id)
                    ->where('task_id', $task->id)
                    ->whereYear('date', $year);

                // Filter Rentang Bulan sesuai Semester
                if ($semester == 1) {
                    $query->whereMonth('date', '>=', 1)->whereMonth('date', '<=', 6);
                } else {
                    $query->whereMonth('date', '>=', 7)->whereMonth('date', '<=', 12);
                }

                $taskScores[] = (int) $query->sum('status');
            }

            return [
                'id' => $user->id,
                'nama' => $user->name,
                'tugas' => $taskScores
            ];
        });

        return response()->json([
            'tugasItems' => $allTasks,
            'petugasData' => $reportData
        ]);
    }

    public function updateTask(Request $request, $id)
    {
        $request->validate(['uraian' => 'required|string']);
        $task = Task::findOrFail($id);
        $task->update(['uraian' => $request->uraian]);
        return response()->json(['message' => 'Tugas diperbarui']);
    }

    public function destroyTask($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Tugas berhasil dihapus']);
    }
}
