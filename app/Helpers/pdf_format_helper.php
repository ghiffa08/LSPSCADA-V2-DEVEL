<?php

if (!function_exists('formatJenisKelamin')) {
    function formatJenisKelamin(string $gender): string
    {
        return $gender === 'Laki-Laki'
            ? 'Laki-Laki / <span style="text-decoration: line-through;">Perempuan</span>'
            : '<span style="text-decoration: line-through;">Laki-Laki</span> / Perempuan';
    }
}

if (!function_exists('formatJenisSertifikasi')) {
    function formatJenisSertifikasi(string $jenis): string
    {
        $map = [
            'KKNI' => 'KKNI / <span style="text-decoration: line-through;">Okupasi</span>/<span style="text-decoration: line-through;">Klaster</span>',
            'Okupasi' => '<span style="text-decoration: line-through;">KKNI</span>/Okupasi/<span style="text-decoration: line-through;">Klaster</span>',
            'Klaster' => '<span style="text-decoration: line-through;">KKNI</span>/<span style="text-decoration: line-through;">Okupasi</span>/Klaster',
        ];
        return $map[$jenis] ?? '';
    }
}

if (!function_exists('formatTujuan')) {
    function formatTujuan(string $selected): string
    {
        $template = [
            'Sertifikasi' => ['Sertifikasi', 'PKT', 'RPL', 'Lainya'],
            'PKT' => ['<s>Sertifikasi</s>', 'PKT', 'RPL', 'Lainya'],
            'RPL' => ['<s>Sertifikasi</s>', '<s>PKT</s>', 'RPL', 'Lainya'],
            'Lainya' => ['<s>Sertifikasi</s>', '<s>PKT</s>', '<s>RPL</s>', 'Lainya'],
        ];

        $rows = '';
        if (array_key_exists($selected, $template)) {
            $rows .= "<tr><td colspan='2' rowspan='4'>Tujuan Asesmen</td><td>:</td><td>{$template[$selected][0]}</td></tr>";
            for ($i = 1; $i < 4; $i++) {
                $rows .= "<tr><td></td><td>{$template[$selected][$i]}</td></tr>";
            }
        }

        return $rows;
    }
}

if (!function_exists('formatJenisKelamin')) {
    function formatJenisKelamin(string $gender): string
    {
        return $gender === 'Laki-Laki'
            ? 'Laki-Laki / <span style="text-decoration: line-through;">Perempuan</span>'
            : '<span style="text-decoration: line-through;">Laki-Laki</span> / Perempuan';
    }
}

if (!function_exists('formatJenisSertifikasi')) {
    function formatJenisSertifikasi(string $jenis): string
    {
        $map = [
            'KKNI' => 'KKNI / <span style="text-decoration: line-through;">Okupasi</span>/<span style="text-decoration: line-through;">Klaster</span>',
            'Okupasi' => '<span style="text-decoration: line-through;">KKNI</span>/Okupasi/<span style="text-decoration: line-through;">Klaster</span>',
            'Klaster' => '<span style="text-decoration: line-through;">KKNI</span>/<span style="text-decoration: line-through;">Okupasi</span>/Klaster',
        ];
        return $map[$jenis] ?? '';
    }
}

function formatTujuanAsesmen($tujuan)
{
    switch ($tujuan) {
        case 'Sertifikasi':
            $html = '
                <tr>
                    <td colspan="2" rowspan="4">Tujuan Asesmen</td>
                    <td>:</td>
                    <td>Sertifikasi</td>
                </tr>
                <tr>
                    <td></td>
                    <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td><span style="text-decoration: line-through;">Lainnya</span></td>
                </tr>
            ';
            break;
        case 'PKT':
            $html = '
                <tr>
                    <td colspan="2" rowspan="4">Tujuan Asesmen</td>
                    <td>:</td>
                    <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Pengakuan Kompetensi Lampau (PKT)</td>
                </tr>
                <tr>
                    <td></td>
                    <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td><span style="text-decoration: line-through;">Lainnya</span></td>
                </tr>
            ';
            break;
        case 'RPL':
            $html = '
                <tr>
                    <td colspan="2" rowspan="4">Tujuan Asesmen</td>
                    <td>:</td>
                    <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Rekognisi Pembelajaran Lampau (RPL)</td>
                </tr>
                <tr>
                    <td></td>
                    <td><span style="text-decoration: line-through;">Lainnya</span></td>
                </tr>
            ';
            break;
        default:
            $html = '
                <tr>
                    <td colspan="2" rowspan="4">Tujuan Asesmen</td>
                    <td>:</td>
                    <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Lainnya</td>
                </tr>
            ';
            break;
    }

    return $html;
}

function formatBuktiDasar($pas_foto)
{
    if (!empty($pas_foto)) {
        $html = '
            <tr>
                <td>2.</td>
                <td>Foto Berwarna Ukuran 3x4 2 Lembar</td>
                <td style="text-align: center;">Ada</td>
                <td></td>
                <td></td>
            </tr>
        ';
    } else {
        $html = '
            <tr>
                <td>2.</td>
                <td>Foto Berwarna Ukuran 3x4 2 Lembar</td>
                <td style="text-align: center;"></td>
                <td></td>
                <td>Tidak Ada</td>
            </tr>
        ';
    }

    return $html;
}


function formatStatus(string $status): string
{
    if ($status === 'validated') {
        return 'Diterima / <span style="text-decoration: line-through;">Tidak Diterima</span>';
    } elseif ($status === 'unvalidated') {
        return '<span style="text-decoration: line-through;">Diterima</span> / Tidak Diterima';
    } else {
        return 'Diterima / Tidak Diterima';
    }
}
