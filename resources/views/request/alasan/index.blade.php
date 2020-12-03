@extends('adminlte3.app')

@section('title_page')
    <p>Request Alasan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Request Alasan</li>
@endsection

@section('add_css')
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/bootstrap/dataTables.bootstrap4.min.css')}}">
    <!-- Datatables -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/select2/css/select2.min.css')}}">
@endsection

@section('add_js')
    <!-- Datatables -->
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
    <!-- select2 -->
    <script src="{{asset('bower_components/admin-lte/plugins/select2/js/select2.full.min.js')}}"></script>
    <!-- fullCalendar 2.2.5 -->
    <script src="{{asset('js/json2.js')}}"></script>
    <script src="{{asset('js/jsonSerialize.js')}}"></script>
    
    <script src="{{asset('js/myjs.js')}}"></script>
    
    <script>
        let dTable = null;
        let tblKeluarga = null;
        let objJadwal = [];
        $(function(e)
        {
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            /* initialize the calendar
            -----------------------------------------------------------------*/
            
            $('#cmdSearch').on('click',function(e)
            {
                dTable.ajax.reload();
            });
            
            $('#sStatus').select2({
                minimumInputLength: 0,
                placeholder: "",
                allowClear: true
            });
            
            $('#sTanggal').daterangepicker({
                singleDatePicker:true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD'
                }
            }); 
            
            $('#cmdUpload').on('click', function(e)
            {
                let frm = document.getElementById('form_data_upload');
                let datas = new FormData(frm);
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
                        callToastOverlay('warning', 'Sedang memproses data upload');
                    },
                    success(result,status,xhr)
                    {
                        toastOverlay.close();
                        if(result.status == 1)
                        {
                            callToast('success', result.msg);
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
                                callToast('error', str);
                            }
                            
                        }
                        dTable.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        toastOverlay.close();
                        console.log(jqXHR.responseText);
                    }
                });
            });
            
            $('#btnCari').on('click', function(e)
            {
                e.preventDefault();
                
                dTable.ajax.reload();
            });
            
            dTable = $('#dTable').DataTable({
                "sPaginationType": "full_numbers",
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": true,
                "serverSide": true,
                "autoWidth": true,
                "lengthMenu": [100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtrequestalasan') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.sDokumen     = $('#sDokumen').val();
                        d.sTanggal     = $('#sTanggal').val();
                        d.sStatus     = $('#sStatus').val();
                    }
                },
                "columnDefs"    :[
                {
                    "targets": 0,
//                    "className":      'btndel',
                    "orderable":      false,
                    "data"     :           'action',
                },
                {
                    targets : 'tdokumen',
                    data : function(data)
                    {
                        if(data.file_dokumen)
                        {
                            return '<a target="_blank" href="'+data.link_file+'">'+data.file_dokumen+'</a>';
                        }
                        else
                        {
                            return '';
                        }
                    }
                },
                {
                    targets : 'tnodokumen',
                    data : 'no_dokumen'
                },
                {
                    targets : 'ttanggal',
                    data : 'tanggal'
                },
                {
                    targets : 'tstatus',
                    data: function(datas)
                    {
                        var band = null;
                        if(datas.status == 'new')
                            band = 'primary';
                        else if(datas.status == 'send')
                            band = 'secondary';
                        else if(datas.status == 'approve')
                            band = 'success';
                        else if(datas.status == 'decline')
                            band = 'danger';
                        else if(datas.status == 'return')
                            band = 'warning';
                        
                        return '<span class="badge badge-'+band+'">'+datas.status.charAt(0).toUpperCase() + datas.status.slice(1)+'</span>'
                    }
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
                    targets : 'tapprove',
                    data: function(data)
                    {
                        if(data.approve)
                        {
                            return 'Oleh : '+data.approve.name+'<br/>Waktu : '+data.approved_at;
                        }
                        else
                        {
                            return '';
                        }
                    }
                },
                {
                    targets : 'tdecline',
                    data: function(data)
                    {
                        if(data.decline)
                        {
                            return 'Oleh : '+data.decline.name+'<br/>Waktu : '+data.declined_at+'<br/>Alasan : '+data.declined_note;
                        }
                        else
                        {
                            return '';
                        }
                    }
                },
                {
                    targets : 'ttdetail',
                    data: "totdetail"
                },
                {
                    targets : 'ttapprove',
                    data: "totapprove"
                },
                {
                    targets : 'ttdecline',
                    data: "totdecline"
                },
                {
                    targets : 'tcatatan',
                    data: "catatan"
                }        

                ]
            });     
            
            $('#dTable tbody').on('click', '.btnedit', function (e)
            {
                e.preventDefault();
                var tr = $(this).closest('tr');
                var row = dTable.row(tr);
                var datas = row.data();

                window.open(datas.link_edit,'_self');
            });     
            
            @if(Auth::user()->type->nama == 'ADMIN' || Auth::user()->type->nama == 'PAYROLL' || Auth::user()->type->nama == 'HRD')
             
            $('#dTable tbody').on('click', '.btnapprove', function (e)
            {
                e.preventDefault();
                var tr = $(this).closest('tr');
                var row = dTable.row(tr);
                var datas = row.data();

                window.open(datas.link_approve,'_self');
            });     
            @endif
            
            $('#dTable tbody').on('click', '.btnshow', function (e)
            {
                e.preventDefault();
                var tr = $(this).closest('tr');
                var row = dTable.row(tr);
                var datas = row.data();

                window.open(datas.link_show,'_self');
            });
            
            $('#dTable tbody').on('click', '.btndel', function (e)
            {
                e.preventDefault();
                var tr = $(this).closest('tr');
                var row = dTable.row(tr);
                var datas = row.data();

                if (confirm('Apakah Anda yakin menghapus data ini?'))
                {
                    $.ajax(
                    {
                        url: '{{route("deleterequestalasan")}}',
                        dataType: 'JSON',
                        type: 'POST',
                        data: {id: datas.id},
                        beforeSend: function (xhr)
                        {
                            callToastOverlay('warning', 'Sedang memproses hapus data');
                        },
                        success(result, status, xhr)
                        {
                            toastOverlay.close();
                            if (result.status == 1)
                            {
                                callToast('success', result.msg);
                            } else
                            {
                                if (Array.isArray(result.msg))
                                {
                                    var str = "";
                                    for (var i = 0; i < result.msg.length; i++)
                                    {
                                        str += result.msg[i] + "<br>";
                                    }
                                    callToast('error', str);
                                }

                            }
                            dTable.ajax.reload();
                        }
                    });

                    return false;
                }
            });
            
            $('#dTable tbody').on('click', '.btnsend', function (e)
            {
                e.preventDefault();
                var tr = $(this).closest('tr');
                var row = dTable.row(tr);
                var datas = row.data();

                if (confirm('Apakah Anda yakin mengirim data ini?'))
                {
                    $.ajax(
                    {
                        url: '{{route("sendrequestalasan")}}',
                        dataType: 'JSON',
                        type: 'POST',
                        data: {id: datas.id},
                        beforeSend: function (xhr)
                        {
                            callToastOverlay('warning', 'Sedang memproses kirim data');
                        },
                        success(result, status, xhr)
                        {
                            toastOverlay.close();
                            if (result.status == 1)
                            {
                                callToast('success', result.msg);
                            } else
                            {
                                if (Array.isArray(result.msg))
                                {
                                    var str = "";
                                    for (var i = 0; i < result.msg.length; i++)
                                    {
                                        str += result.msg[i] + "<br>";
                                    }
                                    callToast('error', str);
                                }

                            }
                            dTable.ajax.reload();
                        }
                    });

                    return false;
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
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Request</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<!--        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >-->
            {{ Form::open(['route' => ['uploadkaryawan'], 'id' => 'form_data_upload', 'files' => true]) }}
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
                            <a class="btn btn-info btn-xs" href="{{route('app.files', 'file_temp_karyawan')}}" target="_blank"><i class="fa fa-download"></i>Template Document</a>
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
                        <div class="row">
                            <div class="col-2">
                                <div class="form-group">                                        
                                    {{ Form::label('sDokumen', 'No Dokumen') }}
                                    {{ Form::text('sDokumen', null, ['id' => 'sDokumen', 'class' => 'form-control form-control-sm']) }}
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">                                        
                                    {{ Form::label('sTanggal', 'Tanggal') }}
                                    {{ Form::text('sTanggal', null, ['id' => 'sTanggal', 'class' => 'form-control form-control-sm']) }}
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">                                        
                                    {{ Form::label('sStatus', 'Status') }}
                                    {{ Form::select('sStatus', [''=>'','new' => 'New', 'send' => 'Send', 'approve' => 'Approve', 'decline' => 'Decline', 'return' => 'Return'], null, ['id' => 'sStatus', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                </div>
                            </div>
                            <div class="col-1">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary" id="btnCari" alt="Search"><i class="fa fa-search"></i>&nbsp;Cari</button>
                                    <!--<button class="btn btn-sm btn-warning" alt="Upload" data-toggle="modal" data-target="#modal-form-upload"><i class="fa fa-upload"></i>&nbsp;Upload</button>-->
                                    <a class="btn btn-success btn-sm" alt="Tambah" id='btnTambah' href="{{route('alasanrequestform')}}"><i class="fa fa-plus-circle"></i>&nbsp;Tambah</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-tools">
                            
                        </div>
                    </div>
                    <div class="card-body">  
                        <table id="dTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="tdokumen">File Dokumen</th>
                                    <th class="tnodokumen">No Dokumen</th>
                                    <th class="ttanggal">Tanggal</th>
                                    <th class="tstatus">Status</th>
                                    <th class="tapprove">Approve</th>
                                    <th class="tdecline">Decline</th>
                                    <th class="ttdetail">Tot. Det</th>
                                    <th class="ttapprove">Tot. App</th>
                                    <th class="ttdecline">Tot. Dec</th>
                                    <th class="tcatatan">Catatan</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection