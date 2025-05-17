<!DOCTYPE html>
<html>

<head>
    <title>Ceklis Observasi - <?= esc($skema['nama_skema'] ?? '-') ?></title>
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
            /* text-align: center; */
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
    <div class="header">FR.IA.01. CEKLIS OBSERVASI AKTIVITAS DI TEMPAT KERJA ATAU TEMPAT KERJA SIMULASI
    </div>

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
            <td colspan="2">Tanggal</td>
            <td>:</td>
            <td><?= isset($observasi['tanggal_observasi']) ?
                    esc(format_tanggal_indonesia(date('Y-m-d', strtotime($observasi['tanggal_observasi'])))) :
                    '-' ?>
            </td>
        </tr>
    </table>

    <div style="clear:both; height:10px;"></div>

    <!-- Assessment Guide -->
    <div style="border: 1px solid #000; margin-top: 10px;">
        <strong>PANDUAN BAGI ASESOR:</strong>
        <ul>
            <li>Lengkapi nama unit kompetensi, elemen, dan kriteria unjuk kerja sesuai kolom dalam tabel.</li>
            <li>Istilah Acuan Pembanding dengan SOP/spesifikasi produk dari industri/organisasi dari tempat kerja atau simulasi tempat kerja</li>
            <li>Beri tanda centang (√) pada kolom K jika Anda yakin asesi dapat melakukan/mendemonstrasikan tugas sesuai KUK, atau centang (√) pada kolom BK bila sebaliknya.</li>
            <li>Penilaian Lanjut diisi bila hasil belum dapat disimpulkan, untuk itu gunakan metode lain sehingga keputusan dapat dibuat.</li>
        </ul>
    </div>

    <!-- Competency Units -->
    <?php foreach (groupByKelompok($detailObservasi) as $kelompok): ?>
        <div style="clear:both; height:10px;"></div>
        <!-- Kelompok Table -->
        <table cellpadding="4" border="1" cellspacing="0" width="100%">
            <tr class="grey-bg bold center">
                <td width="20%" rowspan="<?= count($kelompok['units']) + 1 ?>">
                    <?= esc($kelompok['nama_kelompok']) ?>
                </td>
                <td width="5%">No.</td>
                <td width="20%">Kode Unit</td>
                <td width="55%">Judul Unit</td>
            </tr>

            <?php $index = 1; ?>
            <?php foreach ($kelompok['units'] as $unit): ?>
                <tr>
                    <td class="center"><?= $index++ ?>.</td>
                    <td><?= esc($unit['kode_unit']) ?></td>
                    <td><?= esc($unit['judul_unit']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>



        <!-- Unit Details -->
        <?php foreach ($kelompok['units'] as $unit): ?>
            <div style="clear:both; height:10px;"></div>

            <table cellpadding="4" border="1" cellspacing="0" width="100%" style="margin-top: 20px;">
                <!-- Unit Header -->
                <tr class="grey-bg">
                    <td colspan="7"><strong>Unit Kompetensi</strong></td>
                </tr>
                <tr>
                    <td width="15%"><strong>Kode Unit</strong></td>
                    <td colspan="6" width="85%"><?= esc($unit['kode_unit']) ?></td>
                </tr>
                <tr>
                    <td><strong>Judul Unit</strong></td>
                    <td colspan="6"><?= esc($unit['judul_unit']) ?></td>
                </tr>

                <!-- Table Header -->
                <tr class="grey-bg center">
                    <th rowspan="2" width="5%">No.</th>
                    <th rowspan="2" width="20%">Elemen</th>
                    <th rowspan="2" width="35%">Kriteria Unjuk Kerja*</th>
                    <th rowspan="2" width="15%">Benchmark<br>(SOP / spesifikasi produk industri)</th>
                    <th colspan="2" width="15%">Pencapaian</th>
                    <th rowspan="2" width="10%">Penilaian<br>Lanjut</th>
                </tr>
                <tr class="grey-bg center">
                    <th width="7%">Ya</th>
                    <th width="8%">Tidak</th>
                </tr>

                <!-- KUK Rows -->
                <?php
                $no = 1;
                $currentElemen = null;
                foreach ($unit['kuk'] as $kuk):
                    $id = $kuk['id_kuk'];
                    $kompeten = $existing_data[$id]['kompeten'] ?? '';
                    $keterangan = $existing_data[$id]['keterangan'] ?? '';
                    $elemenText = ($currentElemen !== $kuk['id_elemen']) ? esc($kuk['nama_elemen']) : '';
                    $currentElemen = $kuk['id_elemen'];
                ?>
                    <tr>
                        <td class="center"><?= $no++ ?></td>
                        <td><?= $elemenText ?></td>
                        <td><?= esc($kuk['kriteria_unjuk_kerja']) ?></td>
                        <td class="center">SOP</td>
                        <td class="center"><span style="font-family: dejavusans;"><?= $kompeten === 'Y' ? '☑' : '☐' ?></span></td>
                        <td class="center"><span style="font-family: dejavusans;"><?= $kompeten === 'N' ? '☑' : '☐' ?></span></td>
                        <td><?= esc($keterangan) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <div style="clear:both; height:10px;"></div>

    <!-- Signature Section -->
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <tr>
            <td width="30%">Nama</td>
            <td width="35%">Asesi: <strong><?= esc($observasi['nama_asesi'] ?? '-') ?></strong></td>
            <td width="35%">Asesor: <strong><?= esc($observasi['nama_asesor'] ?? '-') ?></strong></td>
        </tr>
        <tr>
            <td>Tanda Tangan dan Tanggal</td>
            <td class="signature-cell center">
                <?php if (!empty($qr_asesi)): ?>
                    <img src="<?= esc($qr_asesi) ?>" alt="QR Code Asesi" style="width: 150px;">
                <?php endif; ?>
            </td>
            <td class="signature-cell center">
                <?php if (!empty($qr_asesor)): ?>
                    <img src="<?= esc($qr_asesor) ?>" alt="QR Code Asesor" style="width: 150px;">
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <p class="small-text">*) Bila diperlukan</p>
</body>

</html>