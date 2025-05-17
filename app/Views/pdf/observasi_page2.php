<!DOCTYPE html>
<html>

<head>
    <title>Ceklis Observasi - <?= $skema['nama_skema'] ?? '-' ?></title>
    <style>
        /* TCPDF compatible styles */
        body {
            font-family: helvetica;
            font-size: 10pt;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 4px;
            vertical-align: top;
        }

        .header {
            font-weight: bold;
            font-size: 12pt;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <tr>
            <td width="30%">Nama</td>
            <td width="35%">Asesi:</td>
            <td width="35%">Asesor: <strong>Sri Sayuningsih</strong></td>
        </tr>
        <tr>
            <td>Tanda Tangan dan Tanggal</td>
            <td height="60px"></td>
            <td></td>
        </tr>
    </table>
    <p style="font-size: 10px; margin-top: 4px;">*) Bila diperlukan</p>
</body>

</html>