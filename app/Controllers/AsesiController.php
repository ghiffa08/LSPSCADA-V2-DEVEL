<?php

namespace App\Controllers;

use App\Services\AsesiService;
use CodeIgniter\HTTP\ResponseInterface;

class AsesiController extends BaseController
{
    private AsesiService $asesiService;
    private int $userId;

    public function __construct()
    {
        helper('auth');
        $this->asesiService = service('AsesiService');
        $this->userId = user() ? user()->id : 0;
    }

    public function index()
    {

        try {
            $result = $this->asesiService->getAsesiByUserId($this->userId);
            if (!$result->success) {
                log_message('info', 'User ID ' . $this->userId . ' belum memiliki profil asesi. Mengarahkan ke halaman profil.');
                session()->setFlashdata('info', 'Silakan lengkapi profil Anda terlebih dahulu untuk mengakses dashboard.');
                return redirect()->to('asesi/profile');
            }

            $data = [
                'siteTitle' => 'Dashboard',
                'asesi' => $result->data
            ];
            return view('asesi/dashboard', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error memuat dashboard asesi: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan saat memuat dashboard.');
            return redirect()->back();
        }
    }

    /**
     * API Endpoint untuk mengambil data dinamis dashboard.
     * Metode ini akan dipanggil oleh AJAX.
     */
    public function getDashboardData()
    {
        // Pastikan ini adalah request AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        try {
            // 1. Dapatkan ID Asesi dari User ID yang sedang login
            $asesiResult = $this->asesiService->getAsesiByUserId($this->userId);
            if (!$asesiResult->success) {
                return $this->response->setJSON(['success' => false, 'message' => 'Profil asesi tidak ditemukan.']);
            }
            $idAsesi = $asesiResult->data->id_asesi;

            // 2. Dapatkan statistik dari service
            $stats = $this->asesiService->getAsesiStatistics($idAsesi);

            // 3. Dapatkan daftar pengajuan asesmen dari model
            $pengajuanList = $this->pengajuanAsesmenModel->getPengajuanByAsesiId($idAsesi);

            // 4. Gabungkan semua data menjadi satu response JSON
            $responseData = [
                'success' => true,
                'stats' => $stats,
                'pengajuan' => $pengajuanList
            ];

            return $this->response->setJSON($responseData);
        } catch (\Exception $e) {
            log_message('error', 'AJAX Error on getDashboardData: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengambil data.']);
        }
    }

    public function profile()
    {
        try {
            $user = user();
            if (!$user) {
                return redirect()->to('/login');
            }

            // Mengirimkan objek user ke service untuk menghindari error
            $result = $this->asesiService->getProfileViewData($this->userId, $user);

            return view('asesi/profile', $result);
        } catch (\Exception $e) {
            log_message('error', 'Error memuat profil asesi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat profil');
        }
    }

    /**
     * Menyimpan atau memperbarui data profil asesi.
     * Logika validasi dan penyimpanan telah dipindahkan ke AsesiService.
     */
    public function save()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        try {
            $postData = $this->request->getPost();
            $result = $this->asesiService->saveProfileData($postData, $this->userId);

            if (!$result['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Validasi gagal',
                    'errors' => $result['errors']
                ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }

            // MODIFIED: Adjust the JSON response to match JavaScript expectations for the new user flow.
            return $this->response->setJSON([
                'success' => true,
                'message' => $result['message'],
                'is_new'  => !$result['is_update'], // JS expects 'is_new' (boolean)
                'id_asesi' => $result['id_asesi'] ?? null // JS needs the new ID to proceed
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat menyimpan profil asesi: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan internal server.'])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mengunggah dokumen kelengkapan asesi.
     * Logika validasi dan penyimpanan file telah dipindahkan ke AsesiService.
     */
    public function uploadDocuments()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        try {
            $asesiId = $this->request->getPost('id_asesi');
            $files = $this->request->getFiles();

            $result = $this->asesiService->saveUploadedDocuments((int)$asesiId, $files);

            if (!$result['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Validasi file gagal.',
                    'errors'  => $result['errors'] ?? []
                ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $result['message'],
                'files'   => $result['files'] // Mengembalikan URL file yang diunggah
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat mengunggah dokumen: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan internal.'])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validasi field secara real-time via AJAX.
     * Menggunakan aturan validasi dari AsesiService.
     */
    public function validateField()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Invalid request'])->setStatusCode(403);
        }

        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        // Mengambil semua aturan validasi dari service
        $rules = $this->asesiService->getValidationRules();

        // Jika field tidak memiliki aturan, anggap valid
        if (!isset($rules[$field])) {
            return $this->response->setJSON(['valid' => true]);
        }

        // Jalankan validasi hanya untuk field yang diminta
        $validation = \Config\Services::validation();
        $validation->setRules([$field => $rules[$field]]);
        $isValid = $validation->run([$field => $value]);

        return $this->response->setJSON([
            'valid'   => $isValid,
            'message' => $isValid ? '' : $validation->getError($field)
        ]);
    }

    // --- AJAX Helper Functions ---

    public function getSekolah()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }
        $jenjang = $this->request->getPost('jenjang');
        $search = $this->request->getPost('search') ?? '';
        $data = $this->asesiService->searchSekolah($jenjang, $search);
        return $this->response->setJSON($data);
    }

    public function getKabupaten()
    {
        if ($this->request->isAJAX()) {
            $id_provinsi = $this->request->getPost('id_provinsi');
            return $this->response->setJSON($this->dynamicDependentModel->getKabupaten($id_provinsi));
        }
    }

    public function getKecamatan()
    {
        if ($this->request->isAJAX()) {
            $id_kabupaten = $this->request->getPost('id_kabupaten');
            return $this->response->setJSON($this->dynamicDependentModel->getKecamatan($id_kabupaten));
        }
    }

    public function getKelurahan()
    {
        if ($this->request->isAJAX()) {
            $id_kecamatan = $this->request->getPost('id_kecamatan');
            return $this->response->setJSON($this->dynamicDependentModel->getKelurahan($id_kecamatan));
        }
    }
}
