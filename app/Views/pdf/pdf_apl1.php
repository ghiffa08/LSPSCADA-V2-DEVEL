<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        table,
        th,
        td {
            border-collapse: collapse;
            padding: 3px;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid black;
        }
    </style>
    <title>Form APL 1</title>
</head>

<body>
    <h4>FR.APL.01. PERMOHONAN DATA PEMOHON SERTIFIKASI KOMPETENSI</h4>
    <h4>Bagian 1 : Rincian Data Pemohon Sertifikasi</h4>
    <p>Pada bagian ini, cantumlah data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.</p>

    <ol type="a">
        <!-- Poin a: Data Pribadi -->
        <li>
            <table>
                <tr>
                    <th colspan="3">Data Pribadi</th>
                </tr>
                <tr>
                    <th style="width: 30%;">Nama Lengkap</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 65%; border-bottom: 1px solid #000;"><?= esc($apl1['nama_siswa']) ?></td>
                </tr>
                <tr>
                    <th>No. KTP/NIK/Paspor</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['nik']) ?></td>
                </tr>
                <tr>
                    <th>Tempat / Tanggal Lahir</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['tempat_lahir']) ?>, <?= date('d/m/Y', strtotime($apl1['tanggal_lahir'])) ?></td>
                </tr>
                <tr>
                    <th>Jenis Kelamin</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= $jenisKelaminFormatted ?></td>
                </tr>
                <tr>
                    <th>Kebangsaan</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['kebangsaan']) ?></td>
                </tr>
                <tr>
                    <th>Alamat Rumah</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;">
                        Kel. <?= esc($apl1['nama_kelurahan']) ?>, Kec. <?= esc($apl1['nama_kecamatan']) ?>, <?= esc($apl1['nama_kabupaten']) ?>, Prov. <?= esc($apl1['nama_provinsi']) ?>, Kode Pos: <?= esc($apl1['kode_pos']) ?>
                    </td>
                </tr>
                <tr>
                    <th>HP</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['no_hp']) ?></td>
                </tr>
                <tr>
                    <th>No Telepon</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['telpon_rumah']) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['email']) ?></td>
                </tr>
                <tr>
                    <th>Pendidikan Terakhir</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['pendidikan_terakhir']) ?></td>
                </tr>
                <tr>
                    <th>Nama Sekolah/Universitas</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['nama_sekolah']) ?></td>
                </tr>
                <tr>
                    <th>Jurusan</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['jurusan']) ?></td>
                </tr>
            </table>
        </li>

        <li>
            <table>
                <tr>
                    <th colspan="3">Data Pekerjaan Sekarang</th>
                </tr>
                <tr>
                    <th style="width: 30%;">Pekerjaan</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 65%; border-bottom: 1px solid #000;"><?= esc($apl1['pekerjaan']) ?></td>
                </tr>
                <tr>
                    <th>Nama Institusi/Perusahaan</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['nama_lembaga']) ?></td>
                </tr>
                <tr>
                    <th>Jabatan</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['jabatan']) ?></td>
                </tr>
                <tr>
                    <th>Alamat Perusahaan</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['alamat_perusahaan']) ?></td>
                </tr>
                <tr>
                    <th>No Telepon</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['no_telp_perusahaan']) ?></td>
                </tr>
                <tr>
                    <th>Email Perusahaan</th>
                    <td>:</td>
                    <td style="border-bottom: 1px solid #000;"><?= esc($apl1['email_perusahaan']) ?></td>
                </tr>
            </table>
        </li>
    </ol>

    <h4>Bagian 2 : Data Sertifikasi</h4>
    <p>Tuliskan Judul dan Nomor Skema Sertifikasi yang anda ajukan berikut Daftar Unit Kompetensi sesuai kemasan pada skema sertifikasi untuk mendapatkan pengakuan sesuai dengan latar belakang pendidikan, pelatihan serta pengalaman kerja yang anda miliki.</p>

    <table class="table-bordered">
        <tr>
            <td style="width: 30%;" rowspan="2">
                Skema Sertifikasi<br>
                <?= $jenisSertifikasiFormatted ?>
            </td>
            <td style="width: 10%;">Judul</td>
            <td style="width: 5%;">:</td>
            <td style="width: 55%;"><?= esc($apl1['nama_skema']) ?></td>
        </tr>
        <tr>
            <td>Nomor</td>
            <td>:</td>
            <td></td>
        </tr>
        <?php
        $tujuan = '';
        if ($apl1['tujuan'] == "Sertifikasi") {
            $tujuan = '
             <tr>
              <td colspan="2" rowspan="4">Tujuan Asesmen</td>
              <td>:</td>
              <td>Sertifikasi</td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Lainya</span></td>
            </tr>
            ';
        } elseif ($apl1['tujuan'] == "PKT") {
            $tujuan = '
             <tr>
              <td colspan="2" rowspan="4">Tujuan Asesmen</td>
              <td>:</td>
              <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
            </tr>
            <tr>
            <td></td>
            <td>Pengakuan Kompetensi Lampau (PKT)</td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Lainya</span></td>
            </tr>
            ';
        } elseif ($apl1['tujuan'] == "RPL") {
            $tujuan = '
             <tr>
              <td colspan="2" rowspan="4">Tujuan Asesmen</td>
              <td>:</td>
              <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
            </tr>
            <tr>
            <td></td>
            <td>Rekognisi Pembelajaran Lampau (RPL)</td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Lainya</span></td>
            </tr>
            ';
        } else {
            $tujuan = '
             <tr>
              <td colspan="2" rowspan="4">Tujuan Asesmen</td>
              <td>:</td>
              <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
            </tr>
            <tr>
            <td></td>
            <td>Lainya</td>
            </tr>
            ';
        }
        ?>
        <?= $tujuan ?>
    </table>

    <h4>Daftar Unit Kompetensi Sesuai Kemasan:</h4>
    <table class="table-bordered">
        <tr>
            <th style="width: 5%;">No.</th>
            <th style="width: 25%;">Kode Unit</th>
            <th style="width: 45%;">Judul Unit</th>
            <th style="width: 25%;">Standar Kompetensi Kerja</th>
        </tr>
        <?php foreach ($listUnit as $i => $unit): ?>
            <tr>
                <td><?= $i + 1 ?>.</td>
                <td><?= esc($unit['kode_unit']) ?></td>
                <td><?= esc($unit['nama_unit']) ?></td>
                <td style="text-align: center;">SKKNI</td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>