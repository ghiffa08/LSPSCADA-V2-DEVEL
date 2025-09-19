<?php

namespace App\Services;

use App\Models\PMOModel;
use App\Models\PMOJawabanModel;
use App\Models\APL1Model;
use CodeIgniter\Database\BaseConnection;
use Exception;

/**
 * PmoService
 *
 * Service class for handling the business logic of the PMO checklist.
 * It provides methods for saving, retrieving, and deleting PMO data with proper validation and transaction management.
 */
class PMOService
{
    protected PMOModel $pmoModel;
    protected PMOJawabanModel $pmoJawabanModel;
    protected APL1Model $apl1Model;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->pmoModel = new PMOModel();
        $this->pmoJawabanModel = new PMOJawabanModel();
        $this->apl1Model = new APL1Model();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create or update a PMO session with its answers.
     *
     * @param array $data The complete data including master and answer details.
     * @return array Result array with success status, message, and data.
     */
    public function savePmo(array $data): array
    {
        $validatedData = $this->validateAndSanitizeInput($data);
        if (isset($validatedData['error'])) {
            log_message('error', '[PmoService] Validation error: ' . $validatedData['error']);
            return ['success' => false, 'message' => $validatedData['error']];
        }

        $this->db->transStart();

        try {
            $this->validateForeignKeys($validatedData);

            // Prepare master data for the 'pmo' table
            $masterData = [
                'id_apl1'   => $validatedData['id_apl1'],
                'id_skema'  => $validatedData['id_skema'],
                'id_asesor' => $validatedData['id_asesor'],
                'tanggal_observasi' => $validatedData['tanggal_observasi'],
                'catatan'   => $validatedData['catatan'],
            ];

            // Find or create the master PMO record
            $pmo = $this->pmoModel->where('id_apl1', $masterData['id_apl1'])
                ->where('id_skema', $masterData['id_skema'])
                ->first();

            if ($pmo) {
                $id_pmo = $pmo['id_pmo'];
                $this->pmoModel->update($id_pmo, $masterData);
            } else {
                $id_pmo = $this->pmoModel->insert($masterData, true);
            }

            if (!$id_pmo) {
                throw new Exception('Failed to create or update the PMO session.');
            }

            // Upsert the answers using the PmoJawabanModel
            $this->pmoJawabanModel->upsertJawaban($id_pmo, $validatedData['jawaban']);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('The database transaction failed.');
            }

            return [
                'success' => true,
                'message' => 'PMO checklist saved successfully.',
                'data'    => ['id_pmo' => $id_pmo]
            ];
        } catch (Exception $e) {
            $this->db->transRollback();
            log_message('error', '[PmoService] savePmo Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to save PMO data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get a complete PMO session with question structure and existing answers.
     *
     * @param int $id_pmo The ID of the PMO session.
     * @return array Result array with success status, message, and data.
     */
    public function getPmoWithDetails(int $id_pmo): array
    {
        try {
            $pmo = $this->pmoModel->getPmoById($id_pmo);
            if (!$pmo) {
                throw new Exception('PMO session not found.');
            }

            $struktur = $this->pmoModel->getStrukturPmoSkema($pmo['id_skema']);
            $jawaban = $this->pmoModel->getExistingJawaban($id_pmo);

            return [
                'success' => true,
                'message' => 'Data retrieved successfully.',
                'data'    => [
                    'pmo' => $pmo,
                    'struktur' => $struktur,
                    'jawaban' => $jawaban,
                ]
            ];
        } catch (Exception $e) {
            log_message('error', '[PmoService] getPmoWithDetails Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to retrieve PMO data: ' . $e->getMessage()];
        }
    }

    /**
     * Delete a PMO session and all its related answers.
     *
     * @param int $id_pmo The ID of the PMO session to delete.
     * @return array Result array with success status and message.
     */
    public function deletePmo(int $id_pmo): array
    {
        try {
            $pmo = $this->pmoModel->find($id_pmo);
            if (!$pmo) {
                return ['success' => false, 'message' => 'PMO session not found.'];
            }

            // The ON DELETE CASCADE constraint will handle deleting answers
            if ($this->pmoModel->delete($id_pmo)) {
                return ['success' => true, 'message' => 'PMO session deleted successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete the PMO session.'];
            }
        } catch (Exception $e) {
            log_message('error', '[PmoService] deletePmo Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during deletion: ' . $e->getMessage()];
        }
    }

    /**
     * Validate and sanitize the input data for saving.
     */
    private function validateAndSanitizeInput(array $data): array
    {
        $requiredFields = ['id_apl1', 'id_skema', 'id_asesor', 'tanggal_observasi'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return ['error' => "The field '{$field}' is required."];
            }
        }

        return [
            'id_apl1'   => trim(strip_tags($data['id_apl1'])),
            'id_skema'  => filter_var($data['id_skema'], FILTER_VALIDATE_INT),
            'id_asesor' => filter_var($data['id_asesor'], FILTER_VALIDATE_INT),
            'tanggal_observasi' => $data['tanggal_observasi'],
            'catatan'   => isset($data['catatan']) ? trim(strip_tags($data['catatan'])) : null,
            'jawaban'   => $data['jawaban'] ?? [],
        ];
    }

    /**
     * Validate the existence of foreign keys before saving.
     */
    private function validateForeignKeys(array $data): void
    {
        if (!$this->apl1Model->find($data['id_apl1'])) {
            throw new Exception("APL1 with ID {$data['id_apl1']} not found.");
        }
        // Add more checks for skema and asesor if needed
    }
}
