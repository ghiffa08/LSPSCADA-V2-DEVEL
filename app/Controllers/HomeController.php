<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;


use Config\Recaptcha as RecaptchaConfig;
use PHPDevsr\Recaptcha\Recaptcha;

use Ramsey\Uuid\Uuid;


class HomeController extends BaseController
{

    protected RecaptchaConfig $config;

    protected Recaptcha $recaptcha;


    public function __construct()
    {
        $this->config = new RecaptchaConfig();
        $this->recaptcha = new Recaptcha($this->config);
    }


    public function index()
    {

        $data = [
            'provinsi' => $this->dynamicDependentModel->AllProvinsi(),
            'listSkema' => $this->skemaModel->getActiveSchemes(),
            'listSettanggal' => $this->settanggalModel->findAll(),
            'listTUK' => $this->tukModel->findAll(),
            'siteTitle' => 'LSP - P1 SMKN 2 KUNINGAN',
            'siteSubtitle' => 'Lembaga sertifikasi profesi adalah lembaga pendukung BNSP yang bertanggung jawab melaksanakan sertifikasi kompetensi profesi.',
        ];

        return view('asesi/home', $data);
    }

    public function skema()
    {
        $data = [
            'siteTitle' => 'Skema Sertifikasi',
            'listSkema' => $this->skemaModel->AllSkema()
        ];

        return view('asesi/skema', $data);
    }

    public function ujikom()
    {

        $recaptcha = service('recaptcha');

        $data = [
            'siteTitle' => 'Pendaftaran Uji Kompetensi',
            'siteSubtitle' => 'Pada bagian ini, masukan data pribadi, data pendidikan formal, data pekerjaan Anda pada saat ini, serta dokumen pendukung.',
            'provinsi' => $this->dynamicDependentModel->AllProvinsi(),
            'listSkema' => $this->skemaModel->AllSkema(),
            'listAsesmen' => $this->asesmenModel->getAllAsesmen(),
            'scriptTag' => $recaptcha->getScriptTag(),
            'widgetTag' => $recaptcha->getWidget(),
        ];

        return view('asesi/ujikom', $data);
        // dd($data);
    }

    public function asesmen($id_apl1)
    {
        $dataAPL1 = $this->apl1Model->getAPL1($id_apl1);
        $dataAPL2 = $this->apl2Model->getbyId($dataAPL1['id_apl1']);
        $data = [
            'siteTitle' => 'Asesmen Mandiri - ' . $dataAPL1['nama_siswa'] . '',
            'siteSubtitle' => 'Pada bagian ini, masukan data pribadi, data pendidikan formal, data pekerjaan Anda pada saat ini, serta dokumen pendukung.',
            'dataAPL1' => $dataAPL1,
            'listUnit' => $this->unitModel->getUnit($dataAPL1['skema_id']),
            'listElemen' => $this->elemenModel->AllElemen(),
            'listSubelemen' => $this->kukModel->getbySkema($dataAPL1['skema_id']),
            'dataAPL2' => $dataAPL2,
        ];


        // dd($data);
        return view('asesi/asesmen-mandiri', $data);
    }

