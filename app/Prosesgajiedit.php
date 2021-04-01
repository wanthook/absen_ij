<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Prosesgajiedit extends Model
{
    protected $table = 'prosesgajiedits';
    
    protected $fillable = [
        'gaji_pokok',
        'gaji_pokok_dibayar',
        'harian_rp',
        'tunjangan_jabatan',
        'tunjangan_prestasi',
        'tunjangan_haid',
        'tunjangan_hadir',
        'tunjangan_lain',
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
        'prosesgaji_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];  
}
