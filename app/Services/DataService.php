<?php

namespace App\Services;

use Exception;
use Config\Database;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\BaseConnection;

/**
 * DataService - A reusable service to handle CRUD operations
 * 
 * This service handles saving and updating data with validation
 * and database transactions for consistent data integrity.
 */
class DataService
{
    /**
     * Database connection instance
     *
     * @var BaseConnection
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Save or update data
     *
     * @param string $modelName The fully qualified model class name
     * @param array $data The data to save/update
     * @param string|null $primaryKey The primary key field name (will be auto-detected if null)
     * @param callable|null $beforeSave Function to run before saving (receives data array)
     * @param callable|null $afterSave Function to run after saving (receives saved data and id)
     * @return array Response with status and message
     */
    public function save(
        string $modelName,
        array $data,
        ?string $primaryKey = null,
        ?callable $beforeSave = null,
        ?callable $afterSave = null
    ): array {
        $model = new $modelName();

        // If primary key not provided, get it from the model
        if ($primaryKey === null) {
            $primaryKey = $model->primaryKey;
        }

        // Check if this is an update or insert
        $isUpdate = false;
        $id = null;

        if (isset($data[$primaryKey]) && !empty($data[$primaryKey])) {
            $isUpdate = true;
            $id = $data[$primaryKey];
        }

        // Start transaction
        $this->db->transStart();

        try {
            // Process data before saving if callback provided
            if ($beforeSave !== null) {
                $data = $beforeSave($data);
            }

            // Set timestamps if the model uses them
            if (property_exists($model, 'useTimestamps') && $model->useTimestamps) {
                $now = date('Y-m-d H:i:s');
                if ($isUpdate) {
                    $data['updated_at'] = $now;
                } else {
                    $data['created_at'] = $now;
                    $data['updated_at'] = $now;
                }
            }

            // Validate the data
            if (!$model->validate($data)) {
                return [
                    'status' => false,
                    'code' => 422,
                    'message' => 'Validation failed',
                    'errors' => $model->errors()
                ];
            }

            // Save data
            if ($isUpdate) {
                $model->update($id, $data);
            } else {
                $id = $model->insert($data, true);
            }

            // Process data after saving if callback provided
            if ($afterSave !== null) {
                $afterSave($data, $id);
            }

            // Complete transaction
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'status' => false,
                    'code' => 500,
                    'message' => 'Database transaction failed'
                ];
            }

            return [
                'status' => true,
                'code' => 200,
                'message' => $isUpdate ? 'Data updated successfully' : 'Data saved successfully',
                'id' => $id
            ];
        } catch (Exception $e) {
            $this->db->transRollback();

            return [
                'status' => false,
                'code' => 500,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get validation errors formatted for display
     *
     * @param array $errors Array of errors from model validation
     * @return string HTML formatted error messages
     */
    public function formatErrors(array $errors): string
    {
        $html = '<ul class="list-unstyled">';
        foreach ($errors as $field => $message) {
            $html .= "<li>{$message}</li>";
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Send JSON response
     *
     * @param array $data Data to be sent
     * @param int $code HTTP status code
     * @return ResponseInterface
     */
    public function response(array $data, int $code = 200): ResponseInterface
    {
        return Services::response()
            ->setStatusCode($code)
            ->setJSON($data);
    }
}
