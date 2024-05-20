<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Google\Service\CloudSearch\Id;
use TCPDF;

class APL1Controller extends BaseController
{
    public function index()
    {

        $data = [
            'provinsi' => $this->dependent->AllProvinsi(),
            'listSkema' => $this->skema->AllSkema(),
            'dataAPL1' => $this->apl1->getAllAPL1(user()->id),
            'listSkema' => $this->skema->findAll(),
            'listSkemaSiswa' => $this->skema_siswa->getSkemaSiswa(user()->id),
            'siteTitle' => "Form APL 1"
        ];

        return view('dashboard/apl1', $data);


        // dd($data);
    }

    public function detailAPL1($id_apl1)
    {

        $data = [
            'provinsi' => $this->dependent->AllProvinsi(),
            'listSkema' => $this->skema->findAll(),
            'dataAPL1' => $this->apl1->getAPL1byid($id_apl1),
            'listSkema' => $this->skema->findAll(),
            'siteTitle' => "Validasi FR.APL.01"
        ];

        return view('dashboard/detail-apl1', $data);
        // dd($data['dataAPL1']);
    }

    public function edit($id_siswa)
    {
        $data = [
            'provinsi' => $this->dependent->AllProvinsi(),
            'dataAPL1' => $this->apl1->getAllAPL1($id_siswa),
            'listSkema' => $this->skema->findAll(),
            'siteTitle' => "Form APL 1"
        ];

        return view('dashboard/edit-apl1', $data);
        // dd($data);
    }

