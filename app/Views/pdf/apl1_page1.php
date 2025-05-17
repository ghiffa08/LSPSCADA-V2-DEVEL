<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <h4>FR.APL.01. PERMOHONAN DATA PEMOHON SERTIFIKASI KOMPETENSI</h4>
    <h4>Bagian 1 : Rincian Data Pemohon Sertifikasi</h4>
    <p>Pada bagian ini, cantumlah data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.</p>
    <ol type="a">
        <li>
            <table>
                <tr>
                    <th>Data Pribadi</th>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Nama Lengkap</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= htmlspecialchars($apl1['asesi']['nama']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">No. KTP/NIK/Paspor</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= htmlspecialchars($apl1['asesi']['nik']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Tempat / Tanggal Lahir</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= htmlspecialchars($apl1['asesi']['tempat_lahir']) . ', ' . htmlspecialchars($apl1['asesi']['tanggal_lahir']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Jenis Kelamin</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= $jenisKelaminFormatted; ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Kebangsaan</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= htmlspecialchars($apl1['asesi']['kebangsaan']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Alamat Rumah</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= 'Kel. ' . htmlspecialchars($apl1['asesi']['alamat']['desa_nama']) . ', Kec. ' . htmlspecialchars($apl1['asesi']['alamat']['kecamatan_nama']) . ', ' . htmlspecialchars($apl1['asesi']['alamat']['kabupaten_nama']) . ', Prov. ' . htmlspecialchars($apl1['asesi']['alamat']['provinsi_nama']) . ', Kode Pos: ' . htmlspecialchars($apl1['asesi']['alamat']['kode_pos']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">&#160;</th>
                    <td style="width: 5%;"></td>
                    <td style="width: 55%; border-bottom: 1px solid #000;">HP: <?= htmlspecialchars($apl1['asesi']['no_hp']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">No Telepon/Email</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;">Rumah: <?= htmlspecialchars($apl1['asesi']['telpon_rumah']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">&#160;</th>
                    <td style="width: 5%;"></td>
                    <td style="width: 55%; border-bottom: 1px solid #000;">Email: <?= htmlspecialchars($apl1['asesi']['email']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Pendidikan Terakhir</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= htmlspecialchars($apl1['asesi']['pendidikan_terakhir']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Nama Sekolah/Universitas</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= htmlspecialchars($apl1['asesi']['nama_sekolah']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Jurusan</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= htmlspecialchars($apl1['asesi']['jurusan']); ?></td>
                </tr>
            </table>
        </li>
        <li>
            <table>
                <tr>
                    <th>Data Pekerjaan Sekarang</th>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Nama Institusi/Perusahaan</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= htmlspecialchars($apl1['asesi']['nama_lembaga']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Jabatan</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= htmlspecialchars($apl1['asesi']['jabatan']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">Alamat Perusahaan</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;"><?= htmlspecialchars($apl1['asesi']['alamat_perusahaan']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">No Telepon/Email</th>
                    <td style="width: 5%;">:</td>
                    <td style="width: 55%; border-bottom: 1px solid #000;">Telp: <?= htmlspecialchars($apl1['asesi']['no_telp_perusahaan']); ?></td>
                </tr>
                <tr>
                    <td>&#160;</td>
                </tr>
                <tr>
                    <th style="width: 30%;">&#160;</th>
                    <td style="width: 5%;"></td>
                    <td style="width: 55%; border-bottom: 1px solid #000;">Email: <?= htmlspecialchars($apl1['asesi']['email_perusahaan']); ?></td>
                </tr>
            </table>
        </li>
    </ol>

</body>

</html>