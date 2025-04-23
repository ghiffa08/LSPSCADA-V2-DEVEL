<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
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
            text-align: center;
            margin-bottom: 30px;
        }

        .content {
            text-align: center;
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
            color: #fff;
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
            <h2>Aktivasi Akun</h2>
        </div>
        <div class="content">
            <p>Ini adalah email aktivasi untuk akun Anda di <a href="<?= site_url('/dashboard') ?>">Sertifikasi LSP SMKN 2 Kuningan</a>.</p>
            <p>Untuk mengaktifkan akun Anda, klik tombol di bawah ini:</p>
            <p><a href="<?= url_to('activate-account') . '?token=' . $hash ?>" class="button">Aktifkan Akun</a></p>
            <p>Jika Anda tidak mendaftar di situs ini, Anda dapat mengabaikan email ini dengan aman.</p>
        </div>
        <div class="footer">
            <p>Email ini dikirimkan secara otomatis. Jangan membalas email ini.</p>
            <p>&copy; <?= date('Y') ?> Sertifikasi LSP SMKN 2 Kuningan. Hak cipta dilindungi undang-undang.</p>
        </div>
    </div>
</body>

</html>