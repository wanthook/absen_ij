<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityManual extends Model
{
    
    use SoftDeletes;
    
    protected $table = 'activity_manuals';
    
    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'keterangan',
        'mangkir',
        'deleted_at',
        'created_by',
        'updated_by',
        'created_at',
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
        return $this->belongsTo('App\Karyawan')->with(['divisi']);
    }
}
