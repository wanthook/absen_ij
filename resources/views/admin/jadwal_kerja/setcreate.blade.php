@extends('adminlte3.app')

@section('title_page')
<p>
    @php
    echo $var['kode'].' - '.(($var['tipe'] == 'D')?'Dayshift':'Shift');
    @endphp
</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item"><a href="{{route('jkset')}}">Set Jadwal Karyawan</a></li>
<li class="breadcrumb-item active">Form Set Jadwal Karyawan</li>
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
            
            $('#sKarCar').on('click',function(e)
            {
                dTableKar.ajax.reload();
            });
            
            $('#btnSet').on('click',function(e)
            {
                var selectedIds = $( dTableKar.$('input[type="checkbox"]').map(function () 
                {
                    return $(this).prop("checked") ? dTableKar.row($(this).closest('tr')).data().id : null;
                }));
                
                delete selectedIds.length;
                
                var postData = {
                    idsJad : {{$var->id}},
                    idsKar : selectedIds
                };
//                console.log(postData);
                $.ajax(
                {
                    url         : '{{route("savejadwalset")}}',
                    dataType    : 'json',
                    type        : 'POST',
                    contentType: 'application/json',
                    data        : JSON.stringify(postData),
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
//                        $('#loadingDialog').modal('hide');
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
                        dTableKar.ajax.reload();
                        dTableKarJad.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        /* implementation goes here */ 
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
//                "autoWidth": false,
                "lengthMenu": [100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtkaryawan') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.jnJadwalId      = {{$var->id}};
                        d.jPinNikJadwal   = $('#sKarPin').val();
                        d.jDivJadwal      = $('#sKarDiv').val();
                        d.jJabatanJadwal  = $('#sKarJab').val();
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
                            return data.jabatan.kode+" - "+data.jabatan.deskripsi;
                        }
                },
                {
                        targets : 'tdivisi',
                        data: function(data)
                        {
                            return data.divisi.kode+" - "+data.divisi.deskripsi;
                        }
                },
                {
                        targets : 'tjadwal',
                        data: function(data)
                        {
                            return data.jadwal.tipe+" - "+data.jadwal.kode;
                        }
                }]
            });
            
            
            
            dTableKarJad = $('#dTableKarJad').DataTable({
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
                        d.jJadwalId      = {{$var->id}};
                    }
                }, 
                "columnDefs"    :[
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
                            return data.jabatan.kode+" - "+data.jabatan.deskripsi;
                        }
                },
                {
                        targets : 'tdivisi',
                        data: function(data)
                        {
                            return data.divisi.kode+" - "+data.divisi.deskripsi;
                        }
                },
                {
                        targets : 'tjadwal',
                        data: function(data)
                        {
                            return data.jadwal.tipe+" - "+data.jadwal.kode;
                        }
                }]
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
    </script>
@endsection

@section('modal_form')

@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
        <!--    <div class="card-header">
              <h5 class="m-0">Featured</h5>
            </div>-->
            <!-- /.card-header -->
                <div class="card-body">  
                    <form>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::label('sKarPin', 'PIN/NIK') }}
                                    {{ Form::text('sKarPin', null, ['id' => 'sKarPin', 'class' => 'form-control form-control-sm', 'placeholder' => 'PIN/NIK']) }}
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::label('sKarDiv', 'Divisi') }}
                                    {{ Form::select('sKarDiv', [], null, ['id' => 'sKarDiv', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}

                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::label('sKarJab', 'Jabatan') }}
                                    {{ Form::select('sKarJab', [], null, ['id' => 'sKarJab', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::button('Cari',['id' => 'sKarCar', 'class' => 'btn btn-success btn-sm']) }}
                                </div>
                            </div>
                        </div>
                    </form>
                    
                </div>
            <!-- /.card-body -->
        </div>
    </div>  
    <div class="col-6">
        <div class="card card-primary card-outline">
            <div class="card-body">  
                <div class="float-right">
                    <button id="btnSet" class="btn btn-info">Set >></button>
                </div>
                <table id="dTableKar" class="table table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="tpin">PIN</th>
                            <th class="tnik">NIK</th>
                            <th class="tnama">Nama</th>
                            <th class="tdivisi">Divisi</th>
                            <th class="tjabatan">Jabatan</th>
                            <th class="tjadwal">Jadwal Lama</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>  
    <div class="col-6">
        <div class="card card-primary card-outline">
            <div class="card-body">  
                <table id="dTableKarJad" class="table table-hover">
                    <thead>
                        <tr>
                            <th class="tpin">PIN</th>
                            <th class="tnik">NIK</th>
                            <th class="tnama">Nama</th>
                            <th class="tdivisi">Divisi</th>
                            <th class="tjabatan">Jabatan</th>
                            <th class="tjadwal">Jadwal Lama</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection