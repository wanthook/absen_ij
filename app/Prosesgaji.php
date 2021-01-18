<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Prosesgaji extends Model
{
    protected $table = 'prosesgajis';
    
    protected $fillable = [
        'periode_awal',
        'periode_akhir',
        'karyawan_id',
        'periode_hari',
        'gaji_pokok',
        'gaji_pokok_dibayar',
        'harian_rp',
        'tunjangan_jabatan',
        'tunjangan_prestasi',
        'tunjangan_haid',
        'tunjangan_hadir',
        'tunjangan_lain',
        'i',
        'm',
        'h2',
        'ta',
        'in',
        'out',
        'jumlah_absen',
        'potongan_absen',
        'potongan_absen_rp',
        'jumlah_off',
        'jumlah_off_rp',
        'lembur',
        'lembur_rp',
        's3',
        's3_rp',
        'gp',
        'gp_rp',
        'koreksi_plus',
        'koreksi_minus',
        'bruto_rp',
        'bpjs_tk',
        'bpjs_kes',
        'bpjs_pen',
        'pph21',
        'cost_serikat_nama',
        'cost_serikat_rp',
        'cost_asuransi_nama',
        'cost_asuransi_rp',
        'toko',
        'tot_potongan',
        'tot_akhir',
        'tot_bayar',
        'lainlain',
        'absensi',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];         
    
    public function karyawan()
    {
        return $this->belongsTo("App\Karyawan", "karyawan_id")->with('divisi');
    }
}
