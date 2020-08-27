<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExceptionLog extends Model
{
    protected $table = 'exception_log';
    
    public $timestamps = false;
    
    protected $fillable = [
        'file_target',
        'message_log',
        'deleted_at',
        'created_by',
        'created_at'
    ];
}
