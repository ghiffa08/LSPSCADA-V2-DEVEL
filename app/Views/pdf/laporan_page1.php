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
    <div class="header">FR.AK.05. LAPORAN ASESMEN
    </div>

    <!-- Info Table -->
    <table cellpadding="4" border="1" cellspacing="0" width="100%">
        <tr>
            <td rowspan="2" width="25%">Skema Sertifikasi<br>(KKNI/Okupasi/Klaster)</td>
            <td width="10%">Judul</td>
            <td width="5%" style="center">:</td>
            <td width="60%"><?= esc($observasi['nama_skema'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>Nomor</td>
            <td style="center">:</td>
            <td><?= esc($observasi['kode_skema'] ?? '-') ?></td>
        </tr>
        <tr>
            <td colspan="2">TUK</td>
            <td style="center">:</td>
            <td><?= esc($observasi['nama_tuk'] ?? '-') ?></td>
        </tr>
        <tr>
            <td colspan="2">Nama Asesor</td>
            <td style="center">:</td>
            <td><?= esc($observasi['nama_asesor'] ?? '-') ?></td>
        </tr>
        <tr>
            <td colspan="2">Tanggal</td>
            <td style="center">:</td>
            <td><?= isset($observasi['tanggal_observasi']) ?
                    esc(format_tanggal_indonesia(date('Y-m-d', strtotime($observasi['tanggal_observasi'])))) :
                    '-' ?>
            </td>
        </tr>
    </table>

    <div style="clear:both; height:10px;"></div>

    <table cellpadding="4" border="1" cellspacing="0" width="100%">
        <!-- Header Row -->
        <thead>
            <tr>
                <th colspan="2" rowspan="2" style="width: 55%;" class="center">Nama Asesi</th>
                <th colspan="2" style="width: 20%;" class="center">Rekomendasi</th>
                <th rowspan="2" width="25%" class="center">Keterangan**</th>
            </tr>
            <tr>
                <th style="width: 10%;" class="center">K</th>
                <th style="width: 10%;" class="center">BK</th>
            </tr>
        </thead>
        <tbody>
            <!-- Question 1 -->
            <tr>
                <td width="5%" class="center">1.</td>
                <td style="width:50%;" colspan="3"></td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="25%" class="center"></td>
            </tr>
            <tr>
                <td width="5%" class="center">2.</td>
                <td style="width:50%;" colspan="3"></td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="25%" class="center"></td>
            </tr>
            <tr>
                <td width="5%" class="center">3.</td>
                <td style="width:50%;" colspan="3"></td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="10%" class="center"><span style="font-family: dejavusans;">☐</span></td>
                <td width="25%" class="center"></td>
            </tr>
        </tbody>
    </table>

    <p class="small-text">** tuliskan Kode dan Judul Unit Kompetensi yang dinyatakan BK bila mengases satu skema</p>

    <table cellpadding="4" border="1" cellspacing="0" width="100%">
        <tr>
            <td>Aspek Negatif dan Positif dalam Asesemen</td>
            <td></td>
        </tr>
        <tr>
            <td>Pencatatan Penolakan Hasil Asesmen</td>
            <td></td>
        </tr>
        <tr>
            <td>Saran Perbaikan :<br>(Asesor/Personil Terkait)
            </td>
            <td></td>
        </tr>
    </table>

    <div style="clear:both; height:10px;"></div>

    <table cellpadding="4" border="1" cellspacing="0" width="100%">
        <tr>
            <td rowspan="4">Catatan :</td>
            <td colspan="2">Asesor :</td>
        </tr>
        <tr>
            <td>Nama</td>
            <td>Sri Sayuningsih</td>
        </tr>
        <tr>
            <td>No. Reg</td>
            <td>MET. 000. 002233 2021</td>
        </tr>
        <tr>
            <td>Tanda tangan/ Tanggal
            </td>
            <td style="height: 80px;"></td>
        </tr>
    </table>
</body>

</html>