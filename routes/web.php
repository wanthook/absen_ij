<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// abort(404);
Auth::routes();
Route::group(['middleware' => 'auth'],function()
{
    Route::get('/', ['as' => 'dashboard', 'uses' => 'DashboardController@index']);
    
    Route::group(['prefix' => 'hr'], function()
    {
        Route::group(['prefix' => 'master'], function()
        {
            Route::get('divisi', ['as' => 'mdivisi', 'uses' => 'DivisiController@index']);        
            Route::get('jabatan', ['as' => 'mjabatan', 'uses' => 'JabatanController@index']);        
            Route::get('alasan', ['as' => 'malasan', 'uses' => 'AlasanController@index']);        
            Route::get('perusahaan', ['as' => 'mperusahaan', 'uses' => 'PerusahaanController@index']);
            Route::get('mesin', ['as' => 'mmesin', 'uses' => 'MesinController@index']);
            Route::get('jamkerja', ['as' => 'mjamkerja', 'uses' => 'JamKerjaController@index']);        
            Route::get('transaksi-libur-nasional', ['as' => 'mlibur', 'uses' => 'LiburController@index']);

            Route::get('karyawan', ['as' => 'mkaryawan', 'uses' => 'KaryawanController@index']);
            Route::get('form-karyawan', ['as' => 'mkaryawanf', 'uses' => 'KaryawanController@create']);
            Route::get('edit-karyawan/{id}', ['as' => 'mkaryawane', 'uses' => 'KaryawanController@edit']);
        });

        Route::group(['prefix' => 'jadwal-kerja'], function()
        {
            Route::get('day-shift', ['as' => 'jkdayshift', 'uses' => 'JadwalController@dayshift']);        
            Route::get('shift', ['as' => 'jkshift', 'uses' => 'JadwalController@shift']);          
            Route::get('set-manual', ['as' => 'jkmanual', 'uses' => 'JadwalController@jadwalmanual']); 

            Route::get('set-jadwal', ['as' => 'jkset', 'uses' => 'JadwalController@setjadwal']);  
            Route::get('set-jadwal-add/{id}', ['as' => 'jksetadd', 'uses' => 'JadwalController@createsetjadwal']);  
        });

        Route::group(['prefix' => 'transaksi'], function()
        {
            Route::get('tarik-absen', ['as' => 'ttarikabsen', 'uses' => 'MesinController@indexTarik']);
            Route::get('transaksi-alasan', ['as' => 'talasan', 'uses' => 'KaryawanController@indexAlasan']);
            Route::get('status-karyawan', ['as' => 'tstatuskaryawan', 'uses' => 'KaryawanController@indexStatusKaryawan']);
            Route::get('proses-absensi', ['as' => 'tprosesabsen', 'uses' => 'ProsesabsenController@index']);
            Route::get('absen-manual', ['as' => 'tabsenmanual', 'uses' => 'ActivityManualController@index']);
        });


        Route::group(['prefix' => 'laporan'], function()
        {
            Route::get('laporan-detail', ['as' => 'ldetail', 'uses' => 'LaporanController@indexDetail']);
            Route::get('laporan-komulatif', ['as' => 'lkomulatif', 'uses' => 'LaporanController@indexKomulatif']);
            Route::get('laporan-karyawan-aktif', ['as' => 'lkaryawanaktif', 'uses' => 'LaporanController@indexKaryawanAktif']);
            Route::get('laporan-karyawan-mangkir-ta', ['as' => 'lkaryawanmangkirta', 'uses' => 'LaporanController@indexKaryawanMangkirTa']);
            Route::get('laporan-karyawan-habis-kontrak', ['as' => 'lkaryawanhabiskontrak', 'uses' => 'LaporanController@indexKaryawanHabisKontrak']);
            Route::get('laporan-karyawan-daftar-hadir', ['as' => 'lkaryawandaftarhadir', 'uses' => 'LaporanController@indexKaryawanDaftarHadir']);
            Route::get('laporan-karyawan-rekap-absen', ['as' => 'lkaryawanrekapabsen', 'uses' => 'LaporanController@indexKaryawanRekapAbsen']);
            Route::get('laporan-transaksi-alasan', ['as' => 'ltransaksialasan', 'uses' => 'LaporanController@indexTransaksiAlasan']);
            Route::get('laporan-log-jam-masuk', ['as' => 'llogjammasuk', 'uses' => 'LaporanController@indexLogJamMasuk']);
        });

        Route::group(['prefix' => 'administrator'], function()
        {
            Route::get('module', ['as' => 'admmodule', 'uses' => 'ModuleController@index']);
            Route::get('users', ['as' => 'admuser', 'uses' => 'Auth\RegisterController@index']);
            Route::get('master_option', ['as' => 'admmasteroption', 'uses' => 'MasterOptionController@index']);
            
        });
    });
    
    Route::group(['prefix' => 'payroll'], function()
    {
        Route::group(['prefix' => 'salary'], function()
        {
            Route::get('salary-master', ['as' => 'salarymaster', 'uses' => 'SalaryController@index']);
        });
    });
    
    Route::group(['prefix' => 'api'], function()
    {
        Route::group(['prefix' => 'hr'], function()
        {
            Route::post('divisidt', ['as' => 'dtdivisi', 'uses' => 'DivisiController@dt']);
            Route::post('divisisave', ['as' => 'savedivisi', 'uses' => 'DivisiController@store']);
            Route::post('divisidel', ['as' => 'deldivisi', 'uses' => 'DivisiController@destroy']);
            Route::post('divisisel', ['as' => 'seldivisi', 'uses' => 'DivisiController@select2']);
            Route::post('divisiupload', ['as' => 'uploaddivisi', 'uses' => 'DivisiController@storeUpload']);

            Route::post('jabatandt', ['as' => 'dtjabatan', 'uses' => 'JabatanController@dt']);
            Route::post('jabatansave', ['as' => 'savejabatan', 'uses' => 'JabatanController@store']);
            Route::post('jabatandel', ['as' => 'deljabatan', 'uses' => 'JabatanController@destroy']);
            Route::post('jabatansel', ['as' => 'seljabatan', 'uses' => 'JabatanController@select2']);

            Route::post('alasandt', ['as' => 'dtalasan', 'uses' => 'AlasanController@dt']);
            Route::post('alasansave', ['as' => 'savealasan', 'uses' => 'AlasanController@store']);
            Route::post('alasandel', ['as' => 'delalasan', 'uses' => 'AlasanController@destroy']);
            Route::post('alasansel', ['as' => 'selalasan', 'uses' => 'AlasanController@select2']);

            Route::post('perusahaandt', ['as' => 'dtperusahaan', 'uses' => 'PerusahaanController@dt']);
            Route::post('perusahaansave', ['as' => 'saveperusahaan', 'uses' => 'PerusahaanController@store']);
            Route::post('perusahaandel', ['as' => 'delperusahaan', 'uses' => 'PerusahaanController@destroy']);
            Route::post('perusahaansel', ['as' => 'selperusahaan', 'uses' => 'PerusahaanController@select2']);

            Route::post('liburfc', ['as' => 'fclibur', 'uses' => 'LiburController@fc']);  
            Route::post('libursave', ['as' => 'savelibur', 'uses' => 'LiburController@store']);  

            Route::post('karyawandt', ['as' => 'dtkaryawan', 'uses' => 'KaryawanController@dt']);
            Route::post('karyawandttalasan', ['as' => 'dttalasankaryawan', 'uses' => 'KaryawanController@dtTransaksiAlasan']);
            Route::post('karyawandtstatus', ['as' => 'dttstatuskaryawan', 'uses' => 'KaryawanController@dtStatus']);
            Route::post('karyawansave', ['as' => 'savekaryawan', 'uses' => 'KaryawanController@store']);
            Route::post('karyawansavealasan', ['as' => 'savealasankaryawan', 'uses' => 'KaryawanController@storeAlasanKaryawan']);
            Route::post('karyawansavemanual', ['as' => 'savejadwalmanual', 'uses' => 'KaryawanController@manualStore']);
            Route::post('karyawansavestatus/{kode}', ['as' => 'savestatuskaryawan', 'uses' => 'KaryawanController@storeStatusKaryawan']);
            Route::post('karyawanupload', ['as' => 'uploadkaryawan', 'uses' => 'KaryawanController@storeUpload']);
            Route::post('karyawanalasanupload', ['as' => 'uploadalasankaryawan', 'uses' => 'KaryawanController@storeUploadAlasan']);
            Route::post('karyawanjadwalupload', ['as' => 'uploadjadwalkaryawan', 'uses' => 'KaryawanController@storeUploadJadwal']);
            Route::post('karyawanjadwalmanualupload', ['as' => 'uploadjadwalmanualkaryawan', 'uses' => 'KaryawanController@storeUploadJadwalManual']);
            Route::post('karyawandel', ['as' => 'delkaryawan', 'uses' => 'KaryawanController@destroy']);
            Route::post('karyawandelalasan', ['as' => 'delalasankaryawan', 'uses' => 'KaryawanController@destroyAlasanKaryawan']);
            Route::post('karyawansel', ['as' => 'selkaryawan', 'uses' => 'KaryawanController@select2']);
            Route::post('karyawanstatussel', ['as' => 'selkaryawanstatus', 'uses' => 'MasterOptionController@select2karyawanstatus']);       
            Route::post('karyawanjadwalsetdt', ['as' => 'dtjadwalset', 'uses' => 'KaryawanController@dtJadwal']);    
            Route::post('karyawanjadwalsetdelete', ['as' => 'deljadwalkaryawan', 'uses' => 'KaryawanController@destroyJadwalKaryawan']); 
            //deljadwalkaryawan

            Route::post('karyawankeluargasave', ['as' => 'savekeluargakaryawan', 'uses' => 'KaryawanController@storeKeluarga']);
            Route::post('karyawankeluargadt', ['as' => 'dtkaryawankeluarga', 'uses' => 'KaryawanController@dtKel']);
            Route::post('karyawankeluargadel', ['as' => 'delkeluargakaryawan', 'uses' => 'KaryawanController@destroyKeluarga']);

            Route::post('mesindt', ['as' => 'dtmesin', 'uses' => 'MesinController@dt']);
            Route::post('mesinsave', ['as' => 'savemesin', 'uses' => 'MesinController@store']);
            Route::post('mesindel', ['as' => 'delmesin', 'uses' => 'MesinController@destroy']);
            Route::post('mesinsel', ['as' => 'selmesin', 'uses' => 'MesinController@select2']);
            Route::post('mesintarik', ['as' => 'tarikmesin', 'uses' => 'MesinController@tarikAbsen']);

            Route::post('jamkerjadt', ['as' => 'dtjamkerja', 'uses' => 'JamKerjaController@dt']);
            Route::post('jamkerjasave', ['as' => 'savejamkerja', 'uses' => 'JamKerjaController@store']);
            Route::post('jamkerjadel', ['as' => 'deljamkerja', 'uses' => 'JamKerjaController@destroy']);
            Route::post('jamkerjasel', ['as' => 'seljamkerja', 'uses' => 'JamKerjaController@select2']);


            Route::post('jadwaldaysave', ['as' => 'savejadwalday', 'uses' => 'JadwalController@dayshiftStore']);
            Route::post('jadwaldayupload', ['as' => 'uploadjadwalday', 'uses' => 'JadwalController@dayshiftUpload']);
            Route::post('jadwalshiftsave', ['as' => 'savejadwalshift', 'uses' => 'JadwalController@shiftStore']);
            Route::post('jadwalshiftupload', ['as' => 'uploadjadwalshift', 'uses' => 'JadwalController@shiftUpload']);
            Route::post('jadwalshiftcopy', ['as' => 'copyjadwalshift', 'uses' => 'JadwalController@shiftCopyStore']);
            Route::post('setjadwalsave', ['as' => 'savejadwalset', 'uses' => 'KaryawanController@storeJadwalKaryawan']);
            Route::post('jadwaldaydel', ['as' => 'deljadwalday', 'uses' => 'JadwalController@destroyjadwalday']);
            Route::post('jadwalshiftdel', ['as' => 'deljadwalshift', 'uses' => 'JadwalController@destroyjadwalshift']);
            Route::post('jadwalshiftfc', ['as' => 'fcjadwalshift', 'uses' => 'JadwalController@fc']);  
            Route::post('jadwalmanualfc', ['as' => 'fcjadwalmanual', 'uses' => 'JadwalController@fcmanual']);  
            Route::post('jadwalselect', ['as' => 'seljadwal', 'uses' => 'JadwalController@select2']);  
            Route::post('jadwaldaydt', ['as' => 'dtjadwalday', 'uses' => 'JadwalController@dtjadwalday']);        
            Route::post('jadwalshiftdt', ['as' => 'dtjadwalshift', 'uses' => 'JadwalController@dtjadwalshift']);
            Route::post('agamaselect', ['as' => 'selagama', 'uses' => 'MasterOptionController@select2agama']);
            Route::post('jenkelselect', ['as' => 'seljenkel', 'uses' => 'MasterOptionController@select2jenkel']);
            Route::post('goldarselect', ['as' => 'selgoldar', 'uses' => 'MasterOptionController@select2goldar']);
            Route::post('kawinselect', ['as' => 'selkawin', 'uses' => 'MasterOptionController@select2kawin']);
            Route::post('relasiselect', ['as' => 'selrelasi', 'uses' => 'MasterOptionController@select2relasi']);
            Route::post('keteranganstatusselect', ['as' => 'selketeranganstatus', 'uses' => 'MasterOptionController@select2keteranganstatus']);

            Route::post('absenproses', ['as' => 'prosesabsen', 'uses' => 'ProsesabsenController@proses']);

            Route::post('laporandetail', ['as' => 'detaillaporan', 'uses' => 'LaporanController@laporanDetail']);
            Route::post('laporankomulatif', ['as' => 'komulatiflaporan', 'uses' => 'LaporanController@laporanKomulatif']);
            Route::post('laporankaryawanaktif', ['as' => 'karyawanaktiflaporan', 'uses' => 'LaporanController@laporanKaryawanAktif']);
            Route::post('laporankaryawanmangkirta', ['as' => 'karyawanmangkirtalaporan', 'uses' => 'LaporanController@laporanKaryawanMangkirTa']);
            Route::post('laporankaryawanhabiskontrak', ['as' => 'karyawanhabiskontraklaporan', 'uses' => 'LaporanController@laporanKaryawanHabisKontrak']);
            Route::post('laporankaryawandaftarhadir', ['as' => 'karyawandaftarhadirlaporan', 'uses' => 'LaporanController@laporanKaryawanDaftarHadir']);
            Route::post('laporankaryawanrekapabsen', ['as' => 'karyawanrekapabsenlaporan', 'uses' => 'LaporanController@laporanKaryawanRekapAbsen']);
            Route::post('laporantransaksialasan', ['as' => 'transaksialasanlaporan', 'uses' => 'LaporanController@laporanTransaksiAlasan']);
            Route::post('laporanlogjammasuk', ['as' => 'logjammasuklaporan', 'uses' => 'LaporanController@laporanLogJamMasuk']);

            Route::post('absenmanualsave', ['as' => 'saveabsenmanual', 'uses' => 'ActivityManualController@store']);
            Route::post('absenmanualuploadsave', ['as' => 'saveabsenmanualupload', 'uses' => 'ActivityManualController@storeUploadAbsenManual']);
            Route::post('absenmanualdelete', ['as' => 'deleteabsenmanual', 'uses' => 'ActivityManualController@destroy']);
            Route::post('absenmanualdt', ['as' => 'dttabsenmanual', 'uses' => 'ActivityManualController@dt']);
        });
        
        Route::group(['prefix' => 'payroll'], function()
        {
            Route::post('salarysave', ['as' => 'savesalary', 'uses' => 'SalaryController@store']);
            Route::post('salaryjenisselect', ['as' => 'selsalaryjenis', 'uses' => 'MasterOptionController@select2jenissalary']);
            Route::post('salaryuploadsave', ['as' => 'savesalaryupload', 'uses' => 'SalaryController@storeUploadSalary']);
            Route::post('salarydelete', ['as' => 'deletesalary', 'uses' => 'SalaryController@destroy']);
            Route::post('salarydt', ['as' => 'dtsalary', 'uses' => 'SalaryController@dtMaster']);
        });
        
        Route::post('adminmoduledt', ['as' => 'dtadminmodule', 'uses' => 'ModuleController@dt']);        
        Route::post('adminmodulesave', ['as' => 'saveadminmodule', 'uses' => 'ModuleController@store']);
        Route::post('adminmoduledelete', ['as' => 'deleteadminmodule', 'uses' => 'ModuleController@destroy']);
        Route::post('adminmoduleparentselect', ['as' => 'seladminmoduleparent', 'uses' => 'ModuleController@select2parent']);
        Route::post('adminmoduletreeselect', ['as' => 'seladminmoduletree', 'uses' => 'ModuleController@select2tree']);
        
        Route::post('adminuserdt', ['as' => 'dtadminuser', 'uses' => 'Auth\RegisterController@dt']);        
        Route::post('adminusersave', ['as' => 'saveadminuser', 'uses' => 'Auth\RegisterController@store']);
        Route::post('adminuserdelete', ['as' => 'deleteadminuser', 'uses' => 'Auth\RegisterController@destroy']);
        Route::post('adminuserparentselect', ['as' => 'seladminuserparent', 'uses' => 'MasterOptionController@select2tipeuser']);
        
        Route::post('adminmasteroptiondt', ['as' => 'dtadminmasteroption', 'uses' => 'MasterOptionController@dt']);        
        Route::post('adminmasteroptionsave', ['as' => 'saveadminmasteroption', 'uses' => 'MasterOptionController@store']);
        Route::post('adminmasteroptiondelete', ['as' => 'deleteadminmasteroption', 'uses' => 'MasterOptionController@destroy']);
    });
    
    
    Route::get('files/{kode}',      ['as' => 'app.files', function ($kode)
    {
        //$path = storage_path("uploads/profiles/") . $kode;
        $path = "";
        switch($kode)
        {
            case "file_temp_dayshift":
                $path = storage_path('app').'/public/dayshift.xlsx';
                break;
            case "file_temp_shift":
                $path = storage_path('app').'/public/shift.xlsx';
                break;
            case "file_temp_karyawan":
                $path = storage_path('app').'/public/karyawan.xlsx';
                break;
            case "file_temp_karyawan_alasan":
                $path = storage_path('app').'/public/alasan_karyawan.xlsx';
                break;
            case "file_temp_karyawan_jadwal":
                $path = storage_path('app').'/public/jadwal_karyawan.xlsx';
                break;
            case "file_temp_karyawan_jadwal_manual":
                $path = storage_path('app').'/public/jadwal_manual_karyawan.xlsx';
                break;
            case "file_temp_absen_manual":
                $path = storage_path('app').'/public/absen_manual.xlsx';
                break;
            case "file_temp_salary":
                $path = storage_path('app').'/public/salary.xlsx';
                break;
            case "file_temp_divisi":
                $path = storage_path('app').'/public/divisi.xlsx';
                break;
            default:
                $path = "";
        }
        
        if($path)
        {
            $header = ['Content-Type' => File::mimeType($path)];
            return Response::download($path,'template.xlsx', $header);
        }
        else 
        {
            return abort(404);
        }
        
    }]);
    
    Route::get('progress-bar/{kode}', ['as' => 'app.progress', function ($kode)
    {
        $arr = array('percent' => 0,
                     'msg' => null);
        $ss = session($kode);
//        dd(session($kode));
        if($ss)
        {
            $arr = array('percent' => $ss['percent'],
                     'msg' => $ss['msg']);
        }
        
        
        echo json_encode($arr);
    }]);
});
 

// Route::get('/home', 'HomeController@index')->name('home');
