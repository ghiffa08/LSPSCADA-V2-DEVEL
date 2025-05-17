<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>

<body>

    <h4>Bagian 2 : Data Sertifikasi</h4>
    <p>Tuliskan Judul dan Nomor Skema Sertifikasi yang anda ajukan berikut Daftar Unit Kompetensi sesuai kemasan pada skema sertifikasi untuk mendapatkan pengakuan sesuai dengan latar belakang pendidikan, pelatihan serta pengalaman kerja yang anda miliki.</p>

    <table style="width: 100%; border-collapse: collapse;" border="1">
        <tr>
            <td style="width: 30%;" rowspan="2">
                Skema Sertifikasi<br><?= $jenisSertifikasiFormatted ?>
            </td>
            <td style="width: 10%;">Judul</td>
            <td style="width: 5%;">:</td>
            <td style="width: 55%;"><?= htmlspecialchars($apl1['asesmen']['nama_skema']) ?></td>
        </tr>
        <tr>
            <td>Nomor</td>
            <td>:</td>
            <td></td>
        </tr>
        <?= $tujuanFormatted ?>
    </table>


    <h4>Daftar Unit Kompetensi Sesuai Kemasan:</h4>
    <table>
        <tr>
            <th style="width: 5%;">No.</th>
            <th style="width: 25%;">Kode Unit</th>
            <th style="width: 45%;">Judul Unit</th>
            <th style="width: 25%;">Standar Kompetensi Kerja</th>
        </tr>

        <?php $no = 1;
        foreach ($listUnit as $unit): ?>
            <tr>
                <td><?= $no++ ?>.</td>
                <td><?= $unit['kode_unit'] ?></td>
                <td><?= $unit['nama_unit'] ?></td>
                <td style="text-align: center;">SKKNI</td>
            </tr>
        <?php endforeach; ?>

    </table>

</body>

</html>