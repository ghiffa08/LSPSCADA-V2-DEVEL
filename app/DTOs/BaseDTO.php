<?php

namespace App\DTOs;

/**
 * Base DTO Class
 * 
 * Provides common functionality for all DTOs
 */
abstract class BaseDTO
{
    /**
     * Parse JSON field safely
     */
    protected function parseJsonField(string $json): array
    {
        try {
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Get validation rules (to be implemented by child classes)
     */
    public function validationRules(): array
    {
        return [];
    }

    /**
     * Get validation messages (to be implemented by child classes)
     */
    public function validationMessages(): array
    {
        return [];
    }
    /**
     * Validate the DTO data
     */
    public function validate(): array
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->validationRules());

        $customMessages = $this->validationMessages();
        if (!empty($customMessages)) {
            $validation->setRules($this->validationRules(), $customMessages);
        }

        $data = $this->toArray();

        if (!$validation->run($data)) {
            return $validation->getErrors();
        }

        return [];
    }
}
