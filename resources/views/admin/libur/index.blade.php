@extends('adminlte3.app')

@section('title_page')
<p>Libur Nasional</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Libur Nasional</li>
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
        let dTableKar = null;
        let dTableKarJad = null;
        let objLibur = [];
        
        $(function(e)
        {
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
            header    : {
                left  : 'prev,next today',
                center: 'title',
                right : 'dayGridMonth'
                // right : 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            dateClick: async function(info) {
                var tgl = info.dateStr;
                
                const {value: keterangan} = await Swal.fire({
                    title: 'Keterangan Libur',
                    input: 'text',
                    inputPlaceholder: 'keterangan'
                });
                
                $.ajax({
                        url         : '{{route("savelibur")}}',
                        type        : 'POST',
                        dataType    : 'json',
                        data        : {tgl : tgl, keterangan: keterangan},
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
                        },
                        error : function(xhr, status, error)
                        {
                            Toast.fire({
                                type: 'error',
                                title: "Ada error. Mohon hubungi IT"
                            });
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

            loadManual(calendar);
            
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
        });
        
        let loadManual = function(calendar)
        {
            $.ajax({
                url         : '{{route("fclibur")}}',
                type        : 'POST',
                dataType    : 'json',
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

            calendar.render();
        }

        let updateDataObject = function(data)
        {
            let ada = false;
            let dtX = {id:data.id,date:data.start};
            let idxDel = null;
            
            objLibur.forEach(function(el, idx)
            {
                
                if(el.date === data.start)
                {
                    if(data.title == "")
                    {
                        idxDel = idx;
                    }
                    else
                    {
                        objLibur[idx] = dtX;
                        ada = true;
                    }
                }
            });

            if(!ada)
            {
                objLibur.push(dtX);
            }
            
            if(idxDel != null)
            {
//                console.log(idxDel);
                objLibur.splice(idxDel,1);
                objLibur.pop();
            }
        }

        let strSel = function(par)
        {
            return '<span class="badge" style="background-color:#FF0000">'+par.keterangan+'</span>';
        }
    </script>
@endsection

@section('modal_form')

@endsection

@section('content')
<div class="row">     
     
    <div class="col-12">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-body"> 
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection