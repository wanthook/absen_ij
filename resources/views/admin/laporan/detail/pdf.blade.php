@if(isset($var))
    @php
        $totLem = 0;
    @endphp
    <table class="font-family:sourcesanspro,sans-serif; font-style:normal;font-size:9pt;line-height:1em;color:#000;text-align:justify;width:30%">
        <tr>
            <td>PIN / Nama</td>
            <td>:</td>
            <td>{{$var['karyawan']->pin.' - '.$var['karyawan']->nama}}</td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td>:</td>
            <td>{{$var['karyawan']->divisi->kode.' - '.$var['karyawan']->divisi->deskripsi}}</td>
        </tr>
        <tr>
            <td>NIK</td>
            <td>:</td>
            <td>{{$var['karyawan']->nik}}</td>
        </tr>
    </table>
    <table class="detail">
        <thead>
            <tr>
                <th rowspan="2">Tanggal</th>
                <th colspan="2">Jadwal Kerja</th>
                <th colspan="2">Jam Kerja</th>
                <th colspan="2">Masuk</th>
                <th colspan="2">Pulang</th>
                <th rowspan="2">Keterangan</th>
                <th rowspan="2">Lembur<br>Aktual</th>
                <th rowspan="2">Hitung<br>Lembur</th>
                <th rowspan="2">Shift<br>Malam</th>
                <th rowspan="2">Lembur<br>Libur<br>Nas</th>
                <th rowspan="2">Hitung<br>Libur<br>Nas</th>
                <th rowspan="2">Total<br>Lembur</th>
            </tr>
            <tr>
                <th>M</th>
                <th>K</th>
                <th>M</th>
                <th>K</th>
                <th>C</th>
                <th>T</th>
                <th>C</th>
                <th>T</th>
            </tr>
        </thead>
        <tbody>
            @foreach($var['absen'] as $kabs => $vabs)            
            <tr>
                <td class="dc">{{$kabs}}</td>
                @if($vabs)
                @php
                $tLembur = $vabs->hitung_lembur + $vabs->hitung_lembur_ln;
                $totLem += $tLembur;
                @endphp
                <td class="dc">{{substr($vabs->jadwal_jam_masuk,0,5)}}</td>
                <td class="dc">{{substr($vabs->jadwal_jam_keluar,0,5)}}</td>
                <td class="dc">{{substr($vabs->jam_masuk,0,5)}}</td>
                <td class="dc">{{substr($vabs->jam_keluar,0,5)}}</td>
                <td class="dc">{{($vabs->n_masuk < 0)?abs($vabs->n_masuk):''}}</td>
                <td class="dc">{{($vabs->n_masuk > 0)?abs($vabs->n_masuk):''}}</td>
                <td class="dc">{{($vabs->n_keluar > 0)?abs($vabs->n_keluar):''}}</td>
                <td class="dc">{{($vabs->n_keluar < 0)?abs($vabs->n_keluar):''}}</td>
                <td>{{$vabs->keterangan}}</td>
                <td>{{$vabs->lembur_aktual}}</td>
                <td>{{$vabs->hitung_lembur}}</td>
                <td class="dc">{{$vabs->shift3}}</td>
                <td>{{$vabs->lembur_ln}}</td>
                <td>{{$vabs->hitung_lembur_ln}}</td>
                <td>{{($tLembur)?$tLembur:''}}</td>
                @else
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                @endif
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="dc" colspan="15"><strong>Total</strong></td>
                <td><strong>{{($totLem)?$totLem:'0.0'}}</strong></td>
            </tr>
        </tfoot>
    </table>
@endif