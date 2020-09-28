<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RequestAlasanDetail extends Model
{
    use SoftDeletes;
    
    protected $table = 'request_alasan_detail';
    
    protected $fillable = [
        'tanggal',
        'tanggal_akhir',
        'waktu',
        'karyawan_id',
        'alasan_id',   
        'request_alasan_id',
        'status',
        'approved_by',
        'approved_at',
        'declined_by',
        'declined_at',
        'declined_note',
        'catatan',
        'deleted_at',
        'created_by', 
        'created_at',
        'updated_by', 
        'updated_at'
    ];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    public function karyawan()
    {
        return $this->belongsTo('App\Karyawan', 'karyawan_id');
    }
    
    public function alasan()
    {
        return $this->belongsTo('App\Alasan', 'alasan_id');
    }
}
