<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaPelaporan extends Model
{
    use HasFactory;
    protected $table = 'media_pelaporan';
    protected $fillable = ['media'];

    public function reportServices()
    {
        return $this->hasMany(ReportService::class, 'id_media');
    }
}
