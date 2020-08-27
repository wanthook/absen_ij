@extends('adminlte3.app')

@section('title_page')
<p>Proses Absensi</p>
@endsection


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Proses Absensi</li>
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
                showConfirmButton: false
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
            
            $('#form_data').submit( function(e)
            {
                e.preventDefault();
                const data = $(this).serialize();
                
                $.ajax(
                {
                    url         : $(this).attr('action'),
                    dataType    : 'json',
                    type        : 'POST',
                    data        : $('#form_data').serialize(),
                    beforeSend  : function(xhr)
                    {
                        toastOverlay.fire({
                            type: 'warning',
                            title: 'Sedang memproses absen',
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
                            
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) { 
                        /* implementation goes here */ 
                        toastOverlay.close();
//                        console.log(jqXHR.responseText);
                        Toast.fire({
                            type: 'error',
                            title: jqXHR.responseText
                        });
                    }
                    
                });
                
                return false;
            });
            
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
                startDate: sD(),
                endDate: eD(),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });
//            console.log(moment());
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
        
        let eD = function()
        {
            let dt = moment();
            
            if(dt.format("d") > 21)
            {
                return moment(dt.format("YYYY-MM")+"-22").add(1, 'months');
            }
            else
            {
                return moment(dt.format("YYYY-MM")+"-22");
            }
            return moment();
        }
    </script>
@endsection

@section('content')
{{ Form::open(['route' => ['prosesabsen'], 'id' => 'form_data']) }}
{{ Form::hidden('id',null, ['id' => 'id']) }}
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
                    <div class="col-6">
                        <div class="form-group">
                            {{ Form::label('perusahaan', 'Perusahaan') }}
                            {{ Form::select('perusahaan', [], null, ['id' => 'perusahaan', 'class' => 'form-control select2', 'style'=> 'width: 100%;']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-success"><i class="fa fa-tasks"></i>Proses</button>
            </div>
        </div>
    </div>
</div>
{{ Form::close() }}
@endsection