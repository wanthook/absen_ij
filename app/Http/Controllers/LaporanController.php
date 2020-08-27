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
//use PDF;

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


class LaporanController 
{
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
    
    public function laporanDetail(Request $request)
    {
        try
        {
            $req = $request->all();

            $ret = $this->lDet($req);

            if($req['btnSubmit'] == "preview")
            {
                return view('admin.laporan.detail.preview', ['var' => $ret['msg'], 'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
            }
            else if($req['btnSubmit'] == "pdf")
            {

                $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', true);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->setFontSubsetting(false);
                $pdf->SetFont('dejavusans', '', 8);
                foreach($ret['msg'] as $var)
                {
                    $pdf->setHeaderData('ij.jpg', 10, "Laporan Kehadiran Karyawan","Periode : ".$var['periodeStart']." s/d ".$var['periodeEnd']);
                    $pdf->AddPage();
                    $infoWidth = array(25,3,300);
                    $pdf->Cell($infoWidth[0], 3, "PIN / Nama");
                    $pdf->Cell($infoWidth[1], 3, ":");
                    $pdf->Cell($infoWidth[2], 3, $var['karyawan']->pin.' - '.$var['karyawan']->nama);
                    $pdf->Ln();
                    $pdf->Cell($infoWidth[0], 3, "Unit Kerja");
                    $pdf->Cell($infoWidth[1], 3, ":");
                    $pdf->Cell($infoWidth[2], 3, $var['karyawan']->divisi->kode.' - '.$var['karyawan']->divisi->deskripsi);
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
                    $lemburAktual = 0;
                    $hitLembur = 0;
                    $hitNas = 0;
                    $sMalam = 0;
                    $lemburLn = 0;
                    $totLem = 0;
                    foreach($var['absen'] as $tgl => $vabs)
                    {
                                                
                        $pdf->Cell($Width2[0], 4.5, $tgl, '1', 0, 'C');
                        if($vabs)
                        {
                            $tLembur = $vabs->hitung_lembur + $vabs->hitung_lembur_ln;
                        
                        
                            $lemburAktual += $vabs->lembur_aktual;
                            $hitLembur += $vabs->hitung_lembur;
                            $sMalam += $vabs->shift3;
                            $lemburLn += $vabs->lembur_ln;
                            $hitNas += $vabs->hitung_lembur_ln;

                            $totLem += $tLembur;
                            
                            $pdf->Cell($Width2[1], 4.5, substr($vabs->jadwal_jam_masuk,0,5), '1', 0, 'C');
                            $pdf->Cell($Width2[2], 4.5, substr($vabs->jadwal_jam_keluar,0,5), '1', 0, 'C');
                            $pdf->Cell($Width2[3], 4.5, substr($vabs->jam_masuk,0,5), '1', 0, 'C');
                            $pdf->Cell($Width2[4], 4.5, substr($vabs->jam_keluar,0,5), '1', 0, 'C');
                            $pdf->Cell($Width2[5], 4.5, ($vabs->n_masuk < 0)?abs($vabs->n_masuk):'', '1', 0, 'C');
                            $pdf->Cell($Width2[6], 4.5, ($vabs->n_masuk > 0)?abs($vabs->n_masuk):'', '1', 0, 'C');
                            $pdf->Cell($Width2[7], 4.5, ($vabs->n_keluar > 0)?abs($vabs->n_keluar):'', '1', 0, 'C');
                            $pdf->Cell($Width2[8], 4.5, ($vabs->n_keluar < 0)?abs($vabs->n_keluar):'', '1', 0, 'C');
                            $pdf->Cell($Width2[9], 4.5, $vabs->keterangan, '1', 0, 'C');
                            $pdf->Cell($Width2[10], 4.5, $vabs->lembur_aktual, '1', 0, 'C');
                            $pdf->Cell($Width2[11], 4.5, $vabs->hitung_lembur, '1', 0, 'C');
                            $pdf->Cell($Width2[12], 4.5, $vabs->shift3, '1', 0, 'C');
                            $pdf->Cell($Width2[13], 4.5, $vabs->lembur_ln, '1', 0, 'C');
                            $pdf->Cell($Width2[14], 4.5, $vabs->hitung_lembur_ln, '1', 0, 'C');
                            $pdf->Cell($Width2[15], 4.5, ($tLembur)?$tLembur:'', '1', 0, 'C');
                        }
                        else
                        {
                            $pdf->Cell($Width2[1], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[2], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[3], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[4], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[5], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[6], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[7], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[8], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[9], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[10], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[11], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[12], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[13], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[14], 4.5, '', '1', 0, 'C');
                            $pdf->Cell($Width2[15], 4.5, '', '1', 0, 'C');
                        }
                        $pdf->Ln();
                        
                    }
                    $pdf->Cell((25+30+30+20+20+50), 4.5, "Jumlah", '1', 0, 'C');
                    $pdf->Cell($Width2[10], 4.5, $lemburAktual, '1', 0, 'C');
                    $pdf->Cell($Width2[11], 4.5, $hitLembur, '1', 0, 'C');
                    $pdf->Cell($Width2[12], 4.5, $sMalam, '1', 0, 'C');
                    $pdf->Cell($Width2[13], 4.5, $lemburLn, '1', 0, 'C');
                    $pdf->Cell($Width2[14], 4.5, $hitNas, '1', 0, 'C');
                    $pdf->Cell($Width2[15], 4.5, $totLem, '1', 0, 'C');
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
        
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.komulatif.preview', ['var' => $ret['msg'], 
                'periode' => $ret['periode'], 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', true);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetMargins(3, 23, 5);
            $pdf->setFontSubsetting(false);
            $pdf->SetFont('dejavusans', '', 8);
            
            $pdf->setHeaderData('ij.jpg', 10, "Laporan Kehadiran Karyawan Komulatif","Periode : ".reset($ret['periode'])->toDateString()." s/d ".end($ret['periode'])->toDateString());
            $pdf->AddPage();
            $headTbl1 = array('No' => 6,'PIN' => 13, 'TMK' => 18,'SEX' => 7, 'Kode' => 8, 'Divisi' => 15, 'Nama' => 40);
            $headTbl2 = array('Lbr' => 7, 'S3' => 7, 'GP' => 7, 'JK' => 7);
            
            foreach($headTbl1 as $kH => $vH)
            {
                $pdf->Cell($vH, 4, $kH, 1, 0, 'C');
            }

            foreach($ret['periode'] as $per)
            {
                $pdf->Cell(5, 4, $per->format('d'), 1, 0, 'C');
            }

            foreach($headTbl2 as $kH => $vH)
            {
                $pdf->Cell($vH, 4, $kH, 1, 0, 'C');
            }
            $pdf->Ln();
            
            $line = 'LRB';
            foreach($ret['msg'] as $kRet => $rRet)
            {
                $tLembur = 0;
                $s3 = 0;
                $jGp = 0;
                $jJk = 0;
                
                $pdf->Cell($headTbl1['No'], 4, $kRet+1, $line, 0, 'C');
                $pdf->Cell($headTbl1['PIN'], 4, isset($rRet['karyawan']->pin)?$rRet['karyawan']->pin:'', $line, 0, 'C');
                $pdf->Cell($headTbl1['TMK'], 4, isset($rRet['karyawan']->tanggal_masuk)?$rRet['karyawan']->tanggal_masuk:'', $line, 0, 'C');
                $pdf->Cell($headTbl1['SEX'], 4, isset($rRet['karyawan']->jeniskelamin->nama)?$rRet['karyawan']->jeniskelamin->nama:'', $line, 0, 'C');
                $pdf->Cell($headTbl1['Kode'], 4, isset($rRet['karyawan']->divisi->kode)?$rRet['karyawan']->divisi->kode:'', $line, 0, 'C');
                $pdf->Cell($headTbl1['Divisi'], 4, isset($rRet['karyawan']->divisi->deskripsi)?$rRet['karyawan']->divisi->deskripsi:'', $line, 0, 'C');
                $pdf->Cell($headTbl1['Nama'], 4, isset($rRet['karyawan']->nama)?$rRet['karyawan']->nama:'', $line, 0, 'C');
                
                foreach($rRet['absen'] as $tgl => $vabs)
                {
                    
                    $lbl = '';
                        
                    if(isset($vabs->inout))
                    {
                        $lbl = $vabs->inout;
                    }
                    else if(isset($vabs->mangkir))
                    {
                        $lbl = 'M';
                    }
                    else if(isset($vabs->ta))
                    {
                        $lbl = 'TA';
                    }
                    else if(isset($vabs->gp))
                    {
                        $lbl = 'GP';
                        $jGp+=$vabs->gp;
                        $jJk += $vabs->jumlah_jam_kerja;
                    }
                    else if(isset($vabs->libur))
                    {
                        if(isset($vabs->alasan))
                        {
                            $lbl = $vabs->alasan[0]->kode;
                        }
                        else
                        {
                            $lbl = '0';
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
                    
                    $pdf->Cell(5, 4, $lbl, $line, 0, 'C');
                }
                $pdf->Cell($headTbl2['Lbr'], 4, $tLembur, 1, 0, 'C');
                $pdf->Cell($headTbl2['S3'], 4, $s3, 1, 0, 'C');
                $pdf->Cell($headTbl2['GP'], 4, $jGp/60, 1, 0, 'C');
                $pdf->Cell($headTbl2['JK'], 4, $jJk, 1, 0, 'C');
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
            $headTbl1 = array('No','PIN', 'TMK','SEX', 'Kode', 'Divisi', 'Nama');
            $headTbl2 = array('Lbr', 'S3', 'GP', 'JK');
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
            $colStat = 1;
            foreach($ret['msg'] as $kRet => $rRet)
            {
                $colStat = 1;
                $tLembur = 0;
                $s3 = 0;
                $jGp = 0;
                $jJk = 0;
                
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $kRet+1);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet['karyawan']->pin)?$rRet['karyawan']->pin:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet['karyawan']->tanggal_masuk)?$rRet['karyawan']->tanggal_masuk:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet['karyawan']->jeniskelamin->nama)?$rRet['karyawan']->jeniskelamin->nama:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet['karyawan']->divisi->kode)?$rRet['karyawan']->divisi->kode:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet['karyawan']->divisi->deskripsi)?$rRet['karyawan']->divisi->deskripsi:'');
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, isset($rRet['karyawan']->nama)?$rRet['karyawan']->nama:'');
                
                
                foreach($rRet['absen'] as $tgl => $vabs)
                {
                    
                    $lbl = '';
                        
                    if(isset($vabs->inout))
                    {
                        $lbl = $vabs->inout;
                    }
                    else if(isset($vabs->mangkir))
                    {
                        $lbl = 'M';
                    }
                    else if(isset($vabs->ta))
                    {
                        $lbl = 'TA';
                    }
                    else if(isset($vabs->gp))
                    {
                        $lbl = 'GP';
                        $jGp+=$vabs->gp;
                        $jJk += $vabs->jumlah_jam_kerja;
                    }
                    else if(isset($vabs->libur))
                    {
                        if(isset($vabs->alasan))
                        {
                            $lbl = $vabs->alasan[0]->kode;
                        }
                        else
                        {
                            $lbl = '0';
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
                    
                    $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $lbl);
                }
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $tLembur);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $s3);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $jGp/60);
                $ss->getActiveSheet()->setCellValueByColumnAndRow($colStat++, $rowStart, $jJk);
                
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
        
