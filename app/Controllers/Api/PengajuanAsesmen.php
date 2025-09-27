<?php

namespace App\Controllers\Api;

use App\Models\PertanyaanTertulisModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\DataTableController;
use App\Services\EmailService;
use App\Models\AsesorModel;
use App\Models\PengajuanAsesmenModel;
use Exception;

class PengajuanAsesmen extends DataTableController
{

    use ResponseTrait;

    private EmailService $emailService;

    public function __construct()
    {
        parent::__construct();
        $this->emailService = new EmailService();
        $this->model = new PengajuanAsesmenModel();
        $this->columnMap = [
            0 => null, // No
            1 => 'u.nama_lengkap',
            2 => 'sk.nama_skema',
            3 => 'pengajuan_asesmen.created_at',
            4 => 'pengajuan_asesmen.status_pengajuan',
            5 => 'pengajuan_asesmen.status_asesmen',
            6 => 'u_asesor.nama_lengkap',
            7 => null, // Aksi
        ];
    }

    /**
     * Mengambil data pengajuan berdasarkan ID untuk modal edit
     */
    public function getById($id): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        try {
            $pengajuan = $this->model->find($id);
            if (!$pengajuan) {
                return $this->failNotFound('Pengajuan tidak ditemukan');
            }

            $asesi = $this->asesiModel->find($pengajuan['id_asesi']);
            $asesmen = $this->asesmenModel->find($pengajuan['id_asesmen']);

            // Dokumen sekarang dari tabel asesi
            $dokumen = [
                'pas_foto' => $asesi['pas_foto'] ?? null,
                'ktp' => $asesi['ktp'] ?? null,
                'bukti_pendidikan' => $asesi['bukti_pendidikan'] ?? null
            ];

            // Tambahkan query untuk mendapatkan daftar asesor (dari tabel users)
            $userModel = new \App\Models\UserModel();
            $asesorList = $userModel->select('id as id_asesor, nama_lengkap as nama_asesor')->findAll();

            return $this->respond([
                'status' => true,
                'data' => [
                    'pengajuan' => $pengajuan,
                    'asesi' => $asesi,
                    'asesmen' => $asesmen,
                    'dokumen' => $dokumen
                ],
                'asesorList' => $asesorList
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Gagal mengambil data: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan perubahan data pengajuan (update)
     */
    public function save($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $rules = [
            'status_pengajuan' => 'required|in_list[pending,diterima,ditolak,selesai]',
            'status_asesmen'   => 'required|in_list[proses,kompeten,belum_kompeten]',
            'id_asesor'        => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Prioritize URL ID for edits, fall back to POST data for creates
        $id = $id ?? $this->request->getPost('id_pengajuan');
        $data = $this->validator->getValidated();

        try {
            if (empty($data['id_asesor'])) {
                $data['id_asesor'] = null;
            }

            $this->model->update($id, $data);

            return $this->respondUpdated([
                'status' => true,
                'message' => 'Data pengajuan berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * [DIPERBARUI] Menyimpan data validasi dan mengirim email notifikasi.
     */
    public function validatePengajuan($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $rules = [
            'status_pengajuan' => 'required|in_list[diterima,ditolak]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $statusPengajuan = $this->request->getPost('status_pengajuan');

        $pengajuanData = $this->model->getCompletePengajuanData($id);

        if (!$pengajuanData) {
            return $this->failNotFound('Data pengajuan tidak ditemukan.');
        }

        $dataToUpdate = [
            'status_pengajuan' => $statusPengajuan,
            'validator_id'     => user()->id,
            'validated_at'     => date('Y-m-d H:i:s')
        ];

        try {
            if ($this->model->update($id, $dataToUpdate)) {
                try {
                    $this->sendValidationNotificationEmail($id, $pengajuanData, $statusPengajuan);
                } catch (\Exception $emailEx) {
                    // Jika email gagal, jangan batalkan proses. Cukup catat log.
                    log_message('error', '[EMAIL VALIDASI GAGAL] ' . $emailEx->getMessage());
                    // Kirim pesan sukses dengan peringatan
                    return $this->respondUpdated([
                        'status'  => true,
                        'message' => 'Pengajuan berhasil divalidasi, namun email notifikasi gagal dikirim.'
                    ]);
                }

                // Jika update dan email berhasil
                return $this->respondUpdated([
                    'status'  => true,
                    'message' => 'Pengajuan berhasil divalidasi dan email notifikasi telah dikirim.'
                ]);
            } else {
                return $this->fail('Gagal memperbarui data di database.');
            }
        } catch (\Exception $e) {
            log_message('error', '[VALIDATE PENGAJUAN] ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server.');
        }
    }

    /**
     * [METHOD BARU] Method helper untuk mengirim email validasi.
     * Mengambil inspirasi dari kode lama Anda.
     */
    private function sendValidationNotificationEmail(string $id, array $pengajuanData, string $status): void
    {
        $to = $pengajuanData['asesi']['email'] ?? null;

        if (empty($to)) {
            // Jika email tidak ada, batalkan pengiriman
            throw new \Exception('Email asesi tidak ditemukan.');
        }

        $nama_asesi = $pengajuanData['nama_asesi'] ?? 'Peserta';
        $skema = $pengajuanData['nama_skema'] ?? 'Skema Sertifikasi';
        $subject = 'Informasi Validasi Pengajuan Asesmen';

        $emailData = [
            'nama_asesi'        => $nama_asesi,
            'skema'             => $skema,
            'status_validasi'   => $status,
            'validator'         => user()->username,
            'tanggal_validasi'  => date('d F Y H:i'),
        ];

        // Pilih template email berdasarkan status validasi
        if ($status === "diterima") {
            $template = 'email/email_validated_apl1';
            $emailData['next_step_url'] = site_url("asesmen-mandiri/{$id}");
        } else { // ditolak
            $template = 'email/email_unvalidated_apl1';
            $emailData['alasan_penolakan'] = 'Dokumen atau data yang Anda berikan belum memenuhi persyaratan. Silakan hubungi admin untuk informasi lebih lanjut.';
            $emailData['email_kontak'] = 'lspp1smkn2kuningan@gmail.com';
        }

        $this->emailService->sendEmail($to, $subject, $template, $emailData);
    }

    /**
     * Menghapus data pengajuan
     */
    public function delete($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        try {
            if ($this->model->delete($id)) {
                return $this->respondDeleted([
                    'status' => true,
                    'message' => 'Data pengajuan berhasil dihapus.'
                ]);
            }
            return $this->fail('Gagal menghapus data.');
        } catch (\Exception $e) {
            return $this->failServerError('Gagal menghapus data. Kemungkinan terkait dengan data lain.');
        }
    }

    /**
     * AJAX endpoint to fetch filtered and paginated asesmen data.
     * Returns data in JSON format.
     */
    public function getAsesmenJson()
    {
        // Set a default items per page
        $limit = 6;

        // Get parameters from the AJAX request
        $page = $this->request->getGet('page') ? (int) $this->request->getGet('page') : 1;
        $searchTerm = $this->request->getGet('search') ?? '';

        // Calculate the offset for the database query
        $offset = ($page - 1) * $limit;

        // Fetch data from the model
        $result = $this->asesmenModel->getFilteredPaginatedAsesmen($searchTerm, $limit, $offset);

        // Prepare the response
        $response = [
            'data' => $result['data'],
            'total' => $result['totalRows'],
            'limit' => $limit
        ];

        // Return a JSON response
        return $this->response->setJSON($response);
    }

    /**
     * AJAX endpoint untuk mengambil detail asesmen dan unit kompetensinya.
     * Ini adalah method yang sudah ada dari permintaan sebelumnya, `getSkemaDetailJson`.
     * Pastikan method ini ada di controller Anda.
     */
    public function getSkemaDetailJson()
    {
        $id_asesmen = $this->request->getGet('id');

        if (!$id_asesmen || !is_numeric($id_asesmen)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID Asesmen tidak valid.']);
        }

        $asesmen = $this->asesmenModel->getAsesmenById($id_asesmen);
        $listUnit = $this->unitModel->getUnitByIdAsesmen($id_asesmen);

        if (!$asesmen) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Data tidak ditemukan.']);
        }

        $response = [
            'asesmen'  => $asesmen,
            'listUnit' => $listUnit,
        ];

        return $this->response->setJSON($response);
    }

    public function submit_pengajuan_ajax()
    {
        // 1. Validasi AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akses tidak diizinkan'
            ])->setStatusCode(401);
        }

        try {
            // 2. Ambil data dari request
            $id_asesmen = $this->request->getPost('id_asesmen');

            // Validasi input
            if (!$id_asesmen || !is_numeric($id_asesmen)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID Asesmen tidak valid'
                ]);
            }

            // 3. Dapatkan ID user yang sedang login
            $id_user = user()->id ?? null;
            if (!$id_user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Anda harus login untuk mendaftar'
                ]);
            }

            $asesi = $this->asesiModel->where('id_user', $id_user)
                ->where('deleted_at', null)
                ->first();

            if (!$asesi) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Profil Anda belum lengkap. Silakan lengkapi profil terlebih dahulu.'
                ]);
            }

