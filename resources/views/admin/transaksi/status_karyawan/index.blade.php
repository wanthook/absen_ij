@extends('adminlte3.app')

@section('title_page')
<p>Transaksi Status Karyawan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Transaksi Status Karyawan</li>
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
    
    <script>
        let dTableKar = null;
        let dTableKarJad = null;
        let objJadwal = [];
        
        $(function(e)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });   
            
            var toastOverlay = Swal.mixin({
                background: '#000',
                position: 'center',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false
            });
            
            $('#sTanggal').daterangepicker({
                singleDatePicker:true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });  
            
//            $('#sTanggal').on('change', function(e)
//            {
//                dTableKar.ajax.reload();
//            }); 
            
            $('#sTanggal').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
                dTableKar.ajax.reload();
            });
            
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
                            title: 'Sedang memproses data upload'
                        });
                    },
                    success(result,status,xhr)
                    {
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
                    }
                });
            });
            
            $('#btnKembali').on('click', function(e)
            {
                e.preventDefault();
                
                var promises = [];
                
                var selectedId = dTableKar.$('input:checked').map(function () 
                {
                   var _this	= $(this);
                   var datas       = dTableKar.row(_this.parents('tr')).data();
                   
                   var request = $.ajax(
                    {
                        url         : '{{route("savestatuskaryawan", "kembali")}}',
                        dataType    : 'json',
                        type        : 'POST',
                        data        : {sKar : datas.id} ,
                        beforeSend  : function(xhr)
                        {
    //                        $('#loadingDialog').modal('show');
                            toastOverlay.fire({
                                type: 'warning',
                                title: 'Sedang memproses kode mesin '+datas.kode+"..."
                            });
                        },
                        success(result,status,xhr)
                        {
                            if(result.status == 1)
                            {
//                                document.getElementById("form_data").reset(); 

                                Toast.fire({
                                    type: 'success',
                                    title: result.msg
                                });
                                
                                dTableKar.ajax.reload();
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
                                else
                                {
                                    Toast.fire({
                                        type: 'error',
                                        title: result.msg
                                    });
                                }

                            }
                            
                        },
                        error: function(jqXHR, textStatus, errorThrown) { 
                            /* implementation goes here */ 
                            console.log(jqXHR.responseText);
                        }

                    });
                    
                    promises.push(request);
//                    console.log(datas);
                });
                
                $.when.apply(null, promises).done(function()
                {
                    toastOverlay.close();
                            
                    Toast.fire({
                        type: 'success',
                        title: "Semua proses telah diselesaikan."
                    });
                });
//                console.log(selectedId);
            });
            
            $('#btnNonAktif').on('click', function(e)
            {
                e.preventDefault();
                
                var promises = [];
                
                if(confirm("Apakah anda yakin ingin me-Non Aktifkan karyawan yang dipilih?"))
                {
                    var selectedId = dTableKar.$('input:checked').map(function () 
                    {
                        var _this	= $(this);
                        var datas       = dTableKar.row(_this.parents('tr')).data();

                        var request = $.ajax(
                        {
                            url         : '{{route("savestatuskaryawan", "nonaktif")}}',
                            dataType    : 'json',
                            type        : 'POST',
                            data        : {sKar : datas.id} ,
                            beforeSend  : function(xhr)
                            {
        //                        $('#loadingDialog').modal('show');
                                toastOverlay.fire({
                                    type: 'warning',
                                    title: 'Sedang memproses kode mesin '+datas.kode+"..."
                                });
                            },
                            success(result,status,xhr)
                            {
                                if(result.status == 1)
                                {
    //                                document.getElementById("form_data").reset(); 

                                    Toast.fire({
                                        type: 'success',
                                        title: result.msg
                                    });

                                    dTableKar.ajax.reload();
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
                                    else
                                    {
                                        Toast.fire({
                                            type: 'error',
                                            title: result.msg
                                        });
                                    }

                                }

                            },
                            error: function(jqXHR, textStatus, errorThrown) { 
                                /* implementation goes here */ 
                                console.log(jqXHR.responseText);
                            }

                        });

                        promises.push(request);
//                    console.log(datas);
                    });
                
                    $.when.apply(null, promises).done(function()
                    {
                        toastOverlay.close();

                        Toast.fire({
                            type: 'success',
                            title: "Semua proses telah diselesaikan."
                        });
                    });
                }
                
//                console.log(selectedId);
            });
            
            $('#cmdTambah').on('click',function(e)
            {
//                dTableKar.ajax.reload();
                e.preventDefault();
                let frm = document.getElementById('frmTransStatus');
                let datas = new FormData(frm);
//                console.log($('#form_data_upload').attr('action'));
                $.ajax(
                {
                    url         : $('#frmTransStatus').attr('action'),
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
                            title: 'Sedang memproses data'
                        });
                    },
                    success(result,status,xhr)
                    {
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
                    }
                });
            });
            
            $('#selChk').on('click', function(e)
            {
                // Get all rows with search applied
                var rows = dTableKar.rows({ 'search': 'applied' }).nodes();
                
                if($(this).prop('checked'))
                {
                    // Check/uncheck checkboxes for all rows in the table
                    $('input[type="checkbox"]', rows).prop('checked', this.checked);
                }
                else
                {                    
                    // Check/uncheck checkboxes for all rows in the table
                    $('input[type="checkbox"]', rows).prop('checked', false);
                }
            });
            
            dTableKar = $('#dTableKar').DataTable({
                "sPaginationType": "full_numbers",
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": true,
                "serverSide": true,
                "autoWidth": true,
                "select": true,
                "scrollY": 600,
                "lengthMenu": [100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dttstatuskaryawan') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.sTanggal   = $('#sTanggal').val();
                    }
                },        
                select: 
                {
                    style:    'os',
                    selector: 'td:first-child'
                },
                "columnDefs":[{
                    targets: 0,
                    searchable: false,
                    orderable: false,
                    render: function (data, type, full, meta){
                        return '<input type="checkbox" name="id[]" value="'
                                   + $('<div/>').text(data).html() + '">';
                    }
                },
                {
                        targets : 'tpin',
                        data: "pin"
                },
                {
                        targets : 'tnik',
                        data: "nik"
                },
                {
                        targets : 'tnama',
                        data: "nama"
                },
                {
                        targets : 'tjabatan',
                        data: function(data)
                        {
                            if(data.jabatan)
                            {
                                return data.jabatan.kode+" - "+data.jabatan.deskripsi;
                            }
                            else
                            {
                                return '';
                            }
                        }
                },
                {
                        targets : 'tdivisi',
                        data: function(data)
                        {
                            if(data.divisi)
                            {
                                return data.divisi.kode+" - "+data.divisi.deskripsi;
                            }
                            else
                            {
                                return '';
                            }
                        }
                },
                {
                        targets : 'ttanggal',
                        data: "active_status_date"
                },
                {
                        targets : 'tketerangan',
                        data: "active_comment"
                }],
                "drawCallback": function( settings, json ) 
                {
                    $('.btnSet').on('click',function(e)
                    {
                        if(confirm('Apakah anda yakin menghapus alasan ini?'))
                        {
                            let _this	= $(this);
                            let datas = dTableKar.row(_this.parents('tr')).data();

                            $.ajax(
                            {
                                url         : '{{route("delalasankaryawan")}}',
                                dataType    : 'JSON',
                                type        : 'POST',
                                data        : {sTanggal : datas.alasan[0].pivot.tanggal, sKar : datas.id} ,
                                beforeSend  : function(xhr)
                                {
            //                        $('#loadingDialog').modal('show');
                                    toastOverlay.fire({
                                        type: 'warning',
                                        title: 'Sedang memproses data'
                                    });
                                },
                                success(result,status,xhr)
                                {
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
                                }
                            });
                        }
                    });
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
            

            $('#sKeterangan').select2({
                // placeholder: 'Silakan Pilih',
                minimumInputLength: 0,
                allowClear: true,
                delay: 250,
                placeholder: {
                    id: "",
                    placeholder: ""
                },
                ajax: {
                    url: "{{route('selketeranganstatus')}}",
                    dataType    : 'json',
                    type : 'post',
                    data: function (params) 
                    {
                        let query = {
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
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Status Karyawan</h4>
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
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        {{Form::open(['route' => ['savestatuskaryawan', 'tambah'],'class'=>'form-data', 'id' => 'frmTransStatus'])}}
                            <div class="row">
                                <div class="col-2">
                                    <div class="form-group">
                                        {{ Form::label('sTanggal', 'Tanggal') }}
                                        <div class="input-group" data-target-input="nearest">
                                            {{ Form::text('sTanggal', null, ['id' => 'sTanggal', 'class' => 'form-control form-control-sm', 'placeholder' => 'Tanggal Alasan']) }}
                                            <div class="input-group-append" data-target="#tanggal_masuk">
                                                <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">                                        
                                        {{ Form::label('sKar', 'Karyawan') }}
                                        {{ Form::select('sKar', [], null, ['id' => 'sKar', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        {{ Form::label('sKeterangan', 'Keterangan') }}
                                        {{ Form::select('sKeterangan', [], null, ['id' => 'sKeterangan', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    </div>
                                </div>
                                <div class="col-1">
                                    <div class="btn-group">
                                        <button class="btn btn-warning btn-xs" id="cmdTambah"><i class="fa fa-user-slash"></i>Ubah Status</button>
                                        
                                        <button id="btnKembali" class="btn btn-success btn-xs"><i class="fa fa-eraser"></i>Aktifkan Karyawan</button>
                                        <button id="btnNonAktif" class="btn btn-danger btn-xs"><i class="fa fa-user-slash"></i>Non Aktifkan</button>
                                    </div>
                                </div>
<!--                                <div class="col-1">
                                    <div class="form-group">
                                        <button class="btn btn-xs btn-warning" alt="Upload" data-toggle="modal" data-target="#modal-form-upload" type="button"><i class="fa fa-upload"></i><br>Upload</button>
                                    </div>
                                </div>-->
                            </div>
                        </form>
                    </div>
                    <div class="card-body"> 
                        <table id="dTableKar" class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="tact"><input type="checkbox" id="selChk"></th>
                                    <th class="tpin">PIN</th>
                                    <th class="tnik">NIK</th>
                                    <th class="tnama">Nama</th>
                                    <th class="tdivisi">Divisi</th>
                                    <th class="tjabatan">Jabatan</th>
                                    <th class="ttanggal">Tanggal</th>
                                    <th class="tketerangan">Keterangan</th>
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