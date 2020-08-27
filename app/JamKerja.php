<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class JamKerja extends Model
{
    use SoftDeletes;
    
    protected $table = 'jam_kerjas';
    
    protected $fillable = [
        'kode',
        'jam_masuk',
        'jam_keluar',
        'libur',
        'pendek',
        'istirahat',
        'warna',
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

    public function getJamMasukAttribute($value)
    {
        if(empty($value) || is_null($value))
        {
            return "";
        }
        return Carbon::parse($value)->format('H:i');
    }
    
    public function formJamMasukAttribute($value)
    {
        if(empty($value) || is_null($value))
        {
            return "";
        }
        
        return Carbon::parse($value)->format('H:i');
    }
    
    public function getJamKeluarAttribute($value)
    {
        if(empty($value) || is_null($value))
        {
            return "";
        }
        return Carbon::parse($value)->format('H:i');
    }
    
    public function formJamKeluarAttribute($value)
    {
        if(empty($value) || is_null($value))
        {
            return "";
        }
        
        return Carbon::parse($value)->format('H:i');
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
