@extends('adminlte3.app')

@section('title_page')
<p>Approval Transaksi Request Alasan</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Approval Transaksi Request Alasan</li>
@endsection

@section('add_css')
<!-- Datatables -->
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables.net-select-bs4/css/select.bootstrap4.min.css')}}">

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
$(function(e)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
            
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

    @if($var->status == 'send')
    $('#chkAll').on('change', function(e)
    {
        var checked = $(this).prop('checked');

        $('.chkapp').prop('checked', checked);
    });

    $('#cmdSimpanDetail').on('click', function(e)
    {
        e.preventDefault();
        
        var obj = {
            id : $('#id').val(),
            did : $('#did').val(),
            dtanggal : $('#dtanggal').val(),
            dtanggalAkhir : $('#dtanggalAkhir').val(),
            dwaktu : $('#dwaktu').val(),
            dpin : $('#dkaryawanid').val(),
            dalasan : $('#dalasan').val(),
            dcatatan : $('#dcatatan').val()
        };

        $.ajax(
        {
            url         : '{{route('saverequestalasandet')}}',
            dataType    : 'JSON',
            type        : 'POST',
            data        : obj,
            beforeSend  : function(xhr)
            {
                callToastOverlay('warning', 'Loading');
            },
            success(result,status,xhr)
            {
                toastOverlay.close();
                if (result.status == 1)
                {
                    callToast('success', result.msg);
                    location.reload();
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
            error: function(jqXHR, textStatus, errorThrown) { 
                toastOverlay.close();
                console.log(jqXHR.responseText);
            }
        });
    });
    $('#btnApp').on('click', function(e)
    {
        e.preventDefault();
        
        var ids = $('#id').val();

        var checked = [];

        $('.chkapp:checked').each(function(e)
        {
            checked[e] = $(this).val();
        });

        // console.table(checked);

        $.ajax(
        {
            url         : '{{route('apirequestalasanapp')}}',
            dataType    : 'JSON',
            type        : 'POST',
            data        : { id : ids, d:checked},
            beforeSend  : function(xhr)
            {
                callToastOverlay('warning', 'Loading');
            },
            success(result,status,xhr)
            {
                toastOverlay.close();
                if (result.status == 1)
                {
                    callToast('success', result.msg);
                    window.open("{{route('alasanrequest')}}", '_self');
                } 
                else
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
            error: function(jqXHR, textStatus, errorThrown) { 
                toastOverlay.close();
                console.log(jqXHR.responseText);
            }
        });
    });
    $('#btnDec').on('click', function(e)
    {
        e.preventDefault();
        
        if($conf = prompt('Silakan masukkan alasan penolakan'))
        {
            var ids = $('#id').val();
            $.ajax(
            {
                url         : '{{route('apirequestalasandec')}}',
                dataType    : 'JSON',
                type        : 'POST',
                data        : { id : ids,
                                catatan : $conf},
                beforeSend  : function(xhr)
                {
                    callToastOverlay('warning', 'Loading');
                },
                success(result,status,xhr)
                {
                    toastOverlay.close();
                    if (result.status == 1)
                    {
                        callToast('success', result.msg);
                        location.reload();
                    } 
                    else
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
                error: function(jqXHR, textStatus, errorThrown) { 
                    toastOverlay.close();
                    console.log(jqXHR.responseText);
                }
            });
        }
    });
    
    $('.btnedit').on('click', function(e)
    {
        e.preventDefault();
        var ids = $(this).val();
        $.ajax(
        {
            url         : '{{route('apirequestalasandet')}}',
            dataType    : 'JSON',
            type        : 'POST',
            data        : { id : ids },
            beforeSend  : function(xhr)
            {
                callToastOverlay('warning', 'Loading');
            },
            success(result,status,xhr)
            {
                toastOverlay.close();
                if(result.tanggal)
                {
                    var option = new Option($(strSel(result.alasan)).html(), result.alasan.id, true, true);
                    var dalasan = $('#dalasan').append(option).trigger('change');

                    var option = new Option(result.karyawan.pin+' - '+result.karyawan.nama, result.karyawan.id, true, true);
                    var dkaryawanid = $('#dkaryawanid').append(option).trigger('change');
                    
                    // $('#dkaryawan').val(result.karyawan.pin+' - '+result.karyawan.nama);
                    // $('#dkaryawanid').val(result.karyawan.id);

                    $('#dcatatan').val(result.catatan);
                    $('#dtanggal').val(result.tanggal);
                    $('#dtanggalAkhir').val(result.tanggal_akhir);
                    $('#dwaktu').val(result.waktu);
                    $('#did').val(result.id);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) { 
                toastOverlay.close();
                console.log(jqXHR.responseText);
            }
        });
    });
    @endif
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


    $('#dkaryawanid').select2({
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
    
});


function resetDetail()
{
    $('#dkaryawanid').val(null).trigger('change');
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

<div class="row">        
    <div class="col-4">
        <div class="row">
            <div class="col-12">                
                <div class="card card-primary card-outline">
                    <div class="card-header">Master Request Alasan</div>
                    {{Form::hidden('id', $var->id, ['id' => 'id'])}}
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-striped table-sm table-borderless">
                                <tr>
                                    <td><label>No Dokumen</label></td>
                                    <td>{{$var->no_dokumen}}</td>
                                </tr>
                                <td>
                                    @php
                                        if(isset($var->file_dokumen) && !empty($var->file_dokumen))
                                        {
                                            @endphp
                                            <a class="btn btn-success btn-sm" href="{{route('downloadrequestalasan',$var->file_dokumen)}}"><i class="fa fa-file-pdf"></i>{{$var->file_dokumen}}</a>
                                            @php
                                        }
                                        else
                                        {
                                            @endphp
                                            <a class="btn btn-success btn-sm" href="#"><i class="fa fa-file-pdf"></i></a>
                                            @php
                                        }
                                    @endphp
                                    
                                    </td>
                                <tr>
                                    <td><label>Tanggal Request</label></td>
                                    <td>{{$var->tanggal}}</td>
                                </tr>
                                <tr>
                                    <td><label>Catatan</label></td>
                                    <td>{{$var->catatan}}</td>
                                </tr>
                                    <td><label>Status Dokumen</label></td>
                                    <td>
                                        @php
                                            if($var->status == 'new')
                                                echo '<span class="badge badge-primary">'.ucfirst($var->status).'</span>';
                                            else if($var->status == 'send')
                                                echo '<span class="badge badge-secondary">'.ucfirst($var->status).'</span>';
                                            else if($var->status == 'approve')
                                                echo '<span class="badge badge-success">'.ucfirst($var->status).'</span>';
                                            else if($var->status == 'decline')
                                                echo '<span class="badge badge-danger">'.ucfirst($var->status).'</span>';
                                        @endphp
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>  
                    <div class="card-footer">
                        <div class="row">
                            @if($var->status=='send')
                            <button class="btn btn-outline-success btn-sm col-sm-6 d-inline" id="btnApp"><i class="fa fa-check"></i> Approve</button>
                            <button class="btn btn-outline-danger btn-sm col-sm-6 d-inline" id="btnDec"><i class="fa fa-check"></i> Decline</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-8">
        <div class="row">
            <div class="col-12">                
                <div class="card card-primary card-outline">
                    <div class="card-header">List Karyawan</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">    
                                <div class="form-group row">
                                    {{ Form::hidden('did', null, ['id' => 'did']) }}
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
                                        {{ Form::label('dkaryawan', 'Karyawan', ['class' => 'col-sm-3 col-form-label']) }}
                                        <div class="col-sm-9">
                                        {{ Form::select('dkaryawanid', [], null, ['id' => 'dkaryawanid', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
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
                                <div class="col-12">
                                    <div class="form-group row">
                                        @if($var->status == 'send')
                                        <button class="btn btn-outline-primary btn-sm col-sm-3" id="cmdSimpanDetail"><i class="fa fa-save"></i> Simpan</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <th style="width: 75px"><input type="checkbox" id="chkAll"></th>
                                        <th class='ttanggal'>Tanggal</th>
                                        <th class='tpin'>PIN</th>
                                        <th class='tnama'>Nama</th>
                                        <th class='tdivisi'>Divisi</th>
                                        <th class='talasan'>Alasan</th>
                                        <th class='twaktu'>Waktu</th>
                                        <th class='tcatatan'>Catatan</th>
                                        <th class='tstatus'>Status</th>
                                    </thead>
                                    <tbody>
                                        @php
                                            $dataKar = \App\RequestAlasanDetail::with('karyawan', 'alasan')->whereNull('status')->where('request_alasan_id', $var->id)->get();
                                            foreach($dataKar as $rKar)
                                            {
                                                $btn = null;
                                                if(!$rKar->status && $var->status == 'send')
                                                {
                                                    $btn = '<input type="checkbox" class="chkapp" name="chkapp[]" value="'.$rKar->id.'">&nbsp;&nbsp;'.
                                                    '<button class="btn btn-primary btn-xs btnedit" value="'.$rKar->id.'"><i class="fa fa-edit"></i></button>';
                                                }
                                            
                                                $arr = [
                                                $btn,
                                                $rKar->tanggal.(($rKar->tanggal_akhir)?' - '.$rKar->tanggal_akhir:''),
                                                $rKar->karyawan->pin,
                                                $rKar->karyawan->nama,
                                                $rKar->karyawan->divisi->kode.' - '.$rKar->karyawan->divisi->deskripsi,
                                                $rKar->alasan->kode.' - '.$rKar->alasan->deskripsi,
                                                $rKar->waktu,
                                                $rKar->catatan,
                                                ($rKar->status == 'decline')?'<span class="badge badge-danger">Decline</span>':(($rKar->status == 'approve')?'<span class="badge badge-success">Approve</span>':$rKar->status)
                                                ];
                                                
                                                echo '<tr><td>'.implode('</td><td>',$arr).'</tr></td>';
                                            }
                                        @endphp
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection