<!DOCTYPE html>
<html>

<head>
    <title>Laporan Asesmen Lengkap</title>
    <style>
        /* TCPDF compatible styles */
        body {
            font-family: helvetica;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 10px;
            border: 0.1mm solid #000;
        }

        th,
        td {
            border: 0.1mm solid #000;
            padding: 4px;
            vertical-align: top;
        }

        /* Remove double borders */
        tr+tr td {
            border-top: none;
        }

        td+td {
            border-left: none;
        }

        /* Ensure consistent border for cells */
        table[cellpadding="3"] td,
        table[cellpadding="4"] td {
            border: 0.1mm solid #000;
        }

        /* Rest of your existing styles */
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

        .grey-bg {
            background-color: #E0E0E0;
        }

        .light-grey-bg {
            background-color: #F0F0F0;
        }

        ul {
            margin: 5px 0 15px 0;
            padding-left: 20px;
        }

        .signature-cell {
            height: 60px;
        }

        .small-text {
            font-size: 10px;
            margin-top: 4px;
        }
    </style>
</head>

<body>
    <!-- Document Header -->
    <div class="header">FR.AK.05. LAPORAN ASESMEN</div>

    <!-- Info Table -->
    <table cellpadding="3" border="1" cellspacing="0" width="100%">
        <tr>
            <td rowspan="2" width="25%"><?= strtoupper($general_info['jenis_skema'] ?? 'SKEMA SERTIFIKASI<br>(KKNI/OKUPASI/KLASTER)') ?></td>
            <td width="10%">JUDUL</td>
            <td width="5%" class="center">:</td>
            <td width="60%"><?= strtoupper(esc($general_info['nama_skema'] ?? 'SEMUA SKEMA')) ?></td>
        </tr>
        <tr>
            <td>NOMOR</td>
            <td class="center">:</td>
            <td><?= strtoupper(esc($general_info['kode_skema'] ?? $general_info['nomor_skema'] ?? '-')) ?></td>
        </tr>
        <tr>
            <td colspan="2">TUK</td>
            <td class="center">:</td>
            <td><?= strtoupper(esc($general_info['nama_tuk'] ?? '-')) ?></td>
        </tr>
        <tr>
            <td colspan="2">NAMA ASESOR</td>
            <td class="center">:</td>
            <td><?= strtoupper(esc($general_info['nama_asesor'] ?? '-')) ?></td>
        </tr>
        <tr>
            <td colspan="2">TANGGAL</td>
            <td class="center">:</td>
            <td><?= isset($general_info['tanggal_asesmen']) ?
                    strtoupper(esc(format_tanggal_indonesia(date('Y-m-d', strtotime($general_info['tanggal_asesmen'] ?? ''))))) :
                    '-' ?>
            </td>
        </tr>
    </table>

    <div style="height:10px;"></div>

    <!-- Main Data Table -->
    <table cellpadding="3" border="1" cellspacing="0" width="100%">
        <thead>
            <tr class="bold">
                <th width="5%" class="center">NO</th>
                <th width="35%" class="center">NAMA ASESI</th>
                <th width="10%" class="center">K</th>
                <th width="10%" class="center">BK</th>
                <th width="25%" class="center">KETERANGAN**</th>
                <th width="15%" class="center">TANGGAL</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($laporan_data)): ?>
                <?php foreach ($laporan_data as $index => $asesi): ?>
                    <tr>
                        <td width="5%" class="center"><?= $index + 1 ?></td>
                        <td width="35%"><?= strtoupper(esc($asesi['nama_asesi'] ?? '-')) ?></td>
                        <td width="10%" class="center">
                            <?php if (($asesi['rekomendasi'] ?? '') === 'kompeten'): ?>
                                <span style="font-family: dejavusans;">☑</span>
                            <?php else: ?>
                                <span style="font-family: dejavusans;">☐</span>
                            <?php endif; ?>
                        </td>
                        <td width="10%" class="center">
                            <?php if (($asesi['rekomendasi'] ?? '') === 'belum_kompeten'): ?>
                                <span style="font-family: dejavusans;">☑</span>
                            <?php else: ?>
                                <span style="font-family: dejavusans;">☐</span>
                            <?php endif; ?>
                        </td>
                        <td width="25%" class="small-text">
                            <?php if (($asesi['rekomendasi'] ?? '') === 'belum_kompeten' && !empty($asesi['unit_bk_string'])): ?>
                                <?= strtoupper(esc($asesi['unit_bk_string'])) ?>
                            <?php endif; ?>
                        </td>
                        <td width="15%" class="center">
                            <?= isset($asesi['tanggal_rekaman']) ?
                                strtoupper(esc(date('d/m/Y', strtotime($asesi['tanggal_rekaman'])))) :
                                '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="center">TIDAK ADA DATA YANG DITEMUKAN</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p class="small-text">** Tuliskan Kode dan Judul Unit Kompetensi yang dinyatakan BK bila mengases satu skema</p>

    <div style="clear:both; height:10px;"></div>

    <table cellpadding="4" border="1" cellspacing="0" width="100%">
        <tr>
            <td rowspan="4">CATATAN :</td>
            <td colspan="2">ASESOR :</td>
        </tr>
        <tr>
            <td>NAMA</td>
            <td><?= strtoupper(esc($general_info['nama_asesor'] ?? 'SRI SAYUNINGSIH')) ?></td>
        </tr>
        <tr>
            <td>NO. REG</td>
            <td><?= strtoupper(esc($general_info['nomor_reg_asesor'] ?? 'MET. 000. 002233 2021')) ?></td>
        </tr>
        <tr>
            <td>TANDA TANGAN/ TANGGAL</td>
            <td style=" text-align: center; <?= empty($general_info['tanda_tangan_asesor']) ? 'height: 150px;' : '' ?>">
                <?php if (!empty($general_info['tanda_tangan_asesor'])): ?>
                    <img src="<?= esc($general_info['tanda_tangan_asesor']) ?>" alt="QR Code Asesor" style="width: 150px;">
                <?php endif; ?>
            </td>
        </tr>
    </table>

</body>

</html>