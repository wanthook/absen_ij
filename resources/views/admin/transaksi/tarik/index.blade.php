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

                $.ajax(
                {
                    url         : '{{route("tarikmesin")}}',
                    dataType    : 'json',
                    type        : 'POST',
                    data        : {id : selectedId} ,
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
                        data: "total_log" 
                    },
                    { 
                        targets : "tlog", 
                        data: "lastlog" 
                    }]
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
<!--    <div class="card-header">
      <h5 class="m-0">Featured</h5>
    </div>-->
    <!-- /.card-header -->
        <div class="card-body">  
            <button id="cmdHapus" class="btn btn-xs btn-danger float-right" alt="Hapus Absen"><i class="fa fa-eraser"></i>&nbsp;Hapus Absen</button>&nbsp;
            <button id="cmdTarik" class="btn btn-xs btn-success float-right" alt="Tarik Absen"><i class="fa fa-download"></i>&nbsp;Tarik Absen</button>
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