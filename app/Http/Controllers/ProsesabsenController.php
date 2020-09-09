<?php

namespace App\Http\Controllers;

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

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;

class ProsesabsenController extends Controller
{
    private $rangeAbs = 4 * 60;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.transaksi.prosesabsensi.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function proses(Request $request)
    {
        $req = $request->all();
        try
        {
            $karyawanId = array();
            $tanggal = null;

            if(isset($req['pin']))
            {
                $karyawanId[] = $req['pin'];
            }
            else if(isset($req['divisi']))
            {
                $karyawanId = Karyawan::KaryawanAktif()->where('divisi_id', $req['divisi'])->pluck('id');
            }
            else
            {
                $karyawanId = Karyawan::KaryawanAktif()->pluck('id');
            }

            if(count($karyawanId) < 1)
            {
                echo json_encode(array(
                    'status' => 0,
                    'msg'   => 'Karyawan tidak ada pada opsi yang dipilih'
                ));
            }
            else
            {
                $tArr = explode(" - ", $req['tanggal']);
                $tanggal = CarbonPeriod::create($tArr[0], $tArr[1])->toArray();

                foreach($karyawanId as $rowId)
                {
                    $karyawan = Karyawan::find($rowId);
                    
                    $tmk = null;
                    $active = null;

                    if($karyawan->tanggal_masuk)
                    {
                        $tmk = Carbon::createFromFormat('Y-m-d', $karyawan->tanggal_masuk);
                    }

                    if($karyawan->active_status_date)
                    {
                        $active = Carbon::createFromFormat('Y-m-d', $karyawan->active_status_date);
                    }
                    
//                    $jadwal = $karyawan->jadwals->wherePivot;  
                    $jadwal = $this->jadwals($tanggal, $karyawan);
                    
                    $jadwalArr = array();
                    
                    $proses = Prosesabsen::where('karyawan_id', $rowId)->whereBetween('tanggal', [reset($tanggal)->toDateString(), end($tanggal)->toDateString()]);

                    if($proses->count()>0)
                    {
                        $proses->delete();
                    }
                    
                    if(!$jadwal)
                    {
                        ExceptionLog::create(['file_target' => 'ProsesabsenController.php', 'message_log' => json_encode(['karyawan_id' => $karyawan->id, 
                            'message' => 'JADWAL KOSONG']), 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        continue;
                    }
                    
                    $jadwalArr = $this->jadwals($tanggal, $karyawan);

                    $jadwalManual = $this->jadwalManual($tanggal, $karyawan);

                    if($jadwalArr)
                    {
                        foreach($jadwalArr as $key => $val)
                        {        
                            
                            $alasanId = null;
                            

                            $jadwalBefore = null;
//                            $jadwalAfter = null;

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
//                            $isLemburOff = null;
                            $isLibur = null;
                            $isTa = null;

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
                            
                            if($tmk)
                            {
                                if($tmk->diffInDays($key, false) < 0)
                                {
                                    $alasanId[] = Alasan::where('kode','IN')->first()->id;
                                    goto proses_simpan;
                                }
                            }

                            if($active)
                            {
                                if($active->diffInDays($key, false)>=0)
                                {
                                    $alasanId[] = Alasan::where('kode','OUT')->first()->id;
                                    goto proses_simpan;
                                }
                            }
                            
                            
                            if(isset($val->pendek))
                            {
                                if($val->pendek == "1")
                                {
                                    $pendek = 1;
                                }
                            }

                            if(!isset($val->kode))
                            {
                                continue;
                            }

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

                            /*
                             * Start If
                             * 
                             * Apakah kode jadwal adalah L
                             * Jika Ya, Masukkan nilai jadwal masuk dan pulang
                             * Jika Tidak, Nilai libur akan 1
                             */
                            if($val->kode != 'L')
                            {
                                $in = Carbon::createFromFormat("Y-m-d H:i:s", $key." ".$val->jam_masuk.":00");
                                $out = Carbon::createFromFormat("Y-m-d H:i:s", $key." ".$val->jam_keluar.":00");
                            }
                            else
                            {
                                $isLibur = 1;
                            }
                            /*
                             * End If
                             */

                            /*
                             * Buat Variable Carbon untuk tanggal current
                             */
                            $carbonTgl = Carbon::createFromFormat("Y-m-d", $key);
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
//                            if($jadwal->tipe == 'D')
//                            {
//                                $jadwalBefore = $jadwal->jadwalKerjaDay($carbonTgl->copy()->subDays(1)->format("N"))->first();
//                            }
//                            else
//                            {
//                                $jadwalBefore = $jadwal->jadwalKerjaShift($carbonTgl->copy()->subDays(1)->toDateString())->first();
//                            }
                            if(isset($jadwalArr[$carbonTgl->copy()->subDays(1)->toDateString()]))
                            {
                                $jadwalBefore = $jadwalArr[$carbonTgl->copy()->subDays(1)->toDateString()];
                            }
                            else
                            {
                                $jadwalBeforeArr = $this->jadwals([$carbonTgl->copy()->subDays(1)], $karyawan);
                                if($jadwalArr)
                                {
                                    $jadwalBefore = reset($jadwalBeforeArr);
                                }
                            }
                            
                            /*
                             * End If
                             */

                            /*
                             * Ambil alasan karyawan pada tanggal current
                             */
                            $alasan = $karyawan->alasanTanggal($key);
                            /*
                             * End
                             */

                            /*
                             * Ambil libur nasional
                             */
                            $lN = Libur::where('tanggal', $key)->first();
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
        //                                    dd("bla");
        //                                    continue;
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
    //                                dd("bli");
    //                                continue;
                                }
                                /*
                                 * End if
                                 */

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
                                        case "C" : 
                                        case "I" : 
                                        case "D1":
                                        case "D2" :
                                        case "D3" :
                                        case "SD" :
                                        case "SK" :
                                        case "H1" :
                                        case "H2" : 
                                        case "OFF" :$keterangan[] = $vAlasan->deskripsi; $isLibur = 1;break;
        //                                case "L": continue;
                                    }
                                }
                            }

                            if(!$isLnOff && !$isSpo)
                            {
                                if($val->kode != 'L')
                                {
                                    /*
                                    * cek jadwal shift3
                                    */
                                    if($in->greaterThan($out))
                                    {
                                        $out->addDay();
                                        $shift3 = 1;
                                    }

                                    $jadMasuk = $in;
                                    $jadKeluar = $out;
                                
                                    $jumlahJamKerja = $out->diffInHours($in);
                                    
                                    $actIn = Activity::where('pin', $karyawan->key)
                                            ->whereBetween('tanggal', [$in->copy()->subMinutes($this->rangeAbs + $addRangeStart)->toDateTimeString(),$in->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                            ->orderBy('tanggal', 'ASC')
                                            ->first();
                                    $jMasukId = ($actIn)?$actIn->id:null;
                                    
                                    $actOut = Activity::where('pin', $karyawan->key)
                                            ->whereBetween('tanggal', [$out->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),
                                                $out->copy()->addMinutes($this->rangeAbs + $addRangeEnd)->toDateTimeString()])
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
//                                                dd($actOut);
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
                            else
                            {
    //                            dd($jadwalBefore);

                                $inS1 = Carbon::createFromFormat("Y-m-d H:i:s", $key." 07:00:00");
                                $inS2 = Carbon::createFromFormat("Y-m-d H:i:s", $key." 14:00:00");
                                $inS3 = Carbon::createFromFormat("Y-m-d H:i:s", $key." 23:00:00");

                                $outS1 = Carbon::createFromFormat("Y-m-d H:i:s", $key." 15:00:00");
                                $outS2 = $inS3;
                                $outS3 = $inS1->copy()->addDay();

                                $actIn = null;
                                $actOut = null;

                                $curDate    = Carbon::createFromFormat("Y-m-d", $key);
                                $tglBefore  = $curDate->copy()->subDay();

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
    //                                    dd($actIn);
                                        $actOut = Activity::where('pin', $karyawan->key)
                                            ->whereBetween('tanggal', [$outS3->copy()->subMinutes($this->rangeAbs)->toDateTimeString(),$outS3->copy()->addMinutes($this->rangeAbs)->toDateTimeString()])
                                            ->orderBy('tanggal', 'DESC')
                                            ->first();
                                        $shift3 = 1;
                                        
                                        $jMasukId = ($actIn)?$actIn->id:null;
                                        $jKeluarId = ($actOut)?$actOut->id:null;
    //                                    dd($actOut);
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
    //                        dd($actIn);

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

//                            if($actIn && $actOut)
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
                            $actMan = ActivityManual::where('karyawan_id', $rowId)->where('tanggal', $key)->first();
                            
                            if($actMan)
                            {
                                $jMasuk = Carbon::createFromFormat("Y-m-d H:i:s", $key.' '.$actMan->jam_masuk);
                                $jKeluar = Carbon::createFromFormat("Y-m-d H:i:s", $key.' '.$actMan->jam_keluar);
                                
                                if($jMasuk->greaterThan($jKeluar))
                                {
                                    $jKeluar->addDay();
                                    $shift3 = 1;
                                }
                                
                                if($jMasuk)
                                {
                                    $nMasuk = $jadMasuk->diffInMinutes($jMasuk, false);
                                }
                                if($jKeluar)
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
//                            $nilaiGpIn = $this->gp($nMasuk);
//                            $nilaiGpOut = $this->gp($nKeluar);
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
                            if(!$isMangkir && !$isTa && !$nilaiGp)
                            {
                            
                                if(substr($val->kode,0,1) == "J")
                                {
                                    //3.5 hitung lembur kalau gak telat sama gak gp
                                    $lemburAktual += 2;
                                    $hitungLembur = $this->hitungLembur($lemburAktual);
                                }

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

                                                if($lAkt>5)
                                                {
                                                    $lAkt -= 1;
                                                    $lemburAktual -= 1;
                                                }
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
                                            $wkt = (float)$vAlasan->pivot->waktu;

                                            if($jDiff>=$wkt)
                                            {
                                                $lemburLN += $wkt;

                                                if($wkt>5)
                                                {
                                                    $lemburLN -= 1;
                                                }
                                            }
                                            else
                                            {
                                                $lemburLN += (float) $this->roundDec($jDiff);
                                                if($jDiff>5)
                                                {
                                                    $lemburLN -= 1;
                                                }
                                            }

                                            $keterangan[] = "Lembur Libur Nasional ".$lemburLN;
                                            $hitungLemburLN = $lemburLN * 2;
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
                                if(!$isLibur)
                                {
                                    $isMangkir = 1;
                                    $alasanId[] = Alasan::where('kode', 'M')->first()->id;
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
                            
                            
                            if($alasanId)
                            {
                                $alasanId = json_encode($alasanId);
                            }
                            
                            $arrProses = [
                                'karyawan_id' => $rowId,
                                'alasan_id' => $alasanId,
                                'tanggal' => $key,
                                'jam_masuk' => (!empty($jMasuk))?$jMasuk->format('H:i:s'):null,
                                'jam_keluar' => (!empty($jKeluar))?$jKeluar->format('H:i:s'):null,
                                'jam_masuk_id' => $jMasukId,
                                'jam_keluar_id' => $jKeluarId,
                                'kode_jam_kerja' => $val->kode,
                                'jadwal_jam_masuk' => $val->jam_masuk,
                                'jadwal_jam_keluar' => $val->jam_keluar,
                                'n_masuk' => (!empty($nMasuk))?$nMasuk:null,
                                'n_keluar' => (!empty($nKeluar))?$nKeluar:null,
                                'libur' => $isLibur,
                                'libur_nasional' => (!empty($isLn)?1:null),
                                'pendek' => $pendek,
                                'mangkir' => $isMangkir,
                                'ta' => $isTa,
                                'lembur_aktual' => $lemburAktual,
                                'hitung_lembur' => $hitungLembur,
                                'lembur_ln' => $lemburLN,
                                'hitung_lembur_ln' => $hitungLemburLN,
                                'total_lembur' => $totalLembur,
                                'shift3' => (!empty($shift3))?$shift3:null,
                                'gp' => $nilaiGp,
                                'jumlah_jam_kerja' => (!empty($jumlahJamKerja))?$jumlahJamKerja:null,
                                'keterangan' => $keterangan,
                                'created_by' => Auth::user()->id
                            ];

                            Prosesabsen::create($arrProses);
                        }
                    }
    //                    dd($jadwalArr);
                }
            }
            
            echo json_encode(array(
                'status' => 1,
                'msg'   => 'Data berhasil diproses'
                ));
        }
        catch(Exception $e)
        {
            $err = array('file_target' => 'ProsesabsenController.php',
                         'message_log' => json_encode($e->getMessage()),
                         'created_by' => Auth::user()->id);
            
            ExceptionLog::create($err);
            
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal diproses'
                ));
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    private function jadwals($tanggal, $kar)
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
    
    private function jadwalDay($tanggal, $jadwal)
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

