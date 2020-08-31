<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'photo',
        'username',
        'password',
        'name', 
        'email', 
        'ttd_img',
        'type_id',
        'deleted_at',
        'perusahaan_id',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function modules()
    {
        return $this->belongsToMany('App\Module');
    }
    
    public function type()
    {
        return $this->belongsTo('App\MasterOption', 'type_id');
    }
    
    public function perusahaan()
    {
        return $this->belongsTo('App\Perusahaan');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
