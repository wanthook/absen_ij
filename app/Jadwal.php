<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jadwal extends Model
{
    use SoftDeletes;
    
    protected $table = 'jadwals';
    
    protected $fillable = [
        'kode',
        'tipe',
        'deskripsi',
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

    public function jadwal_kerja()
    {
        return $this->belongsToMany('App\JamKerja')
                    ->withPivot('day','tanggal','created_by','created_at')
                    ->orderBy('tanggal', 'asc');
    }
    
    public function jadwalKerjaDay($hari)
    {
        return $this->jadwal_kerja()->wherePivot('day', $hari);
    }
    
    public function jadwalKerjaShift($tanggal)
    {
        return $this->jadwal_kerja()->wherePivot('tanggal', $tanggal);
    }

//    public function jadwal_kerja_shift($sDt)
//    {
//        return $this->jadwal_kerja()
//                    ->wherePivot('tanggal', $sDt);
//    }
    
//    public function scopeJadwalKerjaShift($query, $dt)
//    {
//        return $query->wherePivot('tanggal', $dt);
//    }
    
    public function karyawan()
    {
        return $this->hasMany('App\Karyawan');
    }
    
    public function getCreatedAtAttribute($value)
    {
        if(empty($value) || is_null($value))
        {
            return "";
        }
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
    
    public function formCreatedAtAttribute($value)
    {
        if(empty($value) || is_null($value))
        {
            return "";
        }
        
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
    
    public function getUpdatedAtAttribute($value)
    {
        if(empty($value) || is_null($value))
        {
            return "";
        }
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
    
    public function formUpdatedAtAttribute($value)
    {
        if(empty($value) || is_null($value))
        {
            return "";
        }
        
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
    
    public function createdBy()
    {
        return $this->belongsTo('App\User','created_by');
    }
    
    public function updatedBy()
    {
        return $this->belongsTo('App\User','updated_by');
    }
}
