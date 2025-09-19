<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.APL.01 - Page 2</title>
    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 3px;
        }
    </style>
</head>

<body>
    <h4>Bagian 2 : Data Sertifikasi</h4>
    <p>Tuliskan Judul dan Nomor Skema Sertifikasi yang anda ajukan berikut Daftar Unit Kompetensi sesuai kemasan pada skema sertifikasi untuk mendapatkan pengakuan sesuai dengan latar belakang pendidikan, pelatihan serta pengalaman kerja yang anda miliki.</p>

    <table>
        <tr>
            <td style="width: 30%;" rowspan="2">
                Skema Sertifikasi<br>
                <?= $jenis_sertifikasi_html; ?>
            </td>
            <td style="width: 10%;">Judul</td>
            <td style="width: 5%;">:</td>
            <td style="width: 55%;"><?= esc($pengajuan['asesmen']['nama_skema']); ?></td>
        </tr>
        <tr>
            <td>Nomor</td>
            <td>:</td>
            <td></td>
        </tr>
        <?= $tujuan_html; // Cukup panggil variabel ini, karena sudah berisi 4 baris <tr> lengkap 
        ?>
    </table>

    <br /><br />

    <h4>Daftar Unit Kompetensi Sesuai Kemasan:</h4>
    <table>
        <thead>
            <tr style="background-color: #eee;">
                <th style="width: 5%;"><b>No.</b></th>
                <th style="width: 25%;"><b>Kode Unit</b></th>
                <th style="width: 45%;"><b>Judul Unit</b></th>
                <th style="width: 25%;"><b>Standar Kompetensi Kerja</b></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($listUnit)): ?>
                <?php $no = 1;
                foreach ($listUnit as $unit): ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++; ?>.</td>
                        <td><?= esc($unit['kode_unit']); ?></td>
                        <td><?= esc($unit['nama_unit']); ?></td>
                        <td style="text-align: center;">SKKNI</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada unit kompetensi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>