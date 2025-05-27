<!DOCTYPE html>
<html lang="en">
<head>
    <title>Umpan Balik Asesi - <?= esc($observasi['nama_skema'] ?? '-') ?></title>
    <style>
        /* TCPDF compatible styles */
        body {
            font-family: helvetica, serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        .header {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 10px;
        }

        .center {
            text-align: center;
        }

        ul {
            margin: 5px 0 15px 0;
            padding-left: 20px;
        }

        .small-text {
            font-size: 10px;
            margin-top: 4px;
        }

        /* Symbol for checkboxes */
        .checkbox-checked {
            font-family: dejavusans, serif;
            font-size: 10pt;
        }

        .checkbox-unchecked {
            font-family: dejavusans, serif;
            font-size: 10pt;
        }
    </style>
</head>

<body>
    <!-- Document Header -->
    <div class="header">FR.AK.03. UMPAN BALIK DAN CATATAN ASESMEN</div>

    <!-- Info Table -->
    <table>
        <tr>
            <td rowspan="2" width="25%">Skema Sertifikasi<br>(KKNI/Okupasi/Klaster)</td>
            <td width="10%">Judul</td>
            <td width="5%">:</td>
            <td width="60%"><?= esc($observasi['nama_skema'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>Nomor</td>
            <td>:</td>
            <td><?= esc($observasi['kode_skema'] ?? '-') ?></td>
        </tr>
        <tr>
            <td colspan="2">TUK</td>
            <td>:</td>
            <td><?= esc($observasi['nama_tuk'] ?? '-') ?></td>
        </tr>
        <tr>
            <td colspan="2">Nama Asesor</td>
            <td>:</td>
            <td><?= esc($observasi['nama_asesor'] ?? '-') ?></td>
        </tr>
        <tr>
            <td colspan="2">Nama Asesi</td>
            <td>:</td>
            <td><?= esc($observasi['nama_asesi'] ?? '-') ?></td>
        </tr>
        <tr>
            <td rowspan="2" width="25%">Tanggal Asesmen</td>
            <td width="10%">Mulai</td>
            <td width="5%">:</td>
            <td width="60%"><?= isset($observasi['tanggal_mulai']) ? date('d/m/Y', strtotime($observasi['tanggal_mulai'])) : '-' ?></td>
        </tr>
        <tr>
            <td>Selesai</td>
            <td>:</td>
            <td><?= isset($observasi['tanggal_selesai']) ? date('d/m/Y', strtotime($observasi['tanggal_selesai'])) : '-' ?></td>
        </tr>
    </table>

    <p class="small-text">Umpan balik dari Asesi (diisi oleh Asesi setelah pengambilan keputusan):</p>

    <table cellpadding="4" cellspacing="0" width="100%">
        <!-- Header Row -->
        <thead>
            <tr>
                <th colspan="2" rowspan="2" style="width: 55%;" class="center">Komponen</th>
                <th colspan="2" style="width: 20%;" class="center">Hasil</th>
                <th rowspan="2" width="25%" class="center">Catatan/Komentar Asesi</th>
            </tr>
            <tr>
                <th style="width: 10%;" class="center">Ya</th>
                <th style="width: 10%;" class="center">Tidak</th>
            </tr>
        </thead>
        <tbody>
            <!-- Feedback Details -->
            <?php foreach ($details as $index => $detail): ?>
            <tr>
                <td width="5%" class="center"><?= $index + 1 ?>.</td>
                <td style="width:50%;"><?= esc($detail['pernyataan']) ?></td>
                <td width="10%" class="center">
                    <?php if ($detail['jawaban'] === 'Ya'): ?>
                    <span class="checkbox-checked">☑</span>
                    <?php else: ?>
                    <span class="checkbox-unchecked">☐</span>
                    <?php endif; ?>
                </td>
                <td width="10%" class="center">
                    <?php if ($detail['jawaban'] === 'Tidak'): ?>
                    <span class="checkbox-checked">☑</span>
                    <?php else: ?>
                    <span class="checkbox-unchecked">☐</span>
                    <?php endif; ?>
                </td>
                <td width="25%"><?= esc($detail['komentar']) ?></td>
            </tr>
            <?php endforeach; ?>

            <!-- Catatan Lainnya -->
            <tr>
                <td colspan="5" style="padding: 10px;">
                    <strong>Catatan/komentar lainnya (apabila ada):</strong><br>
                    <?= !empty($catatan_lain) ? esc($catatan_lain) : '- Tidak ada catatan tambahan -' ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Signatures -->
    <table border="0" cellpadding="4" cellspacing="0" style="margin-top: 20px; border: none;">
        <tr>
            <td width="50%" style="border: none; text-align: center; vertical-align: top;">
                <p>Tanggal: <?= isset($observasi['tanggal_selesai']) ? date('d/m/Y', strtotime($observasi['tanggal_selesai'])) : '..................' ?></p>
                <p>Tanda tangan Asesi:</p>
                <?php if (!empty($observasi['ttd_asesi_base64'])): ?>
                <img src="<?= $observasi['ttd_asesi_base64'] ?>" style="height: 60px;"><br>
                <?php else: ?>
                <div style="height: 60px; border-bottom: 1px dotted #000;"></div>
                <?php endif; ?>
                <p><strong><?= esc($observasi['nama_asesi'] ?? '..........................................') ?></strong></p>
            </td>
            <td width="50%" style="border: none; text-align: center; vertical-align: top;">
                <p>Tanggal: <?= isset($observasi['tanggal_selesai']) ? date('d/m/Y', strtotime($observasi['tanggal_selesai'])) : '..................' ?></p>
                <p>Tanda tangan Asesor:</p>
                <?php if (!empty($observasi['ttd_asesor_base64'])): ?>
                <img src="<?= $observasi['ttd_asesor_base64'] ?>" style="height: 60px;"><br>
                <?php else: ?>
                <div style="height: 60px; border-bottom: 1px dotted #000;"></div>
                <?php endif; ?>
                <p><strong><?= esc($observasi['nama_asesor'] ?? '..........................................') ?></strong></p>
            </td>
        </tr>
    </table>

</body>
</html>
