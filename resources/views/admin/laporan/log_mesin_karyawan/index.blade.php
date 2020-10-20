@extends('adminlte3.app')

@section('title_page')
<p>Laporan Log Mesin</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Laporan Log Mesin</li>
@endsection

@section('add_css')
    <!-- Datatables -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/select2/css/select2.min.css')}}">
    
    <!-- daterange picker -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/daterangepicker/daterangepicker.css')}}">
    
@endsection

@section('add_js')
    <!-- Datatables -->
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
    <!-- select2 -->
    <script src="{{asset('bower_components/admin-lte/plugins/select2/js/select2.full.min.js')}}"></script>
    <!-- moment -->
    <script src="{{asset('bower_components/admin-lte/plugins/moment/moment.min.js')}}"></script>
    <!-- date-range-picker -->
    <script src="{{asset('bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js')}}"></script>
    <script src="{{asset('js/myjs.js')}}"></script>
    <script>
        var dTable = null;
        $(function(e)
        {    

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $('#btnSubmit').on('click', function(e)
            {
                e.preventDefault();
                
                dTable.ajax.reload();
            });
            
            $('#pin').select2({
                // placeholder: 'Silakan Pilih',
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('selkaryawan')}}",
                    dataType    : 'json',
                    type : 'post',
                    data: function (params) 
                    {
                        var query = {
                            q: params.term,
                            t: true
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
            
            $('#tanggal').daterangepicker({
                showDropdowns: true,
                minYear: 2010,
                autoUpdateInput: false,
                maxYear: parseInt(moment().format('YYYY'),10),
                locale: {
                    cancelLabel: 'Clear'
                }
            });
            
            $('#tanggal').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });
            
            
            
            dTable = $('#dTable').DataTable({
                "sPaginationType": "full_numbers",
                "searching":false,
                "ordering": true,
                "deferRender": true,
                "processing": true,
                "serverSide": true,
                "autoWidth": true,
                "select": true,
                "scrollY": 600,
                "lengthMenu": [100, 500, 1000, 1500, 2000 ],
                "ajax":
                {
                    "url"       : "{{ route('dtlogmesinkaryawan') }}",
                    "type"      : 'POST',
                    data: function (d) 
                    {
                        d.sPin   = $('#pin').val();
                        d.sTanggal   = $('#tanggal').val();
                    }
                }, 
                "columnDefs":[
                @if(Auth::user()->type->nama == 'ADMIN')
                {
                    targets : 'tact',
                    className :      'details-control',
                    data: "action"
                },
                @endif
                {
                    targets : 'tpin',
                    data: "pin"
                },
                {
                    targets : 'tnama',
                    data: function(data)
                    {
                        if(data.karyawan)
                        {
                            return data.karyawan.nama;
                        }
                        else
                        {
                            return '';
                        }
                    }
                },
                {
                    targets : 'twaktu',
                    data: "tanggal"
                },
                {
                    targets : 'tmesin',
                    data: function(data)
                    {
                        if(data.mesin)
                        {
                            return data.mesin.kode+" - "+data.mesin.lokasi;
                        }
                        else
                        {
                            return '';
                        }
                    }
                }]
            });
            @if(Auth::user()->type->nama == 'ADMIN')
            $('#dTable tbody').on('click', 'td.details-control', function () 
            {
                var tr = $(this).closest('tr');
                var row = dTable.row( tr );
                var datas = row.data();
                
                $('.editrow').on('click',function(e)
                {
//                    console.log(datas.pin);
                    $('#eid').val(datas.id);
                    $('#epin').val(datas.pin);
                    $('#enama').val(datas.karyawan.nama);
                    $('#etanggal').val(datas.tanggal);
                });
            } );
            
            $('#cmdModalSave').on('click', function(e)
            {
                e.preventDefault();
//                console.log($('#form_edit').prop('action'));
                $.ajax(
                {
                    url         : $('#form_edit').prop('action'),
                    type        : 'POST',
                    dataType    : 'json',
                    data        : {id:$('#eid').val(), tanggal:$('#etanggal').val()},
                    beforeSend: function (xhr)
                    {
                        callToastOverlay('warning', 'Sedang memproses data');
                    },
                    success     : function(result,status,xhr)
                    {
                        if(result.status == 1)
                        {
                            callToast('success', result.msg);
                            $('#eid').val('');
                            $('#epin').val('');
                            $('#enama').val('');
                            $('#etanggal').val('');
                            dTable.ajax.reload();
                        }
                        else
                        {
                            callToast('warning', result.msg);
                        }
                    }
                });
            });
            @endif
        });
        
        let sD = function()
        {
            let dt = moment();
            
            if(dt.format("d") == 22)
            {
                return moment();
            }
            else if(dt.format("d") < 22)
            {
                return moment(dt.format("YYYY-MM")+"-22").subtract(1, 'months');
            }
            else
            {
                return moment(dt.format("YYYY-MM")+"-21");
            }
            return moment();
        }
    </script>
