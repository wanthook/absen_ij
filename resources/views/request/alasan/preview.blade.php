@extends('adminlte3.app')

@section('title_page')
<p>Preview Transaksi Request Alasan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Preview Transaksi Request Alasan</li>
@endsection

@section('add_css')
<!-- Datatables -->
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables.net-select-bs4/css/select.bootstrap4.min.css')}}">

<!-- select2 -->
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/select2/css/select2.min.css')}}">
<!-- daterange picker -->
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/daterangepicker/daterangepicker.css')}}">
@endsection

@section('add_js')
<!-- Datatables -->
<script src="{{asset('bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('bower_components/admin-lte/plugins/datatables.net-select-bs4/js/select.bootstrap4.min.js')}}"></script>
<!-- bootstrap color picker -->
<script src="{{asset('bower_components/admin-lte/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
<!-- select2 -->
<script src="{{asset('bower_components/admin-lte/plugins/select2/js/select2.full.min.js')}}"></script>
<!-- date-range-picker -->
<script src="{{asset('bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js')}}"></script>
<!-- bs-custom-file-input -->
<script src="{{asset('bower_components/admin-lte/plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>

<script src="{{asset('js/myjs.js')}}"></script>
<script>
$(function(e)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
});
</script>
@endsection

@section('content')

<div class="row">        
    <div class="col-4">
        <div class="row">
            <div class="col-12">                
                <div class="card card-primary card-outline">
                    <div class="card-header">Master Request Alasan</div>
                    {{Form::hidden('id', $var->id, ['id' => 'id'])}}
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-striped table-sm table-borderless">
                                <tr>
                                    <td><label>No Dokumen</label></td>
                                    <td>{{$var->no_dokumen}}</td>
                                </tr>
                                <tr>
                                    <td><label>File Dokumen</label></td>
                                    <td>
                                    @php
                                        if(isset($var->file_dokumen) && !empty($var->file_dokumen))
                                        {
                                            @endphp
                                            <a class="btn btn-success btn-sm" href="{{route('downloadrequestalasan',$var->file_dokumen)}}"><i class="fa fa-file-pdf"></i>{{$var->file_dokumen}}</a>
                                            @php
                                        }
                                        else
                                        {
                                            @endphp
                                            <a class="btn btn-success btn-sm" href="#"><i class="fa fa-file-pdf"></i></a>
                                            @php
                                        }
                                    @endphp
                                    
                                    </td>
                                </tr>
                                <tr>
                                    <td><label>Tanggal Request</label></td>
                                    <td>{{$var->tanggal}}</td>
                                </tr>
                                <tr>
                                    <td><label>Catatan</label></td>
                                    <td>{{$var->catatan}}</td>
                                </tr>
                                    <td><label>Status Dokumen</label></td>
                                    <td>
                                        @php
                                            if($var->status == 'new')
                                                echo '<span class="badge badge-primary">'.ucfirst($var->status).'</span>';
                                            else if($var->status == 'send')
                                                echo '<span class="badge badge-secondary">'.ucfirst($var->status).'</span>';
                                            else if($var->status == 'approve')
                                                echo '<span class="badge badge-success">'.ucfirst($var->status).'</span>';
                                            else if($var->status == 'decline')
                                                echo '<span class="badge badge-danger">'.ucfirst($var->status).'</span>';
                                        @endphp
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>  
                </div>
            </div>
        </div>
    </div>
    <div class="col-8">
        <div class="row">
            <div class="col-12">                
                <div class="card card-primary card-outline">
                    <div class="card-header">List Karyawan</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <th class='ttanggal'>Tanggal</th>
                                        <th class='tpin'>PIN</th>
                                        <th class='tnama'>Nama</th>
                                        <th class='tdivisi'>Divisi</th>
                                        <th class='talasan'>Alasan</th>
                                        <th class='twaktu'>Waktu</th>
                                        <th class='tcatatan'>Catatan</th>
                                        <th class='tstatus'>Status</th>
                                    </thead>
                                    <tbody>
                                        @php
                                            $dataKar = \App\RequestAlasanDetail::with('karyawan', 'alasan')->where('request_alasan_id', $var->id);
                                            if($var->status != 'approve')
                                            {
                                                $dataKar->whereNull('status');
                                            }
                                            foreach($dataKar->get() as $rKar)
                                            {
                                                                                            
                                                $arr = [
                                                $rKar->tanggal.(($rKar->tanggal_akhir)?' - '.$rKar->tanggal_akhir:''),
                                                $rKar->karyawan->pin,
                                                $rKar->karyawan->nama,
                                                (isset($rKar->karyawan->divisi->kode)?$rKar->karyawan->divisi->kode.' - '.$rKar->karyawan->divisi->deskripsi:''),
                                                $rKar->alasan->kode.' - '.$rKar->alasan->deskripsi,
                                                $rKar->waktu,
                                                $rKar->catatan,
                                                ($rKar->status == 'decline')?'<span class="badge badge-danger">Decline</span>':(($rKar->status == 'approve')?'<span class="badge badge-success">Approve</span>':$rKar->status)
                                                ];
                                                
                                                echo '<tr><td>'.implode('</td><td>',$arr).'</tr></td>';
                                            }
                                        @endphp
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection