@extends('adminlte3.app')

@section('title_page')
<p>Transaksi Alasan Karyawan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Transaksi Alasan Karyawan</li>
@endsection

@section('add_css')
    <!-- jsgrid -->
    <link rel="stylesheet" href="{{asset('bower_component/jsgrid/dist/jsgrid.min.css')}}">
    <link rel="stylesheet" href="{{asset('bower_component/jsgrid/dist/jsgrid-theme.min.css')}}">
   
@endsection

@section('add_js')
    <!-- Datatables -->
    <script src="{{asset('bower_component/jsgrid/dist/jsgrid.min.js')}}"></script>
    <script>
        var tbl = null;
        $(function(e)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            let Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            
            var toastOverlay = Swal.mixin({
                position: 'center',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false
            });
            
            tbl = $('#tblData').jsGrid(
            {
                width: "100%",
                height: "400px",

                inserting: true,
                editing: true,
                sorting: true,
                paging: true,                
                controller: {
                    loadData: function() {
                        var d = $.Deferred();

                        $.ajax({
                            url     : "{{ route('dttalasankaryawan') }}",
                            type    : 'POST',
                            dataType: "json"
                        }).done(function(response) {
                            d.resolve(response.value);
                        });

                        return d.promise();
                    }
                },
                
                fields:[
                    {name: 'pin', type: 'select'
                                
                ]
            });
        });
    </script>
@endsection

@section('modal_form')
<div class="modal fade" id="modal-form-upload">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Alasan Karyawan</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<!--        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >-->
            {{ Form::open(['route' => ['uploadalasankaryawan'], 'id' => 'form_data_upload', 'files' => true]) }}
            {{ Form::hidden('id',null, ['id' => 'uploadId']) }}
            <input type="hidden" name="id" id="id">
            <div class="modal-body">   
                <div class="form-group">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="kode">File</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="formUpload" name="formUpload">
                                        <label class="custom-file-label" for="formUpload">Choose file</label>
                                    </div>
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="cmdUpload">Upload</span>
                                    </div>
                                </div>
                            </div>
                        </div>  
                        <div class="col-12">
                            <a class="btn btn-info btn-xs" href="{{route('app.files', 'file_temp_karyawan_alasan')}}" target="_blank"><i class="fa fa-download"></i>Template Document</a>
                        </div>
                    </div>
                </div>
            </div>   
        {{ Form::close() }}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection

@section('content')
<div class="row">      
    <div class="col-12">
        <div id="tblData"></div>
    </div>  
</div>
@endsection