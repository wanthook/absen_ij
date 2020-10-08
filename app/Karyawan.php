<?php

namespace App;

use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Karyawan extends Model
{
    use NodeTrait;
    use SoftDeletes;
    
    protected $table = 'karyawans';
    
    protected $fillable = [
        'parent_id',
        '_lft',
        '_rgt',
        'pin',
        'key',
        'nik',
        'nama',
        'npwp',
        'rekening',
        'foto',
        'email',
        'ktp',
        'tempat_lahir',
        'tanggal_lahir',
        'telpon',
        'hp',
        'kota',
        'kode_pos',
        'alamat',
        'tanggal_masuk',
        'tanggal_probation',
        'tanggal_kontrak',
        'ukuran_baju',
        'ukuran_sepatu',
        'status_karyawan_id',
        'jenis_kelamin_id',
        'perkawinan_id',
        'darah_id',
        'jabatan_id',
        'divisi_id',
        'golongan_id',
        'jadwal_id',
        'perusahaan_id',
        'agama_id',
        'active_status',
        'active_comment',
        'active_status_date',
        'off_status',
        'off_id',
        'off_comment',
        'off_date',
        'jumlah_anak',
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
    
    public function log_divisi()
    {
        return $this->belongsToMany('App\Divisi','divisi_karyawan_log','karyawan_id','divisi_id')
                    ->withPivot('tanggal', 'keterangan', 'created_by', 'updated_by', 'created_at', 'updated_at')
                    ->orderBy('tanggal', 'desc');
    }
    
    public function log_golongan()
    {
        return $this->belongsToMany('App\MasterOption','golongan_karyawan_log','karyawan_id','golongan_id')
                    ->withPivot('tanggal', 'keterangan', 'created_by', 'updated_by', 'created_at', 'updated_at')
                    ->orderBy('tanggal', 'desc');
    }
    
    public function log_off()
    {
        return $this->belongsToMany('App\Alasan','off_karyawan_log','karyawan_id','off_id')
                    ->withPivot('tanggal', 'keterangan', 'created_by', 'updated_by', 'created_at', 'updated_at')
                    ->orderBy('tanggal', 'desc');
    }
    
    public function logOffTanggal($tanggal)
    {
        return $this->log_off()->wherePivot('tanggal','<=', $tanggal);
    }
    
    public function prosesabsen()
    {
        return $this->hasMany('App\Prosesabsen');
    }

    public function jadwal_manual()
    {
        return $this->belongsToMany('App\JamKerja')
                    ->withPivot('tanggal','created_by','created_at');
    }
    
    
    
    public function jadwals()
    {
        return $this->belongsToMany('App\Jadwal')
                    ->withPivot('tanggal','keterangan')->orderBy('tanggal', 'desc');
    }
    
    public function jadwalsTanggal($tanggal)
    {
        return $this->jadwals()
                    ->wherePivot('tanggal', '<=',$tanggal);
    }
    
    public function jadwalManualTanggal($tanggal)
    {
        return $this->belongsToMany('App\JamKerja')
                    ->withPivot('tanggal','created_by','created_at')->wherePivot('tanggal', $tanggal);
    }
    
    public function salary()
    {
        return $this->belongsToMany('App\MasterOption','salaries','karyawan_id','jenis_id')
                ->using(SubTransaction::class)
                ->withPivot('tanggal', 'nilai', 'tipe', 'created_by', 'created_at')
                ->orderBy('tanggal', 'desc')->orderBy('jenis_id', 'asc');
    }
    
    public function gapok()
    {
        return $this->belongsToMany('App\MasterOption','salaries','karyawan_id','jenis_id')
                ->using(SubTransaction::class)
                ->withPivot('tanggal', 'nilai', 'tipe', 'created_by', 'created_at')
                ->where('master_options.nama','GAPOK')
                ->orderBy('tanggal', 'desc')->orderBy('jenis_id', 'asc');
    }
    
    public function salaryGapokTanggal($tanggal)
    {
        return $this->gapok()->wherePivot('tanggal', '<=', $tanggal);
    }
    
    public function jabatan()
    {
        return $this->belongsTo("App\Jabatan", "jabatan_id");
    }
    
    public function divisi()
    {
        return $this->belongsTo("App\Divisi", "divisi_id");
    }
    
    public function golongan()
    {
        return $this->belongsTo("App\MasterOption", "golongan_id");
    }
    
    public function perusahaan()
    {
        return $this->belongsTo("App\Perusahaan", "perusahaan_id");
    }
    
    public function status()
    {
        return $this->belongsTo("App\MasterOption", "status_karyawan_id");
    }
    
    public function nikah()
    {
        return $this->belongsTo("App\MasterOption", "perkawinan_id");
    }
    
    public function darah()
    {
        return $this->belongsTo("App\MasterOption", "darah_id");
    }
    
    public function jeniskelamin()
    {
        return $this->belongsTo("App\MasterOption", "jenis_kelamin_id");
    }
    
    public function jadwal()
    {
        return $this->belongsTo("App\Jadwal", "jadwal_id");
    }
    
    public function log()
    {
        return $this->hasMany('App\Activity', 'mesin_id');
    }
    
    public function alasan()
    {
        return $this->belongsToMany('App\Alasan')
                    ->withPivot('tanggal','waktu', 'keterangan','created_by','created_at');
    }
    
    public function agama()
    {
        return $this->belongsTo('App\MasterOption','agama_id');
    }
    
    public function alasanTanggal($tanggal)
    {
        return $this->alasan()
                    ->wherePivot('tanggal', $tanggal);
    }
    
    public function absenManual()
    {
        return $this->hasMany('App\ActivityManual');
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

    public function scopeKaryawanAktif($query)
    {
        $query->where('active_status', 1);
        
        return $query;
    }
    
    public function offAlasan()
    {
        return $this->belongsTo('App\Alasan', 'off_id');
    }

    public function scopeAuthor($query)
    {
        if(Auth::user()->type->nama == 'REKANAN')
        {
            $query->where('perusahaan_id', Auth::user()->perusahaan->id);
        }
        
        return $query;
    }
}
