<?php

namespace App\Requests;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Validation\Validation;

/**
 * Base Request Class
 * 
 * Common functionality for all request classes
 * 
 * @package App\Requests
 */
abstract class BaseRequest
{
    protected IncomingRequest $request;
    protected Validation $validation;
    protected array $rules = [];
    protected array $messages = [];

    public function __construct()
    {
        $this->request = service('request');
        $this->validation = service('validation');
    }

    /**
     * Validate request data
     * 
     * @return bool
     */
    abstract public function validate(): bool;

    /**
     * Get validated data
     * 
     * @return array
     */
    abstract public function getValidatedData(): array;

    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->validation->getErrors();
    }

    /**
     * Get input data from request
     * 
     * @return array
     */
    protected function getInputData(): array
    {
        $method = $this->request->getMethod();

        if ($method === 'get') {
            return $this->request->getGet() ?? [];
        }

        return $this->request->getPost() ?? [];
    }

    /**
     * Sanitize input value
     * 
     * @param mixed $value
     * @return mixed
     */
    protected function sanitizeInput($value)
    {
        if (is_string($value)) {
            return trim(strip_tags($value));
        }

        return $value;
    }

    /**
     * Get specific field from input
     * 
     * @param string $field
     * @param mixed $default
     * @return mixed
     */
    protected function getField(string $field, $default = null)
    {
        $data = $this->getInputData();
        return $data[$field] ?? $default;
    }
}
