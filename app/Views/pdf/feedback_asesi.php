<?php
/**
 * PDF Template for Feedback Asesi (FR.IA.06)
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.IA.06. UMPAN BALIK DARI ASESI</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        h1, h2, h3 {
            text-align: center;
            font-weight: bold;
            margin: 10px 0;
        }
        h1 {
            font-size: 14pt;
        }
        h2 {
            font-size: 13pt;
        }
        h3 {
            font-size: 12pt;
        }
        .important {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            padding: 5px;
        }
        td {
            padding: 5px 8px;
            vertical-align: top;
        }
        .table-header {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .table-bordered {
            border: 1px solid #000;
        }
        .section-title {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        .signature {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature table {
            border: none;
        }
        .signature table td {
            text-align: center;
            border: none;
            vertical-align: bottom;
            padding: 5px;
        }
        .signature img {
            max-height: 80px;
            margin-bottom: 5px;
        }
        .page-number {
            position: absolute;
            bottom: 15px;
            width: 100%;
            text-align: center;
            font-size: 9pt;
        }
        .underline {
            text-decoration: underline;
        }
        .center {
            text-align: center;
        }
        .justified {
            text-align: justify;
        }
        .logo {
            max-height: 80px;
            margin-bottom: 5px;
        }
        .checkbox {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1px solid #000;
            vertical-align: middle;
            text-align: center;
            line-height: 12px;
        }
        .checked {
            font-weight: bold;
        }
        .unchecked {
            color: #FFF;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FR.IA.06. UMPAN BALIK DARI ASESI</h1>
    </div>

    <table class="table-bordered">
        <tr>
            <td width="25%"><strong>Nama Asesi</strong></td>
            <td width="75%"><?= $feedback['nama_asesi'] ?? '-' ?></td>
        </tr>
        <tr>
            <td><strong>Nama Asesor</strong></td>
            <td><?= $feedback['nama_asesor'] ?? '-' ?></td>
        </tr>
        <tr>
            <td><strong>Skema Sertifikasi</strong></td>
            <td><?= $feedback['nama_skema'] ?? '-' ?></td>
        </tr>
        <tr>
            <td><strong>Kode Skema</strong></td>
            <td><?= $feedback['kode_skema'] ?? '-' ?></td>
        </tr>
        <tr>
            <td><strong>Tanggal Asesmen</strong></td>
            <td>
                <?php
                    $tanggal_mulai = isset($feedback['tanggal_mulai']) ? date('d F Y', strtotime($feedback['tanggal_mulai'])) : '-';
                    $tanggal_selesai = isset($feedback['tanggal_selesai']) ? date('d F Y', strtotime($feedback['tanggal_selesai'])) : '-';
                    echo $tanggal_mulai . ' s/d ' . $tanggal_selesai;
                ?>
            </td>
        </tr>
    </table>

    <p class="justified">Untuk memperbaiki proses asesmen, kami mohon Anda memberikan umpan balik dengan mengisi formulir ini dan kembalikan kepada asesor.</p>

    <table class="table-bordered">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="55%">Pernyataan</th>
                <th width="20%">Jawaban</th>
                <th width="20%">Komentar</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($detailFeedback as $item): ?>
            <tr>
                <td class="center"><?= $no++ ?></td>
                <td><?= $item['pernyataan'] ?? '' ?></td>
                <td class="center">
                    <?php if ($item['jawaban'] === 'Y'): ?>
                        <strong>Ya</strong>
                    <?php elseif ($item['jawaban'] === 'T'): ?>
                        <strong>Tidak</strong>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= $item['komentar'] ?? '' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (!empty($feedback['catatan_lain'])): ?>
    <div class="section-title">Catatan Lain:</div>
    <p class="justified"><?= $feedback['catatan_lain'] ?></p>
    <?php endif; ?>

    <div class="signature">
        <table>
            <tr>
                <td width="50%">
                    <strong>Tanda Tangan Asesi:</strong><br/><br/>
                    <?php if (!empty($qr_asesi)): ?>
                        <img src="<?= $qr_asesi ?>" alt="QR Code Asesi" width="100"><br/>
                    <?php elseif (!empty($feedback['ttd_asesi_base64'])): ?>
                        <img src="<?= $feedback['ttd_asesi_base64'] ?>" alt="Tanda Tangan Asesi" width="150"><br/>
                    <?php else: ?>
                        <br/><br/><br/><br/><br/>
                    <?php endif; ?>
                    <strong><?= $feedback['nama_asesi'] ?? '' ?></strong>
                </td>
                <td width="50%">
                    <strong>Tanda Tangan Asesor:</strong><br/><br/>
                    <?php if (!empty($qr_asesor)): ?>
                        <img src="<?= $qr_asesor ?>" alt="QR Code Asesor" width="100"><br/>
                    <?php elseif (!empty($feedback['ttd_asesor_base64'])): ?>
                        <img src="<?= $feedback['ttd_asesor_base64'] ?>" alt="Tanda Tangan Asesor" width="150"><br/>
                    <?php else: ?>
                        <br/><br/><br/><br/><br/>
                    <?php endif; ?>
                    <strong><?= $feedback['nama_asesor'] ?? '' ?></strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-number">Halaman 1</div>
</body>
</html>
