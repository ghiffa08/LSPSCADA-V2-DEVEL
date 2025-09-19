<?php

namespace App\Services;

use App\Models\AsesiModel;
use App\Models\UserMythModel;
use App\Models\DynamicDependent;
use App\DTOs\ApiResponseDTO;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use Exception;

class AsesiService
{
    private AsesiModel $asesiModel;
    private UserMythModel $userModel;
    private ValidationService $validationService;
    private BaseConnection $db;

    public function __construct()
    {
        $this->asesiModel = new AsesiModel();
        $this->userModel = new UserMythModel();
        $this->validationService = new ValidationService();
        $this->db = \Config\Database::connect();
    }

    public function getAsesiByUserId(int $userId): ApiResponseDTO
    {
        try {
            $asesi = $this->asesiModel->getByUserId($userId);
            if (!$asesi) {
                return ApiResponseDTO::error('Profil asesi tidak ditemukan.', [], 404);
            }
            // Tambahkan data user yang relevan
            $user = $this->userModel->find($userId);
            if ($user) {
                $asesi['nama_lengkap'] = $user->nama_lengkap;
                $asesi['email'] = $user->email;
                $asesi['no_hp'] = $user->no_hp;
            }

            return ApiResponseDTO::success('Data asesi berhasil diambil', (object)$asesi);
        } catch (Exception $e) {
            log_message('error', '[AsesiService] getAsesiByUserId: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mengambil data asesi.');
        }
    }

    /**
     * Mengambil semua aturan validasi untuk profil asesi.
     * Digunakan oleh controller untuk validasi real-time.
     */
    public function getValidationRules(): array
    {
        return $this->validationService->getValidationRules('asesi_profile');
    }

