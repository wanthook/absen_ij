@extends('adminlte3.app')

@section('title_page')
<p>Form Transaksi Request Alasan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Form Transaksi Request Alasan</li>
@endsection

@section('add_css')
<!-- Datatables -->
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables.net-select-bs4/css/select.bootstrap4.min.css')}}">
<!-- bootstrap color picker -->
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
<!-- select2 -->
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/select2/css/select2.min.css')}}">
<!-- daterange picker -->
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/daterangepicker/daterangepicker.css')}}">
<style>
    td.details-control {
        background: url('{{asset('images/details_open.png')}}') no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url('{{asset('images/details_close.png')}}') no-repeat center center;
    }
</style>
@endsection

@section('add_js')
<!-- Datatables -->
<script src="{{asset('bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('bower_components/admin-lte/plugins/datatables.net-select-bs4/js/select.bootstrap4.min.js')}}"></script>
<!-- bootstrap color picker -->
<script src="{{asset('bower_components/admin-lte/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
<!-- select2 -->
<script src="{{asset('bower_components/admin-lte/plugins/select2/js/select2.full.min.js')}}"></script>
<!-- date-range-picker -->
<script src="{{asset('bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js')}}"></script>
<!-- bs-custom-file-input -->
<script src="{{asset('bower_components/admin-lte/plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>

<script src="{{asset('js/myjs.js')}}"></script>

