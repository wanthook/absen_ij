@php
$show = true;
if(config('global.perusahaan_short') == 'AIC')
{
    if(Auth::user()->id != 1 && Auth::user()->id != 9)
    {
        $show = false;
    }
}
@endphp
@extends('adminlte3.app')

@section('title_page')
    <p>Jadwal Day Shift</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Jadwal Day Shift</li>
@endsection

@section('add_css')
    <!-- Datatables -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/extensions//FixedColumns/css/dataTables.fixedColumns.min.css')}}">
    <!-- bootstrap color picker -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/select2/css/select2.min.css')}}">
@endsection

@section('add_js')
    <!-- Datatables -->
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/extensions/FixedColumns/js/dataTables.fixedColumns.min.js')}}"></script>
    <!-- bootstrap color picker -->
    <script src="{{asset('bower_components/admin-lte/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
    <!-- select2 -->
    <script src="{{asset('bower_components/admin-lte/plugins/select2/js/select2.full.min.js')}}"></script>
    <script src="{{asset('js/myjs.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>
    <script>
        let dTable = null;
        $(function(e)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            bsCustomFileInput.init();          
            
            $('#cmdSearch').on('click',function(e)
            {
                dTable.ajax.reload();
            });
            
            $('#warna').colorpicker();
            

            @if($show)
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
                    }
                });
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
            
            $('#tblHari tr td').on('click', function(e)
            {
                let $td = $(this);
                let $inpt = $td.find('input');
                let $divv = $td.find('div');
                let $sel2 = $('#jm_kerja').val();
                let cb = null;

                if($sel2)
                {
                    getDataSelector($sel2, function(data)
                    {
                        let par = data.items[0];
                        $inpt.val($sel2);
                        $divv.html(strSel(par));
                    });
                }
                else
                {
                    Toast.fire({
                        type: 'error',
                        title: 'Mohon diisi jam kerja nya'
                    });
                }
            });
            
            $('#modal-form').on('hidden.bs.modal', function (e) 
            {
                document.getElementById("form_data").reset(); 
                $('#jm_kerja').val('').trigger('change');
                $('.hri').each(function(index, value)
                {
                    $(this).html("");
                });
                dTable.ajax.reload();
            });
            @endif
            dTable = $('#dTable').DataTable({
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": true,
                "serverSide": true,
                "autoWidth": false,                
                "scrollX" : true,
                "scrollY" : true,
		"scrollCollapse" : true,
                fixedHeader : true,
                fixedColumns : {
                    leftColumns: 2
                },
                "lengthMenu": [10, 50, 100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtjadwalday') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.txtSearch     = $('#txtSearch').val();
                    }
                },
                "columns"           :
                [
                    { data    : "action"},
                    { data    : "kode", name : "kode" },
                    { data    : function(data)
                    {
                        return jadwalSelectorTable(data, 1);
                    }},
                    { data    : function(data)
                    {
                        return jadwalSelectorTable(data, 2);
                    }},
                    { data    : function(data)
                    {
                        return jadwalSelectorTable(data, 3);
                    }},
                    { data    : function(data)
                    {
                        return jadwalSelectorTable(data, 4);
                    }},
                    { data    : function(data)
                    {
                        return jadwalSelectorTable(data, 5);
                    }},
                    { data    : function(data)
                    {
                        return jadwalSelectorTable(data, 6);
                    }},
                    { data    : function(data)
                    {
                        return jadwalSelectorTable(data, 7);
                    }},
                    { data    : "created_by.name", name : "created_by" },
                    { data    : "created_at", name : "created_at" }              

                ],
                "drawCallback": function( settings, json ) 
                {
                    @if($show)
                    $('.delrow').on('click',function(e)
                    {
                        if(confirm('Apakah Anda yakin menghapus data ini?'))
                        {
                            var _this	= $(this);
                            var datas       = dTable.row(_this.parents('tr')).data();
                            
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
                        var _this	= $(this);
                        var datas = dTable.row(_this.parents('tr')).data();
                        $('#id').val(datas.id);
                        $('#kode').val(datas.kode);
                        if(typeof(datas.jadwal_kerja[0]) != "undefined")
                        {
                            $('#senin').val(datas.jadwal_kerja[0].id);
                            $('#divSenin').html(strSel(datas.jadwal_kerja[0]));
                        }
                        if(typeof(datas.jadwal_kerja[1]) != "undefined")
                        {
                            $('#selasa').val(datas.jadwal_kerja[1].id);
                            $('#divSelasa').html(strSel(datas.jadwal_kerja[1]));
                        }
                        if(typeof(datas.jadwal_kerja[2]) != "undefined")
                        {
                            $('#rabu').val(datas.jadwal_kerja[2].id);
                            $('#divRabu').html(strSel(datas.jadwal_kerja[2]));
                        }
                        if(typeof(datas.jadwal_kerja[3]) != "undefined")
                        {
                            $('#kamis').val(datas.jadwal_kerja[3].id);
                            $('#divKamis').html(strSel(datas.jadwal_kerja[3]));
                        }
                        if(typeof(datas.jadwal_kerja[4]) != "undefined")
                        {
                            $('#jumat').val(datas.jadwal_kerja[4].id);
                            $('#divJumat').html(strSel(datas.jadwal_kerja[4]));
                        }
                        if(typeof(datas.jadwal_kerja[5]) != "undefined")
                        {
                            $('#sabtu').val(datas.jadwal_kerja[5].id);
                            $('#divSabtu').html(strSel(datas.jadwal_kerja[5]));
                        }
                        if(typeof(datas.jadwal_kerja[6]) != "undefined")
                        {
                            $('#minggu').val(datas.jadwal_kerja[6].id);
                            $('#divMinggu').html(strSel(datas.jadwal_kerja[6]));
                        }

                    });
                    @endif
                    
                }
            });           

