<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ObservasiTestSeeder extends Seeder
{
    public function run()
    {
        // Clear existing test data
        $this->db->table('detail_observasi')->where('id_asesmen', 1)->delete();
        $this->db->table('observasi')->where('id_asesmen', 1)->delete();

        // Create test observasi records
        $observasiData = [
            [
                'id_observasi' => 1,
                'id_asesmen' => 1,
                'id_asesi' => 1,
                'tanggal_observasi' => date('Y-m-d'),
                'total_kuk' => 0,
                'completed_kuk' => 0,
                'progress_percentage' => 0.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id_observasi' => 2,
                'id_asesmen' => 1,
                'id_asesi' => 2,
                'tanggal_observasi' => date('Y-m-d'),
                'total_kuk' => 0,
                'completed_kuk' => 0,
                'progress_percentage' => 0.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('observasi')->insertBatch($observasiData);

        // Create test detail observasi records
        $detailData = [
            [
                'id_detail_observasi' => 1,
                'id_observasi' => 1,
                'id_asesmen' => 1,
                'id_skema' => 1,
                'id_kuk' => 1,
                'kompeten' => 'Y',
                'keterangan' => 'Kompeten dalam melakukan identifikasi kebutuhan',
                'tanggal_observasi' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id_detail_observasi' => 2,
                'id_observasi' => 1,
                'id_asesmen' => 1,
                'id_skema' => 1,
                'id_kuk' => 2,
                'kompeten' => 'N',
                'keterangan' => 'Perlu perbaikan dalam implementasi',
                'tanggal_observasi' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id_detail_observasi' => 3,
                'id_observasi' => 2,
                'id_asesmen' => 1,
                'id_skema' => 1,
                'id_kuk' => 1,
                'kompeten' => 'Y',
                'keterangan' => 'Sudah memahami dengan baik',
                'tanggal_observasi' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('detail_observasi')->insertBatch($detailData);

        // Create test master data if not exists
        $this->seedMasterData();

        // Update observasi totals using stored procedure if available
        try {
            $this->db->query("CALL UpdateObservasiProgress(1, 1)");
            $this->db->query("CALL UpdateObservasiProgress(1, 2)");
        } catch (\Exception $e) {
            // Fallback if stored procedure not available
            $this->updateProgressManually();
        }
    }

    private function seedMasterData()
    {
        // Check and create test skema if not exists
        $skemaExists = $this->db->table('skema')->where('id_skema', 1)->countAllResults();
        if ($skemaExists == 0) {
            $skemaData = [
                'id_skema' => 1,
                'kode_skema' => 'TIK.J02',
                'nama_skema' => 'Teknisi Komputer',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->table('skema')->insert($skemaData);
        }

        // Check and create test asesmen if not exists
        $asesmenExists = $this->db->table('asesmen')->where('id_asesmen', 1)->countAllResults();
        if ($asesmenExists == 0) {
            $asesmenData = [
                'id_asesmen' => 1,
                'id_skema' => 1,
                'nama_asesmen' => 'Asesmen Teknisi Komputer Batch 1',
                'tanggal_mulai' => date('Y-m-d'),
                'tanggal_selesai' => date('Y-m-d', strtotime('+7 days')),
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->table('asesmen')->insert($asesmenData);
        }

        // Check and create test asesi if not exists
        $asesiExists = $this->db->table('asesi')->where('id_asesi', 1)->countAllResults();
        if ($asesiExists == 0) {
            $asesiData = [
                [
                    'id_asesi' => 1,
                    'nik' => '1234567890123456',
                    'nama' => 'John Doe',
                    'email' => 'john.doe@test.com',
                    'telepon' => '081234567890',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id_asesi' => 2,
                    'nik' => '1234567890123457',
                    'nama' => 'Jane Smith',
                    'email' => 'jane.smith@test.com',
                    'telepon' => '081234567891',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ];
            $this->db->table('asesi')->insertBatch($asesiData);
        }

        // Check and create test kelompok if not exists
        $kelompokExists = $this->db->table('kelompok')->where('id_kelompok', 1)->countAllResults();
        if ($kelompokExists == 0) {
            $kelompokData = [
                'id_kelompok' => 1,
                'id_skema' => 1,
                'nama_kelompok' => 'Instalasi dan Konfigurasi',
                'urutan' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->table('kelompok')->insert($kelompokData);
        }

        // Check and create test unit if not exists
        $unitExists = $this->db->table('unit')->where('id_unit', 1)->countAllResults();
        if ($unitExists == 0) {
            $unitData = [
                'id_unit' => 1,
                'id_kelompok' => 1,
                'kode_unit' => 'TIK.J02.001.01',
                'nama_unit' => 'Mengoperasikan PC dalam jaringan',
                'urutan' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->table('unit')->insert($unitData);
        }

        // Check and create test elemen if not exists
        $elemenExists = $this->db->table('elemen')->where('id_elemen', 1)->countAllResults();
        if ($elemenExists == 0) {
            $elemenData = [
                'id_elemen' => 1,
                'id_unit' => 1,
                'nama_elemen' => 'Mengidentifikasi kebutuhan jaringan',
                'urutan' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->table('elemen')->insert($elemenData);
        }

        // Check and create test KUK if not exists
        $kukExists = $this->db->table('kuk')->where('id_kuk', 1)->countAllResults();
        if ($kukExists == 0) {
            $kukData = [
                [
                    'id_kuk' => 1,
                    'id_elemen' => 1,
                    'kriteria_unjuk_kerja' => 'Kebutuhan aplikasi diidentifikasi sesuai dengan kebutuhan organisasi',
                    'urutan' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id_kuk' => 2,
                    'id_elemen' => 1,
                    'kriteria_unjuk_kerja' => 'Kebutuhan hardware diidentifikasi sesuai spesifikasi minimum',
                    'urutan' => 2,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id_kuk' => 3,
                    'id_elemen' => 1,
                    'kriteria_unjuk_kerja' => 'Kebutuhan software diidentifikasi sesuai dengan sistem operasi',
                    'urutan' => 3,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ];
            $this->db->table('kuk')->insertBatch($kukData);
        }

        // Create test pengajuan data
        $pengajuanExists = $this->db->table('pengajuan')->where('id_pengajuan', 1)->countAllResults();
        if ($pengajuanExists == 0) {
            $pengajuanData = [
                [
                    'id_pengajuan' => 1,
                    'id_asesmen' => 1,
                    'id_asesi' => 1,
                    'status' => 'diterima',
                    'tanggal_pengajuan' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id_pengajuan' => 2,
                    'id_asesmen' => 1,
                    'id_asesi' => 2,
                    'status' => 'diterima',
                    'tanggal_pengajuan' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ];
            $this->db->table('pengajuan')->insertBatch($pengajuanData);
        }
    }

    private function updateProgressManually()
    {
        // Manual progress calculation if stored procedure not available
        $observasiRecords = $this->db->table('observasi')
            ->where('id_asesmen', 1)
            ->get()
            ->getResultArray();

        foreach ($observasiRecords as $observasi) {
            $totalKuk = $this->db->table('detail_observasi')
                ->where('id_observasi', $observasi['id_observasi'])
                ->countAllResults();

            $completedKuk = $this->db->table('detail_observasi')
                ->where('id_observasi', $observasi['id_observasi'])
                ->where('kompeten !=', '')
                ->countAllResults();

            $progressPercentage = $totalKuk > 0 ? ($completedKuk / $totalKuk) * 100 : 0;

            $this->db->table('observasi')
                ->where('id_observasi', $observasi['id_observasi'])
                ->update([
                    'total_kuk' => $totalKuk,
                    'completed_kuk' => $completedKuk,
                    'progress_percentage' => $progressPercentage,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }
    }

    /**
     * Create additional test data for performance testing
     */
    public function createPerformanceTestData()
    {
        // Create 100 KUK records for performance testing
        $kukData = [];
        for ($i = 4; $i <= 103; $i++) {
            $kukData[] = [
                'id_kuk' => $i,
                'id_elemen' => 1,
                'kriteria_unjuk_kerja' => "Kriteria unjuk kerja ke-{$i} untuk testing performa",
                'urutan' => $i,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        // Insert in batches to avoid memory issues
        $chunks = array_chunk($kukData, 50);
        foreach ($chunks as $chunk) {
            $this->db->table('kuk')->insertBatch($chunk);
        }

        // Create detail observasi for performance testing
        $detailData = [];
        for ($i = 4; $i <= 53; $i++) {
            $detailData[] = [
                'id_detail_observasi' => $i,
                'id_observasi' => 1,
                'id_asesmen' => 1,
                'id_skema' => 1,
                'id_kuk' => $i,
                'kompeten' => $i % 2 === 0 ? 'Y' : 'N',
                'keterangan' => "Test keterangan untuk KUK {$i}",
                'tanggal_observasi' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        $detailChunks = array_chunk($detailData, 25);
        foreach ($detailChunks as $chunk) {
            $this->db->table('detail_observasi')->insertBatch($chunk);
        }
    }

    /**
     * Clean up test data
     */
    public function cleanup()
    {
        // Remove test data
        $this->db->table('detail_observasi')->where('id_asesmen', 1)->delete();
        $this->db->table('observasi')->where('id_asesmen', 1)->delete();
        $this->db->table('pengajuan')->where('id_asesmen', 1)->delete();

        // Remove performance test KUK data
        $this->db->table('kuk')->where('id_kuk >', 3)->delete();

        // Note: We keep master data (skema, asesmen, asesi, etc.) as other tests might need them
    }
}