<script>
$(function (e)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    bsCustomFileInput.init();

    $('#tanggal, #dtanggal').daterangepicker({
        singleDatePicker: true,
        locale: {
            format: 'YYYY-MM-DD'
        }
    });

    $('#dtanggalAkhir').daterangepicker({
        singleDatePicker: true,
        autoUpdateInput: false,
        locale: {
            format: 'YYYY-MM-DD'
        }
    }).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });

    $('#dpin').select2({
        // placeholder: 'Silakan Pilih',
        placeholder: "",
        allowClear: true,
        minimumInputLength: 0,
        delay: 250,
        ajax: {
            url: "{{route('selkaryawan')}}",
            dataType: 'json',
            type: 'post',
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


    $('#dalasan').select2({
        minimumInputLength: 0,
        allowClear: true,
        delay: 250,
        placeholder: {
            id: "",
            placeholder: ""
        },
        ajax: {
            url: "{{route('selalasan')}}",
            dataType: 'json',
            type: 'post',
            data: function (params)
            {
                let query = {
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
        templateResult: function (par)
        {
            return par.name || $(strSel(par));
        },
        templateSelection: function (par)
        {
            if (par.text == "")
            {
                return par.name || $(strSel(par));
            }
            return par.name || par.text;
        }
    });

    $('#simpandata').on('click', function (e)
    {
        e.preventDefault();
        let frm = document.getElementById('form_data');
        let datas = new FormData(frm);

        $.ajax(
                {
                    url: '{{route('saverequestalasan')}}',
                    dataType: 'JSON',
                    type: 'POST',
                    data: datas,
                    processData: false,
                    contentType: false,
                    beforeSend: function (xhr)
                    {
                        callToastOverlay('warning', 'Sedang memproses data upload');
                    },
                    success(result, status, xhr)
                    {
                        toastOverlay.close();
                        if (result.status == 1)
                        {
                            callToast('success', result.msg);
                            resetDetail();
                            dTable.ajax.reload();
                        } else
                        {
                            if (Array.isArray(result.msg))
                            {
                                var str = "";
                                for (var i = 0; i < result.msg.length; i++)
                                {
                                    str += result.msg[i] + "<br>";
                                }
                                callToast('error', str);
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        toastOverlay.close();
                        console.log(jqXHR.responseText);
                    }
                });
    });

    $('#tambahDetail').on('click', function (e)
    {
        e.preventDefault();
        let frm = document.getElementById('form_data');
        let datas = new FormData(frm);

        $.ajax(
                {
                    url: '{{route('saverequestalasandet')}}',
                    dataType: 'JSON',
                    type: 'POST',
                    data: datas,
                    processData: false,
                    contentType: false,
                    beforeSend: function (xhr)
                    {
                        callToastOverlay('warning', 'Sedang memproses data upload');
                    },
                    success(result, status, xhr)
                    {
                        toastOverlay.close();
                        if (result.status == 1)
                        {
                            callToast('success', result.msg);
                            resetDetail();
                            dTable.ajax.reload();
                        } else
                        {
                            if (Array.isArray(result.msg))
                            {
                                var str = "";
                                for (var i = 0; i < result.msg.length; i++)
                                {
                                    str += result.msg[i] + "<br>";
                                }
                                callToast('error', str);
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        toastOverlay.close();
                        console.log(jqXHR.responseText);
                    }
                });
    });

    dTable = $('#dTable').DataTable({
//        "sPaginationType": "full_numbers",
        "paging": false,
        "searching": false,
        "ordering": true,
        "deferRender": true,
        "processing": true,
        "serverSide": true,
        "select": true,
        "scrollX": true,
        "scrollY": 600,
        "autoWidth": false,
//        "lengthMenu": [500, 1000, 1500, 2000],
        "ajax":
                {
                    "url": "{{ route('dtrequestalasandet') }}",
                    "type": 'POST',
                    data: function (d)
                    {
                        d.id = $('#id').val();
                    }
                },
        "columnDefs": [
            {
                "targets": 0,
//                "className": 'btndel',
                "orderable": false,
                "data": null,
                "defaultContent": '<button class="btn btn-sm btn-primary btnedit"><i class="fa fa-edit"></i></button><button class="btn btn-sm btn-danger btndel"><i class="fa fa-eraser"></i></button>'
            },
            {
                targets: 'ttanggal',
                data: function (data)
                {
                    if (data.tanggal_akhir)
                    {
                        return data.tanggal + ' - ' + data.tanggal_akhir;
                    } else
                    {
                        return data.tanggal;
                    }
                }
            },
            {
                targets: 'tpin',
                data: 'karyawan.pin'
            },
            {
                targets: 'tnama',
                data: 'karyawan.nama'
            },
            {
                targets: 'tdivisi',
                data: 'divisi'
            },
            {
                targets: 'talasan',
                data: 'alasan'
            },
            {
                targets: 'twaktu',
                data: 'waktu'
            },
            {
                targets: 'tcatatan',
                data: "catatan"
            },
            {
                targets: 'tstatus',
                data: function (data)
                {
                    if (data.status)
                    {
                        var band = null;
                        if (data.status == 'new')
                            band = 'primary';
                        else if (data.status == 'send')
                            band = 'secondary';
                        else if (data.status == 'approve')
                            band = 'success';
                        else if (data.status == 'decline')
                            band = 'danger';
                        else if (data.status == 'return')
                            band = 'warning';

                        return '<span class="badge badge-' + band + '">' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</span>'
                    } else
                    {
                        return null;
                    }
                }
            }
        ]
    });
    
    $('#dTable tbody').on('click', '.btnedit', function (e)
    {
        e.preventDefault();
        var tr = $(this).closest('tr');
        var row = dTable.row(tr);
        var datas = row.data();
        
        var dpin = $('#dpin');
        
        $.ajax({
            url: "{{route('selkaryawan')}}",
            dataType: 'json',
            type: 'post',
            data: {id:datas.karyawan_id}
        }).then(function(data)
        {
            var option = new Option(data.items[0].text, data.items[0].id, true, true);
            dpin.append(option).trigger('change');
        });
        
        var dalasan = $('#dalasan');
        
        $.ajax({
            url: "{{route('selalasan')}}",
            dataType: 'json',
            type: 'post',
            data: {id:datas.alasan_id}
        }).then(function(data)
        {
            var option = new Option(strSel(data.items[0]), data.items[0].id, true, true);
            dalasan.append(option).trigger('change');
        });
        
        $('#dcatatan').val(datas.catatan);
        $('#dtanggal').val(datas.tanggal);
        $('#dtanggalAkhir').val(datas.tanggal_akhir);
        $('#dwaktu').val(datas.waktu);
        $('#did').val(datas.id);
    });
    
    $('#dTable tbody').on('click', '.btndel', function (e)
    {
        e.preventDefault();
        var tr = $(this).closest('tr');
        var row = dTable.row(tr);
        var datas = row.data();

        if (confirm('Apakah Anda yakin menghapus data ini?'))
        {
            $.ajax(
                    {
                        url: '{{route("deleterequestalasandet")}}',
                        dataType: 'JSON',
                        type: 'POST',
                        data: {id: datas.id},
                        beforeSend: function (xhr)
                        {
                            callToastOverlay('warning', 'Sedang memproses hapus data');
                        },
                        success(result, status, xhr)
                        {
                            toastOverlay.close();
                            if (result.status == 1)
                            {
                                callToast('success', result.msg);
                            } else
                            {
                                if (Array.isArray(result.msg))
                                {
                                    var str = "";
                                    for (var i = 0; i < result.msg.length; i++)
                                    {
                                        str += result.msg[i] + "<br>";
                                    }
                                    callToast('error', str);
                                }

                            }
                            dTable.ajax.reload();
                        }
                    });

            return false;
        }
    });
});

function resetDetail()
{
    $('#dpin').val(null).trigger('change');
    $('#dalasan').val(null).trigger('change');
    $('#dcatatan').val(null);
    $('#did').val(null);
    $('#dtanggalAkhir').val(null);
    $('#dwaktu').val(null);
}

var strSel = function (par)
{
    return '<span class="badge" style="background-color:' + par.warna + '">' + par.kode + ' - ' + par.deskripsi + '</span>';
}
</script>
@endsection

@section('content')
{{ Form::model($var, ['route' => ['saverequestalasan'], 'id' => 'form_data', 'class' => 'form-horizontal', 'files' => true]) }}
{{ Form::hidden('id',null, ['id' => 'id']) }}
<div class="row">        
    <div class="col-6">
        <div class="row">
            <div class="col-12">                
                <div class="card card-primary card-outline">
                    <div class="card-header">Form</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group row">
                                    {{ Form::label('no_dokumen', 'No Dokumen', ['class' => 'col-sm-3 col-form-label']) }}
                                    <div class="col-sm-9">
                                        {{ Form::text('no_dokumen', null, ['id' => 'no_dokumen', 'class' => 'form-control form-control-sm', 'placeholder' => 'No. Dokumen', 'readonly']) }}                                
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group row">
                                    {{ Form::label('file_dokumen_upload', 'File Upload', ['class' => 'col-sm-3 col-form-label']) }}
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="file_dokumen_upload" name="file_dokumen_upload">
                                                <label class="custom-file-label" for="formUpload">Choose file</label>
                                            </div>
                                        </div>                                    
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group row">
                                    {{ Form::label('tanggal', 'Tgl Dokumen', ['class' => 'col-sm-3 col-form-label']) }}
                                    <div class="col-sm-9">
                                        <div class="input-group" data-target-input="nearest">
                                            {{ Form::text('tanggal', null, ['id' => 'tanggal', 'class' => 'form-control form-control-sm', 'placeholder' => 'Tanggal']) }}
                                            <div class="input-group-append" data-target="#tanggal">
                                                <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group row">
                                    {{ Form::label('catatan', 'Catatan', ['class' => 'col-sm-3 col-form-label']) }}
                                    <div class="col-sm-9">
                                        {{ Form::text('catatan', null, ['id' => 'catatan', 'class' => 'form-control form-control-sm', 'placeholder' => 'Catatan']) }}                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>                    
                    <div class='card-footer'>
                        {{Form::button('<i class="fa fa-save"></i>Simpan', ['class' => 'btn btn-success btn-sm float-right', 'id' => 'simpandata'])}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row">
            <div class="col-12">                
                <div class="card card-primary card-outline">
                    <div class="card-header">Tambah Detail</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group row">
                                    {{ Form::hidden('did',null, ['id' => 'did']) }}
                                    {{ Form::label('dtanggal', 'Tgl Alasan', ['class' => 'col-sm-3 col-form-label']) }}
                                    <div class="col-sm-4">
                                        <div class="input-group" data-target-input="nearest">
                                            {{ Form::text('dtanggal', null, ['id' => 'dtanggal', 'class' => 'form-control form-control-sm', 'placeholder' => 'Tanggal Alasan']) }}
                                            <div class="input-group-append" data-target="#dtanggal">
                                                <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="input-group" data-target-input="nearest">
                                            {{ Form::text('dtanggalAkhir', null, ['id' => 'dtanggalAkhir', 'class' => 'form-control form-control-sm', 'placeholder' => 'Tanggal Alasan Akhir']) }}
                                            <div class="input-group-append" data-target="#dtanggalAkhir">
                                                <div class="input-group-text"><i class="far fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">    
                                <div class="form-group row">                                        
                                    {{ Form::label('dpin', 'Karyawan', ['class' => 'col-sm-3 col-form-label']) }}
                                    <div class="col-sm-9">
                                        {{ Form::select('dpin', [], null, ['id' => 'dpin', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">    
                            <div class="form-group row">                                        
                                {{ Form::label('dalasan', 'Alasan', ['class' => 'col-sm-3 col-form-label']) }}
                                <div class="col-sm-9">
                                    {{ Form::select('dalasan', [], null, ['id' => 'dalasan', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group row">
                                {{ Form::label('dwaktu', 'Nilai', ['class' => 'col-sm-3 col-form-label']) }}
                                <div class="col-sm-9">
                                    {{ Form::text('dwaktu', null, ['id' => 'dwaktu', 'class' => 'form-control form-control-sm', 'placeholder' => 'Waktu']) }}                                
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group row">
                                {{ Form::label('dcatatan', 'Catatan', ['class' => 'col-sm-3 col-form-label']) }}
                                <div class="col-sm-9">
                                    {{ Form::text('dcatatan', null, ['id' => 'dcatatan', 'class' => 'form-control form-control-sm', 'placeholder' => 'Catatan']) }}                                
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='card-footer'>
                        {{Form::button('<i class="fa fa-plus"></i>Tambah', ['class' => 'btn btn-primary btn-sm float-right', 'id' => 'tambahDetail'])}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="row">
            <div class="col-12">                
                <div class="card card-primary card-outline">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <table id="dTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class='ttanggal'>Tanggal</th>
                                            <th class='tpin'>PIN</th>
                                            <th class='tnama'>Nama</th>
                                            <th class='tdivisi'>Divisi</th>
                                            <th class='talasan'>Alasan</th>
                                            <th class='twaktu'>Waktu</th>
                                            <th class='tcatatan'>Catatan</th>
                                            <th class='tstatus'>Status</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{Form::close()}}
@endsection