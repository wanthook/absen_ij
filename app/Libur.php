<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Libur extends Model
{
    use SoftDeletes;
    
    protected $table = 'liburs';
    
    protected $fillable = [
        'tanggal',
        'keterangan',
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
