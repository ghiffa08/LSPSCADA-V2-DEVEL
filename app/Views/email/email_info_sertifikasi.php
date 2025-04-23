<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Informasi Jadwal Sertifikasi LSP-P1 SMK Negeri 2 Kuningan</title>
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
            <h2>Informasi Jadwal Sertifikasi LSP-P1 SMK Negeri 2 Kuningan</h2>
        </div>
        <div class="content">
            <p>Hai <?= $dataAPL1['nama_siswa'] ?>,</p>

            <p>Selamat! Pendaftaran Sertifikasi Uji Kompetensi Anda dengan ID Pendaftaran <?= $dataAPL1['id_apl1'] ?> telah terverifikasi dan Asesmen Mandiri Anda telah divalidasi.</p>

            <p><b>Informasi Pelaksanaan Sertifikasi:</b> </p>

            <table>
                <tr>
                    <td style="width: 45%;">Hari / Tanggal</td>
                    <td style="width: 5%;">:</td>
                    <td style="width: 50%;"><?= date('d/m/Y', strtotime($dataAPL1['tanggal'])) ?></td>
                </tr>
                <tr>
                    <td style="width: 45%;">Waktu</td>
                    <td style="width: 5%;">:</td>
                    <td style="width: 50%;"><?= date('H:i', strtotime($dataAPL1['tanggal'])) ?></td>
                </tr>
                <tr>
                    <td style="width: 45%;">Tempat Uji Kompetensi</td>
                    <td style="width: 5%;">:</td>
                    <td style="width: 50%;"><?= $dataAPL1['nama_tuk'] ?></td>
                </tr>
            </table>

            <p><b>Persetujuan Asesmen dan Kerahasiaan</b> </p>

            <table>
                <tr>
                    <td style="width: 100%;"><b>Asesi:</b><br>Bahwa saya telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor.
                    </td>
                </tr>
                <tr>
                    <td style="width: 100%;"><b>Asor:</b><br>Menyatakan tidak akan membuka hasil pekerjaan yang saya peroleh karena penugasan saya sebagai Asesor dalam pekerjaan Asesmen kepada siapapun atau organisasi apapun selain kepada pihak yang berwenang sehubungan dengan kewajiban saya sebagai Asesor yang ditugaskan oleh LSP.
                    </td>
                </tr>
                <tr>
                    <td style="width: 100%;"><b>Asesi:</b><br>Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya di gunakan untuk pengembangan profesional dan hanya dapat di akses oleh orang tertentu saja.
                    </td>
                </tr>
            </table>

            <p><b>Informasi Penting:</b></p>

            <ul>
                <li>Anda dapat mengakses portal <a href="http://">Peserta Sertifikasi</a> .</li>
                <li>Jika Anda memiliki pertanyaan, silakan hubungi tim kami melalui lspp1smkn2kuningan@gmail.com atau 0812345678.</li>
            </ul>

            <p>Sekian informasi dari kami. Terima kasih!</p>

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