<?php

namespace App\Exceptions;

use Exception;

/**
 * Observasi Exception
 * 
 * Custom exception for observasi operations
 * 
 * @package App\Exceptions
 */
class ObservasiException extends Exception
{
    private array $context;

    /**
     * Constructor
     * 
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @param array $context Additional context data
     */
    public function __construct(string $message = '', int $code = 0, Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get exception context
     * 
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set exception context
     * 
     * @param array $context
     * @return self
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Create validation exception
     * 
     * @param array $errors
     * @return self
     */
    public static function validation(array $errors): self
    {
        return new self('Validation failed', 422, null, ['errors' => $errors]);
    }

    /**
     * Create not found exception
     * 
     * @param string $resource
     * @return self
     */
    public static function notFound(string $resource = 'Resource'): self
    {
        return new self($resource . ' not found', 404);
    }

    /**
     * Create unauthorized exception
     * 
     * @param string $message
     * @return self
     */
    public static function unauthorized(string $message = 'Unauthorized access'): self
    {
        return new self($message, 401);
    }

    /**
     * Create database exception
     * 
     * @param string $operation
     * @param Exception|null $previous
     * @return self
     */
    public static function database(string $operation, Exception $previous = null): self
    {
        return new self('Database operation failed: ' . $operation, 500, $previous);
    }
}
