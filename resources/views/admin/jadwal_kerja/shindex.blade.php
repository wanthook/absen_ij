@php
$show = true;
if(config('global.perusahaan_short') == 'AIC')
{
    if(Auth::user()->id != 1 && Auth::user()->id != 9)
    {
        $show = false;
    }
}
else
{
    if(Auth::user()->type->nama != 'ADMIN')
    {
        $show = false;
    }
}
@endphp
@extends('adminlte3.app')

@section('title_page')
    <p>Jadwal Shift</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Jadwal Shift</li>
@endsection

@section('add_css')
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/bootstrap/dataTables.bootstrap4.min.css')}}">
    <!-- Datatables -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
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
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/daterangepicker/daterangepicker.css')}}">
    <style>
        .fc-event-container {
            font-size: 10pt;
            font-family: Verdana, Arial, Sans-Serif;
        }
        .fc-day-number{
            font-size: 10pt;
        }
        .fc-time{
            display : none;
        }
    </style>
@endsection

@section('add_js')
    <!-- Datatables -->
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
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
    
    <script src="{{asset('bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js')}}"></script>
    <script src="{{asset('js/json2.js')}}"></script>
    <script src="{{asset('js/jsonSerialize.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>
    <script>
        let dTable = null;
        let objJadwal = [];
        let sMaster, eMaster, sTarget, eTarget;
        let calendar = null, calendarShow = null;
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
            let calendarElShow = document.getElementById('calendar-show');
            
            let calendarShow = new Calendar(calendarElShow, {
                plugins: [ 'bootstrap', 'interaction', 'dayGrid', 'timeGrid' ],
                themeSystem: 'bootstrap',
                locale: 'id',
                firstDay: 1,
                stickyHeaderDates: true,
                height: '50%',
                showNonCurrentDates: false,
                header    : {
                    left  : 'prev,next today',
                    center: 'title',
                    right : 'dayGridMonth'
                },
                editable  : true,
                selectable: true
            });

            calendar = new Calendar(calendarEl, {
                plugins: [ 'bootstrap', 'interaction', 'dayGrid', 'timeGrid' ],
                themeSystem: 'bootstrap',
                locale: 'id',
                firstDay: 1,
                nextDayThreshold: '00:00:00',
                showNonCurrentDates: false,
                header    : {
                    left  : 'prev,next today',
                    center: 'title',
                    right : 'dayGridMonth'
                },
                dateClick: function(info) {
                    let exists = false;
                    let calEv = new Object();
                    calendar.getEvents().forEach(function(data, index)
                    {
    //                    console.log(data);
                        if(new Date(data.start).toDateString() === new Date(info.dateStr).toDateString())
                        {
                            exists = true;
                            calEv = data;
                        }
                    });

                    let $sel = $('#jm_kerja').val();

                    if($sel)
                    {
                        getDataSelector($sel, function(data)
                        {

                            let itm = data.items[0];
                            let arrEv = {
                                    title: itm.kode+"\n"+itm.jam_masuk+" - "+itm.jam_keluar,
                                    start: info.dateStr,
                                    end: info.dateStr,
                                    color: itm.warna,
                                    id: itm.id
                                    // allDay: true
                                };
                            if( !exists )
                            {
                                // updateDataObject(arrEv);
                                calendar.addEvent(arrEv);
                            }
                            else
                            {
                                // updateDataObject(arrEv);
                                calEv.remove();
                                calendar.addEvent(arrEv);
                            }
                        });
                    }
                    else
                    {
                        // updateDataObject({title:"",start:info.dateStr});
                        calEv.remove();
                    }
                },
                datesRender: function(info){
                    var id = $('#id').val();
                    if(id)
                    {
                        // console.log(info.view.activeStart);
                        console.log(dateString(info.view.activeStart));
                    }
                },
                editable  : true,
                selectable: true
            });

            calendar.render();

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
            
            $('#cmdTambahShow').on('click', function()
            {
                $('.grpCopy').hide();
                $('#id').val(null);
                
            });

            $('#copyDateMaster').daterangepicker(
            {
                timePicker: false,
                autoUpdateInput: false
            },
            function(start, end)
            {
                $('#copyDateMaster').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
                sMaster = start.format('YYYY-MM-DD');
                eMaster = end.format('YYYY-MM-DD');
            });
            
            $('#copyDateTarget').daterangepicker(
            {
                
                singleDatePicker: true,
                showDropdowns: true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD'
                }
            },
            function(start, end)
            {
                $('#copyDateTarget').val(start.format('YYYY-MM-DD'));
                sTarget = start.format('YYYY-MM-DD');
//                eTarget = end.format('YYYY-MM-DD');
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
                        dTable.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        toastOverlay.close();
                        console.log(jqXHR.responseText);
                    }
                });
            });

            $('#form_data').submit( function(e)
            {
                e.preventDefault();
                
                let formData = $(this).serializeFormJSON();
                var obj = {};
                calendar.getEvents().forEach(function(data, index)
                {
                    sDate = moment(data.start);
                    eDate = moment(data.end);
                    
                    if(eDate.format('HH:mm:ss') === '00:00:00')
                    {
                        eDate = eDate.subtract(1, 'days');
                    }
                    obj[index] = {
                        id:data.id,
                        date_start:sDate.format('YYYY-MM-DD'),
                        date_end:eDate.format('YYYY-MM-DD')
                    };
                });

                formData.data = obj;
                // console.table(objJadwal);
                $.ajax(
                {
                    url         : $(this).attr('action'),
                    dataType    : 'json',
                    contentType : 'application/json; charset=utf-8',
                    type        : 'POST',
                    data        :JSON.stringify(formData) ,
                    beforeSend  : function(xhr)
                    {
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
                                let str = "";
                                for(let i = 0 ; i < result.msg.length ; i++ )
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
                        dTable.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        /* implementation goes here */ 
                        toastOverlay.close();
                        console.log(jqXHR.responseText);
                    }
                    
                });
                
                return false;
            });
            
            $('#modal-form').on('hidden.bs.modal', function (e) 
            {
                document.getElementById("form_data").reset(); 
                $('#id').val(null);
                $('#jm_kerja').val('').trigger('change');
                dTable.ajax.reload();
                objJadwal = [];
                calendar.getEvents().forEach(function(data, index)
                {
                    data.remove();
                });
                calendar.render();
            });

            $('#modal-form').on('show.bs.modal', function (e) 
            {
                var date = calendar.getDate();
                console.log(date);
                loadCalendar();
            });
            
            $('#cmdCopy').on('click', function(e)
            {
                e.preventDefault();
                
                $.ajax({
                    url         : '{{route("copyjadwalshift")}}',
                    type        : 'POST',
                    dataType    : 'json',
                    data        : {sMaster : sMaster, eMaster : eMaster, sTarget : sTarget, jadwalId : $('#id').val()},
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
                    success     : function(result,status,xhr)
                    {
                        toastOverlay.close();
                        if(result.status == 1)
                        {
                            Toast.fire({
                                type: 'success',
                                title: result.msg
                            });
                            loadCalendar();
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

            $('#warna').colorpicker();
            
            $('#modal-show').on('show.bs.modal', function (e) 
            {                
                calendarShow.getEvents().forEach(function(data, index)
                {
                    data.remove();
                });
                
                let ids = $('#id').val();
                if(ids != "")
                {
                    $.ajax({
                        url         : '{{route("fcjadwalshift")}}',
                        type        : 'POST',
                        dataType    : 'json',
                        data        : {id : ids},
                        success     : function(result,status,xhr)
                        {
                            if(result!="")
                            {
                                result.forEach(function(itm, idx)
                                {
//                                    updateDataObject(itm);
                                    calendarShow.addEvent(itm);
                                });

//                                successCallback(result);
                            }
                            calendarShow.render();
                        }

                    });
                }
                
                calendarShow.render();
            });
            
            
            
            dTable = $('#dTable').DataTable({
                "sPaginationType": "full_numbers",
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": true,
                "serverSide": true,
                "autoWidth": false,
                "lengthMenu": [10, 50, 100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtjadwalshift') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.search     = $('#txtSearch').val();
                    }
                },
                "columns"           :
                [
                    { data    : "action", orderable: false, searchable: false},
                    { data    : "kode_url", name : "kode_url" },
                    { data    : "deskripsi", name : "deskripsi" },
                    { data    : "created_by.name", name : "created_by" },
                    { data    : "created_at", name : "created_at" },
                    { data    : "updated_at", name : "creatupdated_ated_at" }              

                ],
                "drawCallback": function( settings, json ) 
                {
                    $('.delrow').on('click',function(e)
                    {
                        if(confirm('Apakah Anda yakin menghapus data ini?'))
                        {
                            let _this	= $(this);
                            let datas       = dTable.row(_this.parents('tr')).data();
                            
                            $.ajax(
                            {
                                url         : "{{ route('deljadwalday') }}",
                                type        : 'POST',
                                dataType    : 'json',
                                data        : {id:datas.id},
                                success     : function(result,status,xhr)
                                {
                                    if(result.status == 1)
                                    {
                                        _this.parents('tr').fadeOut();
                                        dTable.row(_this.parents('tr')).remove().draw(false);
                                        dTable.ajax.reload();
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
                    });
                    
                    $('.editrow').on('click',function(e)
                    {
                        let _this	= $(this);
                        let datas = dTable.row(_this.parents('tr')).data();
                        $('#id').val(datas.id);
                        $('#kode').val(datas.kode);
                        $('#deskripsi').val(datas.deskripsi);
                        calendar.refetchEvents();
                        calendar.render();
                        $('.grpCopy').show();

                    });

                    $('.show').on('click', function(e)
                    {
                        let _this	= $(this);
                        let datas = dTable.row(_this.parents('tr')).data();
                        $('#id').val(datas.id);
                        calendar.refetchEvents();
                        calendar.render();
                    });
                    
                }
            });            

            $('#jm_kerja').select2({
                // placeholder: 'Silakan Pilih',
                placeholder: "",
                allowClear: true,
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
                    if(typeof(par.text) !== "undefined")
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
            let dtX = {id:data.id,date_start:data.start,date_end:data.end};
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
            if(typeof(par.kode) !== 'undefined')
                return '<span class="badge" style="background-color:'+par.warna+'">'+par.kode+'</span>&nbsp;<span class="badge bg-success">'+par.jam_masuk+' - '+par.jam_keluar+'</span>';
            else
                return '';
        }
        
        let loadCalendar = function()
        {
            localStorage.removeItem('cal');
            
            calendar.getEvents().forEach(function(data, index)
            {
                data.remove();
            });

            var date = calendar.getDate();

            var strFormat =  dateString(date);
            // var y = date.getFullYear();
            // var m = date.getMonth()+1;
            // var d = date.getDate();
            // var strFormat =  y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);

            // console.log(strFormat);

            let ids = $('#id').val();
            if(ids != "")
            {
                $.ajax({
                    url         : '{{route("fcjadwalshift")}}',
                    type        : 'POST',
                    dataType    : 'json',
                    data        : {id : ids},
                    success     : function(result,status,xhr)
                    {
                        if(result!="")
                        {
                            result.forEach(function(itm, idx)
                            {
                                // console.table(itm);
                                // updateDataObject(itm);
                                calendar.addEvent(itm);
                            });
                        }
                        calendar.render();
                    }

                });
            }

            calendar.render();
        }
        
        let reloadCalendar = function(calendar)
        {
            calendar.getEvents().forEach(function(data, index)
            {
                data.remove();
            });

            let ids = $('#id').val();

            if(ids != "")
            {
                $.ajax({
                    url         : '{{route("fcjadwalshift")}}',
                    type        : 'POST',
                    dataType    : 'json',
                    data        : {id : ids},
                    success     : function(result,status,xhr)
                    {
                        if(result!="")
                        {
                            result.forEach(function(itm, idx)
                            {
                                calendar.addEvent(itm);
                            });
                        }
                    }

                });
            }
            calendar.render();
        }

        var dateString = function(dt)
        {
            var y = dt.getFullYear();
            var m = dt.getMonth()+1;
            var d = dt.getDate();
            var strFormat =  y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);

            return strFormat;
        }
    </script>
@endsection

@section('modal_form')
<div class="modal fade" id="modal-form">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h5 class="modal-title">Form Jadwal Kerja Shift</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="form_data" action="{{route('savejadwalshift')}}" accept-charset="UTF-8" >
            {{csrf_field()}}
            <input type="hidden" name="id" id="id">
            <input type="hidden" name="tglCal" id="tglCal">
            <div class="modal-body">   
                <div class="row">
                    <div class="col-5">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="kode">Kode Jadwal</label>
                                    <input type="text" class="form-control form-control-sm" id="kode" name="kode" placeholder="Kode Jadwal Shift">
                                </div>
                            </div> 
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="deskripsi">Deskripsi</label>
                                    <input type="text" class="form-control form-control-sm" id="deskripsi" name="deskripsi" placeholder="Deskripsi">
                                </div>
                            </div>   
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="kode">Jam Kerja</label>
                                    <select class="form-control select2" style="width: 100%;" id="jm_kerja">

                                    </select>
                                    <!--<button id="resetSelect" class="btn btn-warning">Set Ulang Jam Kerja</button>-->
                                </div>
                            </div> 
                            <div class="col-12">
                                <div class="row justify-content-sm-center">
                                    <div class="col col-sm-3">
                                        <button type="button" id="cmdModalClose" class="btn btn-outline-light btn-danger" data-dismiss="modal">Keluar</button>
                                    </div>
                                    <div class="col col-sm-3">
                                        <button type="submit" id="cmdModalSave" class="btn btn-outline-light btn-success">Simpan</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12" style="height: 10px; border-bottom: 2px solid whitesmoke;"></div>
                            <div class="row grpCopy">
                                <div class="col-12">
                                    <div class="form-group">  
                                        <label>Range Jadwal Master:</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="far fa-calendar-alt"></i>
                                                </span>
                                            </div>
                                            <input type="text" id="copyDateMaster" class="form-control form-control-sm float-right" id="reservation">
                                        </div>
                                    </div>      
                                </div>
                                <div class="col-12">
                                    <div class="form-group">  
                                        <label>Range Jadwal Target:</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="far fa-calendar-alt"></i>
                                                </span>
                                            </div>
                                            <input type="text" id="copyDateTarget" name="copyDateTarget" class="form-control form-control-sm float-right" id="reservation">
                                        </div>
                                    </div>       
                                </div>
                                <div class="col-12 d-flex justify-content-center">
                                    <button class="btn btn-warning btn-sm" id="cmdCopy"><i class="fa fa-copy"></i>&nbsp;Copy Jadwal</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-7 bg-gray">
                        <div id="calendar"></div>
                    </div>  
                </div>
            </div>    
        </form>
    </div>
    <!-- /.modal-content -->
</div>
    <!-- /.modal-dialog -->
</div>



<div class="modal fade" id="modal-form-upload">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Jadwal Kerja Shift</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<!--        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >-->
            {{ Form::open(['route' => ['uploadjadwalshift'], 'id' => 'form_data_upload', 'files' => true]) }}
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
                            <a class="btn btn-info btn-xs" href="{{route('app.files', 'file_temp_shift')}}" target="_blank"><i class="fa fa-download"></i>Template Document</a>
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

<div class="modal fade" id="modal-show">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title">Kalender Kerja</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">  
            <div class="row bg-gray">
                <div id="calendar-show"></div>        
            </div>
        </div>    
        <div class="modal-footer justify-content-between">
            <button type="button" id="cmdModalClose" class="btn btn-outline-light" data-dismiss="modal">Keluar</button>
            <!-- <button type="submit" id="cmdModalSave" class="btn btn-outline-light">Simpan</button> -->
        </div>
    </div>
    <!-- /.modal-content -->
</div>
    <!-- /.modal-dialog -->
</div>
@endsection

@section('content')
<div class="card bg-gradient-primary collapsed-card">
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
</div>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h5 class="card-title">&nbsp;</h5>
        <div class="card-tools">
            @if($show)
            <button class="btn btn-xs btn-warning" alt="Upload" data-toggle="modal" data-target="#modal-form-upload"><i class="fa fa-upload"></i>&nbsp;Upload</button>
            <button class="btn btn-xs btn-success" alt="Tambah" data-toggle="modal" data-target="#modal-form" id="cmdTambahShow"><i class="fa fa-plus-circle"></i>&nbsp;Tambah</button>
            @endif
        </div>
    </div>
<!--    <div class="card-header">
      <h5 class="m-0">Featured</h5>
    </div>-->
    <!-- /.card-header -->
        <div class="card-body">  
            <table id="dTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Aksi</th>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Buat</th>
                        <th>Tanggal Ubah</th>
                    </tr>
                </thead>
            </table>
        </div>
    <!-- /.card-body -->
</div>

@endsection