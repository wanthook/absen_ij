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
        .dtl{
            font-family:sourcesanspro,sans-serif;
            border-collapse:collapse;
            width:100%;
            font-size:6pt;
            padding-top:5px;
            padding-bottom:5px;
            text-align:center;
            background-color:#e0e0e0;
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
                <!-- <th colspan="2">JUMLAH OFF</th> -->
                <!-- <th colspan="2">MASUK KERJA</th> -->
                <!-- <th rowspan="2">POT. OFF<br>75%</th> -->
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
                <!-- <th>Rp. (25%)</th>
                <th>JML</th> -->
                <!-- <th>Rp</th>
                <th>JML</th> -->
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
            @php
                $div = "";
                $tDiv = 0;
                $tArr = [
                    'gajiPokok' => 0,
                    'potAbsen' => 0 ,
                    'potAbsenRp' => 0,  
                    'gajiPokokDibayar' => 0,  
                    'lembur' => 0,  
                    'lemburRp' => 0,  
                    's3' => 0,  
                    's3Rp' => 0,  
                    'tunjanganJabatan' => 0,  
                    'tunjanganPrestasi' => 0,  
                    'tunjanganHaid' => 0,  
                    'tunjanganHadir' => 0,  
                    'tunjanganLain' => 0,  
                    'gp' => 0,  
                    'gpRp' => 0,  
                    'koreksiPlus' => 0,  
                    'koreksiMinus' => 0,  
                    'brutoRp' => 0,  
                    'bpjsTk' => 0,  
                    'bpjsKes' => 0,  
                    'bpjsPen' => 0,
                    'pph21' => 0,  
                    'costSerikatRp' => 0,  
                    'toko' => 0,  
                    'lainlain' => 0,  
                    'totAkhir' => 0,  
                    'totBayar' => 0
                ];
            @endphp
            @foreach($var['data'] as $kVar => $vVar)

            @php
            if($div!=$vVar->karyawan->divisi->kode)
            {
                if(!empty($div))
                {
                    $div = $vVar->karyawan->divisi->kode;
                    // dd('$div='.$div.',karkode='.$vVar->karyawan->divisi->kode);
                    
                    @endphp

                    <tr class="dtl">
                        <td colspan="5"><b>Total</b></td>
                        @foreach($tArr as $k => $v)
                        @if($k != 'lembur')
                        <td>{{number_format($v, 0, '', '.')}}</td>
                        @else
                        <td>{{number_format($v, 2, ',', '.')}}</td>
                        @endif
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="32">&nbsp;</td>
                    </tr>
                    @php
                    $tArr = [
                    'gajiPokok' => 0,
                    'potAbsen' => 0 ,
                    'potAbsenRp' => 0,  
                    'gajiPokokDibayar' => 0,  
                    'lembur' => 0,  
                    'lemburRp' => 0,  
                    's3' => 0,  
                    's3Rp' => 0,  
                    'tunjanganJabatan' => 0,  
                    'tunjanganPrestasi' => 0,  
                    'tunjanganHaid' => 0,  
                    'tunjanganHadir' => 0,  
                    'tunjanganLain' => 0,  
                    'gp' => 0,  
                    'gpRp' => 0,  
                    'koreksiPlus' => 0,  
                    'koreksiMinus' => 0,  
                    'brutoRp' => 0,  
                    'bpjsTk' => 0,  
                    'bpjsKes' => 0,  
                    'bpjsPen' => 0,
                    'pph21' => 0,  
                    'costSerikatRp' => 0,  
                    'toko' => 0,  
                    'lainlain' => 0,  
                    'totAkhir' => 0,  
                    'totBayar' => 0
                ];
                    $tDiv=0;
                }
                else
                {
                    $div = $vVar->karyawan->divisi->kode;
                }
            }
            $tDiv+=1;
            @endphp

            <tr>
                <td class="dc">{{$kVar+1}}</td>
                <td class="dc">{{$vVar->karyawan->divisi->deskripsi}}</td>
                <td class="dc">{{$vVar->karyawan->pin}}</td>
                <td class="dc">{{$vVar->karyawan->tanggal_masuk}}</td>
                <td class="dc">{{$vVar->karyawan->nama}}</td>
                @if($vVar->editlistlast && count($vVar->editlistlast)>0)
                @php
                    $tArr['gajiPokok'] += (int)$vVar->editlistlast[0]->gaji_pokok;
                    $tArr['potAbsen'] += (int)$vVar->editlistlast[0]->potongan_absen;
                    $tArr['potAbsenRp'] += (int)$vVar->editlistlast[0]->potongan_absen_rp;
                    $tArr['gajiPokokDibayar'] += (int)$vVar->editlistlast[0]->gaji_pokok_dibayar;
                    $tArr['lembur'] += $vVar->editlistlast[0]->lembur;
                    $tArr['lemburRp'] += (int)$vVar->editlistlast[0]->lembur_rp;
                    $tArr['s3'] += (int)$vVar->editlistlast[0]->s3;
                    $tArr['s3Rp'] += (int)$vVar->editlistlast[0]->s3_rp;
                    $tArr['tunjanganJabatan'] += (int)$vVar->editlistlast[0]->tunjangan_jabatan;
                    $tArr['tunjanganPrestasi'] += (int)$vVar->editlistlast[0]->tunjangan_prestasi;
                    $tArr['tunjanganHaid'] += (int)$vVar->editlistlast[0]->tunjangan_haid;
                    $tArr['tunjanganHadir'] += (int)$vVar->editlistlast[0]->tunjangan_hadir;
                    $tArr['tunjanganLain'] += (int)$vVar->editlistlast[0]->tunjangan_lain;
                    $tArr['gp'] += (int)$vVar->editlistlast[0]->gp;
                    $tArr['gpRp'] += (int)$vVar->editlistlast[0]->gp_rp;
                    $tArr['koreksiPlus'] += (int)$vVar->editlistlast[0]->koreksi_plus;
                    $tArr['koreksiMinus'] += (int)$vVar->editlistlast[0]->koreksi_minus;
                    $tArr['brutoRp'] += (int)$vVar->editlistlast[0]->bruto_rp;
                    $tArr['bpjsTk'] += (int)$vVar->editlistlast[0]->bpjs_tk;
                    $tArr['bpjsKes'] += (int)$vVar->editlistlast[0]->bpjs_kes;
                    $tArr['bpjsPen'] += (int)$vVar->editlistlast[0]->bpjs_pen;
                    $tArr['pph21'] += (int)$vVar->editlistlast[0]->pph21;
                    $tArr['costSerikatRp'] += (int)$vVar->editlistlast[0]->cost_serikat_rp;
                    $tArr['toko'] += (int)$vVar->editlistlast[0]->toko;
                    $tArr['lainlain'] += (int)$vVar->editlistlast[0]->lainlain;
                    $tArr['totAkhir'] += (int)$vVar->editlistlast[0]->tot_akhir;
                    $tArr['totBayar'] += (int)$vVar->editlistlast[0]->tot_bayar;
                @endphp

                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->gaji_pokok, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->potongan_absen, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->potongan_absen_rp, 0, '', '.')}}</td>
                    <!-- <td class="dc">{{number_format((int)$vVar->editlistlast[0]->jumlah_off, 0, '', '.')}}</td> -->
                    <!-- <td class="dc">{{number_format((int)$vVar->editlistlast[0]->jumlah_off_rp, 0, '', '.')}}</td> -->
                    <!-- <td class="dc">{{number_format((int)$vVar->editlistlast[0]->jumlah_absen, 0, '', '.')}}</td> -->
                    <!-- <td class="dc">{{number_format((int)($vVar->editlistlast[0]->gaji_pokok), 0, '', '.')}}</td> -->
                    <!-- <td class="dc">{{number_format((int)$vVar->editlistlast[0]->jumlah_off_rp, 0, '', '.')}}</td> -->
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->gaji_pokok_dibayar, 0, '', '.')}}</td>
                    <td class="dc">{{number_format($vVar->editlistlast[0]->lembur, 2, ',', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->lembur_rp, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->s3, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->s3_rp, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->tunjangan_jabatan, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->tunjangan_prestasi, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->tunjangan_haid, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->tunjangan_hadir, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->tunjangan_lain, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->gp, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->gp_rp, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->koreksi_plus, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->koreksi_minus, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->bruto_rp, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->bpjs_tk, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->bpjs_kes, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->bpjs_pen, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->pph21, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->cost_serikat_rp, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->toko, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->lainlain, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->tot_akhir, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->editlistlast[0]->tot_bayar, 0, '', '.')}}</td>
                @else

                @php
                    $tArr['gajiPokok'] += (int)$vVar->gaji_pokok;
                    $tArr['potAbsen'] += (int)$vVar->potongan_absen;
                    $tArr['potAbsenRp'] += (int)$vVar->potongan_absen_rp;
                    $tArr['gajiPokokDibayar'] += (int)$vVar->gaji_pokok_dibayar;
                    $tArr['lembur'] += $vVar->lembur;
                    $tArr['lemburRp'] += (int)$vVar->lembur_rp;
                    $tArr['s3'] += (int)$vVar->s3;
                    $tArr['s3Rp'] += (int)$vVar->s3_rp;
                    $tArr['tunjanganJabatan'] += (int)$vVar->tunjangan_jabatan;
                    $tArr['tunjanganPrestasi'] += (int)$vVar->tunjangan_prestasi;
                    $tArr['tunjanganHaid'] += (int)$vVar->tunjangan_haid;
                    $tArr['tunjanganHadir'] += (int)$vVar->tunjangan_hadir;
                    $tArr['tunjanganLain'] += (int)$vVar->tunjangan_lain;
                    $tArr['gp'] += (int)$vVar->gp;
                    $tArr['gpRp'] += (int)$vVar->gp_rp;
                    $tArr['koreksiPlus'] += (int)$vVar->koreksi_plus;
                    $tArr['koreksiMinus'] += (int)$vVar->koreksi_minus;
                    $tArr['brutoRp'] += (int)$vVar->bruto_rp;
                    $tArr['bpjsTk'] += (int)$vVar->bpjs_tk;
                    $tArr['bpjsKes'] += (int)$vVar->bpjs_kes;
                    $tArr['bpjsPen'] += (int)$vVar->bpjs_pen;
                    $tArr['pph21'] += (int)$vVar->pph21;
                    $tArr['costSerikatRp'] += (int)$vVar->cost_serikat_rp;
                    $tArr['toko'] += (int)$vVar->toko;
                    $tArr['lainlain'] += (int)$vVar->lainlain;
                    $tArr['totAkhir'] += (int)$vVar->tot_akhir;
                    $tArr['totBayar'] += (int)$vVar->tot_bayar;
                @endphp 
                <td class="dc">{{number_format((int)$vVar->gaji_pokok, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->potongan_absen, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->potongan_absen_rp, 0, '', '.')}}</td>
                    <!-- <td class="dc">{{number_format((int)$vVar->jumlah_off, 0, '', '.')}}</td> -->
                    <!-- <td class="dc">{{number_format((int)$vVar->jumlah_off_rp, 0, '', '.')}}</td> -->
                    <!-- <td class="dc">{{number_format((int)$vVar->jumlah_absen, 0, '', '.')}}</td> -->
                    <!-- <td class="dc">{{number_format((int)($vVar->gaji_pokok), 0, '', '.')}}</td> -->
                    <!-- <td class="dc">{{number_format((int)$vVar->jumlah_off_rp, 0, '', '.')}}</td> -->
                    <td class="dc">{{number_format((int)$vVar->gaji_pokok_dibayar, 0, '', '.')}}</td>
                    <td class="dc">{{number_format($vVar->lembur, 2, ',', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->lembur_rp, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->s3, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->s3_rp, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->tunjangan_jabatan, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->tunjangan_prestasi, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->tunjangan_haid, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->tunjangan_hadir, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->tunjangan_lain, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->gp, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->gp_rp, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->koreksi_plus, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->koreksi_minus, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->bruto_rp, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->bpjs_tk, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->bpjs_kes, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->bpjs_pen, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->pph21, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->cost_serikat_rp, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->toko, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->lainlain, 0, '', '.')}}</td>

                    <td class="dc">{{number_format((int)$vVar->tot_akhir, 0, '', '.')}}</td>
                    <td class="dc">{{number_format((int)$vVar->tot_bayar, 0, '', '.')}}</td>
                @endif
            </tr>
            @endforeach
            <tr class="dtl">
                <td colspan="5"><b>Total</b></td>
                @foreach($tArr as $k => $v)
                @if($k != 'lembur')
                <td>{{number_format($v, 0, '', '.')}}</td>
                @else
                <td>{{number_format($v, 2, ',', '.')}}</td>
                @endif
                @endforeach
            </tr>
        </tbody>
    </table>
    </page>
@endif

</body>
</html>