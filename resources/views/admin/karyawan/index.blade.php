@extends('adminlte3.app')

@section('title_page')
    <p>Master Karyawan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Master Karyawan</li>
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
    
    <script>
        let dTable = null;
        let tblKeluarga = null;
        let objJadwal = [];
        $(function(e)
        {
            let Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false
            });
            
            var toastOverlay = Swal.mixin({
                position: 'center',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false
            });
            
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
            
            $('#perusahaan_id').select2({
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
            
            $('#divisi_id').select2({
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
            
            $('#jabatan_id').select2({
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
            
            $('#status_karyawan_id').select2({
                minimumInputLength: 0,
                delay: 250,
                placeholder: "",
                allowClear: true,
                ajax: {
                    url: "{{route('selkaryawanstatus')}}",
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
            
            $('#jadwal_id').select2({
                minimumInputLength: 0,
                delay: 250,
                placeholder: "",
                allowClear: true,
                ajax: {
                    url: "{{route('seljadwal')}}",
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
            
            $('#agama_id').select2({
                minimumInputLength: 0,
                delay: 250,
                placeholder: "",
                allowClear: true,
                ajax: {
                    url: "{{route('selagama')}}",
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
            
            $('#jenis_kelamin_id').select2({
                minimumInputLength: 0,
                delay: 250,
                placeholder: "",
                allowClear: true,
                ajax: {
                    url: "{{route('seljenkel')}}",
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
            
            $('#darah_id').select2({
                minimumInputLength: 0,
                delay: 250,
                placeholder: "",
                allowClear: true,
                ajax: {
                    url: "{{route('selgoldar')}}",
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
            
            $('#perkawinan_id').select2({
                minimumInputLength: 0,
                delay: 250,
                placeholder: "",
                allowClear: true,
                ajax: {
                    url: "{{route('selkawin')}}",
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
            
            $('#keluarga_relasi').select2({
                minimumInputLength: 0,
                delay: 250,
                placeholder: "",
                allowClear: true,
                ajax: {
                    url: "{{route('selrelasi')}}",
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
            
            $('#status_karyawan_id').on('select2:select', function(e)
            {
                var dt = $(this).select2('data');
                if(dt[0].text === "KONTRAK")
                {
                    $('#grp_kontrak').show();
                    $('#grp_probation').hide();
                }
                else if(dt[0].text === "PERCOBAAN")
                {
                    $('#grp_kontrak').hide();
                    $('#grp_probation').show();
                }
                else
                {
                    $('#grp_kontrak').hide();
                    $('#grp_probation').hide();
                }
            });
            
            $('#jadwal_id').on('select2:select', function(e)
            {
                var dt = $(this).select2('data');
                
                $('#tipe').val(dt[0].label);
            });            

            $('#tanggal_probation, #tanggal_kontrak, #tanggal_lahir').datetimepicker({
                format: 'DD-MM-YYYY'
            });

            $('#form_data').submit( function(e)
            {
                e.preventDefault();
                
                let formData = $(this).serializeFormJSON();

                formData.data = objJadwal;

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
                            title: 'Sedang memproses absen',
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success(result,status,xhr)
                    {
                        if(result.status == 1)
                        {
//                            document.getElementById("form_data").reset(); 
                            
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
                        toastOverlay.close();
                        /* implementation goes here */ 
                        console.log(jqXHR.responseText);
                    }
                    
                });
                
                return false;
            });
            
            $('#btn-keluarga').on('click', function(e)
            {
                e.preventDefault();
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
                        dTable.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        toastOverlay.close();
                        /* implementation goes here */ 
                        console.log(jqXHR.responseText);
                    }
                });
            });
            
            dTable = $('#dTable').DataTable({
                "sPaginationType": "full_numbers",
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": true,
                "serverSide": true,
                "autoWidth": true,
                "lengthMenu": [10, 50, 100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtkaryawan') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.search     = $('#txtSearch').val();
                    }
                },
                "columns"           :
                [
                    { data    : "action", orderable: false, searchable: false},
                    { data    : function(data)
                        {
                            return "NIK : <b>" + data.nik + "</b><br>" +
                                   "PIN : <b>" + data.pin + "</b>";
                        }
                    
                    },                    
                    { data    : "nama", name : "nama" },
                    { data    : function(data)
                        {
                            var str = "";
                            
                            if(data.jabatan != null)
                            {
                                str += "Jabatan : <b>" + data.jabatan.kode + " - " + data.jabatan.deskripsi + "</b><br>";
                            }
                            else
                            {
                                str += "Jabatan : -<br>";
                            }
                            
                            if(data.divisi != null)
                            {
                                str += "Divisi  : <b>" + data.divisi.kode + " - " + data.divisi.deskripsi + "</b><br>";
                            }
                            else
                            {
                                str += "Divisi : -<br>";
                            }
                            
                            if(data.perusahaan != null)
                            {
                                str += "Perusahaan  : <b>" + data.perusahaan.kode + " - " + data.perusahaan.deskripsi + "</b>";
                            }
                            else
                            {
                                str += "Perusahaan : -";
                            }
                            
                            return str;
                        }
                    
                    },
                    { data    : function(data)
                        {
                            var str = "";
                            if(data.jadwal != null)
                            {
                                if(data.jadwal.tipe == "D")
                                {
                                    str += "Tipe : <b>Dayshift</b>";
                                }
                                else if(data.jadwal.tipe == "S")
                                {
                                    str += "Tipe : <b>Shift</b>";
                                }

                                str += "</br>Kode : " + data.jadwal.kode;
                            }
                            else
                            {
                                str += "Tipe : -</br>Kode : -";
                            }
                            
                            return str;
                        }
                    },
                    { data    : "status.deskripsi", name : "status" },
                    { data    : function(data)
                        {
                            var str = "Masuk : <b>" + data.tanggal_masuk  + "</b>";
                            
                            if(data.status.nama == 'PERCOBAAN')
                            {
                                str += "<br>Probation  : <b>" + data.tanggal_probation +  "</b>";
                            }
                            else if(data.status.nama == 'KONTRAK')
                            {
                                str += "Kontrak  : <b>" + data.tanggal_kontrak +  "</b>";
                            }
                            return  str;
                        }
                    
                    },
                    { data    : "created_by.name", name : "created_by" },
                    { data    : "created_at", name : "created_at" }              

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

                    });
                    
                    $('.show').on('click', function(e)
                    {
//                        let _this	= $(this);
//                        let datas = dTable.row(_this.parents('tr')).data();
//                        $('#id').val(datas.id);
//                        calendar.refetchEvents();
//                        calendar.render();
                    });
                    
                }
            });
            
            tblKeluarga = $('#tblKeluarga').DataTable({
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
                    "url"       : "{{ route('dtkaryawankeluarga') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.search     = $('#txtSearch').val();
                    }
                },
                "columns"           :
                [
                    { data    : "action", orderable: false, searchable: false},
                    { data    : "nama", name : "nama" },
                    { data    : "relasi.kode", name : "relasi" },
                    { data    : "telpon", name : "telpon" },
                    { data    : "jenkel.kode", name : "telpon" },
                    { data    : "kota", name : "kota" },

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

                    });
                    
                    $('.show').on('click', function(e)
                    {
                    });
                    
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
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Karyawan</h4>
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
<div class="card card-primary card-outline">
    <div class="card-header">
        <h5 class="card-title">&nbsp;</h5>
        <div class="card-tools">
            <button class="btn btn-xs btn-warning" alt="Upload" data-toggle="modal" data-target="#modal-form-upload"><i class="fa fa-upload"></i>&nbsp;Upload</button>
            <a class="btn btn-success btn-xs" alt="Tambah" href="{{route('mkaryawanf')}}"><i class="fa fa-plus-circle"></i>&nbsp;Tambah</a>
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
                        <th>Nama</th>
                        <th>Info Karyawan</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th>Info Tanggal</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Buat</th>
                    </tr>
                </thead>
            </table>
        </div>
    <!-- /.card-body -->
</div>

@endsection