//            new $.fn.dataTable.FixedColumns( dTable, {
//                    
//            } );

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

            var jadwalSelectorTable = function(data, days)
            {
                var jadwalKerja = data.jadwal_kerja;

                for(var i = 0 ; i < jadwalKerja.length ; i++)
                {
                    if(jadwalKerja[i].pivot.day == days)
                    {
                        return '<span class="badge" style="background-color:'+jadwalKerja[i].warna+'">'+jadwalKerja[i].kode+'</span><span class="badge bg-success">'+jadwalKerja[i].jam_masuk+' - '+jadwalKerja[i].jam_keluar+'</span>';
                    }
                }

                return null;
            }

        });

        var getDataSelector = function(id, callback)
        {
            $.ajax({
                type: 'post',
                url: "{{route('seljamkerja')}}",
                data:{id:id},
                dataType: 'json',
                success: function(data)
                {
                    callback(data);
                }
            });
        }

        var strSel = function(par)
        {
            return '<span class="badge" style="background-color:'+par.warna+'">'+par.kode+'</span>&nbsp;<span class="badge bg-success">'+par.jam_masuk+' - '+par.jam_keluar+'</span>';
        }
    </script>
@endsection

@section('modal_form')
@if($show)
<div class="modal fade" id="modal-form">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title">Form Jadwal Kerja Day Shift</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >
            {{csrf_field()}}
            <input type="hidden" name="id" id="id">
            <div class="modal-body">   
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="kode">Kode Jadwal</label>
                                <input type="text" class="form-control" id="kode" name="kode" placeholder="Kode Jam Kerja">
                            </div>
                        </div>                        
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="kode">Jam Kerja</label>
                                <select class="form-control select2" style="width: 100%;" id="jm_kerja">
                                    
                                </select>
                            </div>
                        </div>                        
                    </div>
                </div>
                <table class="table table-bordered" id="tblHari">
                    <tbody>
                        <tr>
                            <td>Senin<div id="divSenin" class="hri"></div><input type="hidden" name="senin" id="senin"></td>
                            <td>Selasa<div id="divSelasa" class="hri"></div><input type="hidden" name="selasa" id="selasa"></td>
                            <td>Rabu<div id="divRabu" class="hri"></div><input type="hidden" name="rabu" id="rabu"></td>
                            <td>Kamis<div id="divKamis" class="hri"></div><input type="hidden" name="kamis" id="kamis"></td>
                        </tr>
                        <tr>
                            <td>Jumat<div id="divJumat" class="hri"></div><input type="hidden" name="jumat" id="jumat"></td>
                            <td>Sabtu<div id="divSabtu" class="hri"></div><input type="hidden" name="sabtu" id="sabtu"></td>
                            <td>Minggu<div id="divMinggu" class="hri"></div><input type="hidden" name="minggu" id="minggu"></td>
                        </tr>
                    </tbody>
                </table>
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

<div class="modal fade" id="modal-form-upload">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Jadwal Kerja Day Shift</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<!--        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >-->
            {{ Form::open(['route' => ['uploadjadwalday'], 'id' => 'form_data_upload', 'files' => true]) }}
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
                            <a class="btn btn-info btn-xs" href="{{route('app.files', 'file_temp_dayshift')}}" target="_blank"><i class="fa fa-download"></i>Template Document</a>
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
@endif
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
                <label for="txtSearch">Kode</label>
                <input type="text" class="form-control" name="txtSearch" id="txtSearch" placeholder="Kode">                  
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
            @if($show)
            <button class="btn btn-xs btn-warning" alt="Upload" data-toggle="modal" data-target="#modal-form-upload"><i class="fa fa-upload"></i>&nbsp;Upload</button>
            <button class="btn btn-xs btn-success" alt="Tambah" data-toggle="modal" data-target="#modal-form"><i class="fa fa-plus-circle"></i>&nbsp;Tambah</button>
            @endif
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
                        <th>&nbsp;</th>
                        <th>Kode</th>
                        <th>Senin</th>
                        <th>Selasa</th>
                        <th>Rabu</th>
                        <th>Kamis</th>
                        <th>Jumat</th>
                        <th>Sabtu</th>
                        <th>Minggu</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Buat</th>
                    </tr>
                </thead>
            </table>
        </div>
    <!-- /.card-body -->
</div>
@endsection