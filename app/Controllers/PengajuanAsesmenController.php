<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;
use App\Models\User;

use App\Services\HeaderService;

use CodeIgniter\Exceptions\PageNotFoundException;

use App\Services\PDFService;
use App\Services\QRCodeService;
use Exception;

class PengajuanAsesmenController extends BaseController
{

    protected $pengajuanModel;

    protected $headerService;

    protected $pdfService;
    protected $qrCodeService;

    public function __construct()
    {

        $this->asesmenModel = model('AsesmenModel');
        $this->skemaModel = model('SkemaModel');
        $this->settanggalModel = model('SettanggalModel');
        $this->tukModel = model('TukModel');
        $this->unitModel = model('UnitModel');
        $this->pengajuanModel = model('PengajuanAsesmenModel');

        $this->headerService = new HeaderService();

        $this->pdfService = new PDFService();
        $this->qrCodeService = new QRCodeService();


        helper(['auth']);
    }

    /**
     * Menampilkan halaman utama Kelola Pengajuan Asesmen
     */
    public function index(): string
    {
        $asesorList = $this->asesorModel->select('asesor.id_asesor, users.nama_lengkap as nama_asesor')
            ->join('users', 'users.id = asesor.id_user')
            ->orderBy('users.nama_lengkap', 'ASC')
            ->findAll();
        $data = [
            'siteTitle' => 'Kelola Pengajuan Asesmen',
            'asesorList' => $asesorList
        ];


        return view('admin/kelola_pengajuan_asesmen', $data);
    }


