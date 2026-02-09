<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SchedulesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['petugas']) || !isset($row['tanggal'])) {
            return null;
        }

        // Cari user
        $user = User::whereRaw('LOWER(name) = ?', [strtolower($row['petugas'])])->first();

        if (!$user) {
            return null;
        }

        $date = Carbon::parse($row['tanggal'])->format('Y-m-d');

        // CEK DUPLIKAT -> UPDATE ATAU INSERT
        return Schedule::updateOrCreate(
            [
                'user_id' => $user->id,
                'date'    => $date,
            ],
            [
                'keterangan' => $row['keterangan'] ?? null,
            ]
        );
    }
}
