<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PengajuanAsesmenModel;
use App\Models\AsesmenMandiriModel;
use Ramsey\Uuid\Uuid;


class AsesmenMandiriController extends BaseController
{

    public function __construct()
    {
        helper(['auth']);
    }

    /**
     * Menampilkan halaman utama, kini hanya memuat view tanpa data.
     */
    public function index()
    {
        $data = [
            'siteTitle' => 'Daftar Asesmen Mandiri',
        ];

        // Data akan dimuat melalui AJAX, jadi tidak perlu dikirim dari sini.
        return view('asesi/list-asesmen-mandiri', $data);
    }

    /**
     * FUNGSI BARU: Endpoint untuk AJAX request.
     * Mengambil data asesmen berdasarkan filter dan mengembalikannya sebagai JSON.
     */
    /**
     * FUNGSI BARU: Endpoint untuk AJAX request.
     * Mengambil data asesmen berdasarkan filter dan mengembalikannya sebagai JSON.
     */
    public function filterAsesmen()
    {
        // Pastikan ini adalah AJAX request
        if ($this->request->isAJAX()) {
            $filter = $this->request->getGet('filter') ?? 'terbaru';
            $userId = user()->id; // Pastikan session/auth helper tersedia

            // Panggil model untuk mendapatkan data yang sudah difilter
            $data = $this->apl2Model->getByUserId($userId, $filter);

            // Kembalikan data dalam format JSON
            return $this->response->setJSON($data);
        }

        // Jika bukan AJAX, redirect atau tampilkan error 404
        return $this->response->setStatusCode(404);
    }

    /**
     * Menampilkan halaman asesmen mandiri untuk pengajuan tertentu.
     *
     * @param string $id_pengajuan ID pengajuan asesmen
     * @return \CodeIgniter\HTTP\Response|string
     */
    public function asesmen($id_pengajuan)
    {
        $dataPengajuan = $this->pengajuanAsesmenModel->getCompletePengajuanData($id_pengajuan);
        if (!$dataPengajuan) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data pengajuan tidak ditemukan.');
        }

        // Pengecekan awal: Jika APL.02 sudah ada, langsung arahkan ke mode read-only
        $dataAPL2 = $this->apl2Model->getByPengajuanId($id_pengajuan);
        if ($dataAPL2) {
            // Langsung tampilkan view read-only tanpa perlu proses lebih lanjut
            $data = [
                'siteTitle' => 'Asesmen Mandiri - ' . $dataPengajuan['asesi']['nama_lengkap'],
                'dataPengajuan' => $dataPengajuan,
                'dataAPL2' => $dataAPL2
            ];
            return view('asesi/asesmen-mandiri', $data);
        }

        $listKukNav = $this->kukModel->getNavigationList($dataPengajuan['asesmen']['id_skema']);
        $sessionKey = 'asesmen_answers_' . $id_pengajuan;

        // Cek jika pengguna datang dari halaman lain (bukan refresh), kita reset session.
        // Ini adalah cara sederhana untuk mendeteksi sesi baru.
        $referer = previous_url() ?? '';
        if (strpos($referer, 'asesmen-mandiri/' . $id_pengajuan) === false) {
            session()->remove($sessionKey);
        }

        $savedAnswers = session($sessionKey) ?? [];

        $data = [
            'siteTitle' => 'Asesmen Mandiri - ' . $dataPengajuan['asesi']['nama_lengkap'],
            'dataPengajuan' => $dataPengajuan,
            'listKukNav' => $listKukNav,
            'totalKuk' => count($listKukNav),
            'dataAPL2' => null, // Pastikan null karena ini mode pengisian
            'savedAnswers' => json_encode($savedAnswers)
        ];

