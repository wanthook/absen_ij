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
    <script src="{{asset('plugins/easyui/plugins/jquery.combogrid.js')}}"></script> <!-- moment -->
    <script src="{{asset('bower_components/admin-lte/plugins/moment/moment.min.js')}}"></script>
    <!-- date-range-picker -->
    <script src="{{asset('bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js')}}"></script>
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
            
            $('#sTanggal').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                minYear: 2019,
                maxYear: parseInt(moment().format('YYYY'),10),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });
            
            $('#sTanggal').on('change', function(e)
            {
                $('#dg').edatagrid('reload');
            });
            
            var toastOverlay = Swal.mixin({
                position: 'center',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false
            });
            
            dg = $('#dg').edatagrid({
                method: 'post',
                queryParams:{
                    sTanggal: $('#sTanggal').val()
                },
                url: '{{ route('tabletalasankaryawan') }}',
                toolbar: '#toolbar',
                pagination: 'true',
                idField: 'id',
                rownumbers: 'true',
                fitColumns: 'true',
                singleSelect: 'true'
            });

        });
        
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
        
        var selPinId = function(param, success, error)
        {
            var q = param.q || '';
            if (q.length <= 2){return false}
            $.ajax({
                url: '{{route('selkaryawan')}}',
                dataType: 'json',
                type : 'post',
                data: {
                    id: q
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
        
        var selAlasan = function(param, success, error)
        {
            var q = param.q || '';
            if (q.length <= 2){return false}
            $.ajax({
                url: '{{route('selalasan')}}',
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
                            nama: item.kode+' - '+item.deskripsi
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
            var tr = $(this).closest('tr.datagrid-row');
            var index = parseInt(tr.attr('datagrid-row-index'));
            if(typeof(param) !== 'undefined')
            {
                var editor = $('#dg').edatagrid('getEditors', index);
                $(editor[1].target).textbox('setValue',param.divisi.kode);
                $(editor[2].target).textbox('setValue',param.divisi.nama);
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
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label class="label" for="dd">Tanggal</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="far fa-calendar-alt"></i>
                            </span>
                        </div>
                        {{ Form::text('sTanggal', null, ['id' => 'sTanggal', 'class' => 'form-control form-control-sm float-right']) }}
                    </div>
                </div>                
            </div>
        </div>
    </div>
    <div class="col-12">
        <table id="dg" title="Transaksi Alasan" style="width:100%;height:500px">
            <thead>
                <tr>
                    <th data-options="
                        field:'sKar', width:150,
                        formatter:function(value,row){
                            return row.sKarText;
                        },
                        editor:{
                            type:'combobox',
                            options:{
                                url:'{{route('selectkaryawan')}}',
                                method: 'post',
                                mode: 'remote',
                                valueField: 'sKar',
                                textField: 'sKarText',
                                onSelect: selectPin
                            },
                            
                        }
                        ">PIN</th>
                    <th data-options="
                        field:'sDivisiKode', width:50, 
                        editor:{ 
                            type: 'textbox', 
                            options:{
                                readonly: true
                            }
                        }
                        ">Kd Divisi</th>
                    <th data-options="
                        field:'sDivisiNama', width:50, editor:{ type: 'textbox', options:{readonly: true}}
                        ">Nm Divisi</th>
                    <th data-options="
                        field:'sAlasan', width:150,
                        formatter:function(value,row){
                            return row.sAlasanKode+' - '+row.sAlasanNama;
                        },
                        editor:{
                            type:'combobox',
                            options:{
                                loader: selAlasan,
                                mode: 'remote',
                                valueField: 'id',
                                textField: 'nama'
                            },
                            
                        }
                        ">Alasan</th>
                    <th data-options="
                        field:'sWaktu', width:50, editor:'text'
                        ">Waktu</th>
                </tr>
            </thead>
        </table>
        <div id="toolbar">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="javascript:$('#dg').edatagrid('addRow',0)">New</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="javascript:$('#dg').edatagrid('destroyRow')">Destroy</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="javascript:$('#dg').edatagrid('saveRow')">Save</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#dg').edatagrid('cancelRow')">Cancel</a>
        </div>
    </div>  
</div>
@endsection