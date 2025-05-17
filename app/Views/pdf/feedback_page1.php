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
    <div class="header">FR.AK.03. UMPAN BALIK DAN CATATAN ASESMEN</div>

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

    <p class="small-text">Umpan balik dari Asesi (diisi oleh Asesi setelah pengambilan keputusan):</p>

    <table cellpadding="4" border="1" cellspacing="0" width="100%">
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
            <!-- Question 1 -->
            <tr>
                <td width="5%" class="center">1.</td>
                <td style="width:50%;" colspan="3">Saya mendapatkan penjelasan yang cukup memadai mengenai proses asesmen/uji kompetensi</td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="25%" class="center"></td>
            </tr>
            <tr>
                <td width="5%" class="center">2.</td>
                <td style="width:50%;" colspan="3">Saya diberikan kesempatan untuk mempelajari standar kompetensi yang akan diujikan dan menilai diri sendiri terhadap pencapaiannya</td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="25%" class="center"></td>
            </tr>
            <tr>
                <td width="5%" class="center">3.</td>
                <td style="width:50%;" colspan="3">Asesor memberikan kesempatan untuk mendiskusikan/menegosiasikan metoda, instrumen dan sumber asesmen serta jadwal asesmen</td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="25%" class="center"></td>
            </tr>
            <tr>
                <td colspan="7" style="height: 50px;">Catatan/komentar lainnya (apabila ada) :</td>
            </tr>
        </tbody>
    </table>

</body>

</html>