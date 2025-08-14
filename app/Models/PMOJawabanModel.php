<?php
// app/Models/PMOJawabanModel.php

namespace App\Models;

use CodeIgniter\Model;

class PMOJawabanModel extends Model
{
    protected $table = 'pmo_jawaban';
    protected $primaryKey = 'id_jawaban';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_pertanyaan',
        'id_pmo',
        'jawaban',
        'jawaban_nilai',
        'is_benar',
        'skor',
        'tanggapan_asesor',
        'catatan',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Save jawaban with validation
     */
    public function saveJawaban(array $data): array
    {
        try {
            // Validate pertanyaan exists
            $pertanyaan = $this->db->table('pmo_pertanyaan')
                ->where('id_pertanyaan', $data['id_pertanyaan'])
                ->get()
                ->getRowArray();

            if (!$pertanyaan) {
                throw new \Exception('Pertanyaan tidak ditemukan');
            }

            // Process jawaban based on tipe_pertanyaan
            $processedData = $this->processJawaban($pertanyaan, $data);

            // Check if jawaban already exists
            $existing = $this->where('id_pertanyaan', $data['id_pertanyaan'])
                ->where('id_pmo', $data['id_pmo'])
                ->first();

            if ($existing) {
                // Update existing
                $this->update($existing['id_jawaban'], $processedData);
                $id_jawaban = $existing['id_jawaban'];
            } else {
                // Insert new
                $id_jawaban = $this->insert($processedData);
            }

            return [
                'success' => true,
                'id_jawaban' => $id_jawaban,
                'message' => 'Jawaban berhasil disimpan'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error saving jawaban: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process jawaban based on question type
     */
    private function processJawaban(array $pertanyaan, array $data): array
    {
        $processedData = [
            'id_pertanyaan' => $data['id_pertanyaan'],
            'id_pmo' => $data['id_pmo'],
            'jawaban' => $data['jawaban'] ?? '',
            'tanggapan_asesor' => $data['tanggapan_asesor'] ?? '',
            'catatan' => $data['catatan'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        switch ($pertanyaan['tipe_pertanyaan']) {
            case 'ya_tidak':
                $processedData['jawaban_nilai'] = $data['jawaban_nilai']; // Y or N
                $processedData['is_benar'] = ($data['jawaban_nilai'] === 'Y') ? 1 : 0;
                $processedData['skor'] = $processedData['is_benar'] ? $pertanyaan['bobot'] : 0;
                break;

            case 'pilihan_ganda':
                $processedData['jawaban_nilai'] = $data['jawaban_nilai']; // A, B, C, D
                $processedData['is_benar'] = ($data['jawaban_nilai'] === $pertanyaan['jawaban_benar']) ? 1 : 0;
                $processedData['skor'] = $processedData['is_benar'] ? $pertanyaan['bobot'] : 0;
                break;

            case 'essay':
                $processedData['jawaban_nilai'] = null;
                $processedData['is_benar'] = isset($data['is_benar']) ? $data['is_benar'] : null;
                $processedData['skor'] = $data['skor'] ?? 0;
                break;

            case 'praktik':
                $processedData['jawaban_nilai'] = $data['jawaban_nilai']; // Y or N
                $processedData['is_benar'] = ($data['jawaban_nilai'] === 'Y') ? 1 : 0;
                $processedData['skor'] = $processedData['is_benar'] ? $pertanyaan['bobot'] : 0;
                break;
        }

        return $processedData;
    }

    /**
     * Get jawaban by PMO ID
     */
    public function getJawabanByPMO(int $id_pmo): array
    {
        return $this->where('id_pmo', $id_pmo)->findAll();
    }

    /**
     * Batch save jawaban
     */
    public function batchSaveJawaban(int $id_pmo, array $jawabanData): array
    {
        $this->db->transStart();

        try {
            $savedCount = 0;
            $errors = [];

            foreach ($jawabanData as $jawaban) {
                $jawaban['id_pmo'] = $id_pmo;
                $result = $this->saveJawaban($jawaban);

                if ($result['success']) {
                    $savedCount++;
                } else {
                    $errors[] = $result['message'];
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return [
                'success' => true,
                'saved_count' => $savedCount,
                'errors' => $errors,
                'message' => "Berhasil menyimpan {$savedCount} jawaban"
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error batch save jawaban: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
