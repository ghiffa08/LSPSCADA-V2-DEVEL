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
    function formatTujuan(string $tujuan): string
    {
        $template = [
            'Sertifikasi' => ['Sertifikasi', 'PKT', 'RPL', 'Lainya'],
            'PKT' => ['<s>Sertifikasi</s>', 'PKT', 'RPL', 'Lainya'],
            'RPL' => ['<s>Sertifikasi</s>', '<s>PKT</s>', 'RPL', 'Lainya'],
            'Lainya' => ['<s>Sertifikasi</s>', '<s>PKT</s>', '<s>RPL</s>', 'Lainya'],
        ];

        $rows = '';
        if (array_key_exists($tujuan, $template)) {
            $rows .= "<tr><td colspan='2' rowspan='4'>Tujuan Asesmen</td><td>:</td><td>{$template[$tujuan][0]}</td></tr>";
            for ($i = 1; $i < 4; $i++) {
                $rows .= "<tr><td></td><td>{$template[$tujuan][$i]}</td></tr>";
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

if (!function_exists('formatTujuan')) {
    function formatTujuan(string $selected): string
    {
        $list = [
            'Sertifikasi' => 'Sertifikasi',
            'PKT' => 'Pengakuan Kompetensi Lampau (PKT)',
            'RPL' => 'Rekognisi Pembelajaran Lampau (RPL)',
            'Lainnya' => 'Lainnya',
        ];

        $html = '<tr>
        <td rowspan="4">Tujuan Asesmen</td>
        <td>:</td>
        <td>' . ($selected === 'Sertifikasi' ? $list['Sertifikasi'] : '<s>' . $list['Sertifikasi'] . '</s>') . '</td>
    </tr>';

        foreach (['PKT', 'RPL', 'Lainnya'] as $key) {
            $label = $list[$key];
            $html .= '<tr>
            <td></td>
            <td>' . ($selected === $key ? $label : "<s>{$label}</s>") . '</td>
        </tr>';
        }

        return $html;
    }
}
