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
        // Cari user berdasarkan nama
        $user = User::where('name', $row['petugas'])->first();

        if (!$user) {
            return null; // skip kalau user tidak ditemukan
        }

        return new Schedule([
            'user_id'    => $user->id,
            'date'       => Carbon::createFromFormat(
                'd M Y',
                $row['tanggal']
            )->format('Y-m-d'),
            'keterangan' => $row['keterangan'],
        ]);
    }
}
