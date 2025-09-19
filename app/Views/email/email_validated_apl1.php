<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Validasi Pengajuan Asesmen Diterima</title>
    <style>
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
            color: #0056b3;
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

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
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
            <h2>Pengajuan Asesmen Diterima</h2>
        </div>
        <div class="content">
            <p>Halo <strong><?= esc($nama_asesi) ?></strong>,</p>
            <p>Kabar baik! Pengajuan asesmen Anda untuk skema sertifikasi telah kami periksa dan dinyatakan <strong>DITERIMA</strong>.</p>

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
                    <td>: <strong style="color: #28a745;"><?= esc(ucfirst($status_validasi)) ?></strong></td>
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

            <p>Langkah selanjutnya adalah mengisi formulir Asesmen Mandiri (FR-APL-02). Silakan klik tombol di bawah ini untuk melanjutkan.</p>

            <div class="button-container">
                <a href="<?= esc($next_step_url, 'attr') ?>" class="button">Mulai Asesmen Mandiri</a>
            </div>

            <p>Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami.</p>
            <p>Terima kasih,<br>Tim LSP - P1 SMK Negeri 2 Kuningan</p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> LSP - P1 SMKN 2 Kuningan. Email ini dikirim secara otomatis.</p>
        </div>
    </div>
</body>

</html>