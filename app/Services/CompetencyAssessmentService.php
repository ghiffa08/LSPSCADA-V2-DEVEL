<?php

namespace App\Services;

use App\Entities\PengajuanAsesmenEntity;
use App\Entities\AsesiEntity;
use App\Repositories\PengajuanAsesmenRepository;
use App\Repositories\AsesiRepository;
use App\DTOs\ApiResponseDTO;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * CompetencyAssessmentService
 * 
 * Service for handling competency assessment business logic including
 * determining competency status (kompeten/belum kompeten) based on assessment results
 */
class CompetencyAssessmentService
{
    private PengajuanAsesmenRepository $pengajuanRepository;
    private AsesiRepository $asesiRepository;

    public function __construct(
        PengajuanAsesmenRepository $pengajuanRepository,
        AsesiRepository $asesiRepository
    ) {
        $this->pengajuanRepository = $pengajuanRepository;
        $this->asesiRepository = $asesiRepository;
    }

    /**
     * Determine competency status based on assessment results
     *
     * @param string $apl1Id
     * @param array $assessmentResults
     * @return ApiResponseDTO
     */
    public function determineCompetencyStatus(string $apl1Id, array $assessmentResults): ApiResponseDTO
    {
        try {
            $pengajuan = $this->pengajuanRepository->findByIdWithDetails($apl1Id);

            if (!$pengajuan) {
                return ApiResponseDTO::error('Pengajuan asesmen tidak ditemukan', 404);
            }

            $entity = new PengajuanAsesmenEntity($pengajuan);

            // Calculate competency based on assessment results
            $competencyResult = $this->calculateCompetency($assessmentResults);

            // Update competency status
            $updateData = [
                'competency_status' => $competencyResult['status'],
                'competency_score' => $competencyResult['score'],
                'competency_details' => json_encode($competencyResult['details']),
                'assessment_date' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->pengajuanRepository->update($apl1Id, $updateData);

            return ApiResponseDTO::success([
                'competency_status' => $competencyResult['status'],
                'competency_score' => $competencyResult['score'],
                'competency_details' => $competencyResult['details'],
                'recommendations' => $this->generateRecommendations($competencyResult)
            ], 'Status kompetensi berhasil ditentukan');
        } catch (DatabaseException $e) {
            log_message('error', 'Database error in competency assessment: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan database: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            log_message('error', 'Error in competency assessment: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Calculate competency based on assessment results
     *
     * @param array $assessmentResults
     * @return array
     */
    private function calculateCompetency(array $assessmentResults): array
    {
        $totalScore = 0;
        $maxScore = 0;
        $details = [];

        foreach ($assessmentResults as $unit) {
            $unitScore = 0;
            $unitMaxScore = 0;
            $unitDetails = [];

            if (isset($unit['elements']) && is_array($unit['elements'])) {
                foreach ($unit['elements'] as $element) {
                    $elementScore = $this->calculateElementScore($element);
                    $unitScore += $elementScore['score'];
                    $unitMaxScore += $elementScore['max_score'];

                    $unitDetails[] = [
                        'element_code' => $element['code'] ?? '',
                        'element_title' => $element['title'] ?? '',
                        'score' => $elementScore['score'],
                        'max_score' => $elementScore['max_score'],
                        'status' => $elementScore['score'] >= ($elementScore['max_score'] * 0.7) ? 'kompeten' : 'belum_kompeten'
                    ];
                }
            }

            $totalScore += $unitScore;
            $maxScore += $unitMaxScore;

            $details[] = [
                'unit_code' => $unit['code'] ?? '',
                'unit_title' => $unit['title'] ?? '',
                'score' => $unitScore,
                'max_score' => $unitMaxScore,
                'percentage' => $unitMaxScore > 0 ? round(($unitScore / $unitMaxScore) * 100, 2) : 0,
                'status' => $unitScore >= ($unitMaxScore * 0.7) ? 'kompeten' : 'belum_kompeten',
                'elements' => $unitDetails
            ];
        }

        $overallPercentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
        $competencyThreshold = 70; // 70% threshold for competency

        return [
            'status' => $overallPercentage >= $competencyThreshold ? 'kompeten' : 'belum_kompeten',
            'score' => $overallPercentage,
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'threshold' => $competencyThreshold,
            'details' => $details
        ];
    }

    /**
     * Calculate score for individual competency element
     *
     * @param array $element
     * @return array
     */
    private function calculateElementScore(array $element): array
    {
        $score = 0;
        $maxScore = 100; // Default max score per element

        // Different scoring methods based on element type
        if (isset($element['type'])) {
            switch ($element['type']) {
                case 'observation':
                    $score = $this->calculateObservationScore($element);
                    break;
                case 'portfolio':
                    $score = $this->calculatePortfolioScore($element);
                    break;
                case 'interview':
                    $score = $this->calculateInterviewScore($element);
                    break;
                case 'demonstration':
                    $score = $this->calculateDemonstrationScore($element);
                    break;
                default:
                    $score = $this->calculateGenericScore($element);
            }
        } else {
            $score = $this->calculateGenericScore($element);
        }

        return [
            'score' => min($score, $maxScore), // Ensure score doesn't exceed max
            'max_score' => $maxScore
        ];
    }

    /**
     * Calculate observation score
     *
     * @param array $element
     * @return float
     */
    private function calculateObservationScore(array $element): float
    {
        if (!isset($element['criteria']) || !is_array($element['criteria'])) {
            return 0;
        }

        $totalCriteria = count($element['criteria']);
        $metCriteria = 0;

        foreach ($element['criteria'] as $criteria) {
            if (isset($criteria['met']) && $criteria['met'] === true) {
                $metCriteria++;
            }
        }

        return $totalCriteria > 0 ? ($metCriteria / $totalCriteria) * 100 : 0;
    }

    /**
     * Calculate portfolio score
     *
     * @param array $element
     * @return float
     */
    private function calculatePortfolioScore(array $element): float
    {
        if (!isset($element['evidence']) || !is_array($element['evidence'])) {
            return 0;
        }

        $totalEvidence = count($element['evidence']);
        $validEvidence = 0;

        foreach ($element['evidence'] as $evidence) {
            if (isset($evidence['valid']) && $evidence['valid'] === true) {
                $validEvidence++;
            }
        }

        return $totalEvidence > 0 ? ($validEvidence / $totalEvidence) * 100 : 0;
    }

    /**
     * Calculate interview score
     *
     * @param array $element
     * @return float
     */
    private function calculateInterviewScore(array $element): float
    {
        if (!isset($element['questions']) || !is_array($element['questions'])) {
            return 0;
        }

        $totalQuestions = count($element['questions']);
        $correctAnswers = 0;

        foreach ($element['questions'] as $question) {
            if (isset($question['correct']) && $question['correct'] === true) {
                $correctAnswers++;
            }
        }

        return $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
    }

    /**
     * Calculate demonstration score
     *
     * @param array $element
     * @return float
     */
    private function calculateDemonstrationScore(array $element): float
    {
        if (!isset($element['performance_indicators']) || !is_array($element['performance_indicators'])) {
            return 0;
        }

        $totalIndicators = count($element['performance_indicators']);
        $achievedIndicators = 0;

        foreach ($element['performance_indicators'] as $indicator) {
            if (isset($indicator['achieved']) && $indicator['achieved'] === true) {
                $achievedIndicators++;
            }
        }

        return $totalIndicators > 0 ? ($achievedIndicators / $totalIndicators) * 100 : 0;
    }

    /**
     * Calculate generic score for unknown element types
     *
     * @param array $element
     * @return float
     */
    private function calculateGenericScore(array $element): float
    {
        // Default scoring based on available data
        if (isset($element['score']) && is_numeric($element['score'])) {
            return (float) $element['score'];
        }

        if (isset($element['status'])) {
            return $element['status'] === 'kompeten' ? 100 : 0;
        }

        return 0;
    }

    /**
     * Generate recommendations based on competency results
     *
     * @param array $competencyResult
     * @return array
     */
    private function generateRecommendations(array $competencyResult): array
    {
        $recommendations = [];

        if ($competencyResult['status'] === 'kompeten') {
            $recommendations[] = [
                'type' => 'certificate',
                'message' => 'Asesi dinyatakan kompeten dan berhak mendapatkan sertifikat kompetensi.',
                'priority' => 'high'
            ];
        } else {
            $recommendations[] = [
                'type' => 'remedial',
                'message' => 'Asesi belum kompeten dan perlu mengikuti program remedial.',
                'priority' => 'high'
            ];

            // Add specific recommendations for failed units
            foreach ($competencyResult['details'] as $unit) {
                if ($unit['status'] === 'belum_kompeten') {
                    $recommendations[] = [
                        'type' => 'unit_remedial',
                        'message' => "Perlu perbaikan pada unit kompetensi: {$unit['unit_title']}",
                        'unit_code' => $unit['unit_code'],
                        'priority' => 'medium'
                    ];
                }
            }
        }

        // Add general recommendations based on score
        if ($competencyResult['score'] < 50) {
            $recommendations[] = [
                'type' => 'training',
                'message' => 'Disarankan mengikuti pelatihan tambahan sebelum asesmen ulang.',
                'priority' => 'high'
            ];
        } elseif ($competencyResult['score'] < 70) {
            $recommendations[] = [
                'type' => 'practice',
                'message' => 'Perlu latihan tambahan untuk meningkatkan kompetensi.',
                'priority' => 'medium'
            ];
        }

        return $recommendations;
    }

    /**
     * Get competency statistics for reporting
     *
     * @param array $filters
     * @return ApiResponseDTO
     */
    public function getCompetencyStatistics(array $filters = []): ApiResponseDTO
    {
        try {
            $statistics = $this->pengajuanRepository->getCompetencyStatistics($filters);

            return ApiResponseDTO::success($statistics, 'Statistik kompetensi berhasil diambil');
        } catch (\Exception $e) {
            log_message('error', 'Error getting competency statistics: ' . $e->getMessage());
            return ApiResponseDTO::error('Gagal mengambil statistik kompetensi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export competency assessment results
     *
     * @param string $apl1Id
     * @param string $format
     * @return ApiResponseDTO
     */
    public function exportAssessmentResults(string $apl1Id, string $format = 'pdf'): ApiResponseDTO
    {
        try {
            $pengajuan = $this->pengajuanRepository->findByIdWithDetails($apl1Id);

            if (!$pengajuan) {
                return ApiResponseDTO::error('Pengajuan asesmen tidak ditemukan', 404);
            }

            // Generate export data
            $exportData = $this->prepareExportData($pengajuan);

            // Based on format, generate appropriate output
            switch ($format) {
                case 'pdf':
                    $filePath = $this->generatePdfReport($exportData);
                    break;
                case 'excel':
                    $filePath = $this->generateExcelReport($exportData);
                    break;
                default:
                    return ApiResponseDTO::error('Format export tidak didukung', 400);
            }

            return ApiResponseDTO::success([
                'file_path' => $filePath,
                'download_url' => base_url("downloads/assessment/{$apl1Id}." . $format)
            ], 'Laporan berhasil digenerate');
        } catch (\Exception $e) {
            log_message('error', 'Error exporting assessment results: ' . $e->getMessage());
            return ApiResponseDTO::error('Gagal mengexport hasil asesmen: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Prepare data for export
     *
     * @param array $pengajuan
     * @return array
     */
    private function prepareExportData(array $pengajuan): array
    {
        return [
            'pengajuan' => $pengajuan,
            'asesi' => $pengajuan['asesi'] ?? [],
            'asesmen' => $pengajuan['asesmen'] ?? [],
            'competency_details' => json_decode($pengajuan['competency_details'] ?? '[]', true),
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => session('user_id') ?? 'system'
        ];
    }

    /**
     * Generate PDF report
     *
     * @param array $data
     * @return string
     */
    private function generatePdfReport(array $data): string
    {
        // Implementation would depend on PDF library (TCPDF, DOMPDF, etc.)
        // This is a placeholder for the actual implementation
        $fileName = 'assessment_report_' . $data['pengajuan']['id_apl1'] . '_' . date('Ymd_His') . '.pdf';
        $filePath = WRITEPATH . 'uploads/reports/' . $fileName;

        // Create directory if it doesn't exist
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Generate PDF content (placeholder)
        file_put_contents($filePath, 'PDF Report Content - ' . json_encode($data));

        return $filePath;
    }

    /**
     * Generate Excel report
     *
     * @param array $data
     * @return string
     */
    private function generateExcelReport(array $data): string
    {
        // Implementation would depend on Excel library (PhpSpreadsheet, etc.)
        // This is a placeholder for the actual implementation
        $fileName = 'assessment_report_' . $data['pengajuan']['id_apl1'] . '_' . date('Ymd_His') . '.xlsx';
        $filePath = WRITEPATH . 'uploads/reports/' . $fileName;

        // Create directory if it doesn't exist
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Generate Excel content (placeholder)
        file_put_contents($filePath, 'Excel Report Content - ' . json_encode($data));

        return $filePath;
    }
}
