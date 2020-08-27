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
      @page{margin:10 15 0 15}body{background:#fff;color:#000;font-family:sourcesanspro,Arial,sans-serif;width:100%;height:100%;margin:0;padding:0}.j1{font-family:sourcesanspro,sans-serif;letter-spacing:-.01em;font-style:normal;font-size:14pt;margin-left:-3px;line-height:1em;color:#000;text-align:center;margin-bottom:2px}.j2{font-family:sourcesanspro,sans-serif;letter-spacing:-.01em;font-style:normal;font-size:10pt;margin-left:-3px;line-height:1em;color:#000;text-align:center;margin-bottom:2px}.info{font-family:sourcesanspro,sans-serif;font-style:normal;font-size:9pt;line-height:1em;color:#000;text-align:justify}.info-print{font-family:sourcesanspro,sans-serif;font-style:italic;font-size:6pt;line-height:1em;color:#000;text-align:right}.detail{font-family:sourcesanspro,sans-serif;border-collapse:collapse;width:100%;font-size:8pt}.detail td,.detail th{border:1px solid #000;padding:2px}.detail td,.detail th{padding:2px}.dc{text-align:center}.detail th{padding-top:5px;padding-bottom:5px;text-align:center;background-color:#a0a0a0;color:#fff}.page-break{page-break-after:always}        
  </style>
</head>
<body>
@if(isset($var))
    <div class="j1">Laporan Kehadiran Karyawan Komulatif</div>
    <div class="j2">Periode : {{reset($periode)->format('d/m/Y')}} s/d {{end($periode)->format('d/m/Y')}}</div>
    <table class="detail">
        <thead>
            <tr>
                <th>No</th>
                <th>PIN</th>
                <th>TMK</th>
                <th>SEX</th>
                <th>Kode</th>
                <th>Divisi</th>
                <th>Nama</th>
                @foreach($periode as $per)
                <th>{{$per->format('d')}}</th>
                @endforeach
                <th>Lbr</th>
                <th>S3</th>
                <th>GP</th>
                <th>JK</th>
            </tr>
        </thead>
        <tbody>
            @foreach($var as $kVar => $vVar)
                @php
                    $tLembur = 0;
                    $s3 = 0;
                    $jGp = 0;
                    $jJk = 0;
                @endphp
            <tr>
                <td class="dc">{{$kVar+1}}</td>
                <td class="dc">{{isset($vVar['karyawan']->pin)?$vVar['karyawan']->pin:''}}</td>
                <td>{{isset($vVar['karyawan']->tanggal_masuk)?$vVar['karyawan']->tanggal_masuk:''}}</td>
                <td class="dc">{{isset($vVar['karyawan']->jeniskelamin->nama)?$vVar['karyawan']->jeniskelamin->nama:''}}</td>
                <td class="dc">{{isset($vVar['karyawan']->divisi->kode)?$vVar['karyawan']->divisi->kode:''}}</td>
                <td class="dc">{{isset($vVar['karyawan']->divisi->deskripsi)?$vVar['karyawan']->divisi->deskripsi:''}}</td>
                <td>{{isset($vVar['karyawan']->nama)?$vVar['karyawan']->nama:''}}</td>
                @foreach($vVar['absen'] as $kabs => $vabs) 
                    @php
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
                    @endphp
                    <td class="dc">{{$lbl}}</td>
                @endforeach
                <td class="dc">{{$tLembur}}</td>
                <td class="dc">{{$s3}}</td>
                <td class="dc">{{$jGp/60}}</td>
                <td class="dc">{{$jJk}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

</body>
</html>