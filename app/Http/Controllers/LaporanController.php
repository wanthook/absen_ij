<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\View;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;
use TCPDF;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Borders;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Calculation\LookupRef;

use Illuminate\Support\Facades\Storage;

/**
 * Description of LaporanController
 *
 * @author development
 */
use App\Prosesabsen;
use App\Karyawan;
use App\ExceptionLog;
use App\Alasan;
use App\Divisi;
use App\Activity;
use App\Prosesgaji;

use DB;

use App\Http\Traits\TraitProses;


class LaporanController 
{
    use TraitProses;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexDetail()
    {
        return view('admin.laporan.detail.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexKomulatif()
    {
        return view('admin.laporan.komulatif.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexKaryawanAktif()
    {
        return view('admin.laporan.karyawan_aktif.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexKaryawanMangkirTa()
    {
        return view('admin.laporan.karyawan_mangkir_ta.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexKaryawanHabisKontrak()
    {
        return view('admin.laporan.karyawan_habis_kontrak.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexKaryawanDaftarHadir()
    {
        return view('admin.laporan.karyawan_daftar_hadir.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexKaryawanRekapAbsen()
    {
        return view('admin.laporan.karyawan_rekap_absen.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexTransaksiAlasan()
    {
        return view('admin.laporan.transaksi_alasan.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexLogJamMasuk()
    {
        return view('admin.laporan.log_jam_masuk.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexLogMesinKaryawan()
    {
        return view('admin.laporan.log_mesin_karyawan.index');
    }
    
    public function indexStatusNonAktif()
    {
        return view('admin.laporan.status_non_aktif.index');
    }
    
    public function indexListGaji()
    {
        return view('payroll.laporan.list_gaji.index');
    }
    
    public function laporanDetail(Request $request)
    {
        try
        {
            $req = $request->all();

            $ret = $this->lDet($req);
            
            $send = [];
            $curDate = Carbon::now();

            if(isset($ret['msg']))            {
                
                if(count($ret['msg']))
                {
                    foreach($ret['msg'] as $kVar => $vVar)
                    {
                        $sendTemp = [];
                        $lemburAktual = 0;
                        $hitungLembur = 0;
                        $shiftMalam = 0;
                        $lemburLn = 0;
                        $hitungLn = 0;
                        $totLem = 0;
                        $tLembur = 0;

                        $siTukangLembur = 0; //rutin banget sih lemburnya

                        $sendTemp['periodeStart'] = $vVar['periodeStart'];
                        $sendTemp['periodeEnd'] = $vVar['periodeEnd'];
                        $sendTemp['karyawan'] = $vVar['karyawan'];

                        foreach($vVar['absen'] as $kabs => $vabs)
                        {
                            $tmpDet = [];
                            $tmpDet['tanggal'] = $kabs;

                            if(!isset($vabs->inout))
                            {

                                $lemburAktual += (isset($vabs->lembur_aktual)?$vabs->lembur_aktual:null);
                                $hitungLembur += (isset($vabs->hitung_lembur)?$vabs->hitung_lembur:null);
                                $lemburLn += (isset($vabs->lembur_ln)?$vabs->lembur_ln:null);
                                $hitungLn += (isset($vabs->hitung_lembur_ln)?$vabs->hitung_lembur_ln:null);
                                $tLembur = (isset($vabs->total_lembur)?$vabs->total_lembur:null);

                                $totLem += $tLembur;

                                $tmpDet['jadwal_jam_masuk'] = substr((isset($vabs->jadwal_jam_masuk)?$vabs->jadwal_jam_masuk:null),0,5);
                                $tmpDet['jadwal_jam_keluar'] = substr((isset($vabs->jadwal_jam_keluar)?$vabs->jadwal_jam_keluar:null),0,5);
                                $tmpDet['jam_masuk'] = substr((isset($vabs->jam_masuk)?$vabs->jam_masuk:null),0,5);
                                $tmpDet['jam_keluar'] = substr((isset($vabs->jam_keluar)?$vabs->jam_keluar:null),0,5);
                                if(isset($vabs->n_masuk))
                                {
                                   $tmpDet['n_masuk_c'] = ($vabs->n_masuk < 0)?abs($vabs->n_masuk):'';
                                   $tmpDet['n_masuk_t'] = ($vabs->n_masuk > 0)?abs($vabs->n_masuk):'';
                                }
                                else
                                {
                                   $tmpDet['n_masuk_c'] = '';
                                   $tmpDet['n_masuk_t'] = '';
                                }

                                if(isset($vabs->n_keluar))
                                {
                                   $tmpDet['n_keluar_c'] = ($vabs->n_keluar > 0)?abs($vabs->n_keluar):'';
                                   $tmpDet['n_keluar_t'] = ($vabs->n_keluar < 0)?abs($vabs->n_keluar):'';
                                }
                                else
                                {
                                   $tmpDet['n_keluar_c'] = '';
                                   $tmpDet['n_keluar_t'] = '';
                                }

                                if(isset($vabs->inout))
                                {
                                    $tmpDet['keterangan'] = $vabs->inout;
                                }
                                else if(isset($vabs->keterangan))
                                {
                                    $tmpDet['keterangan'] = $vabs->keterangan;
                                }
                                else if(isset($vabs->alasan))
                                {
                                    foreach($vabs->alasan as $als)
                                    {
                                        if($als->kode == 'PM')
                                        {
                                            if(!isset($tmpDet['keterangan']))
                                            {
                                                $tmpDet['keterangan'] = $als->kode;
                                            }
                                            else
                                            {
                                                $tmpDet['keterangan'] .= ', '.$als->kode;
                                            }
                                        }
                                        else
                                        {
                                            $tmpDet['keterangan'] = null;
                                        }
                                    }
                                }
                                else
                                {

                                    $tmpDet['keterangan'] = null;
                                }

                                $tmpDet['lembur_aktual'] = (isset($vabs->lembur_aktual)?$vabs->lembur_aktual:null);
                                $tmpDet['hitung_lembur'] = (isset($vabs->hitung_lembur)?$vabs->hitung_lembur:null);

                                if(isset($vabs->shift3))
                                {
                                    if(isset($vabs->jam_masuk) && isset($vabs->jam_keluar))
                                    {
                                        $tmpDet['shift3'] = $vabs->shift3;
                                        $shiftMalam += 1;
                                    }
                                    else
                                    {
                                        $tmpDet['shift3'] =  '';
                                    }
                                }
                                else 
                                {
                                    $tmpDet['shift3'] =  '';
                                }

                                $tmpDet['lembur_ln'] = (isset($vabs->lembur_ln)?$vabs->lembur_ln:null);
                                $tmpDet['hitung_lembur_ln'] = (isset($vabs->hitung_lembur_ln)?$vabs->hitung_lembur_ln:null);
                                $tmpDet['tLembur'] = ($tLembur=='0.00')?str_replace('0.00',null,$tLembur):$tLembur;

                            }
                            else
                            {
                                $tmpDet['jadwal_jam_masuk'] = null;
                                $tmpDet['jadwal_jam_keluar'] = null;
                                $tmpDet['jam_masuk'] = null;
                                $tmpDet['jam_keluar'] = null;
                                $tmpDet['n_masuk_c'] = null;
                                $tmpDet['n_masuk_t'] = null;
                                $tmpDet['n_keluar_c'] = null;
                                $tmpDet['n_keluar_t'] = null;

                                if(isset($vabs->inout))
                                {
                                    $tmpDet['keterangan'] = $vabs->inout;
                                }
                                else if(isset($vabs->keterangan))
                                {
                                    $tmpDet['keterangan'] = $vabs->keterangan;
                                }

                                $tmpDet['lembur_aktual'] = null;
                                $tmpDet['hitung_lembur'] = null;
                                $tmpDet['shift3'] =  null;
                                $tmpDet['lembur_ln'] = null;
                                $tmpDet['hitung_lembur_ln'] = null;
                                $tmpDet['tLembur'] = null;
                            }

                            $sendTemp['detail'][] = $tmpDet;
                        }

                        $sendTemp['lemburAktual'] = ($lemburAktual)?$lemburAktual:'0.0';
                        $sendTemp['hitungLembur'] = ($hitungLembur)?$hitungLembur:'0.0';
                        $sendTemp['shiftMalam'] = ($shiftMalam)?$shiftMalam:'0.0';
                        $sendTemp['lemburLn'] = ($lemburLn)?$lemburLn:'0.0';
                        $sendTemp['hitungLn'] = ($hitungLn)?$hitungLn:'0.0';
                        $sendTemp['totLem'] = ($totLem)?$totLem:'0.0';
                        $send[] = $sendTemp;
                    }
                    
                }
            }
//            dd($send);
            if($req['btnSubmit'] == "preview")
            {
                return view('admin.laporan.detail.preview', ['var' => $send, 'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
            }
            else if($req['btnSubmit'] == "pdf")
            {

                $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', true);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
                $pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
                $pdf->setFontSubsetting(false);
                $pdf->SetFont('dejavusans', '', 8);
                if(count($send))
                {
                    foreach($send as $var)
                    {
                        $pdf->setHeaderData(config('global.img_laporan'), 10, "Laporan Kehadiran Karyawan","Periode : ".$var['periodeStart']." s/d ".$var['periodeEnd']);
                        $pdf->AddPage();
                        $infoWidth = array(25,3,300);
                        $pdf->Cell($infoWidth[0], 3, "PIN / Nama");
                        $pdf->Cell($infoWidth[1], 3, ":");
                        $pdf->Cell($infoWidth[2], 3, $var['karyawan']->pin.' - '.$var['karyawan']->nama);
                        $pdf->Ln();
                        $pdf->Cell($infoWidth[0], 3, "Unit Kerja");
                        $pdf->Cell($infoWidth[1], 3, ":");
                        $pdf->Cell($infoWidth[2], 3, (isset($var['karyawan']->divisi->kode)?$var['karyawan']->divisi->kode:'').' - '.isset($var['karyawan']->divisi->deskripsi)?$var['karyawan']->divisi->deskripsi:'');
                        $pdf->Ln();
                        $pdf->Cell($infoWidth[0], 3, "NIK");
                        $pdf->Cell($infoWidth[1], 3, ":");
                        $pdf->Cell($infoWidth[2], 3, $var['karyawan']->nik);
                        $pdf->Ln();

                        $Width = array(25,30,30,20,20,50,15,15,15,15,15,15);
                        $Width2 = array(25,15,15,15,15,10,10,10,10,50,15,15,15,15,15,15);
                        $headTbl1 = array('Tanggal','Jadwal Kerja','Jam Kerja','Masuk','Pulang','Keterangan',"Lembur","Hitung","Shift","Lembur","Hitung","Total");
                        $headTbl2 = array('','M','K','M','K','C','T','C','T','',"Aktual","Lembur","Malam","Libur Nas","Libur Nas","Lembur");
                        foreach($headTbl1 as $kH => $vH)
                        {
                            $border = 1;
                            switch($kH)
                            {
                                case 0:
                                case 5:
                                case 6:
                                case 7:
                                case 8:
                                case 9:
                                case 10:
                                case 11:
                                $border = 'LRT';
                            }
                             $pdf->Cell($Width[$kH], 4, $vH, $border, 0, 'C');
                        }
                        $pdf->Ln();
                        foreach($headTbl2 as $kH => $vH)
                        {
                            $border = 1;
                            switch($kH)
                            {
                                case 0:
                                case 9:
                                case 10:
                                case 11:
                                case 12:
                                case 13:
                                case 14:
                                case 15:
                                $border = 'LRB';
                            }
                            $pdf->Cell($Width2[$kH], 4, $vH, $border, 0, 'C');
                        }
                        $pdf->Ln();
                        
                        if(isset($var['detail']))
                        {
                            foreach($var['detail'] as $kabs => $vabs)
                            {
                                $i = 0;
                                foreach($vabs as $kdet => $vdet)
                                {
                                    $pdf->Cell($Width2[$i++], 4.5, $vdet, '1', 0, 'C');
                                }
                                $pdf->Ln();
                            }
                        }
                        $pdf->Cell((25+30+30+20+20+50), 4.5, "Jumlah", '1', 0, 'C');
                        $pdf->Cell($Width2[10], 4.5, $var['lemburAktual'], '1', 0, 'C');
                        $pdf->Cell($Width2[11], 4.5, $var['hitungLembur'], '1', 0, 'C');
                        $pdf->Cell($Width2[12], 4.5, $var['shiftMalam'], '1', 0, 'C');
                        $pdf->Cell($Width2[13], 4.5, $var['lemburLn'], '1', 0, 'C');
                        $pdf->Cell($Width2[14], 4.5, $var['hitungLn'], '1', 0, 'C');
                        $pdf->Cell($Width2[15], 4.5, $var['totLem'], '1', 0, 'C');
                    }
                    $pdf->Ln();
                    $ch = 'Copyright Indah Jaya Textile Industry, PT. Print Date, '.Carbon::now()->format('d-m-Y H:i:s');
                    
                    $pdf->Cell(array_sum($Width), 4, $ch, 0, 0, 'R');
                }
                $pdf->Output('Laporan Absen Detail.pdf', 'I');
            }
            else
            {
                return abort(404,'Not Found');
            }
        }
        catch(Exception $e)
        {
            $e->getMessage();
        }
    }
    
    public function laporanKomulatif(Request $request)
    {
        $req = $request->all();
        
        $ret = $this->lDet($req);
//        dd($ret);
        $send = [];
        $curDate = Carbon::now();
        
        if(isset($ret['msg']))
        {
            foreach($ret['msg'] as $kVar => $vVar)
            {
                $sendTemp = [];
                $tLembur = 0;
                $jGp = 0;
                $jJk = 0;
                $shift3Real = 0;
                $s3v = 0;
                $s3Total = 0;
                $pm = 0;
                $jm = 0;
                $ins = 0; //insentif
                $lN = 0;
                                
                $siTukangLembur = 0; //rutin banget sih lemburnya
                
                $lemburAktual = 0;
                
                
                $sendTemp['no'] = $kVar+1;
                $sendTemp['pin'] = isset($vVar['karyawan']->pin)?$vVar['karyawan']->pin:'';
                $sendTemp['tmk'] = isset($vVar['karyawan']->tanggal_masuk)?$vVar['karyawan']->tanggal_masuk:'';
                $sendTemp['jenkel'] = isset($vVar['karyawan']->jeniskelamin->nama)?$vVar['karyawan']->jeniskelamin->nama:'';
                $sendTemp['kd_divisi'] = isset($vVar['karyawan']->divisi->kode)?$vVar['karyawan']->divisi->kode:'';
                $sendTemp['nm_divisi'] = isset($vVar['karyawan']->divisi->deskripsi)?$vVar['karyawan']->divisi->deskripsi:'';
                $sendTemp['nama'] = isset($vVar['karyawan']->nama)?$vVar['karyawan']->nama:'';
//                dd($vVar['absen']);
                foreach($vVar['absen'] as $kabs => $vabs) 
                {
                    $lbl = '';
                    $isTa = false;
                    
                    if(isset($vabs->inout))
                    {
                        $lbl = $vabs->inout;
                    }
                    else if(isset($vabs->is_off))
                    {
//                        $lbl = $vabs->keterangan;
                        if(isset($vabs->jam_masuk) && isset($vabs->jam_keluar))
                        {
                            if(isset($vabs->total_lembur))
                            {
                                $lbl = $vabs->total_lembur;
                                $tLembur += $vabs->total_lembur;
                            }
                            else
                            {
                                $lbl = '0';
                            }
                        }
                        else if(isset($vabs->alasan))
                        {                            
                            foreach($vabs->alasan as $als)
                            {
                                if($als->libur == 'Y' && $als->kode != 'RM')
                                {
                                    $lbl = $als->kode;
                                    break;
                                }
                                else
                                {
                                    $lbl = 'RM';
                                }
                            }
                        }
                        else
                        {
                            $lbl = 'RM';
                        }
                    }
                    else if(isset($vabs->libur))
                    {
                        if(isset($vabs->alasan))
                        {                            
                            foreach($vabs->alasan as $als)
                            {
                                if($als->kode == 'SPO')
                                {
                                    $lbl = $vabs->total_lembur;
                                    $tLembur += $vabs->total_lembur;
                                    break;
                                }
                                else if($als->libur == 'Y')
                                {
                                    if($als->kode == 'LN')
                                    {
                                        if($vabs->total_lembur)
                                        {
                                            $lbl = $vabs->total_lembur;
                                            $tLembur += $vabs->total_lembur;
                                        }
                                        else
                                        {
                                            $lbl = $als->kode;
                                        }
                                    }
                                    else
                                    {
                                        $lbl = $als->kode;
                                    }
                                    
                                    break;
                                }
                                else
                                {
                                    $lbl = $als->kode;
                                }
                            }
                        }
                        else
                        {
                            $lbl = '0';
                        }
                    }
                    else if(isset($vabs->mangkir))
                    {
                        if(isset($vabs->alasan))
                        {
                            if($vabs->alasan[0]->kode != 'LN')
                            {
                                $lbl = $vabs->alasan[0]->kode;
                            }
                        }
                        else
                        {
                            $lbl = 'M';
                        }
                    }
                    else if(isset($vabs->ta))
                    {
                        $lbl = 'TA';
                    }
                    else if(isset($vabs->gp))
                    {
                        if(isset($vabs->alasan))
                        {
                            if($vabs->alasan[0]->kode != 'LN')
                            {
                                if($vabs->alasan[0]->kode == 'GP')
                                {
                                    $jGp+=$vabs->gp;
                                    $jJk += $vabs->jumlah_jam_kerja;
                                }
                                $lbl = $vabs->alasan[0]->kode;
                            }
                        }
                        else
                        {
                            $lbl = 'GP';
                            $jGp+=$vabs->gp;
                            $jJk += $vabs->jumlah_jam_kerja;
                        }

                        if($vabs->total_lembur)
                        {
                            $tLembur += $vabs->total_lembur;
                        }
                    }
                    else if(isset($vabs->total_lembur))
                    {
                        $lbl = $vabs->total_lembur;
                        $tLembur += $vabs->total_lembur;
                    }
                    else if(isset($vabs->jam_masuk) && isset($vabs->jam_keluar))
                    {
                        $lbl = '0';
                    }
                    else if(isset($vabs->libur_nasional))
                    {
                        if($vabs->libur_nasional)
                        {
                            if(isset($vabs->total_lembur))
                            {
                                $lbl = $vabs->total_lembur;
                                $tLembur += $vabs->total_lembur;
                            }
                            else{
                                $lbl = 'LN';
                            }
                        }
                    }
                    
                    if(isset($vabs->shift3))
                    {
                        if($vabs->shift3 == 1)
                        {
                            $dtProc = Carbon::createFromFormat('Y-m-d', $vabs->tanggal);
                            
                            if($curDate->diffInDays($dtProc, false) < 1)
                            {
                                if(!$vabs->libur_nasional)
                                {
                                    $s3Total+=1;
                                }
                                else if(isset($vabs->jam_masuk) && isset($vabs->jam_keluar))
                                {
                                    $s3Total+=1;
                                }
                                else if(isset($vabs->alasan))
                                {
                                    foreach($vabs->alasan as $als)
                                    {
                                        if($als->kode == 'C' && config('global.perusahaan_short') == 'AIC')
                                        {
                                            $s3Total+=1;
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            if(isset($vabs->jam_masuk) && isset($vabs->jam_keluar))
                            {
                                $shift3Real+=1;
                            }
                            else if(isset($vabs->libur))
                            {
                                if($vabs->libur)
                                {
                                    if(isset($vabs->alasan))
                                    {
                                        $ada = false;
                                        foreach($vabs->alasan as $als)
                                        {
                                            if($als->kode == 'C' && config('global.perusahaan_short') == 'AIC')
                                            {
                                                $ada = true;
                                            }
                                        }
                                        
                                        if($ada)
                                        {
                                            $shift3Real++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    if(isset($vabs->alasan))
                    {
                        $ada = false;
                        foreach($vabs->alasan as $als)
                        {
                            if($als->kode == 'PM')
                            {
                                $ada = true;
                            }
                        }

                        if($ada)
                        {
                            $pm += 1;
                        }
                    }
                    
                    if(isset($vabs->lembur_aktual))
                    {
                        $lemburAktual += $vabs->lembur_aktual;
                    }
                    
                    if(isset($vabs->jam_masuk) && isset($vabs->jam_keluar))
                    {
                        $jm++;
                    }
                    if(isset($vabs->kode_jam_kerja))
                    {
                        if(substr($vabs->kode_jam_kerja,0,1) == "J" || 
                           substr($vabs->kode_jam_kerja,0,1) == "S" || 
                           substr($vabs->kode_jam_kerja,0,1) == "P")
                        {
                            $siTukangLembur++;
                        }
                    }

                    if(isset($vabs->lembur_ln) && config('global.perusahaan_short') == 'AIC')
                    {
                        $lemburAktual += ((float) $vabs->lembur_ln);
                    }
                    
                    $sendTemp['detail'][] = $lbl;
                }
                
                if(isset($vVar['karyawan']))
                {
                    $gapok = $vVar['karyawan']->salaryGapokTanggal(end($ret['periode'])->toDateString())->first();
                    
                    if($gapok)
                    {
                        $gapok = $gapok->pivot->nilai;
                        // dd($siTukangLembur);
                        if($gapok >= 2350000)
                        {
                            if($lemburAktual)
                            {
                                if($siTukangLembur)
                                {
                                    $ins = $this->hitungSpl($lemburAktual);
                                }
                                else
                                {
                                    $ins = floor($lemburAktual/2);
                                }
                            }
                        }
                    }
                }
                
                if($s3Total)
                {
                    if($s3Total >= 3)
                    {
                        // dd(['s3total' => $s3Total, 's3real' => $shift3Real]);
                        if($s3Total == $shift3Real)
                        {
                            $s3v = 1;
                        }
                        else if(($s3Total - $shift3Real) == 1)
                        {
                            $s3v = 0.5;
                        }
                        else if(($s3Total - $shift3Real) > 1)
                        {
                            $s3v = 0;
                        }
                    }
                    else
                    {
                        $s3v = 0;
                    }
                }
                
                
                
                $sendTemp['tLembur'] = $tLembur;
                $sendTemp['lemburAktual'] = $lemburAktual;
                $sendTemp['s3'] = $shift3Real;
                $sendTemp['gp'] = ($jGp/60);
                $sendTemp['jk'] = $jJk;
                $sendTemp['s3v'] = $s3v;
                $sendTemp['pm'] = $pm;
                $sendTemp['jm'] = $jm;
                $sendTemp['ins'] = $ins;
                
                $send[] = $sendTemp;
            }
            
            
        }
//        dd($send);
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.komulatif.preview', ['var' => $send, 
                'periode' => $ret['periode'], 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('L', PDF_UNIT, 'F4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(3, 23, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('helvetica', '', 8);
            
            $pdf->setHeaderData(config('global.img_laporan'), 10, "Laporan Kehadiran Karyawan Komulatif","Periode : ".reset($ret['periode'])->toDateString()." s/d ".end($ret['periode'])->toDateString());
            $pdf->AddPage();
//            $headTbl1 = array('No' => 6,'PIN' => 13, 'TMK' => 18,'SEX' => 7, 'Kode' => 15, 'Divisi' => 25, 'Nama' => 40);
            $headTbl1 = array('No' => 6,'PIN' => 13, 'TMK' => 18,'SEX' => 7, 'Kode' => 15, 'Nama' => 40);
            $headTbl2 = array('Lbr' => 7, 'S3' => 7, 'GP' => 7, 'JK' => 7, 'S3V' => 7, 'PM' => 7, 'JM' => 7, 'INS' => 7);
            
            foreach($headTbl1 as $kH => $vH)
            {
                $pdf->Cell($vH, 4, $kH, 1, 0, 'C');
            }

            foreach($ret['periode'] as $per)
            {
                $pdf->Cell(5.5, 4, $per->format('d'), 1, 0, 'C');
            }

            foreach($headTbl2 as $kH => $vH)
            {
                $pdf->Cell($vH, 4, $kH, 1, 0, 'C');
            }
            $pdf->Ln();
            
            $line = 'LRB';
            foreach($send as $kVar => $vVar)
            {
                
                $pdf->Cell($headTbl1['No'], 4, $kVar+1, $line, 0, 'C');
                $pdf->Cell($headTbl1['PIN'], 4, $vVar['pin'], $line, 0, 'C');
                $pdf->Cell($headTbl1['TMK'], 4, $vVar['tmk'], $line, 0, 'C');
                $pdf->Cell($headTbl1['SEX'], 4, $vVar['jenkel'], $line, 0, 'C');
                $pdf->Cell($headTbl1['Kode'], 4, $vVar['kd_divisi'], $line, 0, 'C');
//                $pdf->Cell($headTbl1['Divisi'], 4, $vVar['nm_divisi'], $line, 0, 'C');
                $pdf->Cell($headTbl1['Nama'], 4, $vVar['nama'], $line, 0, 'C');
                
                if(isset($vVar['detail']))
                {
                    if(count($vVar['detail']))
                    {
                        foreach($vVar['detail'] as $kabs => $vabs)
                        {                        
                            $pdf->Cell(5.5, 4, $vabs, $line, 0, 'C');
                        }
                    }
                    else
                    {
                        foreach($ret['periode'] as $per)
                        {
                            $pdf->Cell(5.5, 4, '', 1, 0, 'C');
                        }
                    }
                }
                else
                {
                    foreach($ret['periode'] as $per)
                    {
                        $pdf->Cell(5.5, 4, '', 1, 0, 'C');
                    }
                }
                $pdf->Cell($headTbl2['Lbr'], 4, $vVar['tLembur'], 1, 0, 'C');
                $pdf->Cell($headTbl2['S3'], 4, $vVar['s3'], 1, 0, 'C');
                $pdf->Cell($headTbl2['GP'], 4, $vVar['gp'], 1, 0, 'C');
                $pdf->Cell($headTbl2['JK'], 4, $vVar['jk'], 1, 0, 'C');
                $pdf->Cell($headTbl2['S3V'], 4, $vVar['s3v'], 1, 0, 'C');
                $pdf->Cell($headTbl2['PM'], 4, $vVar['pm'], 1, 0, 'C');
                $pdf->Cell($headTbl2['JM'], 4, $vVar['jm'], 1, 0, 'C');
                $pdf->Cell($headTbl2['INS'], 4, $vVar['ins'], 1, 0, 'C');
                $pdf->Ln();
                
            }
            $pdf->Output('Laporan Absen Komulatif.pdf', 'I');
        }
        else if($req['btnSubmit'] == "excel")
        {
            $ss = new Spreadsheet();
            $ss->getProperties()
                ->setCreator('Taufiq Hari Widodo')
                ->setLastModifiedBy('Taufiq Hari Widodo')
                ->setTitle('Laporan Absen Komulatif')
                ->setSubject('Laporan Absen Komulatif')
                ->setDescription('Laporan Absen Komulatif')
                ->setKeywords('laporan indahjaya karyawan')
                ->setCategory('Laporan Excel');
            
            $styleHead1 = [
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                        'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                        ]
                ],
                'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                                'rgb' => 'a0a0a0'
                        ]
                ]
            ];
            $ss->createSheet(0);
            $ss->setActiveSheetIndex(0);
            $ss->getActiveSheet()->setTitle('Komulatif');
            
            $ss->getActiveSheet()->setCellValue('A1', 'Laporan Kehadiran Karyawan Komulatif');
            $ss->getActiveSheet()->setCellValue('A2', "Periode : ".reset($ret['periode'])->toDateString()." s/d ".end($ret['periode'])->toDateString());
            $mergeHead = 11 + count($ret['periode']);
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,1,$mergeHead,1);
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,2,$mergeHead,2);
            
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,1,$mergeHead,1)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 16,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,2,$mergeHead,2)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
             
            $rowStart = 4;
            $colStat = 1;
//            $headTbl1 = array('No','PIN', 'TMK','SEX', 'Kode', 'Divisi', 'Nama');
            $headTbl1 = array('No','PIN', 'TMK','SEX', 'Kd. Div',  'Nama');
            $headTbl2 = array('Lbr', 'LbrA','S3', 'GP', 'JK', 'S3V', 'PM', 'JM', 'INS');
            foreach($headTbl1 as $rHead)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
            }
            foreach($ret['periode'] as $per)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $per->format('d'));
            }
            foreach($headTbl2 as $rHead)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
            }
            
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,$rowStart,$colStat-1,$rowStart)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10,
                            'bold' => true
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ],
                    'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                    'rgb' => 'a0a0a0'
                            ]
                    ]
                ]);
            
            $rowStart++;
            foreach($send as $kVar => $vVar)
            {     
                $colStat = 1;           
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $kVar+1);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['pin']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['tmk']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['jenkel']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['kd_divisi']);
//                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['nm_divisi']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['nama']);
                
                if(isset($vVar['detail']))
                {
                    if(count($vVar['detail']))
                    {
                        foreach($vVar['detail'] as $kabs => $vabs)
                        {             

                            $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vabs);
                        }
                    }
                    else
                    {
                        foreach($ret['periode'] as $per)
                        {
                            $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, '');
                        }
                    }
                }
                else
                {
                    foreach($ret['periode'] as $per)
                    {
                        $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, '');
                    }
                }
                
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['tLembur']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['lemburAktual']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['s3']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['gp']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['jk']);
                
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['s3v']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['pm']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['jm']);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $vVar['ins']);
                
                $rowStart++;
                
            }
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,5,$colStat-1,$rowStart-1)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ]
                ]);
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="komulatif.xls"');
            header('Cache-Control: max-age=0');
            
            $writer = IOFactory::createWriter($ss, 'Xlsx');
            $writer->setPreCalculateFormulas(true);
            $writer->save('php://output');
            exit;
        }
        else
        {
            return abort(404,'Not Found');
        }
    }
    
    public function laporanKaryawanAktif(Request $request)
    {
        $req = $request->all();
        
        
        $karAktif       = Karyawan::with('divisi', 'jabatan', 'jadwals', 'log_off', 'golongan')->where('active_status',1)->author();
//        $karNonAktif    = Karyawan::with('divisi', 'jabatan', 'jadwals')->where('active_status',2)->author();
        
        $tanggal = Carbon::now()->toDateString();
        
        if(isset($req['tanggal']))
        {
            $tanggal =  $req['tanggal'];
        }
//        $karNonAktif->where('active_status_date', '>', $tanggal);
        
        if(isset($req['divisi']))
        {
            $karAktif->where('divisi_id', $req['divisi']);
//            $karNonAktif->where('divisi_id', $req['divisi']);
        }
        
        if(isset($req['perusahaan']))
        {
            $karAktif->where('perusahaan_id', $req['perusahaan']);
//            $karNonAktif->where('perusahaan_id', $req['perusahaan']);
        }
        
        $karAktif->orderBy('divisi_id', 'asc')->orderBy('pin', 'asc');
//        $karNonAktif->orderBy('divisi_id', 'asc')->orderBy('pin', 'asc');
//        dd($karAktif->get());
        $karyawan = null;
        
        foreach($karAktif->get() as $kA)
        {
            $off = $kA->logOffTanggal($req['tanggal'])->first();      
            
            if($off)
            {
                if($off->kode != 'AKT')
                {
                    continue;
                }
            }
            
            $jadwal = $kA->jadwals;
            $karyawan[] = ['nik' => ((isset($kA->nik))?$kA->nik:''),
                           'pin' => ((isset($kA->pin))?$kA->pin:''),
                           'nama'=> ((isset($kA->nama))?$kA->nama:''),
                           'jenkel'=> ((isset($kA->jeniskelamin->nama))?$kA->jeniskelamin->nama:''),
                           'tanggal_lahir'=> ((isset($kA->tanggal_lahir))?$kA->tanggal_lahir:''),
                           'golongan' => ((isset($kA->golongan->nama))?$kA->golongan->nama:''),
                           'kode_divisi' => ((isset($kA->divisi->kode))?$kA->divisi->kode:''),
                           'nama_divisi' => ((isset($kA->divisi->deskripsi))?$kA->divisi->deskripsi:''),
                           'kode_jabatan' => ((isset($kA->jabatan->kode))?$kA->jabatan->kode:''),
                           'nama_jabatan' => ((isset($kA->jabatan->deskripsi))?$kA->jabatan->deskripsi:''),
                           'tmk' => ((isset($kA->tanggal_masuk))?$kA->tanggal_masuk:''),
                           'jadwal' =>((isset($jadwal[0]))?$jadwal[0]->kode.' - '.$jadwal[0]->tipe:'') ];
            
        }
        
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.karyawan_aktif.preview', ['var' => $karyawan, 
                'periode' => $tanggal, 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
        }
        else if($req['btnSubmit'] == "excel")
        {
            $ss = new Spreadsheet();
            $ss->getProperties()
                ->setCreator('Taufiq Hari Widodo')
                ->setLastModifiedBy('Taufiq Hari Widodo')
                ->setTitle('Laporan Absen Komulatif')
                ->setSubject('Laporan Karyawan Aktif')
                ->setDescription('Laporan Karyawan Aktif')
                ->setKeywords('laporan indahjaya karyawan')
                ->setCategory('Laporan Excel');
            
            $styleHead1 = [
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                        'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                        ]
                ],
                'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                                'rgb' => 'a0a0a0'
                        ]
                ]
            ];
            $ss->createSheet(0);
            $ss->setActiveSheetIndex(0);
            $ss->getActiveSheet()->setTitle('Laporan Karyawan Aktif');
            
            $ss->getActiveSheet()->setCellValue('A1', 'Laporan Karyawan Aktif');
            $ss->getActiveSheet()->setCellValue('A2', 'Periode : '.$tanggal);
            $mergeHead = 10;
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,1,$mergeHead,1);
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,2,$mergeHead,2);
            
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,1,$mergeHead,1)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 16,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,2,$mergeHead,2)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
             
            $rowStart = 4;
            $colStat = 1;
            $headTbl1 = array('No','PIN', 'Nama', 'Jenis', 'Tanggal', 'Kode', 'Nama', 'Tanggal', 'Kode', 'Nama', 'Kode');
			$headTbl2 = array('','', 'Karyawan','Kelamin','Lahir','Jabatan', 'Jabatan', 'Masuk', 'Divisi', 'Divisi', 'Jadwal');
            if(config('global.perusahaan_short') == 'AIC')
            {
                $headTbl1 = array('No','PIN', 'Nama', 'Jenis', 'Tanggal','Kode', 'Nama', 'Gol', 'Tanggal', 'Kode', 'Nama', 'Kode');
                $headTbl2 = array('','', 'Karyawan','Kelamin','Lahir','Jabatan', 'Jabatan', '', 'Masuk', 'Divisi', 'Divisi', 'Jadwal');
            }
            foreach($headTbl1 as $rHead)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
            }
			
            $colStat = 1;
			$rowStart++;
			
            foreach($headTbl2 as $rHead)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
            }
            
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,$rowStart-1,$colStat-1,$rowStart)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10,
                            'bold' => true
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ],
                    'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                    'rgb' => 'a0a0a0'
                            ]
                    ]
                ]);
            
            $rowStart++;
            $colStat = 1;
			
            if(count($karyawan))
            {
                foreach($karyawan as $kKar => $vKar)
                {
                    $colStat = 1;
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $kKar+1);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['pin'])?$vKar['pin']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['nama'])?$vKar['nama']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['jenkel'])?$vKar['jenkel']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['tanggal_lahir'])?$vKar['tanggal_lahir']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['kode_jabatan'])?$vKar['kode_jabatan']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['nama_jabatan'])?$vKar['nama_jabatan']:'');
                    if(config('global.perusahaan_short') == 'AIC')
                    {
                            $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['golongan'])?$vKar['golongan']:'');
                    }
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['tmk'])?$vKar['tmk']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['kode_divisi'])?$vKar['kode_divisi']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['nama_divisi'])?$vKar['nama_divisi']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['jadwal'])?$vKar['jadwal']:'');

                    $rowStart++;
                }
            }
			
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,6,$colStat-1,$rowStart-1)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ]
                ]);
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Laporan Karyawan Aktif.xls"');
            header('Cache-Control: max-age=0');
            
            $writer = IOFactory::createWriter($ss, 'Xlsx');
            $writer->setPreCalculateFormulas(true);
            $writer->save('php://output');
            exit;
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(3, 23, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('helvetica', '', 8);
            
            $pdf->setHeaderData(config('global.img_laporan'), 10, "Laporan Karyawan Aktif","Periode : ".$tanggal);
            $pdf->AddPage();
            $headTbl1 = array('No','PIN', 'Nama','Jenis','Tanggal','Kode', 'Nama', 'Tanggal', 'Kode', 'Nama', 'Kode');
            $headW = array(10,15,70,12,17,11,30,17,13,70,20);
            $headTbl2 = array('','', 'Karyawan','Kelamin','Lahir','Jabatan', 'Jabatan', 'Masuk', 'Divisi', 'Divisi', 'Jadwal');
            
            if(config('global.perusahaan_short') == 'AIC')
            {
                $headTbl1 = array('No','PIN', 'Nama','Jenis','Tanggal','Kode', 'Nama', 'Gol', 'Tanggal', 'Kode', 'Nama', 'Kode');
                $headW = array(10,15,65,12,17,11,30,10,17,13,70,20);
                $headTbl2 = array('','', 'Karyawan','Kelamin','Lahir','Jabatan', 'Jabatan', '', 'Masuk', 'Divisi', 'Divisi', 'Jadwal');
            }
            
            foreach($headTbl1 as $kH => $vH)
            {
                $pdf->Cell($headW[$kH], 4, $vH, 'LRT', 0, 'C');
            }
            $pdf->Ln();
            foreach($headTbl2 as $kH => $vH)
            {
                $pdf->Cell($headW[$kH], 4, $vH, 'LRB', 0, 'C');
            }
            $pdf->Ln();
            if(count($karyawan))
            {
                foreach($karyawan as $kKar => $vKar)
                {
                    $y = 0;
                    $pdf->Cell($headW[$y++], 4, $kKar+1, 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['pin'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['nama'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['jenkel'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['tanggal_lahir'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['kode_jabatan'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['nama_jabatan'], 'LRB', 0, 'C');
                    
                    if(config('global.perusahaan_short') == 'AIC')
                    {
                        $pdf->Cell($headW[$y++], 4, $vKar['golongan'], 'LRB', 0, 'C');
                    }
                    
                    $pdf->Cell($headW[$y++], 4, $vKar['tmk'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['kode_divisi'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, substr($vKar['nama_divisi'],0,20), 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['jadwal'], 'LRB', 0, 'C');
                    $pdf->Ln();
                }
            }
            $pdf->Output('Laporan Karyawan Aktif.pdf', 'I');
        }
        else
        {
            return abort(404,'Not Found');
        }
    }
    
    public function laporanKaryawanNonAktif(Request $request)
    {
        $req = $request->all();

        $tanggal = [];
        $tanggalRange = [];

        $ret = [];

        if(isset($req['tanggal']))
        {
            $tanggal =  explode(' - ', $req['tanggal']);
            $tanggalRange = CarbonPeriod::create($tanggal[0], $tanggal[1])->toArray();
        }
                
        $karNonAktif    = Karyawan::with('divisi', 'jabatan', 'jadwals')->whereIn('active_status',[2,3])->author()->whereBetween('active_status_date', $tanggal);
              
        $logOff = Karyawan::with('divisi', 'jabatan', 'jadwals', 'log_off')
                    ->whereHas('log_off', function($q) use($tanggal)
                    {
                        $q->where('kode', 'RM');
                        $q->where('off_karyawan_log.tanggal','<=',$tanggal[1]);
                        // $q->whereBetween('off_karyawan_log.tanggal', $tanggal);
                    })
                    ->where('active_status', 1);
        
        if(isset($req['perusahaan']))
        {
            $karNonAktif->where('perusahaan_id', $req['perusahaan']);
            $logOff->where('perusahaan_id', $req['perusahaan']);
        }

        if(isset($req['divisi']))
        {
            $div = Divisi::descendantsAndSelf($req['divisi'])->pluck('id');

            $karNonAktif->whereIn('divisi_id', $div);
            $logOff->whereIn('divisi_id', $div);
        }

        foreach($karNonAktif->get() as $kA)
        {
            $ret[] = array(
                'pin' => (isset($kA->pin)?$kA->pin:''),
                'nik' => (isset($kA->nik)?$kA->nik:''),
                'tanggal_masuk' => (isset($kA->tanggal_masuk)?$kA->tanggal_masuk:''),
                'nama_karyawan' => (isset($kA->nama)?$kA->nama:''),
                'kode_divisi' => (isset($kA->divisi)?$kA->divisi->kode:''),
                'nama_divisi' => (isset($kA->divisi)?$kA->divisi->deskripsi:''),
                'tanggal_set' => (isset($kA->active_status_date)?$kA->active_status_date:''),
                'tanggal_start' => null,
                'tanggal_end' => null,
                'keterangan' => (isset($kA->active_comment)?$kA->active_comment:'')
            );            
        }

        foreach($logOff->get() as $kA)
        {
            $lF = $kA->log_off()->where('kode','RM')
                                ->where('off_karyawan_log.tanggal','<=',$tanggal[1])
                                ->orderBy('off_karyawan_log.tanggal', 'desc')
                                ->first();
            $lA = $kA->log_off()->where('kode','AKT')
                                ->where('off_karyawan_log.tanggal','<=',$tanggal[1])
                                ->orderBy('off_karyawan_log.tanggal', 'desc')
                                ->first();
            
            $tanggal_start = null;
            $tanggal_end = null;

            $tglA = null;
            $tglF = null;

            if($lA)
            {
                $tglA = Carbon::createFromFormat('Y-m-d', $lA->pivot->tanggal);

            }

            if($lF)
            {
                $tglF = Carbon::createFromFormat('Y-m-d', $lF->pivot->tanggal);
            }

            if($tglA)
            {
                if($tanggalRange[0]->diffInDays($tglA, false) < 0)
                {
                    continue;
                }

                $tanggal_end = $tglA->copy()->subDay(1)->toDateString();
                
                
                if($tglF)
                {
                    if($tglF->diffInDays($tanggalRange[0], false) < 0)
                    {
                        $tanggal_start = $tglF->toDateString();
                    }
                    else
                    {
                        $tanggal_start = $tanggalRange[0]->toDateString();
                    }
                }
            }
            else
            {
                $tanggal_end = end($tanggalRange)->toDateString();
                if($tglF)
                {
                    if($tglF->diffInDays($tanggalRange[0], false) < 0)
                    {
                        $tanggal_start = $tglF->toDateString();
                    }
                    else
                    {
                        $tanggal_start = $tanggalRange[0]->toDateString();
                    }
                }
            }

            $ret[] = array(
                'pin' => (isset($kA->pin)?$kA->pin:''),
                'nik' => (isset($kA->nik)?$kA->nik:''),
                'tanggal_masuk' => (isset($kA->tanggal_masuk)?$kA->tanggal_masuk:''),
                'nama_karyawan' => (isset($kA->nama)?$kA->nama:''),
                'kode_divisi' => (isset($kA->divisi)?$kA->divisi->kode:''),
                'nama_divisi' => (isset($kA->divisi)?$kA->divisi->deskripsi:''),
                'tanggal_set' => (isset($lF->pivot)?$lF->pivot->tanggal:''),
                'tanggal_awal' => $tanggal_start,
                'tanggal_akhir' => $tanggal_end,
                'keterangan' => (isset($lF->kode)?$lF->kode:'')
            );        
        }
        
        usort($ret, function($a, $b)
        {
            $ret =  $a['tanggal_set'] <=> $b['tanggal_set'];
            if($ret == 0 )
            {
                $ret = $a['pin'] <=> $b['pin'];
            }
            
            return $ret;
        });
        // dd($ret);
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.status_non_aktif.preview', ['var' => $ret, 
                'periode' => $tanggal[0].' s/d '.$tanggal[1], 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
        }
        else if($req['btnSubmit'] == "excel")
        {
            $ss = new Spreadsheet();
            $ss->getProperties()
                ->setCreator('Taufiq Hari Widodo')
                ->setLastModifiedBy('Taufiq Hari Widodo')
                ->setTitle('Laporan Absen Komulatif')
                ->setSubject('Laporan Karyawan Non Aktif')
                ->setDescription('Laporan Karyawan Non Aktif')
                ->setKeywords('laporan indahjaya karyawan')
                ->setCategory('Laporan Excel');
            
            $styleHead1 = [
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                        'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                        ]
                ],
                'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                                'rgb' => 'a0a0a0'
                        ]
                ]
            ];
            $ss->createSheet(0);
            $ss->setActiveSheetIndex(0);
            $ss->getActiveSheet()->setTitle('Laporan Karyawan Non Aktif');
            
            $ss->getActiveSheet()->setCellValue('A1', 'Laporan Karyawan Non Aktif');
            $ss->getActiveSheet()->setCellValue('A2', 'Periode : '.$tanggal[0].' s/d '.$tanggal[1]);
            $mergeHead = 10;
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,1,$mergeHead,1);
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,2,$mergeHead,2);
            
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,1,$mergeHead,1)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 16,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,2,$mergeHead,2)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
             
            $rowStart = 4;
            $colStat = 1;
            $headTbl1 = array('No','PIN', 'NIK','Tanggal','Nama', 'Kode', 'Nama', 'Tanggal', 'Tanggal', 'Tanggal','Keterangan');
			$headTbl2 = array('','', '','Masuk','Karyawan', 'Divisi', 'Divisi', 'Set', 'Awal', 'Akhir','');
            foreach($headTbl1 as $rHead)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
            }
			
            $colStat = 1;
			$rowStart++;
			
            foreach($headTbl2 as $rHead)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
            }
            
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,$rowStart-1,$colStat-1,$rowStart)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10,
                            'bold' => true
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ],
                    'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                    'rgb' => 'a0a0a0'
                            ]
                    ]
                ]);
            
            $rowStart++;
            $colStat = 1;
			
            if(count($ret))
            {
                foreach($ret as $kKar => $vKar)
                {
                    $colStat = 1;
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $kKar+1);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['pin'])?$vKar['pin']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['nik'])?$vKar['nik']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['tanggal_masuk'])?$vKar['tanggal_masuk']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['nama_karyawan'])?$vKar['nama_karyawan']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['kode_divisi'])?$vKar['kode_divisi']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['nama_divisi'])?$vKar['nama_divisi']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['tanggal_set'])?$vKar['tanggal_set']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['tanggal_awal'])?$vKar['tanggal_awal']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['tanggal_akhir'])?$vKar['tanggal_akhir']:'');
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($vKar['keterangan'])?$vKar['keterangan']:'');

                    $rowStart++;
                }
            }
			
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,6,$colStat-1,$rowStart-1)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ]
                ]);
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Laporan Karyawan Non Aktif.xls"');
            header('Cache-Control: max-age=0');
            
            $writer = IOFactory::createWriter($ss, 'Xlsx');
            $writer->setPreCalculateFormulas(true);
            $writer->save('php://output');
            exit;
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(3, 23, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('helvetica', '', 8);
            
            $pdf->setHeaderData(config('global.img_laporan'), 10, "Laporan Karyawan Aktif","Periode : ".$tanggal);
            $pdf->AddPage();
            $headTbl1 = array('No','PIN', 'NIK','Tanggal','Nama', 'Kode', 'Nama', 'Tanggal', 'Tanggal', 'Tanggal', 'Keterangan');
            $headW = array(10,15,50,10,30,15,13,40,20);
            $headTbl2 = array('','', '','Masuk','Karyawan', 'Divisi', 'Divisi', 'Set', 'Awal', 'Akhir','');
            
            foreach($headTbl1 as $kH => $vH)
            {
                $pdf->Cell($headW[$kH], 4, $vH, 'LRT', 0, 'C');
            }
            $pdf->Ln();
            foreach($headTbl2 as $kH => $vH)
            {
                $pdf->Cell($headW[$kH], 4, $vH, 'LRB', 0, 'C');
            }
            $pdf->Ln();
            if(count($ret))
            {
                foreach($ret as $kKar => $vKar)
                {
                    $y = 0;
                    $pdf->Cell($headW[$y++], 4, $kKar+1, 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['pin'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['nik'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['tanggal_masuk'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['nama_karyawan'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['kode_divisi'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['nama_divisi'], 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, substr($vKar['tanggal_set'],0,20), 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, substr($vKar['tanggal_awal'],0,20), 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, substr($vKar['tanggal_akhir'],0,20), 'LRB', 0, 'C');
                    $pdf->Cell($headW[$y++], 4, $vKar['keterangan'], 'LRB', 0, 'C');
                    $pdf->Ln();
                }
            }
            $pdf->Output('Laporan Karyawan Non Aktif.pdf', 'I');
        }
        else
        {
            return abort(404,'Not Found');
        }
    }
    
    public function laporanKaryawanMangkirTa(Request $request)
    {
        $req = $request->all();
        // dd($req);
        $karRet = null;
        
        $absen       = Prosesabsen::where(function($q)
        {
            $q->where('ta', 1)->orWhere('mangkir', 1);
        });
        
        if(isset($req['tanggal']))
        {
            $tgl = explode(" - ", $req['tanggal']);
            
            $absen->whereBetween('tanggal', [reset($tgl), end($tgl)]);
        }
        
        if(isset($req['divisi']))
        {
            $absen->whereHas('karyawan', function($q) use($req)
            {
                $q->karyawanTerlihat()->where('divisi_id', $req['divisi']);
            });
        }
        
        if(isset($req['perusahaan']))
        {
            $absen->whereHas('karyawan', function($q) use($req)
            {
                $q->karyawanTerlihat()->where('perusahaan_id', $req['perusahaan']);
            });
        }  
        // dd(Auth::user()->type->nama);
        if(Auth::user()->type->nama == 'REKANAN')
        {
            $absen->whereHas('karyawan', function($q) use($req)
            {
                $q->karyawanTerlihat()->where('perusahaan_id', Auth::user()->perusahaan->id);
            });
        }
        
        $absen->orderBy('karyawan_id', 'asc');
        
        foreach($absen->get() as $absenRow)
        {
            $ket = '';
            
            if($absenRow->ta == 1)
            {
                if(empty($absenRow->jam_masuk))
                {
                    $ket = 'TIDAK ABSEN MASUK';
                }
                else if(empty($absenRow->jam_keluar))
                {
                    $ket = 'TIDAK ABSEN PULANG';
                }
            }
            else if($absenRow->mangkir == 1)
            {
                $ket = 'MANGKIR';
            }
            
            $divisi = ['kode' => '', 'deskripsi' => ''];

            if(isset($absenRow->karyawan->divisi->kode))
            {
                $divisi['kode'] = $absenRow->karyawan->divisi->kode;
                $divisi['deskripsi'] = $absenRow->karyawan->divisi->deskripsi;
            }
            if(!isset($absenRow->karyawan))
            {
                continue;
            }

            $karRet[] = [
                'pin' => $absenRow->karyawan->pin,
                'nama_karyawan' => $absenRow->karyawan->nama,
                'tanggal_masuk' => $absenRow->karyawan->tanggal_masuk,
                'kode_divisi' => $divisi['kode'],
                'nama_divisi' => $divisi['deskripsi'],
                'tanggal' => $absenRow->tanggal,
                'jadwal_kerja' => substr($absenRow->jadwal_jam_masuk,0,5).' - '.substr($absenRow->jadwal_jam_keluar,0,5),
                'jam_kerja' => (!empty($absenRow->jam_masuk)?substr($absenRow->jam_masuk,0,5):'00:00').' - '.(!empty($absenRow->jam_keluar)?substr($absenRow->jam_keluar,0,5):'00:00'),
                'keterangan' => $ket
            ];
        }
        
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.karyawan_mangkir_ta.preview', ['var' => $karRet, 
                'periode' => $req['tanggal'], 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(10, 23, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('helvetica', '', 8);
            
            $pdf->setHeaderData(config('global.img_laporan'), 10, "Laporan Karyawan Tidak Absen","Periode : ".$req['tanggal']);
            $pdf->AddPage();
            $headTbl1 = array('No','PIN', 'Nama', 'Tanggal', 'Kode', 'Nama',  'Tanggal','Jadwal', 'Jam', 'Keterangan');
            $headTbl2 = array('','', 'Karyawan','Masuk', 'Divisi', 'Divisi', 'Tidak Absen', 'Kerja', 'Kerja', '');
            $headW = array(10,15,60,20,20,35,20,30,30,40);
            
            foreach($headTbl1 as $kH => $vH)
            {
                $pdf->Cell($headW[$kH], 4, $vH, 'LRT', 0, 'C');
            }
            $pdf->Ln();
            foreach($headTbl2 as $kH => $vH)
            {
                $pdf->Cell($headW[$kH], 4, $vH, 'LRB', 0, 'C');
            }
            $pdf->Ln();
            if(count($karRet))
            {
                foreach($karRet as $kKar => $vKar)
                {
                    $pdf->Cell($headW[0], 4, $kKar+1, 1, 0, 'C');
                    $pdf->Cell($headW[1], 4, $vKar['pin'], 1, 0, 'C');
                    $pdf->Cell($headW[2], 4, $vKar['nama_karyawan'], 1, 0, 'C');
                    $pdf->Cell($headW[3], 4, $vKar['tanggal_masuk'], 1, 0, 'C');
                    $pdf->Cell($headW[4], 4, $vKar['kode_divisi'], 1, 0, 'C');
                    $pdf->Cell($headW[5], 4, $vKar['nama_divisi'], 1, 0, 'C');
                    $pdf->Cell($headW[6], 4, $vKar['tanggal'], 1, 0, 'C');
                    $pdf->Cell($headW[7], 4, $vKar['jadwal_kerja'], 1, 0, 'C');
                    $pdf->Cell($headW[8], 4, $vKar['jam_kerja'], 1, 0, 'C');
                    $pdf->Cell($headW[9], 4, $vKar['keterangan'], 1, 0, 'C');
                    $pdf->Ln();
                }
            }
            
            $pdf->Output('Laporan Karyawan Mangkir/TA.pdf', 'I');
        }
        else
        {
            return abort(404,'Not Found');
        }
    }
    
    public function laporanKaryawanHabisKontrak(Request $request)
    {
        $req = $request->all();
        
        $karRet = null;
        
        $kar = Karyawan::with('divisi', 'jabatan')->whereHas('status', function($q)
        {
            $q->where('nama', 'K');
        })->karyawanTerlihat()->author();
        
        if(isset($req['tanggal']))
        {
            $tgl = explode(" - ", $req['tanggal']);
            
            $kar->whereBetween('tanggal_kontrak', [reset($tgl), end($tgl)]);
        }
        
        if(isset($req['divisi']))
        {
            $kar->where('divisi_id', $req['divisi']);
        }
        
        if(isset($req['perusahaan']))
        {
            $kar->where('perusahaan_id', $req['perusahaan']);
        }        
        
        $kar->orderBy('pin', 'asc');
        
        if($kar->count())
        {
            foreach($kar->get() as $kRow)
            {

                $karRet[] = [
                    'pin' => $kRow->pin,
                    'nama_karyawan' => $kRow->nama,
                    'tanggal_masuk' => $kRow->tanggal_masuk,
                    'kode_divisi' => $kRow->divisi->kode,
                    'nama_divisi' => $kRow->divisi->deskripsi,
                    'kode_jabatan' => $kRow->jabatan->kode,
                    'nama_jabatan' => $kRow->jabatan->deskripsi,
                    'tanggal' => $kRow->tanggal_kontrak
                ];
    //            dd($karRet);
            }
        }
        
        ///////
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.karyawan_habis_kontrak.preview', ['var' => $karRet, 
                'periode' => $req['tanggal'], 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(10, 23, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('helvetica', '', 8);
            
            $pdf->setHeaderData(config('global.img_laporan'), 10, "Laporan Karyawan Aktif","Periode : ".$req['tanggal']);
            $pdf->AddPage();
            $headTbl1 = array('No','PIN', 'Nama', 'Kode', 'Nama',  'Tanggal', 'Kode','Nama', 'Tanggal');
            $headTbl2 = array('','', 'Karyawan','Jabatan', 'Jabatan', 'Masuk', 'Divisi', 'Divisi', 'Habis Kontrak');
            $headW = array(10,15,60,20,40,20,30,60,20);
            
            foreach($headTbl1 as $kH => $vH)
            {
                $pdf->Cell($headW[$kH], 4, $vH, 'LRT', 0, 'C');
            }
            $pdf->Ln();
            foreach($headTbl2 as $kH => $vH)
            {
                $pdf->Cell($headW[$kH], 4, $vH, 'LRB', 0, 'C');
            }
            $pdf->Ln();
            if(count($karRet))
            {
                foreach($karRet as $kKar => $vKar)
                {
                    $pdf->Cell($headW[0], 4, $kKar+1, 1, 0, 'C');
                    $pdf->Cell($headW[1], 4, $vKar['pin'], 1, 0, 'C');
                    $pdf->Cell($headW[2], 4, substr($vKar['nama_karyawan'],0,30), 1, 0, 'C');
                    $pdf->Cell($headW[3], 4, $vKar['kode_jabatan'], 1, 0, 'C');
                    $pdf->Cell($headW[4], 4, $vKar['nama_jabatan'], 1, 0, 'C');
                    $pdf->Cell($headW[5], 4, $vKar['tanggal_masuk'], 1, 0, 'C');
                    $pdf->Cell($headW[6], 4, $vKar['kode_divisi'], 1, 0, 'C');
                    $pdf->Cell($headW[7], 4, $vKar['nama_divisi'], 1, 0, 'C');
                    $pdf->Cell($headW[8], 4, $vKar['tanggal'], 1, 0, 'C');
                    $pdf->Ln();
                }
            }
            
            $pdf->Output('Laporan Karyawan Habis Kontrak.pdf', 'I');
        }
        else
        {
            return abort(404,'Not Found');
        }
    }
    
    public function laporanKaryawanDaftarHadir(Request $request)
    {
        $req = $request->all();
        
        $ret = [];
        
        $div = Divisi::with(['karyawan' => function($query) use ($req)
        {
            if(Auth::user()->type->nama == 'REKANAN')
            {
                $query->karyawanTerlihat()->where('perusahaan_id', Auth::user()->perusahaan_id);
            }
            else
            {
                if(isset($req['perusahaan']))
                {
                    $query->karyawanTerlihat()->where('perusahaan_id', $req['perusahaan']);
                }
            }
        }]);
        
        if(isset($req['divisi']))
        {
            if(isset($req['divisi_akhir']))
            {
                $dAwal = Divisi::find($req['divisi']);
                $dAkhir = Divisi::find($req['divisi_akhir']);
                $div->whereBetween('kode', [$dAwal->kode, $dAkhir->kode]);
            }
            else
            {
                $div->where('id', $req['divisi']);
            }
        }
                
        $periode = null;
        if(isset($req['tanggal']))
        {
            $tgl = Carbon::createFromFormat('Y-m-d', $req['tanggal'].'-22')->subMonth();

            $periode = CarbonPeriod::create($tgl, $tgl->copy()->addMonth(1)->subDay(1))->toArray();
            
        }
        
        $div->whereRaw('LENGTH(kode)>0');
        // dd($div->get());
        foreach($div->get() as $rowDiv)
        {
            if($rowDiv->karyawan->count() > 0)
            {
                $kar = [];
                
                foreach($rowDiv->karyawan as $rKar)
                {
                    $jadwals = $rKar->jadwals()->first();
                    
                    $off = $rKar->logOffTanggal(end($periode))->first();
                    // dd($off);
                    if(!$off)
                    {
                        $kar[] = [
                            'tanggal_masuk' => $rKar->tanggal_masuk,
                            'pin' => $rKar->pin,
                            'jenkel' => (isset($rKar->jeniskelamin)?$rKar->jeniskelamin->nama:''),
                            'jadwal' => $jadwals['kode'],
                            'nama' => $rKar->nama
                        ];
                    }
                    else
                    {
                        if($off->kode != 'RM')
                        {
                            $kar[] = [
                                'tanggal_masuk' => $rKar->tanggal_masuk,
                                'pin' => $rKar->pin,
                                'jenkel' => (isset($rKar->jeniskelamin)?$rKar->jeniskelamin->nama:''),
                                'jadwal' => $jadwals['kode'],
                                'nama' => $rKar->nama
                            ];
                        }
                    }
                }
                
                usort($kar, function($a, $b)
                {
                    $ret =  $a['jadwal'] <=> $b['jadwal'];
                    if($ret == 0 )
                    {
                        $ret = $a['nama'] <=> $b['nama'];
                    }
                    
                    return $ret;
                });
                
                
                $bag = [];
                
                foreach(Divisi::defaultOrder()->ancestorsAndSelf($rowDiv->id) as $kTree => $dTree)
                {
                    $bag[] = $dTree->kode.' - '.$dTree->deskripsi;
                }
                
                if(count($bag)>=4)
                {
                    $bag = array_slice($bag, count($bag)-4);
                }
                
                $ret[] = [
                    'periode_awal' => reset($periode)->format('d-m-Y'),
                    'periode_akhir' => end($periode)->format('d-m-Y'),
                    'periode' => $periode,
                    'bagian' => implode(' -> ', $bag),
//                    'kode_bagian' => $rowDiv->kode,
//                    'nama_bagian' => $rowDiv->deskripsi,
                    'karyawan' => $kar
                ];
            }
        }
        
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.karyawan_daftar_hadir.preview', ['var' => $ret, 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]
            );
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('L', PDF_UNIT, 'F4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//            $pdf->setPrintFooter(false);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(6, 20, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('helvetica', '', 8);
            
            if(count($ret))
            {
                foreach($ret as $kRet => $vRet)
                {
                    $pdf->setHeaderData();
                    $pdf->setHeaderData(config('global.img_laporan'), 10, "Daftar Hadir Karyawan","Periode : ".$vRet['periode_awal'].' S/D '.$vRet['periode_akhir']);
                    $pdf->AddPage();
                    
                    $infoWidth = array(25,3,300);
                    $pdf->Cell($infoWidth[0], 3, "Unit Kerja");
                    $pdf->Cell($infoWidth[1], 3, ":");
                    $pdf->Cell($infoWidth[2], 3, $vRet['bagian']);
                    $pdf->Ln();
                    $pdf->Ln();
                    
                    $headTbl1 = array('No', 'Nama Karyawan', 'Tanggal', 'L/P',  'PIN', 'Kd Jad','Tanggal', 'Keterangan');
                    $headTbl2 = array('','', 'Masuk','', '', '','','');
                    $headW = array(7,50,17,5,15,25,5.5,30);

                    foreach($headTbl1 as $kH => $vH)
                    {
                        if($kH == 6)
                        {
                            $pdf->Cell(($headW[$kH] * count($vRet['periode'])), 4, $vH, 'LRT', 0, 'C');
                        }
                        else
                        {
                            $pdf->Cell($headW[$kH], 4, $vH, 'LRT', 0, 'C');
                        }
                    }
                    $pdf->Ln();
                    foreach($headTbl2 as $kH => $vH)
                    {
                        if($kH == 6)
                        {
                            foreach($vRet['periode'] as $per)
                            {
                                $pdf->Cell($headW[$kH], 4, $per->format('d'), 'LRTB', 0, 'C');
                            }
                        }
                        else
                        {
                            $pdf->Cell($headW[$kH], 4, $vH, 'LRB', 0, 'C');
                        }
                    }
                    $pdf->Ln();
                    foreach($vRet['karyawan'] as $k => $v)
                    {
                        if($k % 25 == 0 && $k!=0)
                        {
                            $pdf->AddPage();
                            $pdf->Cell($infoWidth[0], 3, "Unit Kerja");
                            $pdf->Cell($infoWidth[1], 3, ":");
                            $pdf->Cell($infoWidth[2], 3, $vRet['bagian']);
                            $pdf->Ln();
                            $pdf->Ln();
                            foreach($headTbl1 as $kH => $vH)
                            {
                                if($kH == 6)
                                {
                                    $pdf->Cell(($headW[$kH] * count($vRet['periode'])), 4, $vH, 'LRT', 0, 'C');
                                }
                                else
                                {
                                    $pdf->Cell($headW[$kH], 4, $vH, 'LRT', 0, 'C');
                                }
                            }
                            $pdf->Ln();
                            foreach($headTbl2 as $kH => $vH)
                            {
                                if($kH == 6)
                                {
                                    foreach($vRet['periode'] as $per)
                                    {
                                        $pdf->Cell($headW[$kH], 4, $per->format('d'), 'LRTB', 0, 'C');
                                    }
                                }
                                else
                                {
                                    $pdf->Cell($headW[$kH], 4, $vH, 'LRB', 0, 'C');
                                }
                            }
                            $pdf->Ln();
                        }
                        
                        $sizeCell = 6;
                        $pdf->Cell($headW[0], $sizeCell, $k+1, 1, 0, 'C');
                        $pdf->Cell($headW[1], $sizeCell, ' '.$v['nama'], 1, 0, 'L');
                        $pdf->Cell($headW[2], $sizeCell, $v['tanggal_masuk'], 1, 0, 'C');
                        $pdf->Cell($headW[3], $sizeCell, $v['jenkel'], 1, 0, 'C');
                        $pdf->Cell($headW[4], $sizeCell, $v['pin'], 1, 0, 'C');
                        $pdf->Cell($headW[5], $sizeCell, $v['jadwal'], 1, 0, 'C');
                        foreach($vRet['periode'] as $per)
                        {
                            $pdf->Cell($headW[6], $sizeCell, '', 'LRTB', 0, 'C');
                        }
                        $pdf->Cell($headW[7], $sizeCell, '', 'LRTB', 0, 'C');
                        $pdf->Ln();
                    }
//                    $pdf->Ln();
                    $pdf->Cell(10, 5, "Keterangan");
                    $pdf->Ln();
                    $pdf->Cell(5, 5, 'K', 1, 0, 'C');
                    $pdf->Cell(60, 5, 'Masuk Kerja', 1, 0);
                    $pdf->Cell(5, 5);
                    $pdf->Cell(5, 5, 'C', 1, 0, 'C');
                    $pdf->Cell(60, 5, 'Tidak Masuk Kerja Karena Cuti Tahunan', 1, 0);
                    
                    $pdf->Cell(30, 5);
                    $pdf->Cell(50, 5, 'Diketahui Oleh,', 0, 0, 'C');
                    $pdf->Cell(30, 5);
                    $pdf->Cell(50, 5, 'Dibuat Oleh,', 0, 0, 'C');
                    
                    $pdf->Ln();
                    $pdf->Cell(5, 5, 'M', 1, 0, 'C');
                    $pdf->Cell(60, 5, 'Tidak Masuk Kerja Tanpa Ijin', 1, 0);
                    $pdf->Cell(5, 5);
                    $pdf->Cell(5, 5, 'D', 1, 0, 'C');
                    $pdf->Cell(60, 5, 'Tidak Masuk Kerja Karena Dispensi', 1, 0);
                    $pdf->Ln();
                    $pdf->Cell(5, 5, 'P1', 1, 0, 'C');
                    $pdf->Cell(60, 5, 'Tidak Masuk Kerja Karena Ijin', 1, 0);
                    $pdf->Cell(5, 5);
                    $pdf->Cell(5, 5, 'H1', 1, 0, 'C');
                    $pdf->Cell(60, 5, 'Tidak Masuk Kerja Karena Haid', 1, 0);
                    $pdf->Ln();
                    $pdf->Cell(5, 5, 'SD', 1, 0, 'C');
                    $pdf->Cell(60, 5, 'Tidak Masuk Kerja Karena Surat Dokter', 1, 0);
                    $pdf->Cell(5, 5);
                    $pdf->Cell(5, 5, 'H2', 1, 0, 'C');
                    $pdf->Cell(60, 5, 'Tidak Masuk Kerja Karena Cuti Hamil', 1, 0);
                    
                    $pdf->Cell(30, 5);
                    $pdf->Cell(50, 5, '(______________________)', 0, 0, 'C');
                    $pdf->Cell(30, 5);
                    $pdf->Cell(50, 5, '(______________________)', 0, 0, 'C');
                    $pdf->Ln();
                    $pdf->Cell(165, 5);
                    $pdf->Cell(50, 5, 'Pimpinan Bagian', 0, 0, 'C');
                    $pdf->Cell(30, 5);
                    $pdf->Cell(50, 5, 'ADM Bagian', 0, 0, 'C');
                }
            }
            $pdf->Output('Laporan Kehadiran Karyawan.pdf', 'I');
        }
        else
        {
            return abort(404,'Not Found');
        }
        
    }
    
    public function laporanKaryawanRekapAbsen(Request $request)
    {
        $req = $request->all();
        
        $ret = [];
        
        $kar = Karyawan::with('divisi')->karyawanAktif()->author();
        
        if(isset($req['divisi']))
        {
            $kar->where('divisi_id', $req['divisi']);
        }
        
        if(isset($req['perusahaan']))
        {
            $kar->where('perusahaan_id', $req['perusahaan']);
        }
                
        $tgl = null;
        if(isset($req['tanggal']))
        {
            $tgl = explode(" - ", $req['tanggal']);
            
        }
        
//        $perusahaan = null;
        $karyawan = [];
        foreach($kar->get() as $rowKar)
        {
            $abs = [
                    'C' => 0, 'D1' => 0, 'D2' => 0, 'D3' => 0, 'SD' => 0, 'SK' => 0,
                    'I' => 0, 'M' => 0, 'H1' => 0, 'H2' => 0, 'TA' => 0, 'GP' => 0,
                    'IN' => 0, 'OUT' => 0, 'OFF' => 0, 'S1' => 0, 'S2' => 0, 'S3' => 0
                ];
            // $tmk = Carbon::createFromFormat('Y-m-d',$rowKar->tanggal_masuk);

            $proc = DB::table('prosesabsens')
                        ->where('karyawan_id', $rowKar->id)
                        ->whereBetween('tanggal', $tgl)
                        ->orderBy('tanggal', 'asc');
            if($proc->count())
            {
                foreach($proc->get() as $proses)
                {
                    if($proses->alasan_id != null)
                    {
                        $alsId = json_decode($proses->alasan_id, true);
                        $als = DB::table('alasans')->whereIn('id', $alsId)->get();
                        foreach($als as $vAls)
                        {
                            if(isset($abs[$vAls->kode]))
                            {
                                $abs[$vAls->kode] += 1;
                            }
                        }
                    }

                    if($proses->shift3)
                    {
                        $abs['S3'] += 1;
                    }
                    else
                    {
                        if($proses->jam_masuk)
                        {
                            $s1 = Carbon::createFromFormat('Y-m-d H:i:s', $proses->tanggal.' 07:00:00');
                            $s2 = Carbon::createFromFormat('Y-m-d H:i:s', $proses->tanggal.' 14:00:00');
                            // dd($s1);
                            $jMasuk = Carbon::createFromFormat('Y-m-d H:i:s', $proses->tanggal.' '.$proses->jam_masuk);
                            // $jPulang = Carbon::createFromFormat('Y-m-d H:i:s', $proses->tanggal.' '.$proses->jam_pulang);

                            if($jMasuk->between($s1->copy()->subHours(6), $s1->copy()->addHours(6)))
                            {
                                $abs['S1'] += 1;
                            }
                            else if($jMasuk->between($s2->copy()->subHours(6), $s2->copy()->addHours(6)))
                            {
                                $abs['S2'] += 1;
                            }
                        }
                    }
                }
            }
            
            $karyawan[] = [
                'karyawan' =>  ['pin' => $rowKar->pin, 
                                'nama' => $rowKar->nama,
                                'tmk' => $rowKar->tanggal_masuk,
                                'kd' => $rowKar->divisi->kode,
                                'nd' => $rowKar->divisi->deskripsi],
                'absensi' => $abs
            ];
            
        }
        $ret[] = [
            'tgl_awal' => Carbon::createFromFormat('Y-m-d',reset($tgl))->format('d-m-Y'),
            'tgl_akhir' => Carbon::createFromFormat('Y-m-d',end($tgl))->format('d-m-Y'),
            'data' => $karyawan
        ];
        
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.karyawan_rekap_absen.preview', ['var' => $ret, 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]
            );
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('L', PDF_UNIT, 'F4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(6, 23, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('helvetica', '', 8);
            
            if(count($ret))
            {
                foreach($ret as $kRet => $vRet)
                {
                    $pdf->setHeaderData();
                    $pdf->setHeaderData(config('global.img_laporan'), 10, "Rekap Absen Karyawan","Periode : ".$vRet['tgl_awal'].' S/D '.$vRet['tgl_akhir']);
                    $pdf->AddPage();
                                        
                    $headTbl1 = array('No', 'Kode', 'Nama', 'PIN',  'TMK', 'Nama','Absensi');
                    $headTbl2 = array('','Divisi', 'Divisi', '', '','Karyawan', 'C', 'D1', 'D2', 'D3', 'SD', 'SK', 'I', 'M', 'H1', 'H2', 'TA', 'GP', 'IN', 'OUT', 'OFF', 'S1', 'S2', 'S3');
                    $headW = array(10,15,35,15,17,65,9*18);

                    foreach($headTbl1 as $kH => $vH)
                    {
                        $pdf->Cell($headW[$kH] , 4, $vH, 'LRT', 0, 'C');
                    }
                    $pdf->Ln();
                    foreach($headTbl2 as $kH => $vH)
                    {
                        if($kH >= 6)
                        {
                            $pdf->Cell(9, 4, $vH, 'LRTB', 0, 'C');
                        }
                        else
                        {
                            $pdf->Cell($headW[$kH], 4, $vH, 'LRB', 0, 'C');
                        }
                    }
                    $pdf->Ln();
                    foreach($vRet['data'] as $k => $v)
                    {
                        $sizeCell = 4;
                        $pdf->Cell($headW[0], $sizeCell, $k+1, 1, 0, 'C');
                        $pdf->Cell($headW[1], $sizeCell, $v['karyawan']['kd'], 1, 0, 'C');
                        $pdf->Cell($headW[2], $sizeCell, $v['karyawan']['nd'], 1, 0, 'C');
                        $pdf->Cell($headW[3], $sizeCell, $v['karyawan']['pin'], 1, 0, 'C');
                        $pdf->Cell($headW[4], $sizeCell, $v['karyawan']['tmk'], 1, 0, 'C');
                        $pdf->Cell($headW[5], $sizeCell, $v['karyawan']['nama'], 1, 0, 'C');
                        foreach($v['absensi'] as $per)
                        {
                            $pdf->Cell(9, $sizeCell, $per, 'LRTB', 0, 'C');
                        }
                        $pdf->Ln();
                    }
                }
            }
            $pdf->Output('Laporan Rekap Absen Karyawan.pdf', 'I');
        }
        else
        {
            return abort(404,'Not Found');
        }
        
    }
    
    public function laporanTransaksiAlasan(Request $request)
    {
        $req = $request->all();
        
        $ret = [];
        
        $datas = DB::table('alasan_karyawan')
                  ->selectRaw('alasan_karyawan.tanggal as tanggal, alasan_karyawan.alasan_id as alasan_id, alasan_karyawan.waktu as waktu, alasan_karyawan.keterangan as keterangan, karyawans.id as karyawan_id, karyawans.pin as pin, karyawans.nik as nik, karyawans.nama as nama, divisis.kode as divisi_kode, divisis.deskripsi as divisi_deskripsi, alasans.kode as alasan_kode, alasans.deskripsi as alasan_deskripsi, prosesabsens.lembur_aktual as lembur_aktual, prosesabsens.hitung_lembur as hitung_lembur, request_alasan.no_dokumen as no_dokumen')
                  ->join('karyawans', 'karyawans.id', '=', 'alasan_karyawan.karyawan_id')
                  ->join('alasans', 'alasans.id', '=', 'alasan_karyawan.alasan_id')
                  ->join('divisis', 'divisis.id', '=', 'karyawans.divisi_id')
                  ->leftJoin('prosesabsens', function($q){
                        $q->on('prosesabsens.tanggal', '=','alasan_karyawan.tanggal');
                        $q->on('prosesabsens.karyawan_id', '=', 'alasan_karyawan.karyawan_id')
                        ->whereRaw('locate(`alasan_karyawan`.`alasan_id`, `prosesabsens`.`alasan_id`)');
                  })
                  ->leftJoin('request_alasan', 'request_alasan.id', '=', 'alasan_karyawan.request_alasan_id');
        $datasRange = DB::table('alasan_karyawan_range')
                ->selectRaw('alasan_karyawan_range.tanggal_awal as tanggal, alasan_karyawan_range.tanggal_akhir as tanggal_akhir,alasan_karyawan_range.alasan_id as alasan_id, alasan_karyawan_range.waktu as waktu, alasan_karyawan_range.keterangan as keterangan, karyawans.id as karyawan_id, karyawans.pin as pin, karyawans.nik as nik, karyawans.nama as nama, divisis.kode as divisi_kode, divisis.deskripsi as divisi_deskripsi, alasans.kode as alasan_kode, alasans.deskripsi as alasan_deskripsi, prosesabsens.lembur_aktual as lembur_aktual, prosesabsens.hitung_lembur as hitung_lembur, request_alasan.no_dokumen as no_dokumen')
                ->join('karyawans', 'karyawans.id', '=', 'alasan_karyawan_range.karyawan_id')
                ->join('alasans', 'alasans.id', '=', 'alasan_karyawan_range.alasan_id')
                ->join('request_alasan', 'request_alasan.id', '=', 'alasan_karyawan_range.request_alasan_id')
                ->join('divisis', 'divisis.id', '=', 'karyawans.divisi_id')
                ->leftJoin('prosesabsens', function($q){
                      $q->whereRaw('prosesabsens.tanggal between (alasan_karyawan_range.tanggal_awal and alasan_karyawan_range.tanggal_akhir)');
                      $q->on('prosesabsens.karyawan_id', '=', 'alasan_karyawan_range.karyawan_id');
                      $q->whereRaw('locate(`alasan_karyawan_range`.`alasan_id`, `prosesabsens`.`alasan_id`)');
                });
        $tgl = [];
        if(isset($req['tanggal']))
        {
            $tgl = explode(" - ", $req['tanggal']);
            $datas->whereBetween('alasan_karyawan.tanggal',$tgl);
            $datasRange->where(function($q) use($tgl)
            {
                $q->whereBetween('alasan_karyawan_range.tanggal_awal', $tgl);
                $q->orWhereBetween('alasan_karyawan_range.tanggal_akhir', $tgl);
            });
        }
        
        if(isset($req['divisi']))
        {
            $datas->where('karyawans.divisi_id', $req['divisi']);
            $datasRange->where('karyawans.divisi_id', $req['divisi']);
        }
        
        if(isset($req['pin']))
        {
            $datas->where('alasan_karyawan.karyawan_id', $req['pin']);
            $datasRange->where('alasan_karyawan_range.karyawan_id', $req['pin']);
        }
        
        $datas->orderBy('karyawans.pin', 'asc')->orderBy('alasan_karyawan.tanggal', 'desc');
        $datasRange->orderBy('karyawans.pin', 'asc')->orderBy('alasan_karyawan_range.tanggal_awal', 'desc');

        $kar = [];
        foreach($datas->get() as $rowKar)
        {
            $rowKar->tanggal = Carbon::createFromFormat('Y-m-d',$rowKar->tanggal)->format('d-m-Y');
            $kar[] = $rowKar;
        }

        foreach($datasRange->get() as $rowKar)
        {
            $rowKar->tanggal= Carbon::createFromFormat('Y-m-d',$rowKar->tanggal)->format('d-m-Y');
            $rowKar->tanggal_akhir = Carbon::createFromFormat('Y-m-d',$rowKar->tanggal_akhir)->format('d-m-Y');
            $kar[] = $rowKar;
        }

        $ret[] = [
            'tgl_awal' => Carbon::createFromFormat('Y-m-d',reset($tgl))->format('d-m-Y'),
            'tgl_akhir' => Carbon::createFromFormat('Y-m-d',end($tgl))->format('d-m-Y'),
            'data' => $kar
        ];
        
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.transaksi_alasan.preview', ['var' => $ret, 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]
            );
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('L', PDF_UNIT, 'F4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(6, 23, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('helvetica', '', 8);
            
            if(count($ret))
            {
                foreach($ret as $kRet => $vRet)
                {
                    $pdf->setHeaderData();
                    $pdf->setHeaderData(config('global.img_laporan'), 10, "Rekap Absen Karyawan","Periode : ".$vRet['tgl_awal'].' S/D '.$vRet['tgl_akhir']);
                    $pdf->AddPage();
                                        
                    $headTbl1 = array('No', 'Tanggal', 'No. Dok','PIN', 'Nama Karyawan',  'Kode Divisi', 'Nama Divisi','Kd. Als','Nama Alasan', 'Waktu', 'L, Akt', 'L, Kom');
                    $headW = array(10,17,35,15,60,20,60,15,50,10,15,15);

                    foreach($headTbl1 as $kH => $vH)
                    {
                        $pdf->Cell($headW[$kH] , 4, $vH, 'LRT', 0, 'C');
                    }
                    $pdf->Ln();
                    foreach($vRet['data'] as $k => $v)
                    {
                        $sizeCell = 4;
                        $i = 0;
                        $pdf->Cell($headW[$i++], $sizeCell, $k+1, 1, 0, 'C');
                        $pdf->Cell($headW[$i++], $sizeCell, $v->tanggal, 1, 0, 'C');
                        $pdf->Cell($headW[$i++], $sizeCell, $v->no_dokumen, 1, 0, 'C');
                        $pdf->Cell($headW[$i++], $sizeCell, $v->pin, 1, 0, 'C');
                        $pdf->Cell($headW[$i++], $sizeCell, $v->nama, 1, 0, 'C');
                        $pdf->Cell($headW[$i++], $sizeCell, $v->divisi_kode, 1, 0, 'C');
                        $pdf->Cell($headW[$i++], $sizeCell, $v->divisi_deskripsi, 1, 0, 'C');
                        $pdf->Cell($headW[$i++], $sizeCell, $v->alasan_kode, 1, 0, 'C');
                        $pdf->cell($headW[$i++], $sizeCell, $v->alasan_deskripsi, 1, 0, 'C');
                        $pdf->Cell($headW[$i++], $sizeCell, $v->waktu, 1, 0, 'C');
                        $pdf->Cell($headW[$i++], $sizeCell, $v->lembur_aktual, 1, 0, 'C');
                        $pdf->Cell($headW[$i++], $sizeCell, $v->hitung_lembur, 1, 0, 'C');
                        $pdf->Ln();
                    }
                }
            }
            $pdf->Output('Laporan Transaksi Alasan.pdf', 'I');
        }
        else if($req['btnSubmit'] == "excel")
        {
            $ss = new Spreadsheet();
            $ss->getProperties()
                ->setCreator('Taufiq Hari Widodo')
                ->setLastModifiedBy('Taufiq Hari Widodo')
                ->setTitle('Laporan Absen Komulatif')
                ->setSubject('Laporan Transaksi Alasan')
                ->setDescription('Laporan Transaksi Alasan')
                ->setKeywords('laporan indahjaya karyawan')
                ->setCategory('Laporan Excel');
            
            $styleHead1 = [
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                        'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                        ]
                ],
                'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                                'rgb' => 'a0a0a0'
                        ]
                ]
            ];
            $ss->createSheet(0);
            $ss->setActiveSheetIndex(0);
            $ss->getActiveSheet()->setTitle('Transaksi Alasan');
            
            $ss->getActiveSheet()->setCellValue('A1', 'Laporan Transaksi Alasan');
            $ss->getActiveSheet()->setCellValue('A2', "Periode : ".$ret[0]['tgl_awal']." s/d ".$ret[0]['tgl_akhir']);
            $mergeHead = 10;
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,1,$mergeHead,1);
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,2,$mergeHead,2);
            
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,1,$mergeHead,1)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 16,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,2,$mergeHead,2)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
             
            $rowStart = 4;
            $colStat = 1;
            $headTbl1 = array('No','Tanggal', 'No. Dok','PIN','Nama Karyawan', 'Kode Divisi', 'Nama Divisi', 'Kode Alasan', 'Nama Alasan', 'Waktu', 'L. Akt', 'L. Kom');
            foreach($headTbl1 as $rHead)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
            }
            
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,$rowStart,$colStat-1,$rowStart)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10,
                            'bold' => true
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ],
                    'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                    'rgb' => 'a0a0a0'
                            ]
                    ]
                ]);
            
            $rowStart++;
            $colStat = 1;
            foreach($ret[0]['data'] as $kRet => $rRet)
            {
                $colStat = 1;
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $kRet+1);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->tanggal)?$rRet->tanggal:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->no_dokumen)?$rRet->no_dokumen:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->pin)?$rRet->pin:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->nama)?$rRet->nama:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->divisi_kode)?$rRet->divisi_kode:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->divisi_deskripsi)?$rRet->divisi_deskripsi:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->alasan_kode)?$rRet->alasan_kode:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->alasan_deskripsi)?$rRet->alasan_deskripsi:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->waktu)?$rRet->waktu:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->lembur_aktual)?$rRet->lembur_aktual:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet->hitung_lembur)?$rRet->hitung_lembur:'');
                                
                $rowStart++;
                
            }
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,5,$colStat-1,$rowStart-1)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ]
                ]);
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Transaksi Alasan.xls"');
            header('Cache-Control: max-age=0');
            
            $writer = IOFactory::createWriter($ss, 'Xlsx');
            $writer->setPreCalculateFormulas(true);
            $writer->save('php://output');
            exit;
        }
        else
        {
            return abort(404,'Not Found');
        }
        
    }
        
    public function laporanLogJamMasukNew(Request $request)
    {
        $req = $request->all();
        
        $ret = [];
        
        $periode = null;
        $sf = null;
        
        $action = [];
        
        $divisi = Divisi::with(['karyawan' => function($query) use ($req)
        {
            if(Auth::user()->type->nama == 'REKANAN')
            {
                $query->where('perusahaan_id', Auth::user()->perusahaan_id);
            }
            else
            {
                if(isset($req['perusahaan']))
                {
                    $query->where('perusahaan_id', $req['perusahaan']);
                }
            }
        }]);
                                 
        if(isset($req['divisi']))
        {
//            $kar = Karyawan::where('divisi_id',$req['divisi'])->pluck('key');
            $divisi->where('id',$req['divisi']);
        }
//        
        if(isset($req['tanggal']))
        {            
            $periode = CarbonPeriod::create($req['tanggal'], $req['tanggal'])->toArray();
        }
//        
        if(isset($req['tanggalRange']))
        {            
            $tgl = explode(' - ', $req['tanggalRange']);
            $periode = CarbonPeriod::create($tgl[0], $tgl[1])->toArray();
        }
        
        if(isset($req['shift']))
        {
            $sf = $req['shift'];
        }
        
        foreach($karyawan->KaryawanAktif()->get() as $kar)
        {
//            dd($kar->id);
            foreach($periode as $per)
            {
                $abs = $this->absenMasuk($per, $kar->id,$sf);
                if($abs)
                {
                    $action[] = [
                        'pin' => $kar->pin,
                        'nama' => $kar->nama,
                        'kode_jam' => (isset($abs['jadwal'])?$abs['jadwal']->kode:null),
                        'jam_masuk' => (isset($abs['jadwal'])?substr($abs['jadwal']->jam_masuk,0,5):null),
                        'jam_pulang' => (isset($abs['jadwal'])?substr($abs['jadwal']->jam_pulang,0,5):null),
                        'kode_divisi' => $kar->divisi->kode,
                        'nama_divisi' => $kar->divisi->deskripsi,
                        'tanggal_absen' => $per->format('d-m-Y'),
                        'jam_absen' => (isset($abs['activity'])?substr($abs['activity']->tanggal,11,5):null),
                        'jam_absen_pulang' => (isset($abs['activity_out'])?substr($abs['activity_out']->tanggal,11,5):null),
                        'lokasi_mesin' => (isset($abs['activity'])?$abs['activity']->mesin->lokasi:null)
                    ];
                }
            }
        }
        dd($action);
        $ret = [
            'periode' => $periode,
            'data' => $action
        ];
        
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.log_jam_masuk.preview', ['var' => $ret, 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]
            );
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(6, 23, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('helvetica', '', 8);
            
            if(count($ret))
            {
                $pdf->setHeaderData();
                $pdf->setHeaderData(config('global.img_laporan'), 10, "Laporan Log Jam Masuk Karyawan","Periode : ".reset($ret['periode'])->format('d/m/Y').' S/D '.end($ret['periode'])->format('d/m/Y'));
                $pdf->AddPage();
                
                $headTbl1 = array('No', 'PIN', 'Nama', 'Kode Jam',  'Jadwal Masuk', 'Kode Divisi','Nama Divisi','Tanggal Absen','Jam Absen','Lokasi Mesin');
                $headW = array(10,15,50,20,20,20,40,20,20,50);
                foreach($headTbl1 as $kH => $vH)
                {
                    $pdf->Cell($headW[$kH] , 4, $vH, 'LRT', 0, 'C');
                }
                $pdf->Ln();
                
                foreach($ret['data'] as $kRet => $vRet)
                {      
                    $sizeCell = 4;
                    $pdf->Cell($headW[0], $sizeCell, $kRet+1, 1, 0, 'C');
                    $i = 1;
                    foreach($vRet as $v)
                    {
                        $pdf->Cell($headW[$i++], $sizeCell, $v, 1, 0, 'C');
                        
                    }
                    $pdf->Ln();
                }
            }
            $pdf->Output('Laporan Rekap Log Jam Masuk Karyawan.pdf', 'I');
        }
        else if($req['btnSubmit'] == "excel")
        {
            $ss = new Spreadsheet();
            $ss->getProperties()
                ->setCreator('Taufiq Hari Widodo')
                ->setLastModifiedBy('Taufiq Hari Widodo')
                ->setTitle('Laporan Absen Log Jam Masuk Karyawan')
                ->setSubject('Laporan Log Jam Masuk Karyawan')
                ->setDescription('Laporan Log Jam Masuk Karyawan')
                ->setKeywords('laporan '.config('global.perusahaan_short').' karyawan')
                ->setCategory('Laporan Excel');
            
            $styleHead1 = [
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                        'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                        ]
                ],
                'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                                'rgb' => 'a0a0a0'
                        ]
                ]
            ];
            $ss->createSheet(0);
            $ss->setActiveSheetIndex(0);
            $ss->getActiveSheet()->setTitle('Transaksi Log Jam Masuk');
            
            $ss->getActiveSheet()->setCellValue('A1', 'Laporan Log Jam Masuk');
            $ss->getActiveSheet()->setCellValue('A2', "Periode : ".reset($ret['periode'])->format('d/m/Y').' S/D '.end($ret['periode'])->format('d/m/Y'));
            $mergeHead = 10;
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,1,$mergeHead,1);
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,2,$mergeHead,2);
            
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,1,$mergeHead,1)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 16,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,2,$mergeHead,2)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
             
            $rowStart = 4;
            $colStat = 1;
            $headTbl1 = array('No','PIN', 'Nama', 'Kode Jam', 'Jadwal Masuk', 'Kode Divisi', 'Nama Divisi', 'Tanggal Absen', 'Jam Absen', 'Lokasi Mesin');
            foreach($headTbl1 as $rHead)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
            }
            
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,$rowStart,$colStat-1,$rowStart)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10,
                            'bold' => true
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ],
                    'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                    'rgb' => 'a0a0a0'
                            ]
                    ]
                ]);
            
            $rowStart++;
            $colStat = 1;
            foreach($ret['data'] as $kRet => $vRet)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $kRet+1);
                foreach($vRet as $v)
                {
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $v);
                }
                $colStat = 1;
                $rowStart++;
                
            }
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,5,$colStat-1,$rowStart-1)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ]
                ]);
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Transaksi Log Masuk Karyawan.xls"');
            header('Cache-Control: max-age=0');
            
            $writer = IOFactory::createWriter($ss, 'Xlsx');
            $writer->setPreCalculateFormulas(true);
            $writer->save('php://output');
            exit;
        }
        else
        {
            return abort(404,'Not Found');
        }
        
    }
        
    public function laporanLogJamMasuk(Request $request)
    {
        $req = $request->all();
        
        $ret = [];
        
        $periode = null;
        $sf = null;
        
        $action = [];
        
        $karyawan = Karyawan::with('jadwals', 'divisi');
//        $act = Activity::with('mesin', 'karyawan');
//        
        if(isset($req['pin']))
        {
//            $kar = Karyawan::find($req['pin']);
            $karyawan->where('id', $req['pin']);
        }
//        
        if(isset($req['divisi']))
        {
//            $kar = Karyawan::where('divisi_id',$req['divisi'])->pluck('key');
            $div = Divisi::descendantsAndSelf($req['divisi'])->pluck('id');
            $karyawan->whereIn('divisi_id',$div);
        }
//        
        if(isset($req['perusahaan']))
        {
//            $kar = Karyawan::where('perusahaan_id',$req['perusahaan'])->pluck('key');
            $karyawan->where('perusahaan_id',$req['perusahaan']);
        }
//        
        if(isset($req['tanggal']))
        {            
            $periode = CarbonPeriod::create($req['tanggal'], $req['tanggal'])->toArray();
        }
//        
        if(isset($req['tanggalRange']))
        {            
            $tgl = explode(' - ', $req['tanggalRange']);
            $periode = CarbonPeriod::create($tgl[0], $tgl[1])->toArray();
        }
        
        if(isset($req['shift']))
        {
            $sf = $req['shift'];
        }
        
        
        
        foreach($karyawan->karyawanTerlihat()->get() as $kar)
        {
//            dd($kar->id);
            foreach($periode as $per)
            {
                $abs = $this->absenMasuk($per, $kar->id,$sf);
                
                if($abs)
                {
                    $gol = null;
                        
                    if($kar->logGolonganTanggal($per->toDateString())->first())
                    {
                        $gol = $kar->logGolonganTanggal($per->toDateString())->first()->nama;
                    }
                    $action[] = [
                        'pin' => $kar->pin,
                        'nama' => $kar->nama,
                        'golongan' => $gol,
                        'kode_jam' => (isset($abs['jadwal'])?$abs['jadwal']->kode:null),
                        'jam_masuk' => (isset($abs['jadwal'])?substr($abs['jadwal']->jam_masuk,0,5):null),
                        'jam_pulang' => (isset($abs['jadwal'])?substr($abs['jadwal']->jam_keluar,0,5):null),
                        'kode_divisi' => $kar->divisi->kode,
                        'nama_divisi' => $kar->divisi->deskripsi,
                        'tanggal_absen' => $per->format('d-m-Y'),
                        'jam_absen' => (isset($abs['activity'])?substr($abs['activity']->tanggal,11,5):null),
                        'jam_absen_keluar' => (isset($abs['activity_out'])?substr($abs['activity_out']->tanggal,11,5):null),
                        'lokasi_mesin' => (isset($abs['activity'])?$abs['activity']->mesin->lokasi:null),
                        'lokasi_mesin_keluar' => (isset($abs['activity_out'])?$abs['activity_out']->mesin->lokasi:null)
                    ];
                }
            }
        }
        $ret = [
            'periode' => $periode,
            'data' => $action
        ];
        
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.log_jam_masuk.preview', ['var' => $ret, 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]
            );
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(6, 23, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('helvetica', '', 8);
            
            if(count($ret))
            {
                $pdf->setHeaderData();
                $pdf->setHeaderData(config('global.img_laporan'), 10, "Laporan Log Jam Masuk Karyawan","Periode : ".reset($ret['periode'])->format('d/m/Y').' S/D '.end($ret['periode'])->format('d/m/Y'));
                $pdf->AddPage();
                
                $headTbl1 = array('No', 'PIN', 'Nama', 'Gol', 'Kode Jam',  'Jadwal Masuk', 'Kode Divisi','Nama Divisi','Tanggal Absen','Jam Absen','Lokasi Mesin');
                $headW = array(10,15,50, 20,20,20,20,40,20,20,50);
                foreach($headTbl1 as $kH => $vH)
                {
                    $pdf->Cell($headW[$kH] , 4, $vH, 'LRT', 0, 'C');
                }
                $pdf->Ln();
                
                foreach($ret['data'] as $kRet => $vRet)
                {      
                    $sizeCell = 4;
                    $pdf->Cell($headW[0], $sizeCell, $kRet+1, 1, 0, 'C');
                    $i = 1;
                    foreach($vRet as $v)
                    {
                        $pdf->Cell($headW[$i++], $sizeCell, $v, 1, 0, 'C');
                        
                    }
                    $pdf->Ln();
                }
            }
            $pdf->Output('Laporan Rekap Log Jam Masuk Karyawan.pdf', 'I');
        }
        else if($req['btnSubmit'] == "excel")
        {
            $ss = new Spreadsheet();
            $ss->getProperties()
                ->setCreator('Taufiq Hari Widodo')
                ->setLastModifiedBy('Taufiq Hari Widodo')
                ->setTitle('Laporan Absen Log Jam Masuk Karyawan')
                ->setSubject('Laporan Log Jam Masuk Karyawan')
                ->setDescription('Laporan Log Jam Masuk Karyawan')
                ->setKeywords('laporan '.config('global.perusahaan_short').' karyawan')
                ->setCategory('Laporan Excel');
            
            $styleHead1 = [
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                        'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                        ]
                ],
                'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                                'rgb' => 'a0a0a0'
                        ]
                ]
            ];
            $ss->createSheet(0);
            $ss->setActiveSheetIndex(0);
            $ss->getActiveSheet()->setTitle('Transaksi Log Jam Masuk');
            
            $ss->getActiveSheet()->setCellValue('A1', 'Laporan Log Jam Masuk');
            $ss->getActiveSheet()->setCellValue('A2', "Periode : ".reset($ret['periode'])->format('d/m/Y').' S/D '.end($ret['periode'])->format('d/m/Y'));
            $mergeHead = 10;
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,1,$mergeHead,1);
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,2,$mergeHead,2);
            
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,1,$mergeHead,1)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 16,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,2,$mergeHead,2)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
             
            $rowStart = 4;
            $colStat = 1;
            $headTbl1 = array('No','PIN', 'Nama', 'Gol','Kode Jam', 'Jadwal Masuk', 'Jadwal Pulang', 'Kode Divisi', 'Nama Divisi', 'Tanggal Absen', 'Jam Absen Masuk', 'Jam Absen Pulang', 'Mesin Masuk', 'Mesin Pulang');
            foreach($headTbl1 as $rHead)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
            }
            
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,$rowStart,$colStat-1,$rowStart)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10,
                            'bold' => true
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ],
                    'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                    'rgb' => 'a0a0a0'
                            ]
                    ]
                ]);
            
            $rowStart++;
            $colStat = 1;
            foreach($ret['data'] as $kRet => $vRet)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $kRet+1);
                foreach($vRet as $v)
                {
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $v);
                }
                $colStat = 1;
                $rowStart++;
                
            }
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,5,$colStat-1,$rowStart-1)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ]
                ]);
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Transaksi Log Masuk Karyawan.xls"');
            header('Cache-Control: max-age=0');
            
            $writer = IOFactory::createWriter($ss, 'Xlsx');
            $writer->setPreCalculateFormulas(true);
            $writer->save('php://output');
            exit;
        }
        else
        {
            return abort(404,'Not Found');
        }
        
    }

    public function laporanListGaji(Request $request)
    {
        $req = $request->all();
        
        $ret = [];
        
        $periode = null;
        $sf = null;
        
        $action = [];
        
        $karyawan = Karyawan::with('divisi');
//        
        if(isset($req['pin']))
        {
//            $kar = Karyawan::find($req['pin']);
            $karyawan->where('id', $req['pin']);
        }
//        
        if(isset($req['divisi']))
        {
//            $kar = Karyawan::where('divisi_id',$req['divisi'])->pluck('key');
            $div = Divisi::descendantsAndSelf($req['divisi'])->pluck('id');
            $karyawan->whereIn('divisi_id',$div);
        }
//        
        if(isset($req['perusahaan']))
        {
//            $kar = Karyawan::where('perusahaan_id',$req['perusahaan'])->pluck('key');
            $karyawan->where('perusahaan_id',$req['perusahaan']);
        }
//        
        // if(isset($req['tanggal']))
        // {            
        //     $periode = CarbonPeriod::create($req['tanggal'], $req['tanggal'])->toArray();
        // }
//        
        if(isset($req['tanggal']))
        {            
            $tgl = Carbon::createFromFormat('Y-m-d', $req['tanggal'].'-22')->subMonth();

            $periode = CarbonPeriod::create($tgl, $tgl->copy()->addMonth(1)->subDay(1))->toArray();
        }
        
        foreach($karyawan->karyawanTerlihat()->orderBy('divisi_id', 'asc')->get() as $kar)
        {
            $proc = Prosesgaji::with('karyawan', 'editlistlast')->where('periode_awal', '>=', reset($periode)->toDateString())
                              ->where('periode_akhir', '<=', end($periode)->toDateString())
                              ->where('karyawan_id', $kar['id'])->orderBy('periode_awal', 'asc');
            
            if($proc->count())
            {
                foreach($proc->get() as $valProc)
                {
                    $action[] = $valProc;
                }
            }
            
            
        }
        $ret = [
            'periode' => $periode,
            'data' => $action
        ];
        // dd($ret);
        if($req['btnSubmit'] == "preview")
        {
            return view('payroll.laporan.list_gaji.preview', ['var' => $ret, 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]
            );
        }
        else if($req['btnSubmit'] == "excel")
        {
            $ss = new Spreadsheet();
            $ss->getProperties()
                ->setCreator('Taufiq Hari Widodo')
                ->setLastModifiedBy('Taufiq Hari Widodo')
                ->setTitle('Laporan List Gaji Karyawan')
                ->setSubject('Laporan List Gaji Karyawan')
                ->setDescription('Laporan List Gaji Karyawan')
                ->setKeywords('laporan '.config('global.perusahaan_short').' karyawan')
                ->setCategory('Laporan Excel');
            
            $styleHead1 = [
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                        'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                        ]
                ],
                'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                                'rgb' => 'a0a0a0'
                        ]
                ]
            ];
            $ss->createSheet(0);
            $ss->setActiveSheetIndex(0);
            $ss->getActiveSheet()->setTitle('Laporan List Gaji');
            
            $ss->getActiveSheet()->setCellValue('A1', 'Laporan List Gaji');
            $ss->getActiveSheet()->setCellValue('A2', "Periode : ".reset($ret['periode'])->format('d/m/Y').' S/D '.end($ret['periode'])->format('d/m/Y'));
            $mergeHead = 32;
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,1,$mergeHead,1);
            $ss->getActiveSheet()->mergeCellsByColumnAndRow(1,2,$mergeHead,2);
            
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,1,$mergeHead,1)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 16,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $ss->getActiveSheet()->getStyleByColumnAndRow(1,2,$mergeHead,2)->applyFromArray([
                'font' => [
                        'name' => 'sans-serif',
                        'size' => 10,
                        'bold' => true
                ],
                'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]);
             
            $rowStart = 4;
            $colStat = 1;
            $headTbl1 = array('NO', 'NAMA', 'PIN', 'TANGGAL', 'NAMA', 'GAJI', 'POT. ABSEN','', 'GAJI POKOK', 'UPAH LEMBUR', '', 'SHIFT 3', '', 'TUNJANGAN','','','','', 'GETPAS', '', 'KOREKSI', '', 'PENDAPATAN', 'POTONGAN', '', '', '', '', '', '', 'TOTAL', 'TOTAL');
            $headTbl2 = array('','DIVISI', '', 'MASUK','KARYAWAN', 'POKOK', 'JML', 'Rp', 'DIBAYAR', 'JML', 'Rp', 'JML', 'Rp', 'JABATAN', 'PRESTASI', 'HAID', 'HADIR', 'LAIN2', 'JAM', 'Rp', 'KOR(+)', 'KOR(-)', 'BRUTO', 'BPJS-TK', 'BPJS-KES', 'BPJS-PEN', 'PPH21', 'COST SRKT', 'TOKO', 'LAIN2', 'AKHIR', 'BAYAR');
            $empty = 0;
            foreach($headTbl1 as $rHead)
            {
                if(empty($rHead))
                {
                    $empty++;
                }
                else
                {
                    if($empty)
                    {                        
                        $ss->getActiveSheet()->mergeCellsByColumnAndRow($colStat-$empty-1, $rowStart, $colStat-1, $rowStart);
                        $empty = 0;
                    }
                }
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
            }
            $rowStart++;
            $colStat = 1;
            foreach($headTbl2 as $rHead)
            {
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $rHead);
                if(empty($rHead))
                {
                    $ss->getActiveSheet()->mergeCellsByColumnAndRow($colStat-1, $rowStart-1, $colStat-1, $rowStart);
                }
            }
            
            $ss->getActiveSheet()
               ->getStyleByColumnAndRow(1,$rowStart-1,$colStat-1,$rowStart)
               ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 10,
                            'bold' => true
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ],
                    'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                    'rgb' => 'a0a0a0'
                            ]
                    ]
                ]);
            
            $rowStart++;
            $colStat = 1;

            $div = "";
            $tDiv = 0;
            foreach($ret['data'] as $kRet => $vRet)
            {

                if($div!=$vRet->karyawan->divisi->kode)
                {
                    if(!empty($div))
                    {
                        $div = $vRet->karyawan->divisi->kode;
                        // dd('$div='.$div.',karkode='.$vRet->karyawan->divisi->kode);
                        
                        $this->totalReportGajiRowXls($ss,$rowStart,$tDiv);

                        $rowStart+=2;
                        $tDiv=0;
                    }
                    else
                    {
                        $div = $vRet->karyawan->divisi->kode;
                    }
                }
                $tDiv+=1;
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $kRet+1);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,$vRet->karyawan->divisi->deskripsi);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,$vRet->karyawan->pin);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,$vRet->karyawan->tanggal_masuk);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,$vRet->karyawan->nama);


                if($vRet->editlistlast && count($vRet->editlistlast)>0)
				{
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->gaji_pokok);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->potongan_absen);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->potongan_absen_rp);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->gaji_pokok_dibayar);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,$vRet->editlistlast[0]->lembur);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->lembur_rp);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->s3);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->s3_rp);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->tunjangan_jabatan);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->tunjangan_prestasi);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->tunjangan_haid);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->tunjangan_hadir);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->tunjangan_lain);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->gp);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->gp_rp);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->koreksi_plus);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->koreksi_minus);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->bruto_rp);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->bpjs_tk);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->bpjs_kes);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->bpjs_pen);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->pph21);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->cost_serikat_rp);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->toko);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->lainlain);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->tot_akhir);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->editlistlast[0]->tot_bayar);
                }
				else
				{
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->gaji_pokok);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->potongan_absen);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->potongan_absen_rp);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->gaji_pokok_dibayar);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,$vRet->lembur);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->lembur_rp);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->s3);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->s3_rp);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->tunjangan_jabatan);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->tunjangan_prestasi);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->tunjangan_haid);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->tunjangan_hadir);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->tunjangan_lain);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->gp);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->gp_rp);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->koreksi_plus);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->koreksi_minus);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->bruto_rp);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->bpjs_tk);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->bpjs_kes);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->bpjs_pen);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->pph21);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->cost_serikat_rp);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->toko);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->lainlain);

                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->tot_akhir);
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart,(int)$vRet->tot_bayar);
                }

                $ss->getActiveSheet()
                    ->getStyleByColumnAndRow(1,$rowStart,$colStat-1,$rowStart)
                    ->applyFromArray([
                            'font' => [
                                    'name' => 'sans-serif',
                                    'size' => 10
                            ],
                            'alignment' => [
                                    'vertical' => Alignment::VERTICAL_CENTER
                            ],
                            'borders' => [
                                    'allBorders' => [
                                            'borderStyle' => Border::BORDER_THIN
                                    ]
                            ]
                        ]);
                $ss->getActiveSheet()
                    ->getStyleByColumnAndRow(6,$rowStart,$colStat-1,$rowStart)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $ss->getActiveSheet()
                ->getStyleByColumnAndRow(10,$rowStart,10,$rowStart)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
                $colStat = 1;
                $rowStart++;
            }
            $this->totalReportGajiRowXls($ss,$rowStart,$tDiv);
            // foreach($ret['data'] as $kRet => $vRet)
            // {
            //     $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $kRet+1);
            //     foreach($vRet as $v)
            //     {
            //         $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $v);
            //     }
            //     $colStat = 1;
            //     $rowStart++;
                
            // }
            // $ss->getActiveSheet()
            //    ->getStyleByColumnAndRow(1,5,$colStat-1,$rowStart-1)
            //    ->applyFromArray([
            //         'font' => [
            //                 'name' => 'sans-serif',
            //                 'size' => 10
            //         ],
            //         'alignment' => [
            //                 'vertical' => Alignment::VERTICAL_CENTER,
            //                 'horizontal' => Alignment::HORIZONTAL_CENTER,
            //         ],
            //         'borders' => [
            //                 'allBorders' => [
            //                         'borderStyle' => Border::BORDER_THIN
            //                 ]
            //         ]
            //     ]);
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Transaksi Log Masuk Karyawan.xls"');
            header('Cache-Control: max-age=0');
            
            $writer = IOFactory::createWriter($ss, 'Xlsx');
            $writer->setPreCalculateFormulas(true);
            $writer->save('php://output');
            exit;
        }
        else
        {
            return abort(404,'Not Found');
        }
        
    }

    private function totalReportGajiRowXls($ss,$rowStart, $tDiv)
    {
        $cl = 1;
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl, $rowStart, "Total");
        $ss->getActiveSheet()->mergeCellsByColumnAndRow($cl, $rowStart, $cl+=4, $rowStart);
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl+1, $rowStart, "=SUM(F".($rowStart-$tDiv).":F".($rowStart-1).")");
        $cl+=2;
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(G".($rowStart-$tDiv).":G".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(H".($rowStart-$tDiv).":H".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(I".($rowStart-$tDiv).":I".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(J".($rowStart-$tDiv).":J".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(K".($rowStart-$tDiv).":K".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(L".($rowStart-$tDiv).":L".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(M".($rowStart-$tDiv).":M".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(N".($rowStart-$tDiv).":N".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(O".($rowStart-$tDiv).":O".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(P".($rowStart-$tDiv).":P".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(Q".($rowStart-$tDiv).":Q".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(R".($rowStart-$tDiv).":R".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(S".($rowStart-$tDiv).":S".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(T".($rowStart-$tDiv).":T".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(U".($rowStart-$tDiv).":U".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(V".($rowStart-$tDiv).":V".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(W".($rowStart-$tDiv).":W".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(X".($rowStart-$tDiv).":X".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(Y".($rowStart-$tDiv).":Y".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(Z".($rowStart-$tDiv).":Z".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(AA".($rowStart-$tDiv).":AA".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(AB".($rowStart-$tDiv).":AB".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(AC".($rowStart-$tDiv).":AC".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(AD".($rowStart-$tDiv).":AD".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(AE".($rowStart-$tDiv).":AE".($rowStart-1).")");
        $ss->getActiveSheet()->setCellValueByColumnAndRow($cl++, $rowStart, "=SUM(AF".($rowStart-$tDiv).":AF".($rowStart-1).")");
        $ss->getActiveSheet()
            ->getStyleByColumnAndRow(1,$rowStart,$cl-1,$rowStart)
            ->applyFromArray([
                    'font' => [
                            'name' => 'sans-serif',
                            'size' => 12,
                            'bold' => true
                    ],
                    'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                            'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN
                            ]
                    ],
                    'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                    'rgb' => 'e0e0e0'
                            ]
                    ]
                ])
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        $ss->getActiveSheet()
                ->getStyleByColumnAndRow(10,$rowStart,10,$rowStart)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
    }
}