        return view('asesi/asesmen-mandiri', $data);
    }

    /**
     * [SEMPURNAKAN] Mengembalikan state session terbaru setelah validasi.
     */
    public function validateStep()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $id_pengajuan = $this->request->getPost('id_pengajuan');
        $id_kuk = $this->request->getPost('id_kuk');
        $jawaban = $this->request->getPost('bk_' . $id_kuk);
        $bukti = $this->request->getPost('bukti_pendukung_' . $id_kuk);

        $rules = ['bk_' . $id_kuk => 'required'];
        if ($jawaban === 'K') {
            $rules['bukti_pendukung_' . $id_kuk] = 'required';
        }

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Jawaban tidak valid, harap lengkapi pilihan Anda.',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $sessionKey = 'asesmen_answers_' . $id_pengajuan;
        $answers = session($sessionKey) ?? [];
        $answers[$id_kuk] = [
            'tk' => $jawaban,
            'bukti_pendukung' => $bukti ?? '',
        ];
        session()->set($sessionKey, $answers);

        // [UBAH] Kembalikan semua jawaban yang tersimpan sebagai konfirmasi
        return $this->response->setJSON([
            'success' => true,
            'savedAnswers' => $answers // Kirim balik state terbaru
        ]);
    }

    /**
     * [BARU] Endpoint AJAX untuk mengambil data detail satu pertanyaan (KUK).
     */
    public function getAsesmenStep($id_kuk)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        // Query yang sangat spesifik dan cepat
        $kukData = $this->kukModel->getDetailKuk($id_kuk);

        if ($kukData) {
            return $this->response->setJSON(['success' => true, 'data' => $kukData]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
    }

    /**
     * [MODIFIKASI] Menyimpan hasil dari session via AJAX.
     */
    public function store_asesmen()
    {
        // Pastikan ini adalah request AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden action');
        }

        $id_pengajuan = $this->request->getPost('id_pengajuan');
        $sessionKey = 'asesmen_answers_' . $id_pengajuan;
        $savedAnswers = session($sessionKey);

        $dataPengajuan = $this->pengajuanAsesmenModel->getCompletePengajuanData($id_pengajuan);
        $listKuk = $this->kukModel->getNavigationList($dataPengajuan['asesmen']['id_skema']);

        // Final validation
        if (empty($savedAnswers) || count($savedAnswers) !== count($listKuk)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal! Harap lengkapi semua pertanyaan sebelum submit.'
            ]);
        }

        // --- Proses Penyimpanan Data (Tetap sama) ---
        $id_apl2 = "FR-APL-02-" . substr(Uuid::uuid4()->toString(), 0, 8);
        $kode_jawaban_apl2 = "ANS-APL-02-" . substr(Uuid::uuid4()->toString(), 0, 6);

        $this->apl2Model->insert([
            'id_apl2' => $id_apl2,
            'id_pengajuan' => $id_pengajuan,
            'kode_jawaban_apl2' => $kode_jawaban_apl2,
            'validasi_apl2' => 'pending'
        ]);

        $fullListKuk = $this->kukModel->getBySkema($dataPengajuan['asesmen']['id_skema']);
        $insertDataJawaban = [];
        foreach ($fullListKuk as $kuk) {
            if (isset($savedAnswers[$kuk['id_kuk']])) {
                $answer = $savedAnswers[$kuk['id_kuk']];
                $insertDataJawaban[] = [
                    'kode_jawaban_apl2' => $kode_jawaban_apl2,
                    'id_apl2' => $id_apl2,
                    'tk' => $answer['tk'],
                    'id_skema' => $kuk['id_skema'],
                    'id_unit' => $kuk['id_unit'],
                    'id_elemen' => $kuk['id_elemen'],
                    'id_kuk' => $kuk['id_kuk'],
                    'bukti_pendukung' => $answer['bukti_pendukung'],
                ];
            }
        }

        if (!empty($insertDataJawaban)) {
            $this->apl2JawabanModel->insertBatch($insertDataJawaban);
        }

        session()->remove($sessionKey); // Hapus data dari session setelah berhasil disimpan

        // Panggil helper untuk kirim email
        // $this->sendAsesmenNotification($dataPengajuan['asesi'], $id_apl2);

        session()->setFlashdata('pesan', 'Asesmen Mandiri berhasil disubmit!');

        // Kirim URL redirect ke JavaScript
        return $this->response->setJSON([
            'success' => true,
            'redirectUrl' => site_url('asesmen-mandiri/' . $id_pengajuan)
        ]);
    }

    /**
     * Helper function untuk mengirim email notifikasi.
     *
     * @param array $pengajuanData
     * @param string $id_apl2
     */
    private function sendAsesmenNotification($pengajuanData, $id_apl2)
    {
        $email = \Config\Services::email();
        $email->setTo($pengajuanData['email']);
        $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');
        $email->setSubject('Asesmen Mandiri Telah Disubmit');
        $email->setMailType('html');

        $message = view('email/email_send_apl2', [
            'name'       => $pengajuanData['nama_lengkap'],
            // 'id_pengajuan' => $pengajuanData['id_pengajuan'],
            'id_asesmen' => $id_apl2,
            // 'skema'      => $pengajuanData['nama_skema'],
        ]);

        $email->setMessage($message);

        if (!$email->send()) {
            log_message('error', '[sendAsesmenNotification] Gagal mengirim email: ' . $email->printDebugger(['headers']));
        }
    }
}
