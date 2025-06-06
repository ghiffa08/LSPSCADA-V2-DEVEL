<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * PengajuanAsesmen Entity
 * 
 * Represents the assessment application entity with proper typing and business logic
 */
class PengajuanAsesmenEntity extends Entity
{
    protected $datamap = [
        'id'         => 'id_apl1',
        'asesi_id'   => 'id_asesi',
        'asesmen_id' => 'id_asesmen'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'id_apl1'        => 'string',
        'id_asesi'       => 'string',
        'id_asesmen'     => 'integer',
        'status'         => 'string',
        'validator'      => '?string',
        'email_validasi' => '?string',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => '?datetime'
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all valid status options
     *
     * @return array
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED
        ];
    }

    /**
     * Check if the application is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the application is approved
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the application is completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get status display name
     *
     * @return string
     */
    public function getStatusDisplay(): string
    {
        $statusMap = [
            self::STATUS_PENDING   => 'Menunggu Persetujuan',
            self::STATUS_APPROVED  => 'Disetujui',
            self::STATUS_REJECTED  => 'Ditolak',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan'
        ];

        return $statusMap[$this->status] ?? 'Status Tidak Dikenal';
    }

    /**
     * Set validator for the application
     *
     * @param string|null $validator
     * @return self
     */
    public function setValidator(?string $validator): self
    {
        $this->attributes['validator'] = $validator;
        return $this;
    }

    /**
     * Set validation email
     *
     * @param string|null $email
     * @return self
     */
    public function setValidationEmail(?string $email): self
    {
        $this->attributes['email_validasi'] = $email;
        return $this;
    }

    /**
     * Approve the application
     *
     * @param string $validator
     * @param string|null $email
     * @return self
     */
    public function approve(string $validator, ?string $email = null): self
    {
        $this->attributes['status'] = self::STATUS_APPROVED;
        $this->attributes['validator'] = $validator;
        $this->attributes['email_validasi'] = $email;
        $this->attributes['updated_at'] = Time::now();

        return $this;
    }

    /**
     * Reject the application
     *
     * @param string $validator
     * @param string|null $email
     * @return self
     */
    public function reject(string $validator, ?string $email = null): self
    {
        $this->attributes['status'] = self::STATUS_REJECTED;
        $this->attributes['validator'] = $validator;
        $this->attributes['email_validasi'] = $email;
        $this->attributes['updated_at'] = Time::now();

        return $this;
    }

    /**
     * Complete the application
     *
     * @return self
     */
    public function complete(): self
    {
        $this->attributes['status'] = self::STATUS_COMPLETED;
        $this->attributes['updated_at'] = Time::now();

        return $this;
    }
}
