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
    <link rel="stylesheet" href="{{asset('plugins/easyui/themes/color.css')}}">
   
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
    <script src="{{asset('bower_components/admin-lte/plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>
    <script>
        var dg = null;
        var dgRange = null;
        var editIndex = undefined;
        var editRangeIndex = undefined;
        
        $(function(e)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            bsCustomFileInput.init(); 
            
            let Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
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
                            }
                            
                        }
                        dTableKar.ajax.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        /* implementation goes here */ 
                        toastOverlay.close();
                        console.log(jqXHR.responseText);
                    }
                });
            });
            
            $('#cmdSave').on('click', function(e)
            {
                e.preventDefault();
                
                var rows = dg.datagrid('getRows');
//                console.log(rows);
                var datas = [];
                
                $.each(rows, function(i, row)
                {
                    if(row.sFlag)
                    {
                        if(row.sKar && row.sAlasan)
                        {
                            datas[i] = {
                                sKar : row.sKar, 
                                sAlasan : row.sAlasan, 
                                sWaktu : row.sWaktu, 
                                sKeterangan : row.sKeterangan,
                                sAlasanOld : row.sAlasanOld,
                            };
                        }
                    }
                });
                
                var obj = {sTanggal : $('#sTanggal').val(), sData : datas}
                if(datas.length > 0)
                {
                    $.ajax(
                    {
                        url         : '{{route('savealasankaryawan2')}}',
                        dataType    : 'JSON',
                        type        : 'POST',
                        contentType: "application/json; charset=utf-8",
                        data        :  JSON.stringify(obj),
                        processData: false,
                        beforeSend  : function(xhr)
                        {
    //                        $('#loadingDialog').modal('show');
                            toastOverlay.fire({
                                type: 'warning',
                                title: 'Sedang memproses data',
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

//                                $('#sAlasanOld').val(null);
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
//                            dTableKar.ajax.reload();
                        },
                        error: function(jqXHR, textStatus, errorThrown) { 
                            /* implementation goes here */ 
                            toastOverlay.close();
                            console.log(jqXHR.responseText);
                        }
                    });
                }
                else
                {
                    Toast.fire({
                        type: 'error',
                        title: 'Tidak ada data yang harus disimpan.'
                    });
                }
            });
            
            $('#cmdSaveRange').on('click', function(e)
            {
                e.preventDefault();
                
                var rows = dgRange.datagrid('getRows');
//                console.log(rows);
                var datas = [];
                
                $.each(rows, function(i, row)
                {
                    if(row.sRangeFlag)
                    {
                        if(row.sRangeKar && row.sRangeAlasan && row.sTanggalAwal && row.sTanggalAkhir)
                        {
                            datas[i] = {
                                sTanggalAwal : row.sTanggalAwal, 
                                sTanggalAkhir : row.sTanggalAkhir, 
                                sKar : row.sRangeKar, 
                                sAlasan : row.sRangeAlasan, 
                                sWaktu : row.sRangeWaktu, 
                                sKeterangan : row.sRangeKeterangan,
                                sAlasanOld : row.sRangeAlasanOld,
                            };
                        }
                    }
                });
                
                var obj = {sData : datas};

                if(datas.length > 0)
                {
                    $.ajax(
                    {
                        url         : '{{route('savealasankaryawanrange')}}',
                        dataType    : 'JSON',
                        type        : 'POST',
                        contentType: "application/json; charset=utf-8",
                        data        :  JSON.stringify(obj),
                        processData: false,
                        beforeSend  : function(xhr)
                        {
    //                        $('#loadingDialog').modal('show');
                            toastOverlay.fire({
                                type: 'warning',
                                title: 'Sedang memproses data',
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
                                }

                            }
//                            dTableKar.ajax.reload();
                        },
                        error: function(jqXHR, textStatus, errorThrown) { 
                            /* implementation goes here */ 
                            toastOverlay.close();
                            console.log(jqXHR.responseText);
                        }
                    });
                }
            });
            
            $('#cmdHapus').on('click', function(e)
            {
                var idx = $('#dg').edatagrid('getSelected');
                
                if(idx)
                {
                    if(!idx.isNewRecord)
                    {
                        if(confirm('Apakah anda ingin menghapus data '+idx.sKarText+', dengan alasan '+idx.sAlsText+' ?'))
                        {
                            $.ajax(
                            {
                                url         : '{{route("delalasankaryawan")}}',
                                dataType    : 'JSON',
                                type        : 'POST',
                                data        : {sTanggal : idx.tanggal, sKar : idx.sKar, sAlasan: idx.sAlasan} ,
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
                                        }

                                    }
                                    reloadTable();
                                }
                            });
                        }
                    }
                }
            });
            
            $('#cmdHapusRange').on('click', function(e)
            {
                var idx = $('#dgRange').edatagrid('getSelected');
                
                if(idx)
                {
                    if(!idx.isNewRecord)
                    {
                        if(confirm('Apakah anda ingin menghapus Alasan Range data '+idx.sRangePin+', dengan alasan '+idx.sRangeAlasanNama+' ?'))
                        {
                            $.ajax(
                            {
                                url         : '{{route("delalasankaryawanrange")}}',
                                dataType    : 'JSON',
                                type        : 'POST',
                                data        : {sTanggalAwal : idx.sTanggalAwal, sTanggalAkhir : idx.sTanggalAkhir, sKar : idx.sRangeKar, sAlasan: idx.sRangeAlasan} ,
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
                                        }

                                    }
                                    reloadTableRange();
                                }
                            });
                        }
                    }
                }
            });
            
            var toastOverlay = Swal.mixin({
                position: 'center',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false
            });
            
            $('#sTanggal').on('change', function()
            {
                reloadTable();
            });
            
            $('#sRangeTanggal').on('change', function()
            {
                reloadTableRange();
            });
            
            dg = $('#dg').edatagrid({
                method: 'post',
                queryParams:{
                    sTanggal: $('#sTanggal').val()
                },
                url: '{{ route('tabletalasankaryawan') }}',
                toolbar: '#toolbar',
                idField: 'id',
                rownumbers: 'true',
                fitColumns: 'true',
                singleSelect: 'true',
                onAdd: onAdd,
                // onEndEdit: onEndEdit,
                onClickCell: onClickCell,
                onBeginEdit: function(index,row){
                    var dg = $(this);
                    var editors = dg.edatagrid('getEditors',index);

                    var sKar = dg.edatagrid('getEditor', {index:index, field:'sKar'});
                    var sPin = dg.edatagrid('getEditor', {index:index, field:'sPin'});
                    var sKarNama = dg.edatagrid('getEditor', {index:index, field:'sKarNama'});

                    var sAlasanKode = dg.edatagrid('getEditor', {index:index, field:'sAlasanKode'});
                    var sAlasanNama = dg.edatagrid('getEditor', {index:index, field:'sAlasanNama'});
                    var sAlasan = dg.edatagrid('getEditor', {index:index, field:'sAlasan'});

                    var sWaktu = dg.edatagrid('getEditor', {index:index, field:'sWaktu'});  
                    var sKeterangan = dg.edatagrid('getEditor', {index:index, field:'sKeterangan'}); 

                    var sFlag = dg.edatagrid('getEditor', {index:index, field:'sFlag'});    

                    $(sPin.target).textbox('textbox').bind('blur',function(e){
                        var q = $(this).val();
                        if(q != '')
                        {
                            $.ajax({
                                url : '{{route('selectkaryawan')}}',
                                method : 'post',
                                dataType: 'json',
                                data: {
                                    pin: q
                                },
                                success: function(data)
                                {
                                    
                                    if(data.length > 0)
                                    {
                                        var items = $.map(data, function(item, index)
                                        {
                                            $(sKar.target).textbox('setValue',item.sKar);
                                            $(sKarNama.target).textbox('setValue',item.sKarNama);
                                        });
                                        $(sAlasanKode.target).textbox('textbox').focus();
                                        $(sFlag.target).textbox('setValue','1');
                                    }
                                    else
                                    {
                                        Toast.fire({
                                            type: 'error',
                                            title: 'Karyawan tidak ada di database.'
                                        });
                                        $(sKar.target).textbox('setValue','');
                                        $(sKarNama.target).textbox('setValue','');
                                        $(sPin.target).textbox('textbox').focus();
                                    }
                                }
                            });
                        }
                        else
                        {
                            Toast.fire({
                                type: 'error',
                                title: 'Karyawan tidak ada di database.'
                            });
                            $(sKar.target).textbox('setValue','');
                            $(sKarNama.target).textbox('setValue','');
                            $(sPin.target).textbox('textbox').focus();
                        }
                    });

                    $(sAlasanKode.target).textbox('textbox').bind('blur',function(e){
                        var q = $(this).val();
                        if(q != '')
                        {
                            $.ajax({
                                url : '{{route('selectalasan')}}',
                                method : 'post',
                                dataType: 'json',
                                data: {
                                    kode: q
                                },
                                success: function(data)
                                {                                
                                    if(data.length > 0)
                                    {
                                        var items = $.map(data, function(item, index)
                                        {
                                            $(sAlasan.target).textbox('setValue',item.id);
                                            $(sAlasanNama.target).textbox('setValue',item.deskripsi);
                                        });
                                        $(sWaktu.target).textbox('textbox').focus();
                                        $(sFlag.target).textbox('setValue','1');
                                    }
                                    else
                                    {
                                        Toast.fire({
                                            type: 'error',
                                            title: 'Kode Alasan tidak ada di database.'
                                        });
                                        $(sAlasan.target).textbox('setValue','');
                                        $(sAlasanNama.target).textbox('setValue','');
                                        $(sAlasanKode.target).textbox('textbox').focus();
                                    }
                                }
                            });
                        }
                        else
                        {
                            Toast.fire({
                                type: 'error',
                                title: 'Kode Alasan tidak ada di database.'
                            });
                            $(sAlasan.target).textbox('setValue','');
                            $(sAlasanNama.target).textbox('setValue','');
                            $(sAlasanKode.target).textbox('textbox').focus();
                        }
                    });

                    // $(sKeterangan.target).textbox('textbox').bind('blur',function(e)
                    // {                        
                    //     dg.edatagrid('endEdit', index);
                    //     dg.edatagrid('addRow',0);
                    // });

                    $(sKeterangan.target).textbox('textbox').bind('keypress',function(e)
                    {                        
                        dg.edatagrid('endEdit', index);
                        dg.edatagrid('addRow',0);
                    });
                }
            });
            
            dgRange = $('#dgRange').edatagrid({
                method: 'post',
                queryParams:{
                    sRangeTanggal: $('#sRangeTanggal').val()
                },
                url: '{{ route('tabletalasanrangekaryawan') }}',
                toolbar: '#toolbarRange',
                idField: 'id',
                rownumbers: 'true',
                fitColumns: 'true',
                singleSelect: 'true',
                onAdd: onAddRange,
                // onEndEdit: onEndEditRange,
                onClickCell: onClickCellRange,
                onBeginEdit: function(index,row){
                    var dg = $(this);
                    var editors = dg.edatagrid('getEditors',index);

                    var sRangeKar = dg.edatagrid('getEditor', {index:index, field:'sRangeKar'});
                    var sRangePin = dg.edatagrid('getEditor', {index:index, field:'sRangePin'});
                    var sRangeKarNama = dg.edatagrid('getEditor', {index:index, field:'sRangeKarNama'});

                    var sRangeAlasanKode = dg.edatagrid('getEditor', {index:index, field:'sRangeAlasanKode'});
                    var sRangeAlasanNama = dg.edatagrid('getEditor', {index:index, field:'sRangeAlasanNama'});
                    var sRangeAlasan = dg.edatagrid('getEditor', {index:index, field:'sRangeAlasan'});

                    var sRangeWaktu = dg.edatagrid('getEditor', {index:index, field:'sRangeWaktu'});  
                    var sRangeKeterangan = dg.edatagrid('getEditor', {index:index, field:'sRangeKeterangan'}); 

                    var sRangeFlag = dg.edatagrid('getEditor', {index:index, field:'sRangeFlag'});

                    $(sRangePin.target).textbox('textbox').bind('blur',function(e){
                        var q = $(this).val();
                        if(q != '')
                        {
                            $.ajax({
                                url : '{{route('selectkaryawan')}}',
                                method : 'post',
                                dataType: 'json',
                                data: {
                                    pin: q
                                },
                                success: function(data)
                                {
                                    
                                    if(data.length > 0)
                                    {
                                        var items = $.map(data, function(item, index)
                                        {
                                            $(sRangeKar.target).textbox('setValue',item.sKar);
                                            $(sRangeKarNama.target).textbox('setValue',item.sKarNama);
                                        });
                                        $(sRangeAlasanKode.target).textbox('textbox').focus();
                                        $(sRangeFlag.target).textbox('setValue','1');
                                    }
                                    else
                                    {
                                        Toast.fire({
                                            type: 'error',
                                            title: 'Karyawan tidak ada di database.'
                                        });
                                        $(sRangeKar.target).textbox('setValue','');
                                        $(sRangeKarNama.target).textbox('setValue','');
                                        $(sRangePin.target).textbox('textbox').focus();
                                    }
                                }
                            });
                        }
                        else
                        {
                            Toast.fire({
                                type: 'error',
                                title: 'Karyawan tidak ada di database.'
                            });
                            $(sRangeKar.target).textbox('setValue','');
                            $(sRangeKarNama.target).textbox('setValue','');
                            $(sRangePin.target).textbox('textbox').focus();
                        }
                    });

                    $(sRangeAlasanKode.target).textbox('textbox').bind('blur',function(e){
                        var q = $(this).val();
                        if(q != '')
                        {
                            $.ajax({
                                url : '{{route('selectalasan')}}',
                                method : 'post',
                                dataType: 'json',
                                data: {
                                    kode: q
                                },
                                success: function(data)
                                {                                
                                    if(data.length > 0)
                                    {
                                        var items = $.map(data, function(item, index)
                                        {
                                            $(sRangeAlasan.target).textbox('setValue',item.id);
                                            $(sRangeAlasanNama.target).textbox('setValue',item.deskripsi);
                                        });
                                        $(sRangeWaktu.target).textbox('textbox').focus();
                                        $(sRangeFlag.target).textbox('setValue','1');
                                    }
                                    else
                                    {
                                        Toast.fire({
                                            type: 'error',
                                            title: 'Kode Alasan tidak ada di database.'
                                        });
                                        $(sRangeAlasan.target).textbox('setValue','');
                                        $(sRangeAlasanNama.target).textbox('setValue','');
                                        $(sRangeAlasanKode.target).textbox('textbox').focus();
                                    }
                                }
                            });
                        }
                        else
                        {
                            Toast.fire({
                                type: 'error',
                                title: 'Kode Alasan tidak ada di database.'
                            });
                            $(sRangeAlasan.target).textbox('setValue','');
                            $(sRangeAlasanNama.target).textbox('setValue','');
                            $(sRangeAlasanKode.target).textbox('textbox').focus();
                        }
                    });

                    $(sRangeKeterangan.target).text('textbox').bind('blur',function(e)
                    {                        
                        dg.edatagrid('endEdit', index);
                        dg.edatagrid('addRow',0);
                    })
                }
            });

        });
        
        var onAdd = function(index,row)
        {
            var ed1 = $('#dg').datagrid('getEditor', {
                index: index,
                field: 'sPin'
            });
            var t = $(ed1.target).textbox('textbox').focus();
        }
        
        var onAddRange = function(index,row)
        {
            var ed1 = $('#dgRange').datagrid('getEditor', {
                index: index,
                field: 'sTanggalAwal'
            });
            var t = $(ed1.target).datebox('textbox').focus();
        }
        
        // var onEndEdit = function(index, row)
        // {
        //     var sKar = $(this).datagrid('getEditor', {
        //         index: index,
        //         field: 'sPin'
        //     });
        //     row.sKarText = $(sKar.target).textbox('getText');
            
        //     var sAlasan = $(this).datagrid('getEditor', {
        //         index: index,
        //         field: 'sAlasan'
        //     });
        //     row.sAlsText = $(sAlasan.target).combobox('getText');
        // }
        
        // var onEndEditRange = function(index, row)
        // {
        //     var sKar = $(this).datagrid('getEditor', {
        //         index: index,
        //         field: 'sKar'
        //     });
        //     row.sKarText = $(sKar.target).combobox('getText');
            
        //     var sAlasan = $(this).datagrid('getEditor', {
        //         index: index,
        //         field: 'sAlasan'
        //     });
        //     row.sAlsText = $(sAlasan.target).combobox('getText');
        // }
        
        var endEditing = function()
        {
            if (editIndex == undefined){return true}
            if ($('#dg').datagrid('validateRow', editIndex))
            {
                $('#dg').datagrid('endEdit', editIndex);
                editIndex = undefined;
                return true;
            } 
            else 
            {
                return false;
            }
        }
        
        var endEditingRange = function()
        {
            if (editRangeIndex == undefined)
            {
                return true;
            }
            if ($('#dgRange').datagrid('validateRow', editIndex))
            {
                $('#dgRange').datagrid('endEdit', editIndex);
                editRangeIndex = undefined;
                return true;
            } 
            else 
            {
                // console.log('kesini');
                return false;
            }
        }
        
        var onClickCell = function(index, field)
        {
            if (editIndex != index)
            {
                if (endEditing())
                {
                    $('#dg').datagrid('selectRow', index)
                            .datagrid('beginEdit', index);
                    var ed = $('#dg').datagrid('getEditor', {index:index,field:field});
                    if (ed)
                    {
//                        ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                    }
                    editIndex = index;
                } 
                else 
                {
                    setTimeout(function()
                    {
                        $('#dg').datagrid('selectRow', editIndex);
                    },0);
                }
            }
        }
        
        var onClickCellRange = function(index, field)
        {
            if (editRangeIndex != index)
            {
                if (endEditing())
                {
                    $('#dgRange').datagrid('selectRow', index)
                            .datagrid('beginEdit', index);
                    var ed = $('#dgRange').datagrid('getEditor', {index:index,field:field});
                    if (ed)
                    {
//                        ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                    }
                    editRangeIndex = index;
                } 
                else 
                {
                    setTimeout(function()
                    {
                        $('#dgRange').datagrid('selectRow', editIndex);
                    },0);
                }
            }
        }
        
        var reloadTable = function()
        {
            var dt = $('#sTanggal').val();
            
            $('#dg').edatagrid('reload', {sTanggal: dt});
        }
        
        var reloadTableRange = function()
        {
            var dt = $('#sRangeTanggal').val();
            
            $('#dgRange').edatagrid('reload', {sRangeTanggal: dt});
        }
        
        var myformatter = function(date)
        {
            
            if(!date)
            {
                return '';
            }
            else
            {
                // console.log(date);
                var y = date.getFullYear();
                var m = date.getMonth()+1;
                var d = date.getDate();
                return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
            }
        }
        var myparser = function(s)
        {
            if (!s) return new Date();
            var ss = (s.split('-'));
            var y = parseInt(ss[0],10);
            var m = parseInt(ss[1],10);
            var d = parseInt(ss[2],10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d))
            {
                return new Date(y,m-1,d);
            } 
            else 
            {
                return new Date();
            }
        }
        
        var onSelectTanggal = function()
        {
            reloadTable();
        }
        
        var addDg = function(e)
        {
            if(e.which == 13) 
            {
                $('#dg').edatagrid('addRow',0);

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
        <div class="easyui-tabs" data-options="tabWidth:112" style="width:100%;height:600px">
            <div title="Harian" style="padding:10px">
                <table id="dg" title="Transaksi Alasan Harian" style="width:100%;height:500px">
                    <thead>
                        <tr>
                            <th data-options="
                                field:'sPin', idField:'sPin', width:50, 
                                editor:{
                                    type:'textbox'
                                }
                                ">PIN</th>
                            <th data-options="
                                field:'sKarNama', idField:'sKarNama', width:70, 
                                editor:{
                                    type:'textbox', 
                                    options:{
                                        readonly:true
                                    }                                    
                                }
                                ">Nama</th>
                            <th data-options="
                                field:'sAlasanKode', width:30, 
                                editor:{
                                    type:'textbox'
                                }
                                ">Kode Alasan</th>
                            <th data-options="
                                field:'sAlasanNama', width:70, 
                                editor:{
                                    type:'textbox', 
                                    options:{
                                        readonly:true
                                    }
                                }
                                ">Alasan</th>
                            <th data-options="
                                field:'sWaktu', width:50,
                                editor:{
                                    type:'textbox'
                                }
                                ">Waktu</th>
                            <th data-options="
                                field:'sKeterangan', width:150, 
                                editor:{
                                    type:'textbox'
                                }
                                ">Keterangan</th>
                            <th data-options="
                                field:'sKar', width:50, hidden:true,
                                editor:{
                                    type:'textbox'
                                }
                                "></th>
                            <th data-options="
                                field:'sAlasan', width:50, hidden:true,
                                editor:{
                                    type:'textbox'
                                }
                                "></th>
                            <th data-options="
                                field: 'sAlasanOld',
                                formatter:function(value,row){
                                    return row.sAlasanOld;
                                }" hidden="true">                       
                            </th>
                            <th data-options="
                                field:'sFlag', width:50, hidden:true,
                                editor:{
                                    type:'textbox'
                                }
                                "></th>
                        </tr>
                    </thead>
                </table>
                <div id="toolbar">
                    <input type="date" id="sTanggal" value="{{\Carbon\Carbon::now()->toDateString()}}">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="javascript:$('#dg').edatagrid('addRow',0)">Tambah</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cmdHapus">Hapus</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" id="cmdSave">Simpan</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#dg').edatagrid('cancelRow')">Batal</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton c1" plain="true" style="width:100px" alt="Upload" data-toggle="modal" data-target="#modal-form-upload"><i class="fa fa-upload"></i>Upload</a>

                </div>
            </div>
            <div title="Range" style="padding:10px">
                <table id="dgRange" title="Transaksi Alasan Range" style="width:100%;height:500px">
                    <thead>
                        <tr>
                            <th data-options="
                                field:'sTanggalAwal', width:50,
                                editor:{
                                    type: 'datebox'
                                }
                            ">Tanggal Awal</th>

                            <th data-options="
                                field:'sTanggalAkhir', width:50,
                                editor:{
                                    type: 'datebox'
                                }
                            ">Tanggal Akhir</th>
                            <th data-options="
                                field:'sRangePin', idField:'sRangePin', width:50, 
                                editor:{
                                    type:'textbox'
                                }
                                ">PIN</th>
                            <th data-options="
                                field:'sRangeKarNama', idField:'sRangeKarNama', width:80, 
                                editor:{
                                    type:'textbox', 
                                    options:{
                                        readonly:true
                                    }                                    
                                }
                                ">Nama</th>
                            <th data-options="
                                field:'sRangeAlasanKode', width:30, 
                                editor:{
                                    type:'textbox'
                                }
                                ">Kode Alasan</th>
                            <th data-options="
                                field:'sRangeAlasanNama', width:70, 
                                editor:{
                                    type:'textbox', 
                                    options:{
                                        readonly:true
                                    }
                                }
                                ">Alasan</th>
                            <th data-options="
                                field:'sRangeWaktu', width:50, 
                                editor:{
                                    type:'textbox'
                                }
                                ">Waktu</th>

                            <th data-options="
                                field:'sRangeKeterangan', width:150,   
                                editor:{
                                    type:'textbox'
                                }
                                ">Keterangan</th>
                            <th data-options="
                                field:'sRangeKar', width:50, hidden:true,
                                editor:{
                                    type:'textbox'
                                }
                                "></th>
                            <th data-options="
                                field:'sRangeAlasan', width:50, hidden:true,
                                editor:{
                                    type:'textbox'
                                }
                                "></th>

                            <th data-options="
                                field: 'sRangeAlasanOld',
                                formatter:function(value,row){
                                    return row.sAlasanOld;
                                }" hidden="true">     
                            <th data-options="
                                field:'sRangeFlag', width:50, hidden:true,
                                editor:{
                                    type:'textbox'
                                }
                                "></th>                  
                            </th>
                        </tr>
                    </thead>
                </table>
                <div id="toolbarRange">
<!--                    <input id="sTanggalRange" class="easyui-datebox" label="Tanggal : " labelPosition="left" data-options="formatter:myformatter, 
                           parser:myparser, 
                           onChange:onSelectTanggal, options : { setValue : myformatter(new Date())}" style="width: 30%">-->
                    <input type="date" id="sRangeTanggal" value="{{\Carbon\Carbon::now()->toDateString()}}">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="javascript:$('#dgRange').edatagrid('addRow',0)">Tambah</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cmdHapusRange">Hapus</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" id="cmdSaveRange">Simpan</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#dgRange').edatagrid('cancelRow')">Batal</a>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection