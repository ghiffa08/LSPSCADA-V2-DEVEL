<?php

namespace App\Controllers;

use Config\Database;
use Config\Services;
use Ramsey\Uuid\Uuid;
use CodeIgniter\I18n\Time;
use App\Controllers\BaseController;
use App\Services\FileUploadService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Recaptcha as RecaptchaConfig;
use PHPDevsr\Recaptcha\Recaptcha;

class PengajuanAsesmenController extends BaseController
{

    // protected RecaptchaConfig $config;

    // protected Recaptcha $recaptcha;

    /**
     * @var File Upload
     */
    protected $fileUploadService;

    /**
     * Class constructor
     */
    public function __construct()
    {
        // $this->config = new RecaptchaConfig();
        // $this->recaptcha = new Recaptcha($this->config);
        $this->fileUploadService = new FileUploadService();
    }

    /**
     * Display the assessment application form
     *
     * @return string
     */
    public function index()
    {
        // $recaptcha = service('recaptcha');

        $data = [
            'siteTitle' => 'Pendaftaran Uji Kompetensi',
            'siteSubtitle' => 'Pada bagian ini, masukan data pribadi, data pendidikan formal, data pekerjaan Anda pada saat ini, serta dokumen pendukung.',
            'provinsi' => $this->dependent->AllProvinsi(),
            'listSkema' => $this->skema->AllSkema(),
            'listAsesmen' => $this->asesmen->getAllAsesmen(),
            // 'scriptTag' => $recaptcha->getScriptTag(),
            // 'widgetTag' => $recaptcha->getWidget(),
        ];

        return view('asesi/pengajuan-sertifikasi', $data);
    }

