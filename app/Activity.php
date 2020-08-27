<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes;
    
    protected $table = 'activities';
    
    protected $fillable = [
        'pin',
        'tanggal',
        'verified',
        'status',
        'workcode',
        'mesin_id',  
        'created_by', 
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    public function mesin()
    {
        return $this->belongsTo('App\Mesin');
    }
    
    public function karyawan()
    {
        return $this->belongsTo('App\Karyawan', 'pin', 'key');
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