@endsection

@section('modal_form')
@if(Auth::user()->type->nama == 'ADMIN')
<div class="modal fade" id="modal-form">
    <div class="modal-dialog">
        <div class="modal-content bg-secondary">
            <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-pencil"></i>Form Edit Activity</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<!--        <form id="form_data" action="{{route('savejadwalday')}}" accept-charset="UTF-8" >-->
            {{ Form::open(['route' => ['editlogmesinkaryawan'], 'id' => 'form_edit']) }}
            {{ Form::hidden('eid',null, ['id' => 'eid']) }}
            <div class="modal-body">   
                <div class="form-group">
                    <div class="row">
                        <div class="col-12">
                           <div class="form-group">
                                <label for="epin">Pin</label>
                                <input type="text" class="form-control" id="epin" name="epin" placeholder="PIN" readonly="">
                            </div>
                            <div class="form-group">
                                <label for="enama">Nama</label>
                                <input type="text" class="form-control" id="enama" name="enama" placeholder="Nama" readonly="">
                            </div>
                            <div class="form-group">
                                <label for="etanggal">Tanggal</label>
                                <input type="text" class="form-control" id="etanggal" name="etanggal" placeholder="Tanggal">
                            </div>
                        </div>  
                    </div>
                </div>
            </div>  
            
            <div class="modal-footer justify-content-between">
                <button type="button" id="cmdModalClose" class="btn btn-outline-light" data-dismiss="modal">Keluar</button>
                <button type="submit" id="cmdModalSave" class="btn btn-outline-light">Simpan</button>
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
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline card-tabs">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            {{ Form::label('pin', 'Karyawan') }}
                            {{ Form::select('pin', [], null, ['id' => 'pin', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            {{ Form::label('tanggal', 'Range Tanggal') }}
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                </div>
                                {{ Form::text('tanggal', null, ['id' => 'tanggal', 'class' => 'form-control form-control-sm float-right']) }}
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-success btn-submit" id="btnSubmit"><i class="fa fa-search"></i>Preview</button>
<!--                <button class="btn btn-success btn-submit" name="btnSubmit" value="pdf"><i class="fa fa-file-pdf"></i>PDF</button>
                <button type="reset" class="btn btn-success btn-reset" name="btnReset" value="reset"><i class="fa fa-refresh"></i>Reset</button>-->
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card card-primary card-outline card-tabs">
            <div class="card-body">
                <table id="dTable" class="table table-hover">
                    <thead>
                        <tr>
                            @if(Auth::user()->type->nama == 'ADMIN')<th class="tact"></th>@endif
                            <th class="tpin">PIN</th>
                            <th class="tnama">Nama</th>
                            <th class="twaktu">Waktu Absen</th>
                            <th class="tmesin">Mesin Absen</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection