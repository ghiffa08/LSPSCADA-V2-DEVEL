<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.APL.01</title>
</head>

<body>
    <h4>FR.APL.01. PERMOHONAN DATA PEMOHON SERTIFIKASI KOMPETENSI</h4>

    <h4>Bagian 1 : Rincian Data Pemohon Sertifikasi</h4>
    <p>Pada bagian ini, cantumlah data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.</p>

    <table>
        <tr>
            <td style="width: 2%;"><b>a.</b></td>
            <td><b>Data Pribadi</b></td>
        </tr>
    </table>

    <table border="0" cellpadding="2">
        <tr>
            <td style="width: 30%;">Nama Lengkap</td>
            <td style="width: 5%;">:</td>
            <td style="width: 65%; border-bottom: 1px solid #000;"><?= esc($pengajuan['asesi']['nama_lengkap']); ?></td>
        </tr>
        <tr>
            <td>No. KTP/NIK/Paspor</td>
            <td>:</td>
            <td style="border-bottom: 1px solid #000;"><?= esc($pengajuan['asesi']['nik']); ?></td>
        </tr>
        <tr>
            <td>Tempat / Tanggal Lahir</td>
            <td>:</td>
            <td style="border-bottom: 1px solid #000;"><?= esc($pengajuan['asesi']['tempat_lahir'] . ', ' . format_tanggal_indonesia($pengajuan['asesi']['tanggal_lahir'])); ?></td>
        </tr>
        <tr>
            <td>Jenis Kelamin</td>
            <td>:</td>
            <td><?= $jenis_kelamin_html; ?></td>
        </tr>
        <tr>
            <td>Kebangsaan</td>
            <td>:</td>
            <td style="border-bottom: 1px solid #000;"><?= esc($pengajuan['asesi']['kebangsaan']); ?></td>
        </tr>
        <tr>
            <td rowspan="2">Alamat Rumah</td>
            <td rowspan="2">:</td>
            <td style="border-bottom: 1px solid #000;">
                Kel. <?= esc($pengajuan['asesi']['alamat']['desa_nama']); ?>, Kec. <?= esc($pengajuan['asesi']['alamat']['kecamatan_nama']); ?>, <?= esc($pengajuan['asesi']['alamat']['kabupaten_nama']); ?>, Prov. <?= esc($pengajuan['asesi']['alamat']['provinsi_nama']); ?>
            </td>
        </tr>
        <tr>
            <td style="border-bottom: 1px solid #000;">Kode Pos: <?= esc($pengajuan['asesi']['alamat']['kode_pos']); ?></td>
        </tr>
        <tr>
            <td rowspan="3">No Telepon/Email</td>
            <td rowspan="3">:</td>
            <td style="border-bottom: 1px solid #000;">HP: <?= esc($pengajuan['asesi']['no_hp']); ?></td>
        </tr>
        <tr>
            <td style="border-bottom: 1px solid #000;">Rumah: <?= esc($pengajuan['asesi']['telpon_rumah']); ?></td>
        </tr>
        <tr>
            <td style="border-bottom: 1px solid #000;">Email: <?= esc($pengajuan['asesi']['email']); ?></td>
        </tr>
        <tr>
            <td>Pendidikan Terakhir</td>
            <td>:</td>
            <td style="border-bottom: 1px solid #000;"><?= esc($pengajuan['asesi']['pendidikan_terakhir']); ?></td>
        </tr>
        <tr>
            <td>Nama Sekolah/Universitas</td>
            <td>:</td>
            <td style="border-bottom: 1px solid #000;"><?= esc($pengajuan['asesi']['nama_sekolah']); ?></td>
        </tr>
        <tr>
            <td>Jurusan</td>
            <td>:</td>
            <td style="border-bottom: 1px solid #000;"><?= esc($pengajuan['asesi']['jurusan']); ?></td>
        </tr>
    </table>


    <table>
        <tr>
            <td style="width: 2%;"><b>b.</b></td>
            <td><b>Data Pekerjaan Sekarang</b></td>
        </tr>
    </table>
    <table border="0" cellpadding="2" style="margin-left: 20px;">
        <tr>
            <td style="width: 30%;">Nama Institusi/Perusahaan</td>
            <td style="width: 5%;">:</td>
            <td style="width: 65%; border-bottom: 1px solid #000;"><?= esc($pengajuan['asesi']['nama_lembaga']); ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td style="border-bottom: 1px solid #000;"><?= esc($pengajuan['asesi']['jabatan']); ?></td>
        </tr>
        <tr>
            <td>Alamat Kantor</td>
            <td>:</td>
            <td style="border-bottom: 1px solid #000;"><?= esc($pengajuan['asesi']['alamat_perusahaan']); ?></td>
        </tr>
        <tr>
            <td rowspan="2">No Telepon/Email</td>
            <td rowspan="2">:</td>
            <td style="border-bottom: 1px solid #000;">Telp: <?= esc($pengajuan['asesi']['no_telp_perusahaan']); ?></td>
        </tr>
        <tr>
            <td style="border-bottom: 1px solid #000;">Email: <?= esc($pengajuan['asesi']['email_perusahaan']); ?></td>
        </tr>
    </table>
</body>

</html>