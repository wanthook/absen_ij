@extends('adminlte3.app')

@section('title_page')
<p>Master Divisi</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Master Divisi</li>
@endsection

@section('add_css')
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
    
    <script>
        var dTable = null;
        $(function(e)
        {
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

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $('#cmdSearch').on('click',function(e)
            {
                dTable.ajax.reload();
            });
            
            $('#parent_id').select2({
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
            
            $('#cost_center_id').select2({
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('selcostcenter')}}",
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
            
            $('#type_id').select2({
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('selcostcentertype')}}",
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
            
            $('#form_data').submit( function(e)
            {
                e.preventDefault();
                const data = $(this).serialize();
                console.log(data);
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
                                $('#tipe_exim').attr('disabled','disabled');
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
            });
            
            $('#modal-form').on('hidden.bs.modal', function (e) 
            {
                document.getElementById("form_data").reset(); 
                $('#parent_id').val(null).trigger('change');
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
                    "url"       : "{{ route('dtdivisi') }}",
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
                    { data    : function(datas)
                    {
                        if(datas.parents)
                        {
                            return datas.parents.kode+' - '+datas.parents.deskripsi;
                        }
                        else
                        {
                            return '';
                        }
                    }, orderable: false},
                    { data    : function(datas)
                    {
                        if(datas.costcenter)
                        {
                            return datas.costcenter.kode+' - '+datas.costcenter.deskripsi;
                        }
                        else
                        {
                            return '';
                        }
                    }, orderable: false},
                    { data    : function(datas)
                    {
                        if(datas.type)
                        {
                            return datas.type.nama;
                        }
                        else
                        {
                            return '';
                        }
                    }, orderable: false},
                    { data    : "created_by.name", name : "created_by", orderable: false },
                    { data    : "created_at", name : "created_at", orderable: false }              

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
                                url         : "{{ route('deldivisi') }}",
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
                        $('#deskripsi').val(datas.deskripsi);

                        if(datas.parents)
                        {
                            var val = {
                                id: datas.parents.id,
                                text: datas.parents.kode+' - '+datas.parents.deskripsi
                            };

                            var newOption = new Option(val.text, val.id, false, false);
                            $('#parent_id').append(newOption).trigger('change');
                        }

                        if(datas.costcenter)
                        {
                            var val = {
                                id: datas.costcenter.id,
                                text: datas.costcenter.kode+' - '+datas.costcenter.deskripsi
                            };

                            var newOption = new Option(val.text, val.id, false, false);
                            $('#cost_center_id').append(newOption).trigger('change');
                        }

                        if(datas.type)
                        {
                            var val = {
                                id: datas.type.id,
                                text: datas.type.nama+' - '+datas.parents.deskripsi
                            };

                            var newOption = new Option(val.text, val.id, false, false);
                            $('#type_id').append(newOption).trigger('change');
                        }
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
                <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Absen Manual Karyawan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::open(['route' => ['uploaddivisi'], 'id' => 'form_data_upload', 'files' => true]) }}
            {{ Form::hidden('uploadId',null, ['id' => 'uploadId']) }}
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
                            <a class="btn btn-info btn-xs" href="{{route('app.files', 'file_temp_divisi')}}" target="_blank"><i class="fa fa-download"></i>Template Document</a>
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
<div class="modal fade" id="modal-form">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title">Form Divisi</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="form_data" action="{{route('savedivisi')}}" accept-charset="UTF-8" >
            {{csrf_field()}}
            <input type="hidden" name="id" id="id">
        <div class="modal-body">           
                
                <div class="form-group">
                    <label for="kode">Kode Divisi</label>
                    <input type="text" class="form-control" id="kode" name="kode" placeholder="Kode Divisi">
                </div>
                <div class="form-group">
                    <label for="kode">Nama Divisi</label>
                    <input type="text" class="form-control" id="deskripsi" name="deskripsi" placeholder="Nama Divisi">
                </div>
                <div class="form-group">
                    <label for="kode">Parent Divisi</label>
                    <select name="parent_id" id="parent_id" class="form-control select2" style="width:100%"></select>
                </div>
                <div class="form-group">
                    <label for="kode">Cost Center</label>
                    <select name="cost_center_id" id="cost_center_id" class="form-control select2" style="width:100%"></select>
                </div>
                <div class="form-group">
                    <label for="kode">Tipe Cost Center</label>
                    <select name="type_id" id="type_id" class="form-control select2" style="width:100%"></select>
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
                <label for="txtSearch">Kode / Nama</label>
                <input type="text" class="form-control" name="txtSearch" id="txtSearch" placeholder="Kode/Nama">                  
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
            <button class="btn btn-xs btn-warning" alt="Upload" data-toggle="modal" data-target="#modal-form-upload"><i class="fa fa-upload"></i>&nbsp;Upload</button>
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
                        <th>Kode Divisi</th>
                        <th>Nama Divisi</th>
                        <th>Parents</th>
                        <th>Cost Center</th>
                        <th>Type</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Buat</th>
                    </tr>
                </thead>
            </table>
        </div>
    <!-- /.card-body -->
</div>

@endsection