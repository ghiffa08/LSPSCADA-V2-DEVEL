<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.APL.01 - Page 3</title>
    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 2.5px;
        }
    </style>
</head>

<body>
    <h4>Bagian 3 : Bukti Kelengkapan Pemohon</h4>
    <h4>3.1 Bukti Persyaratan Dasar Pemohon</h4>

    <table>
        <thead>
            <tr style="text-align: center; background-color: #eee;">
                <th style="width: 5%;" rowspan="2"><b>No.</b></th>
                <th style="width: 50%;" rowspan="2"><b>Bukti Persyaratan Dasar</b></th>
                <th style="width: 30%;" colspan="2"><b>Ada</b></th>
                <th style="width: 15%;" rowspan="2"><b>Tidak Ada</b></th>
            </tr>
            <tr style="text-align: center; background-color: #eee;">
                <th style="width: 15%;"><b>Memenuhi Syarat</b></th>
                <th style="width: 15%;"><b>Tidak Memenuhi Syarat</b></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1.</td>
                <td>Fotocopy Kartu Keluarga</td>
                <td style="text-align: center;"><?= !empty($pengajuan['dokumen']['ktp']) ? 'Ada' : ''; ?></td>
                <td></td>
                <td></td>
            </tr>
            <?= $bukti_dasar_html; // Variabel dari controller untuk pas foto 
            ?>
            <tr>
                <td>3.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <br><br>
    <h4>3.2 Bukti Administratif</h4>

    <table>
        <thead>
            <tr style="text-align: center; background-color: #eee;">
                <th style="width: 5%;" rowspan="2"><b>No.</b></th>
                <th style="width: 50%;" rowspan="2"><b>Bukti Persyaratan Dasar</b></th>
                <th style="width: 30%;" colspan="2"><b>Ada</b></th>
                <th style="width: 15%;" rowspan="2"><b>Tidak Ada</b></th>
            </tr>
            <tr style="text-align: center; background-color: #eee;">
                <th style="width: 15%;"><b>Memenuhi Syarat</b></th>
                <th style="width: 15%;"><b>Tidak Memenuhi Syarat</b></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1.</td>
                <td>Fotocopy Raport</td>
                <td style="text-align: center;"><?= !empty($pengajuan['dokumen']['raport']) ? 'Ada' : ''; ?></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Fotocopy Sertifikat/Surat Keterangan PKL</td>
                <td style="text-align: center;"><?= !empty($pengajuan['dokumen']['sertifikat_pkl']) ? 'Ada' : ''; ?></td>
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
        </tbody>
    </table>

    <br><br>

    <table>
        <tr>
            <th style="width: 50%;" rowspan="4">
                <b>Rekomendasi (Diisi oleh LSP):</b><br>
                Berdasarkan Ketentuan Persyaratan Dasar, Maka Pemohon: <br><i><?= $status_apl1_html; ?></i> *) Sebagai Peserta Sertifikasi <br>
                <small>*coret yang tidak perlu</small>
            </th>
            <th style="width: 50%;" colspan="2"><b>Pemohon/Kandidat</b></th>
        </tr>
        <tr>
            <th style="width: 20%;">Nama</th>
            <th style="width: 30%; text-align: center;"><?= esc($pengajuan['asesi']['nama_lengkap']); ?></th>
        </tr>
        <tr>
            <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
            <td style="width: 30%; height: 70px; text-align: center;">
                <?php if ($qr_asesi): ?>
                    <img src="<?= $qr_asesi; ?>" alt="QR Asesi" width="70">
                <?php endif; ?>
            </td>
        </tr>
        <tr style="text-align: center;">
            <td><?= format_tanggal_indonesia($pengajuan['pengajuan']['created_at']); ?></td>
        </tr>

        <tr>
            <th rowspan="4"><b>Catatan :</b><br>Direkomendasikan untuk melanjutkan Asesmen</th>
            <th colspan="2"><b>Admin LSP</b></th>
        </tr>
        <tr>
            <th style="width: 20%;">Nama</th>
            <th style="width: 30%; text-align: center;"><?= esc($nama_admin); ?></th>
        </tr>
        <tr>
            <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
            <td style="width: 30%; height: 70px; text-align: center;">
                <?php if ($qr_admin): ?>
                    <img src="<?= $qr_admin; ?>" alt="QR Admin" width="70">
                <?php endif; ?>
            </td>
        </tr>
        <tr style="text-align: center;">
            <td>
                <?= !empty($pengajuan['pengajuan']['validated_at']) ? format_tanggal_indonesia($pengajuan['pengajuan']['validated_at']) : '...'; ?>
            </td>
        </tr>
    </table>
</body>

</html>