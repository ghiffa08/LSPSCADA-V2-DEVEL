<?php

namespace App\Controllers;

use Config\Database;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class RekamanAsesmenController extends ResourceController
{
    use ResponseTrait;

    protected QRCodeService $qrCodeService;
    protected int $id_asesor;
    protected string $nama_asesor;
    protected object $asesmenModel;
    protected object $skemaModel;
    protected object $rekamanAsesmenModel;
    protected object $rekamanAsesmenKompetensiModel;
    protected object $unitModel;
    protected object $pengajuanAsesmenModel;
    protected PDFService $pdfService;

    public function __construct()
    {
        helper('auth');

        $this->qrCodeService = new QRCodeService();
        $this->asesmenModel = model('AsesmenModel');
        $this->skemaModel = model('SkemaModel');
        $this->rekamanAsesmenModel = model('RekamanAsesmenModel');
        $this->rekamanAsesmenKompetensiModel = model('RekamanAsesmenKompetensiModel');
        $this->unitModel = model('UnitModel');
        $this->pengajuanAsesmenModel = model('PengajuanAsesmenModel');
        $this->pdfService = new PDFService();
        $this->id_asesor = user()->id;
    }

    /**
     * Display a listing of the resource for admin
     *
     * @return string
     */
    public function index()
    {
        $data = [
            'siteTitle' => 'Rekaman Asesmen Kompetensi',
            'skema' => $this->asesmenModel->getAllAsesmen()
        ];

        return view('admin/rekaman_asesmen', $data);
    }

    /**
     * Show the form for creating a new resource
     *
     * @return string
     */
    public function create()
    {
        $data = [
            'siteTitle' => 'Rekaman Asesmen Kompetensi',
            'skema' => $this->asesmenModel->getAllAsesmen()
        ];

        return view('asesor/ceklist_rekaman_asesmen', $data);
    }

    /**
     * Store a newly created resource in storage
     *
     * @return ResponseInterface
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        try {
            $db = Database::connect();
            $db->transBegin();

            // Get form data
            $id_apl1 = $this->request->getPost('id_apl1');
            $tanggal_asesmen = $this->request->getPost('tanggal_asesmen');
            $rekomendasi = $this->request->getPost('rekomendasi');
            $catatan = $this->request->getPost('catatan');
            $ttd_asesor = $this->request->getPost('ttd_asesor');
            $ttd_asesi = $this->request->getPost('ttd_asesi');

            // Validate required fields
            if (empty($id_apl1) || empty($tanggal_asesmen) || empty($rekomendasi)) {
                throw new \Exception('Data yang diperlukan tidak lengkap');
            }

            // Check if rekaman asesmen already exists
            $existing = $this->rekamanAsesmenModel
                ->where('id_apl1', $id_apl1)
                ->where('deleted_at', null)
                ->first();

            if ($existing) {
                // Update existing record
                $rekamanData = [
                    'tanggal_asesmen' => $tanggal_asesmen,
                    'rekomendasi' => $rekomendasi,
                    'catatan' => $catatan,
                    'ttd_asesor' => $ttd_asesor,
                    'ttd_asesi' => $ttd_asesi,
                    'id_asesor' => $this->id_asesor,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->rekamanAsesmenModel->update($existing['id_rekaman_asesmen'], $rekamanData);
                $id_rekaman_asesmen = $existing['id_rekaman_asesmen'];
            } else {
                // Create new record
                $rekamanData = [
                    'id_apl1' => $id_apl1,
                    'tanggal_asesmen' => $tanggal_asesmen,
                    'rekomendasi' => $rekomendasi,
                    'catatan' => $catatan,
                    'ttd_asesor' => $ttd_asesor,
                    'ttd_asesi' => $ttd_asesi,
                    'id_asesor' => $this->id_asesor,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $id_rekaman_asesmen = $this->rekamanAsesmenModel->insert($rekamanData);
            }

            // Process competency assessments
            $kompetensi_data = $this->request->getPost('kompetensi');
            if (!empty($kompetensi_data)) {
                // Delete existing competency records for this assessment
                $this->rekamanAsesmenKompetensiModel
                    ->where('id_rekaman_asesmen', $id_rekaman_asesmen)
                    ->delete();

                // Insert new competency records
                foreach ($kompetensi_data as $id_unit => $data) {
                    $kompetensiRecord = [
                        'id_rekaman_asesmen' => $id_rekaman_asesmen,
                        'id_unit' => $id_unit,
                        'observasi' => isset($data['observasi']) ? 1 : 0,
                        'portofolio' => isset($data['portofolio']) ? 1 : 0,
                        'pihak_ketiga' => isset($data['pihak_ketiga']) ? 1 : 0,
                        'lisan' => isset($data['lisan']) ? 1 : 0,
                        'tertulis' => isset($data['tertulis']) ? 1 : 0,
                        'proyek' => isset($data['proyek']) ? 1 : 0,
                        'lainnya' => isset($data['lainnya']) ? 1 : 0,
                        'keterangan' => $data['keterangan'] ?? '',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->rekamanAsesmenKompetensiModel->insert($kompetensiRecord);
                }
            }

            $db->transCommit();

            return $this->respond([
                'status' => 'success',
                'message' => 'Rekaman asesmen berhasil disimpan',
                'data' => ['id_rekaman_asesmen' => $id_rekaman_asesmen]
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error saving rekaman asesmen: ' . $e->getMessage());

            return $this->fail('Gagal menyimpan rekaman asesmen: ' . $e->getMessage());
        }
    }

    /**
     * Load assessment record data for editing
     *
     * @return ResponseInterface
     */
    public function loadRekamanAsesmen()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        try {
            $id_skema = $this->request->getGet('id_skema');
            $id_asesmen = $this->request->getGet('id_asesmen');
            $id_asesi = $this->request->getGet('id_asesi');

            if (empty($id_skema) || empty($id_asesmen) || empty($id_asesi)) {
                throw new \Exception('Parameter tidak lengkap');
            }

            // Get pengajuan asesmen data with proper field names
            $pengajuan = $this->pengajuanAsesmenModel
                ->select('pengajuan_asesmen.*, users.fullname as nama_asesi, users.username, users.email, skema.nama_skema, skema.kode_skema')
                ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi')
                ->join('users', 'users.id = asesi.user_id')
                ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
                ->join('skema', 'skema.id_skema = asesmen.id_skema')
                ->where('pengajuan_asesmen.id_asesi', $id_asesi)
                ->where('asesmen.id_asesmen', $id_asesmen)
                ->where('asesmen.id_skema', $id_skema)
                ->where('pengajuan_asesmen.deleted_at', null)
                ->first();

            if (!$pengajuan) {
                throw new \Exception('Data pengajuan asesmen tidak ditemukan');
            }

            // Get unit kompetensi for the schema
            $units = $this->unitModel->getUnitsByScheme($id_skema);

            // Get existing rekaman asesmen if any
            $existingRekaman = $this->rekamanAsesmenModel
                ->where('id_apl1', $pengajuan['id_apl1'])
                ->where('deleted_at', null)
                ->first();

            $existingData = [];
            if ($existingRekaman) {
                $kompetensiData = $this->rekamanAsesmenKompetensiModel
                    ->where('id_rekaman_asesmen', $existingRekaman['id_rekaman_asesmen'])
                    ->findAll();

                foreach ($kompetensiData as $item) {
                    $existingData[] = [
                        'id_unit' => $item['id_unit'],
                        'observasi' => $item['observasi'],
                        'portofolio' => $item['portofolio'],
                        'pihak_ketiga' => $item['pihak_ketiga'],
                        'tes_lisan' => $item['lisan'],
                        'tes_tertulis' => $item['tertulis'],
                        'proyek_kerja' => $item['proyek_kerja'],
                        'lainnya' => $item['lainnya'],
                        'keterangan' => $item['keterangan']
                    ];
                }
            }

            return $this->respond([
                'success' => true,
                'rekaman_asesmen' => $units,
                'existing_data' => $existingData,
                'existing_recommendation' => $existingRekaman,
                'totalUnits' => count($units),
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading rekaman asesmen: ' . $e->getMessage());
            return $this->fail('Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF for assessment record
     *
     * @param int $id_rekaman_asesmen Assessment record ID
     * @return mixed
     */
    public function pdf(int $id_rekaman_asesmen)
    {
        try {
            // Get assessment record data
            $data = $this->getRekamanAsesmenData($id_rekaman_asesmen);

            // Generate QR codes for signatures
            if (!empty($data['rekaman']['ttd_asesi'])) {
                $data['qr_asesi'] = $this->qrCodeService->generate(
                    base_url('/scan-tanda-tangan-asesi/' . $data['rekaman']['ttd_asesi']),
                    'logolsp.png'
                );
            }

            if (!empty($data['rekaman']['ttd_asesor'])) {
                $data['qr_asesor'] = $this->qrCodeService->generate(
                    base_url('/scan-tanda-tangan-asesor/' . $data['rekaman']['ttd_asesor']),
                    'logolsp.png'
                );
            }

            // Generate PDF
            $this->generatePdf($data);
        } catch (\Exception $e) {
            log_message('error', 'Error generating PDF: ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal menggenerate PDF: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    /**
     * Generate and output PDF
     *
     * @param array $data Data for PDF views
     * @return void
     */
    private function generatePdf(array $data): void
    {
        $views = [
            'pdf/rekaman_page1',
            'pdf/rekaman_page2',
        ];

        $filename = 'FR.IA.03. REKAMAN ASESMEN KOMPETENSI';

        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }

    /**
     * Get complete assessment record data
     *
     * @param int $id_rekaman_asesmen Assessment record ID
     * @return array All data needed for PDF generation
     */
    private function getRekamanAsesmenData(int $id_rekaman_asesmen): array
    {
        // Get main assessment record with related data
        $rekaman = $this->rekamanAsesmenModel->getAssessmentRecordById($id_rekaman_asesmen);

        if (!$rekaman) {
            throw new \Exception('Data rekaman asesmen tidak ditemukan');
        }

        // Get competency assessment details
        $kompetensiDetails = $this->rekamanAsesmenKompetensiModel
            ->select('rekaman_asesmen_kompetensi.*, unit_kompetensi.kode_unit, unit_kompetensi.nama_unit')
            ->join('unit_kompetensi', 'unit_kompetensi.id_unit = rekaman_asesmen_kompetensi.id_unit')
            ->where('id_rekaman_asesmen', $id_rekaman_asesmen)
            ->findAll();

        // Group competencies by unit
        $kelompokKompetensi = [];
        foreach ($kompetensiDetails as $detail) {
            $kelompokKompetensi[] = [
                'kode_unit' => $detail['kode_unit'],
                'nama_unit' => $detail['nama_unit'],
                'metode_asesmen' => [
                    'observasi' => $detail['observasi'],
                    'portofolio' => $detail['portofolio'],
                    'pihak_ketiga' => $detail['pihak_ketiga'],
                    'lisan' => $detail['lisan'],
                    'tertulis' => $detail['tertulis'],
                    'proyek' => $detail['proyek'],
                    'lainnya' => $detail['lainnya']
                ],
                'keterangan' => $detail['keterangan']
            ];
        }

        return [
            'rekaman' => $rekaman,
            'kelompokKompetensi' => $kelompokKompetensi
        ];
    }

    /**
     * Delete assessment record
     *
     * @param int $id_rekaman_asesmen
     * @return ResponseInterface
     */
    public function delete($id_rekaman_asesmen = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        $db = Database::connect();
        try {
            $db->transBegin();

            // Soft delete main record
            $this->rekamanAsesmenModel->delete($id_rekaman_asesmen);

            // Delete related competency records
            $this->rekamanAsesmenKompetensiModel
                ->where('id_rekaman_asesmen', $id_rekaman_asesmen)
                ->delete();

            $db->transCommit();

            return $this->respond([
                'status' => 'success',
                'message' => 'Rekaman asesmen berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error deleting rekaman asesmen: ' . $e->getMessage());

            return $this->fail('Gagal menghapus rekaman asesmen: ' . $e->getMessage());
        }
    }


    /**
     * Auto-save functionality for real-time saving
     * 
     * @return ResponseInterface
     */
    public function autoSave()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        try {
            $id_apl1 = $this->request->getPost('id_apl1');
            $id_skema = $this->request->getPost('id_skema');
            $id_asesi = $this->request->getPost('id_asesi');

            if (empty($id_apl1)) {
                throw new \Exception('Parameter tidak lengkap');
            }

            $db = Database::connect();
            $db->transBegin();

            // Get or create rekaman asesmen
            $rekaman = $this->rekamanAsesmenModel
                ->where('id_apl1', $id_apl1)
                ->where('deleted_at', null)
                ->first();

            if (!$rekaman) {
                $rekamanData = [
                    'id_apl1' => $id_apl1,
                    'id_asesor' => $this->id_asesor,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $id_rekaman_asesmen = $this->rekamanAsesmenModel->insert($rekamanData);
            } else {
                $id_rekaman_asesmen = $rekaman['id_rekaman_asesmen'];
            }

            // Save general fields
            $generalFields = ['tanggal_asesmen', 'rekomendasi', 'catatan'];
            $updateData = ['updated_at' => date('Y-m-d H:i:s')];

            foreach ($generalFields as $field) {
                $value = $this->request->getPost($field);
                if ($value !== null) {
                    $updateData[$field] = $value;
                }
            }

            if (count($updateData) > 1) { // More than just updated_at
                $this->rekamanAsesmenModel->update($id_rekaman_asesmen, $updateData);
            }

            // Process unit data
            $units = $this->request->getPost('units');
            if (!empty($units)) {
                foreach ($units as $id_unit => $unitData) {
                    // Get or create kompetensi record
                    $kompetensi = $this->rekamanAsesmenKompetensiModel
                        ->where('id_rekaman_asesmen', $id_rekaman_asesmen)
                        ->where('id_unit', $id_unit)
                        ->first();

                    $kompetensiData = [
                        'observasi' => isset($unitData['observasi']) ? 1 : 0,
                        'portofolio' => isset($unitData['portofolio']) ? 1 : 0,
                        'lisan' => isset($unitData['tes_lisan']) ? 1 : 0,
                        'tertulis' => isset($unitData['tes_tertulis']) ? 1 : 0,
                        'keterangan' => $unitData['keterangan'] ?? '',
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if (!$kompetensi) {
                        $kompetensiData['id_rekaman_asesmen'] = $id_rekaman_asesmen;
                        $kompetensiData['id_unit'] = $id_unit;
                        $kompetensiData['created_at'] = date('Y-m-d H:i:s');
                        $this->rekamanAsesmenKompetensiModel->insert($kompetensiData);
                    } else {
                        $this->rekamanAsesmenKompetensiModel->update($kompetensi['id_rekaman_asesmen_kompetensi'], $kompetensiData);
                    }
                }
            }

            $db->transCommit();

            return $this->respond([
                'status' => 'success',
                'message' => 'Data berhasil disimpan otomatis',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error auto-saving: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Get skema details including available asesi
     * 
     * @return ResponseInterface
     */
    public function getSkemaDetails()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        try {
            $id_skema = $this->request->getGet('id_skema');
            $id_asesmen = $this->request->getGet('id_asesmen');

            if (empty($id_skema) || empty($id_asesmen)) {
                throw new \Exception('Parameter tidak lengkap');
            }

            // Validate that the asesmen exists
            $asesmen = $this->asesmenModel->find($id_asesmen);
            if (!$asesmen) {
                throw new \Exception('Data asesmen tidak ditemukan');
            }

            // Get asesi data for this asesmen with proper field mapping
            $asesiData = $this->pengajuanAsesmenModel
                ->select('pengajuan_asesmen.id_apl1, pengajuan_asesmen.id_asesi, users.fullname as nama_asesi, users.username, users.email')
                ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi')
                ->join('users', 'users.id = asesi.user_id')
                ->where('pengajuan_asesmen.id_asesmen', $id_asesmen)
                ->where('pengajuan_asesmen.deleted_at', null)
                ->findAll();

            // Log for debugging
            log_message('info', 'Found ' . count($asesiData) . ' asesi for asesmen ' . $id_asesmen);

            return $this->respond([
                'success' => true,
                'data' => [
                    'asesi' => $asesiData
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting skema details: ' . $e->getMessage());
            return $this->fail('Gagal memuat data: ' . $e->getMessage());
        }
    }

    public function saveMethod()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        $db = null;

        try {
            $id_apl1 = $this->request->getPost('id_apl1');
            $id_unit = $this->request->getPost('id_unit');
            $method = $this->request->getPost('method');
            $value = $this->request->getPost('value');

            if (empty($id_apl1) || empty($id_unit) || empty($method)) {
                throw new \Exception('Parameter tidak lengkap');
            }

            $db = Database::connect();
            $db->transBegin();

            // Get or create rekaman asesmen
            $rekaman = $this->rekamanAsesmenModel
                ->where('id_apl1', $id_apl1)
                ->where('deleted_at', null)
                ->first();

            if (!$rekaman) {
                $rekamanData = [
                    'id_apl1' => $id_apl1,
                    // Hapus id_asesor karena tidak ada di tabel
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $insertResult = $this->rekamanAsesmenModel->insert($rekamanData);

                if (!$insertResult) {
                    $errors = $this->rekamanAsesmenModel->errors();
                    throw new \Exception('Gagal membuat rekaman asesmen: ' . json_encode($errors));
                }

                $id_rekaman = $this->rekamanAsesmenModel->getInsertID();
            } else {
                $id_rekaman = $rekaman['id']; // Gunakan 'id', bukan 'id_rekaman_asesmen'
            }

            // Get or create kompetensi record
            $kompetensi = $this->rekamanAsesmenKompetensiModel
                ->where('id_rekaman', $id_rekaman) // Gunakan 'id_rekaman'
                ->where('id_unit', $id_unit)
                ->first();

            // Map method names to database field names (dengan prefix metode_)
            $methodMapping = [
                'observasi' => 'metode_observasi',
                'portofolio' => 'metode_portofolio',
                'pihak_ketiga' => 'metode_pihak_ketiga',
                'tes_lisan' => 'metode_lisan',
                'tes_tertulis' => 'metode_tertulis',
                'proyek_kerja' => 'metode_proyek',
                'lainnya' => 'metode_lainnya'
            ];

            $dbField = $methodMapping[$method] ?? null;

            if (!$dbField) {
                throw new \Exception('Method tidak valid: ' . $method);
            }

            if (!$kompetensi) {
                $kompetensiData = [
                    'id_rekaman' => $id_rekaman, // Gunakan 'id_rekaman'
                    'id_unit' => $id_unit,
                    $dbField => $value ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $insertKompetensiResult = $this->rekamanAsesmenKompetensiModel->insert($kompetensiData);

                if (!$insertKompetensiResult) {
                    $errors = $this->rekamanAsesmenKompetensiModel->errors();
                    throw new \Exception('Gagal menyimpan kompetensi: ' . json_encode($errors));
                }
            } else {
                $updateData = [
                    $dbField => $value ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $updateResult = $this->rekamanAsesmenKompetensiModel->update($kompetensi['id'], $updateData);

                if (!$updateResult) {
                    $errors = $this->rekamanAsesmenKompetensiModel->errors();
                    throw new \Exception('Gagal mengupdate kompetensi: ' . json_encode($errors));
                }
            }

            $db->transCommit();

            return $this->respond([
                'status' => 'success',
                'message' => 'Metode asesmen berhasil disimpan',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            if ($db) {
                $db->transRollback();
            }
            log_message('error', 'Error saving method: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan metode: ' . $e->getMessage());
        }
    }

    /**
     * Save bulk methods (check all/uncheck all)
     * 
     * @return ResponseInterface
     */
    public function saveBulkMethods()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        $db = null;

        try {
            $input = $this->request->getJSON(true);
            $id_apl1 = $input['id_apl1'] ?? '';
            $id_skema = $input['id_skema'] ?? '';
            $checkAll = $input['check_all'] ?? false;

            if (empty($id_apl1) || empty($id_skema)) {
                throw new \Exception('Parameter tidak lengkap');
            }

            $db = Database::connect();
            $db->transBegin();

            // Get all units for this schema - perbaiki query
            $units = $this->unitModel->getUnitsByScheme($id_skema);

            if (empty($units)) {
                throw new \Exception('Tidak ada unit kompetensi ditemukan untuk skema ini');
            }

            // Get or create rekaman asesmen
            $rekaman = $this->rekamanAsesmenModel
                ->where('id_apl1', $id_apl1)
                ->where('deleted_at', null)
                ->first();

            if (!$rekaman) {
                $rekamanData = [
                    'id_apl1' => $id_apl1,
                    // Hapus id_asesor karena tidak ada di tabel
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $insertResult = $this->rekamanAsesmenModel->insert($rekamanData);

                if (!$insertResult) {
                    throw new \Exception('Gagal membuat rekaman asesmen');
                }

                $id_rekaman = $this->rekamanAsesmenModel->getInsertID();
            } else {
                $id_rekaman = $rekaman['id']; // Gunakan 'id', bukan 'id_rekaman_asesmen'
            }

            // Update all units
            foreach ($units as $unit) {
                $kompetensi = $this->rekamanAsesmenKompetensiModel
                    ->where('id_rekaman', $id_rekaman) // Gunakan 'id_rekaman'
                    ->where('id_unit', $unit['id']) // Sesuaikan dengan field yang benar
                    ->first();

                // Gunakan nama field yang sesuai dengan tabel (dengan prefix metode_)
                $methodData = [
                    'metode_observasi' => $checkAll ? 1 : 0,
                    'metode_portofolio' => $checkAll ? 1 : 0,
                    'metode_pihak_ketiga' => $checkAll ? 1 : 0,
                    'metode_lisan' => $checkAll ? 1 : 0,
                    'metode_tertulis' => $checkAll ? 1 : 0,
                    'metode_proyek' => $checkAll ? 1 : 0,
                    'metode_lainnya' => $checkAll ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if (!$kompetensi) {
                    $methodData['id_rekaman'] = $id_rekaman; // Gunakan 'id_rekaman'
                    $methodData['id_unit'] = $unit['id']; // Sesuaikan dengan field yang benar
                    $methodData['created_at'] = date('Y-m-d H:i:s');

                    $insertResult = $this->rekamanAsesmenKompetensiModel->insert($methodData);

                    if (!$insertResult) {
                        throw new \Exception('Gagal menyimpan kompetensi untuk unit: ' . $unit['id']);
                    }
                } else {
                    $updateResult = $this->rekamanAsesmenKompetensiModel->update($kompetensi['id'], $methodData);

                    if (!$updateResult) {
                        throw new \Exception('Gagal mengupdate kompetensi untuk unit: ' . $unit['id']);
                    }
                }
            }

            $db->transCommit();

            return $this->respond([
                'status' => 'success',
                'message' => 'Metode asesmen berhasil diperbarui',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            if ($db) {
                $db->transRollback();
            }
            log_message('error', 'Error saving bulk methods: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan metode: ' . $e->getMessage());
        }
    }

    /**
     * Complete the assessment record
     * 
     * @return ResponseInterface
     */
    public function complete()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        try {
            $id_apl1 = $this->request->getPost('id_apl1');
            $rekomendasi = $this->request->getPost('rekomendasi');
            $tindak_lanjut = $this->request->getPost('tindak_lanjut');
            $catatan = $this->request->getPost('catatan');
            $tanggal_asesmen = $this->request->getPost('tanggal_asesmen');

            if (empty($id_apl1) || empty($rekomendasi)) {
                throw new \Exception('Parameter tidak lengkap');
            }

            $db = Database::connect();
            $db->transBegin();

            // Update rekaman asesmen
            $updateData = [
                'rekomendasi' => $rekomendasi,
                'tindak_lanjut' => $tindak_lanjut,
                'catatan' => $catatan,
                'tanggal_asesmen' => $tanggal_asesmen,
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->rekamanAsesmenModel->where('id_apl1', $id_apl1)->set($updateData)->update();

            $db->transCommit();

            return $this->respond([
                'status' => 'success',
                'message' => 'Rekaman asesmen berhasil diselesaikan',
                'redirect' => base_url('asesor/rekaman-asesmen'),
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error completing rekaman asesmen: ' . $e->getMessage());
            return $this->fail('Gagal menyelesaikan rekaman asesmen: ' . $e->getMessage());
        }
    }

    /**
     * Save general settings (rekomendasi, catatan, etc.)
     * 
     * @return ResponseInterface
     */
    public function saveGeneral()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        try {
            $id_apl1 = $this->request->getPost('id_apl1');
            $rekomendasi = $this->request->getPost('rekomendasi');
            $tindak_lanjut = $this->request->getPost('tindak_lanjut');
            $catatan = $this->request->getPost('catatan');
            $tanggal_asesmen = $this->request->getPost('tanggal_asesmen');

            if (empty($id_apl1)) {
                throw new \Exception('Parameter tidak lengkap');
            }

            $db = Database::connect();
            $db->transBegin();

            // Get or create rekaman asesmen
            $rekaman = $this->rekamanAsesmenModel
                ->where('id_apl1', $id_apl1)
                ->where('deleted_at', null)
                ->first();

            if (!$rekaman) {
                $rekamanData = [
                    'id_apl1' => $id_apl1,
                    'id_asesor' => $this->id_asesor,
                    'rekomendasi' => $rekomendasi,
                    'tindak_lanjut' => $tindak_lanjut,
                    'catatan' => $catatan,
                    'tanggal_asesmen' => $tanggal_asesmen,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->rekamanAsesmenModel->insert($rekamanData);
            } else {
                $updateData = [
                    'rekomendasi' => $rekomendasi,
                    'tindak_lanjut' => $tindak_lanjut,
                    'catatan' => $catatan,
                    'tanggal_asesmen' => $tanggal_asesmen,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->rekamanAsesmenModel->update($rekaman['id_rekaman_asesmen'], $updateData);
            }

            $db->transCommit();

            return $this->respond([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error saving general data: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Save keterangan for specific unit
     * 
     * @return ResponseInterface
     */
    public function saveKeterangan()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        try {
            $id_apl1 = $this->request->getPost('id_apl1');
            $id_unit = $this->request->getPost('id_unit');
            $keterangan = $this->request->getPost('keterangan');

            if (empty($id_apl1) || empty($id_unit)) {
                throw new \Exception('Parameter tidak lengkap');
            }

            $db = Database::connect();
            $db->transBegin();

            // Get or create rekaman asesmen
            $rekaman = $this->rekamanAsesmenModel
                ->where('id_apl1', $id_apl1)
                ->where('deleted_at', null)
                ->first();

            if (!$rekaman) {
                $rekamanData = [
                    'id_apl1' => $id_apl1,
                    'id_asesor' => $this->id_asesor,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $id_rekaman_asesmen = $this->rekamanAsesmenModel->insert($rekamanData);
            } else {
                $id_rekaman_asesmen = $rekaman['id_rekaman_asesmen'];
            }

            // Get or create kompetensi record
            $kompetensi = $this->rekamanAsesmenKompetensiModel
                ->where('id_rekaman_asesmen', $id_rekaman_asesmen)
                ->where('id_unit', $id_unit)
                ->first();

            if (!$kompetensi) {
                $kompetensiData = [
                    'id_rekaman_asesmen' => $id_rekaman_asesmen,
                    'id_unit' => $id_unit,
                    'keterangan' => $keterangan,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->rekamanAsesmenKompetensiModel->insert($kompetensiData);
            } else {
                $updateData = [
                    'keterangan' => $keterangan,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->rekamanAsesmenKompetensiModel->update($kompetensi['id_rekaman_asesmen_kompetensi'], $updateData);
            }

            $db->transCommit();

            return $this->respond([
                'status' => 'success',
                'message' => 'Keterangan berhasil disimpan',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error saving keterangan: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan keterangan: ' . $e->getMessage());
        }
    }
}
