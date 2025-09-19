<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Informasi Validasi Pengajuan Asesmen</title>
    <style>
        /* Menggunakan CSS yang sama persis dengan email 'validated' untuk konsistensi */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .header h2 {
            /* Warna diubah untuk menandakan status ditolak/butuh perhatian */
            color: #dc3545;
        }

        .content table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .content th,
        .content td {
            padding: 8px;
            text-align: left;
        }

        .content th {
            width: 150px;
            color: #555;
        }

        .alasan {
            background-color: #fff3f3;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Pengajuan Asesmen Perlu Diperbaiki</h2>
        </div>
        <div class="content">
            <p>Halo <strong><?= esc($nama_asesi) ?></strong>,</p>
            <p>Setelah melakukan pemeriksaan, kami informasikan bahwa pengajuan asesmen Anda untuk skema sertifikasi terkait <strong>memerlukan perhatian lebih lanjut</strong> atau <strong>ditolak</strong>.</p>

            <table>
                <tr>
                    <th>Nama Asesi</th>
                    <td>: <?= esc($nama_asesi) ?></td>
                </tr>
                <tr>
                    <th>Skema Sertifikasi</th>
                    <td>: <?= esc($skema) ?></td>
                </tr>
                <tr>
                    <th>Status Validasi</th>
                    <td>: <strong style="color: #dc3545;"><?= esc(ucfirst($status_validasi)) ?></strong></td>
                </tr>
                <tr>
                    <th>Divalidasi Oleh</th>
                    <td>: Admin (<?= esc($validator) ?>)</td>
                </tr>
                <tr>
                    <th>Tanggal Validasi</th>
                    <td>: <?= esc($tanggal_validasi) ?></td>
                </tr>
            </table>

            <div class="alasan">
                <p><strong>Alasan Penolakan:</strong></p>
                <p><?= esc($alasan_penolakan) ?></p>
            </div>

            <p>Silakan perbaiki data atau dokumen Anda sesuai dengan catatan di atas dan lakukan pengajuan ulang, atau hubungi kami jika memerlukan bantuan lebih lanjut.</p>

            <p>Terima kasih atas perhatian Anda,<br>Tim LSP - P1 SMK Negeri 2 Kuningan</p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> LSP - P1 SMKN 2 Kuningan. Email ini dikirim secara otomatis.</p>
        </div>
    </div>
</body>

</html>