        $karAktif       = Karyawan::with('divisi', 'jabatan', 'jadwals')->where('active_status',1);
        $karNonAktif    = Karyawan::with('divisi', 'jabatan', 'jadwals')->where('active_status',2);
        
        $tanggal = Carbon::now()->toDateString();
        
        if(isset($req['tanggal']))
        {
            $tanggal =  $req['tanggal'];
        }
        $karNonAktif->where('active_status_date', '>', $tanggal);
        
        if(isset($req['divisi']))
        {
            $karAktif->where('divisi_id', $req['divisi']);
            $karNonAktif->where('divisi_id', $req['divisi']);
        }
        
        if(isset($req['perusahaan']))
        {
            $karAktif->where('perusahaan_id', $req['perusahaan']);
            $karNonAktif->where('perusahaan_id', $req['perusahaan']);
        }
        
        $karAktif->orderBy('divisi_id', 'asc')->orderBy('pin', 'asc');
        $karNonAktif->orderBy('divisi_id', 'asc')->orderBy('pin', 'asc');
//        dd($karAktif->get());
        $karyawan = null;
        
        foreach($karAktif->get() as $kA)
        {
            $jadwal = $kA->jadwals;
            $karyawan[] = ['nik' => ((isset($kA->nik))?$kA->nik:''),
                           'pin' => ((isset($kA->pin))?$kA->pin:''),
                           'nama'=> ((isset($kA->nama))?$kA->nama:''),
                           'kode_divisi' => ((isset($kA->divisi->kode))?$kA->divisi->kode:''),
                           'nama_divisi' => ((isset($kA->divisi->deskripsi))?$kA->divisi->deskripsi:''),
                           'kode_jabatan' => ((isset($kA->jabatan->kode))?$kA->jabatan->kode:''),
                           'nama_jabatan' => ((isset($kA->jabatan->deskripsi))?$kA->jabatan->deskripsi:''),
                           'tmk' => ((isset($kA->tanggal_masuk))?$kA->tanggal_masuk:''),
                           'jadwal' =>((isset($jadwal[0]))?$jadwal[0]->kode.' - '.$jadwal[0]->tipe:'') ];
        }
        
