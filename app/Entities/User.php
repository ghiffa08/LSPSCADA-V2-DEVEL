<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use Myth\Auth\Entities\User as MythUser;

class User extends MythUser
{
    protected $dates = ['created_at', 'updated_at', 'reset_at', 'reset_expires', 'force_pass_reset'];
    protected $casts = [
        'active'           => 'boolean',
        'force_pass_reset' => 'boolean',
    ];

    /**
     * Returns whether the user has the specified role or not
     *
     * @param string $role Role name
     * @return boolean
     */
    public function hasRole(string $role): bool
    {
        if (empty($this->roles)) {
            $this->roles = model('GroupUserModel')->getRolesByUserId($this->id);
        }

        return in_array(strtolower($role), array_map('strtolower', $this->roles ?? []));
    }

    /**
     * Returns whether the user is an admin
     *
     * @return boolean
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Returns whether the user is an asesor
     *
     * @return boolean
     */
    public function isAsesor(): bool
    {
        return $this->hasRole('asesor');
    }

    /**
     * Returns whether the user is an asesi
     *
     * @return boolean
     */
    public function isAsesi(): bool
    {
        return $this->hasRole('asesi');
    }

    /**
     * Get Asesi data if user is an asesi
     * 
     * @return \App\Entities\Asesi|null
     */
    public function getAsesiData()
    {
        if (!$this->isAsesi()) {
            return null;
        }

        $asesiModel = model('AsesiModel');
        $data = $asesiModel->where('user_id', $this->id)->first();

        if ($data) {
            return new Asesi($data);
        }

        return null;
    }

    /**
     * Get Asesor data if user is an asesor
     * 
     * @return \App\Entities\Asesor|null
     */
    public function getAsesorData()
    {
        if (!$this->isAsesor()) {
            return null;
        }

        $asesorModel = model('AsesorModel');
        $data = $asesorModel->where('user_id', $this->id)->first();

        if ($data) {
            return new Asesor($data);
        }

        return null;
    }

    /**
     * Override id property agar selalu mengembalikan id
     */
    public function getId()
    {
        return $this->attributes['id'] ?? null;
    }
    public function getIdUser()
    {
        return $this->attributes['id'] ?? null;
    }
}
