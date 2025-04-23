<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{


    public function total_asesi()
    {
        return $this->db->table('apl1')->countAll();
    }
    public function total_admin()
    {
        return $this->db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->where('auth_groups.name', 'Admin')
            ->countAllResults();
    }

    public function total_asesor()
    {
        return $this->db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->where('auth_groups.name', 'Asesor')
            ->countAllResults();
    }
}
