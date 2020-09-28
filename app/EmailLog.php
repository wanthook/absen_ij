<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    
    protected $table = 'email_log';
    
    protected $fillable = [
        'app_id',
        'app_path',
        'email',
        'created_by', 
        'created_at'
    ];
}
