@extends('adminlte3.app')

@section('title_page')
    <p>Form Edit Proses Gaji</p>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Beranda</a></li>
<li class="breadcrumb-item active">Edit Proses Gaji</li>
@endsection

@section('content')
{{ Form::open(['route' => ['saveprosesgajiedit'], 'id' => 'form_data', 'files' => true]) }}
{{ Form::hidden('id',null, ['id' => 'id']) }}
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <span class="card-title">Info Karyawan</span>
                <div class="card-tools">
                    <div class="btn-group">
                        <button id="btnSave" class="btn btn-sm btn-success">Simpan</button>
                        <button id="btnDelete" class="btn btn-sm btn-danger">Hapus</button>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('periode', 'Periode', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    {{ Form::text('periode', null, ['id' => 'periode', 'class' => 'form-control form-control-sm float-right']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('pin', 'PIN', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                            {{ Form::select('pin', [], null, ['id' => 'pin', 'class' => 'select2', 'style'=> 'width: 100%']) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('nama_karyawan', 'Nama Karyawan', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('nama_karyawan', null, ['id' => 'nama_karyawan', 'class' => 'form-control form-control-sm', 'placeholder' => 'Nama Karyawan', 'readonly']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('divisi', 'Divisi', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('divisi', null, ['id' => 'divisi', 'class' => 'form-control form-control-sm', 'placeholder' => 'Divisi', 'readonly']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('tanggal_masuk', 'Tanggal Masuk', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    {{ Form::text('tanggal_masuk', null, ['id' => 'tanggal_masuk', 'class' => 'form-control form-control-sm float-right', 'readonly']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('gaji_pokok', 'Gaji Pokok', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('gaji_pokok', null, ['id' => 'gaji_pokok', 'class' => 'form-control form-control-sm', 'placeholder' => '0', 'readonly']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('bruto_rp', 'Pendapatan Bruto', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('bruto_rp', null, ['id' => 'bruto_rp', 'class' => 'form-control form-control-sm', 'placeholder' => '0', 'readonly']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('tot_akhir', 'Total Akhir', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('tot_akhir', null, ['id' => 'tot_akhir', 'class' => 'form-control form-control-sm', 'placeholder' => '0', 'readonly']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('tot_bayar', 'Total Bayar', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('tot_bayar', null, ['id' => 'tot_bayar', 'class' => 'form-control form-control-sm', 'placeholder' => '0', 'readonly']) }}                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-primary">
            <div class="card-header">Tunjangan</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('tunjangan_jabatan', 'Jabatan', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('tunjangan_jabatan', null, ['id' => 'tunjangan_jabatan', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('tunjangan_prestasi', 'Prestasi.', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('tunjangan_prestasi', null, ['id' => 'tunjangan_prestasi', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('tunjangan_haid', 'Haid', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('tunjangan_haid', null, ['id' => 'tunjangan_haid', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('tunjangan_hadir', 'Hadir', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('tunjangan_hadir', null, ['id' => 'tunjangan_hadir', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('tunjangan_lain', 'Lain-lain', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('tunjangan_lain', null, ['id' => 'tunjangan_lain', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-primary">
            <div class="card-header">Potongan</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('bpjs_tk', 'BPJS TK.', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('bpjs_tk', null, ['id' => 'bpjs_tk', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('bpjs_kes', 'BPJS Kes.', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('bpjs_kes', null, ['id' => 'bpjs_kes', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('bpjs_pen', 'BPJS Pensiun', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('bpjs_pen', null, ['id' => 'bpjs_pen', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('pph21', 'PPH21', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('pph21', null, ['id' => 'pph21', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('cost_serikat_nama', 'Serikat', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-5">
                                {{ Form::text('cost_serikat_nama', null, ['id' => 'cost_serikat_nama', 'class' => 'form-control form-control-sm', 'placeholder' => 'Nama Serikat', 'readonly']) }}                                
                            </div>
                            <div class="col-sm-4">
                                {{ Form::text('cost_serikat_rp', null, ['id' => 'cost_serikat_rp', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('toko', 'Toko', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('toko', null, ['id' => 'toko', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('lainlain', 'Lain-lain', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('lainlain', null, ['id' => 'lainlain', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">Potongan Absen</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('potongan_absen', 'Jumlah', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('potongan_absen', null, ['id' => 'potongan_absen', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('potongan_absen_rp', 'Rp.', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('potongan_absen_rp', null, ['id' => 'potongan_absen_rp', 'class' => 'form-control form-control-sm', 'placeholder' => '0', 'readonly']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('gaji_pokok_dibayar', 'Gaji Pokok Dibayar', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('gaji_pokok_dibayar', null, ['id' => 'gaji_pokok_dibayar', 'class' => 'form-control form-control-sm', 'placeholder' => '0', 'readonly']) }}                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-primary">
            <div class="card-header">Upah Lembur</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('lembur', 'Jumlah', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('lembur', null, ['id' => 'lembur', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('lembur_rp', 'Rp.', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('lembur_rp', null, ['id' => 'lembur_rp', 'class' => 'form-control form-control-sm', 'placeholder' => '0', 'readonly']) }}                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-primary">
            <div class="card-header">Shift 3</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('s3', 'Jumlah', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('s3', null, ['id' => 's3', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('s3_rp', 'Rp.', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('s3_rp', null, ['id' => 's3_rp', 'class' => 'form-control form-control-sm', 'placeholder' => '0', 'readonly']) }}                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-primary">
            <div class="card-header">Getpas</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('gp', 'Jam', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('gp', null, ['id' => 'gp', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('gp_rp', 'Rp.', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('gp_rp', null, ['id' => 'gp_rp', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-primary">
            <div class="card-header">Koreksi</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('koreksi_plus', 'Kor (+)', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('koreksi_plus', null, ['id' => 'koreksi_plus', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group row">
                            {{ Form::label('koreksi_minus', 'Kor (-)', ['class' => 'col-sm-3 col-form-label']) }}
                            <div class="col-sm-9">
                                {{ Form::text('koreksi_minus', null, ['id' => 'koreksi_minus', 'class' => 'form-control form-control-sm act', 'placeholder' => '0']) }}                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{ Form::close() }}
@endsection

@section('add_css')
<link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/bootstrap/dataTables.bootstrap4.min.css')}}">
    <!-- Datatables -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap4.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/select2/css/select2.min.css')}}">
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
    <script src="{{asset('js/json2.js')}}"></script>
    <script src="{{asset('js/jsonSerialize.js')}}"></script>
    
    <script>
        $(function(e)
        {

            $.ajaxSetup(
            {
                headers: 
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })

            $('#btnSave').on('click', function(e)
            {
                e.preventDefault();



                let frm = document.getElementById('form_data');
                let datas = new FormData(frm);
                $.ajax(
                {
                    url         : $('#form_data').attr('action'),
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
                        toastOverlay.close();
                        if(result.status == 1)
                        {
                            Toast.fire({
                                type: 'success',
                                title: result.msg
                            });

                            resetDoc();
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
                        toastOverlay.close();
                        /* implementation goes here */ 
                        console.log(jqXHR.responseText);
                    }
                });
            })
            
            $('#periode').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                minYear: 2020,
                maxYear: parseInt(moment().format('YYYY'),10),
                locale: {
                    format: 'YYYY-MM'
                }
            });

            // $('form').submit(function(e) { 
            //     e.preventDefault();
            // });

            // $('#pin').on('keypress', function(e)
            // {                
            //     if(e.keyCode == 13)
            //     {
            //         pinPress($(this).val())
            //     }
            // });
            
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

            $('#pin').on('select2:select', function(e)
            {
                if(e.params.data)
                {
                    pinPress(e.params.data.id)
                }
            })

            $('#pin').on('select2:clearing', function(e)
            {
                if(!confirm("Apakah yakin ingin mambatalkan edit data ini?"))
                {
                    e.preventDefault()
                }
                else
                {
                    resetDoc();
                }
            })

            $('.act').on('keyup', function(e)
            {
                calculateData();
            })
        })

        let Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false
        })

        let toastOverlay = Swal.mixin({
            position: 'center',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false
        })

        var pinPress = function(pin)
        {
            $.ajax(
            {
                url         : '{{route("prosessalarydata")}}',
                dataType    : 'json',
                type        : 'POST',
                data        : {periode:$('#periode').val(), pin:pin},
                beforeSend  : function(xhr)
                {
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
                        setV(result.msg);
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
                    Toast.fire({
                        type: 'error',
                        title: jqXHR.responseText
                    });
                }
                
            });
            
            // return false;
        }

        var setV = function(par)
        {
            if(par)
            {
                $('#id').val(par.id)
                $('#nama_karyawan').val(par.karyawan.nama)
                $('#karyawan_id').val(par.karyawan_id)
                $('#divisi').val(par.karyawan.divisi.kode+' - '+par.karyawan.divisi.deskripsi)
                $('#tanggal_masuk').val(par.karyawan.tanggal_masuk)
                if(par.editlistlast.length > 0)
                {
                    $('#gaji_pokok').val(par.editlistlast[0].gaji_pokok)
                    $('#bruto_rp').val(par.editlistlast[0].bruto_rp)
                    $('#tot_akhir').val(par.editlistlast[0].tot_akhir)
                    $('#tot_bayar').val(par.editlistlast[0].tot_bayar)

                    $('#tunjangan_jabatan').val(par.editlistlast[0].tunjangan_jabatan)
                    $('#tunjangan_prestasi').val(par.editlistlast[0].tunjangan_prestasi)
                    $('#tunjangan_haid').val(par.editlistlast[0].tunjangan_haid)
                    $('#tunjangan_hadir').val(par.editlistlast[0].tunjangan_hadir)
                    $('#tunjangan_lain').val(par.editlistlast[0].tunjangan_lain)

                    $('#bpjs_tk').val(parseInt(par.editlistlast[0].bpjs_tk))
                    $('#bpjs_kes').val(parseInt(par.editlistlast[0].bpjs_kes))
                    $('#bpjs_pen').val(parseInt(par.editlistlast[0].bpjs_pen))
                    $('#pph21').val(par.editlistlast[0].pph21)
                    $('#cost_serikat_nama').val(par.editlistlast[0].cost_serikat_nama)
                    $('#cost_serikat_rp').val(par.editlistlast[0].cost_serikat_rp)
                    $('#toko').val(par.editlistlast[0].toko)
                    $('#lainlain').val(par.editlistlast[0].lainlain)

                    $('#potongan_absen').val(par.editlistlast[0].potongan_absen)
                    $('#potongan_absen_rp').val(par.editlistlast[0].potongan_absen_rp)
                    $('#gaji_pokok_dibayar').val(par.editlistlast[0].gaji_pokok_dibayar)

                    $('#lembur').val(par.editlistlast[0].lembur)
                    $('#lembur_rp').val(par.editlistlast[0].lembur_rp)

                    $('#s3').val(par.editlistlast[0].s3)
                    $('#s3_rp').val(par.editlistlast[0].s3_rp)
                
                    $('#gp').val(par.editlistlast[0].gp)
                    $('#gp_rp').val(par.editlistlast[0].gp_rp)
                
                    $('#koreksi_plus').val(par.editlistlast[0].koreksi_plus)
                    $('#koreksi_minus').val(par.editlistlast[0].koreksi_minus)
                }
                else
                {
                    $('#gaji_pokok').val(par.gaji_pokok)
                    $('#bruto_rp').val(par.bruto_rp)
                    $('#tot_akhir').val(par.tot_akhir)
                    $('#tot_bayar').val(par.tot_bayar)

                    $('#tunjangan_jabatan').val(par.tunjangan_jabatan)
                    $('#tunjangan_prestasi').val(par.tunjangan_prestasi)
                    $('#tunjangan_haid').val(par.tunjangan_haid)
                    $('#tunjangan_hadir').val(par.tunjangan_hadir)
                    $('#tunjangan_lain').val(par.tunjangan_lain)

                    $('#bpjs_tk').val(parseInt(par.bpjs_tk))
                    $('#bpjs_kes').val(parseInt(par.bpjs_kes))
                    $('#bpjs_pen').val(parseInt(par.bpjs_pen))
                    $('#pph21').val(par.pph21)
                    $('#cost_serikat_nama').val(par.cost_serikat_nama)
                    $('#cost_serikat_rp').val(par.cost_serikat_rp)
                    $('#toko').val(par.toko)
                    $('#lainlain').val(par.lainlain)

                    $('#potongan_absen').val(par.potongan_absen)
                    $('#potongan_absen_rp').val(par.potongan_absen_rp)
                    $('#gaji_pokok_dibayar').val(par.gaji_pokok_dibayar)

                    $('#lembur').val(par.lembur)
                    $('#lembur_rp').val(par.lembur_rp)

                    $('#s3').val(par.s3)
                    $('#s3_rp').val(par.s3_rp)
                
                    $('#gp').val(par.gp)
                    $('#gp_rp').val(par.gp_rp)
                
                    $('#koreksi_plus').val(par.koreksi_plus)
                    $('#koreksi_minus').val(par.koreksi_minus)
                }
                    
            }
            else
            {
                resetDoc();
            }
                
        }

        var resetDoc = function()
        {
            $('#id').val('')
            $('#nama_karyawan').val('')
            $('#karyawan_id').val('')
            $('#divisi').val('')
            $('#tanggal_masuk').val('')
            $('#gaji_pokok').val('')
            $('#bruto_rp').val('')
            $('#tot_akhir').val('')
            $('#tot_bayar').val('')

            $('#tunjangan_jabatan').val('')
            $('#tunjangan_prestasi').val('')
            $('#tunjangan_haid').val('')
            $('#tunjangan_hadir').val('')
            $('#tunjangan_lain').val('')

            $('#bpjs_tk').val('')
            $('#bpjs_kes').val('')
            $('#bpjs_pen').val('')
            $('#pph21').val('')
            $('#cost_serikat_nama').val('')
            $('#cost_serikat_rp').val('')
            $('#toko').val('')
            $('#lainlain').val('')

            $('#potongan_absen').val('')
            $('#potongan_absen_rp').val('')
            $('#gaji_pokok_dibayar').val('')

            $('#lembur').val('')
            $('#lembur_rp').val('')

            $('#s3').val('')
            $('#s3_rp').val('')
            
            $('#gp').val('')
            $('#gp_rp').val('')
            
            $('#koreksi_plus').val('')
            $('#koreksi_minus').val('')
        }

        var calculateData = function()
        {
            // var  = $('#nama_karyawan').val()
            // var  = $('#karyawan_id').val()
            // var  = $('#divisi').val()
            // var  = $('#tanggal_masuk').val()
            var gaji_pokok = parseInt($('#gaji_pokok').val())
            // var  = $('#bruto_rp').val()
            // var  = $('#tot_akhir').val()
            // var  = $('#tot_bayar').val()

            var tunjangan_jabatan = parseInt($('#tunjangan_jabatan').val())
            var tunjangan_prestasi = parseInt($('#tunjangan_prestasi').val())
            var tunjangan_haid = parseInt($('#tunjangan_haid').val())
            var tunjangan_hadir = parseInt($('#tunjangan_hadir').val())
            var tunjangan_lain = parseInt($('#tunjangan_lain').val())

            var bpjs_tk = parseInt($('#bpjs_tk').val())
            var bpjs_kes = parseInt($('#bpjs_kes').val())
            var bpjs_pen = parseInt($('#bpjs_pen').val())
            var pph21 = parseInt($('#pph21').val())
            var cost_serikat_nama = parseInt($('#cost_serikat_nama').val())
            var cost_serikat_rp = parseInt($('#cost_serikat_rp').val())
            var toko = parseInt($('#toko').val())
            var lainlain = parseInt($('#lainlain').val())

            var potongan_absen = parseInt($('#potongan_absen').val())
            // var potongan_absen_rp = $('#potongan_absen_rp').val()
            // var gaji_pokok_dibayar = $('#gaji_pokok_dibayar').val()

            var lembur = parseInt($('#lembur').val())
            // var  = $('#lembur_rp').val()

            var s3 = parseInt($('#s3').val())
            // var  = $('#s3_rp').val()
            
            var gp = parseInt($('#gp').val())
            var gp_rp = parseInt($('#gp_rp').val())
            
            var koreksi_plus = parseInt($('#koreksi_plus').val())
            var koreksi_minus = parseInt($('#koreksi_minus').val())

            var harian = parseInt(gaji_pokok / 30)
            var potonganAbsen = harian * potongan_absen

            var tunjS3 = s3 * 7500
            var lemburRp = 0
            @if(config('global.perusahaan_short') == 'Indah Jaya')
                lemburRp = parseInt((lembur / 173 * (gaji_pokok + tunjangan_jabatan + tunjangan_prestasi)));
            @else
                lemburRp = parseInt((lembur / 173 * (gaji_pokok + tunjangan_jabatan)));
            @endif

            var gaji_pokok_dibayar = parseInt(gaji_pokok - potonganAbsen);
            
            var brutto = parseInt(gaji_pokok_dibayar + lemburRp + tunjS3 + tunjangan_jabatan + tunjangan_prestasi + tunjangan_haid + tunjangan_hadir + gp_rp + koreksi_plus - koreksi_minus)
            var jumlahPotongan = bpjs_tk + bpjs_kes + bpjs_pen + pph21 + cost_serikat_rp + toko + lainlain;
            var totAkhir = brutto - jumlahPotongan
            var totBayar = parseInt(totAkhir/100) * 100

            // console.log('')

            $('#bruto_rp').val(brutto)
            $('#tot_akhir').val(totAkhir)
            $('#tot_bayar').val(totBayar)
            $('#potongan_absen_rp').val(potonganAbsen)
            $('#gaji_pokok_dibayar').val(gaji_pokok_dibayar)
            $('#lembur_rp').val(lemburRp)
            $('#s3_rp').val(tunjS3)

            // console.log(harian)
        }
    </script>
@endsection