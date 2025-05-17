<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>

<body>

    <h4>Bagian 3 : Bukti Kelengkapan Pemohon</h4>
    <h4>3.1 Bukti Persyaratan Dasar Pemohon</h4>

    <table>
        <tr style="text-align: center;">
            <th style="width: 5%;" rowspan="2">No.</th>
            <th style="width: 50%;" rowspan="2">Bukti Persyaratan Dasar</th>
            <th style="width: 30%;" colspan="2">Ada</th>
            <th style="width: 15%;" rowspan="2">Tidak Ada</th>
        </tr>
        <tr style="text-align: center;">
            <th style="width: 15%;">Memenuhi Syarat</th>
            <th style="width: 15%;">Tidak Memenuhi Syarat</th>
        </tr>
        <tr>
            <td>1.</td>
            <td>Fotocopy Kartu Keluarga</td>
            <td style="text-align: center;">Ada</td>
            <td></td>
            <td></td>
        </tr>
        <?= $buktiDasarFormatted ?>
        <tr>
            <td>3.</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <h4>3.2 Bukti Administratif</h4>

    <table>
        <tr style="text-align: center;">
            <th style="width: 5%;" rowspan="2">No.</th>
            <th style="width: 50%;" rowspan="2">Bukti Persyaratan Dasar</th>
            <th style="width: 30%;" colspan="2">Ada</th>
            <th style="width: 15%;" rowspan="2">Tidak Ada</th>
        </tr>
        <tr style="text-align: center;">
            <th>Memenuhi Syarat</th>
            <th>Tidak Memenuhi Syarat</th>
        </tr>
        <tr>
            <td>1.</td>
            <td>Fotocopy Raport</td>
            <td style="text-align: center;">Ada</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Fotocopy Sertifikat/Surat Keterangan PKL</td>
            <td style="text-align: center;">Ada</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Fotocopy Kartu Pelajar</td>
            <td style="text-align: center;">Ada</td>
            <td></td>
            <td></td>
        </tr>

    </table>

    <h4></h4>

    <table border="1" cellspacing="0" cellpadding="5">
        <tr>
            <th style="width: 50%;" rowspan="4">Rekomendasi (DIisi Oleh LSP):<br>Berdasarkan Ketentuan Persyaratan Dasar, Maka Pemohon<i>'<?= $statusFormatted ?></i> *) Sebagai Peserta Sertifikasi <br>*coret yang tidak perlu
            </th>
            <th style="width: 50%;" colspan="2">Pemohon/Kandidat</th>
        </tr>
        <tr>
            <th style="width: 20%;">Nama</th>
            <th style="width: 30%; text-align: center;">
                <?= htmlspecialchars($apl1['asesi']['nama']) ?>
            </th>
        </tr>
        <tr>
            <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
            <td style="width: 30%; text-align: center;">
                <img style="width: 150px;" src="<?= $qr_asesi ?>" alt="QR Code Asesi">
            </td>
        </tr>
        <tr>
            <td style="width: 30%; text-align: center;">
                <?= format_tanggal_indonesia($apl1['pengajuan']['created_at']) ?>
            </td>
        </tr>
        <tr>
            <th rowspan="4">Catatan :<br>Direkomendasikan untuk melanjutkan Asesmen
            </th>
            <th colspan="2">Admin LSP</th>
        </tr>
        <tr>
            <th style="width: 20%;">Nama</th>
            <th style="width: 30%; text-align: center;">
                <?= htmlspecialchars($nama_admin) ?>
            </th>
        </tr>
        <tr>
            <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
            <td style="width: 30%; text-align: center; <?= empty($qr_admin) ? 'height: 150px;' : '' ?>">
                <?php if (!empty($qr_admin)): ?>
                    <img src="<?= esc($qr_admin) ?>" alt="QR Code Admin" style="width: 150px;">
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td style="width: 30%; text-align: center;">
                <?= format_tanggal_indonesia($apl1['pengajuan']['updated_at']) ?>
            </td>
        </tr>
    </table>


</body>

</html>