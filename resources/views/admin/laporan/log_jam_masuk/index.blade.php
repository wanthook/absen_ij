@extends('adminlte3.app')

@section('title_page')
<p>Laporan Log Jam Masuk</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Laporan Log Jam Masuk</li>
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
    <script>
        var dTable = null;
        $(function(e)
        {            
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

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $('#shift').select2({placeholder: "",allowClear: true});
            
            $('#divisi').select2({
                // placeholder: 'Silakan Pilih',
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('seldivisi')}}",
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
                }
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
            
            $('#perusahaan').select2({
                // placeholder: 'Silakan Pilih',
                placeholder: "",
                allowClear: true,
                minimumInputLength: 0,
                delay: 250,
                ajax: {
                    url: "{{route('selperusahaan')}}",
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
                }
            });
            
            $('#tanggal').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                minYear: 2001,
                maxYear: parseInt(moment().format('YYYY'),10),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });
            
            $('#tanggalRange').daterangepicker({
                showDropdowns: true,
                minYear: 2010,
                autoUpdateInput: false,
                maxYear: parseInt(moment().format('YYYY'),10),
                locale: {
                    cancelLabel: 'Clear'
                }
            });
            
            $('#tanggalRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });
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

@section('content')
{{ Form::open(['route' => ['logjammasuklaporan'], 'id' => 'form_data', 'target' => '_blank']) }}
{{ Form::hidden('id',null, ['id' => 'id']) }}
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline card-tabs">
            <div class="card-body">
                <div class="row">
<!--                    <div class="col-6">
                        <div class="form-group">
                            {{ Form::label('pin', 'Karyawan') }}
                            {{ Form::select('pin', [], null, ['id' => 'pin', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                        </div>
                    </div>-->
                    <div class="col-6">
                        <div class="form-group">
                            {{ Form::label('divisi', 'Divisi') }}
                            {{ Form::select('divisi', [], null, ['id' => 'divisi', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            {{ Form::label('tanggal', 'Tanggal') }}
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
                    @if(Auth::user()->type->nama != 'REKANAN')
                    <div class="col-6">
                        <div class="form-group">
                            {{ Form::label('perusahaan', 'Perusahaan') }}
                            {{ Form::select('perusahaan', [], null, ['id' => 'perusahaan', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                        </div>
                    </div>
                    @endif
                    <div class="col-6">
                        <div class="form-group">
                            {{ Form::label('tanggal', 'Range Tanggal') }}
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                </div>
                                {{ Form::text('tanggalRange', null, ['id' => 'tanggalRange', 'class' => 'form-control form-control-sm float-right']) }}
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            {{ Form::label('shift', 'Jenis Shift') }}
                            {{ Form::select('shift', [1=>'Shift 1', 2=>'Shift 2', 3=>'Shift 3'], null, ['id' => 'shift', 'class' => 'form-control form-control-sm select2', 'style'=> 'width: 100%;']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-success btn-submit" name="btnSubmit" value="preview"><i class="fa fa-search"></i>Preview</button>
                <button class="btn btn-success btn-submit" name="btnSubmit" value="pdf"><i class="fa fa-file-pdf"></i>PDF</button>
                <button class="btn btn-success btn-submit" name="btnSubmit" value="excel"><i class="fa fa-file-excel"></i>Excel</button>
                <button type="reset" class="btn btn-success btn-reset" name="btnReset" value="reset"><i class="fa fa-refresh"></i>Reset</button>
            </div>
        </div>
    </div>
</div>
{{ Form::close() }}
@endsection