<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

use Illuminate\Support\Facades\Crypt;

class Jabatan extends Model
{
    use SoftDeletes;
    
    protected $table = 'jabatans';
    
    protected $fillable = [
        'deskripsi',   
        'kode',        
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
    
//    public function getTunjanganAttribute($value)
//    {
//        if(Auth::user()->type->nama == 'ADMIN')
//        {
//            if(!empty($value))
//            {
//                return Crypt::decryptString($value);
//            }
//            else
//            {
//                return null;
//            }
//        }
//        else
//        {
//            return $value;
//        }
//    }
    
//    public function getPrestasiAttribute($value)
//    {
//        if(Auth::user()->type->nama == 'ADMIN')
//        {
//            if(!empty($value))
//            {
//                return Crypt::decryptString($value);
//            }
//            else
//            {
//                return null;
//            }
//        }
//        else
//        {
//            return $value;
//        }
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