    /**
     * Store a new assessment application
     *
     * @return ResponseInterface
     */
    public function store()
    {
        // Check if the request is AJAX
        // if (!$this->request->isAJAX()) {
        //     // Using the Response class to return an error with a 403 status
        //     return $this->response->setStatusCode(403)
        //         ->setJSON(['status' => 'error', 'message' => 'Invalid request method. This action requires an AJAX request.']);
        // }

        // Validate reCAPTCHA
        // if (!$this->validateRecaptcha()) {
        //     return $this->failedResponse('warning', 'Silahkan Click "Im not a robot"');
        // }

        // Validate form input data
        if (!$this->validateFormData()) {
            return $this->failedResponse('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
        }

        // Validate uploaded files
        if (!$this->validateUploadedFiles()) {
            return $this->failedResponse('warning', 'Periksa kembali, terdapat beberapa kesalahan pada file yang diunggah.');
        }

        try {
            // Begin transaction
            $db = Database::connect();
            $db->transBegin();

            // Process uploaded files
            $uploadedFiles = $this->processFileUploads();

            // Generate unique ID for APL1
            $id = generate_application_id('APL01', 'apl1', 'id_apl1');
            $emailAsesi = $this->request->getVar('email');
            $namaAsesi = $this->request->getVar('nama_siswa');

            // Prepare data for insertion
            $data = $this->prepareApplicationData($id, $emailAsesi, $namaAsesi, $uploadedFiles);

            // Insert data
            $this->apl1Model->insert($data);

            // Commit transaction
            $db->transCommit();

            // Send email notification (queued)
            $this->queueEmailNotification($emailAsesi, $namaAsesi, $id);

            // Success response
            session()->setFlashdata('pesan', 'Pengajuan Uji Kompetensi berhasil terkirim, Silahkan Cek Email untuk info lebih lanjut!');
            return redirect()->to('/asesi');
        } catch (\Exception $e) {
            // Rollback transaction on error
            $db->transRollback();
            log_message('error', 'Error during application submission: ' . $e->getMessage());

            session()->setFlashdata('error', 'Terjadi kesalahan saat memproses pengajuan. Silakan coba lagi.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Validate reCAPTCHA response
     *
     * @return bool
     */
    // private function validateRecaptcha(): bool
    // {
    //     $captcha = $this->request->getPost('g-recaptcha-response');
    //     $response = $this->recaptcha->verifyResponse($captcha);


    //     return isset($response['success']) && $response['success'] === true;
    // }

    /**
     * Validate form input data
     *
     * @return bool
     */
    private function validateFormData(): bool
    {
        $rules = [
            'skema_sertifikasi' => [
                'label' => 'Skema Sertifikasi',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus diisi.'],
            ],
            'nama_siswa' => [
                'label' => 'Nama Siswa',
                'rules' => 'required',
                'errors' => ['required' => 'Kolom {field} harus diisi.'],
            ],
            'nik' => [
                'label' => 'Nomor KTP/NIK/Paspor',
                'rules' => 'required|numeric|is_unique[apl1.ktp]|max_length[16]',
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
                    'max_length' => '{field} tidak boleh melebihi :max karakter.',
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
                    'max_length' => 'Panjang teks {field} tidak boleh melebihi :max karakter.',
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
            'no_hp' => [
                'label' => 'Nomor HP',
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'numeric' => 'Kolom {field} harus berupa angka.',
                ],
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|is_unique[apl1.email]',
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

        return $this->validate($rules);
    }

    /**
     * Validate uploaded files
     *
     * @return bool
     */
    private function validateUploadedFiles(): bool
    {
        $rules = [
            'pas_foto' => [
                'label' => 'Pas Foto',
                'rules' => 'uploaded[pas_foto]|max_size[pas_foto,2048]|mime_in[pas_foto,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'Harus upload {field} *',
                    'max_size' => 'File {field} maksimal 2MB *',
                    'mime_in' => 'File {field} harus berupa gambar / foto'
                ],
            ],
            'tanda_tangan_asesi' => [
                'label' => 'Tanda Tangan Asesi',
                'rules' => 'uploaded[tanda_tangan_asesi]|max_size[tanda_tangan_asesi,2048]|mime_in[tanda_tangan_asesi,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'Harus upload {field} *',
                    'max_size' => 'File {field} maksimal 2MB *',
                    'mime_in' => 'File {field} harus berupa gambar / foto'
                ],
            ],
            'file_ktp' => [
                'label' => 'KTP/KK/Paspor',
                'rules' => 'uploaded[file_ktp]|max_size[file_ktp,2048]|mime_in[file_ktp,application/pdf,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'Harus upload {field} *',
                    'max_size' => 'File {field} maksimal 2MB *',
                    'mime_in' => 'File {field} harus berupa gambar / foto atau PDF'
                ],
            ],
            'bukti_pendidikan' => [
                'label' => 'Bukti Pendidikan',
                'rules' => 'uploaded[bukti_pendidikan]|max_size[bukti_pendidikan,2048]|mime_in[bukti_pendidikan,application/pdf,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'Harus upload {field} *',
                    'max_size' => 'File {field} maksimal 2MB *',
                    'mime_in' => 'File {field} harus berupa gambar / foto atau PDF'
                ],
            ],
            'raport' => [
                'label' => 'Raport',
                'rules' => 'uploaded[raport]|max_size[raport,2048]|mime_in[raport,application/pdf]',
                'errors' => [
                    'uploaded' => 'Harus upload {field} *',
                    'max_size' => 'File {field} maksimal 2MB *',
                    'mime_in' => 'File {field} harus berupa file PDF'
                ],
            ],
            'sertifikat_pkl' => [
                'label' => 'Sertifikat PKL',
                'rules' => 'uploaded[sertifikat_pkl]|max_size[sertifikat_pkl,2048]|mime_in[sertifikat_pkl,application/pdf,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'Harus upload {field} *',
                    'max_size' => 'File {field} maksimal 2MB *',
                    'mime_in' => 'File {field} harus berupa gambar / foto atau PDF'
                ],
            ],
        ];

        return $this->validate($rules);
    }

    /**
     * Process file uploads using FileUploadService
     *
     * @return array Upload results
     */
    private function processFileUploads(): array
    {
        $uploadedFiles = [];
        $uploadConfig = [];

        // Define file upload configurations
        $fileFields = [
            'pas_foto' => [
                'directory' => 'asesi/photo',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png'],
                'max_size' => 1024 // 1MB
            ],
            'file_ktp' => [
                'directory' => 'asesi/ktp',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png', 'application/pdf'],
                'max_size' => 2048 // 2MB
            ],
            'bukti_pendidikan' => [
                'directory' => 'asesi/education',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png', 'application/pdf'],
                'max_size' => 5120 // 5MB
            ],
            'tanda_tangan_asesi' => [
                'directory' => 'asesi/signature',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png'],
                'max_size' => 1024 // 1MB
            ],
            'raport' => [
                'directory' => 'asesi/report',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png', 'application/pdf'],
                'max_size' => 5120 // 5MB
            ],
            'sertifikat_pkl' => [
                'directory' => 'asesi/certificate',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png', 'application/pdf'],
                'max_size' => 5120 // 5MB
            ],
        ];

        // Prepare files and configurations
        $filesToUpload = [];

        foreach ($fileFields as $field => $config) {
            $file = $this->request->getFile($field);

            // Skip if no file uploaded
            if (!$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $filesToUpload[$field] = $file;
            $uploadConfig[$field] = $config;
        }

        // Process uploads using the service
        if (!empty($filesToUpload)) {
            $results = $this->fileUploadService->uploadMultipleFiles($filesToUpload, $uploadConfig);

            // Check for errors
            $hasErrors = false;
            $errorMessage = '';

            foreach ($results as $field => $result) {
                if ($result['success']) {
                    $uploadedFiles[$field] = $result['filename'];
                } else {
                    $hasErrors = true;
                    $errorMessage .= "Error uploading {$field}: {$result['error']}. ";
                }
            }

            if ($hasErrors) {
                return ['error' => $errorMessage];
            }
        }

        return $uploadedFiles;
    }

    /**
     * Prepare application data for database insertion
     *
     * @param string $id
     * @param string $emailAsesi
     * @param string $namaAsesi
     * @param array $uploadedFiles
     * @return array
     */
    private function prepareApplicationData(string $id, string $emailAsesi, string $namaAsesi, array $uploadedFiles): array
    {
        return [
            'id_apl1'               => $id,
            'email'                 => $emailAsesi,
            'nama_siswa'            => $namaAsesi,
            'nik'                   => $this->request->getVar('nik'),
            'tempat_lahir'          => $this->request->getVar('tempat_lahir'),
            'tanggal_lahir'         => $this->request->getVar('tanggal_lahir'),
            'jenis_kelamin'         => $this->request->getVar('jenis_kelamin'),
            'pendidikan_terakhir'   => $this->request->getVar('pendidikan_terakhir'),
            'nama_sekolah'          => $this->request->getVar('nama_sekolah'),
            'jurusan'               => $this->request->getVar('jurusan'),
            'kebangsaan'            => $this->request->getVar('kebangsaan'),
            'provinsi'              => $this->request->getVar('provinsi'),
            'kabupaten'             => $this->request->getVar('kabupaten'),
            'kecamatan'             => $this->request->getVar('kecamatan'),
            'kelurahan'             => $this->request->getVar('kelurahan'),
            'rt'                    => $this->request->getVar('rt'),
            'rw'                    => $this->request->getVar('rw'),
            'kode_pos'              => $this->request->getVar('kode_pos'),
            'telpon_rumah'          => $this->request->getVar('telpon_rumah'),
            'no_hp'                 => $this->request->getVar('no_hp'),
            'pekerjaan'             => $this->request->getVar('pekerjaan'),
            'nama_lembaga'          => $this->request->getVar('nama_lembaga'),
            'alamat_perusahaan'     => $this->request->getVar('alamat_perusahaan'),
            'jabatan'               => $this->request->getVar('jabatan'),
            'email_perusahaan'      => $this->request->getVar('email_perusahaan'),
            'no_telp_perusahaan'    => $this->request->getVar('no_telp_perusahaan'),
            'id_asesmen'            => $this->request->getVar('id_asesmen'),
            'pas_foto'              => $uploadedFiles['pas_foto'],
            'ktp'                   => $uploadedFiles['file_ktp'],
            'bukti_pendidikan'      => $uploadedFiles['bukti_pendidikan'],
            'tanda_tangan_asesi'    => $uploadedFiles['tanda_tangan_asesi'],
            'raport'                => $uploadedFiles['raport'],
            'sertifikat_pkl'        => $uploadedFiles['sertifikat_pkl'],
            'validasi_apl1'         => "pending",
            'created_at'            => Time::now(),
        ];
    }

    /**
     * Queue email notification to be sent asynchronously
     *
     * @param string $emailAsesi
     * @param string $namaAsesi
     * @param string $id
     * @return void
     */
    private function queueEmailNotification(string $emailAsesi, string $namaAsesi, string $id): void
    {
        // Check if Queue service is available
        if (class_exists('\CodeIgniter\Queue\Queue')) {
            // Use the Queue service if available
            $queue = service('queue');
            $queue->push('sendAsesmenEmail', [
                'to' => $emailAsesi,
                'name' => $namaAsesi,
                'id' => $id
            ]);
        } else {
            // Fall back to immediate sending if queue isn't available
            $this->sendEmail($emailAsesi, $namaAsesi, $id);
        }
    }

    /**
     * Send notification email
     *
     * @param string $to
     * @param string $name
     * @param string $id
     * @return bool
     */
    public function sendEmail(string $to, string $name, string $id): bool
    {
        $subject = 'Pendaftaran Uji Kompetensi Keahlian';

        // HTML message
        $message = view('email/email_message', [
            'name' => $name,
            'id' => $id
        ]);

        $email = Services::email();
        $email->setTo($to);
        $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');

        $email->setSubject($subject);
        $email->setMessage($message);
        $email->setMailType('html');

        $result = false;
        try {
            $result = $email->send();
            if (!$result) {
                log_message('error', 'Failed to send email: ' . print_r($email->printDebugger(['headers']), true));
            }
        } catch (\Exception $e) {
            log_message('error', 'Email sending exception: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Return failed response with flash data
     *
     * @param string $type
     * @param string $message
     * @return ResponseInterface
     */
    private function failedResponse(string $type, string $message): ResponseInterface
    {
        session()->setFlashdata($type, $message);
        $errors = $this->validator ? $this->validator->getErrors() : [];

        return redirect()->back()->withInput()->with('errors', $errors);
    }
}
