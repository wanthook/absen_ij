<?php

namespace App;

use Kalnoy\Nestedset\NodeTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use SoftDeletes;
    use NodeTrait;
    
    protected $table = 'module';
    
    protected $fillable = [
        'nama',
        'deskripsi',
        'route',
        'param', 
        'parent_id', 
        '_lft',
        '_rgt',
        'icon',
        'deleted_at',   
        'created_by', 
        'created_at',
        'updated_by', 
        'updated_at'
    ];
    
    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
