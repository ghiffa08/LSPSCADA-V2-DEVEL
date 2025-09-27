<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait; // Tambahkan ini

class InstansiModel extends Model
{
    use DataTableTrait; // Tambahkan ini

    protected $table            = 'instansi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $allowedFields    = ['nama_instansi'];
    protected $useTimestamps    = true;

    // Kolom yang dapat dicari untuk DataTable
    protected array $dataTableSearchFields = ['instansi.nama_instansi'];
}
