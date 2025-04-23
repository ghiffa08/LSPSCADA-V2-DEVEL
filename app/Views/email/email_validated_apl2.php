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
            <h2>Validasi Asesmen Mandiri</h2>
        </div>
        <div class="content">
            <p>Hai <?= $dataAPL1['nama_siswa'] ?>,</p>

            <p>Selamat! Pendaftaran Sertifikasi Uji Kompetensi Anda telah terverifikasi dan Asesmen Mandiri Anda telah divalidasi.</p>

            <p><b>Berikut detail data sertifikasi Anda:</b> </p>

            <table>
                <tr>
                    <td style="width: 45%;">Nama Asesi</td>
                    <td style="width: 5%;">:</td>
                    <td style="width: 50%;"><?= $dataAPL1['nama_siswa'] ?></td>
                </tr>
                <tr>
                    <td style="width: 45%;">ID Pendaftaran</td>
                    <td style="width: 5%;">:</td>
                    <td style="width: 50%;"><?= $dataAPL1['id_apl1'] ?></td>
                </tr>
                <tr>
                    <td style="width: 45%;">Validator FR.APL.01</td>
                    <td style="width: 5%;">:</td>
                    <td style="width: 50%;"><?= $dataAPL1['valdator_apl1'] ?></td>
                </tr>

                <tr>
                    <td style="width: 45%;">ID Asesmen</td>
                    <td style="width: 5%;">:</td>
                    <td style="width: 50%;"><?= $dataAPL1['id_apl2'] ?></td>
                </tr>
                <tr>
                    <td style="width: 45%;">Validator FR.APL.02</td>
                    <td style="width: 5%;">:</td>
                    <td style="width: 50%;"><?= $dataAPL1['validator_apl2'] ?></td>
                </tr>
                <tr>
                    <td style="width: 45%;">Skema Sertifikasi</td>
                    <td style="width: 5%;">:</td>
                    <td style="width: 50%;"><?= $dataAPL1['nama_skema'] ?></td>
                </tr>
            </table>

            <p>Pendaftaranmu untuk sertifikasi uji kompetensi dengan skema <?= $dataAPL1['nama_skema'] ?> di LSP - P1 SMK Negeri 2 Kuningan telah berhasil.</p>

            <p>Sekarang kamu sudah terdaftar menjadi peserta sertifikasi!</p>

            <p>Tunggu informasi lebih lanjut mengenai sertifikasi dan jadwal asesmen yang akan segera kami kirimkan ke email kamu.</p>

            <p>Tetap semangat dalam mengikuti proses sertifikasi!</p>

            <p>Sekian informasi dari kami.</p>

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