@extends('adminlte3.app')

@section('title_page')
    <p>Tarik Absen</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Tarik Absen</li>
@endsection

@section('add_css')
    <!-- Datatables -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables.net-select-bs4/css/select.bootstrap4.min.css')}}">
    <!-- bootstrap color picker -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/select2/css/select2.min.css')}}">
    <!-- fullCalendar -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/fullcalendar/main.min.css')}}">
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/fullcalendar-interaction/main.min.css')}}">
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/fullcalendar-daygrid/main.min.css')}}">
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/fullcalendar-timegrid/main.min.css')}}">
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/fullcalendar-bootstrap/main.min.css')}}">
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
    <!-- fullCalendar 2.2.5 -->
    <script src="{{asset('bower_components/admin-lte/plugins/moment/moment.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/fullcalendar/main.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/fullcalendar-daygrid/main.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/fullcalendar-timegrid/main.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/fullcalendar-interaction/main.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/fullcalendar-bootstrap/main.min.js')}}"></script>
    <script src="{{asset('js/json2.js')}}"></script>
    <script src="{{asset('js/jsonSerialize.js')}}"></script>
    
    <script>
        let dTable = null;
        let dTableShow = null;
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
            
            $('#cmdSearch').on('click',function(e)
            {
                dTable.ajax.reload();
            });
            
            $('#sPeriode').daterangepicker({
                singleDatePicker: true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM'
                }
            }).on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM'));
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
                        toastOverlay.fire({
                            type: 'warning',
                            title: 'Sedang memproses data upload'
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
                    }
                });
            });
            
            $('#cmdTarik').on('click', function(e)
            {
                e.preventDefault();
                var selectedId = [];
                dTable.$('input:checked').map(function () 
                {
                    var _this	= $(this);
                    var datas       = dTable.row(_this.parents('tr')).data();
                    
                    selectedId.push(datas.id);
                });
                var per = $('#sPeriode').val();
                $.ajax(
                {
                    url         : '{{route("tarikmesin")}}',
                    dataType    : 'json',
                    type        : 'POST',
                    data        : {id : selectedId, periode:per} ,
                    beforeSend  : function(xhr)
                    {
//                        $('#loadingDialog').modal('show');
                        toastOverlay.fire({
                            type: 'warning',
                            title: 'Sedang memproses tarik mesin <b><b>',
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        toastOverlay.getTitle();
                    },
                    success(result,status,xhr)
                    {
                        toastOverlay.close();
                        if(result.status == 1)
                        {
//                                document.getElementById("form_data").reset(); 

                            Toast.fire({
                                type: 'success',
                                title: result.msg
                            });

                            dTable.ajax.reload();
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
                        toastOverlay.close();
                        console.log(jqXHR.responseText);
                    }

                });
            });
            
            $('#cmdHapus').on('click', function(e)
            {
                e.preventDefault();
                var selectedId = [];
                if(confirm('Apakah anda yakin ingin menghapus log mesin ini?'))
                {
                    dTable.$('input:checked').map(function () 
                    {
                        var _this	= $(this);
                        var datas       = dTable.row(_this.parents('tr')).data();

                        selectedId.push(datas.id);
                    });

                    $.ajax(
                    {
                        url         : '{{route("hapusmesin")}}',
                        dataType    : 'json',
                        type        : 'POST',
                        data        : {id : selectedId} ,
                        beforeSend  : function(xhr)
                        {
    //                        $('#loadingDialog').modal('show');
                            toastOverlay.fire({
                                type: 'warning',
                                title: 'Sedang memproses hapus mesin <b><b>',
                                onBeforeOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            toastOverlay.getTitle();
                        },
                        success(result,status,xhr)
                        {
                            toastOverlay.close();
                            if(result.status == 1)
                            {
    //                                document.getElementById("form_data").reset(); 

                                Toast.fire({
                                    type: 'success',
                                    title: result.msg
                                });

                                dTable.ajax.reload();
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
                            toastOverlay.close();
                            console.log(jqXHR.responseText);
                        }

                    });
                }
            });
            
            $('#selChk').on('click', function(e)
            {
                // Get all rows with search applied
                var rows = dTable.rows({ 'search': 'applied' }).nodes();
                
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
            
            dTable = $('#dTable').DataTable({
                "sPaginationType": "full_numbers",
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": true,
                "serverSide": true,
                "autoWidth": true,
                "select": true,
                "scrollX": true,
                "scrollY": 600,
                "lengthMenu": [100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtmesin') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.search     = $('#txtSearch').val();
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
                        if(full.ping_status != 0)
                        {
                            return '';
                        }
                        else
                        {
                            return '<input type="checkbox" name="id[]" value="'
                                   + $('<div/>').text(data).html() + '">';
                        }
                    }
                },
                { 
                    targets : 'tkode',
                    data: "kode"
                },
                    { 
                        targets : "tlokasi", 
                        data: "lokasi" 
                    },
                    { 
                        targets : "tmerek", 
                        data: "merek" 
                    },
                    { 
                        targets : "tketerangan", 
                        data: "keterangan" 
                    },
                    
                    { 
                        targets : "tip", 
                        data: "ip" 
                    },
                    { 
                        targets : "tstatus",  
                        searchable: false,  
                        orderable: false, 
                        render: function (data, type, full, meta){
                            if(full.ping_status == 0)
                            {
                                return '<small class="text-success mr-1"><i class="fas fa-arrow-up"></i>Connected</small>';
                            }
                            else
                            {
                                return '<small class="text-danger mr-1"><i class="fas fa-arrow-down"></i>Disconnect</small>';
                            }
                        }
                    },
                    { 
                        targets : "tkey", 
                        searchable: false,  
                        orderable: false, 
                        render: function (data, type, full, meta){
                            return '<a href="#" class="logShow" data-toggle="modal" data-target="#modal-log" value="'+full.id+'">'+full.total_log+'</a>';
                        }
                        // data: "total_log" 
                    },
                    { 
                        targets : "tlog", 
                        data: "lastlog" 
                    }],

                "drawCallback": function( settings, json ) 
                {
                    $('.logShow').on('click', function(e)
                    {
                        let _this	= $(this);
                        let datas = dTable.row(_this.parents('tr')).data();
                        $('#showId').val(datas.id);
                        $('#lblLog').html(datas.kode);
                    });
                }
            });
            
            dTableShow = $('#dTableShow').DataTable({
                "sPaginationType": "full_numbers",
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": false,
                "serverSide": true,
                "autoWidth": true,
                "select": true,
                "scrollX": true,
                "scrollY": 400,
                "lengthMenu": [100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtmesinactivity') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.sMesin     = $('#showId').val();
                    }
                },   
                "columnDefs":[{ 
                    targets : 'tshowpin',
                    data: "pin"
                },
                { 
                    targets : "tshowtanggal", 
                    data: "tanggal" 
                },
                { 
                    targets : "tshowverified", 
                    data: "verified" 
                },
                { 
                    targets : "tshowcreated", 
                    data: "created_at" 
                }],
            });

            $('#modal-log').on('show.bs.modal', function (e) 
            {                  
                dTableShow.ajax.reload();
            });
                        
            $('#sMesin').select2({
                // placeholder: 'Silakan Pilih',
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('selmesin')}}",
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

            $('#jm_kerja').select2({
                // placeholder: 'Silakan Pilih',
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('seljamkerja')}}",
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

            let jadwalSelectorTable = function(data, days)
            {
                let jadwalKerja = data.jadwal_kerja;

                for(let i = 0 ; i < jadwalKerja.length ; i++)
                {
                    if(jadwalKerja[i].pivot.day == days)
                    {
                        return '<span class="badge" style="background-color:'+jadwalKerja[i].warna+'">'+jadwalKerja[i].kode+'</span><span class="badge bg-success">'+jadwalKerja[i].jam_masuk+' - '+jadwalKerja[i].jam_keluar+'</span>';
                    }
                }

                return null;
            }

        });
    </script>
@endsection

@section('modal_form')

<div class="modal fade" id="modal-log">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-upload"></i>Log Mesin <span id="lblLog"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="showId" id="showId">
                <table id="dTableShow" class="table table-hover">
                    <thead>
                        <tr>
                            <th class="tshowpin">PIN</th>
                            <th class="tshowtanggal">Tanggal Absen</th>
                            <th class="tshowverified">Verified</th>
                            <th class="tshowcreated">Tanggal Masuk</th>
                        </tr>
                    </thead>
                </table>
            </div>   
        </div>
    </div>
</div>
<div class="modal fade" id="modal-form-upload">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Data Absen</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<!--        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >-->
            {{ Form::open(['route' => ['uploaddataabsen'], 'id' => 'form_data_upload', 'files' => true]) }}
            {{ Form::hidden('id',null, ['id' => 'uploadId']) }}
            <input type="hidden" name="id" id="id">
            <div class="modal-body">   
                <div class="form-group">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">                                        
                                {{ Form::label('sMesin', 'Mesin') }}
                                {{ Form::select('sMesin', [], null, ['id' => 'sMesin', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                            </div>
                        </div>            
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
                        <!-- <div class="col-12">
                            <a class="btn btn-info btn-xs" href="{{route('app.files', 'file_temp_set_divisi')}}" target="_blank"><i class="fa fa-download"></i>Template Document</a>
                        </div> -->
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
<!-- <div class="card bg-gradient-primary collapsed-card">
    <div class="card-header">
        <h5 class="card-title"><i class=" fas fa-search"></i>&nbsp;Pencarian</h5>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <form role="form">
            {{csrf_field()}}
            <div class="form-group">
                <label for="txtSearch">Kode / Deskripsi / IP</label>
                <input type="text" class="form-control" name="txtSearch" id="txtSearch" placeholder="Kode/Deskripsi/IP">                  
            </div>
        </form>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary" id="cmdSearch"><i class=" fas fa-search"></i>&nbsp;Cari</button>
    </div>
</div> -->
<div class="card card-primary card-outline">
   <div class="card-header">
        <div class="row">
            <div class="col-sm-2">
                <div class="form-group">                                        
                    {{ Form::label('sPeriode', 'Periode') }}
                    {{ Form::text('sPeriode', null, ['id' => 'sPeriode', 'class' => 'form-control form-control-sm']) }}
                </div>
            </div>
            <div class="col-sm-4">      
                <div class="btn-group">      
                    <button id="cmdTarik" class="btn btn-sm btn-success" alt="Tarik Absen"><i class="fa fa-download"></i>&nbsp;Tarik Absen</button>
                    @if(Auth::user()->type->nama == 'ADMIN')              
                    <button id="cmdHapus" class="btn btn-sm btn-danger" alt="Hapus Absen"><i class="fa fa-eraser"></i>&nbsp;Hapus Absen</button>
                    <button class="btn btn-sm btn-warning" alt="Upload Absen" data-toggle="modal" data-target="#modal-form-upload" type="button"><i class="fa fa-upload"></i>Upload Absen</button>
                    @endif
                </div>
            </div>
            <div class="col-12">
                
            </div>
        </div>
    </div>
    <!-- /.card-header -->
        <div class="card-body">
            
            <table id="dTable" class="table table-hover">
                <thead>
                    <tr>
                        <th class="tact"><input type="checkbox" id="selChk"></th>
                        <th class="tkode">Kode</th>
                        <th class="tlokasi">Lokasi</th>
                        <th class="tmerek">Merek</th>
                        <th class="tketerangan">Keterangan</th>
                        <th class="tip">IP Mesin</th>
                        <th class="tstatus">Status Mesin</th>
                        <th class="tkey">Total Log</th>
                        <th class="tlog">Log Terakhir</th>
                    </tr>
                </thead>
            </table>
        </div>
    <!-- /.card-body -->
</div>

@endsection