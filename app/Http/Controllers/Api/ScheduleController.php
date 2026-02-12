<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $schedules = Schedule::with('user')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        return response()->json(['data' => $schedules]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'assignments' => 'required|array|min:1',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.keterangan' => 'required|string',
        ]);

        // 1. Cek duplikasi terlebih dahulu
        foreach ($validated['assignments'] as $assign) {
            $exists = Schedule::where('user_id', $assign['user_id'])
                ->where('date', $validated['date'])
                ->exists();

            if ($exists) {
                $user = User::find($assign['user_id']);
                return response()->json([
                    'message' => "Jadwal untuk {$user->name} pada tanggal tersebut sudah ada!"
                ], 422); // Kirim error 422 ke frontend
            }
        }

        // 2. Jika aman, baru simpan
        foreach ($validated['assignments'] as $assign) {
            Schedule::create([
                'user_id' => $assign['user_id'],
                'date' => $validated['date'],
                'keterangan' => $assign['keterangan'],
            ]);
        }

        return response()->json(['message' => 'Jadwal berhasil disimpan']);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'keterangan' => 'required|string',
        ]);

        // 🔥 cek duplikat (kecuali data yg sedang diedit)
        $exists = Schedule::where('user_id', $validated['user_id'])
            ->where('date', $validated['date'])
            ->where('id', '!=', $schedule->id) // penting agar tidak bentrok dengan dirinya sendiri
            ->exists();

        if ($exists) {
            $user = User::find($validated['user_id']);

            return response()->json([
                'message' => "Jadwal untuk {$user->name} pada tanggal tersebut sudah ada!"
            ], 422);
        }

        // update data
        $schedule->update([
            'user_id' => $validated['user_id'],
            'date' => $validated['date'],
            'keterangan' => $validated['keterangan'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil diperbarui'
        ]);
    }


    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return response()->json(['message' => 'Jadwal dihapus']);
    }


    public function mySchedule(Request $request)
    {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $schedules = Schedule::where('user_id', $request->user()->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $schedules
        ]);
    }
}
