<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Traits;

/**
 * Description of traitProses
 *
 * @author development
 */
use App\Karyawan;
use App\KaryawanAsuransi;
use App\KaryawanKeluarga;
use App\KaryawanPendidikan;

use App\Jadwal;
use App\Perusahaan;

use App\MasterOption;
use App\Jabatan;
use App\Divisi;
use App\Alasan;
use App\Libur;
use App\Prosesabsen;
use App\ExceptionLog;
use App\Activity;
use App\ActivityManual;


use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\QueryException;
use Auth;
use Validator;

trait TraitGaji
{
    //persen potongan off (Lagi korona nih yeee)
    private $offPercent = 0.25;
    
    private $lemburNilai = 173;
    
    private $bpjsTk = 0.02;
    
    private $bpjsKes = 0.01;
    
    private $bpjsPen = 0.01;
    
    private $hariKerja = 30;
    
    
    public function jumlahPotonganAbsen($karyawan, $periode)
    {
        $diff = (reset($periode)->startOfDay())->diffInDays(end($periode)->startOfDay(),false)+1;

        if($periode && $karyawan)
        {
            // $karyawan = Karyawan::find($karyawan_id);
            
            $abs = [
                    'I' => 0, 'M' => 0, 'H2' => 0, 'TA' => 0, 'GP' => 0,
                    'IN' => 0, 'OUT' => 0
                ];
            
            if($karyawan->prosesabsen->count() > 0)
            {
                foreach($karyawan->prosesabsen()->whereBetween('tanggal', [reset($periode)->toDateString(), end($periode)->toDateString()])->get() as $proses)
                {
                    if($proses->alasan_id != null)
                    {
                        $als = Alasan::whereIn('id', $proses->alasan_id)->get();
                        foreach($als as $vAls)
                        {
                            if(isset($abs[$vAls->kode]))
                            {
                                $abs[$vAls->kode] += 1;
                            }
                        }
                        
                    }
                }
            }

            if($diff>30)
            {
                if($abs['IN']>=10)
                {
                    $abs['IN'] = $abs['IN'] - 1;
                }

                if($abs['OUT']>=22)
                {
                    $abs['OUT'] = $abs['OUT'] - 1;
                }

                if($abs['H2']==31)
                {
                    $abs['H2'] = 30;
                }
            }
            // else
            // {
            //     $yr = (int)reset($periode)->format('Y');
            //     if($yr%4 == 0)
            //     {

            //     }
            // }
            
            return $abs;
        }
        return false;
    }
    
    public function gajiPokok($karyawan, $periode)
    {
        $arr = array();
        $diffDt = array();
        $dtList = array();
        
        $oldNilai = 0;
        $i = 0;
        foreach($periode as $k => $dt)
        {                        
            if($gp = $karyawan->salaryGapokTanggal($dt->toDateString())->first())
            {
                if($oldNilai != $gp->pivot->nilai)
                {
                    if($oldNilai != 0)
                    {
                        $i++;
                    }
                    $oldNilai = $gp->pivot->nilai;
                    // $diffDt[$i] = $dt->toDateString();
                    $dtList[$i] = [
                        'tanggal' => $dt,
                        'tanggalEnd' => null,
                        'gapok' => $gp->pivot->nilai
                    ];
                }
                else
                {
                    $dtList[$i]['tanggalEnd'] = $dt;
                }
            }
            else
            {
                return false;
            }
            
            
        }
        return $dtList;
//        $arr = ['diffDate' => $diffDt, 'data' => $dtList];
//        dd($arr);
    }
    
    public function jumlahOff($karyawan, $periode)
    {
        $jml = 0;
        if($periode && $karyawan)
        {
            // $karyawan = Karyawan::find($karyawan_id);
            
            
            if($karyawan->prosesabsen->count() > 0)
            {
                foreach($karyawan->prosesabsen()->whereBetween('tanggal', [reset($periode)->toDateString(), end($periode)->toDateString()])->get() as $proses)
                {
                    if($proses->alasan_id != null)
                    {
                        foreach($proses->alasan_id as $pAls)
                        {
                            if($als = Alasan::find($pAls))
                            {
                                if($als->kode == 'OFF')
                                {
                                    $jml += 1;
                                }
                            }      
                        }                  
                    }
                }
            }
        }
            
        return $jml;
    }
    
