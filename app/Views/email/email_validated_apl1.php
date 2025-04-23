<!DOCTYPE html>
<html lang="en">

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

            <p>Kabar baik! Data pendaftaran Sertifikasi kamu dengan ID Pendaftaran <?= $id ?> sudah terverifikasi!</p>

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
            </table>

            <p>Selanjutnya, kamu tinggal menyelesaikan Asesmen Mandiri untuk melanjutkan proses sertifikasi.</p>

            <a href="<?= site_url('/asesmen-mandiri/' . $id) ?>" class="button">Mulai Asesmen Mandiri</a>

            <p>Punya pertanyaan? Jangan ragu untuk menghubungi kami!</p>

            <p>Salam,</p>

            <p>Tim LSP - P1 SMK Negeri 2 Kuningan</p>
        </div>

        <div class="footer">
            <p>Email ini dikirimkan secara otomatis. Jangan membalas email ini.</p>
            <p>&copy; <?= date('Y') ?> Sertifikasi LSP - P1 SMKN 2 Kuningan. Hak cipta dilindungi undang-undang.</p>
        </div>
    </div>
</body>

</html>