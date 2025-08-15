<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;
use App\Services\AsesiService;
use App\Services\ValidationService;
use App\Services\CustomResponseService;
use CodeIgniter\I18n\Time;
use Exception;
use Config\Services;

class AsesiController extends BaseController
{
    private AsesiService $asesiService;
    private ValidationService $validationService;
    private CustomResponseService $responseService;
    // private $dependent;
    private $usermodel;
    private int $userId;

    public function __construct()
    {
        helper('auth');

        $this->asesiService = service('AsesiService');
        $this->validationService = new ValidationService();
        $this->responseService = service('CustomResponseService');
        $this->dependent = new \App\Models\DynamicDependent();
        $this->usermodel = new \App\Models\UserMythModel();
        $this->userId = user() ? user()->id : 0;
    }

    /**
     * Display asesi dashboard with statistics
     */
    public function index()
    {
        try {
            // Get asesi data by user id
            $result = $this->asesiService->getAsesiByUserId($this->userId);

            // If asesi profile doesn't exist yet, redirect to profile page to create it
            if (!$result->success) {
                log_message('info', 'User ID ' . $this->userId . ' does not have an asesi profile yet. Redirecting to profile page.');
                session()->setFlashdata('info', 'Silakan lengkapi profil Anda terlebih dahulu untuk mengakses dashboard.');
                return redirect()->to('asesi/profile');
            }

            $asesi = $result->data;

            // Get asesi's application statistics
            // Entity already casts id_asesi to integer
            $idAsesi = $asesi->id_asesi ?? 0;

            log_message('debug', 'AsesiController::index - idAsesi resolved to: ' . $idAsesi . ' (type: ' . gettype($idAsesi) . ')');

            $stats = $this->getAsesiStatistics($idAsesi);

            $data = [
                'siteTitle' => 'Dashboard',
                'asesi' => $asesi,
                'stat' => $stats
            ];
            return view('asesi/dashboard', $data);
        } catch (Exception $e) {
            log_message('error', 'Error loading asesi dashboard: ' . $e->getMessage());
            return $this->responseService->error('Terjadi kesalahan saat memuat dashboard');
        }
    }

