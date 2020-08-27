<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class KaryawanKeluarga extends Model
{    
    use SoftDeletes;
    
    protected $table = 'karyawan_keluargas';
    
    protected $fillable = [
        'nama',
        'ktp',
        'tempat_lahir',
        'tanggal_lahir',
        'telpon',
        'kota',
        'kode_pos',
        'alamat',
        'relasi_id',
        'jenis_kelamin_id',
        'agama_id',
        'karyawan_id',
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
    
    public function relasi()
    {
        return $this->belongsTo('App\MasterOption','relasi_id');
    }
    
//    public function jenkel()
//    {
//        return $this->hasOne('App\MasterOptions','jenis_kelamin_id');
//    }
    
//    public function agama()
//    {
//        return $this->hasOne('App\MasterOptions','agama_id');
//    }
    
//    public function getTanggalLahirAttribute($value)
//    {
//        if(empty($value) || is_null($value))
//        {
//            return "";
//        }
//        return Carbon::parse($value)->format('d-m-Y');
//    }
//    
//    public function formTanggalLahirAttribute($value)
//    {
//        if(empty($value) || is_null($value))
//        {
//            return "";
//        }
//        
//        return Carbon::parse($value)->format('d-m-Y');
//    }
    
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
