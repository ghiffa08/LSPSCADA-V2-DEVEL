<?php

namespace App\Models;

use CodeIgniter\Model;

class AsesorInstansiModel extends Model
{
    protected $table            = 'asesor_instansi';
    protected $primaryKey       = 'asesor_id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['asesor_id', 'instansi_id'];
}
