<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalDetail extends Model
{
    use SoftDeletes;
    
    protected $table = 'jadwal_details';
    
    protected $fillable = [
        'day',
        'tanggal',
        'jam_kerja_id',
        'jadwal_id',
        'created_by',
        'created_at',
    ];

    public function jadwal()
    {
        return $this->belongsTo('App\Jadwal');
    }
}
