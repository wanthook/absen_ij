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


trait TraitProses 
{
    private $rangeAbs = 4 * 60;
    public function cekProses($karId, $tanggal)
    {
        $proc = Prosesabsen::where('karyawan_id', $karId)->where('tanggal', $tanggal)->count();
        
        if($proc)
        {
            return true;
        }
        return false;
    }
    
    public function prosesAbsTanggal($karId, $tanggal)
    {
        $tgl = CarbonPeriod::create($tanggal, $tanggal)->toArray();
        
        $this->prosesAbs($karId, $tgl);
    }
    
    public function prosesAbsTanggalRange($karId, $tanggalAwal, $tanggalAkhir)
    {
        $tgl = CarbonPeriod::create($tanggalAwal, $tanggalAkhir)->toArray();
        
        $this->prosesAbs($karId, $tgl);
    }
    
    public function prosesAbs($karId, $tanggal)
    {
        $karyawan = Karyawan::find($karId);
        $tmk = null;
        $active = null;
        $off = null;

        if($karyawan->tanggal_masuk)
        {
            $tmk = Carbon::createFromFormat('Y-m-d', $karyawan->tanggal_masuk);
        }

        if($karyawan->active_status_date)
        {
            $active = Carbon::createFromFormat('Y-m-d', $karyawan->active_status_date);
        }
                

        $jadwal = $this->jadwals($tanggal, $karyawan);

        $jadwalArr = array();

        $proses = Prosesabsen::where('karyawan_id', $karId)
                ->whereBetween('tanggal', [reset($tanggal)->toDateString(), end($tanggal)->toDateString()]);
        
        if($proses->count()>0)
        {
            $proses->delete();
        }
//        dd($proses);
        if(!$jadwal)
        {
            ExceptionLog::create(['file_target' => 'ProsesabsenController.php', 'message_log' => json_encode(['karyawan_id' => $karyawan->id, 
                'message' => 'JADWAL KOSONG']), 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
            return false;
        }

        $jadwalArr = $this->jadwals($tanggal, $karyawan);
        $jadwalManual = $this->jadwalManual($tanggal, $karyawan);
        // dd($jadwalArr);
        if($jadwalArr)
        {
            $arrProses = [];
            foreach($jadwalArr as $key => $val)
            {    
                /*
                 * Ambil Jadwal Manual
                 */
                if($jadwalManual[$key])
                {
                    $val = $jadwalManual[$key];
                }
                /*
                 * End Ambil Jadwal Manual
                 */

                $alasanId = null;

                $jadwalBefore = null;

                $jadMasuk = null;
                $jadKeluar = null;

                $jMasuk = null;
                $jSubMasuk = null;
                $jMasukId = null;

                $jKeluar = null;
                $jSubKeluar = null;
                $jKeluarId = null;

                $nMasuk = null;
                $nKeluar = null;

                $isPendek = null;
                $isMangkir = null;
                $isLibur = null;
                $isTa = null;
                $isInOut = null;
                $isOff = null;

                $lemburAktual = null;
                $hitungLembur = null;
                $shift3 = null;
                $lemburLN = null;
                $hitungLemburLN = null;
                $totalLembur = null;
                $nilaiGp = null;
                $nilaiGpIn = null;
                $nilaiGpOut = null;
                $jumlahJamKerja = null;
                $jumlahActivityKerja = null;

                $absenManual = null;

                $addRangeStart = 0;
                $addRangeEnd = 0;

                $actIn = null;
                $actOut = null;

                $keterangan = null;

                $isSpo      = false;
                $isLn       = false;
                $isLnOff    = false;

                $flagNotInOut = null;
                $pendek = null;
                $kodeJadwal = null;

                if(isset($val->kode))
                {
                    $kodeJadwal = $val->kode;
                }

                if($tmk)
                {
                    if($tmk->diffInDays($key, false) < 0)
                    {
                        $alasanId[] = Alasan::where('kode','IN')->first()->id;
                        $isInOut = 'IN';
                        $keterangan[] = 'IN';
                        goto proses_simpan;
                    }
                }

                if($active)
                {
                    if($active->diffInDays($key, false)>=0)
                    {
                        $alasanId[] = Alasan::where('kode','OUT')->first()->id;                        
                        $isInOut = 'OUT';
                        $keterangan[] = 'OUT';
//                        goto proses_simpan;
                    }
                }
                              
                
                $off = $karyawan->logOffTanggal($key)->first();
                if($off)
                {
                    if($off->kode != 'AKT')
                    {
                        $alasanId[] = $off->id;
                        $isOff = 'Y';
                        $keterangan[] = $off->kode;
//                        goto proses_simpan;
                    }
                }


                if(isset($val->pendek))
                {
                    if($val->pendek == "1")
                    {
                        $pendek = 1;
                    }
                }

                
                /*
                 * Start If
                 * 
                 * Apakah kode jadwal bukan L
                 * Jika Ya, Masukkan nilai jadwal masuk dan pulang
                 * Jika Tidak, Nilai libur akan 1
                 */
                if($kodeJadwal)
                {
                    if($kodeJadwal != 'L')
                    {
                        /*
                        * Absen Manual
                        */                       
                        if($val->jam_masuk && $val->jam_keluar)
                        {
                            $in = Carbon::createFromFormat("Y-m-d H:i:s", $key." ".$val->jam_masuk.":00");
                            $out = Carbon::createFromFormat("Y-m-d H:i:s", $key." ".$val->jam_keluar.":00");
                            
                            /*
                            * cek jadwal shift3
                            */
                            if($in->greaterThan($out))
                            {
                                $out->addDay();
                                $shift3 = 1;
                            }
                        }
                        else
                        {
                            goto proses_simpan;
                        }

                    }
                    else
                    {
                        $isLibur = 1;
                    }
                }
                /*
                 * End If
                 */

                /*
                 * Buat Variable Carbon untuk tanggal current
                 */
                $curDate = Carbon::createFromFormat("Y-m-d", $key);
                /*
                 * End
                 */

                /*
                 * Start If
                 * 
                 * Jika Ya, kode Jadwal adalah D / Dayshift
                 * Ambil jadwal sebelumnya berdasarkan nilai hari
                 * Jika Tidak, kode Jadwal adalah S / Shift
                 * Ambil Jadwal sebelumnya berdasarkan tanggal kemarin
                 */
                // if(isset($jadwalArr[$curDate->copy()->subDays(1)->toDateString()]))
                // {
                //     $jadwalBefore = $jadwalArr[$curDate->copy()->subDays(1)->toDateString()];
                // }
                // else
                // {
                //     $jadwalBeforeArr = $this->jadwals([$curDate->copy()->subDays(1)], $karyawan);
                //     if($jadwalArr)
                //     {
                //         $jadwalBefore = reset($jadwalBeforeArr);
                //     }
                // }

                /*
                 * End If
                 */

                /*
                 * Ambil alasan karyawan pada tanggal current
                 */
                $alasan = $karyawan->alasanTanggal($key);
                
                if(!$alasan->count())
                {
                    $alasan = $karyawan->alasanRangeTanggal($key);
                }
                
                /*
                 * End
                 */

                /*
                 * Ambil libur nasional
                 */
                $lN = Libur::where('tanggal', $key)->first();
                if($lN)
                {
                    $isLn = true;
                }
                /*
                 * End
                 */


                /*
                 * Start If
                 * 
                 * Jika Ya, ada libur nasional
                 */
                if($lN)
                {
                    /*
                     * Start If
                     * 
                     * Jika Ya, ada Libur dan ada alasan
                     */
                    if($alasan->count())
                    {
                        foreach($alasan->get() as $vAlasan)
                        {
                            /*
                             * Start If
                             * 
                             * Jika Ya, ada Libur dan ada alasan
                             * dengan kode alasan LN
                             */
                            if($vAlasan->kode == 'LN')
                            {
                                /*
                                 * Tag LN ada
                                 */
                                $isLn = true;
                                /*
                                 * Start If
                                 * 
                                 * Jika Ya, ada Libur nasional dan ada alasan
                                 * dengan kode alasan LN
                                 * ada jadwal dengan kode L
                                 */
                                if($vAlasan->kode == 'L')
                                {
                                    $isLnOff = true;
                                }
                            }
                            /*
                             * Jika Tidak, ada libur nasional dan ada alasan
                             * tidak ada alasan LN
                             */
                            else
                            {
                                $isLibur = 1;
                            }
                            /*
                             * End if
                             */
                        }
                    }
                    /*
                     * Jika Tidak, ada libur nasional dan tidak ada alasan
                     */
                    else
                    {
                        $isLibur = 1;
                        $keterangan[] = "Libur Nasional";
                    }
                    /*
                     * End if
                     */

                }
                else
                {
                    /*
                     * Jadi ada kondisi di apac yang dimana itu adalah libur nasional namun
                     * tidak diset libur nasional di tanggal libur nasional
                     * tapi ada beberapa karyawan yang di set alasan Libur Nasional.
                     * 23-11-2020
                     */
                    if(isset($alasan))
                    {
                        if($alasan->count())
                        {
                            $iLn = false;
                            foreach($alasan->get() as $al)
                            {
                                if($al->kode == 'LN')
                                {
                                    $iLn = true; break;
                                }
                            }

                            if($iLn)
                            {
                                $isLn = true;
                                $isLibur = 1;
                                $keterangan[] = "Libur Nasional";
                            }
                        }
                    }
                }

                if($alasan->count())
                {
                    foreach($alasan->get() as $vAlasan)
                    {
                        $alasanId[] = $vAlasan->id;
                        switch($vAlasan->kode)
                        {
                            case "SPL": $addRangeEnd = 60 * $vAlasan->pivot->waktu; break;
                            case "SLA": $addRangeStart = 60 * $vAlasan->pivot->waktu; break;
                            case "SPO": $isSpo = true; break;
                        }
                        
                        if($vAlasan->libur == 'Y' && $vAlasan->kode != 'LN')
                        {
                            $keterangan[] = $vAlasan->deskripsi; $isLibur = 1;
                        }
//                        else
//                        {
//                            $keterangan[] = $vAlasan->kode;
//                        }
                    }
                }

                if(!$isLnOff && !$isSpo)
                {
                    if($kodeJadwal)
                    {
                        if($kodeJadwal != 'L')
                        {                        

                            $jadMasuk = $in;
                            $jadKeluar = $out;

                            $jumlahJamKerja = $out->diffInHours($in);

                            $actIn = Activity::where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [
                                        $in->copy()->subMinutes($this->rangeAbs + $addRangeStart)->toDateTimeString(),
                                        $in->copy()->addMinutes($this->rangeAbs)->toDateTimeString()
                                    ])
                                    ->orderBy('tanggal', 'ASC')
                                    ->first();
                            $jMasukId = ($actIn)?$actIn->id:null;

                            $actOut = Activity::where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [
                                        $out->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),
                                        $out->copy()->addMinutes($this->rangeAbs + $addRangeEnd)->toDateTimeString()
                                    ])
                                    ->orderBy('tanggal', 'DESC')
                                    ->first();
                            $jKeluarId = ($actOut)?$actOut->id:null;

                            if($jMasukId)
                            {
                                if(!$jKeluarId)
                                {
                                    if(!$shift3)
                                    {
                                        $actOut = Activity::where('pin', $karyawan->key)
                                                ->whereDate('tanggal', $out->copy()->toDateString())
                                                ->orderBy('tanggal', 'DESC')
                                                ->first();

                                        $flagNotInOut = "out";

                                        $jKeluarId = ($actOut)?$actOut->id:null;
                                    }
                                    else
                                    {
                                        $actOut = Activity::where('pin', $karyawan->key)
                                                ->whereBetween('tanggal', [$out->copy()->toDateString().' 00:00:00', $out->copy()->addDay()->toDateString().' 09:00:00'])
                                                ->orderBy('tanggal', 'DESC')
                                                ->first();

                                        $flagNotInOut = "out";
                                        
                                        $jKeluarId = ($actOut)?$actOut->id:null;
                                    }
                                }
                            }
                            else if($jKeluarId)
                            {
                                if(!$jMasukId)
                                {
                                    if(!$shift3)
                                    {
                                        $actIn = Activity::where('pin', $karyawan->key)
                                                ->whereDate('tanggal', $in->copy()->toDateString())
                                                ->orderBy('tanggal', 'ASC')
                                                ->first();

                                        $flagNotInOut = "in";

                                        $jMasukId = ($actIn)?$actIn->id:null;
                                    }
                                    else
                                    {
                                        $actIn = Activity::where('pin', $karyawan->key)
                                                ->whereBetween('tanggal', [$in->copy()->subMinutes($this->rangeAbs)->toDateTimeString(), $in->copy()->addDay()->toDateString().' 09:00:00'])
                                                ->orderBy('tanggal', 'ASC')
                                                ->first();

                                        $flagNotInOut = "in";

                                        $jMasukId = ($actIn)?$actIn->id:null;                                                
                                    }
                                }
                            }
                        }
                        else
                        {
                            $actIn = null;
                            $actOut = null;
                        }
                    }

                }
                else
                {
                    $inS1 = Carbon::createFromFormat("Y-m-d H:i:s", $key." 07:00:00");
                    $inS2 = Carbon::createFromFormat("Y-m-d H:i:s", $key." 14:00:00");
                    $inS3 = Carbon::createFromFormat("Y-m-d H:i:s", $key." 23:00:00");

                    $outS1 = Carbon::createFromFormat("Y-m-d H:i:s", $key." 15:00:00");
                    $outS2 = $inS3;
                    $outS3 = $inS1->copy()->addDay();

                    $actIn = null;
                    $actOut = null;
                    
                    $tglBefore  = $curDate->copy()->subDay();
                    $jadwalBefore = $this->jadwalSingle($tglBefore, $karyawan);
                    
                    if($jadwalBefore)
                    {
                        $inBefore = Carbon::createFromFormat("Y-m-d H:i:s", $tglBefore->toDateString()." ".$jadwalBefore->jam_masuk.":00");
                        $outBefore = Carbon::createFromFormat("Y-m-d H:i:s", $tglBefore->toDateString()." ".$jadwalBefore->jam_keluar.":00");
                        if($inBefore->greaterThan($outBefore))
                        {
                            $tmpAct = Activity::where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [$inS2->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$inS2->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                    ->orderBy('tanggal', 'ASC')
                                    ->first();
                            /*
                            * apakah shift2
                            */
                            if($tmpAct)
                            {
                                $actIn = $tmpAct;
                                $actOut = Activity::where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [$outS2->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$outS2->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                    ->orderBy('tanggal', 'DESC')
                                    ->first();

                                $jMasukId = ($actIn)?$actIn->id:null;
                                $jKeluarId = ($actOut)?$actOut->id:null;
                            }
                            else
                            {
                                $actIn = Activity::where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [$inS3->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$inS3->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                    ->orderBy('tanggal', 'ASC')
                                    ->first();
                                
                                $actOut = Activity::where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [$outS3->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$outS3->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                    ->orderBy('tanggal', 'DESC')
                                    ->first();
                                $shift3 = 1;

                                $jMasukId = ($actIn)?$actIn->id:null;
                                $jKeluarId = ($actOut)?$actOut->id:null;                            
                            }
                        }
                    }
                    else
                    {
                        $tmpAct = Activity::where('pin', $karyawan->key)
                                ->whereBetween('tanggal', [$inS1->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$inS1->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                ->orderBy('tanggal', 'ASC')
                                ->first();
                        /*
                         * shift 1
                         */
                        if($tmpAct)
                        {
                            $actIn = $tmpAct;

                            $actOut = Activity::where('pin', $karyawan->key)
                                ->whereBetween('tanggal', [$outS1->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$outS1->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                ->orderBy('tanggal', 'DESC')
                                ->first();

                            $jMasukId = ($actIn)?$actIn->id:null;
                            $jKeluarId = ($actOut)?$actOut->id:null;
                        }
                        else
                        {
                            $actIn = Activity::where('pin', $karyawan->key)
                                ->whereBetween('tanggal', [$inS2->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$inS2->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                ->orderBy('tanggal', 'DESC')
                                ->first();

                            if($actIn)
                            {

                                $actOut = Activity::where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [$outS2->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$outS2->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                    ->orderBy('tanggal', 'DESC')
                                    ->first();

                                $jMasukId = ($actIn)?$actIn->id:null;
                                $jKeluarId = ($actOut)?$actOut->id:null;
                            }
                            else
                            {
                                $actIn = Activity::where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [$inS3->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$inS3->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                    ->orderBy('tanggal', 'DESC')
                                    ->first();

                                $actOut = Activity::where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [$outS3->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$outS3->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                    ->orderBy('tanggal', 'DESC')
                                    ->first();

                                $jMasukId = ($actIn)?$actIn->id:null;
                                $jKeluarId = ($actOut)?$actOut->id:null;
                            }
                        }
                    }
                    if($actIn && $actOut)
                    {
                        $aIn = Carbon::createFromFormat("Y-m-d H:i:s", $actIn->tanggal);
                        $aOut = Carbon::createFromFormat("Y-m-d H:i:s", $actOut->tanggal);
                        $jumlahJamKerja = $aOut->diffInHours($aIn);
                    }
                }
                
                if($actIn)
                {
                    $jMasuk = Carbon::createFromFormat('Y-m-d H:i:s', $actIn->tanggal);
                    $jSubMasuk = $jMasuk->copy()->subSeconds((int)$jMasuk->format('s'));
                }

                if($actOut)
                {
                    $jKeluar = Carbon::createFromFormat('Y-m-d H:i:s', $actOut->tanggal);
                    $jSubKeluar = $jKeluar->copy()->subSeconds((int)$jKeluar->format('s'));
                }
                
                if($jMasukId && $jKeluarId)
                {
                    if(!$isLn && !$isLnOff && !$isSpo)
                    {
                        if($jSubMasuk)
                        {
                            $nMasuk = $jadMasuk->diffInMinutes($jSubMasuk, false);
                        }
                        if($jSubKeluar)
                        {
                            $nKeluar = $jSubKeluar->diffInMinutes($jadKeluar, false);
                        }
                    }

                    $jumlahActivityKerja = $jKeluar->diffInMinutes($jMasuk);
                }

                /*
                 * start absen manual
                 */
                $actMan = $karyawan->absenManual()->where('activity_manuals.tanggal', $key)->first();
                
                if($actMan)
                {
                    $isTa = null;
                    $isMangkir = null;
                    $flagNotInOut = null;
                    
                    $jMasuk = Carbon::createFromFormat("Y-m-d H:i:s", $actMan->tanggal.' '.$actMan->jam_masuk);
                    $jMasukId = $actMan->id;
                    $jKeluar = Carbon::createFromFormat("Y-m-d H:i:s", $actMan->tanggal.' '.$actMan->jam_keluar);
                    $jKeluarId = $actMan->id;
                    
                    $jumlahActivityKerja = $jKeluar->diffInMinutes($jMasuk);

                    if($jMasuk->greaterThan($jKeluar))
                    {
                        $jKeluar->addDay();
                        $shift3 = 1;
                    }

                    if($jMasuk && $jadMasuk)
                    {
                        $nMasuk = $jadMasuk->diffInMinutes($jMasuk, false);
                    }
                    if($jKeluar && $jadKeluar)
                    {
                        $nKeluar = $jKeluar->diffInMinutes($jadKeluar, false);
                    }
                }
                /*
                 * End absen manual
                 */

                if($flagNotInOut)
                {
                    if($jumlahActivityKerja < 5)
                    {
                        if($flagNotInOut == 'out')
                        {
                            $jKeluarId = null;
                            $jKeluar = null;
                            $nKeluar = null;
                        }
                        else
                        {
                            $jMasukId = null;
                            $jMasuk = null;
                            $nMasuk = null;
                        }
                    }
                }

                /*
                 * Hitung GP
                 */
                $nilaiGp = $this->gpOld($nMasuk, $nKeluar);
                /*
                 * End Hitung GP
                 */

                /*
                 * End Hitung Lembur
                 */
                proses_simpan:
                /*
                 * Hitung Lembur otomatis
                 */
//                if(!$isMangkir && !$isTa && !$nilaiGp && $jMasuk && $jKeluar)
                if(!$isMangkir && !$isTa && $jMasuk && $jKeluar)
                {

                    if($kodeJadwal)
                    {
//                        dd($val->kode);
                        if(substr($kodeJadwal,0,1) == "J" && !$isLn)
                        {
                            if(isset($jumlahActivityKerja))
                            {
                                if(isset($jumlahActivityKerja))
                                {
                                    $jAct = ($jumlahActivityKerja/60) - 5;
                                    if($jAct > 0 && $jAct < 2)
                                    {
                                        $lemburAktual += 1;
                                        $hitungLembur = 1.5;
                                    }
                                    else if($jAct >= 2)
                                    {
                                        $lemburAktual += 2;
                                        $hitungLembur = 3.5;
                                    }
                                    else
                                    {
                                        $lemburAktual += 0;
                                        $hitungLembur = 0;
                                    }
                                }
//                                if(($jumlahActivityKerja/60) >= 7)
//                                {
//                                    $lemburAktual += 2;
//                                    $hitungLembur = $this->hitungLembur($lemburAktual);
//                                }
                            }
                            //3.5 hitung lembur kalau gak telat sama gak gp
                            
                        }
                        else if(substr($kodeJadwal,0,1) == "S" && !$isLn)
                        {
                            if(isset($jumlahActivityKerja))
                            {
                                $jAct = ($jumlahActivityKerja/60) - 4;
                                
                                if($jAct > 0)
                                {
                                    $lemburAktual += 0.5;
                                    $hitungLembur = 0.75;
                                }
                                else
                                {
                                    $lemburAktual += 0;
                                    $hitungLembur = 0;
                                }
                            }
//                            $lemburAktual += 0.5;
//                            $hitungLembur = 0.75;
                        }
                        else if(substr($kodeJadwal,0,1) == "P" && !$isLn)
                        {
                            if(isset($jumlahActivityKerja))
                            {
                                $jAct = ($jumlahActivityKerja/60) - 5;
                                if($jAct > 0 && $jAct < 2)
                                {
                                    $lemburAktual += 2.5;
                                    $hitungLembur = 2.5;
                                }
                                else if($jAct >= 2)
                                {
                                    $lemburAktual += 2.5;
                                    $hitungLembur = 4.5;
                                }
                                else
                                {
                                    $lemburAktual += 0.5;
                                    $hitungLembur = 0.75;
                                }
                            }
                        }
                    }
                    
                    if(isset($alasan))
                    {
                        if($alasan->count())
                        {
                           
                            foreach($alasan->get() as $vAlasan)
                            {
                                $lAkt = null;

                                if($vAlasan->kode == 'SPL')
                                {
                                    $waktuMenit = $addRangeEnd;

                                    if(abs($nKeluar) >= $waktuMenit)
                                    {
                                        $lAkt = (float) $vAlasan->pivot->waktu;
                                        $lemburAktual += $lAkt;
                                    }
                                    else
                                    {
                                        $lAkt = (float) $this->roundDec($nKeluar);
                                        $lemburAktual += $lAkt;
                                    }
                                    $keterangan[] = "SPL ".$lAkt;
                                    $hitungLembur = $this->hitungLembur($lemburAktual);
                                }
                                else if($vAlasan->kode == 'SLA')
                                {
                                    $waktuMenit = $addRangeStart;
                                    $lAkt = null;

                                    if(abs($nMasuk) >= $waktuMenit)
                                    {
                                        $lAkt = (float) $vAlasan->pivot->waktu;
                                        $lemburAktual += $lAkt;
                                    }
                                    else
                                    {
                                        $lAkt = (float) $this->roundDec($nMasuk);
                                        $lemburAktual += $lAkt;
                                    }
                                    $keterangan[] = "SLA ".$lAkt;
                                    $hitungLembur = $this->hitungLembur($lemburAktual);
                                }
                                else if($vAlasan->kode == 'SPO')
                                {     
                                    $jDiff = $jKeluar->diffInHours($jMasuk);
                                    $wkt = (float)$vAlasan->pivot->waktu;
                                    $lAkt = null;
                                    if($jDiff>=$wkt)
                                    {
                                        $lAkt = $wkt;
                                        $lemburAktual += $lAkt;
                                    }
                                    else
                                    {
                                        $lAkt = (float) $this->roundDec($jDiff);
                                        $lemburAktual += $lAkt;
                                        if($jDiff>5)
                                        {
                                            $lAkt -= 1;
                                            $lemburAktual -= 1;
                                        }
                                    }
                                    $keterangan[] = "SPO ".$lAkt;
                                    $hitungLembur = $lemburAktual * 2;
                                }
                                else if($vAlasan->kode == 'LN')
                                {       
                                    $jDiff = $jKeluar->diffInHours($jMasuk);
                                    $wkt = (float)$vAlasan->pivot->waktu;

                                    if($jDiff>=$wkt)
                                    {
                                        $lemburLN += $wkt;

                                    }
                                    else
                                    {
                                        $lemburLN += (float) $this->roundDec($jDiff);
                                    }

                                    $keterangan[] = "Lembur Libur Nasional ".$lemburLN;
                                    
                                    $wktKerja = 7;
                                    // dd($lemburLN <= $wktKerja);
                                    if(substr($kodeJadwal,0,1) == "P" || substr($kodeJadwal,0,1) == "J") 
                                    {
                                        $wktKerja = 5;
                                    }

                                    if($lemburLN <= $wktKerja)
                                    {
                                        $hitungLemburLN = $lemburLN * 2;
                                    }
                                    else
                                    {
                                        $hitungLemburLN += $wktKerja * 2;
                                        $hitungLemburLN += 1 * 3;
                                        $hitungLemburLN += ($lemburLN - ($wktKerja + 1)) * 4;

                                    }
                                }
                            }
                        }
                    }
                }

                if(is_array($keterangan))
                {
                    $keterangan = implode(', ', $keterangan);
                }

                if($jumlahActivityKerja>(5*60))
                {
                    if(!$pendek)
                    {
                        $jumlahActivityKerja -= 60;
                    }
                }

                if($jumlahJamKerja)
                {
                    if(!$pendek)
                    {
                        if(($jumlahActivityKerja/60)>4)
                        {
                            $jumlahJamKerja -= 1;
                        }
                    }
                }

                
                if(!$jMasuk && !$jKeluar)
                {
                    // dd($key);
                    if(!$isLibur && !$isInOut && !$isOff)
                    {
                        $today = Carbon::now();
                        if($today->greaterThanOrEqualTo($curDate))
                        {
                            $isMangkir = 1;
                            $alasanId[] = Alasan::where('kode', 'M')->first()->id;
                        }
                    }
                }
                else if(!$jMasuk || !$jKeluar)
                {
                    if(!$isLibur)
                    {
                        $isTa = 1;
                        $alasanId[] = Alasan::where('kode', 'TA')->first()->id;
                    }
                }

                if($nilaiGp)
                {
                    if(($nilaiGp/60)>4)
                    {
                        $nilaiGp -= 60;
                        $jumlahJamKerja = round($jumlahActivityKerja/60);
                    }
                    else
                    {                                
                        $jumlahJamKerja = $jumlahJamKerja - ($nilaiGp/60);
                    }
                    $alasanId[] = Alasan::where('kode', 'GP')->first()->id;
                }

//                if($isLibur || $isMangkir)
//                {
//                    $shift3 = null;
//                }

                if($alasanId)
                {
                    $alasanId = json_encode($alasanId);
                }
                
                $tLembur = null;
                
                if($hitungLembur+$hitungLemburLN)
                {
                    $tLembur = $hitungLembur+$hitungLemburLN;
                }
                
                $arrProses[] = [
                    'karyawan_id' => $karId,
                    'alasan_id' => $alasanId,
                    'tanggal' => $key,
                    'jam_masuk' => (!empty($jMasuk))?$jMasuk->format('H:i:s'):null,
                    'jam_keluar' => (!empty($jKeluar))?$jKeluar->format('H:i:s'):null,
                    'jam_masuk_id' => $jMasukId,
                    'jam_keluar_id' => $jKeluarId,
                    'kode_jam_kerja' => $kodeJadwal,
                    'jadwal_jam_masuk' => (isset($val->jam_masuk)?$val->jam_masuk:null),
                    'jadwal_jam_keluar' => (isset($val->jam_keluar)?$val->jam_keluar:null),
                    'n_masuk' => (!empty($nMasuk))?$nMasuk:null,
                    'n_keluar' => (!empty($nKeluar))?$nKeluar:null,
                    'libur' => $isLibur,
                    'libur_nasional' => (($isLn)?1:null),
                    'pendek' => $pendek,
                    'mangkir' => $isMangkir,
                    'ta' => $isTa,
                    'lembur_aktual' => $lemburAktual,
                    'hitung_lembur' => $hitungLembur,
                    'lembur_ln' => $lemburLN,
                    'hitung_lembur_ln' => $hitungLemburLN,
                    'total_lembur' => $tLembur,
                    'shift3' => (!empty($shift3))?$shift3:null,
                    'gp' => $nilaiGp,
                    'jumlah_jam_kerja' => (!empty($jumlahJamKerja))?$jumlahJamKerja:null,
                    'keterangan' => $keterangan,
                    'is_off' => $isOff,
                    'created_by' => Auth::user()->id
                ];

//                Prosesabsen::create($arrProses);
            }
            if(count($arrProses) > 0)
            {
                Prosesabsen::insert($arrProses);
            }
        }
    }
    
    
    
    public function jadwals($tanggal, $kar)
    {
        $arr = array();
        
        
        
        foreach($tanggal as $tgl)
        {
            $jad = null;
            $kJad = $kar->jadwalsTanggal($tgl->toDateString())->first();
            
            if($kJad)
            {
                if($kJad->tipe == 'D')
                {
                    $jad = Jadwal::find($kJad->id)->jadwalKerjaDay($tgl->format("N"))->first();
                }
                else
                {
                    $jad = Jadwal::find($kJad->id)->jadwalKerjaShift($tgl->toDateString())->first();
                }
            }
            $arr[$tgl->toDateString()] = $jad;            
        }
        
        return $arr;
    }
    
    public function jadwalSingle($tanggal, $kar)
    {
        $jad = null;
        $kJad = $kar->jadwalsTanggal($tanggal->toDateString())->first();

        if($kJad)
        {
            if($kJad->tipe == 'D')
            {
                $jad = Jadwal::find($kJad->id)->jadwalKerjaDay($tanggal->format("N"))->first();
            }
            else
            {
                $jad = Jadwal::find($kJad->id)->jadwalKerjaShift($tanggal->toDateString())->first();
            }
        }
        return $jad;     
    }
    
    
    
    public function jadwalDay($tanggal, $jadwal)
    {
        $arr = array();
        foreach($tanggal as $tgl)
        {
            foreach($jadwal->jadwal_kerja as $jad)
            {
                $jad = $jadwal->jadwalKerjaDay($tgl->format("N"))->first();
                $arr[$tgl->toDateString()] = $jad;
            }
        }
        return $arr;
    }
    
    public function absenMasuk($tanggal, $karyawanId, $sf = null)
    {
        $karyawan = Karyawan::find($karyawanId);
        
        $in = null;
        $inS1 = Carbon::createFromFormat("Y-m-d H:i:s", $tanggal->toDateString()." 07:00:00");
        $inS2 = Carbon::createFromFormat("Y-m-d H:i:s", $tanggal->toDateString()." 14:00:00");
        $inS3 = Carbon::createFromFormat("Y-m-d H:i:s", $tanggal->toDateString()." 23:00:00");

        $out = null;
        $outS1 = Carbon::createFromFormat("Y-m-d H:i:s", $tanggal->toDateString()." 14:00:00");
        $outS2 = Carbon::createFromFormat("Y-m-d H:i:s", $tanggal->toDateString()." 23:00:00");
        $outS3 = Carbon::createFromFormat("Y-m-d H:i:s", $tanggal->copy()->addDay()->toDateString()." 07:00:00");
        
        if($karyawan)
        {
            $jadwal = $this->jadwalSingle($tanggal, $karyawan);
            
            if($jadwal)
            {
                $in = Carbon::createFromFormat("Y-m-d H:i:s", $tanggal->toDateString()." ".$jadwal->jam_masuk.":00");
                $out = Carbon::createFromFormat("Y-m-d H:i:s", $tanggal->toDateString()." ".$jadwal->jam_keluar.":00");
                
                if($sf == 1)
                {
                    if($in->between($inS1->copy()->subMinutes($this->rangeAbs), $inS1->copy()->addMinute($this->rangeAbs)) ||
                       $out->between($outS1->copy()->subMinutes($this->rangeAbs), $outS1->copy()->addMinute($this->rangeAbs)))
                    {
                        $in = $inS1;
                        $out = $outS1;
                        goto proses;
                    }
                    else
                    {
                        return null;
                    }
                }
                else if($sf == 2)
                {
                    if($in->between($inS2->copy()->subMinutes($this->rangeAbs), $inS2->copy()->addMinute($this->rangeAbs)) ||
                       $out->between($outS2->copy()->subMinutes($this->rangeAbs), $outS2->copy()->addMinute($this->rangeAbs)))
                    {
                        $in = $inS2;
                        $out = $outS2;
                        goto proses;
                    }
                    else
                    {
                        return null;
                    }
                }
                else if($sf == 3)
                {
                    if($in->between($inS3->copy()->subMinutes($this->rangeAbs), $inS3->copy()->addMinute($this->rangeAbs)) ||
                       $out->between($outS3->copy()->subMinutes($this->rangeAbs), $outS3->copy()->addMinute($this->rangeAbs)))
                    {
                        $in = $inS3;
                        $out = $outS3;
                        goto proses;
                    }
                    else
                    {
                        return null;
                    }
                }
                
                proses:
                $actIn = Activity::with('mesin')->where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [$inS1->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$in->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                    ->orderBy('tanggal', 'ASC')
                                    ->first();
                $actOut = Activity::with('mesin')->where('pin', $karyawan->key)
                                    ->whereBetween('tanggal', [$out->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$out->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                    ->orderBy('tanggal', 'asc')
                                    ->first();
                                    
                if(!$actIn && $sf != null)
                {
                    $actIn = Activity::with('mesin')->where('pin', $karyawan->key)
                                    ->whereDate('tanggal', $tanggal->toDateString())
                                    ->orderBy('tanggal', 'ASC')
                                    ->first();
                    $actOut = Activity::with('mesin')->where('pin', $karyawan->key)
                                    ->whereDate('tanggal', $tanggal->toDateString())
                                    ->orderBy('tanggal', 'desc')
                                    ->first();
                }

                return ['jadwal' => $jadwal, 'activity' => $actIn, 'activity_out' => $actOut];
            }
            return null;
            
        }
        return null;
    }
    
    
    
    private function lDet($req)
    {
        try
        {
            $ret = [];
            $karyawanId = array();
            $periode = null;
            
            if(isset($req['tanggalRange']))
            {
                $tgl = explode(' - ', $req['tanggalRange']);
                
                $periode = CarbonPeriod::create($tgl[0], $tgl[1])->toArray();
            }
            else
            {
                $tgl = Carbon::createFromFormat('Y-m-d', $req['tanggal'].'-22')->subMonth();

                $periode = CarbonPeriod::create($tgl, $tgl->copy()->addMonth(1)->subDay(1))->toArray();
            }
            
            if(isset($req['pin']))
            {
                $karyawanId[] = $req['pin'];
            }
            else if(isset($req['divisi']))
            {
                $div = Divisi::descendantsAndSelf($req['divisi'])->pluck('id');
//                dd($div);
                if(isset($req['perusahaan']))
                {
                    $karyawanId = Karyawan::author()->karyawanTerlihat()->whereIn('divisi_id',$div)->where('perusahaan_id', $req['perusahaan'])->orderBy('pin', 'asc')->pluck('id');
                }
                else
                {
                    $karyawanId = Karyawan::author()->karyawanTerlihat()->whereIn('divisi_id', $div)->orderBy('pin', 'asc')->pluck('id');
                }
            }
            else
            {
                if(isset($req['perusahaan']))
                {
                    $karyawanId = Karyawan::author()->karyawanTerlihat()->orderBy('divisi_id', 'asc')->where('perusahaan_id', $req['perusahaan'])->orderBy('pin', 'asc')->pluck('id');
                }
                else
                {
                    $karyawanId = Karyawan::author()->karyawanTerlihat()->orderBy('divisi_id', 'asc')->orderBy('pin', 'asc')->pluck('id');
                }
            }            
//            dd($karyawanId);
            foreach ($karyawanId as $kId)
            {
                $kar = Karyawan::find($kId);
                
                $tmk = null;
                $active = null;
                $off = null;
                $gapok = null;
                
                if($kar->tanggal_masuk)
                {
                    $tmk = Carbon::createFromFormat('Y-m-d', $kar->tanggal_masuk);
                }
                
                if($kar->active_status_date)
                {
                    $active = Carbon::createFromFormat('Y-m-d', $kar->active_status_date);
                }
                
                $pAbsen = Prosesabsen::where('karyawan_id', $kId)
                        ->whereBetween('tanggal',
                                [
                                    reset($periode)->toDateString(), 
                                    end($periode)->toDateString()
                                ]);
                
                
                if($pAbsen->count()>0)
                {
                    $pAbsen = $pAbsen->get();
                    $arrTgl = [];
                    foreach ($periode as $per)
                    {
                        $arrTgl[$per->format('d/m/Y')] = new \stdClass();
                        $arrTgl[$per->format('d/m/Y')] = $pAbsen->where('tanggal', $per->toDateString())->first();
                        
                        if(isset($arrTgl[$per->format('d/m/Y')]->alasan_id))
                        {
                            
                            $alasan = Alasan::find($arrTgl[$per->format('d/m/Y')]->alasan_id);
//                            dd($alasan);
                            $arrTgl[$per->format('d/m/Y')]['alasan'] = $alasan;
                        }
                        
                        if($tmk)
                        {
                            if($tmk->diffInDays($per, false) < 0)
                            {
                                $arrTgl[$per->format('d/m/Y')]['inout'] = 'IN';
                            }
                        }
                        
                        if($active)
                        {
                            if($active->diffInDays($per, false)>=0)
                            {
                                $arrTgl[$per->format('d/m/Y')]['inout'] = 'OUT';
                            }
                        }
                        
                        if(isset($arrTgl[$per->format('d/m/Y')]))
                            $arrTgl[$per->format('d/m/Y')] = (object)$arrTgl[$per->format('d/m/Y')];
                        
                    }
//                    dd($arrTgl);
                    $ret[] = array('karyawan' => $kar,
                                   'periodeStart' => reset($periode)->toDateString(),
                                   'periodeEnd' => end($periode)->toDateString(),
                                   'absen' => $arrTgl);
                }
                else
                {
                    $arrTgl = [];
                    foreach ($periode as $per)
                    {        
                        
                        if(isset($arrTgl[$per->format('d/m/Y')]->alasan_id))
                        {
                            $alasan = Alasan::find($arrTgl[$per->format('d/m/Y')]->alasan_id);
                            $arrTgl[$per->format('d/m/Y')]['alasan'] = $alasan;
                        }
                        
                        if($tmk)
                        {
                            if($tmk->diffInDays($per, false) < 0)
                            {
                                $arrTgl[$per->format('d/m/Y')]['inout'] = 'IN';
                            }
                        }
                        
                        if($active)
                        {
                            if($active->diffInDays($per, false)>=0)
                            {
                                $arrTgl[$per->format('d/m/Y')]['inout'] = 'OUT';
                            }
                        }
                        
                        if(isset($arrTgl[$per->format('d/m/Y')]))
                            $arrTgl[$per->format('d/m/Y')] = (object)$arrTgl[$per->format('d/m/Y')];
                    }
                    
                    $ret[] = array('karyawan' => $kar,
                                   'periodeStart' => reset($periode)->toDateString(),
                                   'periodeEnd' => end($periode)->toDateString(),
                                   'absen' => $arrTgl);
                    
                }
            }
            
            
            return array(
                'status' => 1,
                'periode' => $periode,
                'msg'   => $ret
                );
//            dd($ret);
        } 
        catch (Exception $ex) 
        {
            $err = array('file_target' => 'LaporanController.php',
                         'message_log' => $e->getMessage(),
                         'created_by' => Auth::user()->id);
            
            ExceptionLog::create($err);
            
            return array(
                'status' => 0,
                'msg'   => 'Data gagal diproses'
                );
        }
    }
    
    public function hitungSpl($val)
    {
        if(!$this->is_decimal($val))
        {
            if($val % 2 == 0)
            {
                return $val/2;
            }
            else
            {
                return floor($val/2);
            }
        }
        else
        {
            if(($val-0.5) % 2 == 0)
            {
                return ($val-0.5)/2;
            }
            else
            {
                return ($val+0.5)/2;
            }
        }
    }

    public function is_decimal( $val )
    {
        return is_numeric( $val ) && floor( $val ) != $val;
    } 
    
    private function jadwalShift($tanggal, $jadwal)
    {
        $arr = array();
        foreach($tanggal as $tgl)
        {
            $jad = $jadwal->jadwalKerjaShift($tgl->toDateString())->first();
            $arr[$tgl->toDateString()] = $jad;
        }
        return $arr;
    }
    
    private function jadwalManual($tanggal, $karyawan)
    {
        $arr = array();
        foreach($tanggal as $tgl)
        {
            $jad = $karyawan->jadwalManualTanggal($tgl->toDateString())->first();
            $arr[$tgl->toDateString()] = $jad;
        }
        return $arr;
    }
    
    
    
    private function HitungLibNas($jam,$jJam=7)
    {
        $hit = 0;
        
        if($jam<=$jJam)
        {
            $hit = $jam*2;
        }
        else if($jam>$jJam)
        {
            $hit = 14;
            
            if(($jam-$jJam)==1)
            {
                $hit += (1*3);
            }
            else if(($jam-$jJam)>1)
            {
                $hit += (1*3);
                $hit += (($jam-8)*4);
            }
        }
            
        return $hit;
    }
    
    private function hitungLembur($nilai)
    {
        $ret = (float) ((2*$nilai) - 0.5);
        
        return $ret;
    }
    
    private function hitungLemburIstirahat($nilai)
    {
        $ret = (float) ($nilai * 0.5);
        
        return $ret;
    }
    
    private function gpOld($masuk, $pulang)
    {
        $ret = null;
        
        if($masuk > 0)
        {
            $ret += abs($masuk);
        }
        if($pulang > 0)
        {
            $ret += abs($pulang);
        }
        if($ret)
        {
            $ret = ceil($ret/30)*30;
        }
        
        return $ret;
    }
    
    private function gp($time)
    {
        $ret = null;
        
        if($time)
        {
            $ret = ceil(abs($time)/30)*30;
        }
        
        return $ret;
    }
    
    private function roundDec($param)
    {
        $scr = abs($param) / 60;
        $scr = round($scr,1,PHP_ROUND_HALF_DOWN);
        $scr -= fmod($scr,0.5);
        
        return $scr;
    }
}