    /**
     * Generate FR.APL.01 PDF document based on the old logic but with the new structure.
     *
     * @param string $id_pengajuan UUID of the submission
     */
    public function generateAPL1(string $id_pengajuan)
    {
        try {
            $data['pengajuan'] = $this->pengajuanModel->getCompletePengajuanData($id_pengajuan);
            if (!$data['pengajuan']) {
                throw PageNotFoundException::forPageNotFound('Data pengajuan tidak ditemukan.');
            }

            $assessorId = $this->asesorModel->getIdAsesorByUserId(user()->id ?? null) ?? null;

            // 1. Dapatkan konfigurasi header yang sesuai untuk asesor ini
            $headerData = $this->headerService->getHeaderForAssessor($assessorId);

            $data['listUnit'] = $this->unitModel->getUnit($data['pengajuan']['asesmen']['id_skema']);
            $this->prepareDynamicDataForView($data);
            $this->generateQRCodes($data);

            $views = [
                'pdf/apl1_page1',
                'pdf/apl1_page2',
                'pdf/apl1_page3',
            ];

            $filename = 'FR.APL.01 - ' . $data['pengajuan']['asesi']['nama_lengkap'];

            // 2. Panggil PDF service dan sertakan $headerData
            $this->pdfService->generateMultiPagePdf($views, $data, $filename, $headerData);
        } catch (\Exception $e) {
            log_message('error', '[PengajuanAsesmenController::generateAPL1] ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal membuat PDF: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Prepares dynamic HTML strings and formatted data needed by the views.
     * DISESUAIKAN agar output variabel sama persis dengan controller lama.
     */
    private function prepareDynamicDataForView(array &$data): void
    {
        $pengajuanData = $data['pengajuan'];

        // Format Jenis Kelamin (Sudah Sesuai)
        $jk = $pengajuanData['asesi']['jenis_kelamin'];
        $data['jenis_kelamin_html'] = ($jk === "Laki-Laki")
            ? 'Laki-Laki / <span style="text-decoration: line-through;">Perempuan</span>'
            : '<span style="text-decoration: line-through;">Laki-Laki</span> / Perempuan';

        // Format Jenis Sertifikasi (Sudah Sesuai)
        $jenis_skema = $pengajuanData['asesmen']['jenis_skema'];
        if ($jenis_skema === "KKNI") {
            $data['jenis_sertifikasi_html'] = 'KKNI / <span style="text-decoration: line-through;">Okupasi</span>/<span style="text-decoration: line-through;">Klaster</span>';
        } elseif ($jenis_skema === "Okupasi") {
            $data['jenis_sertifikasi_html'] = '<span style="text-decoration: line-through;">KKNI</span>/Okupasi/<span style="text-decoration: line-through;">Klaster</span>';
        } else {
            $data['jenis_sertifikasi_html'] = '<span style="text-decoration: line-through;">KKNI</span>/<span style="text-decoration: line-through;">Okupasi</span>/Klaster';
        }

        // [PERBAIKAN] Logika Tujuan Asesmen agar menghasilkan blok HTML yang lengkap
        $tujuanOptions = [
            'Sertifikasi' => 'Sertifikasi',
            'PKT'         => 'Pengakuan Kompetensi Lampau (PKT)',
            'RPL'         => 'Rekognisi Pembelajaran Lampau (RPL)',
            'Lainnya'     => 'Lainnya'
        ];
        $selectedTujuan = $pengajuanData['asesmen']['tujuan'];

        $tujuanHtml = '<tr><td colspan="2" rowspan="4">Tujuan Asesmen</td><td>:</td>';
        $tujuanHtml .= '<td>' . (($selectedTujuan === 'Sertifikasi') ? $tujuanOptions['Sertifikasi'] : '<span style="text-decoration: line-through;">' . $tujuanOptions['Sertifikasi'] . '</span>') . '</td></tr>';
        $tujuanHtml .= '<tr><td></td><td>' . (($selectedTujuan === 'PKT') ? $tujuanOptions['PKT'] : '<span style="text-decoration: line-through;">' . $tujuanOptions['PKT'] . '</span>') . '</td></tr>';
        $tujuanHtml .= '<tr><td></td><td>' . (($selectedTujuan === 'RPL') ? $tujuanOptions['RPL'] : '<span style="text-decoration: line-through;">' . $tujuanOptions['RPL'] . '</span>') . '</td></tr>';
        $tujuanHtml .= '<tr><td></td><td>' . (($selectedTujuan === 'Lainnya') ? $tujuanOptions['Lainnya'] : '<span style="text-decoration: line-through;">' . $tujuanOptions['Lainnya'] . '</span>') . '</td></tr>';

        $data['tujuan_html'] = $tujuanHtml;


        // Logika untuk Bukti Persyaratan Dasar (Pas Foto) (Sudah Sesuai)
        $data['bukti_dasar_html'] = '
            <tr>
                <td>2.</td>
                <td>Foto Berwarna Ukuran 3x4 2 Lembar</td>
                <td style="text-align: center;">' . (!empty($pengajuanData['dokumen']['pas_foto']) ? 'Ada' : '') . '</td>
                <td></td>
                <td style="text-align: center;">' . (empty($pengajuanData['dokumen']['pas_foto']) ? 'Tidak Ada' : '') . '</td>
            </tr>';

        // Logika untuk Status APL.01 (Sudah Sesuai)
        $status = $pengajuanData['pengajuan']['status_pengajuan'];
        if ($status == "diterima") {
            $data['status_apl1_html'] = 'Diterima / <span style="text-decoration: line-through;">Tidak Diterima</span>';
        } elseif ($status == "ditolak") {
            $data['status_apl1_html'] = '<span style="text-decoration: line-through;">Diterima</span> / Tidak Diterima';
        } else {
            // Default jika statusnya masih pending atau lainnya
            $data['status_apl1_html'] = 'Diterima / Tidak Diterima';
        }
    }
    /**
     * Generates QR codes for signatures and adds them to the data array.
     */
    private function generateQRCodes(array &$data): void
    {
        // Generate QR Code untuk Asesi (diasumsikan selalu ada)
        $data['qr_asesi'] = null;
        if (!empty($data['pengajuan']['dokumen']['tanda_tangan_asesi'])) {
            $data['qr_asesi'] = $this->qrCodeService->generate(
                base_url('scan-ttd/asesi/' . $data['pengajuan']['dokumen']['tanda_tangan_asesi']),
                'logolsp.png' // Pastikan file ini ada di path yang benar
            );
        }

        // Generate QR Code untuk Admin (Validator)
        $data['qr_admin'] = null;
        $data['nama_admin'] = ''; // default

        // Cek jika validator_id ada
        if (!empty($data['pengajuan']['pengajuan']['validator_id'])) {
            // Anda perlu method di model untuk mengambil data admin berdasarkan ID
            // Asumsi methodnya bernama `getValidatorData`
            $validatorData = $this->pengajuanModel->getValidatorData($data['pengajuan']['pengajuan']['validator_id']);

            if ($validatorData) {
                $data['nama_admin'] = $validatorData['nama_lengkap']; // Ambil nama admin
                if (!empty($validatorData['tanda_tangan_admin'])) {
                    $data['qr_admin'] = $this->qrCodeService->generate(
                        base_url('scan-ttd/admin/' . $validatorData['tanda_tangan_admin']),
                        'logolsp.png'
                    );
                }
            }
        }
    }

    /**
     * The original page-loading method.
     * It now just loads the view, which will be populated by AJAX.
     */
    public function skema()
    {
        $data = [
            'siteTitle' => 'Skema Sertifikasi',
        ];
        return view('asesi/skema', $data);
    }

    public function skema_detail($id_asesmen, $cache = false)
    {
        $data = [
            'siteTitle' => 'Skema Sertifikasi',
            'id_asesmen' => $id_asesmen
        ];

        // dd($data);
        return view('asesi/skema-detail', $data);
    }

    public function skema_daftar($id_asesmen)
    {
        $data = [
            'siteTitle' => 'Daftar Skema Sertifikasi',
            'id_asesmen' => $id_asesmen
        ];

        return view('asesi/skema-daftar', $data);
    }
}
