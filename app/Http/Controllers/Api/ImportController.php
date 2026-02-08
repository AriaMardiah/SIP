<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\SchedulesImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls'
    ]);

    Excel::import(new SchedulesImport, $request->file('file'));

    return response()->json([
        'status' => 'success',
        'message' => 'Jadwal berhasil diimport'
    ]);
}

}
