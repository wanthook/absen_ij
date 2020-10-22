@extends('adminlte3.app')

@section('title_page')
<p>Transaksi Alasan Karyawan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Transaksi Alasan Karyawan</li>
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
        let dTableKarJad = null;
        let objJadwal = [];
        
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
            
            $('#sTanggal, #sTanggalAkhir').daterangepicker({
                singleDatePicker:true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            });
            
            $('#sTanggal, #sTanggalAkhir').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
            
            $('#sTanggal').on('change', function(e)
            {
                dTableKar.ajax.reload();
            });    
            
            $('#perusahaan').on('select2:select', function(e)
            {
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
                let frm = document.getElementById('frmTransAlasan');
                let datas = new FormData(frm);
                
                $.ajax(
                {
                    url         : $('#frmTransAlasan').attr('action'),
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
                            title: 'Sedang memproses data',
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
                            
                            $('#sAlasanOld').val(null);
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
                        console.log(jqXHR.responseText);
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
                "lengthMenu": [500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dttalasankaryawan') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.sTanggal   = $('#sTanggal').val();
                        d.perusahaan   = $('#perusahaan').val();
                    }
                },        
                select: 
                {
                    style:    'os',
                    selector: 'td:first-child'
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
                    targets : 'ttanggalawal',
                    data : 'tanggal_awal'
                },
                {
                    targets : 'ttanggalakhir',
                    data : 'tanggal_akhir'
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
                        targets : 'talasan',
                        data: function(data)
                        {
                            return data.alasan_kode+" - "+data.alasan_deskripsi;
                        }
                },
                {
                        targets : 'talasanwaktu',
                        data: function(data)
                        {
                            return ((data.waktu)?data.waktu:'');
                        }
                },
                {
                        targets : 'tdivisi',
                        data: function(data)
                        {
                            return data.divisi_kode+" - "+data.divisi_deskripsi;
                        }
                }
                ],
                "drawCallback": function( settings, json ) 
                {
                    $('.btnSet').on('click',function(e)
                    {
                        if(confirm('Apakah anda yakin menghapus alasan ini?'))
                        {
                            let _this	= $(this);
                            let datas = dTableKar.row(_this.parents('tr')).data();
                            console.log(_this.val());
                        }
                    });
                }
            });
            
            $('#dTableKar tbody').on('click', '.btndel', function () 
            {
                var tr = $(this).closest('tr');
                var row = dTableKar.row( tr );
                var datas = row.data();
                
                if(confirm('Apakah Anda yakin menghapus data ini?'))
                {
                    $.ajax(
                    {
                        url         : '{{route("delalasankaryawan")}}',
                        dataType    : 'JSON',
                        type        : 'POST',
                        data        : {sTanggal : datas.tanggal, sKar : datas.karyawan_id, sAlasan: datas.alasan_id} ,
                        beforeSend  : function(xhr)
                        {
    //                        $('#loadingDialog').modal('show');
                            toastOverlay.fire({
                                type: 'warning',
                                title: 'Sedang memproses hapus data',
                                onBeforeOpen: () => {
                                    Swal.showLoading();
                                }
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

                    return false;
                }
            });
            
            $('#dTableKar tbody').on('click', '.btnedit', function () 
            {
                var tr = $(this).closest('tr');
                var row = dTableKar.row( tr );
                var datas = row.data();
                
                $('#sTanggal').val(datas.tanggal_awal);
                $('#sTanggalAkhir').val(datas.tanggal_akhir);
                
                var newOption = new Option(datas.pin+' - '+datas.nama, datas.karyawan_id, true, true);
                $('#sKar').append(newOption).trigger('change'); 
                
                $('#sAlasanOld').val(datas.alasan_id);
                var newOption = new Option(datas.alasan_kode+' - '+datas.alasan_deskripsi, datas.alasan_id, true, true);
                $('#sAlasan').append(newOption).trigger('change'); 
                
                $('#sWaktu').val(datas.waktu);
                $('#sKeterangan').val(datas.keterangan);
//                $('#sId').val(datas.id);
//                
//                console.log(datas);
            });
            
            
            $('#perusahaan').select2({
                // placeholder: 'Silakan Pilih',
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('selperusahaan')}}",
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
            

            $('#sAlasan').select2({
                // placeholder: 'Silakan Pilih',
                minimumInputLength: 0,
                allowClear: true,
                delay: 250,
                placeholder: {
                    id: "",
                    placeholder: ""
                },
                ajax: {
                    url: "{{route('selalasan')}}",
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
                },
                templateResult: function(par)
                {
                    return par.name || $(strSel(par));
                },
                templateSelection: function(par)
                {
                    if(par.text == "")
                    {
                        return par.name || $(strSel(par));
                    }
                    return par.name || par.text;
                }
            });
        });

        var strSel = function(par)
        {
            return '<span class="badge" style="background-color:'+par.warna+'">'+par.kode+' - '+par.deskripsi+'</span>';
        }
        
        var detFormat = function(dt)
        {
            var ret = '<table cellpadding="5" cellspacing="0" border="0" style="table"><thead><tr><th>&nbsp;</th><th>Kode Alasan</th><th>Nama Alasan</th><th>Waktu Alasan</th><th>Keterangan</th></tr>';
            
            dt.alasan.forEach(function(i,x)
            {
                if(i.pivot.tanggal == $('#sTanggal').val())
                {
                    ret += '<tr>'+
                                '<td><button class="btn btn-danger btn-xs btnSet" value="'+i.id+'"><i class="fa fa-eraser"></i></button></td>'+
                                '<td>'+i.kode+'</td>'+
                                '<td>'+i.deskripsi+'</td>'+
                                '<td>'+((i.pivot.waktu)?i.pivot.waktu:'&nbsp;')+'</td>'+
                                '<td>'+((i.pivot.keterangan)?i.pivot.keterangan:'&nbsp;')+'</td>'+
                            '</tr>';
                }
                
            });
            
            ret += '</table>';
            return ret;
        };
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
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        {{Form::open(['url' => route('savealasankaryawan'),'class'=>'form-data', 'id' => 'frmTransAlasan'])}}
                        {{Form::hidden('sAlasanOld', null, ['id' => 'sAlasanOld'])}}
                            <div class="row">
                                <div class="col-3">
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
                                        {{ Form::label('sTanggalAkhir', 'Tanggal Akhir') }}
                                        <div class="input-group" data-target-input="nearest">
                                            {{ Form::text('sTanggalAkhir',null, ['id' => 'sTanggalAkhir', 'class' => 'form-control form-control-sm', 'placeholder' => 'Tanggal Alasan']) }}
                                            <div class="input-group-append" data-target="#tanggal_masuk">
                                                <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(Auth::user()->type->nama != 'REKANAN')
                                <div class="col-3">
                                    <div class="form-group">
                                        {{ Form::label('perusahaan', 'Perusahaan') }}
                                        {{ Form::select('perusahaan', [], null, ['id' => 'perusahaan', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    </div>
                                </div>
                                @endif
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
                                <div class="col-3">
                                    <div class="form-group">
                                        {{ Form::label('sAlasan', 'Alasan') }}
                                        {{ Form::select('sAlasan', [], null, ['id' => 'sAlasan', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        {{ Form::label('sWaktu', 'Waktu') }}
                                        {{ Form::text('sWaktu',  null, ['id' => 'sWaktu', 'class' => 'form-control form-control-sm', 'style'=> 'width: 100%;']) }}
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
                                    <th class="ttanggalawal">Tanggal Awal</th>
                                    <th class="ttanggalakhir">Tanggal Akhir</th>
                                    <th class="tpin">PIN</th>
                                    <!--<th class="tnik">NIK</th>-->
                                    <th class="tnama">Nama</th>
                                    <th class="tdivisi">Divisi</th>
                                    <th class="talasan">Alasan</th>
                                    <th class="talasanwaktu">Waktu</th>
<!--                                    <th class="talasan">Alasan</th>
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