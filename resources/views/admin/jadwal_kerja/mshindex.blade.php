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
    
    <script>
        var dTable = null;
        let objJadwal = new Object();
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
            let Draggable = FullCalendarInteraction.Draggable;

            let calendarEl = document.getElementById('calendar');

            let calendar = new Calendar(calendarEl, {
            plugins: [ 'bootstrap', 'interaction', 'dayGrid', 'timeGrid' ],
            // defaultView: 'dayGridMonth',
            themeSystem: 'bootstrap',
            header    : {
                left  : 'prev,next today',
                center: 'title',
                right : 'dayGridMonth'
                // right : 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            dateClick: function(info) {
                let exists = false;
                let calEv = new Object();
                calendar.getEvents().forEach(function(data, index)
                {
                    if(new Date(data.start).toDateString() === new Date(info.dateStr).toDateString())
                    {
                        exists = true;
                        calEv = data;
                    }
                });
                
                var $sel = $('#jm_kerja').val();

                if($sel)
                {
                    getDataSelector($sel, function(data)
                    {
                        let itm = data.items[0];
                        let arrEv = {
                                title: itm.kode+"\n"+itm.jam_masuk+" - "+itm.jam_keluar,
                                start: info.dateStr,
                                color: itm.warna
                                // allDay: true
                            };
                        if( !exists )
                        {
                            // objJadwal = {info.dateStr : {jam_kerja_id : itm.id}};
                            console.log(objJadwal);
                            calendar.addEvent(arrEv);
                        }
                        else
                        {
                            calEv.remove();
                            calendar.addEvent(arrEv);
                        }
                    });
                }
            },
            editable  : true,
            selectable: true
            });

            calendar.render();
            
            $('#cmdSearch').on('click',function(e)
            {
                dTable.ajax.reload();
            });
            
            $('#warna').colorpicker();

            $('#form_data').submit( function(e)
            {
                e.preventDefault();
                const data = $(this).serialize();
                
                $.ajax(
                {
                    url         : $(this).attr('action'),
                    dataType    : 'json',
                    type        : 'POST',
                    data        : $('#form_data').serialize() ,
                    success(result,status,xhr)
                    {
                        if(result.status == 1)
                        {
                            document.getElementById("form_data").reset(); 
                            
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
                        dTable.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        /* implementation goes here */ 
                        console.log(jqXHR.responseText);
                    }
                    
                });
                
                return false;
            });

            $('#tblHari tr td').on('click', function(e)
            {
                var $td = $(this);
                var $inpt = $td.find('input');
                var $divv = $td.find('div');
                var $sel2 = $('#jm_kerja').val();
                var cb = null;

                if($sel2)
                {
                    getDataSelector($sel2, function(data)
                    {
                        console.log(data.items[0]);
                        console.log($divv);

                        var par = data.items[0];
                        $inpt.val($sel2);
                        $divv.html(strSel(par));
                    });
                }
                else
                {
                    Toast.fire({
                        type: 'error',
                        title: 'Mohon diisi jam kerja nya'
                    });
                }
            });
            
            $('#modal-form').on('hidden.bs.modal', function (e) 
            {
                document.getElementById("form_data").reset(); 
                $('#jm_kerja').val('').trigger('change');
                $('.hri').each(function(index, value)
                {
                    $(this).html("");
                });
                dTable.ajax.reload();
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
                    "url"       : "{{ route('dtjadwalday') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.search     = $('#txtSearch').val();
                    }
                },
                "columns"           :
                [
                    { data    : "action", orderable: false, searchable: false},
                    { data    : "kode", name : "kode" },
                    { data    : "deskripsi", name : "deskripsi" },
                    { data    : "created_by.name", name : "created_by" },
                    { data    : "created_at", name : "created_at" }              

                ],
                "drawCallback": function( settings, json ) 
                {
                    $('.delrow').on('click',function(e)
                    {
                        if(confirm('Apakah Anda yakin menghapus data ini?'))
                        {
                            var _this	= $(this);
                            var datas       = dTable.row(_this.parents('tr')).data();
                            
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
                        var _this	= $(this);
                        var datas = dTable.row(_this.parents('tr')).data();
                        $('#id').val(datas.id);
                        $('#kode').val(datas.kode);
                        if(typeof(datas.jadwal_kerja[0]) != "undefined")
                        {
                            $('#senin').val(datas.jadwal_kerja[0].id);
                            $('#divSenin').html(strSel(datas.jadwal_kerja[0]));
                        }
                        if(typeof(datas.jadwal_kerja[1]) != "undefined")
                        {
                            $('#selasa').val(datas.jadwal_kerja[1].id);
                            $('#divSelasa').html(strSel(datas.jadwal_kerja[1]));
                        }
                        if(typeof(datas.jadwal_kerja[2]) != "undefined")
                        {
                            $('#rabu').val(datas.jadwal_kerja[2].id);
                            $('#divRabu').html(strSel(datas.jadwal_kerja[2]));
                        }
                        if(typeof(datas.jadwal_kerja[3]) != "undefined")
                        {
                            $('#kamis').val(datas.jadwal_kerja[3].id);
                            $('#divKamis').html(strSel(datas.jadwal_kerja[3]));
                        }
                        if(typeof(datas.jadwal_kerja[4]) != "undefined")
                        {
                            $('#jumat').val(datas.jadwal_kerja[4].id);
                            $('#divJumat').html(strSel(datas.jadwal_kerja[4]));
                        }
                        if(typeof(datas.jadwal_kerja[5]) != "undefined")
                        {
                            $('#sabtu').val(datas.jadwal_kerja[5].id);
                            $('#divSabtu').html(strSel(datas.jadwal_kerja[5]));
                        }
                        if(typeof(datas.jadwal_kerja[6]) != "undefined")
                        {
                            $('#minggu').val(datas.jadwal_kerja[6].id);
                            $('#divMinggu').html(strSel(datas.jadwal_kerja[6]));
                        }

                    });
                    
                    
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
            
            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });

            var jadwalSelectorTable = function(data, days)
            {
                var jadwalKerja = data.jadwal_kerja;

                for(var i = 0 ; i < jadwalKerja.length ; i++)
                {
                    if(jadwalKerja[i].pivot.day == days)
                    {
                        return '<span class="badge" style="background-color:'+jadwalKerja[i].warna+'">'+jadwalKerja[i].kode+'</span><span class="badge bg-success">'+jadwalKerja[i].jam_masuk+' - '+jadwalKerja[i].jam_keluar+'</span>';
                    }
                }

                return null;
            }

        });

        var setShift = function(data)
        {
            console.log(data);
            // $.ajax({
            //     type: 'post',
            //     url: "{{route('seljamkerja')}}",
            //     data:{id:id},
            //     dataType: 'json',
            //     success: function(data)
            //     {
            //         callback(data);
            //     }
            // });
        }

        var getDataSelector = function(id, callback)
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

        var strSel = function(par)
        {
            return '<span class="badge" style="background-color:'+par.warna+'">'+par.kode+'</span>&nbsp;<span class="badge bg-success">'+par.jam_masuk+' - '+par.jam_keluar+'</span>';
        }
    </script>