        foreach($karNonAktif->get() as $kA)
        {
            $jadwal = $kA->jadwals;
            $karyawan[] = ['nik' => ((isset($kA->nik))?$kA->nik:''),
                           'pin' => ((isset($kA->pin))?$kA->pin:''),
                           'nama'=> ((isset($kA->nama))?$kA->nama:''),
                           'kode_divisi' => ((isset($kA->divisi->kode))?$kA->divisi->kode:''),
                           'nama_divisi' => ((isset($kA->divisi->deskripsi))?$kA->divisi->deskripsi:''),
                           'kode_jabatan' => ((isset($kA->jabatan->kode))?$kA->jabatan->kode:''),
                           'nama_jabatan' => ((isset($kA->jabatan->deskripsi))?$kA->jabatan->deskripsi:''),
                           'tmk' => ((isset($kA->tanggal_masuk))?$kA->tanggal_masuk:''),
                           'jadwal' =>((isset($jadwal[0]))?$jadwal[0]->kode.' - '.$jadwal[0]->tipe:'') ];
        }
        
//        dd($karyawan);
        
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.karyawan_aktif.preview', ['var' => $karyawan, 
                'periode' => $tanggal, 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $view = (string)View::make('admin.laporan.karyawan_aktif.preview', ['var' => $karyawan, 'periode' => $tanggal, 'printDate' => Carbon::now()]);
//            dd($view);
            $pdf = PDF::loadHTML($view);
//            $pdf = PDF::loadView('admin.laporan.karyawan_aktif.preview', ['var' => $karyawan, 'periode' => $tanggal, 'printDate' => Carbon::now()])->setPaper('a4', 'landscape');
            return $pdf->stream('Laporan Karyawan Aktif '.Carbon::now()->format('d-m-Y').'.pdf');
        }
        else
        {
            return abort(404,'Not Found');
        }
    }
    
    public function laporanKaryawanMangkirTa(Request $request)
    {
        $req = $request->all();
        
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
                $q->where('divisi_id', $req['divisi']);
            });
        }
        
        if(isset($req['perusahaan']))
        {
            $absen->whereHas('karyawan', function($q) use($req)
            {
                $q->where('perusahaan_id', $req['perusahaan']);
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
            
            $karRet[] = [
                'pin' => $absenRow->karyawan->pin,
                'nama_karyawan' => $absenRow->karyawan->nama,
                'tanggal_masuk' => $absenRow->karyawan->tanggal_masuk,
                'kode_divisi' => $absenRow->karyawan->divisi->kode,
                'nama_divisi' => $absenRow->karyawan->divisi->deskripsi,
                'tanggal' => $absenRow->tanggal,
                'jadwal_kerja' => substr($absenRow->jadwal_jam_masuk,0,5).' - '.substr($absenRow->jadwal_jam_keluar,0,5),
                'jam_kerja' => (!empty($absenRow->jam_masuk)?substr($absenRow->jam_masuk,0,5):'00:00').' - '.(!empty($absenRow->jam_keluar)?substr($absenRow->jam_keluar,0,5):'00:00'),
                'keterangan' => $ket
            ];
//            dd($karRet);
        }
        
        ///////
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.karyawan_mangkir_ta.preview', ['var' => $karRet, 
                'periode' => $req['tanggal'], 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $view = (string)View::make('admin.laporan.karyawan_mangkir_ta.preview', ['var' => $karRet, 'periode' => $req['tanggal'], 'printDate' => Carbon::now()]);
            
            $pdf = PDF::loadHTML($view);
            
            return $pdf->stream('Laporan Karyawan Mangkir Ta '.Carbon::now()->format('d-m-Y').'.pdf');
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
        });
        
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
        
        ///////
        if($req['btnSubmit'] == "preview")
        {
            return view('admin.laporan.karyawan_habis_kontrak.preview', ['var' => $karRet, 
                'periode' => $req['tanggal'], 
                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $view = (string)View::make('admin.laporan.karyawan_habis_kontrak.preview', ['var' => $karRet, 'periode' => $req['tanggal'], 'printDate' => Carbon::now()]);
//            dd($view);
            $pdf = PDF::loadHTML($view);
//            $pdf = PDF::loadView('admin.laporan.karyawan_aktif.preview', ['var' => $karyawan, 'periode' => $tanggal, 'printDate' => Carbon::now()])->setPaper('a4', 'landscape');
            return $pdf->stream('Laporan Karyawan Mangkir Ta '.Carbon::now()->format('d-m-Y').'.pdf');
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
        
        $div = Divisi::with('karyawan');
        
        if(isset($req['divisi']))
        {
            $div->where('id', $req['divisi']);
        }
                
        $periode = null;
        if(isset($req['tanggal']))
        {
            $tgl = Carbon::createFromFormat('Y-m-d', $req['tanggal'].'-22')->subMonth();

            $periode = CarbonPeriod::create($tgl, $tgl->copy()->addMonth(1)->subDay(1))->toArray();
            
        }
        
        $perusahaan = null;
        if(isset($req['perusahaan']))
        {
            $perusahaan = $req['perusahaan'];
        }
        
        foreach($div->get() as $rowDiv)
        {
            if($rowDiv->karyawan->count() > 0)
            {
                $kar = [];
                
                foreach($rowDiv->karyawan as $rKar)
                {
                    $jadwals = $rKar->jadwals()->first();
                    $kar[] = [
                        'tanggal_masuk' => $rKar->tanggal_masuk,
                        'pin' => $rKar->pin,
                        'jenkel' => $rKar->jeniskelamin->nama,
                        'jadwal' => $jadwals['kode'],
                        'nama' => $rKar->nama
                    ];
                }
                
                $ret[] = [
                    'periode_awal' => reset($periode)->toDateString(),
                    'periode_akhir' => end($periode)->toDateString(),
                    'periode' => $periode,
                    'kode_bagian' => $rowDiv->kode,
                    'nama_bagian' => $rowDiv->deskripsi,
                    'karyawan' => $kar
                ];
            }
        }
        
        if($req['btnSubmit'] == "preview")
        {
            $view = (string)View::make('admin.laporan.karyawan_daftar_hadir.preview', ['var' => $ret, 'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
//            dd($view);
            $storage = Storage::put('others/daftarhadirkaryawan.tpk',$view);
            dd($storage);
            
//            $html = IOFactory::createReader('Html');
//            $cnt = $html->load(storage_path().'/others/daftarhadirkaryawan.tpk');
            
//            dd($cnt);
            
//            return view('admin.laporan.karyawan_daftar_hadir.preview', ['var' => $ret,
//                'printDate' => Carbon::now()->format('d-m-Y H:i:s')]);
        }
        else if($req['btnSubmit'] == "pdf")
        {
            $view = (string)View::make('admin.laporan.karyawan_habis_kontrak.preview', ['var' => $karRet, 'periode' => $req['tanggal'], 'printDate' => Carbon::now()]);
//            dd($view);
            $pdf = PDF::loadHTML($view);
//            $pdf = PDF::loadView('admin.laporan.karyawan_aktif.preview', ['var' => $karyawan, 'periode' => $tanggal, 'printDate' => Carbon::now()])->setPaper('a4', 'landscape');
            return $pdf->stream('Laporan Karyawan Mangkir Ta '.Carbon::now()->format('d-m-Y').'.pdf');
        }
        else
        {
            return abort(404,'Not Found');
        }
        
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
                if(isset($req['perusahaan']))
                {
                    $karyawanId = Karyawan::where('divisi_id', $req['divisi'])->where('perusahaan_id', $req['perusahaan'])->orderBy('pin', 'asc')->pluck('id');
                }
                else
                {
                    $karyawanId = Karyawan::where('divisi_id', $req['divisi'])->orderBy('pin', 'asc')->pluck('id');
                }
            }
            else
            {
                if(isset($req['perusahaan']))
                {
                    $karyawanId = Karyawan::orderBy('divisi_id', 'asc')->where('perusahaan_id', $req['perusahaan'])->orderBy('pin', 'asc')->pluck('id');
                }
                else
                {
                    $karyawanId = Karyawan::orderBy('divisi_id', 'asc')->orderBy('pin', 'asc')->pluck('id');
                }
            }            
            
            foreach ($karyawanId as $kId)
            {
                $kar = Karyawan::find($kId);
                
                $tmk = null;
                $active = null;
                
                if($kar->tanggal_masuk)
                {
                    $tmk = Carbon::createFromFormat('Y-m-d', $kar->tanggal_masuk);
                }
                
                if($kar->active_status_date)
                {
                    $active = Carbon::createFromFormat('Y-m-d', $kar->active_status_date);
                }
                
                
                
                if($kar->tanggal_masuk)
                {
                    $tmk = Carbon::createFromFormat('Y-m-d', $kar->tanggal_masuk);
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
                    $arrTgl = array();
                    foreach ($periode as $per)
                    {
                        $arrTgl[$per->format('d/m/Y')] = new \stdClass();
                        $arrTgl[$per->format('d/m/Y')] = $pAbsen->where('tanggal', $per->toDateString())->first();
                        
                        if(isset($arrTgl[$per->format('d/m/Y')]->alasan_id))
                        {
                            $alasanId = json_decode($arrTgl[$per->format('d/m/Y')]->alasan_id, true);
                            $alasan = Alasan::find($alasanId);
                            $arrTgl[$per->format('d/m/Y')]->alasan = $alasan;
                        }
                        
                        if($tmk)
                        {
                            if($tmk->diffInDays($per, false) < 0)
                            {
                                $arrTgl[$per->format('d/m/Y')]->inout = 'IN';
                            }
                        }
                        
                        if($active)
                        {
                            if($active->diffInDays($per, false)>=0)
                            {
                                $arrTgl[$per->format('d/m/Y')]->inout = 'OUT';
                            }
                        }
                        
                    }
                    
                    $ret[] = array('karyawan' => $kar,
                                   'periodeStart' => reset($periode)->toDateString(),
                                   'periodeEnd' => end($periode)->toDateString(),
                                   'absen' => $arrTgl);
                    
                }
                else
                {
                    continue;
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
}
