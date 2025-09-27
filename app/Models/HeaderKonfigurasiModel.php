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
        'instansi_id',
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

    // Kolom yang dapat dicari untuk DataTable
    protected array $dataTableSearchFields = ['header_konfigurasi.nama_kop', 'header_konfigurasi.title', 'instansi.nama_instansi'];

    /**
     * Menerapkan join ke tabel instansi untuk DataTable.
     */
    protected function applyDataTableJoins(object $builder): object
    {
        return $builder
            ->join('instansi', 'instansi.id = header_konfigurasi.instansi_id', 'left');
    }

    /**
     * Memilih kolom yang dibutuhkan untuk DataTable.
     */
    protected function applyDataTableSelects(object $builder): object
    {
        return $builder->select('header_konfigurasi.*, instansi.nama_instansi as instansi_name');
    }
}