    public function jumlahHariKerja($karyawan, $periode, $tipe = 'normal')
    {
        if($tipe == 'normal')
        {
            return $this->hariKerja;
        }
        else if($tipe == 'khusus')
        {
            $totLibur = Prosesabsen::where('karyawan_id', $karyawan->id)
                                   ->where(function($q)
                                   {
                                       $q->where('kode_jam_kerja', 'L')
                                         ->orWhere('libur_nasional', 1);
                                   })
                                   ->whereNull('lembur_ln')
                                   ->whereBetween('tanggal', [reset($periode)->toDateString(), end($periode)->toDateString()])->count();
                                                       
            return ($totLibur > 0)?($this->hariKerja - $totLibur):0;
        }
        return 0;
    }
    
    public function cekTunjanganHadir($karyawan, $periode)
    {
        if($periode && $karyawan)
        {
            // $karyawan = Karyawan::find($karyawan_id);
            
            $abs = [
                    'M','I','SD','GP','H2','H1','D2','D3','TA','IN','OUT','OFF', 'SKK'
                ];
            
            if($karyawan->prosesabsen->count() > 0)
            {
                foreach($karyawan->prosesabsen()->whereBetween('tanggal', [reset($periode)->toDateString(), end($periode)->toDateString()])->get() as $proses)
                {
                    if($proses->alasan_id != null)
                    {
                        $als = Alasan::whereIn('id', $proses->alasan_id)->get();
                        foreach($als as $vAls)
                        {
                            if(in_array($vAls->kode, $abs))
                            {
                                return false;
                            }
                        }
                        
                    }
                }
            }
        }
        
        return true;
    }

    public function cekHamil($karyawan)
    {
        if($karyawan)
        {
            if($karyawan->hamil == 'Y')
            {
                return true;
            }
        }
        return false;
    }
    
