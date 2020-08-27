<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use SoftDeletes;
    
    protected $table = 'module';
    
    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
