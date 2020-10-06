@extends('adminlte3.app')

@section('title_page')
<p>Transaksi Status Karyawan Off</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Transaksi Status Karyawan Off</li>
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
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            bsCustomFileInput.init();
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
                        url         : '{{route("savestatusoffkaryawan", "kembali")}}',
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
                    "url"       : "{{ route('dttstatusoffkaryawan') }}",
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
                    "targets": 0,
                    "className":      'details-control',
                    "orderable":      false,
                    "data"     :           null,
                    "defaultContent": ''
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
                        data: function(data)
                        {
                            if(data.log_off.length > 0)
                                return data.log_off[0].pivot.tanggal;
                            else
                                return '';
                        }
                },
                {
                        targets : 'talasan',
                        data: function(data)
                        {
                            if(data.log_off.length > 0)
                                return '<span class="badge badge-warning">'+data.log_off[0].kode+' - '+data.log_off[0].deskripsi+'</span>';
                            else
                                return '';
                        }
                },
                {
                        targets : 'tketerangan',
                        data: "off_comment"
                }]
            });
            $('#dTableKar tbody').on('click', 'td.details-control', function () 
            {
                var tr = $(this).closest('tr');
                var row = dTableKar.row( tr );

                if ( row.child.isShown() ) 
                {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else 
                {
                    // Open this row
                    var dts = row.data();
                    row.child(detFormat(dts)).show();
                    tr.addClass('shown');
                }
                
                $('.btnSet').on('click',function(e)
                    {
                        if(confirm('Apakah anda yakin menghapus jadwal ini?'))
                        {
                            let datas = $(this).closest("tr").find('input[type=hidden]').val();
                            
                            datas = JSON.parse(datas);
                            
                            $.ajax(
                            {
                                url         : '{{route("deloffkaryawan")}}',
                                dataType    : 'JSON',
                                type        : 'POST',
                                data        : {sKar: datas.karyawanId, sTanggal: datas.tanggal},
                                beforeSend  : function(xhr)
                                {
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
            } );
            $('#sKar').select2({
                // placeholder: 'Silakan Pilih',
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('selkaryawanoff')}}",
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
                placeholder: '',
                minimumInputLength: 0,
                allowClear: true,
            });
        });
        
        
        var detFormat = function(dt)
        {
            if(dt.log_off.length > 0)
            {
                var ret = '<table cellpadding="5" cellspacing="0" border="0" style="table"><thead><tr><th>&nbsp;</th><th>Tanggal</th><th>Alasan</th><th>Keterangan</th></tr>';
            
                dt.log_off.forEach(function(i,x)
                {
                    var val = JSON.stringify({karyawanId : i.pivot.karyawan_id, tanggal : i.pivot.tanggal});
                    
                    ret += '<tr><input class="jdId" type="hidden" value=\''+val+'\'>'+
                                '<td><button class="btn btn-danger btn-xs btnSet"><i class="fa fa-eraser"></i></button></td>'+
                                '<td>'+i.pivot.tanggal+'</td>'+
                                '<td>'+i.kode+' - '+i.deskripsi+'</td>'+
                                '<td>'+((i.pivot.keterangan)?i.pivot.keterangan:'&nbsp;')+'</td>'+
                            '</tr>';
                });

                ret += '</table>';
                return ret;
            }
        };
    </script>
@endsection

@section('modal_form')
<div class="modal fade" id="modal-form-upload">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Status Karyawan Off</h4>
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
                            <a class="btn btn-info btn-xs" href="{{route('app.files', 'file_temp_set_off')}}" target="_blank"><i class="fa fa-download"></i>Template Document</a>
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
                        {{Form::open(['route' => ['savestatusoffkaryawan', 'tambah'],'class'=>'form-data', 'id' => 'frmTransStatus'])}}
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
                                <div class="col-2">
                                    <div class="form-group">
                                        {{ Form::label('sAlasan', 'Alasan') }}
                                        @php
                                        $alasan = null;
                                        
                                        $mo = \App\Alasan::where('show','N')->get();
                                        
                                        foreach($mo as $moV)
                                        {
                                            $alasan[$moV->id ] = $moV->kode.' - '.$moV->deskripsi;
                                        }
                                        
                                        @endphp
                                        {{ Form::select('sAlasan', $alasan, null, ['id' => 'sAlasan', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        {{ Form::label('sKeterangan', 'Keterangan') }}
                                        {{ Form::text('sKeterangan', null, ['id' => 'sKeterangan', 'class' => 'form-control form-control-sm', 'placeholder' => 'Keterangan']) }}
                                         
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="btn-group">
                                        <button class="btn btn-success btn-xs" id="cmdTambah"><i class="fa fa-user-slash"></i> Simpan</button>     
                                        <button class="btn btn-xs btn-warning" alt="Upload" data-toggle="modal" data-target="#modal-form-upload" type="button"><i class="fa fa-upload"></i><br>Upload</button>
                                        <!--<button id="btnKembali" class="btn btn-success btn-xs"><i class="fa fa-eraser"></i>Aktifkan Karyawan</button>-->
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
                                    <th class="talasan">Alasan</th>
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