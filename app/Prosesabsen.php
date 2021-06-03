<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Prosesabsen extends Model
{
    protected $table = 'prosesabsens';
    
//    protected $casts = [
//        'alasan_id' => 'array',
//    ];
    
    protected $fillable = [
        'karyawan_id',
        'alasan_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'jam_masuk_id',
        'jam_keluar_id',
        'kode_jam_kerja',
        'jadwal_jam_masuk',
        'jadwal_jam_keluar',
        'n_masuk',
        'n_keluar',
        'libur',
        'libur_nasional',
        'pendek',
        'mangkir',
        'ta',
        'lembur_aktual',
        'hitung_lembur',
        'lembur_ln',
        'hitung_lembur_ln',
        'shift3',
        'gp',
        'jumlah_jam_kerja',
        'total_lembur',
        'keterangan',
        'jam_masuk_random',
        'jam_keluar_random',
        'is_off',
        'absen_manual',
        'created_at',
        'updated_at',
        'created_by'
    ];
    
    public function karyawan()
    {
        return $this->belongsTo("App\Karyawan", "karyawan_id")->with('divisi');
    }
    
    public function getAlasanIdAttribute($value)
    {
        return json_decode($value, true);
    }
}
