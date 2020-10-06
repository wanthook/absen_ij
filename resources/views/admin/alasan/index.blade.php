@extends('adminlte3.app')

@section('title_page')
<p>Master Alasan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Master Alasan</li>
@endsection

@section('add_css')
    <!-- Datatables -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
    <!-- bootstrap color picker -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
@endsection

@section('add_js')
    <!-- Datatables -->
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
    <!-- bootstrap color picker -->
    <script src="{{asset('bower_components/admin-lte/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>
    <script>
        var dTable = null;
        $(function(e)
        {
            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $('#warna').colorpicker();
            bsCustomFileInput.init();
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
                    "url"       : "{{ route('dtalasan') }}",
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
                    { data    : function(data)
                    {
                        if(data.warna)
                        {
                            return '<span class="label" style="background-color: '+data.warna+';">'+data.warna+'</span>';  
                        }
                        else
                        {
                            return "";
                        }
                    } },
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
                                url         : "{{ route('delalasan') }}",
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
//                        console.log(datas);
                        $('#id').val(datas.id);
                        $('#kode').val(datas.kode);
                        $('#deskripsi').val(datas.deskripsi);
                        $('#warna').val(datas.warna);
                        if(datas.libur == 'Y')
                            $('#libur').prop('checked');
                        if(datas.show == 'N')
                            $('#show').prop('checked');
                    });
                    
                    
                }
            });
            
        });
    </script>
@endsection

@section('modal_form')
<div class="modal fade" id="modal-form">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title">Form Alasan</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="form_data" action="{{route('savealasan')}}" accept-charset="UTF-8" >
            {{csrf_field()}}
            <input type="hidden" name="id" id="id">
        <div class="modal-body">            
                
                <div class="form-group">
                    <label for="kode">Kode Alasan</label>
                    <input type="text" class="form-control" id="kode" name="kode" placeholder="Kode Alasan">
                </div>
                <div class="form-group">
                    <label for="deskripsi">Nama Alasan</label>
                    <input type="text" class="form-control" id="deskripsi" name="deskripsi" placeholder="Nama Alasan">
                </div>
                <div class="form-group">
                    <label for="warna">Warna</label>
                    <input type="text" class="form-control" id="warna" name="warna" readonly>
                </div>  
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="libur" name="libur" value="Y">
                        <label class="custom-control-label" for="libur">Libur</label>
                    </div>
                </div>   
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="show" name="show" value="N">
                        <label class="custom-control-label" for="show">Tidak Tampil</label>
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
                        <th>Aksi</th>
                        <th>Kode Alasan</th>
                        <th>Nama Alasan</th>
                        <th>Warna</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Buat</th>
                    </tr>
                </thead>
            </table>
        </div>
    <!-- /.card-body -->
</div>

@endsection