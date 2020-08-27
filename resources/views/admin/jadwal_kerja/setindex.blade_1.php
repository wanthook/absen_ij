@extends('adminlte3.app')

@section('title_page')
    <p>Set Jadwal</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Set Jadwal Karyawan</li>
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
    <script src="{{asset('js/json2.js')}}"></script>
    <script src="{{asset('js/jsonSerialize.js')}}"></script>
    
    <script>
        let dTable = null;
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
            
            $('#cmdSearch').on('click',function(e)
            {
                dTable.ajax.reload();
            });
            
            dTable = $('#dTable').DataTable({
                "sPaginationType": "full_numbers",
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": true,
                "serverSide": true,
//                "autoWidth": false,
                "lengthMenu": [100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtjadwalset') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.sTipe        = $('#sTipe').val();
                        d.sKode        = $('#sKode').val();
                    }
                },
                "columns"           :
                [
                    { data    : "action", orderable: false, searchable: false},
                    { data    : "tipejadwal", name : "tipejadwal" },
                    { data    : "kode", name : "kode" },
                    { data    : "deskripsi", name : "deskripsi" },
                    { data    : "jumlahkaryawan", name : "jumlahkaryawan" }

                ]
            });

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
            <div class="row">
                <div class="col-2">
                    <div class="form-group">
                        <label for="sTipe">Tipe</label>
                        <select id="sTipe" class="custom-select custom-select-sm">
                            <option value="">--Silakan Pilih--</option>
                            <option value="D">Day Shift</option>
                            <option value="S">Shift</option>
                        </select>                  
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group">
                        <label for="sKode">Kode Jadwal</label>
                        <input type="text" class="form-control form-control-sm" id="sKode" placeholder="-- Kode Jadwal --">                  
                    </div>
                </div>  
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
<!--        <div class="card-tools">
            <button class="btn btn-xs btn-success" alt="Tambah" data-toggle="modal" data-target="#modal-form"><i class="fa fa-plus-circle"></i>&nbsp;Tambah</button>
        </div>-->
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
                        <th>Tipe Jadwal</th>
                        <th>Kode Jadwal</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Karyawan</th>
                    </tr>
                </thead>
            </table>
        </div>
    <!-- /.card-body -->
</div>

@endsection