    public function cekTunjanganHaid($karyawan, $periode)
    {
        $tunjangan = true;
        if($periode && $karyawan)
        {
            // $karyawan = Karyawan::find($karyawan_id);

            //tidak berjenis kelamin laki-laki
            if($karyawan->jeniskelamin()->first()->nama == 'P')
            {
                //tidak sedang hamil
                if($karyawan->hamil != 'Y')
                {
                    foreach($karyawan->prosesabsen()->whereBetween('tanggal', [reset($periode)->toDateString(), end($periode)->toDateString()])->get() as $proses)
                    {
                        if($proses->alasan_id != null)
                        {
                            foreach($proses->alasan_id as $pAls)
                            {
                                $iAls = Alasan::find($pAls);
                                //tidak ada cuti haid, cuti melahirkan, out dan in
                                if(in_array($iAls->kode, ['H1', 'H2', 'IN', 'OUT']))
                                {
                                    $tunjangan = false;
                                }
                                else
                                {
                                    if($karyawan->tanggal_masuk)
                                    {
                                        $tglMasuk = Carbon::createFromFormat('Y-m-d',$karyawan->tanggal_masuk);
                                        $firstPeriode = reset($periode);

                                        if($tglMasuk->diffInMonths($firstPeriode, false) >= 3)
                                        {
                                            if($karyawan->active_status != '1')
                                            {
                                                $tunjangan = false;
                                            }
                                        }

                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    $tunjangan = false;
                }
            }
            else
            {
                $tunjangan = false;
            }
        }
        else
        {
            $tunjangan = false;
        }
        return $tunjangan;
    }

    public function tunjanganJabatan($karyawan, $periode)
    {
        // $karyawan = Karyawan::find($karyawan_id);
        if(!$karyawan)
        {
            return false;
        }

        $jabatan = $karyawan->salaryJabatanTanggal(reset($periode)->toDateString())->first();

        if($jabatan)
        {
            return $jabatan->pivot->nilai;
        }
        else
        {
            return 0;
        }
    }

    public function getGetPass($karyawan, $periode)
    {
        $ret = [];
        // $karyawan = Karyawan::find($karyawan_id);
        if(!$karyawan)
        {
            return false;
        }

        if($karyawan->prosesabsen->count() > 0)
        {
            foreach($karyawan->prosesabsen()->whereBetween('tanggal', [reset($periode)->toDateString(), end($periode)->toDateString()])->get() as $proses)
            {
                if($proses->gp > 0)
                {
                    $jamMasuk = Carbon::createFromFormat('Y-m-d H:i:s', $proses->tanggal.' '.$proses->jadwal_jam_masuk);
                    $jamKeluar = Carbon::createFromFormat('Y-m-d H:i:s', $proses->tanggal.' '.$proses->jadwal_jam_keluar);

                    $jadwalJamKerja = $jamMasuk->diffInMinutes($jamKeluar, false) / 60;
                    if($jadwalJamKerja < 0)
                    {
                        $jamKeluar->addDay();
                        $jadwalJamKerja = $jamMasuk->diffInMinutes($jamKeluar, false) / 60;
                    }

                    if($proses->pendek)
                    {
                        $jadwalJamKerja -= 0.5;
                    }
                    else
                    {
                        $jadwalJamKerja -= 1;
                    }
                    
                    $ret[] = [
                        'tanggal' => Carbon::createFromFormat('Y-m-d', $proses->tanggal),
                        'gp' => $proses->gp,
                        'jumlah_jam_kerja' => $proses->jumlah_jam_kerja,
                        'jadwal_jam_kerja' => $jadwalJamKerja,
                        'jadwal_jam_masuk' => $proses->jadwal_jam_masuk,
                        'jadwal_jam_keluar' => $proses->jadwal_jam_keluar,
                        'jam_masuk' => $proses->jam_masuk,
                        'jam_keluar' => $proses->jam_keluar,
                        'pendek' => $proses->pendek
                    ];
                }
            }
        }

        return $ret;

    }

    public function pph21($karyawan, $periode)
    {
        return 0;
    }

    public function tunjanganPrestasi($karyawan, $periode)
    {
        // $karyawan = Karyawan::find($karyawan_id);
        if(!$karyawan)
        {
            return false;
        }

        $prestasi = $karyawan->salaryPrestasiTanggal(reset($periode)->toDateString())->first();

        if($prestasi)
        {
            return $prestasi->pivot->nilai;
        }
        else
        {
            return 0;
        }
    }

    public function s3($karyawan, $periode)
    {
        // $karyawan = Karyawan::find($karyawan_id);
        if(!$karyawan)
        {
            return false;
        }
        $sf3 = 0;
        foreach($karyawan->prosesabsen()->whereBetween('tanggal', [reset($periode)->toDateString(), end($periode)->toDateString()])->where('shift3', 1)->get() as $ks)
        {
            if(isset($ks->jam_masuk) && isset($ks->jam_keluar))
            {
                $sf3++;
            }
        }

        return $sf3;
    }

    public function lembur($karyawan, $periode)
    {
        // $karyawan = Karyawan::find($karyawan_id);
        if(!$karyawan)
        {
            return false;
        }
        $lembur = 0;
        foreach($karyawan->prosesabsen()->whereBetween('tanggal', [reset($periode)->toDateString(), end($periode)->toDateString()])->get() as $ks)
        {
            if(isset($ks->jam_masuk) && isset($ks->jam_keluar))
            {
                if($ks->total_lembur)
                {
                    $lembur += $ks->total_lembur;
                }
            }
        }

        return $lembur;
    }
    
    public function koreksi($karyawan, $periode)
    {
        $arr = array();
        $koreksiPlus = 0;
        $koreksiMinus = 0;
        
        $oldNilai = 0;
        
        $kor = $karyawan->salaryKoreksiTanggal(reset($periode)->toDateString(), end($periode)->toDateString())->get();

        if($kor)
        {
            foreach($kor as $val)
            {
                if($val->pivot->tipe == 'kredit')
                {
                    $koreksiPlus += $val->pivot->nilai;
                }
                else if($val->pivot->tipe == 'debit')
                {
                    $koreksiMinus += $val->pivot->nilai;
                }
            }
        }

        return array('kor_plus' =>$koreksiPlus, 'kor_min' => $koreksiMinus);
    }
    
    public function serikat($karyawan, $periode)
    {
        $arr = array();
        $serikatNama = "";
        $serikatRp = 0;
        
        $ser = $karyawan->salarySerikatTanggal(reset($periode)->toDateString(), end($periode)->toDateString())->get();

        if($ser)
        {
            foreach($ser as $val)
            {
                foreach($ser as $val)
                {
                    $serikatNama  = $val->nama;
                    $serikatRp = (int)$val->pivot->nilai;
                    
                }
            }
        }

        return array('serikat_nama' =>$serikatNama, 'serikat_rp' => $serikatRp);
    }
    
    public function toko($karyawan, $periode)
    {
        $arr = array();
        $tokoRp = 0;
        
        $tok = $karyawan->salaryTokoTanggal(reset($periode)->toDateString(), end($periode)->toDateString())->get();

        if($tok)
        {
            foreach($tok as $val)
            {
                $tokoRp += (int)$val->pivot->nilai;
            }
        }

        return $tokoRp;
    }
    
    public function asuransi($karyawan, $periode)
    {
        $arr = array();
        $asuransiNama = "";
        $asuransiRp = 0;
        
        $ser = $karyawan->salaryAsuransiTanggal(reset($periode)->toDateString(), end($periode)->toDateString())->get();

        if($ser)
        {
            foreach($ser as $val)
            {
                $asuransiNama  = $val->nama;
                $asuransiRp = (int)$val->pivot->nilai;
                
            }
        }

        return array('asuransi_nama' =>$asuransiNama, 'asuransi_rp' => $asuransiRp);
    }
    
    public function others($karyawan, $periode)
    {
        $arr = array();
        $otherNama = "";
        $otherRp = 0;
        
        $ser = $karyawan->salaryOtherTanggal(reset($periode)->toDateString(), end($periode)->toDateString())->get();

        if($ser)
        {
            foreach($ser as $val)
            {
                $otherNama  = $val->nama;
                $otherRp = (int)$val->pivot->nilai;
                
            }
        }

        return array('other_nama' =>$otherNama, 'other_rp' => $otherRp);
    }

    public function absensiList($karyawan, $periode)
    {
        if($periode && $karyawan)
        {
            // $karyawan = Karyawan::find($karyawan_id);
            
            $abs = [
                    'C' => 0, 'D1' => 0, 'D2' => 0, 'D3' => 0, 'SD' => 0, 'SKK' => 0,
                    'P1' => 0, 'M' => 0, 'H1' => 0, 'H2' => 0, 'TA' => 0, 'GP' => 0,
                    'IN' => 0, 'OUT' => 0
                ];
            
            if($karyawan->prosesabsen->count() > 0)
            {
                foreach($karyawan->prosesabsen()->whereBetween('tanggal', [reset($periode)->toDateString(), end($periode)->toDateString()])->get() as $proses)
                {
                    if($proses->alasan_id != null)
                    {
                        $als = Alasan::whereIn('id', $proses->alasan_id)->get();
                        foreach($als as $vAls)
                        {
                            if(isset($abs[$vAls->kode]))
                            {
                                $abs[$vAls->kode] += 1;
                            }
                        }
                        
                    }
                }
            }
            
            return $abs;
        }
        return false;
    }
    
    public function prosesGaji($karyawan_id, $periodevar, $tipe = 'normal')
    {
        $karyawan = Karyawan::find($karyawan_id);
        if(!$karyawan)
        {
            return false;
        }
        $ret = [];

        $jumlahOff = 0;
        $hariKerja = 0;

        /*
        $gapok
        array:1 [
            0 => array:2 [
                "tanggal" => "2020-10-22"
                "gapok" => "4500000"
            ]
            ]
        */
        
        $gapok = $this->gajiPokok($karyawan, $periodevar);
        if(empty($gapok))
        {
            return [];
        }
        // $gajiPokok = (float)(($gapok)?$gapok[0]['gapok']:0);
        foreach($gapok as $gapokKey => $gapokVal)
        {
            $prosesSave = [];

            $gajiPokok = (int) $gapokVal['gapok'];
            $periode = [0 => $gapokVal['tanggal'], 1 => $gapokVal['tanggalEnd']];
            $jumlahOff = $this->jumlahOff($karyawan, $periode);

            if($tipe == 'khusus')
            {
                $hariKerja = $this->jumlahHariKerja($karyawan, $periode, 'khusus') - $jumlahOff;
            }
            else
            {
                if(count($gapok) > 1)
                {
                    if($gapokKey == 0)
                    {
                        $hariKerja = 9;
                    }
                    else
                    {
                        $hariKerja = 21;
                    }
                }
                else
                {
                    $hariKerja = $this->jumlahHariKerja($karyawan, $periode);
                }
            }

            /*
            $potAbsArr
            array:7 [
                "I" => 0
                "M" => 0
                "H2" => 0
                "TA" => 0
                "GP" => 0
                "IN" => 0
                "OUT" => 0
            ]
            */
            $potAbsArr = $this->jumlahPotonganAbsen($karyawan, $periode);
            // unset($potAbsArr['GP']);
            $potonganAbsen = array_sum($potAbsArr) + ($jumlahOff * 0.75);


                    
            //kalau false, gak dapet
            $tunjanganHadir = 0;
            if($this->cekTunjanganHadir($karyawan, $periode))
            {
                $tunjanganHadir = (float)MasterOption::where('nama', 'TUNHADIR')->first()->nilai;
            }

            $tunjanganJabatan = $this->tunjanganJabatan($karyawan, $periode);
            
            $tunjanganHaid = 0;
            if($this->cekTunjanganHaid($karyawan, $periode))
            {
                $tunjanganHaid = (float)MasterOption::where('nama', 'TUNHAID')->first()->nilai;
            }

            $harian = $gajiPokok/$hariKerja;
            // dd($harian);
            $potonganAbsenRp = $harian*$potonganAbsen;
            $jumlahOffRp = (float)$harian*$jumlahOff*$this->offPercent;
            

            $getPass = $this->getGetPass($karyawan, $periode);
            // dd($getPass);
            $gp = 0;
            $gpJkerja = 0;
            $gpRp = 0;
            $thread = [];
            if($getPass)
            {
                foreach($getPass as $gpF)
                {
                    $jadJamKerja = $gpF['jadwal_jam_kerja'];
                    // $gp += $gpF['gp'];
                    $gp += $gpF['jumlah_jam_kerja'];
                    $pembagiGp = $jadJamKerja;
                    if($gpF['pendek'])
                    {
                        $pembagiGp = 5;
                    }
                    $jaman = $harian / $pembagiGp ;
                    $gpRp += ($jaman * $gpF['jumlah_jam_kerja']);
                    // $thread[] = ['gp' => $gpF['gp'], 'jaman' => $jaman, 'gpRp' => $gpRp, 'jadJamKerja' => $jadJamKerja];
                    // $gpJkerja += $gpF['jadwal_jam_kerja'];
                }

                // dd($thread);
            }

            

            $tunjanganPrestasi = $this->tunjanganPrestasi($karyawan, $periode);

            $tunjS3 = (float)MasterOption::where('nama', 'TUNJS3')->first()->nilai;
            $s3 = $this->s3($karyawan, $periode);
            $s3Rp = $tunjS3 * $s3;

            $lembur = $this->lembur($karyawan, $periode);
            $lemburRp = 0;
            if(config('global.perusahaan_short') == 'Indah Jaya')
            {
                $lemburRp = (int) ($lembur / $this->lemburNilai * ($gajiPokok + $tunjanganJabatan + $tunjanganPrestasi));
            }
            else
            {
                $lemburRp = (int) ($lembur / $this->lemburNilai * ($gajiPokok + $tunjanganJabatan));
            }

            /*
            $koreksi
            array:2 [
                "kor_plus" => 0
                "kor_min" => 0
            ]

            */
            $koreksi = $this->koreksi($karyawan, $periode);

            $gajiPokokDibayar = (int)($gajiPokok - $potonganAbsenRp);

            $brutto = (int) ($gajiPokokDibayar + 
                    $lemburRp + $s3Rp + $jumlahOffRp +
                    $tunjanganJabatan + $tunjanganPrestasi + 
                    $tunjanganHaid + $tunjanganHadir + $gpRp +
                    $koreksi['kor_plus'] - $koreksi['kor_min']);
                    
            $bpjsTk = ($this->bpjsTk * ($gajiPokok + $tunjanganJabatan));
            $bpjsPen = ($this->bpjsPen * ($gajiPokok + $tunjanganJabatan));
            $bpjsKes = ($this->bpjsKes * ($gajiPokok + $tunjanganJabatan));

            $pph21 = $this->pph21($karyawan, $periode);

            /*
            $serikat
            array:2 [
                "serikat_nama" => 0
                "serikat_rp" => 0
            ]

            */
            $serikat = $this->serikat($karyawan, $periode);

            $asuransi = $this->asuransi($karyawan, $periode);

            $toko = $this->toko($karyawan, $periode);

            $other = $this->others($karyawan, $periode);

            $absensiList = $this->absensiList($karyawan, $periode);
            
            $absensiList['off'] = $jumlahOff;

            $prosesSave['periode_awal'] = reset($periode)->toDateString();
            $prosesSave['periode_akhir'] = end($periode)->toDateString();
            $prosesSave['karyawan_id'] = $karyawan_id;
            $prosesSave['gaji_pokok'] = $gajiPokok;
            $prosesSave['gaji_pokok_dibayar'] = $gajiPokokDibayar;
            $prosesSave['harian_rp'] = $harian;
            $prosesSave['tunjangan_jabatan'] = (float)$tunjanganJabatan;
            $prosesSave['tunjangan_prestasi'] = $tunjanganPrestasi;
            $prosesSave['tunjangan_haid'] = $tunjanganHaid;
            $prosesSave['tunjangan_hadir'] = $tunjanganHadir;
            $prosesSave['i'] = $potAbsArr['I'];
            $prosesSave['m'] = $potAbsArr['M'];
            $prosesSave['h2'] = $potAbsArr['H2'];
            $prosesSave['ta'] = $potAbsArr['TA'];
            $prosesSave['in'] = $potAbsArr['IN'];
            $prosesSave['out'] = $potAbsArr['OUT'];
            
            // $prosesSave['gp'] = $gp;
            $prosesSave['periode_hari'] = $hariKerja;
            $prosesSave['jumlah_absen'] = $hariKerja-$potonganAbsen;

            $prosesSave['potongan_absen'] = $potonganAbsen;
            $prosesSave['potongan_absen_rp'] = $potonganAbsenRp;
            $prosesSave['jumlah_off'] = $jumlahOff;
            $prosesSave['jumlah_off_rp'] = $jumlahOffRp;
            $prosesSave['gp'] = $gp;
            $prosesSave['gp_rp'] = $gpRp;
            $prosesSave['s3'] = $s3;
            $prosesSave['s3_rp'] = $s3Rp;
            $prosesSave['lembur'] = $lembur;
            $prosesSave['lembur_rp'] = $lemburRp;
            $prosesSave['koreksi_plus'] = $koreksi['kor_plus'];
            $prosesSave['koreksi_minus'] = $koreksi['kor_min'];
            $prosesSave['bruto_rp'] = $brutto;

            $prosesSave['bpjs_tk'] = $bpjsTk;
            $prosesSave['bpjs_pen'] = $bpjsPen;
            $prosesSave['bpjs_kes'] = $bpjsKes;
            $prosesSave['pph21'] = $pph21;

            $prosesSave['cost_serikat_nama'] = $serikat['serikat_nama'];
            $prosesSave['cost_serikat_rp'] = $serikat['serikat_rp'];
            
            $prosesSave['cost_asuransi_nama'] = $asuransi['asuransi_nama'];
            $prosesSave['cost_asuransi_rp'] = $asuransi['asuransi_rp'];

            $prosesSave['toko'] = $toko;

            $prosesSave['lainlain'] = $other['other_rp'];

            $jumlahPotongan = $bpjsTk + $bpjsPen + $bpjsKes + $pph21 + $serikat['serikat_rp'] + $asuransi['asuransi_rp']+ $toko;
            $prosesSave['tot_potongan'] = $jumlahPotongan;
            $totalAkhir = (int)($brutto - $jumlahPotongan);
            $prosesSave['tot_akhir'] = $totalAkhir;

            $tAkh = $totalAkhir / 100;

            $totalDibayar = (int)(round($tAkh) * 100);

            $prosesSave['tot_bayar'] = $totalDibayar;

            $prosesSave['absensi'] = json_encode($absensiList);

            $ret[] = $prosesSave;
        }
        return $ret;

    }

    /*
     * @param 
     * 
     *
     */
    public function calculateGaji($param)
    {

    }
}