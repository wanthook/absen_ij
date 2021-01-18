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
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Aplikasi Absensi Indah Jaya</title>
  <!-- Theme style -->
  <!--<link rel="stylesheet" href="{{asset('bower_components/admin-lte/dist/css/adminlte.min.css')}}">-->
  <!-- Google Font: Source Sans Pro -->
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
        page[size="F4"] {  
            width: 21.59cm;
            height: 33cm; 
        }
        page[size="F4"][layout="landscape"] {
            width: auto;
            height: auto;  
        }
        page[size="A4"] {  
            width: 21cm;
            height: 29.7cm; 
        }
        page[size="A4"][layout="landscape"] {
            width: 29.7cm;
            height: auto;  
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
            font-size:5pt
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
            color:#fff;
            font-size: 5pt;
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
    <page size="F4" layout="landscape">
    <div class="j1">Laporan List Gaji</div>
    <div class="j2">Periode : {{reset($var['periode'])->format('d/m/Y')}} s/d {{end($var['periode'])->format('d/m/Y')}}</div>
    <table class="detail">
        <thead>
            <tr>
                <th rowspan="2">NO</th>
                <th rowspan="2">NAMA<br>DIVISI</th>
                <th rowspan="2">PIN</th>
                <th rowspan="2">TANGGAL<br>MASUK</th>
                <th rowspan="2">NAMA<br>KARYAWAN</th>
                <th rowspan="2">GAJI<br>POKOK</th>
                <th colspan="2">POT. ABSEN</th>
                <th colspan="2">JUMLAH OFF</th>
                <th colspan="2">MASUK KERJA</th>
                <th rowspan="2">POT. OFF<br>75%</th>
                <th rowspan="2">GAJI POKOK<br>DIBAYAR</th>
                <th colspan="2">UPAH LEMBUR</th>
                <th colspan="2">SHIFT 3</th>
                <th colspan="5">TUNJANGAN</th>
                <th colspan="2">GETPAS</th>
                <th colspan="2">KOREKSI</th>
                <th rowspan="2">PENDAPATAN<br>BRUTO</th>
                <th colspan="7">POTONGAN</th>
                <th rowspan="2">TOTAL<br>AKHIR</th>
                <th rowspan="2">TOTAL<br>BAYAR</th>
            </tr>
            <tr>
                <th>JML</th>
                <th>Rp</th>
                <th>JML</th>
                <th>Rp. (25%)</th>
                <th>JML</th>
                <th>Rp</th>
                <th>JML</th>
                <th>Rp</th>
                <th>JML</th>
                <th>Rp.</th>
                <th>JABATAN</th>
                <th>PRESTASI</th>
                <th>HAID</th>
                <th>HADIR</th>
                <th>LAIN2</th>
                <th>JAM</th>
                <th>Rp.</th>
                <th>KOR ( + )</th>
                <th>KOR ( - )</th>
                <th>BPJS-TK</th>
                <th>BPJS-KES</th>
                <th>BPJS-PEN</th>
                <th>PPH21</th>
                <th>COST SRKT</th>
                <th>TOKO</th>
                <th>LAIN2</th>
            </tr>
        </thead>
        <tbody>
            @foreach($var['data'] as $kVar => $vVar)
            <tr>
                <td class="dc">{{$kVar+1}}</td>
                <td class="dc">{{$vVar->karyawan->divisi->deskripsi}}</td>
                <td class="dc">{{$vVar->karyawan->pin}}</td>
                <td class="dc">{{$vVar->karyawan->tanggal_masuk}}</td>
                <td class="dc">{{$vVar->karyawan->nama}}</td>
                <td class="dc">{{(int)$vVar->gaji_pokok}}</td>
                <td class="dc">{{(int)$vVar->potongan_absen}}</td>
                <td class="dc">{{(int)$vVar->potongan_absen_rp}}</td>
                <td class="dc">{{(int)$vVar->jumlah_off}}</td>
                <td class="dc">{{(int)$vVar->jumlah_off_rp}}</td>
                <td class="dc">{{(int)$vVar->jumlah_absen}}</td>
                <td class="dc">{{(int)($vVar->gaji_pokok)}}</td>
                <td class="dc">{{(int)$vVar->jumlah_off_rp}}</td>
                <td class="dc">{{(int)$vVar->gaji_pokok_dibayar}}</td>
                <td class="dc">{{(int)$vVar->lembur}}</td>
                <td class="dc">{{(int)$vVar->lembur_rp}}</td>
                <td class="dc">{{(int)$vVar->s3}}</td>
                <td class="dc">{{(int)$vVar->s3_rp}}</td>

                <td class="dc">{{(int)$vVar->tunjangan_jabatan}}</td>
                <td class="dc">{{(int)$vVar->tunjangan_prestasi}}</td>
                <td class="dc">{{(int)$vVar->tunjangan_haid}}</td>
                <td class="dc">{{(int)$vVar->tunjangan_hadir}}</td>
                <td class="dc">{{(int)$vVar->tunjangan_lain}}</td>

                <td class="dc">{{(int)$vVar->gp}}</td>
                <td class="dc">{{(int)$vVar->gp_rp}}</td>

                <td class="dc">{{(int)$vVar->koreksi_plus}}</td>
                <td class="dc">{{(int)$vVar->koreksi_minus}}</td>

                <td class="dc">{{(int)$vVar->bruto_rp}}</td>

                <td class="dc">{{(int)$vVar->bpjs_tk}}</td>
                <td class="dc">{{(int)$vVar->bpjs_kes}}</td>
                <td class="dc">{{(int)$vVar->bpjs_pen}}</td>
                <td class="dc">{{(int)$vVar->pph21}}</td>
                <td class="dc">{{(int)$vVar->cost_serikat_rp}}</td>
                <td class="dc">{{(int)$vVar->toko}}</td>
                <td class="dc">{{(int)$vVar->lainlain}}</td>

                <td class="dc">{{(int)$vVar->tot_akhir}}</td>
                <td class="dc">{{(int)$vVar->tot_bayar}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </page>
@endif

</body>
</html>