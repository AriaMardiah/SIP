<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportService extends Model
{
    protected $table = 'report_services';

    protected $fillable = [
        'nama_konsumen', 'instansi', 'email_konsumen', 'no_hp_konsumen',
        'service_id', 'media_pelaporan', 'uraian', 'status',
        'tindak_lanjut', 'dokumentasi', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
