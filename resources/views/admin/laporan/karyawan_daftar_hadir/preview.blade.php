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
@foreach($var as $rVar)
<page size="A4" layout="landscape">
    <div class="j1">Daftar Kehadiran Karyawan</div>
    <table class="info">
        <tr>
            <td>Periode</td>
            <td>:</td>
            <td>{{$rVar['periode_awal'].' S/D '.$rVar['periode_akhir']}}</td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td>:</td>
            <td>{{$rVar['kode_bagian'].' - '.$rVar['nama_bagian']}}</td>
        </tr>
    </table>
    <table class="detail">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Nama Karyawan</th>
                <th rowspan="2">Tanggal<br>Masuk</th>
                <th rowspan="2">L/P</th>
                <th rowspan="2">PIN</th>
                <th rowspan="2">Kd Jad</th>
                <th colspan="{{count($rVar['periode'])}}">Tanggal</th>               
                <th rowspan="2">Keterangan</th>
            </tr>
            <tr>
                @foreach($rVar['periode'] as $per)
                    <th>{{$per->format('d')}}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rVar['karyawan'] as $kKar => $rKar)
            <tr style="line-height: 9px;">
                <td>{{$kKar+1}}</td>
                <td>{{substr($rKar['nama'],0,15)}}</td>
                <td>{{$rKar['tanggal_masuk']}}</td>
                <td>{{$rKar['jenkel']}}</td>
                <td>{{$rKar['pin']}}</td>
                <td>{{$rKar['jadwal']}}</td>
                @foreach($rVar['periode'] as $per)
                    <td></td>
                @endforeach
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</page>
@endforeach
@endif

</body>
</html>