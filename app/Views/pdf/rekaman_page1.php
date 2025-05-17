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
    <div class="header">FR.AK.02. REKAMAN ASESMEN KOMPETENSI</div>

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
            <td rowspan="2" width="25%">Tanggal Asesmen</td>
            <td width="10%">Mulai</td>
            <td width="5%">:</td>
            <td width="60%"><?= esc($observasi['nama_skema'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>Selesai</td>
            <td>:</td>
            <td><?= esc($observasi['kode_skema'] ?? '-') ?></td>
        </tr>
    </table>

    <p class="small-text">Beri tanda centang (√) di kolom yang sesuai untuk mencerminkan bukti yang sesuai untuk setiap Unit Kompetensi.</p>

    <table cellpadding="4" border="1" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th style="width: 30%;" class="center">Unit Kompetensi</th>
                <th style="width: 10%;" class="center">Observasi Demonstrasi</th>
                <th style="width: 10%;" class="center">Portofolio</th>
                <th style="width: 10%;" class="center">Pernyataan Pihak Ketiga Pertanyaan Wawancara</th>
                <th style="width: 10%;" class="center">Pertanyaan Lisan</th>
                <th style="width: 10%;" class="center">Pertanyaan Tertulis</th>
                <th style="width: 10%;" class="center">Proyek Kerja</th>
                <th style="width: 10%;" class="center">Lainnya</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 30%;">Menggunakan Struktur Data</td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
            </tr>
            <tr>
                <td style="width: 30%;">Menggunakan Spesifikasi Program</td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
            </tr>
            <tr>
                <td style="width: 30%;">Menerapkan Perintah Eksekusi Bahasa, Pemrograman Berbasis Teks, Grafik, dan Multimedia</td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
            </tr>
            <tr>
                <td style="width: 30%;">Menulis Kode dengan Prinsip sesuai Gudlines dan Best Practices</td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
            </tr>
            <tr>
                <td style="width: 30%;">Mengimplementasikan Pemrograman Terstruktur</td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td style="width: 10%;" class="center"><span style="font-family: dejavusans;">☐</span></td>
            </tr>
            <tr>
                <td style="width: 30%;">Rekomendasi hasil asesmen</td>
                <td colspan="7"><span style="font-family: dejavusans;">☑ Kompeten / ☐ Belum kompeten</span></td>
            </tr>
            <tr>
                <td style="width: 30%;">Tindak lanjut yang dibutuhkan
                    (Masukkan pekerjaan tambahan dan asesmen yang diperlukan untuk mencapai kompetensi)
                </td>
                <td colspan="7"></td>
            </tr>
            <tr>
                <td style="width: 30%;">Komentar/ Observasi oleh asesor
                </td>
                <td colspan="7"></td>
            </tr>
        </tbody>
    </table>

</body>

</html>