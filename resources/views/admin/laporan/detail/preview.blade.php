<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>Aplikasi Absensi Indah Jaya</title>
  <link href="{{asset('fonts/vendor/google-font/SourceSansPro/sourcesanspro.css')}}" rel="stylesheet">
  <style>
        body {
            background: rgb(204,204,204); 
        }
        page {
            background: white;
            display: block;
            margin: 0 auto;
            margin-bottom: 0.5cm;
            box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
        }
        page[size="A4"] {  
            width: 21cm;
            height: 29.7cm; 
        }
        page[size="A4"][layout="landscape"] {
            width: 29.7cm;
            height: 21cm;  
        }
        page[size="A3"] {
            width: 29.7cm;
            height: 42cm;
        }
        page[size="A3"][layout="landscape"] {
            width: 42cm;
            height: 29.7cm;  
        }
        page[size="A5"] {
            width: 14.8cm;
            height: 21cm;
        }
        page[size="A5"][layout="landscape"] {
            width: 21cm;
            height: 14.8cm;  
        }
        .j1{
            font-family:sourcesanspro,sans-serif;
            letter-spacing:-.01em;
            font-style:normal;
            font-size:14pt;
            margin-left:-3px;
            line-height:1em;
            color:#000;
            text-align:center;
            margin-bottom:2px
        }
        .j2{
            font-family:sourcesanspro,sans-serif;
            letter-spacing:-.01em;
            font-style:normal;
            font-size:10pt;
            margin-left:-3px;
            line-height:1em;
            color:#000;
            text-align:center;
            margin-bottom:2px
        }
        .info{
            font-family:sourcesanspro,sans-serif;
            font-style:normal;
            font-size:9pt;
            line-height:1em;
            color:#000;
            text-align:justify
        }
        .ip{
            font-family:sourcesanspro,sans-serif;
            font-style:italic;
            font-size:6pt;
            line-height:1em;
            color:#000;
            text-align:right
        }
        .detail{
            font-family:sourcesanspro,sans-serif;
            border-collapse:collapse;
            width:100%;
            font-size:8pt
        }
        .detail td,.detail th{
            border:1px solid #000;
            padding:2px
        }.detail td,.detail th{
            padding:2px
        }.dc{
            text-align:center
        }
        .detail th{
            padding-top:5px;
            padding-bottom:5px;
            text-align:center;
            background-color:#a0a0a0;
            color:#fff
        }
        .page-break{
            page-break-after:always
        }
        @media print {
          body, page {
            margin: 0;
            box-shadow: 0;
          }
        }  
  </style>
</head>
<body>
@if(isset($var))
    @foreach($var as $vVal)
    @php
        $lemburAktual = 0;
        $hitungLembur = 0;
        $shiftMalam = 0;
        $lemburLn = 0;
        $hitungLn = 0;
        $totLem = 0;
        $tLembur = 0;
    @endphp
    <page size="A4" layout="landscape">
    <div class="j1">Laporan Kehadiran Karyawan</div>
    <div class="j2">Periode : {{$vVal['periodeStart']}} s/d {{$vVal['periodeEnd']}}</div>
    <table class="info">
        <tr>
            <td>PIN / Nama</td>
            <td>:</td>
            <td>{{$vVal['karyawan']->pin.' - '.$vVal['karyawan']->nama}}</td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td>:</td>
            <td>{{$vVal['karyawan']->divisi->kode.' - '.$vVal['karyawan']->divisi->deskripsi}}</td>
        </tr>
        <tr>
            <td>NIK</td>
            <td>:</td>
            <td>{{$vVal['karyawan']->nik}}</td>
        </tr>
    </table>
    <table class="detail">
        <thead>
            <tr>
                <th rowspan="2">Tanggal</th>
                <th colspan="2">Jadwal Kerja</th>
                <th colspan="2">Jam Kerja</th>
                <th colspan="2">Masuk</th>
                <th colspan="2">Pulang</th>
                <th rowspan="2">Keterangan</th>
                <th rowspan="2">Lembur<br>Aktual</th>
                <th rowspan="2">Hitung<br>Lembur</th>
                <th rowspan="2">Shift<br>Malam</th>
                <th rowspan="2">Lembur<br>Libur<br>Nas</th>
                <th rowspan="2">Hitung<br>Libur<br>Nas</th>
                <th rowspan="2">Total<br>Lembur</th>
            </tr>
            <tr>
                <th>M</th>
                <th>K</th>
                <th>M</th>
                <th>K</th>
                <th>C</th>
                <th>T</th>
                <th>C</th>
                <th>T</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vVal['absen'] as $kabs => $vabs)            
            <tr>
                <td class="dc">{{$kabs}}</td>
                @if($vabs)
                @php
                $tLembur = $vabs->hitung_lembur + $vabs->hitung_lembur_ln;
                
                $lemburAktual += $vabs->lembur_aktual;
                $hitungLembur += $vabs->hitung_lembur;
                $shiftMalam += $vabs->shift3;
                $lemburLn += $vabs->lembur_ln;
                $hitungLn += $vabs->hitung_lembur_ln;
                
                $totLem += $tLembur;
                @endphp
                <td class="dc">{{substr($vabs->jadwal_jam_masuk,0,5)}}</td>
                <td class="dc">{{substr($vabs->jadwal_jam_keluar,0,5)}}</td>
                <td class="dc">{{substr($vabs->jam_masuk,0,5)}}</td>
                <td class="dc">{{substr($vabs->jam_keluar,0,5)}}</td>
                <td class="dc">{{($vabs->n_masuk < 0)?abs($vabs->n_masuk):''}}</td>
                <td class="dc">{{($vabs->n_masuk > 0)?abs($vabs->n_masuk):''}}</td>
                <td class="dc">{{($vabs->n_keluar > 0)?abs($vabs->n_keluar):''}}</td>
                <td class="dc">{{($vabs->n_keluar < 0)?abs($vabs->n_keluar):''}}</td>
                <td>{{$vabs->keterangan}}</td>
                <td>{{$vabs->lembur_aktual}}</td>
                <td>{{$vabs->hitung_lembur}}</td>
                <td class="dc">{{$vabs->shift3}}</td>
                <td>{{$vabs->lembur_ln}}</td>
                <td>{{$vabs->hitung_lembur_ln}}</td>
                <td>{{($tLembur)?$tLembur:''}}</td>
                @else
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                @endif
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="dc" colspan="10"><strong>Total</strong></td>
                <td><strong>{{($lemburAktual)?$lemburAktual:'0.0'}}</strong></td>
                <td><strong>{{($hitungLembur)?$hitungLembur:'0.0'}}</strong></td>
                <td><strong>{{($shiftMalam)?$shiftMalam:'0.0'}}</strong></td>
                <td><strong>{{($lemburLn)?$lemburLn:'0.0'}}</strong></td>
                <td><strong>{{($hitungLn)?$hitungLn:'0.0'}}</strong></td>
                <td><strong>{{($totLem)?$totLem:'0.0'}}</strong></td>
            </tr>
        </tfoot>
    </table>
    <div class="ip">Copyright &copy; Indah Jaya Textile Industry, PT. Print Date, {{$printDate}}</div>
    </page>
    <div class="page-break"></div>
    @endforeach
@endif

</body>
</html>