    public function store_pengajuan()
    {

        $captcha = $this->request->getPost('g-recaptcha-response');
        $response = $this->recaptcha->verifyResponse($captcha);

        if (isset($response['success']) and $response['success'] !== true) {
            session()->setFlashdata('warning', 'Silahkan Click "Im not a robot"');
            return redirect()->back()->withInput();
        }

        $rules = [
            'skema_sertifikasi' => [
                'label' => 'Skema Sertifikasi',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'nama_siswa' => [
                'label' => 'Nama Siswa',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
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
                'rules' => 'required|date',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'date' => 'Format {field} tidak valid.',
                ],
            ],
            'jenis_kelamin' => [
                'label' => 'Jenis Kelamin',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ],
            'pendidikan_terakhir' => [
                'label' => 'Pendidikan Terakhir',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ],
            'nama_sekolah' => [
                'label' => 'Nama Sekolah',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
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
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ],
            'provinsi' => [
                'label' => 'Provinsi',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'kabupaten' => [
                'label' => 'Kabupaten',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'kecamatan' => [
                'label' => 'Kecamatan',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'kelurahan' => [
                'label' => 'Kelurahan',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'rt' => [
                'label' => 'RT',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'rw' => [
                'label' => 'RW',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'kode_pos' => [
                'label' => 'Kode Pos',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'telpon_rumah' => [
                'label' => 'Telepon Rumah',
                'rules' => 'permit_empty|numeric',
                'errors' => [
                    'numeric' => 'Kolom {field} harus berupa angka.',
                ],
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
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

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
                'label' => 'Pas Foto',
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
                'label' => 'Bukti Pendidikan',
                'rules' => 'uploaded[sertifikat_pkl]|max_size[sertifikat_pkl,2048]|mime_in[sertifikat_pkl,application/pdf,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'Harus upload {field} *',
                    'max_size' => 'File {field} maksimal 2MB *',
                    'mime_in' => 'File {field} harus berupa gambar / foto atau PDF'
                ],
            ],

        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $pas_foto = $this->request->getFile('pas_foto');
        $nama_foto = $pas_foto->getRandomName();
        $pas_foto->move('html/upload/pas foto', $nama_foto);

        $ktp = $this->request->getFile('file_ktp');
        $nama_ktp = $ktp->getRandomName();
        $ktp->move('html/upload/ktp', $nama_ktp);

        $bukti_pendidikan = $this->request->getFile('bukti_pendidikan');
        $nama_bukti_pendidikan = $bukti_pendidikan->getRandomName();
        $bukti_pendidikan->move('html/upload/bukti pendidikan', $nama_bukti_pendidikan);

        $tanda_tangan_asesi = $this->request->getFile('tanda_tangan_asesi');
        $nama_tanda_tangan_asesi = $tanda_tangan_asesi->getRandomName();
        $tanda_tangan_asesi->move('html/upload/tanda tangan', $nama_tanda_tangan_asesi);

        $raport = $this->request->getFile('raport');
        $nama_raport = $raport->getRandomName();
        $raport->move('html/upload/raport', $nama_raport);

        $sertifikat_pkl = $this->request->getFile('sertifikat_pkl');
        $nama_sertifikat_pkl = $sertifikat_pkl->getRandomName();
        $sertifikat_pkl->move('html/upload/sertifikat pkl', $nama_sertifikat_pkl);

        // $id = "FR-APL-01-" . Uuid::uuid4()->toString();

        $id = "FR-APL-01-" . substr(Uuid::uuid4()->toString(), 0, 8);

        $emailAsesi =  $this->request->getVar('email');

        $namaAsesi =  $this->request->getVar('nama_siswa');

        $data = [
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
            'pas_foto'              => $nama_foto,
            'ktp'                   => $nama_ktp,
            'bukti_pendidikan'      => $nama_bukti_pendidikan,
            'tanda_tangan_asesi'    => $nama_tanda_tangan_asesi,
            'raport'                => $nama_raport,
            'sertifikat_pkl'        => $nama_sertifikat_pkl,
            'validasi_apl1'         => "pending",

        ];

        $this->apl1Model->insert($data);

        $to = $emailAsesi;
        $subject = 'Pendaftaran Uji Kompetensi Keahlian';

        // HTML message
        $message = view('email/email_message', [
            'name' => $namaAsesi,
            'id' => $id
        ]);

        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');

        $email->setSubject($subject);
        $email->setMessage($message);

        // Set mail type to HTML
        $email->setMailType('html');

        if (!$email->send()) {
            $data = $email->printDebugger(['headers']);
            print_r($data);
        }

        // Success!
        session()->setFlashdata('pesan', 'Pengajuan Uji Kompetensi berhasil terkirim, Silahkan Cek Email untuk info lebih lanjut!');
        return redirect()->to('/asesi');
    }

    public function store_asesmen()
    {
        $dataAPL1 = $this->apl1Model->getAPL1($this->request->getPost('id'));
        $listSubelemen = $this->kukModel->getbySkema($dataAPL1['skema_id']);

        $rules = [];
        $insertData = [];

        foreach ($listSubelemen as $subelemen) {
            $rules['bk_' . $subelemen['id_subelemen']] = [
                'label' => $subelemen['pertanyaan'],
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ];

            if ($this->request->getPost('bk_' . $subelemen['id_subelemen']) == "K") {
                $rules['bukti_pendukung_' . $subelemen['id_subelemen']] = [
                    'label' => 'Bukti Pendukung',
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom {field} harus dipilih.',
                    ],
                ];
            }
        }

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $id_apl2 = "FR-APL-02-" . substr(Uuid::uuid4()->toString(), 0, 8);
        $kode_jawaban_apl2 = "ANS-APL-02-" . substr(Uuid::uuid4()->toString(), 0, 6);

        $insertAPL2 = [
            'id_apl2' => $id_apl2,
            'id_apl1' => $dataAPL1['id_apl1'],
            'kode_jawaban_apl2' => $kode_jawaban_apl2,
            'validasi_apl2' => 'pending'
        ];

        $this->apl2Model->insert($insertAPL2);

        foreach ($listSubelemen as $subelemen) {

            // Prepare data to insert
            $insertData[] = [
                'kode_jawaban_apl2' => $kode_jawaban_apl2,
                'id_apl2' => $id_apl2,
                'tk' => $this->request->getPost('bk_' . $subelemen['id_subelemen']),
                'id_skema' => $this->request->getPost('id_skema_' . $subelemen['id_subelemen']),
                'id_unit' => $this->request->getPost('id_unit_' . $subelemen['id_subelemen']),
                'id_elemen' => $this->request->getPost('id_elemen_' . $subelemen['id_subelemen']),
                'id_subelemen' => $this->request->getPost('id_subelemen_' . $subelemen['id_subelemen']),
                'bukti_pendukung' => $this->request->getPost('bukti_pendukung_' . $subelemen['id_subelemen']),
            ];
        }

        if (!empty($insertData)) {

            if ($this->apl2JawabanModel->insertBatch($insertData)) {

                $to = $dataAPL1['email'];
                $subject = 'Asesmen Mandiri';

                $id_apl1 = $dataAPL1['id_apl1'];
                $nama_asesi = $dataAPL1['nama_siswa'];
                $skema = $dataAPL1['nama_skema'];

                $message = view('email/email_send_apl2', [
                    'name' => $nama_asesi,
                    'id' => $id_apl1,
                    'id_asesmen' => $id_apl2,
                    'skema' => $skema
                ]);


                $email = \Config\Services::email();
                $email->setTo($to);
                $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');

                $email->setSubject($subject);
                $email->setMessage($message);

                // Set mail type to HTML
                $email->setMailType('html');

                if (!$email->send()) {
                    $data = $email->printDebugger(['headers']);
                    print_r($data);
                }
            }
        }


        // dd($insertData);

        session()->setFlashdata('pesan', 'Subelemen berhasil ditambahkan!');
        return redirect()->to('/asesmen-mandiri/' . $id_apl1);
    }

    public function send_feedback()
    {

        $rules = [
            'fullname' => [
                'label' => 'Nama Lengkap',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'phone' => [
                'label' => 'Nomor Handphone',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'message' => [
                'label' => 'Umpan Balik',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],

        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama' => $this->request->getVar('fullname'),
            'email' => $this->request->getVar('email'),
            'no_hp' => $this->request->getVar('phone'),
            'feedback' => $this->request->getVar('message'),
        ];

        $this->feedbackModel->save($data);
        session()->setFlashdata('pesan', 'Feedback Berhasil Terkirim!');
        return redirect()->to('/');
    }




    // public function store_asesmen()
    // {

    //     $dataAPL1 = $this->apl1Model->getAllAPL1(user()->id);
    //     $listSubelemen = $this->kukModel->getbySkema($dataAPL1['id_skema']);

    //     $rules = [];
    //     foreach ($listSubelemen as $subelemen) {
    //         $rules['bk_' . $subelemen['id_subelemen']] = [
    //             'label' => $subelemen['pertanyaan'],
    //             'rules' => 'required',
    //             'errors' => [
    //                 'required' => 'Kolom {field} harus dipilih.',
    //             ],
    //         ];
    //         if ($this->request->getPost('bk_' . $subelemen['id_subelemen']) == "K") {
    //             $rules['bukti_pendukung_' . $subelemen['id_subelemen']] = [
    //                 'label' => 'Bukti Pendukung',
    //                 'rules' => 'uploaded[bukti_pendukung_' . $subelemen['id_subelemen'] . ']',
    //                 'errors' => [
    //                     'uploaded' => 'Harus upload {field} *',
    //                 ],
    //             ];
    //         }
    //     }

    //     if (!$this->validate($rules)) {
    //         return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    //     }

    //     $data = [];

    //     foreach ($listSubelemen as $subelemen) {

    //         $data['tk'] = [
    //             'tk' => $this->request->getPost('bk_' . $subelemen['id_subelemen']),
    //         ];
    //         $data['id_skema'] = [
    //             'id_skema' => $this->request->getPost('id_skema_' . $subelemen['id_subelemen']),
    //         ];
    //         $data['id_unit'] = [
    //             'id_unit' => $this->request->getPost('id_unit_' . $subelemen['id_subelemen']),
    //         ];
    //         $data['id_elemen'] = [
    //             'id_elemen' => $this->request->getPost('id_elemen_' . $subelemen['id_subelemen']),
    //         ];
    //         $data['id_subelemen'] = [
    //             'id_subelemen' => $this->request->getPost('id_subelemen_' . $subelemen['id_subelemen']),
    //         ];
    //         $data['id_user'] = [
    //             'id_user' => $this->request->getPost('id_user_' . $subelemen['id_subelemen']),
    //         ];


    //         $this->apl2Model->save($data);
    //     }



    //     session()->setFlashdata('pesan', 'Subelemen berhasil ditambahkan!');
    //     return redirect()->to('/asesmen-mandiri');
    // }
}
