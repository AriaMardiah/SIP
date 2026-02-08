<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\TaskReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardAdmin extends Controller
{
    public function index(): JsonResponse
    {
        $alluser = User::where('is_active',true)->get();
        $today = now()->toDateString();

            // Cek jadwal hari ini
            $hasSchedule = Schedule::where('date', $today)
                ->exists();
                if (!$hasSchedule) {
                    return response()->json([
                        'has_schedule' => false,
                        'message' => 'Anda tidak memiliki jadwal tugas untuk hari ini.',
                        'data' => []
                    ]);
                }
    
                // Ambil semua master tugas dan cek progresnya
                $tasks = Task::all()->map(function ($task) use ( $today) {
                    $report = TaskReport::all()
                        ->where('task_id', $task->id)
                        ->where('date', $today)
                        ->first();
    
                    return [
                        'id' => $task->id,
                        'uraian' => $task->uraian,
                        'selesai' => $report ? ($report->status == 1) : false
                    ];
                });
    
                return response()->json([
                    'has_schedule' => true,
                    'data' => $tasks
                ]);
        // dd($alluser);
    }
}
