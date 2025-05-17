<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <div class="header">FR.IA.03. PERTANYAAN UNTUK MENDUKUNG OBSERVASI
    </div>

    <!-- Info Table -->
    <table cellpadding="4">
        <tr>
            <td rowspan="2" width="25%">Skema Sertifikasi<br><?= $jenisSertifikasiFormatted ?></td>
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

    <table cellpadding="4" border="1" cellspacing="0" width="100%">
        <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
            <td width="20%" rowspan="3">Kelompok Pekerjaan 1 - Melakukan Perancangan</td>
            <td width="5%">No.</td>
            <td width="20%">Kode Unit</td>
            <td width="55%">Judul Unit</td>
        </tr>

        <!-- Unit 1 -->
        <tr>
            <td class="center">1.</td>
            <td>J.620100.004.01</td>
            <td>Menggunakan Struktur Data</td>
        </tr>

        <!-- Unit 2 -->
        <tr>
            <td class="center">2.</td>
            <td>J.620100.009.01</td>
            <td>Menggunakan Spesifikasi Program</td>
        </tr>
    </table>

    <div style="clear:both; height:10px;"></div>

    <table cellpadding="4" border="1" cellspacing="0" width="100%">
        <!-- Header Row -->
        <thead>
            <tr>
                <th colspan="2" rowspan="2" style="width: 75%;" class="center">Pertanyaan</th>
                <th colspan="2" style="width: 25%;" class="center">Pencapaian</th>
            </tr>
            <tr>
                <th width="12%" class="center">Ya</th>
                <th width="13%" class="center">Tidak</th>
            </tr>
        </thead>
        <tbody>
            <!-- Question 1 -->
            <tr>
                <td width="5%" rowspan="3" class="center">1.</td>
                <td style="width: 70%;" colspan="3">E2-KUK 2.1</td>
                <td width="12%" rowspan="3" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="13%" rowspan="3" class="center"><span style="font-family: dejavusans;">☐</span></td>
            </tr>
            <tr>
                <td colspan="3">Struktur data diimplementasikan sesuai dengan bahasa pemrograman yang akan dipergunakan</td>
            </tr>
            <tr>
                <td colspan="3">Bagaimana penanganan error dan exception dalam bahasa pemrograman diterapkan saat mengimplementasikan operasi-operasi pada struktur data?</td>
            </tr>
            <tr>
                <td colspan="6" style="height: 50px;">Tanggapan:</td>
            </tr>
        </tbody>
    </table>


</body>

</html>