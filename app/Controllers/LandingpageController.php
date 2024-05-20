<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;


use Config\Recaptcha as RecaptchaConfig;
use PHPDevsr\Recaptcha\Recaptcha;

use Ramsey\Uuid\Uuid;


class LandingpageController extends BaseController
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
            // 'listSkema' => $this->skema->AllSkema(),
            'provinsi' => $this->dependent->AllProvinsi(),
            'listSkema' => $this->skema->AllSkema(),
            'listSettanggal' => $this->settanggal->findAll(),
            'listTUK' => $this->tuk->findAll(),
            'siteTitle' => 'LSP - P1 SMKN 2 KUNINGAN',
            'siteSubtitle' => 'Lembaga sertifikasi profesi adalah lembaga pendukung BNSP yang bertanggung jawab melaksanakan sertifikasi kompetensi profesi.',
        ];

        return view('landingpage/landingpage', $data);
    }

    public function skema()
    {
        $data = [
            'siteTitle' => 'Skema Sertifikasi',
            'listSkema' => $this->skema->AllSkema()
        ];

        return view('landingpage/skema', $data);
    }

    public function ujikom()
    {

        $recaptcha = service('recaptcha');

        $data = [
            'siteTitle' => 'Pendaftaran Uji Kompetensi',
            'siteSubtitle' => 'Pada bagian ini, masukan data pribadi, data pendidikan formal, data pekerjaan Anda pada saat ini, serta dokumen pendukung.',
            'provinsi' => $this->dependent->AllProvinsi(),
            'listSkema' => $this->skema->AllSkema(),
            'listSettanggal' => $this->settanggal->findAll(),
            'listTUK' => $this->tuk->findAll(),
            'scriptTag' => $recaptcha->getScriptTag(),
            'widgetTag' => $recaptcha->getWidget(),
        ];

        return view('landingpage/ujikom', $data);
        // dd($data);
    }

    public function asesmen($id_apl1)
    {
        $dataAPL1 = $this->apl1->getAPL1byid($id_apl1);
        $data = [
            'siteTitle' => "Asesmen Mandiri",
            'siteSubtitle' => 'Pada bagian ini, masukan data pribadi, data pendidikan formal, data pekerjaan Anda pada saat ini, serta dokumen pendukung.',
            'dataAPL1' => $dataAPL1,
            'listUnit' => $this->unit->getUnit($dataAPL1['id_skema']),
            'listElemen' => $this->elemen->AllElemen(),
            'listSubelemen' => $this->subelemen->getbySkema($dataAPL1['id_skema']),

        ];

        return view('landingpage/asesmen-mandiri', $data);
        // dd($data);
    }

    public function store_pengajuan()
    {

        $captcha = $this->request->getPost('g-recaptcha-response');
        $response = $this->recaptcha->verifyResponse($captcha);

        if (isset($response['success']) and $response['success'] === true) {
            $rules = [
                'skema_sertifikasi' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom Skema Sertifikasi harus diisi.',
                        'is_unique' => 'Nama sudah terdaftar.'
                    ],
                ],
                'nama_siswa' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom nama_siswa harus diisi.',
                        'is_unique' => 'Nama sudah terdaftar.'
                    ],
                ],
                'nik' => [
                    'rules' => 'required|numeric|is_unique[apl1.ktp]|max_length[16]',
                    'errors' => [
                        'required' => 'Kolom nomor KTP/NIK/Paspor harus diisi.',
                        'numeric' => 'Nomor KTP/NIK/Paspor harus berupa angka.',
                        'is_unique' => 'Nomor KTP/NIK/Paspor sudah digunakan, silakan gunakan nomor KTP/NIK/Paspor lain.',
                        'max_length' => 'Nomor KTP/NIK/Paspor tidak boleh lebih dari 16 digit.'
                    ],
                ],
                'tempat_lahir' => [
                    'rules' => 'required|max_length[255]',
                    'errors' => [
                        'required' => 'Kolom tempat lahir harus diisi.',
                        'max_length' => 'Tempat lahir tidak boleh melebihi :max karakter.',
                    ],
                ],
                'tanggal_lahir' => [
                    'rules' => 'required|date',
                    'errors' => [
                        'required' => 'Kolom tanggal lahir harus diisi.',
                        'date' => 'Format tanggal lahir tidak valid.',
                    ],
                ],
                'jenis_kelamin' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom jenis kelamin harus dipilih.',
                    ],
                ],
                'pendidikan_terakhir' => [
                    'label' => 'Pendidikan Terakhir',
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom {{field}} harus dipilih.',
                    ],
                ],
                'nama_sekolah' => [
                    'label' => 'Nama Sekolah',
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom {{field}} harus dipilih.',
                    ],
                ],
                'jurusan' => [
                    'rules' => 'required|max_length[255]',
                    'errors' => [
                        'required' => 'Kolom jurusan harus diisi.',
                        'max_length' => 'Panjang teks jurusan tidak boleh melebihi :max karakter.',
                    ],
                ],
                'kebangsaan' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom kebangsaan harus dipilih.',
                    ],
                ],
                'provinsi' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom provinsi harus diisi.',
                    ],
                ],

                'kabupaten' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom kabupaten harus diisi.',
                    ],
                ],

                'kecamatan' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom kecamatan harus diisi.',
                    ],
                ],

                'kelurahan' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom kelurahan harus diisi.',
                    ],
                ],

                'rt' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom RT harus diisi.',
                    ],
                ],

                'rw' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom RW harus diisi.',
                    ],
                ],

                'kode_pos' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom kode pos harus diisi.',
                    ],
                ],
                'telpon_rumah' => [
                    'rules' => 'permit_empty|numeric',
                    'errors' => [
                        'numeric' => 'Kolom telepon rumah harus berupa angka.',
                    ],
                ],
                'no_hp' => [
                    'rules' => 'required|numeric',
                    'errors' => [
                        'required' => 'Kolom nomor HP harus diisi.',
                        'numeric' => 'Kolom nomor HP harus berupa angka.',
                    ],
                ],
                'email'    =>  [
                    'label' => "Email",
                    'rules' =>  'required|valid_email|is_unique[apl1.email]',
                    'errors' => [
                        'required' => 'Kolom {field} harus diisi.',
                        'valid_email' => 'Format {field} harus valid',
                        'is_unique' => 'Kolom {field} sudah terdaftar. Silakan pilih username lain.',
                    ],
                ],
                'pekerjaan' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom pekerjaan harus diisi.',
                    ],
                ],
                'pas_foto' => [
                    'label' => 'Pas Foto',
                    'rules' => 'uploaded[pas_foto]|max_size[pas_foto,2048]|mime_in[pas_foto,image/jpg,image/jpeg,image/png]',
                    'errors' => [
                        'uploaded' => 'Harus upload {field} *',
                        'max_size' => 'File maksimal 2MB *',
                        'mime_in' => 'File harus berupa gambar / foto'
                    ],
                ],
                'file_ktp' => [
                    'label' => 'KTP/KK/Paspor',
                    'rules' => 'uploaded[file_ktp]|max_size[file_ktp,2048]|mime_in[file_ktp,image/jpg,image/jpeg,image/png]',
                    'errors' => [
                        'uploaded' => 'Harus upload {field} *',
                        'max_size' => 'File maksimal 2MB *',
                        'mime_in' => 'File harus berupa gambar / foto'
                    ],
                ],
                'bukti_pendidikan' => [
                    'label' => 'Bukti Pendidikan',
                    'rules' => 'uploaded[bukti_pendidikan]|max_size[bukti_pendidikan,2048]|mime_in[bukti_pendidikan,image/jpg,image/jpeg,image/png]',
                    'errors' => [
                        'uploaded' => 'Harus upload {field} *',
                        'max_size' => 'File maksimal 2MB *',
                        'mime_in' => 'File harus berupa gambar / foto'
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
                        'max_size' => 'File maksimal 2MB *',
                        'mime_in' => 'File harus berupa gambar / foto'
                    ],
                ],
                'file_ktp' => [
                    'label' => 'KTP/KK/Paspor',
                    'rules' => 'uploaded[file_ktp]|max_size[file_ktp,2048]|mime_in[file_ktp,image/jpg,image/jpeg,image/png]',
                    'errors' => [
                        'uploaded' => 'Harus upload {field} *',
                        'max_size' => 'File maksimal 2MB *',
                        'mime_in' => 'File harus berupa gambar / foto'
                    ],
                ],
                'bukti_pendidikan' => [
                    'label' => 'Bukti Pendidikan',
                    'rules' => 'uploaded[bukti_pendidikan]|max_size[bukti_pendidikan,2048]|mime_in[bukti_pendidikan,image/jpg,image/jpeg,image/png]',
                    'errors' => [
                        'uploaded' => 'Harus upload {field} *',
                        'max_size' => 'File maksimal 2MB *',
                        'mime_in' => 'File harus berupa gambar / foto'
                    ],
                ],
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $pas_foto = $this->request->getFile('pas_foto');
            $nama_foto = $pas_foto->getRandomName();
            $pas_foto->move('upload/pas foto', $nama_foto);

            $ktp = $this->request->getFile('file_ktp');
            $nama_ktp = $ktp->getRandomName();
            $ktp->move('upload/ktp', $nama_ktp);

            $bukti_pendidikan = $this->request->getFile('bukti_pendidikan');
            $nama_bukti_pendidikan = $bukti_pendidikan->getRandomName();
            $bukti_pendidikan->move('upload/bukti pendidikan', $nama_bukti_pendidikan);

            $id = Uuid::uuid4()->toString();

            $data = [
                'id_apl1'               => $id,
                'nama_siswa'            => $this->request->getVar('nama_siswa'),
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
                'email'                 => $this->request->getVar('email'),
                'pekerjaan'             => $this->request->getVar('pekerjaan'),
                'nama_lembaga'          => $this->request->getVar('nama_lembaga'),
                'alamat_perusahaan'     => $this->request->getVar('alamat_perusahaan'),
                'jabatan'               => $this->request->getVar('jabatan'),
                'email_perusahaan'      => $this->request->getVar('email_perusahaan'),
                'no_telp_perusahaan'    => $this->request->getVar('no_telp_perusahaan'),
                'id_skema'              => $this->request->getVar('skema_sertifikasi'),
                'pas_foto'              => $nama_foto,
                'ktp'                   => $nama_ktp,
                'bukti_pendidikan'      => $nama_bukti_pendidikan,
                'validasi_apl1'         => "N",

            ];
        } else {
            session()->setFlashdata('warning', 'Periksa kembali, Captcha.');
            return redirect()->back()->withInput();
        }

        $this->apl1->insert($data);

        $to = $this->request->getVar('email');
        $subject = 'Pendaftaran Uji Kompetensi Keahlian';

        // HTML message
        $message = view('email/email_message', [
            'name' => $this->request->getPost('nama_siswa'),
        ]);

        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom('haikal1080p@gmail.com', 'LSP SMK NEGERI 2 KUNINGAN');

        $email->setSubject($subject);
        $email->setMessage($message);

        // Set mail type to HTML
        $email->setMailType('html');

        if ($email->send()) {
            echo 'Email successfully sent';
        } else {
            $data = $email->printDebugger(['headers']);
            print_r($data);
        }

        // Success!
        session()->setFlashdata('pesan', 'Pengajuan Uji Kompetensi berhasil terkirim, Silahkan Cek Email untuk info lebih lanjut!');
        return redirect()->to('/landingpage');
    }

    public function store_asesmen()
    {
        $dataAPL1 = $this->apl1->getAPL1byid($this->request->getPost('id'));
        $listSubelemen = $this->subelemen->getbySkema($dataAPL1['id_skema']);

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

            if (!$this->validate($rules)) {
                session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Prepare data to insert
            $insertData[] = [
                'id_apl1' => $this->request->getPost('id_apl1_' . $subelemen['id_subelemen']),
                'tk' => $this->request->getPost('bk_' . $subelemen['id_subelemen']),
                'id_skema' => $this->request->getPost('id_skema_' . $subelemen['id_subelemen']),
                'id_unit' => $this->request->getPost('id_unit_' . $subelemen['id_subelemen']),
                'id_elemen' => $this->request->getPost('id_elemen_' . $subelemen['id_subelemen']),
                'id_subelemen' => $this->request->getPost('id_subelemen_' . $subelemen['id_subelemen']),
                'id_user' => $this->request->getPost('id_user_' . $subelemen['id_subelemen']),
                'bukti_pendukung' => $this->request->getPost('bukti_pendukung_' . $subelemen['id_subelemen']),
            ];
        }

        // Insert data in batch
        if (!empty($insertData)) {
            $this->apl2->insertBatch($insertData);
        }

        // dd($insertData);

        session()->setFlashdata('pesan', 'Subelemen berhasil ditambahkan!');
        return redirect()->to('/landingpage');
    }




    // public function store_asesmen()
    // {

    //     $dataAPL1 = $this->apl1->getAllAPL1(user()->id);
    //     $listSubelemen = $this->subelemen->getbySkema($dataAPL1['id_skema']);

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


    //         $this->apl2->save($data);
    //     }



    //     session()->setFlashdata('pesan', 'Subelemen berhasil ditambahkan!');
    //     return redirect()->to('/asesmen-mandiri');
    // }
}
