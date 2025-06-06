<?php

namespace App\DTOs;

/**
 * ApiResponseDTO
 * 
 * Standardized API response format for consistent responses
 */
class ApiResponseDTO
{
    public bool $success;
    public string $message;
    public mixed $data;
    public array $errors;
    public int $code;
    public ?string $csrf_hash;

    public function __construct(
        bool $success = true,
        string $message = '',
        mixed $data = null,
        array $errors = [],
        int $code = 200
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
        $this->code = $code;
        $this->csrf_hash = csrf_hash();
    }

    /**
     * Create success response
     *
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @return self
     */
    public static function success(string $message = 'Operation successful', mixed $data = null, int $code = 200): self
    {
        return new self(true, $message, $data, [], $code);
    }

    /**
     * Create error response
     *
     * @param string $message
     * @param array $errors
     * @param int $code
     * @param mixed $data
     * @return self
     */
    public static function error(string $message = 'Operation failed', array $errors = [], int $code = 400, mixed $data = null): self
    {
        return new self(false, $message, $data, $errors, $code);
    }

    /**
     * Create validation error response
     *
     * @param array $errors
     * @param string $message
     * @return self
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): self
    {
        return new self(false, $message, null, $errors, 422);
    }

    /**
     * Convert to array for JSON response
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors,
            'csrf_hash' => $this->csrf_hash
        ];
    }

    /**
     * Convert to JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