    /**
     * Calculate statistics for asesi dashboard
     */
    private function getAsesiStatistics(int $idAsesi): array
    {
        // Add validation for idAsesi
        if ($idAsesi <= 0) {
            log_message('warning', 'getAsesiStatistics called with invalid idAsesi: ' . $idAsesi);
            return [
                'total_pengajuan' => 0,
                'status' => ['proses' => 0, 'menunggu' => 0, 'selesai' => 0],
                'dokumen' => 0,
                'progress' => 0
            ];
        }

        try {
            $pengajuanModel = new \App\Models\PengajuanAsesmenModel();
            $db = \Config\Database::connect();

            // Get asesi's applications
            $userApplications = $pengajuanModel->where('id_asesi', $idAsesi)->findAll();
            $totalPengajuan = count($userApplications);

            // Initialize counters
            $statusCounts = [
                'proses' => 0,
                'menunggu' => 0,
                'selesai' => 0
            ];

            // Count applications by status
            foreach ($userApplications as $row) {
                if ($row['status'] === 'pending') $statusCounts['menunggu']++;
                elseif ($row['status'] === 'approved') $statusCounts['selesai']++;
                else $statusCounts['proses']++;
            }

            // Count documents
            $applicationIds = array_column($userApplications, 'id_apl1');
            $dokumenCount = empty($applicationIds) ? 0 :
                $db->table('dokumen_apl1')
                ->whereIn('id_apl1', $applicationIds)
                ->countAllResults();

            // Calculate progress percentage
            $progress = $totalPengajuan > 0 ?
                round(($statusCounts['selesai'] / $totalPengajuan) * 100) : 0;

            return [
                'total_pengajuan' => $totalPengajuan,
                'status' => $statusCounts,
                'dokumen' => $dokumenCount,
                'progress' => $progress
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in getAsesiStatistics for idAsesi ' . $idAsesi . ': ' . $e->getMessage());
            return [
                'total_pengajuan' => 0,
                'status' => ['proses' => 0, 'menunggu' => 0, 'selesai' => 0],
                'dokumen' => 0,
                'progress' => 0
            ];
        }
    }

    /**
     * Display asesi profile page
     */
    public function profile()
    {
        try {
            $currentUser = user();
            if (!$currentUser) {
                return redirect()->to('/login');
            }

            // Get existing asesi data if available
            $asesiData = null;
            $hasAsesiData = false;

            $result = $this->asesiService->getAsesiByUserId($this->userId);
            if ($result->success && $result->data) {
                $asesiData = $result->data;
                $hasAsesiData = true;
            }

            $data = [
                'siteTitle' => 'Profile Asesi',
                'siteSubtitle' => 'Pada bagian ini, masukan data pribadi, data pendidikan formal, data pekerjaan Anda pada saat ini, serta dokumen pendukung.',
                'provinsi' => $this->dependent->AllProvinsi(),
                'user' => $currentUser,
                'asesi' => $asesiData,
                'hasAsesiData' => $hasAsesiData
            ];

            return view('asesi/profile', $data);
        } catch (Exception $e) {
            log_message('error', 'Error loading asesi profile: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat profile');
        }
    }

    /**
     * Save detailed asesi profile data
     */
    public function save()
    {
        try {
            // Check if the user wants to insert detailed asesi data
            if (empty($this->request->getVar('nik'))) {
                session()->setFlashdata('error', 'Form detail profil harus diisi.');
                return redirect()->back()->withInput();
            }

            // Update user's phone number if provided
            $no_telp = $this->request->getVar('no_hp');
            if (!empty($no_telp)) {
                $phoneRules = [
                    'no_hp' => 'required|max_length[20]|regex_match[/^(\+62|62|0)[0-9]{9,12}$/]'
                ];
                $phoneCustomErrors = [
                    'no_hp' => [
                        'regex_match' => 'Format nomor telepon tidak valid. Gunakan format: 08123456789'
                    ]
                ];

                $validationPhone = \Config\Services::validation();
                $validationPhone->setRules($phoneRules, $phoneCustomErrors);

                if (!$validationPhone->run(['no_hp' => $no_telp])) {
                    return redirect()->back()->withInput()->with('errors', $validationPhone->getErrors());
                }

                // Update user's phone number in users table
                $this->usermodel->update($this->userId, ['no_hp' => $no_telp]);
                log_message('info', 'Updated user phone number during asesi profile save: ' . $no_telp);
            }

            // Prepare and sanitize data
            $data = $this->prepareAsesiData();

            // Validate form input data
            $validationResult = $this->validationService->validateAsesi($data);
            if (!$validationResult->success) {
                return redirect()->back()->withInput()->with('errors', $validationResult->errors);
            }

            // Check if this is an update (ID exists) or new entry
            $id = $this->request->getVar('id_asesi');
            $isUpdate = !empty($id);

            // Log full data before saving for debugging
            log_message('debug', 'Data to be saved: ' . json_encode($data));

            // Save or update asesi data
            $result = $isUpdate ?
                $this->asesiService->updateAsesi($id, $data) :
                $this->asesiService->createAsesi($data);

            if ($result->success) {
                $message = $isUpdate ? 'Data profil berhasil diperbarui!' : 'Data profil berhasil disimpan!';
                session()->setFlashdata('pesan', $message);
                log_message('info', ($isUpdate ? 'Updated' : 'Created') . ' asesi profile for user_id: ' . $this->userId);
                return redirect()->to('asesi/profile')->with('success', $message);
            } else {
                $errorMsg = $result->message;
                $errors = $result->errors ?? [];

                log_message('error', 'Error ' . ($isUpdate ? 'updating' : 'creating') . ' asesi: ' . $errorMsg);
                log_message('error', 'Full result: ' . json_encode($result));

                if (!empty($errors)) {
                    log_message('error', 'Validation errors: ' . json_encode($errors));
                }

                // Debug database errors
                $db = \Config\Database::connect();
                if ($db->error()['code'] !== 0) {
                    log_message('error', 'DB Error Code: ' . $db->error()['code'] . ', Message: ' . $db->error()['message']);
                }

                // Additional detailed logging
                log_message('error', 'Input data causing error: ' . json_encode($data));
                log_message('error', 'Error context: ' . ($isUpdate ? 'Update' : 'Create') . ' operation for asesi ID: ' . ($id ?? 'new'));

                session()->setFlashdata('error', $errorMsg);
                return redirect()->back()->withInput()->with('errors', $errors);
            }
        } catch (Exception $e) {
            log_message('error', 'Error saving asesi data: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Prepare and sanitize asesi data from form submission
     * 
     * @return array
     */
    private function prepareAsesiData(): array
    {
        $currentUser = user();

        // Mapping antara nama field di form dan nama field di database
        $fieldMap = [
            // Basic information
            'id_asesi' => 'id_asesi',
            'id_user' => 'id_user',
            'nik' => 'nik',
            'tempat_lahir' => 'tempat_lahir',
            'tanggal_lahir' => 'tanggal_lahir',
            'jenis_kelamin' => 'jenis_kelamin',
            'kebangsaan' => 'kebangsaan',

            // Education
            'pendidikan_terakhir' => 'pendidikan_terakhir',
            'nama_sekolah' => 'nama_sekolah',
            'jurusan' => 'jurusan',

            // Contact
            'telpon_rumah' => 'telpon_rumah',
            'email' => 'email',

            // Location
            'provinsi' => 'provinsi',
            'kabupaten' => 'kabupaten',
            'kecamatan' => 'kecamatan',
            'kelurahan' => 'kelurahan',
            'rt' => 'rt',
            'rw' => 'rw',
            'kode_pos' => 'kode_pos',

            // Employment
            'pekerjaan' => 'pekerjaan',
            'nama_lembaga' => 'nama_lembaga',
            'jabatan' => 'jabatan',
            'alamat_perusahaan' => 'alamat_perusahaan',
            'email_perusahaan' => 'email_perusahaan',
            'no_telp_perusahaan' => 'no_telp_perusahaan',
        ];

        $data = [];

        // Get data based on field mapping
        foreach ($fieldMap as $formField => $dbField) {
            $data[$dbField] = $this->request->getPost($formField);
        }

        // Add special fields handling
        $data['id_user'] = $data['id_user'] ?? $this->userId;

        // Update user's no_telp if provided in the form
        $no_telp = $this->request->getPost('no_hp');
        if (!empty($no_telp) && $currentUser) {
            $this->usermodel->update($currentUser->id, ['no_hp' => $no_telp]);
            log_message('info', 'Updated user phone number: ' . $no_telp);
        }

        // Verifikasi field-field penting
        $requiredFields = [
            'id_user',
            'nik',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'pendidikan_terakhir',
            'nama_sekolah',
            'jurusan',
            'kebangsaan',
            'email',
            'provinsi',
            'kabupaten',
            'kecamatan',
            'kelurahan'
        ];

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            log_message('warning', 'Missing required fields: ' . implode(', ', $missingFields));
        }

        // Log data setelah diproses
        log_message('debug', 'Prepared asesi data: ' . json_encode($data));

        return $data;
    }

    /**
     * Update basic user information without detailed asesi data
     */
    public function updateUserInfo()
    {
        try {
            $userData = [
                'no_hp' => $this->request->getVar('no_hp'),
            ];

            // Enhanced validation for user data
            $rules = [
                'no_hp' => 'required|max_length[20]|regex_match[/^(\+62|62|0)[0-9]{9,12}$/]'
            ];

            $customErrors = [
                'no_hp' => [
                    'regex_match' => 'Format nomor telepon tidak valid. Gunakan format: 08123456789'
                ]
            ];

            if (!$this->validate($rules, $customErrors)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            log_message('debug', 'Updating user info: ' . json_encode($userData));

            // Update user data
            if ($this->usermodel->update($this->userId, $userData)) {
                session()->setFlashdata('pesan', 'Informasi akun berhasil diperbarui!');
            } else {
                session()->setFlashdata('error', 'Gagal memperbarui informasi akun.');
            }

            return redirect()->to('asesi/profile');
        } catch (Exception $e) {
            log_message('error', 'Error updating user info: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui informasi akun');
        }
    }

    /**
     * Update user's signature
     */
    public function updateSignature()
    {
        try {
            $signatureData = $this->request->getVar('tanda_tangan');
            $signatureFile = $this->request->getFile('tanda_tangan');

            if (empty($signatureData) && (!$signatureFile || !$signatureFile->isValid())) {
                return redirect()->back()->with('error', 'Tidak ada tanda tangan yang dikirimkan');
            }

            $signatureDir = WRITEPATH . 'uploads/user/signatures/';
            if (!is_dir($signatureDir)) {
                mkdir($signatureDir, 0755, true);
            }

            $user = $this->usermodel->find($this->userId);
            $newSignatureName = null;

            // Handle signature from canvas
            if (!empty($signatureData) && strpos($signatureData, 'data:image') === 0) {
                list(, $encodedData) = explode(',', $signatureData);
                $decodedData = base64_decode($encodedData);
                $newSignatureName = uniqid('sig_') . '.png';

                if (!file_put_contents($signatureDir . $newSignatureName, $decodedData)) {
                    return redirect()->back()->with('error', 'Gagal menyimpan tanda tangan');
                }
            }
            // Handle uploaded signature file
            else if ($signatureFile && $signatureFile->isValid()) {
                $newSignatureName = $signatureFile->getRandomName();

                if (!$signatureFile->move($signatureDir, $newSignatureName)) {
                    return redirect()->back()->with('error', 'Gagal mengupload tanda tangan');
                }
            }

            // Remove old signature if exists
            if (!empty($user['tanda_tangan'])) {
                $oldSignaturePath = $signatureDir . $user['tanda_tangan'];
                if (file_exists($oldSignaturePath)) {
                    unlink($oldSignaturePath);
                }
            }

            // Update user with new signature
            $this->usermodel->update($this->userId, ['tanda_tangan' => $newSignatureName]);

            return redirect()->to('asesi/profile')->with('success', 'Tanda tangan berhasil diperbarui');
        } catch (Exception $e) {
            log_message('error', 'Error updating signature: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui tanda tangan: ' . $e->getMessage());
        }
    }
}