    public function store()
    {
        $existingDataCount = $this->apl1->where('id_siswa', $this->request->getVar('id_siswa'))->countAllResults();
        $dataAPL1 = $this->apl1->where('id_siswa', $this->request->getVar('id_siswa'))->findAll();

        $rules = [
            'nama_siswa' => [
                'rules' => 'required' . ($this->request->getVar('nama_siswa') != isset($dataAPL1[0]['nama_siswa']) ? '|is_unique[apl1.nama_siswa]' : ''),
                'errors' => [
                    'required' => 'Kolom nama_siswa harus diisi.',
                    'is_unique' => 'Nama sudah terdaftar.'
                ],
            ],
            'ktp' => [
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

            // 'telpon_kantor' => [
            //     'rules' => 'required|numeric',
            //     'errors' => [
            //         'required' => 'Kolom telepon kantor harus diisi.',
            //         'numeric' => 'Kolom telepon kantor harus berupa angka.',
            //     ],
            // ],

            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Kolom email harus diisi.',
                    'valid_email' => 'Format email tidak valid.',
                ],
            ],
            'pekerjaan' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom pekerjaan harus diisi.',
                ],
            ],
            // tambahkan aturan validasi lainnya sesuai kebutuhan
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        // '' => $this->request->getVar(''),
        $data = [
            'id_siswa' => $this->request->getVar('id_siswa'),
            'nama_siswa' => $this->request->getVar('nama_siswa'),
            'nik' => $this->request->getVar('ktp'),
            'tempat_lahir' => $this->request->getVar('tempat_lahir'),
            'tanggal_lahir' => $this->request->getVar('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getVar('jenis_kelamin'),
            'pendidikan_terakhir' => $this->request->getVar('pendidikan_terakhir'),
            'nama_sekolah' => $this->request->getVar('nama_sekolah'),
            'jurusan' => $this->request->getVar('jurusan'),
            'kebangsaan' => $this->request->getVar('kebangsaan'),
            'provinsi' => $this->request->getVar('provinsi'),
            'kabupaten' => $this->request->getVar('kabupaten'),
            'kecamatan' => $this->request->getVar('kecamatan'),
            'kelurahan' => $this->request->getVar('kelurahan'),
            'rt' => $this->request->getVar('rt'),
            'rw' => $this->request->getVar('rw'),
            'kode_pos' => $this->request->getVar('kode_pos'),
            'telpon_rumah' => $this->request->getVar('telpon_rumah'),
            'no_hp' => $this->request->getVar('no_hp'),
            // 'telpon_kantor' => $this->request->getVar('telpon_kantor'),
            'email' => $this->request->getVar('email'),
            // 'email2' => $this->request->getVar('email2'),
            // 'pendidikan' => $this->request->getVar('pendidikan'),
            'pekerjaan' => $this->request->getVar('pekerjaan'),
            'nama_lembaga' => $this->request->getVar('nama_lembaga'),
            'alamat_perusahaan' => $this->request->getVar('alamat_perusahaan'),
            'jabatan' => $this->request->getVar('jabatan'),
            'email_perusahaan' => $this->request->getVar('email_perusahaan'),
            'no_telp_perusahaan' => $this->request->getVar('no_telp_perusahaan'),
            'id_skema' => $this->request->getVar('id_skema'),
            // 'tujuan' => $this->request->getVar('tujuan'),
            'validasi_apl1' => "N",

        ];

        if ($existingDataCount > 0) {
            // Jika data dengan id_siswa yang sama sudah ada, lakukan pembaruan data
            $this->apl1->update($dataAPL1[0]['id_apl1'], $data);
            session()->setFlashdata('pesan', 'Data APL1 berhasil diperbarui');
        } else {
            // Jika tidak, lakukan penyimpanan data baru
            $this->apl1->insert($data);
            session()->setFlashdata('pesan', 'APL1 berhasil ditambahkan!');
        }

        // $this->apl1->save($data);
        // session()->setFlashdata('pesan', 'APL1 berhasil ditambahkan!');
        // return redirect()->to('/edit-apl1/' . user()->id);
        return redirect()->to('/apl1');
    }

    public function storeDocument()
    {
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


        $data = [
            'pas_foto' => $nama_foto,
            'ktp' => $nama_ktp,
            'bukti_pendidikan' => $nama_bukti_pendidikan
        ];

        // $this->apl1->save($data);
        $this->apl1->update($this->request->getVar('id'), $data);

        $userImage = [
            'user_image' => $nama_foto,
        ];

        $this->usermodel->update(user()->id, $userImage);
        session()->setFlashdata('pesan', 'APL1 berhasil ditambahkan!');
        return redirect()->to('/apl1');
    }

    public function validasi()
    {

        $rules = [

            'validasi_apl1' => [
                'label' => 'Validasi',
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
            'validasi_apl1' => $this->request->getVar('validasi_apl1'),
        ];

        $this->apl1->update($this->request->getVar('id'), $data);

        $to = $this->request->getVar('email');
        $subject = 'Validasi Data Pendaftaran Uji Kompetensi Keahlian';

        // HTML message
        $message = view('email/email_validasi_apl1', [
            'name' => $this->request->getVar('name'),
            'id' => $this->request->getVar('id')
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

        session()->setFlashdata('pesan', 'Form APL 1 berhasil divalidasi!');
        return redirect()->to('/apl1');
    }

    public function update()
    {
        $dataAPL1 = $this->apl1->getAllAPL1($this->request->getVar('id_siswa'));
        $rules = [
            'nama_siswa' => [
                'rules' => 'required' . ($this->request->getVar('nama_siswa') != $dataAPL1['nama_siswa'] ? '|is_unique[apl1.nama_siswa]' : ''),
                'errors' => [
                    'required' => 'Kolom nama_siswa harus diisi.',
                    'is_unique' => 'Nama sudah terdaftar.'
                ],
            ],
            'ktp' => [
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
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Kolom telepon rumah harus diisi.',
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

            // 'telpon_kantor' => [
            //     'rules' => 'required|numeric',
            //     'errors' => [
            //         'required' => 'Kolom telepon kantor harus diisi.',
            //         'numeric' => 'Kolom telepon kantor harus berupa angka.',
            //     ],
            // ],

            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Kolom email harus diisi.',
                    'valid_email' => 'Format email tidak valid.',
                ],
            ],

            'pendidikan' => [
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Kolom pendidikan harus diisi.',
                    'max_length' => 'Panjang teks pendidikan tidak boleh melebihi :max karakter.',
                ],
            ],

            'nama_lembaga' => [
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Kolom nama lembaga harus diisi.',
                    'max_length' => 'Panjang teks nama lembaga tidak boleh melebihi :max karakter.',
                ],
            ],

            'jurusan' => [
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Kolom jurusan harus diisi.',
                    'max_length' => 'Panjang teks jurusan tidak boleh melebihi :max karakter.',
                ],
            ],

            // tambahkan aturan validasi lainnya sesuai kebutuhan
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        // '' => $this->request->getVar(''),
        $data = [
            'id_siswa' => $this->request->getVar('id_siswa'),
            'nama_siswa' => $this->request->getVar('nama_siswa'),
            'nik' => $this->request->getVar('ktp'),
            'tempat_lahir' => $this->request->getVar('tempat_lahir'),
            'tanggal_lahir' => $this->request->getVar('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getVar('jenis_kelamin'),
            'kebangsaan' => $this->request->getVar('kebangsaan'),
            'provinsi' => $this->request->getVar('provinsi'),
            'kabupaten' => $this->request->getVar('kabupaten'),
            'kecamatan' => $this->request->getVar('kecamatan'),
            'kelurahan' => $this->request->getVar('kelurahan'),
            'rt' => $this->request->getVar('rt'),
            'rw' => $this->request->getVar('rw'),
            'kode_pos' => $this->request->getVar('kode_pos'),
            'telpon_rumah' => $this->request->getVar('telpon_rumah'),
            'no_hp' => $this->request->getVar('no_hp'),
            // 'telpon_kantor' => $this->request->getVar('telpon_kantor'),
            'email' => $this->request->getVar('email'),
            // 'email2' => $this->request->getVar('email2'),
            'pendidikan' => $this->request->getVar('pendidikan'),
            'nama_lembaga' => $this->request->getVar('nama_lembaga'),
            'jurusan' => $this->request->getVar('jurusan'),
            'tujuan' => $this->request->getVar('tujuan'),
            'id_skema' => $this->request->getVar('id_skema'),
            'validasi_apl1' => "N",

        ];

        $this->apl1->update($dataAPL1['id_apl1'], $data);
        session()->setFlashdata('pesan', 'Form APL 1 berhasil diupdate!');
        return redirect()->to('/edit-apl1/' . user()->id);
    }

    public function kabupaten()
    {
        $id_provinsi = $this->request->getPost('id_provinsi');
        $kab = $this->dependent->AllKabupaten($id_provinsi);
        echo '<option>-- Pilih Kabupaten/Kota --</option>';
        foreach ($kab as $key => $k) {
            echo "<option value=" . $k['id'] . ">" . $k['nama'] . "</option>";
        }
    }

    public function kecamatan()
    {
        $id_kabupaten = $this->request->getPost('id_kabupaten');
        $kec = $this->dependent->AllKecamatan($id_kabupaten);
        echo '<option>-- Pilih Kecamatan --</option>';
        foreach ($kec as $key => $k) {
            echo "<option value=" . $k['id'] . ">" . $k['nama'] . "</option>";
        }
    }

    public function desa()
    {
        $id_kecamatan = $this->request->getPost('id_kecamatan');
        $desa = $this->dependent->AllDesa($id_kecamatan);
        echo '<option>-- Pilih Kelurahan/Desa --</option>';
        foreach ($desa as $key => $k) {
            echo "<option value=" . $k['id'] . ">" . $k['nama'] . "</option>";
        }
    }


    public function pdf($id_apl1)
    {
        $dataAPL1 = $this->apl1->getAPL1byid($id_apl1);
        $listUnit = $this->unit->getUnit($dataAPL1['id_skema']);

        $jenis_kelamin = ($dataAPL1['jenis_kelamin'] == "Laki-Laki") ? 'Laki-Laki / <span style="text-decoration: line-through;">Perempuan</span>' : '<span style="text-decoration: line-through;">Laki-Laki</span> / Perempuan';

        // Create a new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('LSP SMK NEGERI 2 Kuningan');

        // Set title based on user's full name
        $pdf->SetTitle('FR.APL.01. ' . $dataAPL1['nama_siswa']);

        // Set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'LEMBAGA SERTIFIKASI PROFESI - P1 SMK NEGERI 2 KUNINGAN', PDF_HEADER_STRING);

        // Set header and footer fonts
        $pdf->SetHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->SetFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        // $pdf->SetAutoPageBreak(true, 5);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Add a page
        $pdf->AddPage();

        // // set cell padding
        // $pdf->setCellPaddings(1, 1, 1, 1);

        // // set cell margins
        // $pdf->setCellMargins(1, 1, 1, 1);

        // Add title
        // $pdf->SetFont('helvetica', 'B', 10);
        // $pdf->Cell(0, 10, 'FR.APL.01. PERMOHONAN SERTIFIKASI KOMPETENSI', 0, 1, 'L');

        // // Add subtitle
        // $pdf->SetFont('helvetica', 'B', 10);
        // $pdf->Cell(0, 10, 'Bagian 1 : Rincian Data Pemohon Sertifikasi', 0, 1, 'L');

        // // Add description
        // $pdf->SetFont('helvetica', '', 10);
        // $pdf->MultiCell(0, 10, 'Pada bagian ini, cantumlah data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.', 0, 'L');

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            
            <body>
            ';

        $html .= '
    
    
            <h4>FR.APL.01. PERMOHONAN DATA PEMOHON SERTIFIKASI KOMPETENSI</h4>
            <h4>Bagian 1 : Rincian Data Pemohon Sertifikasi</h4>
            <p>Pada bagian ini, cantumlah data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.</p>
            <ol type="a">
            <li>
            <table>  
            <tr>
            <th>Data Pribadi</th>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Nama Lengkap</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_siswa'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >No. KTP/NIK/Paspor</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nik'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Tempat / Tanggal Lahir</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['tempat_lahir'] . ', ' .  $dataAPL1['tanggal_lahir'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Jenis Kelamin</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; ">' .  $jenis_kelamin . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Kebangsaan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['kebangsaan'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Alamat Rumah</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">Kel. ' .  $dataAPL1['nama_kelurahan'] . ', Kec. ' .  $dataAPL1['nama_kecamatan'] . ', ' .  $dataAPL1['nama_kabupaten'] . ', Prov. ' .  $dataAPL1['nama_provinsi'] . ', Kode Pos :' .  $dataAPL1['kode_pos'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" ></th>
            <td style="width: 5%;"></td>
            <td style="width: 55%; border-bottom: 1px solid #000;">HP: ' .  $dataAPL1['no_hp'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >No Telepon/Email</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">Rumah: ' .  $dataAPL1['telpon_rumah'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" ></th>
            <td style="width: 5%;"></td>
            <td style="width: 55%; border-bottom: 1px solid #000;">Email: ' .  $dataAPL1['email'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Pendidikan Terakhir</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['pendidikan_terakhir'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Nama Sekolah/Universitas</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_sekolah'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Jurusan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['jurusan'] . '</td>
            </tr>
            </table>
            </li>
            <li>
            <table>  
            <tr>
            <th>Data Pekerjaan Sekarang</th>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Nama Institusi/Perusahaan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_lembaga'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Jabatan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['jabatan'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Alamat Perusahaan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['alamat_perusahaan'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >No Telepon/Email</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">Telp: ' .  $dataAPL1['no_telp_perusahaan'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" ></th>
            <td style="width: 5%;"></td>
            <td style="width: 55%; border-bottom: 1px solid #000;">Email: ' .  $dataAPL1['email_perusahaan'] . '</td>
            </tr>
            </table>
            </li>
            </ol>
            ';

        $html .= '
            </body>
            </html>';

        // Output the HTML content to the PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->AddPage();

        $html2 = '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <style>
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
              }
            </style>
            <body>
            ';

        $html2 .= '
    
            <h4>Bagian 2 : Data Sertifikasi</h4>
            <p>Tuliskan Judul dan Nomor Skema Sertifikasi yang anda ajukan berikut Daftar Unit Kompetensi sesuai kemasan pada skema sertifikasi untuk mendapatkan pengakuan sesuai dengan latar belakang pendidikan, pelatihan serta pengalaman kerja yang anda miliki.</p>
    
            <table>
            <tr>
              <td style="width: 30%;" rowspan="2">
              Skema Sertifikasi
              (KKNI/Okupasi/Klaster)
              </td>
              <td style="width: 10%;">Judul</td>
              <td style="width: 5%;">:</td>
              <td style="width: 55%;">' . $dataAPL1['nama_skema'] . '</td>
            </tr>
            <tr>
              <td>Nomor</td>
              <td>:</td>
              <td></td>
            </tr>
            <tr>
              <td colspan="2" rowspan="4">Tujuan Asesmen</td>
              <td>:</td>
              <td>Sertifikasi</td>
            </tr>
            <tr>
            <td></td>
            <td>Pengakuan Kompetensi Lampau (PKT)</td>
            </tr>
            <tr>
            <td></td>
            <td>Rekognisi Pembelajaran Lampau (RPL)</td>
            </tr>
            <tr>
            <td></td>
            <td>Lainya</td>
            </tr>
          </table>
    
          <h4>Daftar Unit Kompetensi Sesuai Kemasan:</h4>
          <table>
          <tr>
            <th style="width: 5%;">No.</th>
            <th style="width: 25%;">Kode Unit</th>
            <th style="width: 45%;">Judul Unit</th>
            <th style="width: 25%;">Standar Kompetensi Kerja</th>
          </tr>
          ';

        $no = 0;
        foreach ($listUnit as $key => $value) {
            $no++;

            $html2 .= '
    
                <tr>
                  <td>' . $no . '.</td>
                  <td>' . $value['kode_unit'] . '</td>
                  <td>' . $value['nama_unit'] . '</td>
                  <td></td>
                </tr>
    
               ';
        }

        $html2 .= '
            </table>
    
            </body>
            </html>';

        // Output the HTML content to the PDF
        $pdf->writeHTML($html2, true, false, true, false, '');

        $pdf->AddPage();

        $html3 = '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <style>
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
              }
            </style>
            <body>
            ';

        $html3 .= '
            <h4>Bagian 3 : Bukti Kelengkapan Pemohon</h4>
            <h4>3.1 Bukti Persyaratan Dasar Pemohon</h4>
    
            <table>
            <tr style="text-align: center;">
                <th style="width: 5%;" rowspan="2">No.</th>
                <th style="width: 50%;" rowspan="2">Bukti Persyaratan Dasar</th>
                <th style="width: 30%;" colspan="2">Ada</th>
                <th style="width: 15%;" rowspan="2">Tidak Ada</th>
            </tr>
            <tr style="text-align: center;">
                <th style="width: 15%;">Memenuhi Syarat</th>
                <th style="width: 15%;">Tidak Memenuhi Syarat</th>
            </tr>
            <tr>
                <td>1.</td>
                <td>Fotocopy Kartu Keluarga</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Foto Berwarna Ukuran 3x4 2 Lembar</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>3.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </table>
    
            <h4>3.2 Bukti Administratif</h4>
    
            <table>
            <tr style="text-align: center;">
                <th style="width: 5%;"  rowspan="2">No.</th>
                <th style="width: 50%;" rowspan="2">Bukti Persyaratan Dasar</th>
                <th style="width: 30%;" colspan="2">Ada</th>
                <th style="width: 15%;" rowspan="2">Tidak Ada</th>
            </tr>
            <tr style="text-align: center;">
                <th>Memenuhi Syarat</th>
                <th>Tidak Memenuhi Syarat</th>
            </tr>
            <tr>
                <td>1.</td>
                <td>Fotocopy Raport</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Fotocopy Sertifikat/Surat Keterangan PKL</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>3.</td>
                <td>Fotocopy Kartu Pelajar</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
    
            </table>
    
            <h4></h4>
    
            <table>
            <tr>
              <th style="width: 50%;" rowspan="3">
              Rekomendasi (DIisi Oleh LSP): <br>
              Berdasarkan Ketentuan Persyaratan Dasar, Maka Pemohon: <br> 
              <i>Diterima / Tidak Diterima</i> *) Sebagai Peserta Sertifikasi <br>
              *coret yang tidak perlu
              </th>
              <th style="width: 50%;" colspan="2">Pemohon/Kandidat</th>
            </tr>
            <tr>
              <th style="width: 20%;">Nama</th>
              <th style="width: 30%;">' . $dataAPL1['nama_siswa'] . '</th>
            </tr>
            <tr>
              <th style="width: 20%;">Tanda Tangan / Tanggal</th>
              <th style="width: 30%;"></th>
            </tr>
            <tr>
              <th rowspan="3">
              Catatan :
              </th>
              <th colspan="2">Admin LSP</th>
            </tr>
            <tr>
              <th style="width: 20%;">Nama</th>
              <th style="width: 30%;"></th>
            </tr>
            <tr>
              <th style="width: 20%;">Tanda Tangan / Tanggal</th>
              <th style="width: 30%;"></th>
            </tr>
            </table>
    
           ';


        $html3 .= '
            </body>
            </html>';

        $pdf->writeHTML($html3, true, false, true, false, '');

        $pdf->AddPage();

        $html4 = '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <style>
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
              }
            </style>
            <body>
            ';

        $html4 .= '
            <h4>Bagian 4 : Dokumen Portofolio</h4>
    
            <table>
            <tr>
            <td>Pas Foto</td>
            </tr>
            <tr>
            <td>
            <img src="upload/pas foto/' . $dataAPL1['pas_foto'] . '" style="width: 200px; height: auto;">
            </td>
            </tr>
            <tr>
            <td>Identitas Pemohon (KTP/Kartu Pelajar)</td>
            </tr>
            <tr>
            <td>
            <img src="upload/ktp/' . $dataAPL1['ktp'] . '" style="width: 200px; height: auto;">
            </td>
            </tr>
            <tr>
            <td>Bukti Pendidikan</td>
            </tr>
            <tr>
            <td>
            <img src="upload/bukti pendidikan/' . $dataAPL1['bukti_pendidikan'] . '" style="width: 200px; height: auto;"> 
            </td>
            </tr>
            </table>
           ';


        $html4 .= '
            </body>
            </html>';

        $pdf->lastPage();

        // Output the HTML content to the PDF
        $pdf->writeHTML($html4, true, false, true, false, '');

        // Output the PDF as attachment to browser
        $this->response->setContentType('application/pdf');
        $pdf->Output('FR.APL.01. ' . $dataAPL1['nama_siswa'] . '.pdf', 'I');
    }
}
