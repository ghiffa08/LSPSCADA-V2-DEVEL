<?php
// Helper function to group data by kelompok
if (!function_exists('groupByKelompok')) {
    function groupByKelompok($detailObservasi)
    {
        $kelompokGrouped = [];

        foreach ($detailObservasi as $row) {
            $kelompokId = $row['id_kelompok'];
            $unitId = $row['id_unit'];

            if (!isset($kelompokGrouped[$kelompokId])) {
                $kelompokGrouped[$kelompokId] = [
                    'nama_kelompok' => $row['nama_kelompok'],
                    'units' => [],
                ];
            }

            if (!isset($kelompokGrouped[$kelompokId]['units'][$unitId])) {
                $kelompokGrouped[$kelompokId]['units'][$unitId] = [
                    'id_unit' => $row['id_unit'],
                    'kode_unit' => $row['kode_unit'],
                    'judul_unit' => $row['nama_unit'],
                    'kuk' => [],
                ];
            }

            $kelompokGrouped[$kelompokId]['units'][$unitId]['kuk'][] = $row;
        }

        return $kelompokGrouped;
    }
}
