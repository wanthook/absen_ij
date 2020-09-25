@extends('adminlte3.app')

@section('title_page')
    <p>Master Jam Kerja</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
    <li class="breadcrumb-item active">Master Jam Kerja</li>
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

            $('#timepicker,#timepicker2').datetimepicker({
                format: 'HH:mm'
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
                    "url"       : "{{ route('dtjamkerja') }}",
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
                    { data    : "jam_masuk", name : "jam_masuk" },
                    { data    : "jam_keluar", name : "jam_keluar" },
                    { data    : function(data)
                    {
                        if(data.libur == '1')
                        {
                            return '<span class="label label-success">Libur</span>';
                        }
                        return '<span class="label label-primary">Masuk</span>';                        
                    } },
                    { data    : function(data)
                    {
                        if(data.pendek == '1')
                        {
                            return '<span class="label label-success">Pendek</span>';
                        }
                        return '<span class="label label-primary">Normal</span>';                        
                    } },
                    { data    : function(data)
                    {
                        if(data.istirahat == '1')
                        {
                            return '<span class="label label-success">1/2 Jam</span>';
                        }
                        return '<span class="label label-primary">1 Jam</span>';                        
                    } },
                    { data    : function(data)
                    {
                        
                        return '<span class="label" style="background-color: '+data.warna+';">'+data.warna+'</span>';                        
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
                                url         : "{{ route('deljamkerja') }}",
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
                        $('#jam_masuk').val(datas.jam_masuk);
                        $('#jam_keluar').val(datas.jam_keluar);
                        // $('#libur').val(datas.libur);
                        if(datas.libur == '1')
                        {
                           $("#libur1").prop('checked',true);
                        }
                        else
                        {
                            $("#libur2").prop('checked',true);
                        }

                        if(datas.pendek == '1')
                        {
                           $("#pendek1").prop('checked',true);
                        }
                        else
                        {
                            $("#pendek2").prop('checked',true);
                        }

                        if(datas.istirahat == '1')
                        {
                        $("#istirahat1").prop('checked',true);
                        }
                        else
                        {
                            $("#istirahat2").prop('checked',true);
                        }
                        $('#warna').val(datas.warna);
                    });
                    
                    
                }
            });
            
        });
    </script>
@endsection

@section('modal_form')
<div class="modal fade" id="modal-form">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title">Form Jam Kerja</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="form_data" action="{{route('savejamkerja')}}" accept-charset="UTF-8" >
            {{csrf_field()}}
            <input type="hidden" name="id" id="id">
        <div class="modal-body">   
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="kode">Kode Jam Kerja</label>
                                <input type="text" class="form-control" id="kode" name="kode" placeholder="Kode Jam Kerja">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="warna">Warna</label>
                                <input type="text" class="form-control" id="warna" name="warna" readonly>
                            </div>  
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <label for="jam_masuk">Jam Masuk</label>
                            <div class="input-group date" id="timepicker" data-target-input="nearest">
                                <input type="text" name="jam_masuk" id="jam_masuk" class="form-control datetimepicker-input" data-target="#timepicker"/>
                                <div class="input-group-append" data-target="#timepicker" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <label for="jam_keluar">Jam Keluar</label>
                            <div class="input-group date" id="timepicker2" data-target-input="nearest">
                                <input type="text" name="jam_keluar" id="jam_keluar" class="form-control datetimepicker-input" data-target="#timepicker2"/>
                                <div class="input-group-append" data-target="#timepicker2" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Lokasi JamKerja"> -->
                </div>    

                <div class="form-group clearfix">
                    <div class="row">
                        <div class="col-4">
                            <label for="libur">Jam Kerja Libur : </label>
                            <div>
                                <div class="icheck-primary d-inline">
                                    <input type="radio" id="libur1" name="libur" value="1">
                                    <label for="libur1">Libur</label>
                                </div>
                                <div class="icheck-primary d-inline">
                                    <input type="radio" id="libur2" name="libur" value="0">
                                    <label for="libur2">Tidak Libur</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <label for="libur">Tipe Jam Kerja : </label>
                            <div>
                                <div class="icheck-primary d-inline">
                                    <input type="radio" id="pendek1" name="pendek" value="1">
                                    <label for="pendek1">Pendek</label>
                                </div>
                                <div class="icheck-primary d-inline">
                                    <input type="radio" id="pendek2" name="pendek" value="0">
                                    <label for="pendek2">Normal</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <label for="istirahat">Tipe Istirahat : </label>
                            <div>
                                <div class="icheck-primary d-inline">
                                    <input type="radio" id="istirahat1" name="istirahat" value="1">
                                    <label for="istirahat1">1/2 Jam</label>
                                </div>
                                <div class="icheck-primary d-inline">
                                    <input type="radio" id="istirahat2" name="istirahat" value="0">
                                    <label for="istirahat2">1 Jam</label>
                                </div>
                            </div>
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
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Ket. Libur</th>
                        <th>Tipe Kerja</th>
                        <th>Waktu Istirahat</th>
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