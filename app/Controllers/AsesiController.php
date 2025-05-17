<?php

namespace App\Controllers;

use Config\Database;
use Config\Services;
use CodeIgniter\I18n\Time;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AsesiController extends BaseController
{
    protected $db;
    protected $user_id;
    protected $validator;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->user_id = user()->id;
    }

    public function index()
    {
        $data = [
            'siteTitle' => 'Dashboard',

        ];

        return view('asesi/dashboard', $data);
    }

    public function profile()
    {
        $data = [
            'siteTitle' => 'Profile Asesi',
            'siteSubtitle' => 'Pada bagian ini, masukan data pribadi, data pendidikan formal, data pekerjaan Anda pada saat ini, serta dokumen pendukung.',
            'provinsi' => $this->dependent->AllProvinsi(),
            'asesi' => $this->asesiModel->getWithUserByUserId($this->user_id),
        ];

        return view('asesi/profile', $data);
    }


    public function save()
    {
        // Check if this is an update (ID exists) or new entry
        $id = $this->request->getVar('id_asesi');
        $isUpdate = !empty($id);

        // Validate form input data
        if (!$this->validateFormData($isUpdate)) {
            return $this->failedResponse('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
        }

        try {
            // Begin transaction
            $this->db->transBegin();

            $userId = $this->request->getVar('user_id') ?? $this->user_id;

            // First, handle user data updates (including signature)
            $this->updateUserData($userId);

            // For new entries, generate a unique ID
            if (!$isUpdate) {
                $id = generate_application_id('ASI', 'asesi', 'id_asesi');
            }

            // Prepare data for update/insert
            $data = $this->prepareAsesiData($id, $userId);

            // Update or insert based on whether ID exists
            if ($isUpdate) {
                // Update existing data
                if (!$this->asesiModel->update($id, $data)) {
                    throw new \Exception('Gagal mengupdate data asesi.');
                }
            } else {
                // Insert new data
                if (!$this->asesiModel->insert($data)) {
                    throw new \Exception('Gagal menyimpan data asesi.');
                }

                // Here you would handle additional inserts for related tables
                // like APL1, etc. (based on your original code)
            }

            // Commit transaction
            $this->db->transCommit();

            // Success response
            $message = $isUpdate ?
                'Data asesi berhasil diupdate.' :
                'Pengajuan Uji Kompetensi berhasil terkirim, Silahkan Cek Email untuk info lebih lanjut!';

            session()->setFlashdata('pesan', $message);
            return redirect()->to($isUpdate ? '/profile' : '/dashboard');
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->transRollback();
            log_message('error', 'Error during ' . ($isUpdate ? 'update' : 'submission') . ': ' . $e->getMessage());

            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }



    /**
     * Update user data including signature handling
     *
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    private function updateUserData(int $userId): bool
    {
        $userData = [
            // 'username' => $this->request->getVar('username'),
            'fullname' => $this->request->getVar('fullname'),
            'no_telp'  => $this->request->getVar('no_telp'),
        ];

        // Handle signature - check if it has been updated
        $signatureData = $this->request->getVar('tanda_tangan');
        $signatureFile = $this->request->getFile('tanda_tangan');
        $signatureDir = WRITEPATH . 'uploads/user/signatures/';

        if (!is_dir($signatureDir)) {
            mkdir($signatureDir, 0755, true);
        }

        $newSignatureName = null;
        $user = $this->usermodel->find($userId);

        // Handle base64 signature data (from canvas)
        if (!empty($signatureData) && strpos($signatureData, 'data:image') === 0) {
            list(, $encodedData) = explode(',', $signatureData);
            $decodedData = base64_decode($encodedData);
            $newSignatureName = uniqid('sig_') . '.png';
            $savePath = $signatureDir . $newSignatureName;

            if (!file_put_contents($savePath, $decodedData)) {
                throw new \Exception('Gagal menyimpan tanda tangan.');
            }
        }
        // Handle file upload signature
        else if ($signatureFile && $signatureFile->isValid() && !$signatureFile->hasMoved()) {
            $newSignatureName = $signatureFile->getRandomName();

            if (!$signatureFile->move($signatureDir, $newSignatureName)) {
                throw new \Exception('Gagal mengupload tanda tangan.');
            }
        }

        // Update signature in user data if there's a new one
        if ($newSignatureName) {
            // Remove old signature file if exists
            if (!empty($user['tanda_tangan'])) {
                $oldSignaturePath = $signatureDir . $user['tanda_tangan'];
                if (file_exists($oldSignaturePath)) {
                    unlink($oldSignaturePath);
                }
            }

            $userData['tanda_tangan'] = $newSignatureName;
        }

        // Update user data
        if (!$this->usermodel->update($userId, $userData)) {
            throw new \Exception('Gagal mengupdate data user.');
        }

        return true;
    }


    /**
     * Validate form input data
     *
     * @param bool $isUpdate Whether this is an update operation
     * @return bool
     */
    private function validateFormData(bool $isUpdate = false): bool
    {
        $rules = [
            'nik' => [
                'label' => 'Nomor KTP/NIK/Paspor',
                'rules' => 'required|numeric|max_length[16]' . ($isUpdate ? '' : '|is_unique[asesi.nik]'),
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'numeric' => 'Nomor {field} harus berupa angka.',
                    'is_unique' => 'Nomor {field} sudah digunakan, silakan gunakan nomor {field} lain.',
                    'max_length' => 'Nomor {field} tidak boleh lebih dari 16 digit.'
                ],
            ],
            'tempat_lahir' => [
                'label' => 'Tempat Lahir',
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'max_length' => '{field} tidak boleh melebihi 255 karakter.',
                ],
            ],
            'tanggal_lahir' => [
                'label' => 'Tanggal Lahir',
                'rules' => 'required|valid_date',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'valid_date' => 'Format {field} tidak valid.',
                ],
            ],
            'jenis_kelamin' => [
                'label' => 'Jenis Kelamin',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus dipilih.'],
            ],
            'pendidikan_terakhir' => [
                'label' => 'Pendidikan Terakhir',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus dipilih.'],
            ],
            'nama_sekolah' => [
                'label' => 'Nama Sekolah',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus dipilih.'],
            ],
            'jurusan' => [
                'label' => 'Jurusan',
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'max_length' => 'Panjang teks {field} tidak boleh melebihi 255 karakter.',
                ],
            ],
            'kebangsaan' => [
                'label' => 'Kebangsaan',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus dipilih.'],
            ],
            'provinsi' => [
                'label' => 'Provinsi',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus diisi.'],
            ],
            'kabupaten' => [
                'label' => 'Kabupaten',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus diisi.'],
            ],
            'kecamatan' => [
                'label' => 'Kecamatan',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus diisi.'],
            ],
            'kelurahan' => [
                'label' => 'Kelurahan',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus diisi.'],
            ],
            'rt' => [
                'label' => 'RT',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus diisi.'],
            ],
            'rw' => [
                'label' => 'RW',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus diisi.'],
            ],
            'kode_pos' => [
                'label' => 'Kode Pos',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus diisi.'],
            ],
            'telpon_rumah' => [
                'label' => 'Telepon Rumah',
                'rules' => 'permit_empty|numeric',
                'errors' => ['numeric' => 'Kolom {field} harus berupa angka.'],
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email' . ($isUpdate ? '' : '|is_unique[asesi.email]'),
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'valid_email' => 'Format {field} harus valid.',
                    'is_unique' => 'Kolom {field} sudah terdaftar. Silakan pilih email lain.',
                ],
            ],
            'pekerjaan' => [
                'label' => 'Pekerjaan',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus diisi.'],
            ],
        ];

        $this->validator = Services::validation();
        return $this->validate($rules);
    }

    /**
     * Prepare asesi data for database insertion or update
     *
     * @param string $idAsesi
     * @param int $userId
     * @return array
     */
    private function prepareAsesiData(string $idAsesi, int $userId): array
    {
        $data = [
            'id_asesi'             => $idAsesi,
            'user_id'              => $userId,
            'nik'                  => $this->request->getVar('nik'),
            'tempat_lahir'         => $this->request->getVar('tempat_lahir'),
            'tanggal_lahir'        => $this->request->getVar('tanggal_lahir'),
            'jenis_kelamin'        => $this->request->getVar('jenis_kelamin'),
            'pendidikan_terakhir'  => $this->request->getVar('pendidikan_terakhir'),
            'nama_sekolah'         => $this->request->getVar('nama_sekolah'),
            'jurusan'              => $this->request->getVar('jurusan'),
            'kebangsaan'           => $this->request->getVar('kebangsaan'),
            'provinsi'             => $this->request->getVar('provinsi'),
            'kabupaten'            => $this->request->getVar('kabupaten'),
            'kecamatan'            => $this->request->getVar('kecamatan'),
            'kelurahan'            => $this->request->getVar('kelurahan'),
            'rt'                   => $this->request->getVar('rt'),
            'rw'                   => $this->request->getVar('rw'),
            'kode_pos'             => $this->request->getVar('kode_pos'),
            'telpon_rumah'         => $this->request->getVar('telpon_rumah'),
            'email'                => $this->request->getVar('email'),
            'pekerjaan'            => $this->request->getVar('pekerjaan'),
            'nama_lembaga'         => $this->request->getVar('nama_lembaga'),
            'jabatan'              => $this->request->getVar('jabatan'),
            'alamat_perusahaan'    => $this->request->getVar('alamat_perusahaan'),
            'email_perusahaan'     => $this->request->getVar('email_perusahaan'),
            'no_telp_perusahaan'   => $this->request->getVar('no_telp_perusahaan'),
        ];

        // For new entries, add created_at timestamp
        if (!$this->request->getVar('id_asesi')) {
            $data['created_at'] = Time::now();
        } else {
            // For updates, add updated_at timestamp
            $data['updated_at'] = Time::now();
        }

        return $data;
    }

    /**
     * Handle file uploads for the form
     *
     * @param bool $isUpdate Whether this is an update operation
     * @return array File paths or error message
     */
    private function handleFileUploads(bool $isUpdate = false): array
    {
        $result = [];

        // Handle signature (assuming this might be part of your form)
        $signatureData = $this->request->getVar('tanda_tangan');

        if (!empty($signatureData) && strpos($signatureData, 'data:image') === 0) {
            $signatureDir = WRITEPATH . 'uploads/user/signatures/';
            if (!is_dir($signatureDir)) {
                mkdir($signatureDir, 0755, true);
            }

            // Process base64 signature
            list(, $encodedData) = explode(',', $signatureData);
            $decodedData = base64_decode($encodedData);
            $newSignatureName = uniqid('sig_') . '.png';
            $savePath = $signatureDir . $newSignatureName;

            if (!file_put_contents($savePath, $decodedData)) {
                return ['error' => 'Gagal menyimpan tanda tangan.'];
            }

            $result['tanda_tangan'] = $newSignatureName;

            // If updating, remove old signature
            if ($isUpdate) {
                $oldData = $this->asesiModel->find($this->request->getVar('id_asesi'));
                if (!empty($oldData['tanda_tangan'])) {
                    $oldSignaturePath = $signatureDir . $oldData['tanda_tangan'];
                    if (file_exists($oldSignaturePath)) {
                        unlink($oldSignaturePath);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Handle failed form submission
     *
     * @param string $type Alert type (warning, error, etc.)
     * @param string $message Alert message
     * @return ResponseInterface
     */
    private function failedResponse(string $type, string $message): ResponseInterface
    {
        session()->setFlashdata($type, $message);
        $errors = $this->validator ? $this->validator->getErrors() : [];

        return redirect()->back()->withInput()->with('errors', $errors);
    }
}