            $id_asesi = $asesi['id_asesi'];

            // 5. Cek apakah sudah pernah mendaftar
            $existing = $this->pengajuanAsesmenModel->where('id_asesi', $id_asesi)
                ->where('id_asesmen', $id_asesmen)
                ->where('deleted_at', null)
                ->first();

            if ($existing) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Anda sudah terdaftar pada skema ini'
                ]);
            }

            // 6. Siapkan data untuk insert
            $dataToSave = [
                'id_asesi'         => $id_asesi,
                'id_asesmen'       => $id_asesmen,
                'status_pengajuan' => 'pending',
                'status_asesmen'   => 'proses'
            ];

            // 7. Simpan ke database menggunakan method dari model
            $result = $this->pengajuanAsesmenModel->createPengajuan($dataToSave);

            if ($result) {
                // 8. Kirim email notifikasi (optional - bisa di-comment jika tidak dibutuhkan)
                $this->sendNotificationEmail($id_user);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil! Silakan tunggu konfirmasi dari admin.'
                ]);
            } else {
                // Debug: tampilkan error dari model
                $errors = $this->pengajuanAsesmenModel->errors();
                log_message('error', 'Insert pengajuan failed: ' . json_encode($errors));

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . implode(', ', $errors)
                ]);
            }
        } catch (\Exception $e) {
            // Log error untuk debugging
            log_message('error', 'Error in submit_pengajuan_ajax: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Method terpisah untuk mengirim email notifikasi
     */
    private function sendNotificationEmail($id_user)
    {
        try {
            $auth = service('authentication');
            $user = $auth->user();

            if (!$user || !$user->email) {
                return false;
            }

            $email = \Config\Services::email();
            $email->setTo($user->email);
            $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');
            $email->setSubject('Konfirmasi Pendaftaran Uji Kompetensi');

            $message = "Halo {$user->nama_lengkap},\n\n";
            $message .= "Pendaftaran Anda untuk uji kompetensi telah berhasil diterima.\n";
            $message .= "Silakan tunggu konfirmasi lebih lanjut dari admin.\n\n";
            $message .= "Terima kasih.";

            $email->setMessage($message);
            $email->send();

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Failed to send notification email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Method untuk mengecek status pendaftaran asesi
     */
    public function check_registration_status()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized'
            ])->setStatusCode(401);
        }

        try {
            $id_asesmen = $this->request->getGet('id_asesmen');
            $id_user = user()->id ?? null;

            if (!$id_user || !$id_asesmen) {
                return $this->response->setJSON([
                    'already_registered' => false
                ]);
            }

            // Cari data asesi
            $asesi = $this->asesiModel->where('id_user', $id_user)
                ->where('deleted_at', null)
                ->first();

            if (!$asesi) {
                return $this->response->setJSON([
                    'already_registered' => false
                ]);
            }

            // Cek apakah sudah pernah mendaftar
            $existing = $this->pengajuanAsesmenModel->where('id_asesi', $asesi['id_asesi'])
                ->where('id_asesmen', $id_asesmen)
                ->where('deleted_at', null)
                ->first();

            if ($existing) {
                return $this->response->setJSON([
                    'already_registered' => true,
                    'data' => [
                        'status_pengajuan' => $existing['status_pengajuan'],
                        'status_asesmen' => $existing['status_asesmen'],
                        'tanggal_pengajuan' => $existing['tanggal_pengajuan']
                    ]
                ]);
            }

            return $this->response->setJSON([
                'already_registered' => false
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'already_registered' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
