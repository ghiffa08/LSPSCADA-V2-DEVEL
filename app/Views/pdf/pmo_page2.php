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
            text-align: center;
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

    <div style="clear:both; height:10px;"></div>

    <!-- Signature Sections -->
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