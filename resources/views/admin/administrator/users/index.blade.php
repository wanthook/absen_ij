@extends('adminlte3.app')

@section('title_page')
<p>Administrator User</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Administrator User</li>
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
            
            $('#modal-form').on('hidden.bs.modal', function (e) 
            {
                document.getElementById("form_data").reset(); 
                dTable.ajax.reload();
            });
            
            $('#type_id').select2({
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('seladminuserparent')}}",
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
            
            $('#module').select2({
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('seladminmoduletree')}}",
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
            
            $('#perusahaan_id').select2({
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
            
            dTable = $('#dTable').DataTable({
                "sPaginationType": "full_numbers",
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": true,
                "serverSide": true,
                "autoWidth": false,
                "lengthMenu": [100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtadminuser') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.search     = $('#txtSearch').val();
                    }
                },
                "columnDefs"    :[
                {
                    "targets": 0,
                    "className":      'btndel',
                    "orderable":      false,
                    "data"     :           null,
                    "defaultContent": '<button class="btn btn-sm btn-primary btnedit" data-toggle="modal" data-target="#modal-form"><i class="fa fa-edit"></i></button><button class="btn btn-sm btn-danger btndelete"><i class="fa fa-eraser"></i></button>'
                },
                {
                        targets : 'tusername',
                        data: "username"
                },
                {
                        targets : 'tnama',
                        data: "name"
                },
                {
                        targets : 'temail',
                        data: "email"
                },
                {
                        targets : 'ttype',
                        data: "type.nama"
                },
                {
                        targets : 'tperusahaan',
                        data: "perusahaan.kode"
                }]
            });
            $('#dTable tbody').on('click', '.btndelete', function () 
            {
                var tr = $(this).closest('tr');
                var row = dTable.row( tr );
                var datas = row.data();
                
                if(confirm('Apakah Anda yakin menghapus data ini?'))
                {
                    $.ajax(
                    {
                        url         : '{{route("deleteadminuser")}}',
                        dataType    : 'JSON',
                        type        : 'POST',
                        data        : {sId : datas.id} ,
                        beforeSend  : function(xhr)
                        {
    //                        $('#loadingDialog').modal('show');
                            toastOverlay.fire({
                                type: 'warning',
                                title: 'Sedang memproses hapus data',
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

                    return false;
                }
            });
            $('#dTable tbody').on('click', '.btnedit', function () 
            {
                var tr = $(this).closest('tr');
                var row = dTable.row( tr );
                var datas = row.data();
                
                $('#id').val(datas.id);
                $('#username').val(datas.username);
                $('#name').val(datas.name);
                $('#email').val(datas.email);
                                
                var newOption = new Option(datas.type.nama, datas.type.id, false, false);
                $('#type_id').append(newOption).trigger('change');
                
                var newOption = new Option(datas.perusahaan.kode+' - '+datas.perusahaan.deskripsi, datas.perusahaan.id, false, false);
                $('#perusahaan_id').append(newOption).trigger('change');
                
                var $mod = $('#module');
                var $grp = [];
                $.each(datas.modules, function(key, val)
                {
//                    console.log(key)
                    if(val.parent_id == null)
                    {
                        $grp = $('<optgroup label="' + val.nama + '" />');
                        $mod.append($grp);
                    }
                    else
                    {
                        var newOption = new Option(val.nama, val.id, false, true);
                        $grp.append(newOption);
//                        $('<option />').html(this.name).appendTo(group);
                    }
                });
                $mod.trigger('change');
//                console.log($grp);
                
            });
        });
    </script>
@endsection

@section('modal_form')
<div class="modal fade" id="modal-form">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title">Form User</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="form_data" action="{{route('saveadminuser')}}" accept-charset="UTF-8" >
            {{csrf_field()}}
            <input type="hidden" name="id" id="id">
            <div class="modal-body">
            <div class="row">
                <div class="col-12">                                
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control form-control-sm" id="username" name="username">
                    </div>
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text" class="form-control form-control-sm" id="name" name="name">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control form-control-sm" id="password" name="password" placeholder="Password">
                        <input type="password" class="form-control form-control-sm" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password">
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="text" class="form-control form-control-sm" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="type_id">Tipe User</label>
                        <select name="type_id" id="type_id" class="form-control form-control-sm select2" style="width:100%"></select>
                    </div>
                    <div class="form-group">
                        <label for="perusahaan_id">Perusahaan</label>
                        <select name="perusahaan_id" id="perusahaan_id" class="form-control form-control-sm select2" style="width:100%"></select>
                    </div>
                    <div class="form-group">
                        <label for="module">Module</label>
                        <select name="module[]" multiple="multiple" id="module" class="form-control form-control-sm select2" style="width:100%"></select>
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
                        <th></th>
                        @php
                        $lstTbl = ['tusername' => 'Username', 
                        'tnama' => 'Nama', 
                        'temail' => 'Email',
                        'ttype' => 'Tipe User',
                        'tperusahaan' => 'Perusahaan'];
                        
                        foreach($lstTbl as $k => $v)
                        {
                            echo '<th class="'.$k.'">'.$v.'</th>';
                        }
                        @endphp
                    </tr>
                </thead>
            </table>
        </div>
    <!-- /.card-body -->
</div>

@endsection