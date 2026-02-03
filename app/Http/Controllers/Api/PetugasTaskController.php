<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskReport; // Perbaikan: Gunakan PascalCase
use App\Models\Task;       // Pastikan Model Task ada
use App\Models\Schedule;   // Pastikan Model Schedule ada
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class PetugasTaskController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $today = now()->toDateString();

            // Cek jadwal hari ini
            $hasSchedule = Schedule::where('user_id', $user->id)
                ->where('date', $today)
                ->exists();

            if (!$hasSchedule) {
                return response()->json([
                    'has_schedule' => false,
                    'message' => 'Anda tidak memiliki jadwal tugas untuk hari ini.',
                    'data' => []
                ]);
            }

            // Ambil semua master tugas dan cek progresnya
            $tasks = Task::all()->map(function ($task) use ($user, $today) {
                $report = TaskReport::where('user_id', $user->id)
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

        } catch (Exception $e) {
            // Menangkap error spesifik agar tidak hanya muncul Error 500
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) return response()->json(['message' => 'Unauthenticated.'], 401);

            $today = now()->toDateString();

            foreach ($request->tasks as $taskId => $isCompleted) {
                TaskReport::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'task_id' => $taskId,
                        'date'    => $today
                    ],
                    ['status' => $isCompleted ? 1 : 0]
                );
            }

            return response()->json(['message' => 'Progres harian berhasil disimpan.']);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
