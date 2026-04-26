<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportService extends Model
{
    protected $table = 'report_services';

    protected $fillable = [
        'nama_konsumen', 'instansi', 'email_konsumen', 'no_hp_konsumen',
        'service_id', 'id_media', 'uraian', 'status',
        'tindak_lanjut', 'dokumentasi', 'user_id','penerima', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function penerima()
    {
        return $this->belongsTo(User::class,'penerima');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    public function media()
    {
        return $this->belongsTo(MediaPelaporan::class, 'id_media');
    }
}
