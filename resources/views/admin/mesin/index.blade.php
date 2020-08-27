@extends('adminlte3.app')

@section('title_page')
    <p>Master Mesin</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
    <li class="breadcrumb-item active">Master Mesin</li>
@endsection

@section('add_css')
    <!-- Datatables -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
@endsection

@section('add_js')
    <!-- Datatables -->
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
    
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
                "columns"           :
                [
                    { data    : "action", orderable: false, searchable: false},
                    { data    : "kode", name : "kode" },
                    { data    : "lokasi", name : "lokasi" },
                    { data    : "merek", name : "merek" },
                    { data    : "keterangan", name : "keterangan" },
                    { data    : "ip", name : "ip" },
                    { data    : function(rows)
                        {
                            if(rows.ping_status == 0)
                            {
                                return '<small class="text-success mr-1"><i class="fas fa-arrow-up"></i>Up</small>';
                            }
                            else
                            {
                                return '<small class="text-danger mr-1"><i class="fas fa-arrow-down"></i>Down</small>';
                            }
                        }
                    }, 
                    { data    : "key", name : "key" },
                    { data    : "lastlog", name : "lastlog" },
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
                                url         : "{{ route('delmesin') }}",
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
                        $('#lokasi').val(datas.lokasi);
                        $('#merek').val(datas.merek);
                        $('#keterangan').val(datas.keterangan);
                        $('#api_address').val(datas.api_address);
                        $('#lastlog').val(datas.merek);
                        $('#ip').val(datas.ip);
                        $('#key').val(datas.key);
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
            <h4 class="modal-title">Form Mesin</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="form_data" action="{{route('savemesin')}}" accept-charset="UTF-8" >
            {{csrf_field()}}
            <input type="hidden" name="id" id="id">
        <div class="modal-body">            
                
                <div class="form-group">
                    <label for="kode">Kode Mesin</label>
                    <input type="text" class="form-control" id="kode" name="kode" placeholder="Kode Mesin">
                </div>
                <div class="form-group">
                    <label for="lokasi">Lokasi Mesin</label>
                    <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Lokasi Mesin">
                </div>     
                <div class="form-group">
                    <label for="merek">Merek Mesin</label>
                    <input type="text" class="form-control" id="merek" name="merek" placeholder="Merek Mesin">
                </div>         
                <div class="form-group">
                    <label for="keterangan">Keterangan Mesin</label>
                    <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Keterangan Mesin">
                </div>            
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <label for="ip">IP Mesin</label>
                            <input type="text" class="form-control" id="ip" name="ip" placeholder="xxx.xxx.xxx.xxx" data-mask>
                        </div>
                        <div class="col-6">
                            <label for="key">Comm Key</label>
                            <input type="text" class="form-control" id="key" name="key" placeholder="Key Mesin">
                        </div>
                    </div>
                </div>        
                <div class="form-group">
                    <label for="keterangan">API</label>
                    <input type="text" class="form-control" id="api_address" name="api_address" placeholder="API Mesin">
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
                        <th>Lokasi</th>
                        <th>Merek</th>
                        <th>Keterangan</th>
                        <th>IP Mesin</th>
                        <th>Status Mesin</th>
                        <th>Key Mesin</th>
                        <th>Log Terakhir</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Buat</th>
                    </tr>
                </thead>
            </table>
        </div>
    <!-- /.card-body -->
</div>

@endsection