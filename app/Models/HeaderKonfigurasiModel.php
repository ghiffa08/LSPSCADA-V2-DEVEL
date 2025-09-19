<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class HeaderKonfigurasiModel extends Model
{
    use DataTableTrait;

    protected $table            = 'header_konfigurasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $allowedFields    = [
        'assessor_id',
        'nama_kop',
        'logo',
        'logo_width',
        'title',
        'header_string',
        'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected array $dataTableSearchFields = ['header_konfigurasi.nama_kop', 'header_konfigurasi.title', 'users.nama_lengkap'];

    /**
     * Menerapkan join ke tabel asesor dan user untuk DataTable.
     */
    protected function applyDataTableJoins(object $builder): object
    {
        return $builder
            ->join('asesor', 'asesor.id_asesor = header_konfigurasi.assessor_id', 'left')
            ->join('users', 'users.id = asesor.id_user', 'left');
    }

    /**
     * Memilih kolom yang dibutuhkan untuk DataTable.
     */
    protected function applyDataTableSelects(object $builder): object
    {
        return $builder->select('header_konfigurasi.*, users.nama_lengkap as assessor_name');
    }
}
