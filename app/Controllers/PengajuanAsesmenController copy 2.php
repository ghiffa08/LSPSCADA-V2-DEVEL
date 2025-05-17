<?php

namespace App\Controllers;

use Config\Database;
use Config\Services;
use Ramsey\Uuid\Uuid;
use CodeIgniter\I18n\Time;
use App\Services\EmailService;
use App\Controllers\BaseController;
use App\Services\FileUploadService;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\FileUploadInterface;
use CodeIgniter\RESTful\ResourceController;

class PengajuanAsesmenController extends BaseController
{
    /**
     * @var File Upload
     */
    protected FileUploadInterface $fileUploadService;


    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->fileUploadService = Services::fileUpload();
    }

    /**
     * Display the assessment application form
     *
     * @return string
     */
    public function index()
    {
        $data = [
            'siteTitle' => 'Pendaftaran Uji Kompetensi',
            'siteSubtitle' => 'Pada bagian ini, masukan data pribadi, data pendidikan formal, data pekerjaan Anda pada saat ini, serta dokumen pendukung.',
            'provinsi' => $this->dependent->AllProvinsi(),
            'listSkema' => $this->skema->AllSkema(),
            'listAsesmen' => $this->asesmen->getAllAsesmen(),
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
            $db = \Config\Database::connect();
            $db->transBegin();

            // Process uploaded files
            $uploadedFiles = $this->processFileUploads();

            if (isset($uploadedFiles['error'])) {
                throw new \Exception($uploadedFiles['error']);
            }

            // Generate unique IDs
            $idAsesi = generate_application_id('ASI', 'asesi', 'id_asesi');
            $idApl1 = generate_application_id('APL01', 'pengajuan_asesmen', 'id_apl1');

            $emailAsesi = $this->request->getVar('email');
            $namaAsesi = $this->request->getVar('nama_siswa');
            $userId = $this->request->getVar('user_id');

            // Prepare data for insertion into three tables
            // 1. Prepare and insert Asesi data
            $asesiData = $this->prepareAsesiData($idAsesi, $userId);
            $this->asesiModel->insert($asesiData);

            // 2. Prepare and insert Pengajuan Asesmen data
            $pengajuanData = $this->preparePengajuanData($idApl1, $idAsesi);
            $this->pengajuanAsesmenModel->insert($pengajuanData);

            // 3. Dokumen APL1 data
            $dokumenData = $this->prepareDokumenData($idApl1, $uploadedFiles);
            $this->dokumenApl1Model->insert($dokumenData);

            // Commit transaction
            $db->transCommit();

            // Send email notification
            $this->queueEmailNotification($emailAsesi, $namaAsesi, $idApl1);

            // Success response
            session()->setFlashdata('pesan', 'Pengajuan Uji Kompetensi berhasil terkirim, Silahkan Cek Email untuk info lebih lanjut!');
            return redirect()->to('/dashboard');
        } catch (\Exception $e) {
            // Rollback transaction on error
            $db->transRollback();
            log_message('error', 'Error during application submission: ' . $e->getMessage());

            session()->setFlashdata('error', 'Terjadi kesalahan saat memproses pengajuan: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

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
     * Prepare asesi data for database insertion
     *
     * @param string $idAsesi
     * @param int $userId
     * @return array
     */
    private function prepareAsesiData(string $idAsesi, int $userId): array
    {
        return [
            'id_asesi'             => $idAsesi,
            'user_id'              => $userId,
            'nik'                  => $this->request->getVar('nik'),
            'nama'                 => $this->request->getVar('nama_siswa'),
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
            'no_hp'                => $this->request->getVar('no_hp'),
            'email'                => $this->request->getVar('email'),
            'pekerjaan'            => $this->request->getVar('pekerjaan'),
            'nama_lembaga'         => $this->request->getVar('nama_lembaga'),
            'jabatan'              => $this->request->getVar('jabatan'),
            'alamat_perusahaan'    => $this->request->getVar('alamat_perusahaan'),
            'email_perusahaan'     => $this->request->getVar('email_perusahaan'),
            'no_telp_perusahaan'   => $this->request->getVar('no_telp_perusahaan'),
            'created_at'           => Time::now(),
        ];
    }

    /**
     * Prepare pengajuan asesmen data
     * 
     * @param string $idApl1
     * @param string $idAsesi
     * @param array $uploadedFiles
     * @return array
     */
    private function preparePengajuanData(string $idApl1, string $idAsesi): array
    {
        return [
            'id_apl1'               => $idApl1,
            'id_asesi'              => $idAsesi,
            'id_asesmen'            => $this->request->getVar('id_asesmen'),
            'status'                => 'pending',
            'created_at'            => Time::now(),
        ];
    }

    /**
     * Prepare dokumen data
     * 
     * @param string $idApl1
     * @param array $uploadedFiles
     * @return array
     */
    private function prepareDokumenData(string $idApl1, array $uploadedFiles): array
    {
        return [
            'id_apl1'            => $idApl1,
            'pas_foto'           => $uploadedFiles['pas_foto'],
            'bukti_pendidikan'   => $uploadedFiles['bukti_pendidikan'],
            'ktp'                => $uploadedFiles['ktp'],
            'raport'             => $uploadedFiles['raport'],
            'sertifikat_pkl'     => $uploadedFiles['sertifikat_pkl'],
        ];
    }

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
            'file_ktp' => [
                'label' => 'KTP',
                'rules' => 'uploaded[file_ktp]|max_size[file_ktp,2048]|mime_in[file_ktp,application/pdf,image/jpg,image/jpeg,image/png]',
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
     * Process uploaded files for APL1 document
     * 
     * @return array Processed file information
     */
    private function processFileUploads(): array
    {
        $uploadedFiles = [];

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
            'raport' => [
                'directory' => 'asesi/report',
                'allowed_types' => ['application/pdf'],
                'max_size' => 5120 // 5MB
            ],
            'sertifikat_pkl' => [
                'directory' => 'asesi/certificate',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png', 'application/pdf'],
                'max_size' => 5120 // 5MB
            ],
        ];

        // Collect valid files to upload
        $filesToUpload = [];

        foreach ($fileFields as $field => $config) {
            $file = $this->request->getFile($field);

            // Skip if no file uploaded
            if ($file === null || !$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $filesToUpload[$field] = $file;
        }

        // Process uploads using the service
        if (!empty($filesToUpload)) {
            $results = $this->fileUploadService->uploadMultipleFiles($filesToUpload, $fileFields);

            // Process results
            foreach ($results as $field => $result) {
                if ($result['success']) {
                    $uploadedFiles[$field] = $result['filename'];
                } else {
                    // Log error and handle failure
                    log_message('error', "File upload failed for {$field}: {$result['error']}");

                    // You could throw an exception here or handle the error differently
                    // throw new \RuntimeException("Failed to upload {$field}: {$result['error']}");
                }
            }
        }

        return $uploadedFiles;
    }

    /**
     * Queue email notification to be sent asynchronously
     *
     * @param string $emailAsesi
     * @param string $namaAsesi
     * @param string $id
     * @return void
     */
    private function queueEmailNotification(string $emailAsesi, string $namaAsesi, string $idApl1): void
    {
        // Check if Queue service is available
        if (class_exists('\CodeIgniter\Queue\Queue')) {
            $queue = service('queue');
            $queue->push('sendAsesmenEmail', [
                'to'   => $emailAsesi,
                'name' => $namaAsesi,
                'id'   => $idApl1
            ]);
        } else {
            // Jika Queue tidak tersedia, langsung kirim menggunakan EmailService
            $emailService = new EmailService();
            $emailService->sendEmail(
                $emailAsesi,
                'Pendaftaran Uji Kompetensi Keahlian',
                'email/email_message',
                [
                    'name' => $namaAsesi,
                    'id'   => $idApl1
                ]
            );
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
