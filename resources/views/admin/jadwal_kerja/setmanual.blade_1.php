@extends('adminlte3.app')

@section('title_page')
<p>Set Jadwal Manual</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Set Jadwal Manual</li>
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
    <script src="{{asset('bower_components/admin-lte/plugins/fullcalendar/locales/id.js')}}"></script>
    <script src="{{asset('js/json2.js')}}"></script>
    <script src="{{asset('js/jsonSerialize.js')}}"></script>
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
            
            /* initialize the calendar
            -----------------------------------------------------------------*/
            let Calendar = FullCalendar.Calendar;
//            let Draggable = FullCalendarInteraction.Draggable;

            let calendarEl = document.getElementById('calendar');

            let calendar = new Calendar(calendarEl, {
            plugins: [ 'bootstrap', 'interaction', 'dayGrid', 'timeGrid' ],
            themeSystem: 'bootstrap',
            locale: 'id',
            'firstDay': 1,
            showNonCurrentDates: false,
            header    : {
                left  : 'prev,next today',
                center: 'title',
                right : 'dayGridMonth',
                // right : 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            dateClick: function(info) {
                var karId = $('#setId').val();
                var jamId = $('#jm_kerja').val();
                var tgl = info.dateStr;
                
                $.ajax({
                    url         : '{{route("savejadwalmanual")}}',
                    type        : 'POST',
                    dataType    : 'json',
                    data        : {karId : karId, jamId : jamId, tgl : tgl},
                    beforeSend  : function(xhr)
                    {
//                        $('#loadingDialog').modal('show');
                        toastOverlay.fire({
                            type: 'warning',
                            title: 'Sedang memproses data'
                        });
                    },
                    success     : function(result,status,xhr)
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
                            else
                            {
                                Toast.fire({
                                    type: 'error',
                                    title: result.msg
                                });
                            }
                            
                        }
                    }
                        
                });
                calendar.getEvents().forEach(function(data, index)
                {
                    data.remove();
                });
                loadManual(calendar);
            },
            editable  : true,
            selectable: true
            });

            calendar.render();
            
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
            
            $('#sKarCar').on('click',function(e)
            {
                dTableKar.ajax.reload();
            });
            
            $('#setRefresh').on('click', function(e)
            {
                $('#setId').val('');
                $('#setPin').val('');
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
                            }
                            
                        }
                        dTableKar.ajax.reload();
                    }
                });
            });
            
            
            
            $('#sPerusahaan').select2({
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
//                "autoWidth": false,
                "lengthMenu": [100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtkaryawan') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.sNama   = $('#sKarPin').val();
                        d.sDivisi      = $('#sKarDiv').val();
                        d.sPerusahaan      = $('#sPerusahaan').val();
                    }
                },        
                select: 
                {
                    style:    'os',
                    selector: 'td:first-child'
                },
                "columnDefs"    :[{
                    targets: 0,
                    searchable: false,
                    orderable: false,
                    render: function (data, type, full, meta){
//                        console.log(full.id);
                        return '<button class="btn btn-warning btn-xs btnSet" value="#"><i class="fa fa-calendar"></i></button>';
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
                        targets : 'tjadwal',
                        data: function(data)
                        {
                            return data.jadwal.tipe+" - "+data.jadwal.kode;
                        }
                }],
                "drawCallback": function( settings, json ) 
                {
                    $('.btnSet').on('click',function(e)
                    {
                        let _this	= $(this);
                        let datas = dTableKar.row(_this.parents('tr')).data();
                        
                        $('#setId').val(datas.id);
                        $('#setPin').val(datas.pin);
                        
                        calendar.getEvents().forEach(function(data, index)
                        {
                            data.remove();
                        });
                        loadManual(calendar);
                        
                    });
                }
            });
            
            $('#sKarDiv').select2({
                // placeholder: 'Silakan Pilih',
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('seldivisi')}}",
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
                allowClear: true,
                delay: 250,
                placeholder: {
                    id: "",
                    placeholder: ""
                },
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
            
            $('#sKarJab').select2({
                // placeholder: 'Silakan Pilih',
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('seljabatan')}}",
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
        
        let loadManual = function(calendar)
        {
            var id =  $('#setId').val();
            if(id != "")
            {
                $.ajax({
                    url         : '{{route("fcjadwalmanual")}}',
                    type        : 'POST',
                    dataType    : 'json',
                    data        : {id : id},
                    success     : function(result,status,xhr)
                    {
                        if(result!="")
                        {
                            result.forEach(function(itm, idx)
                            {
                                updateDataObject(itm);
                                calendar.addEvent(itm);
                            });
                        }
                        calendar.render();
                    }

                });
            }

            calendar.render();
        }
        
        
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

        let getDataSelector = function(id, callback)
        {
            $.ajax({
                type: 'post',
                url: "{{route('seljamkerja')}}",
                data:{id:id},
                dataType: 'json',
                success: function(data)
                {
                    callback(data);
                }
            });
        }

        let updateDataObject = function(data)
        {
            let ada = false;
            let dtX = {id:data.id,date:data.start};
            let idxDel = null;
            
            objJadwal.forEach(function(el, idx)
            {
                
                if(el.date === data.start)
                {
                    if(data.title == "")
                    {
                        idxDel = idx;
                    }
                    else
                    {
                        objJadwal[idx] = dtX;
                        ada = true;
                    }
                }
            });

            if(!ada)
            {
                objJadwal.push(dtX);
            }
            
            if(idxDel != null)
            {
//                console.log(idxDel);
                objJadwal.splice(idxDel,1);
                objJadwal.pop();
            }
        }

        let strSel = function(par)
        {
            return '<span class="badge" style="background-color:'+par.warna+'">'+par.kode+'</span>&nbsp;<span class="badge bg-success">'+par.jam_masuk+' - '+par.jam_keluar+'</span>';
        }
    </script>
