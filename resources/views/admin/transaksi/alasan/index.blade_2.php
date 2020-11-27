@extends('adminlte3.app')

@section('title_page')
<p>Transaksi Alasan Karyawan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Transaksi Alasan Karyawan</li>
@endsection

@section('add_css')
    <!-- Datatables -->
    <!--<link rel="stylesheet" href="{{asset('plugins/easyui/themes/default/easyui.css')}}">-->
    <link rel="stylesheet" href="{{asset('plugins/easyui/themes/bootstrap/easyui.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/easyui/themes/icon.css')}}">
   
@endsection

@section('add_js')
    <!-- Datatables -->
    <script src="{{asset('plugins/easyui/jquery.easyui.min.js')}}"></script>
    <script src="{{asset('plugins/easyui/plugins/jquery.edatagrid.js')}}"></script>
    <script src="{{asset('plugins/easyui/plugins/jquery.datetimebox.js')}}"></script>
    <script src="{{asset('plugins/easyui/plugins/jquery.combobox.js')}}"></script>
    <script src="{{asset('plugins/easyui/plugins/jquery.combogrid.js')}}"></script>
    <script>
        var dg = null;
        $(function(e)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
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
            
            dg = $('#dg').edatagrid({
                
            });

        });
            
        function formattedDate(val, row) 
        {
            var d = new Date(val || Date.now()),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }
        
        var pinChange = function(param)
        {
            console.log('djhfdjsh');
        }
        
        var selPin = function(param, success, error)
        {
            var q = param.q || '';
            if (q.length <= 2){return false}
            $.ajax({
                url: '{{route('selkaryawan')}}',
                dataType: 'json',
                type : 'post',
                data: {
                    q: q
                },
                success: function(data){
                    var items = $.map(data.items, function(item,index){
//                        console.log(item);
                        return {
                            id: item.id,
                            nama: item.text,
                            kd_div: item.divisi.kode,
                            nm_div: item.divisi.nama
                        };
                    });
                    success(items);
                },
                error: function(){
                    error.apply(this, arguments);
                }
            });
        }
        var selectPin = function(param)
        {
            if ($('#dg').data('datagrid'))
            {
                var selectedrow = dg.edatagrid("getSelected");
                var i = dg.edatagrid("getRowIndex", selectedrow);
                if(typeof(param) !== 'undefined')
                {
                    $('#dg').edatagrid('updateRow', 
                    {
                        index:i, 
                        row:
                        {
                            sDivisikode:param.kd_div,
                            sDivisiNama:param.nm_div
                        }
                    }).change();
                }
            }
        }
    </script>
@endsection

@section('modal_form')
<div class="modal fade" id="modal-form-upload">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-upload"></i>Form Upload Alasan Karyawan</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<!--        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >-->
            {{ Form::open(['route' => ['uploadalasankaryawan'], 'id' => 'form_data_upload', 'files' => true]) }}
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
                            <a class="btn btn-info btn-xs" href="{{route('app.files', 'file_temp_karyawan_alasan')}}" target="_blank"><i class="fa fa-download"></i>Template Document</a>
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
<div class="row">      
    <div class="col-12">
        <table id="dg" title="Transaksi Alasan" style="width:100%;height:500px"
                toolbar="#toolbar" pagination="true" idField="id"
                rownumbers="true" fitColumns="true" singleSelect="true">
            <thead>
                <tr>
                    <th data-options="
                        field:'sTanggal', width:50, editor:'text'
                        ">Tanggal Awal</th>
                    <th class="easyui-combogrid" data-options="
                        field:'sTanggalAkhir', wdith:50, editor:'text'
                        ">Tanggal Akhir</th>
                    <th data-options="
                        field:'sKar', width:150,
                        editor:{
                            type:'combobox',
                            options:{
                                loader: selPin,
                                mode: 'remote',
                                valueField: 'id',
                                textField: 'nama',
                                onClick: selectPin
                            }
                        }
                        ">PIN</th>
                    <th data-options="
                        field:'sDivisiKode', width:50
                        ">Kd Divisi</th>
                    <th data-options="
                        field:'sDivisiNama', width:50
                        ">Nm Divisi</th>
                    <th data-options="
                        field:'sAlasanKode', width:50
                        ">Kd Alasan</th>
                    <th data-options="
                        field:'sAlasanNama', width:50
                        ">Nm Alasan</th>
                    <th data-options="
                        field:'sWaktu', width:50, editor:'text'
                        ">Waktu</th>
                </tr>
            </thead>
        </table>
        <div id="toolbar">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="javascript:$('#dg').edatagrid('addRow')">New</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="javascript:$('#dg').edatagrid('destroyRow')">Destroy</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="javascript:$('#dg').edatagrid('saveRow')">Save</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#dg').edatagrid('cancelRow')">Cancel</a>
        </div>
    </div>  
</div>
@endsection