@endsection

@section('modal_form')
<div class="modal fade" id="modal-form">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title">Form Jadwal Kerja Shift</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >
            {{csrf_field()}}
            <input type="hidden" name="id" id="id">
            <div class="modal-body">   
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="kode">Kode Jadwal</label>
                                <input type="text" class="form-control" id="kode" name="kode" placeholder="Kode Jam Kerja">
                            </div>
                        </div>                        
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="kode">Jam Kerja</label>
                                <select class="form-control select2" style="width: 100%;" id="jm_kerja">
                                    
                                </select>
                            </div>
                        </div>       
                        <div class="col-12">
                            <div class="card  bg-gradient-primary">
                                <div class="card-body p-0">
                                    <!-- THE CALENDAR -->
                                    <div id="calendar"></div>
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>                 
                    </div>
                </div>
            </div>    
            <div class="modal-footer justify-content-between">
                <button type="button" id="cmdModalClose" class="btn btn-outline-light" data-dismiss="modal">Keluar</button>
                <button type="submit" id="cmdModalSave" class="btn btn-outline-light">Simpan</button>
            </div>
        </form>
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
            <button class="btn btn-xs btn-success" alt="Tambah" data-toggle="modal" data-target="#modal-form"><i class="fa fa-plus-circle"></i>&nbsp;Tambah</button>
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
                    </tr>
                </thead>
            </table>
        </div>
    <!-- /.card-body -->
</div>

@endsection