@endsection



@section('modal_form')
<div class="modal fade" id="modal-form-upload">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Jadwal Manual Karyawan</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<!--        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >-->
            {{ Form::open(['route' => ['uploadjadwalmanualkaryawan'], 'id' => 'form_data_upload', 'files' => true]) }}
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
                            <a class="btn btn-info btn-xs" href="{{route('app.files', 'file_temp_karyawan_jadwal_manual')}}" target="_blank"><i class="fa fa-download"></i>Template Document</a>
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
    <div class="col-6">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <form>
                            <div class="row">
                                <div class="col-2">
                                    <div class="form-group">
                                        {{ Form::label('sKarPin', 'PIN/NIK') }}
                                        {{ Form::text('sKarPin', null, ['id' => 'sKarPin', 'class' => 'form-control form-control-sm', 'placeholder' => 'PIN/NIK']) }}
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        {{ Form::label('sKarDiv', 'Divisi') }}
                                        {{ Form::select('sKarDiv', [], null, ['id' => 'sKarDiv', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}

                                    </div>
                                </div>
                                @if(Auth::user()->type->nama != 'REKANAN')
                                <div class="col-4">
                                    <div class="form-group">
                                        {{ Form::label('sPerusahaan', 'Perusahaan') }}
                                        {{ Form::select('sPerusahaan', [], null, ['id' => 'sPerusahaan', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    </div>
                                </div>
                                @endif
                                <div class="col-2">
                                    <div class="btn-group">
                                        {{ Form::button('<i class="fa fa-search"></i>Cari',['id' => 'sKarCar', 'class' => 'btn btn-success btn-sm']) }}
                                        {{ Form::button('<i class="fa fa-upload"></i>Upload',['id' => 'sKarUpload', 'class' => 'btn btn-primary btn-sm', 'alt'=>'Upload', 'data-toggle' => 'modal', 'data-target' => '#modal-form-upload']) }}
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
                                    <th class="tdivisi">Divisi</th>
                                    <th class="tjabatan">Jabatan</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>  
    <div class="col-6">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-body"> 
                        <div class="row">
                            <div class="col-3">PIN</div>
                            <div class="col-3">
                                <input type="hidden" id="setId">
                                <div class="form-inline">
                                    <div class="form-group">
                                        <input type="text" id="setPin" class="form-control form-control-sm" readonly="readonly">
                                        <button class="btn btn-default btn-sm" id="setRefresh">Refresh</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="kode">Jam Kerja</label>
                            <select class="form-control select2" style="width: 100%;" id="jm_kerja">

                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-body bg-gray"> 
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection