@extends('adminlte3.app')

@section('title_page')
<p>Transaksi Absen Manual Karyawan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Transaksi Absen Manual Karyawan</li>
@endsection

@section('add_css')
    <!-- Datatables -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables.net-select-bs4/css/select.bootstrap4.min.css')}}">
    <!-- bootstrap color picker -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/select2/css/select2.min.css')}}">
    <!-- daterange picker -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/daterangepicker/daterangepicker.css')}}">
    <style>
        td.details-control {
            background: url('{{asset('images/details_open.png')}}') no-repeat center center;
            cursor: pointer;
        }
        tr.shown td.details-control {
            background: url('{{asset('images/details_close.png')}}') no-repeat center center;
        }
    </style>
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
    <script src="{{asset('bower_components/admin-lte/plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>
    <script>
        let dTableKar = null;
        
        $(function(e)
        {
            bsCustomFileInput.init();
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
            
            $('#sTanggal').daterangepicker({
                singleDatePicker:true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            });  
            
            $('#sTanggal').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
                dTableKar.ajax.reload();
            });
//            $('#sWaktuIn, #sWaktuOut').datetimepicker({
//                format: 'HH:mm',
//                use24hours: true
//              });
            $('#cmdUpload').on('click', function(e)
            {
                let frm = document.getElementById('form_data_upload');
                let datas = new FormData(frm);
//                console.log($('#form_data_upload').attr('action'));
                $.ajax(
                {
                    url         : $('#form_data_upload').attr('action'),
                    dataType    : 'JSON',
                    type        : 'POST',
                    data        : datas ,
                    processData: false,
                    contentType: false,
                    beforeSend  : function(xhr)
                    {
//                        $('#loadingDialog').modal('show');
                        toastOverlay.fire({
                            type: 'warning',
                            title: 'Sedang memproses data upload',
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success(result,status,xhr)
                    {
                        toastOverlay.close();
                        if(result.status == 1)
                        {
                            Toast.fire({
                                type: 'success',
                                title: result.msg
                            });
                        }
                        else
                        {
                            if(Array.isArray(result.msg))
                            {
                                var str = "";
                                for(var i = 0 ; i < result.msg.length ; i++ )
                                {
                                    str += result.msg[i]+"<br>";
                                }
                                Toast.fire({
                                    type: 'error',
                                    title: str
                                });
                                $('#tipe_exim').attr('disabled','disabled');
                            }
                            
                        }
                        dTableKar.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        /* implementation goes here */ 
                        toastOverlay.close();
                        console.log(jqXHR.responseText);
                    }
                });
            });
            
            $('#cmdTambah').on('click',function(e)
            {
//                dTableKar.ajax.reload();
                e.preventDefault();
                let frm = document.getElementById('frmTransAbsenManual');
                let datas = new FormData(frm);
//                console.log($('#form_data_upload').attr('action'));
                $.ajax(
                {
                    url         : $('#frmTransAbsenManual').attr('action'),
                    dataType    : 'JSON',
                    type        : 'POST',
                    data        : datas ,
                    processData: false,
                    contentType: false,
                    beforeSend  : function(xhr)
                    {
//                        $('#loadingDialog').modal('show');
                        toastOverlay.fire({
                            type: 'warning',
                            title: 'Sedang memproses..',
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success(result,status,xhr)
                    {
                        toastOverlay.close();
                        if(result.status == 1)
                        {
                            Toast.fire({
                                type: 'success',
                                title: result.msg
                            });
                        }
                        else
                        {
                            if(Array.isArray(result.msg))
                            {
                                var str = "";
                                for(var i = 0 ; i < result.msg.length ; i++ )
                                {
                                    str += result.msg[i]+"<br>";
                                }
                                Toast.fire({
                                    type: 'error',
                                    title: str
                                });
                            }
                            
                        }
                        dTableKar.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        /* implementation goes here */ 
                        toastOverlay.close();
//                        console.log(jqXHR.responseText);
                        Toast.fire({
                            type: 'error',
                            title: jqXHR.responseText
                        });
                    }
                });
            });
            
            dTableKar = $('#dTableKar').DataTable({
                "sPaginationType": "full_numbers",
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": true,
                "serverSide": true,
                "select": true,
                "scrollX": true,
                "scrollY": 600,
                "autoWidth": false,
                "lengthMenu": [100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dttabsenmanual') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.sTanggal   = $('#sTanggal').val();
                    }
                },
                "columnDefs"    :[
                    {
                        "targets": 0,
                        "orderable":      false,
                        "data"     :      function(d)
                        {
                            return '<button class="btn btn-sm btn-primary btnedit"><i class="fa fa-edit"></i></button>'+
                                   '<button class="btn btn-sm btn-danger btndel"><i class="fa fa-eraser"></i></button>';
                        }
                    },
                    {
                            targets : 'tpin',
                            data: function(data)
                            {
                                return data.karyawan.pin;
                            }
                    },
                    {
                            targets : 'tnik',
                            data: function(data)
                            {
                                return data.karyawan.nik;
                            }
                    },
                    {
                            targets : 'tnama',
                            data: function(data)
                            {
                                return data.karyawan.nama;
                            }
                    },
                    {
                            targets : 'tjammasuk',
                            data: "jam_masuk"
                    },
                    {
                            targets : 'tjamkeluar',
                            data: "jam_keluar"
                    },
                    {
                            targets : 'tketerangan',
                            data: "keterangan"
                    },
                    {
                            targets : 'tmangkir',
                            data: "mangkir"
                    },

                ]
            });
            
            // Add event listener for opening and closing details
            $('#dTableKar tbody').on('click', '.btndel', function () 
            {
                var tr = $(this).closest('tr');
                var row = dTableKar.row( tr );
                var datas = row.data();

                if(confirm('Apakah Anda yakin menghapus data ini?'))
                {
                    $.ajax(
                    {
                        url         : "{{route('deleteabsenmanual')}}",
                        type        : 'POST',
                        dataType    : 'json',
                        data        : {id:datas.id},
                        success     : function(result,status,xhr)
                        {
                            if(result.status == 1)
                            {
                                tr.fadeOut();
                                dTableKar.row(tr).remove().draw(false);
                                dTableKar.ajax.reload();
                                Toast.fire({
                                    type: 'success',
                                    title: result.msg
                                });
                            }
                            else
                            {
                                Toast.fire({
                                    type: 'success',
                                    title: result.msg
                                });
                            }
                        }
                    });

                    return false;
                }
            } );
            
            
            
            $('#dTableKar tbody').on('click', '.btnedit', function () 
            {
                var tr = $(this).closest('tr');
                var row = dTableKar.row( tr );
                var datas = row.data();
                
                $('#sTanggal').val(datas.tanggal);
                
//                var emptyOption = new Option(null, null, false, false);
                
                var newOption = new Option(datas.karyawan.pin+' - '+datas.karyawan.nama, datas.karyawan_id, true, true);
//                $('#sKar').append(emptyOption).trigger('change'); 
                $('#sKar').append(newOption).trigger('change'); 
                
                $('#sWaktuIn').val(datas.jam_masuk);
                $('#sWaktuOut').val(datas.jam_keluar);
                $('#sKeterangan').val(datas.keterangan);

                if(datas.mangkir == 'Y')
                {
                    $('#sMangkir').prop('checked', true);
                }
                else
                {
                    $('#sMangkir').prop('checked', false);
                }
            });
            
            $('#sKar').select2({
                // placeholder: 'Silakan Pilih',
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('selkaryawan')}}",
                    dataType    : 'json',
                    type : 'post',
                    data: function (params) 
                    {
                        var query = {
                            q: params.term
                        }
                        
                        return query;
                    },
                    processResults: function (data) 
                    {
                        return {
                            results: data.items
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
@endsection

@section('modal_form')
<div class="modal fade" id="modal-form-upload">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Absen Manual Karyawan</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<!--        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >-->
            {{ Form::open(['route' => ['saveabsenmanualupload'], 'id' => 'form_data_upload', 'files' => true]) }}
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
                            <a class="btn btn-info btn-xs" href="{{route('app.files', 'file_temp_absen_manual')}}" target="_blank"><i class="fa fa-download"></i>Template Document</a>
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
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        {{Form::open(['url' => route('saveabsenmanual'),'class'=>'form-data', 'id' => 'frmTransAbsenManual'])}}
                            <div class="row">
                                <div class="col-5">
                                    <div class="form-group">
                                        {{ Form::label('sTanggal', 'Tanggal') }}
                                        <div class="input-group" data-target-input="nearest">
                                            {{ Form::text('sTanggal', null, ['id' => 'sTanggal', 'class' => 'form-control form-control-sm', 'placeholder' => 'Tanggal Absen Manual']) }}
                                            <div class="input-group-append" data-target="#tanggal_masuk">
                                                <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-1">
                                    <div class="form-group">
                                        <button class="btn btn-xs btn-warning" alt="Upload" data-toggle="modal" data-target="#modal-form-upload" type="button"><i class="fa fa-upload"></i><br>Upload</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group">                                        
                                        {{ Form::label('sKar', 'Karyawan') }}
                                        {{ Form::select('sKar', [], null, ['id' => 'sKar', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        {{ Form::label('sWaktuIn', 'Waktu Masuk') }}
                                        <div class="input-group">
                                            {{ Form::text('sWaktuIn',  null, ['id' => 'sWaktuIn', 'class' => 'form-control form-control-sm datetimepicker-input']) }}
                                            <div class="input-group-append" data-target="#sWaktuIn">
                                                <div class="input-group-text"><i class="far fa-clock"></i></div>
                                            </div>
                                        </div>                                        
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        {{ Form::label('sWaktuOut', 'Waktu Pulang') }}
                                        <div class="input-group">
                                            {{ Form::text('sWaktuOut',  null, ['id' => 'sWaktuOut', 'class' => 'form-control form-control-sm datetimepicker-input']) }}
                                            <div class="input-group-append" data-target="#sWaktuOut">
                                                <div class="input-group-text"><i class="far fa-clock"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        {{ Form::label('sKeterangan', 'Keterangan') }}
                                        {{ Form::text('sKeterangan',  null, ['id' => 'sKeterangan', 'class' => 'form-control form-control-sm', 'style'=> 'width: 100%;']) }}
                                    </div>
                                </div>
                                
                                <div class="col-1">
                                    <div class="form-group">
                                        {{ Form::label('sMangkir', 'Mangkir?') }}
                                        {{ Form::checkbox('sMangkir',  'Y', false, ['id' => 'sMangkir', 'class' => 'form-control form-control-sm', 'style'=> 'width: 100%;']) }}
                                    </div>
                                </div>
                                <div class="col-1">
                                    <div class="form-group">
                                        <button class="btn btn-success btn-xs" id="cmdTambah"><i class="fa fa-plus-circle"></i><br>Tambah</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">  
        <!--                <div class="float-right">
                            <button id="btnSet" class="btn btn-info">Set >></button>
                        </div>-->
                        <table id="dTableKar" class="table table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="tpin">PIN</th>
                                    <th class="tnik">NIK</th>
                                    <th class="tnama">Nama</th>
                                    <!--<th class="tdivisi">Divisi</th>-->
                                    <th class="tjammasuk">Jam Masuk</th>
                                    <th class="tjamkeluar">Jam Pulang</th>
                                    <th class="tketerangan">Keterangan</th>
                                    <th class="tmangkir">Mangkir</th>
<!--                                    <th class="talasan">Absen Manual</th>
                                    <th class="twaktu">Waktu</th>-->
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>  
</div>
@endsection