    /**
     * Menyimpan atau memperbarui data profil asesi.
     */
    public function saveProfileData(array $data, int $userId): array
    {
        // 1. Dapatkan aturan validasi dasar dan cek apakah ini proses update
        $rules = $this->getValidationRules();
        $existingAsesi = $this->asesiModel->getByUserId($userId);
        $isUpdate = (bool)$existingAsesi;

        // Atur aturan validasi NIK secara dinamis
        if ($isUpdate) {
            $rules['nik'] = str_replace('{id_asesi}', $existingAsesi['id_asesi'], $rules['nik']);
        } else {
            $rules['nik'] = 'required|exact_length[16]|numeric|is_unique[asesi.nik]';
        }

        // Tambahkan aturan kondisional berdasarkan status pekerjaan
        $statusPekerjaan = $data['status_pekerjaan'] ?? '';
        if ($statusPekerjaan === 'Pelajar/Mahasiswa') {
            $rules['nama_sekolah'] = 'required|max_length[100]';
        } elseif ($statusPekerjaan === 'Bekerja') {
            $rules['detail_pekerjaan'] = 'required|max_length[100]';
        }

        // Jalankan validasi
        if (!$this->validationService->validateData($data, $rules)) {
            return ['success' => false, 'message' => 'Data yang Anda masukkan tidak valid.', 'errors' => $this->validationService->getErrors()];
        }

        // 2. Transaksi Database
        $this->db->transStart();
        try {
            $asesiData = $this->prepareAsesiData($data, $userId, $isUpdate);

            // MODIFIED: Variable to hold the ID for both update and insert
            $asesiId = null;

            // Lewati validasi internal model
            if ($isUpdate) {
                $asesiId = $existingAsesi['id_asesi']; // Get ID from existing data
                $this->asesiModel->skipValidation(true)->update($asesiId, $asesiData);
            } else {
                $asesiData['kode_asesi'] = $this->generateKodeAsesi();
                $asesiId = $this->asesiModel->skipValidation(true)->insert($asesiData); // Capture new ID from insert operation
            }

            // Update no_hp di tabel users jika ada perubahan
            if (!empty($data['no_hp'])) {
                $this->userModel->update($userId, ['no_hp' => $data['no_hp']]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Gagal menyimpan data ke database.');
            }

            // MODIFIED: Add 'id_asesi' to the return array. This is crucial for the controller.
            return [
                'success'   => true,
                'is_update' => $isUpdate,
                'id_asesi'  => $asesiId,
                'message'   => 'Profil berhasil ' . ($isUpdate ? 'diperbarui' : 'disimpan')
            ];
        } catch (Exception $e) {
            $this->db->transRollback();
            log_message('error', '[AsesiService] saveProfileData: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan internal saat menyimpan profil.'];
        }
    }

    /**
     * Menangani upload dokumen untuk asesi.
     */
    public function saveUploadedDocuments(int $asesiId, array $files): array
    {
        if (empty($files)) {
            return ['success' => false, 'message' => 'Tidak ada file yang dipilih untuk diunggah.'];
        }

        $rules = $this->validationService->getValidationRules('file_uploads');

        // --- PERBAIKAN UTAMA ---
        // Panggil validasi dengan data kosong agar CI memeriksa file dari request, bukan dari array $files.
        // Ini adalah cara yang benar untuk memvalidasi file upload.
        if (!$this->validationService->validateData([], $rules)) {
            return ['success' => false, 'message' => 'Validasi file gagal.', 'errors' => $this->validationService->getErrors()];
        }
        // --- AKHIR PERBAIKAN ---

        $this->db->transStart();
        try {
            $asesiData = $this->asesiModel->find($asesiId);
            if (!$asesiData) {
                throw new Exception("Asesi dengan ID {$asesiId} tidak ditemukan.");
            }

            $result = $this->handleFileUploads($files, $asesiData);

            if (!empty($result['dataToUpdate'])) {
                $result['dataToUpdate']['updated_at'] = date('Y-m-d H:i:s');
                $this->asesiModel->update($asesiId, $result['dataToUpdate']);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $this->rollbackUploads($result['dataToUpdate'] ?? []);
                throw new Exception('Gagal memperbarui data dokumen di database.');
            }

            return ['success' => true, 'message' => 'Dokumen berhasil diunggah.', 'files' => $result['uploadedUrls']];
        } catch (Exception $e) {
            $this->db->transRollback();
            log_message('error', '[AsesiService] saveUploadedDocuments: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan internal saat mengunggah dokumen.'];
        }
    }

    /**
     * Menyiapkan data untuk disimpan ke tabel 'asesi'.
     */
    private function prepareAsesiData(array $data, int $userId, bool $isUpdate): array
    {
        $statusPekerjaan = $data['status_pekerjaan'];
        $asesiData = [
            'nik' => $data['nik'],
            'tempat_lahir' => $data['tempat_lahir'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'kebangsaan' => $data['kebangsaan'],
            'provinsi' => $data['provinsi'],
            'kabupaten' => $data['kabupaten'],
            'kecamatan' => $data['kecamatan'],
            'kelurahan' => $data['kelurahan'],
            'rt' => $data['rt'],
            'rw' => $data['rw'],
            'kode_pos' => $data['kode_pos'],
            'telpon_rumah' => $data['telpon_rumah'] ?? null,
            'pekerjaan' => $statusPekerjaan === 'Bekerja' ? ($data['detail_pekerjaan'] ?? 'Bekerja') : $statusPekerjaan,
        ];

        // Reset semua field terkait pekerjaan/pendidikan
        $resettableFields = ['pendidikan_terakhir', 'nama_sekolah', 'jurusan', 'nama_lembaga', 'jabatan', 'alamat_perusahaan', 'email_perusahaan', 'no_telp_perusahaan'];
        foreach ($resettableFields as $field) {
            $asesiData[$field] = null;
        }

        if ($statusPekerjaan === 'Pelajar/Mahasiswa') {
            $asesiData['pendidikan_terakhir'] = $data['pendidikan_terakhir'] ?? null;
            $asesiData['nama_sekolah'] = $data['nama_sekolah'] ?? null;
            $asesiData['jurusan'] = $data['jurusan'] ?? null;
        } elseif ($statusPekerjaan === 'Bekerja') {
            $asesiData['nama_lembaga'] = $data['nama_lembaga'] ?? null;
            $asesiData['jabatan'] = $data['jabatan'] ?? null;
            $asesiData['alamat_perusahaan'] = $data['alamat_perusahaan'] ?? null;
            $asesiData['email_perusahaan'] = $data['email_perusahaan'] ?? null;
            $asesiData['no_telp_perusahaan'] = $data['no_telp_perusahaan'] ?? null;
        }

        if (!$isUpdate) {
            $asesiData['id_user'] = $userId;
        }

        return $asesiData;
    }

    private function handleFileUploads(array $files, array $existingAsesi): array
    {
        $dataToUpdate = [];
        $uploadedUrls = [];
        $uploadPath = FCPATH . 'uploads/asesi_dokumen';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        foreach ($files as $fieldName => $file) {
            if ($file instanceof UploadedFile && $file->isValid() && !$file->hasMoved()) {
                // Hapus file lama jika ada
                if (!empty($existingAsesi[$fieldName]) && file_exists($uploadPath . '/' . $existingAsesi[$fieldName])) {
                    unlink($uploadPath . '/' . $existingAsesi[$fieldName]);
                }

                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);

                $dataToUpdate[$fieldName] = $newName;
                $uploadedUrls[$fieldName] = base_url('uploads/asesi_dokumen/' . $newName);
            }
        }
        return ['dataToUpdate' => $dataToUpdate, 'uploadedUrls' => $uploadedUrls];
    }

    private function rollbackUploads(array $fileNames): void
    {
        $uploadPath = FCPATH . 'uploads/asesi_dokumen';
        foreach ($fileNames as $fileName) {
            if (is_string($fileName) && !empty($fileName)) {
                $filePath = $uploadPath . '/' . $fileName;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }

    private function generateKodeAsesi(): string
    {
        $prefix = "ASI-" . date('Y-m') . "-";
        $lastAsesi = $this->asesiModel->like('kode_asesi', $prefix, 'after')->orderBy('kode_asesi', 'DESC')->first();
        $lastNumber = $lastAsesi ? (int) substr($lastAsesi['kode_asesi'], -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    // Fungsi-fungsi lain yang mungkin dibutuhkan oleh controller

    public function getProfileViewData(int $userId, ?object $user): array
    {
        $asesiResult = $this->getAsesiByUserId($userId);
        $hasAsesiData = $asesiResult->success;
        $asesiData = $hasAsesiData ? (array)$asesiResult->data : null;

        $dependentModel = new DynamicDependent();
        $wilayah = [
            'provinsi' => $asesiData && !empty($asesiData['provinsi']) ? $dependentModel->getNamaWilayah('provinsi', $asesiData['provinsi']) : '',
            'kabupaten' => $asesiData && !empty($asesiData['kabupaten']) ? $dependentModel->getNamaWilayah('kabupaten', $asesiData['kabupaten']) : '',
            'kecamatan' => $asesiData && !empty($asesiData['kecamatan']) ? $dependentModel->getNamaWilayah('kecamatan', $asesiData['kecamatan']) : '',
            'kelurahan' => $asesiData && !empty($asesiData['kelurahan']) ? $dependentModel->getNamaWilayah('kelurahan', $asesiData['kelurahan']) : '',
        ];

        return [
            'siteTitle' => 'Profil Asesi',
            'siteSubtitle' => 'Kelola data pribadi, pendidikan, dan pekerjaan Anda.',
            'provinsi' => $dependentModel->AllProvinsi(),
            'user' => $user,
            'asesi' => $asesiData,
            'hasAsesiData' => $hasAsesiData,
            'wilayah' => $wilayah
        ];
    }

    /**
     * [DIPERBARUI] Menghitung statistik pengajuan untuk satu asesi spesifik.
     */
    public function getAsesiStatistics(int $idAsesi): array
    {
        $defaultStats = [
            'total' => 0,
            'kompeten' => 0,
            'belum_kompeten' => 0
        ];

        if ($idAsesi <= 0) {
            return $defaultStats;
        }

        try {
            $pengajuanModel = new \App\Models\PengajuanAsesmenModel();

            // Dapatkan semua pengajuan untuk asesi ini
            $userApplications = $pengajuanModel->where('id_asesi', $idAsesi)
                ->where('deleted_at', null)
                ->findAll();

            if (empty($userApplications)) {
                return $defaultStats;
            }

            // Inisialisasi statistik
            $stats = [
                'total'          => count($userApplications),
                'kompeten'       => 0,
                'belum_kompeten' => 0,
            ];

            // Hitung statistik berdasarkan status_asesmen
            foreach ($userApplications as $row) {
                if ($row['status_asesmen'] === 'kompeten') {
                    $stats['kompeten']++;
                } elseif ($row['status_asesmen'] === 'belum_kompeten') {
                    $stats['belum_kompeten']++;
                }
            }

            return $stats;
        } catch (Exception $e) {
            log_message('error', '[AsesiService] getAsesiStatistics: ' . $e->getMessage());
            return $defaultStats;
        }
    }

    public function searchSekolah(?string $jenjang, string $search): array
    {
        if (empty($jenjang)) {
            return ['success' => false, 'message' => 'Jenjang pendidikan harus dipilih', 'results' => []];
        }

        try {
            if (in_array($jenjang, ['SD', 'SMP', 'SMA', 'SMK', 'MA', 'MTS', 'TK', 'PAUD'])) {
                $data = $this->fetchSekolahFromAPI($jenjang, $search);
            } elseif (in_array($jenjang, ['Diploma', 'Sarjana', 'Magister', 'Doktor'])) {
                $data = $this->fetchUniversitasFromAPI($search);
            } else {
                $data = [];
            }
            return ['success' => true, 'results' => $data, 'total' => count($data)];
        } catch (Exception $e) {
            log_message('error', 'Error fetching data: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengambil data', 'results' => []];
        }
    }

    /**
     * Fetch sekolah data dari API sekolah.devapi.id
     */
    private function fetchSekolahFromAPI($jenjang, $search = '')
    {
        $bentuk = $this->mapJenjangToBentuk($jenjang);

        // Build URL
        $url = 'https://sekolah.devapi.id/sekolah';
        $params = [
            'bentuk_pendidikan' => $bentuk,
            'limit' => 50
        ];

        if (!empty($search)) {
            $params['nama'] = $search;
        }

        $url .= '?' . http_build_query($params);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: LSP-App/1.0'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);

            if (isset($data['success']) && $data['success'] && isset($data['data'])) {
                return array_map(function ($item) {
                    $alamat = '';
                    if (isset($item['alamat'])) {
                        $alamat = $item['alamat']['nama_kabupaten'] ?? '';
                        if (!empty($item['alamat']['nama_provinsi'])) {
                            $alamat .= ', ' . $item['alamat']['nama_provinsi'];
                        }
                    }

                    return [
                        'nama' => $item['nama'],
                        'bentuk' => $item['bentukPendidikan'] ?? '',
                        'alamat' => $alamat,
                        'display' => $item['nama'] . ' - ' . ($item['bentukPendidikan'] ?? '') . ($alamat ? ' (' . $alamat . ')' : '')
                    ];
                }, $data['data']);
            }
        }

        return [];
    }

    /**
     * Fetch universitas data dari API PDDIKTI
     */
    private function fetchUniversitasFromAPI($search = '')
    {
        $url = 'https://api-pddikti.ridwaanhall.com/search';

        if (!empty($search)) {
            $url .= '?nama=' . urlencode($search);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: LSP-App/1.0'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);

            if (isset($data['data']) && is_array($data['data'])) {
                return array_map(function ($item) {
                    $alamat = $item['alamat'] ?? '';

                    return [
                        'nama' => $item['nama'],
                        'bentuk' => $item['bentuk'] ?? 'Universitas',
                        'alamat' => $alamat,
                        'display' => $item['nama'] . ' - ' . ($item['bentuk'] ?? 'Universitas') . ($alamat ? ' (' . $alamat . ')' : '')
                    ];
                }, array_slice($data['data'], 0, 50));
            }
        }

        return [];
    }

    /**
     * Map jenjang pendidikan ke bentuk pendidikan untuk API
     */
    private function mapJenjangToBentuk($jenjang)
    {
        $mapping = [
            'SD' => 'SD',
            'SMP' => 'SMP',
            'SMA' => 'SMA',
            'SMK' => 'SMK',
            'MA' => 'MA',
            'MTS' => 'MTS',
            'TK' => 'TK',
            'PAUD' => 'PAUD'
        ];

        return $mapping[$jenjang] ?? $jenjang;
    }
}
