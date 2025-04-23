<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Validasi Data Pendaftaran Uji Kompetensi Keahlian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #000000;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            margin-bottom: 30px;
        }

        .content {
            margin-bottom: 30px;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        /* Tombol Responsif */
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #000000;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #0056b3;
        }

        /* Responsif */
        @media screen and (max-width: 600px) {
            .container {
                max-width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Informasi Pendaftaran Sertifikasi Uji Kompetensi</h2>
        </div>
        <div class="content">
            <p>Hai <?= $name ?>,</p>

            <p>Dengan hormat,</p>

            <p>Bersama email ini, kami ingin menyampaikan bahwa data pendaftaran Sertifikasi Uji Kompetensi atas nama <?= $name ?> dengan ID Pendaftaran <?= $id ?> tidak dapat diterima.</p>

            <p>Berikut detail data pendaftarannya:</p>
            <table>
                <tr>
                    <td>Nama Lengkap</td>
                    <td>:</td>
                    <td><?= $name ?></td>
                </tr>
                <tr>
                    <td>ID Pendaftaran</td>
                    <td>:</td>
                    <td><?= $id ?></td>
                </tr>
                <tr>
                    <td>Skema Sertifikasi</td>
                    <td>:</td>
                    <td><?= $skema ?></td>
                </tr>
                <tr>
                    <td>Alasan Penolakan</td>
                    <td>:</td>
                    <td><?= $alasan_penolakan ?></td>
                </tr>
            </table>

            <p>Untuk informasi lebih lanjut terkait alasan penolakan, silakan hubungi kami melalui email atau nomor telepon yang tercantum di bawah ini.</p>

            <p>Email: <?= $email_kontak ?></p>
            <p>Telepon: <?= $telepon_kontak ?></p>

            <p>Hormat kami,</p>

            <p>Tim LSP - P1 SMK Negeri 2 Kuningan</p>
        </div>

        <div class="footer">
            <p>Email ini dikirimkan secara otomatis. Jangan membalas email ini.</p>
            <p>&copy; <?= date('Y') ?> Sertifikasi LSP - P1 SMKN 2 Kuningan. Hak cipta dilindungi undang-undang.</p>
        </div>
    </div>
</body>

</html>