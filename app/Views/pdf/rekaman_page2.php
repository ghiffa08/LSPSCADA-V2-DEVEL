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
    <table cellpadding="4" border="1" cellspacing="0" width="100%" style="margin-top: 20px;">
        <tr>
            <td colspan="3"><strong>Asesi:</strong></td>
        </tr>
        <tr>
            <td style="width: 20%;">Nama</td>
            <td style="width: 5%;">:</td>
            <td style="width: 75%;">Haikal Jibran A</td>
        </tr>
        <tr>
            <td style="width: 20%;">Tanda tangan dan Tanggal
            </td>
            <td style="width: 5%;">:</td>
            <td style="width: 75%; height:80px;"></td>
        </tr>
        <tr>
            <td colspan="3"><strong>Asesor:</strong></td>
        </tr>
        <tr>
            <td style="width: 20%;">Nama</td>
            <td style="width: 5%;">:</td>
            <td style="width: 75%;">Sri Sayuningsih</td>
        </tr>
        <tr>
            <td style="width: 20%;">No. Reg</td>
            <td style="width: 5%;">:</td>
            <td style="width: 75%;">MET. 000. 002233 2021</td>
        </tr>
        <tr>
            <td style="width: 20%;">Tanda tangan dan Tanggal
            </td>
            <td style="width: 5%;">:</td>
            <td style="width: 75%; height:80px;"></td>
        </tr>
    </table>

    <div style="clear:both; height:10px;"></div>


    <div>
        <strong> LAMPIRAN DOKUMEN:</strong>
        <ol type="1">
            <li>Dokumen APL 01 peserta</li>
            <li>Dokumen APL 02 peserta</li>
            <li>Bukti-bukti berkualitas peserta</li>
            <li>Tinjauan proses asesmen</li>
        </ol>
    </div>
</body>

</html>