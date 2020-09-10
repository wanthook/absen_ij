@extends('adminlte3.app')

@section('title_page')
    <p>Form Master Karyawan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item"><a href="{{route('mkaryawan')}}">Master Karyawan</a></li>
<li class="breadcrumb-item active">Form Master Karyawan</li>
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
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            
            var ToastX = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                onClose: function()
                {
                    document.location.href="{{route('mkaryawan')}}";
                }
            });
            
            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            /* initialize the calendar
            -----------------------------------------------------------------*/
            
            $('#cmdSearch').on('click',function(e)
            {
                dTable.ajax.reload();
            });
            
            $('#pin').on('keypress', function(e)
            {
//                e.preventDefault();
                if(e.keyCode == 13)
                {
                    e.preventDefault();
                    var param = {pin:$(this).val()};
                    $.ajax(
                    {
                        url         : '{{route('selkaryawan')}}',
                        dataType    : 'json',
                        type        : 'POST',
                        data        : param,
                        success(result,status,xhr)
                        {
//                            console.log(result);
                            if(result.items)
                            {
                                window.open("{{route('mkaryawane','')}}"+"/"+result.items[0].id, '_self');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) { 
                            /* implementation goes here */ 
                            console.log(jqXHR.responseText);
                        }

                    });
                }
            });
            
            $("#photos_img").click(function(e)
            {
                e.preventDefault();
                $("#foto").click();
            });
            
            $("#foto").on("change",function(e)
            {
//                console.log('stop');
                if (this.files && this.files[0]) 
                {
                    var reader = new FileReader();

                    reader.onload = function (es) {
                        $("#photos_img").attr('src', es.target.result);
                    }

                    reader.readAsDataURL(this.files[0]);
                }

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
            
            $('#kel_relasi_id').select2({
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
//                console.log(dt);
                if(dt[0].deskripsi === "KONTRAK")
                {
                    $('#grp_kontrak').show();
                    $('#grp_probation').hide();
                }
                else if(dt[0].deskripsi === "PERCOBAAN")
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

//            $('#tanggal_probation, #tanggal_kontrak, #tanggal_lahir').datetimepicker({
//                format: 'DD-MM-YYYY'
//            });
            
            $('#form_data').submit( function(e)
            {
                e.preventDefault();
                
                let formData = $(this).serializeFormJSON();

                $.ajax(
                {
                    url         : $(this).attr('action'),
                    dataType    : 'json',
                    contentType : 'application/json; charset=utf-8',
                    type        : 'POST',
                    data        :JSON.stringify(formData) ,
                    success(result,status,xhr)
                    {
                        if(result.status == 1)
                        {
//                            document.getElementById("form_data").reset(); 
                            
                            ToastX.fire({
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
//                        dTable.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        /* implementation goes here */ 
                        console.log(jqXHR.responseText);
                    }
                    
                });
                
                return false;
            });
            
            $('#form_data_keluarga').submit( function(e)
            {
                e.preventDefault();
                
                let formData = $(this).serializeFormJSON();

                $.ajax(
                {
                    url         : $(this).attr('action'),
                    dataType    : 'json',
                    contentType : 'application/json; charset=utf-8',
                    type        : 'POST',
                    data        :JSON.stringify(formData) ,
                    success(result,status,xhr)
                    {
                        if(result.status == 1)
                        {
//                            document.getElementById("form_data").reset(); 
                            document.getElementById("form_data_keluarga").reset(); 
                            $('#kel_relasi_id').val("").trigger('change');
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
                        tblKeluarga.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
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
            
            $('#modal-form-keluarga').on('hidden.bs.modal', function (e) 
            {
                document.getElementById("form_data_keluarga").reset(); 
                $('#kel_relasi_id').val("").trigger('change');
                tblKeluarga.ajax.reload();
                
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
                        d.karyawan_id     = $('#id').val();
                    }
                },
                "columns"           :
                [
                    { data    : "action", orderable: false, searchable: false},
                    { data    : "nama", name : "nama" },
                    { data    : "relasi.kode", name : "relasi" },
                    { data    : "telpon", name : "telpon" },
                    { data    : "kota", name : "kota" },
                    { data    : "alamat", name : "alamat" },

                ],
                "drawCallback": function( settings, json ) 
                {
                    $('.delrowkel').on('click',function(e)
                    {
                        e.preventDefault();
                        if(confirm('Apakah Anda yakin menghapus data ini?'))
                        {
                            let _this	= $(this);
                            let datas       = tblKeluarga.row(_this.parents('tr')).data();
                            
                            $.ajax(
                            {
                                url         : "{{ route('delkeluargakaryawan') }}",
                                type        : 'POST',
                                dataType    : 'json',
                                data        : {id:datas.id},
                                success     : function(result,status,xhr)
                                {
                                    if(result.status == 1)
                                    {
                                        _this.parents('tr').fadeOut();
                                        tblKeluarga.row(_this.parents('tr')).remove().draw(false);
                                        tblKeluarga.ajax.reload();
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
                    
                    $('.editrowkel').on('click',function(e)
                    {
                        e.preventDefault();
                        let _this	= $(this);
                        let datas = tblKeluarga.row(_this.parents('tr')).data();
                        $('#kel_keluarga_id').val($('#id').val());
                        $('#kel_id').val(datas.id);
                        $('#kel_nama').val(datas.nama);
                        $('#kel_ktp').val(datas.ktp);
                        
                        var option = new Option(datas.relasi.nama, datas.relasi.id, true, true);
                        $('#kel_relasi_id').append(option).trigger('change');
                        
                        $('#kel_tempat_lahir').val(datas.tempat_lahir);
                        $('#kel_tanggal_lahir').val(datas.tanggal_lahir);
                        $('#kel_telpon').val(datas.telpon);
                        $('#kel_alamat').val(datas.alamat);
                        $('#kel_kota').val(datas.kota);
                        $('#kel_kode_pos').val(datas.kode_pos);

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
<div class="modal fade" id="modal-form-keluarga">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Form Data Keluarga Karyawan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{Form::open(['url' => route('savekeluargakaryawan'), 'id' => 'form_data_keluarga'])}}     
            {{ Form::hidden('kel_id', null, ['id' => 'kel_id']) }}
            {{ Form::hidden('kel_keluarga_id', null, ['id' => 'kel_keluarga_id']) }}
            <div class="modal-body">
                <div class="row">                    
                    <div class="col-5">
                        <div class="form-group">
                            {{ Form::label('kel_nama', 'Nama') }}
                            {{ Form::text('kel_nama', null, ['id' => 'kel_nama', 'class' => 'form-control form-control-sm', 'placeholder' => 'Nama']) }}
                        </div>
                    </div>                 
                    <div class="col-4">
                        <div class="form-group">
                            {{ Form::label('kel_ktp', 'KTP') }}
                            {{ Form::text('kel_ktp', null, ['id' => 'kel_ktp', 'class' => 'form-control form-control-sm', 'placeholder' => 'KTP']) }}
                        </div>
                    </div>  
                    <div class="col-3">
                        <div class="form-group">
                            {{ Form::label('kel_relasi_id', 'Relasi') }}
                            {{ Form::select('kel_relasi_id', [], null, ['id' => 'kel_relasi_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                        </div>
                    </div>   
                    
                    <div class="col-4">
                        <div class="form-group">
                            {{ Form::label('kel_tempat_lahir', 'Tempat Lahir') }}
                            {{ Form::text('kel_tempat_lahir', null, ['id' => 'kel_tempat_lahir', 'class' => 'form-control form-control-sm', 'placeholder' => 'Tempat Lahir']) }}
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            {{ Form::label('kel_tanggal_lahir', 'Tanggal Lahir') }}
                            <div class="input-group" data-target-input="nearest">
                                {{ Form::date('kel_tanggal_lahir', null, ['id' => 'kel_tanggal_lahir', 'class' => 'form-control form-control-sm']) }}
                                <div class="input-group-append" data-target="#kel_tanggal_lahir">
                                    <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>            
                    <div class="col-4">
                        <div class="form-group">
                            {{ Form::label('kel_telpon', 'Telpon') }}
                            {{ Form::text('kel_telpon', null, ['id' => 'kel_telpon', 'class' => 'form-control form-control-sm', 'placeholder' => 'Telpon']) }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            {{ Form::label('kel_alamat', 'Alamat') }}
                            {{ Form::textarea('kel_alamat', null, ['id' => 'kel_alamat', 'rows' => '3','class' => 'form-control form-control-sm', 'placeholder' => 'Alamat']) }}
                        </div>
                        <!--<input type="text" class="form-control" id="alamat" name="alamat">-->
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            {{ Form::label('kel_kota', 'Kota') }}
                            {{ Form::text('kel_kota', null, ['id' => 'kel_kota', 'class' => 'form-control form-control-sm', 'placeholder' => 'Kota']) }}
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            {{ Form::label('kel_kode_pos', 'Kode Pos') }}
                            {{ Form::text('kel_kode_pos', null, ['id' => 'kel_kode_pos', 'class' => 'form-control form-control-sm', 'placeholder' => 'Kode Pos']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" id="cmdModalClose" class="btn btn-danger" data-dismiss="modal">Keluar</button>
                <button type="submit" id="cmdModalSave" class="btn btn-primary">Simpan</button>
            </div>
            {{Form::close()}}
        </div>
    </div>
</div>
            
<div class="modal fade" id="modal-form">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title">Form Data Karyawan</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        
    </div>
    <!-- /.modal-content -->
</div>
    <!-- /.modal-dialog -->
</div>
@endsection

@section('content')
{{ Form::model($var, ['route' => ['savekaryawan'], 'id' => 'form_data']) }}
{{ Form::hidden('id',null, ['id' => 'id']) }}
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tabs-pegawai-id" data-toggle="pill" href="#tabs-pegawai" role="tab" aria-controls="tabs-pegawai" aria-selected="true">Pegawai</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-pribadi-id" data-toggle="pill" href="#tabs-pribadi" role="tab" aria-controls="tabs-pribadi" aria-selected="true">Pribadi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-keluarga-id" data-toggle="pill" href="#tabs-keluarga" role="tab" aria-controls="tabs-keluarga" aria-selected="true">Keluarga</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-pendidikan-id" data-toggle="pill" href="#tabs-pendidikan" role="tab" aria-controls="tabs-pendidikan" aria-selected="true">Pendidikan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-lain-id" data-toggle="pill" href="#tabs-lain" role="tab" aria-controls="tabs-lain" aria-selected="true">Lain-lain</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">   
                <div class="tab-content" id="tabs-karyawan">
                    <!-- Pegawai -->
                    <div class="tab-pane fade show active" id="tabs-pegawai" role="tabpanel" aria-labelledby="custom-tabs-one-home-tab">
                        <div class="row">
                            <div class="col-12">
                                <div class="text-center">
                                    <input type="image" id="photos_img" class="profile-user-img img-fluid img-circle" src="{{asset('bower_components/admin-lte/dist/img/user1-128x128.jpg')}}"  alt="Karyawan profile picture" />
                                    <input type="file" id="foto" name="foto" style="display: none;"  accept="image/*" />
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    {{ Form::label('pin', 'PIN') }}
                                    {{ Form::text('pin', null, ['id' => 'pin', 'class' => 'form-control form-control-sm', 'placeholder' => 'PIN']) }}
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    {{ Form::label('key', 'KEY') }}
                                    {{ Form::text('key', null, ['id' => 'key', 'class' => 'form-control form-control-sm', 'placeholder' => 'KEY']) }}
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    {{ Form::label('nik', 'NIK') }}
                                    {{ Form::text('nik', null, ['id' => 'nik', 'class' => 'form-control form-control-sm', 'placeholder' => 'NIK']) }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    {{ Form::label('nama', 'Nama') }}
                                    {{ Form::text('nama', null, ['id' => 'nama', 'class' => 'form-control form-control-sm', 'placeholder' => 'Nama']) }}
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('perusahaan_id', 'Perusahaan') }}
                                    @if($var->perusahaan)
                                    {{ Form::select('perusahaan_id', [$var->perusahaan->id => $var->perusahaan->kode.' - '.$var->perusahaan->deskripsi], null, ['id' => 'perusahaan_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    @else
                                    {{ Form::select('perusahaan_id', [], null, ['id' => 'perusahaan_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    @endif
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('divisi_id', 'Divisi') }}
                                    @if($var->divisi)
                                    {{ Form::select('divisi_id', [$var->divisi->id => $var->divisi->kode.' - '.$var->divisi->deskripsi], null, ['id' => 'divisi_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    @else
                                    {{ Form::select('divisi_id', [], null, ['id' => 'divisi_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    @endif
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('jabatan_id', 'Jabatan') }}
                                    @if($var->jabatan)
                                    {{ Form::select('jabatan_id', [$var->jabatan->id => $var->jabatan->kode.' - '.$var->jabatan->deskripsi], null, ['id' => 'jabatan_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    @else
                                    {{ Form::select('jabatan_id', [], null, ['id' => 'jabatan_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    @endif
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('status_karyawan_id', 'Status Karyawan') }}
                                    @if($var->status)
                                    {{ Form::select('status_karyawan_id', [$var->status->id => $var->status->nama.' - '.$var->status->deskripsi], null, ['id' => 'status_karyawan_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    @else
                                    {{ Form::select('status_karyawan_id', [], null, ['id' => 'status_karyawan_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    @endif
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('tanggal_masuk', 'Tanggal Masuk') }}
                                    <div class="input-group" data-target-input="nearest">
                                        {{ Form::date('tanggal_masuk', null, ['id' => 'tanggal_masuk', 'class' => 'form-control form-control-sm', 'placeholder' => 'Tanggal Masuk']) }}
                                        <div class="input-group-append" data-target="#tanggal_masuk">
                                            <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div id="grp_probation" style="display:none;">
                                    <div class="form-group">
                                        {{ Form::label('tanggal_probation', 'Tanggal Akhir Percobaan') }}
                                        <div class="input-group" data-target-input="nearest">
                                            {{ Form::date('tanggal_probation', null, ['id' => 'tanggal_probation', 'class' => 'form-control form-control-sm']) }}
                                            <div class="input-group-append" data-target="#tanggal_probation">
                                                <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="grp_kontrak" style="display:none;">
                                    <div class="form-group">
                                        {{ Form::label('tanggal_kontrak', 'Tanggal Akhir Kontrak') }}
                                        <div class="input-group" data-target-input="nearest">
                                            {{ Form::date('tanggal_kontrak', null, ['id' => 'tanggal_kontrak', 'class' => 'form-control form-control-sm']) }}
                                            <div class="input-group-append" data-target="#tanggal_kontrak">
                                                <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('jadwal_id', 'Jadwal Karyawan') }}
                                    @if($var->jadwals)
                                    {{ Form::select('jadwal_id', [$var->jadwals[0]->id => $var->jadwals[0]->tipe.' - '.$var->jadwals[0]->kode], null, ['id' => 'jadwal_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}     
                                    @else
                                    {{ Form::select('jadwal_id', [], null, ['id' => 'jadwal_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}     
                                    @endif                               
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('tipe', 'Tipe Jadwal') }}
                                    @if($var->jadwals)
                                    {{ Form::text('tipe', (($var->jadwals[0]->tipe == 'D')?'Dayshift':'Shift'), ['id' => 'tipe', 'class' => 'form-control form-control-sm', 'readonly']) }}
                                    @else
                                    {{ Form::text('tipe', null, ['id' => 'tipe', 'class' => 'form-control form-control-sm', 'readonly']) }}
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div><!-- End Pegawai -->

                    <!-- Pegawai -->
                    <div class="tab-pane" id="tabs-pribadi" role="tabpanel" aria-labelledby="tabs-pribadi">
                        <div class="row">                                            
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('email', 'E-Mail') }}
                                    {{ Form::email('email', null, ['id' => 'email', 'class' => 'form-control form-control-sm', 'placeholder' => 'E-Mail']) }}
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('ktp', 'KTP') }}
                                    {{ Form::text('ktp', null, ['id' => 'ktp', 'class' => 'form-control form-control-sm', 'placeholder' => 'KTP']) }}
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('agama_id', 'Agama') }}
                                    @if($var->agama)
                                    {{ Form::select('agama_id', [$var->agama->id => $var->agama->deskripsi], null, ['id' => 'agama_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}     
                                    @else
                                    {{ Form::select('agama_id', [], null, ['id' => 'agama_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}     
                                    @endif     
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::label('jenis_kelamin_id', 'Jenis Kelamin') }}
                                    @if($var->jeniskelamin)
                                    {{ Form::select('jenis_kelamin_id', [$var->jeniskelamin->id => $var->jeniskelamin->nama.' - '.$var->jeniskelamin->deskripsi], null, ['id' => 'jenis_kelamin_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}     
                                    @else
                                    {{ Form::select('jenis_kelamin_id', [], null, ['id' => 'jenis_kelamin_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}     
                                    @endif     
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::label('darah_id', 'Golongan Darah') }}
                                    @if($var->darah)
                                    {{ Form::select('darah_id', [$var->darah->id => $var->darah->nama.' - '.$var->darah->deskripsi], null, ['id' => 'darah_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}     
                                    @else
                                    {{ Form::select('darah_id', [], null, ['id' => 'darah_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}     
                                    @endif     
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::label('tempat_lahir', 'Tempat Lahir') }}
                                    {{ Form::text('tempat_lahir', null, ['id' => 'tempat_lahir', 'class' => 'form-control form-control-sm', 'placeholder' => 'Tempat Lahir']) }}
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::label('tanggal_lahir', 'Tanggal Lahir') }}
                                    <div class="input-group" data-target-input="nearest">
                                        {{ Form::date('tanggal_lahir', null, ['id' => 'tanggal_lahir', 'class' => 'form-control form-control-sm']) }}
                                        <div class="input-group-append" data-target="#tanggal_lahir">
                                            <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::label('perkawinan_id', 'Status Menikah') }}
                                    @if($var->nikah)
                                    {{ Form::select('perkawinan_id', [$var->nikah->id => $var->nikah->nama.' - '.$var->nikah->deskripsi], null, ['id' => 'perkawinan_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}     
                                    @else
                                    {{ Form::select('perkawinan_id', [], null, ['id' => 'perkawinan_id', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}     
                                    @endif 
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::label('telpon', 'Telpon Rumah') }}
                                    {{ Form::text('telpon', null, ['id' => 'telpon', 'class' => 'form-control form-control-sm', 'placeholder' => 'Telpon Rumah']) }}
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    {{ Form::label('hp', 'HP') }}
                                    {{ Form::text('hp', null, ['id' => 'hp', 'class' => 'form-control form-control-sm', 'placeholder' => 'HP']) }}
                                </div>
                            </div>
                            <div class="col-3"></div>
                            <div class="col-8">
                                <div class="form-group">
                                    {{ Form::label('alamat', 'Alamat') }}
                                    {{ Form::textarea('alamat', null, ['id' => 'alamat', 'rows' => '3','class' => 'form-control form-control-sm', 'placeholder' => 'Alamat']) }}
                                </div>
                                <!--<input type="text" class="form-control" id="alamat" name="alamat">-->
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    {{ Form::label('kota', 'Kota') }}
                                    {{ Form::text('kota', null, ['id' => 'kota', 'class' => 'form-control form-control-sm', 'placeholder' => 'Kota']) }}
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    {{ Form::label('kode_pos', 'Kode Pos') }}
                                    {{ Form::text('kode_pos', null, ['id' => 'kode_pos', 'class' => 'form-control form-control-sm', 'placeholder' => 'Kode Pos']) }}
                                </div>
                            </div>

                        </div>
                    </div><!-- End Pegawai -->

                    <!-- Keluarga -->
                    <div class="tab-pane" id="tabs-keluarga" role="tabpanel" aria-labelledby="tabs-keluarga" style="color:#000;">

                        <div class="row">
                            <div class="col-12">
                                {{Form::button('<i class="fa fa-plus-circle"></i>&nbsp;Tambah Keluarga',['id' => 'btn_tambah_keluarga', 'class' => 'btn btn-success btn-sm float-right', 'data-toggle' => 'modal', 'data-target' => '#modal-form-keluarga'])}}
                            </div>
                            <div class="col-12">                                                
                                <!--<button class="btn btn-success float-right" id="btn-keluarga" alt="Tambah" data-toggle="modal" data-target="#modal-form-keluarga"><i class="fa fa-plus-circle"></i>&nbsp;Tambah</button>-->
                                <table id="tblKeluarga" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>&nbsp;</th>
                                            <th>Nama</th>
                                            <th>Relasi</th>
                                            <th>Tlp</th>
                                            <!--<th>Jenkel</th>-->
                                            <th>Kota</th>
                                            <th>Alamat</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div><!-- End Keluarga -->
                    
                    <!-- Lain-lain -->
                    <div class="tab-pane" id="tabs-lain" role="tabpanel" aria-labelledby="tabs-lain">
                        <div class="row">                                            
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('ukuran_baju', 'Ukuran Baju') }}
                                    {{ Form::text('ukuran_baju', null, ['id' => 'ukuran_baju', 'class' => 'form-control form-control-sm', 'placeholder' => 'Ukuran Baju']) }}
                                </div>
                            </div>                                           
                            <div class="col-4">
                                <div class="form-group">
                                    {{ Form::label('ukuran_sepatu', 'Ukuran Baju') }}
                                    {{ Form::text('ukuran_sepatu', null, ['id' => 'ukuran_sepatu', 'class' => 'form-control form-control-sm', 'placeholder' => 'Ukuran Sepatu']) }}
                                </div>
                            </div>
                        </div>
                    </div><!-- End Lain-lain -->
                </div>  
            </div>
            <div class="card-footer">
                <button type="button" id="cmdKeluar" class="btn btn-danger"><i class="fa fa-backward"></i>&nbsp;Keluar</button>
                <button type="submit" id="cmdSave" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;Simpan</button>
            </div>
        </div>
    </div>
</div> 
{{ Form::close() }}

@endsection