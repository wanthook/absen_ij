<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RequestAlasan extends Model
{
    use SoftDeletes;
    
    protected $table = 'request_alasan';
    
    protected $fillable = [
        'uid_dokumen',
        'file_dokumen',
        'no_dokumen',   
        'tanggal',
        'status',
        'approved_by',
        'approved_at',
        'declined_by',
        'declined_at',
        'declined_note',
        'catatan',
        'deleted_at',
        'created_by', 
        'created_at',
        'updated_by', 
        'updated_at'
    ];
    
    protected $hidden = ['uid_dokumen'];
    
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    public function detail()
    {
        return $this->hasMany('App\RequestAlasanDetail');
    }
    
    public function decline()
    {
        return $this->belongsTo('App\User', 'declined_by');
    }
    
    public function approve()
    {
        return $this->belongsTo('App\User', 'approved_by');
    }
    
}