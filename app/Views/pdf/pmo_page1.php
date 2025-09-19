<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ceklis Observasi - <?= esc($pmo['nama_skema'] ?? '-') ?></title>
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
    <div class="header">FR.IA.03. PERTANYAAN UNTUK MENDUKUNG OBSERVASI
    </div>

    <!-- Info Table -->
    <table cellpadding="4">
        <tr>
            <td rowspan="2" width="25%">Skema Sertifikasi<br><?= $jenisSertifikasiFormatted ?? '-' ?></td>
            <td width="10%">Judul</td>
            <td width="5%">:</td>
            <td width="60%"><?= strtoupper(esc($pmo['nama_skema'] ?? '-')) ?></td>
        </tr>
        <tr>
            <td>Nomor</td>
            <td>:</td>
            <td><?= strtoupper(esc($pmo['kode_skema'] ?? '-')) ?></td>
        </tr>
        <tr>
            <td colspan="2">TUK</td>
            <td>:</td>
            <td><?= strtoupper(esc($pmo['nama_tuk'] ?? '-')) ?></td>
        </tr>
        <tr>
            <td colspan="2">Nama Asesor</td>
            <td>:</td>
            <td><?= strtoupper(esc($pmo['nama_asesor'] ?? '-')) ?></td>
        </tr>
        <tr>
            <td colspan="2">Nama Asesi</td>
            <td>:</td>
            <td><?= strtoupper(esc($pmo['nama_asesi'] ?? '-')) ?></td>
        </tr>
        <tr>
            <td colspan="2">Tanggal</td>
            <td>:</td>
            <td><?= isset($pmo['tanggal_observasi']) ?
                    esc(date('d/m/Y', strtotime($pmo['tanggal_observasi']))) :
                    '-' ?>
            </td>
        </tr>
    </table>

    <div style="clear:both; height:10px;"></div>

    <!-- Assessment Guide -->
    <div style="border: 1px solid #000;">
        <strong>PANDUAN BAGI ASESOR:</strong>
        <ul>
            <li style="margin-bottom: 4px;">Formulir ini di isi oleh asesor kompetensi dapat sebelum, pada saat atau setelah melakukan asesmen dengan metode observasi demonstrasi.</li>
            <li style="margin-bottom: 4px;">Pertanyaan dibuat dengan tujuan untuk menggali, dapat berisi pertanyaan yang berkaitan dengan dimensi kompetensi, batasan variabel dan aspek kritis yang relevan dengan skenario tugas dan praktik demonstrasi.</li>
            <li style="margin-bottom: 4px;">Jika pertanyaan disampaikan sebelum asesi melakukan praktik demonstrasi, maka pertanyaan dibuat berkaitan dengan aspek K3L, SOP, penggunaan peralatan dan perlengkapan.</li>
            <li style="margin-bottom: 4px;">Jika setelah asesi melakukan praktik demonstrasi terdapat item pertanyaan pendukung observasi telah terpenuhi, maka pertanyaan tersebut tidak perlu ditanyakan lagi dan cukup memberi catatan bahwa sudah terpenuhi pada saat tugas praktek demonstrasi pada kolom tanggapan.</li>
            <li style="margin-bottom: 4px;">Jika pada saat observasi ada hal yang perlu dikonfirmasi sedangkan di instrumen daftar pertanyaan pendukung observasi tidak ada, maka asesor dapat memberikan pertanyaan dengan syarat pertanyaan harus berkaitan dengan tugas praktek demonstrasi. Jika dilakukan, asesor harus mencatat dalam instrumen pertanyaan pendukung observasi.</li>
            <li>Tanggapan asesi ditulis pada kolom tanggapan.</li>
        </ul>
    </div>

    <div style="clear:both; height:10px;"></div>

    <!-- Bagian Kelompok Kerja dan Unit Kompetensi (Dinamis) -->
    <?php if (!empty($struktur['kelompok_kerja'])) : ?>
        <?php foreach ($struktur['kelompok_kerja'] as $kelompok) : ?>
            <table cellpadding="4" border="1" cellspacing="0" width="100%">
                <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                    <td width="20%" rowspan="<?= count($kelompok['units']) + 1 ?>"><?= esc($kelompok['nama_kelompok']) ?></td>
                    <td width="5%">No.</td>
                    <td width="20%">Kode Unit</td>
                    <td width="55%">Judul Unit</td>
                </tr>
                <?php if (!empty($kelompok['units'])) : ?>
                    <?php foreach ($kelompok['units'] as $index => $unit) : ?>
                        <tr>
                            <td class="center"><?= $index + 1 ?>.</td>
                            <td><?= esc($unit['kode_unit']) ?></td>
                            <td><?= esc($unit['nama_unit']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="3" class="center">Tidak ada unit kompetensi dalam kelompok ini.</td>
                    </tr>
                <?php endif; ?>
            </table>
            <div style="clear:both; height:10px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <table cellpadding="4" border="1" cellspacing="0" width="100%">
        <!-- Header Row -->
        <thead>
            <tr>
                <th rowspan="2" width="5%" class="center">No.</th>
                <th rowspan="2" width="70%" class="center">Pertanyaan</th>
                <th colspan="2" style="width: 25%;" class="center">Pencapaian</th>
            </tr>
            <tr>
                <th width="12.5%" class="center">Ya</th>
                <th width="12.5%" class="center">Tidak</th>
            </tr>
        </thead>
        <tbody>
            <?php $questionNum = 1; ?>
            <?php if (!empty($struktur['kelompok_kerja'])) : ?>
                <?php foreach ($struktur['kelompok_kerja'] as $kelompok) : ?>
                    <?php foreach ($kelompok['units'] as $unit) : ?>
                        <?php foreach ($unit['elemen'] as $elemen) : ?>
                            <?php foreach ($elemen['kuk'] as $kuk) : ?>
                                <?php foreach ($kuk['pertanyaan_list'] as $pertanyaan) : ?>
                                    <?php
                                    $jawaban = $jawaban_list[$pertanyaan['id_pertanyaan']] ?? null;
                                    $pencapaian = $jawaban['pencapaian'] ?? null;
                                    $tanggapan_asesor = $jawaban['jawaban_asesor'] ?? '';
                                    $tanggapan_asesi = $jawaban['tanggapan_asesi'] ?? '';
                                    ?>
                                    <tr>
                                        <td width="5%" rowspan="2" class="center"><?= $questionNum++ ?>.</td>
                                        <td style="width: 70%;" colspan="3"><b>Referensi: <?= esc($elemen['kode_elemen'] . '-' . $kuk['kode_kuk']) ?></b></td>
                                        <td width="12.5%" rowspan="2" class="center"></td>
                                        <td width="12.5%" rowspan="2" class="center"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"><?= esc($pertanyaan['pertanyaan']) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="height: 50px;"><b>Tanggapan:</b></td>
                                        <td width="12.5%" class="center"><span style="font-family: dejavusans;"><?= ($pencapaian === 'Y') ? '☑' : '☐' ?></span></td>
                                        <td width="12.5%" class="center"><span style="font-family: dejavusans;"><?= ($pencapaian === 'N') ? '☑' : '☐' ?></span></td>
                                    </tr>
                                    <?php endforeach; ?><?php endforeach; ?><?php endforeach; ?><?php endforeach; ?><?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" class="center">Tidak ada pertanyaan yang tersedia.</td>
                                    </tr>
                                <?php endif; ?>
        </tbody>
    </table>

    <div style="clear:both; height:10px;"></div>

    <!-- Signature Sections -->
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <tr>
            <td width="30%">Nama</td>
            <td width="35%">Asesi: <strong><?= strtoupper(esc($pmo['nama_asesi'] ?? '-')); ?></strong></td>
            <td width="35%">Asesor: <strong><?= strtoupper(esc($pmo['nama_asesor'] ?? '-')); ?></strong></td>
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