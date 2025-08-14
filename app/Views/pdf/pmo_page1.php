<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMO - <?= esc($observasi['nama_skema'] ?? '-') ?></title>
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

        th,
        td {
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

        .bold {
            font-weight: bold;
        }

        .small-text {
            font-size: 9px;
        }
    </style>
</head>

<body>
    <!-- Document Header -->
    <div class="header">FR.IA.03. PERTANYAAN UNTUK MENDUKUNG OBSERVASI</div>

    <!-- Info Table -->
    <table cellpadding="4">
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
            <td><?= esc($observasi['jenis_tuk'] ?? '-') ?> - <?= esc($observasi['nama_tuk'] ?? '-') ?></td>
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
            <td colspan="2">Tanggal</td>
            <td>:</td>
            <td><?= isset($observasi['tanggal_observasi']) ? esc(date('d/m/Y', strtotime($observasi['tanggal_observasi']))) : esc(date('d/m/Y')) ?></td>
        </tr>
    </table>

    <p class="small-text">
        <strong>Petunjuk:</strong><br>
        • Pertanyaan ini untuk mendukung observasi yang dilakukan asesor<br>
        • Jawab dengan "YA" atau "TIDAK" berdasarkan observasi yang telah dilakukan<br>
        • Berikan catatan tambahan jika diperlukan
    </p>

    <!-- Questions Table -->
    <table cellpadding="4" border="1" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th style="width: 5%;" class="center">No</th>
                <th style="width: 50%;" class="center">Pertanyaan</th>
                <th style="width: 15%;" class="center">YA</th>
                <th style="width: 15%;" class="center">TIDAK</th>
                <th style="width: 15%;" class="center">Catatan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($questions)): ?>
                <?php 
                $currentUnit = null;
                $questionNumber = 1;
                ?>
                <?php foreach ($questions as $question): ?>
                    <?php if ($currentUnit !== $question['id_unit']): ?>
                        <?php $currentUnit = $question['id_unit']; ?>
                        <tr>
                            <td colspan="5" class="bold" style="background-color: #f0f0f0;">
                                <?= esc($question['kode_unit']) ?> - <?= esc($question['nama_unit']) ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                    <tr>
                        <td class="center"><?= $questionNumber ?></td>
                        <td>
                            <?= esc($question['pertanyaan']) ?>
                            <?php if ($question['kuk_reference']): ?>
                                <br><small class="small-text"><em>Ref: <?= esc($question['kuk_reference']) ?></em></small>
                            <?php endif; ?>
                        </td>
                        <td class="center">
                            <span style="font-family: dejavusans;">
                                <?= ($question['pencapaian'] === 'ya') ? '☑' : '☐' ?>
                            </span>
                        </td>
                        <td class="center">
                            <span style="font-family: dejavusans;">
                                <?= ($question['pencapaian'] === 'tidak') ? '☑' : '☐' ?>
                            </span>
                        </td>
                        <td class="small-text">
                            <?= esc($question['jawaban'] ?? '') ?>
                            <?php if ($question['tanggapan_asesi']): ?>
                                <br><em>Tanggapan: <?= esc($question['tanggapan_asesi']) ?></em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php $questionNumber++; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="center">Tidak ada pertanyaan yang tersimpan</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Catatan Asesor -->
    <?php if (!empty($pmo['catatan_asesor'])): ?>
        <table cellpadding="4" border="1" cellspacing="0" width="100%" style="margin-top: 10px;">
            <tr>
                <td width="20%" class="bold">Catatan Asesor</td>
                <td width="80%"><?= esc($pmo['catatan_asesor']) ?></td>
            </tr>
        </table>
    <?php endif; ?>
</body>

</html>