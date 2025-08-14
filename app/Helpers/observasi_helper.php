<?php
// Helper function to group data by kelompok
if (!function_exists('groupByKelompok')) {
    function groupByKelompok($detailObservasi)
    {
        // Debug logging
        log_message('info', 'groupByKelompok called with data type: ' . gettype($detailObservasi));
        
        if (empty($detailObservasi)) {
            log_message('warning', 'groupByKelompok: Empty data provided');
            return [];
        }

        $kelompokGrouped = [];

        // Handle structured data (array with 'kelompok_kerja' key)
        if (isset($detailObservasi['kelompok_kerja']) && is_array($detailObservasi['kelompok_kerja'])) {
            log_message('info', 'groupByKelompok: Processing kelompok_kerja structure');
            $counter = 1;
            foreach ($detailObservasi['kelompok_kerja'] as $kelompok) {
                $kelompokId = $kelompok['id_kelompok'] ?? $counter;
                $namaKelompok = $kelompok['nama_kelompok'] ?? ('Kelompok ' . $counter);
                $units = $kelompok['units'] ?? [];

                $kelompokGrouped[$kelompokId] = [
                    'id_kelompok' => $kelompokId,
                    'nama_kelompok' => $namaKelompok,
                    'units' => []
                ];

                foreach ($units as $unit) {
                    if (empty($unit['id_unit'])) {
                        log_message('warning', 'groupByKelompok: Missing id_unit in unit: ' . json_encode($unit));
                        continue;
                    }
                    
                    $unitId = $unit['id_unit'];
                    $kelompokGrouped[$kelompokId]['units'][$unitId] = [
                        'id_unit' => $unitId,
                        'kode_unit' => $unit['kode_unit'] ?? '',
                        'judul_unit' => $unit['nama_unit'] ?? $unit['judul_unit'] ?? '', // PERBAIKAN: gunakan nama_unit
                        'nama_unit' => $unit['nama_unit'] ?? $unit['judul_unit'] ?? '',
                        'kuk' => []
                    ];

                    if (isset($unit['kuk']) && is_array($unit['kuk'])) {
                        foreach ($unit['kuk'] as $kuk) {
                            $normalizedKuk = $kuk;
                            if (isset($kuk['nama_kuk']) && !isset($kuk['kriteria_unjuk_kerja'])) {
                                $normalizedKuk['kriteria_unjuk_kerja'] = $kuk['nama_kuk'];
                            }
                            if (isset($kuk['kriteria_unjuk_kerja']) && !isset($kuk['nama_kuk'])) {
                                $normalizedKuk['nama_kuk'] = $kuk['kriteria_unjuk_kerja'];
                            }
                            $kelompokGrouped[$kelompokId]['units'][$unitId]['kuk'][] = $normalizedKuk;
                        }
                    }
                }
                $counter++;
            }
        }
        // Handle direct array structure (hasil dari getStrukturById)
        else if (is_array($detailObservasi)) {
            log_message('info', 'groupByKelompok: Processing direct array structure');
            
            $isStructured = true;
            foreach ($detailObservasi as $key => $value) {
                if (!is_array($value) || !isset($value['id_kelompok'])) {
                    $isStructured = false;
                    break;
                }
            }
            
            if ($isStructured) {
                // PERBAIKAN: Data sudah dalam format kelompok, pastikan field judul_unit ada
                log_message('info', 'groupByKelompok: Data already structured by kelompok');
                foreach ($detailObservasi as $kelompokId => $kelompok) {
                    if (isset($kelompok['units']) && is_array($kelompok['units'])) {
                        foreach ($kelompok['units'] as $unitId => $unit) {
                            // PERBAIKAN: Pastikan field judul_unit ada
                            if (!isset($unit['judul_unit']) && isset($unit['nama_unit'])) {
                                $detailObservasi[$kelompokId]['units'][$unitId]['judul_unit'] = $unit['nama_unit'];
                            }
                        }
                    }
                }
                return $detailObservasi;
            } else {
                // Process flat array data
                log_message('info', 'groupByKelompok: Processing flat array structure');
                foreach ($detailObservasi as $row) {
                    if (!isset($row['id_kelompok'])) {
                        log_message('warning', 'groupByKelompok: Missing id_kelompok in row: ' . json_encode($row));
                        continue;
                    }
                    
                    if (!isset($row['id_unit']) || empty($row['id_unit'])) {
                        log_message('warning', 'groupByKelompok: Missing id_unit in row: ' . json_encode($row));
                        continue;
                    }

                    $kelompokId = $row['id_kelompok'];
                    $unitId = $row['id_unit'];

                    if (!isset($kelompokGrouped[$kelompokId])) {
                        $kelompokGrouped[$kelompokId] = [
                            'id_kelompok' => $kelompokId,
                            'nama_kelompok' => $row['nama_kelompok'] ?? ('Kelompok ' . $kelompokId),
                            'units' => [],
                        ];
                    }

                    if (!isset($kelompokGrouped[$kelompokId]['units'][$unitId])) {
                        $kelompokGrouped[$kelompokId]['units'][$unitId] = [
                            'id_unit' => $unitId,
                            'kode_unit' => $row['kode_unit'] ?? '',
                            'judul_unit' => $row['nama_unit'] ?? $row['judul_unit'] ?? '', // PERBAIKAN: gunakan nama_unit
                            'nama_unit' => $row['nama_unit'] ?? '',
                            'kuk' => [],
                        ];
                    }

                    $normalizedRow = $row;
                    if (isset($row['nama_kuk']) && !isset($row['kriteria_unjuk_kerja'])) {
                        $normalizedRow['kriteria_unjuk_kerja'] = $row['nama_kuk'];
                    }
                    if (isset($row['kriteria_unjuk_kerja']) && !isset($row['nama_kuk'])) {
                        $normalizedRow['nama_kuk'] = $row['kriteria_unjuk_kerja'];
                    }

                    $kelompokGrouped[$kelompokId]['units'][$unitId]['kuk'][] = $normalizedRow;
                }
            }
        }

        log_message('info', 'groupByKelompok: Returning ' . count($kelompokGrouped) . ' kelompok');
        return $kelompokGrouped;
    }
}

if (!function_exists('format_tanggal_indonesia')) {
    /**
     * Format date to Indonesian format
     * 
     * @param string $date
     * @return string
     */
    function format_tanggal_indonesia(string $date): string
    {
        if (empty($date)) {
            return '-';
        }

        $bulan = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return '-';
        }

        $hari = date('d', $timestamp);
        $bulan_idx = (int)date('m', $timestamp);
        $tahun = date('Y', $timestamp);

        return $hari . ' ' . $bulan[$bulan_idx] . ' ' . $tahun;
    }
}
