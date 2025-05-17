<?php

/**
 * Menu Group Model
 *
 * @package    App
 * @subpackage Models
 */

namespace App\Models;

use CodeIgniter\Model;

class MenuGroupModel extends Model
{
    protected $table         = 'menu_groups';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['name', 'order', 'active'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $validationRules = [
        'name'  => 'required|min_length[3]|max_length[100]',
        'order' => 'required|integer',
        'active' => 'required|integer|in_list[0,1]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Get active menu groups with their items
     *
     * @return array
     */
    public function getActiveGroupsWithItems()
    {
        $menuItemModel = new MenuItemModel();
        $groups = $this->where('active', 1)
            ->orderBy('order', 'ASC')
            ->findAll();

        $result = [];
        foreach ($groups as $group) {
            $result[$group->id] = $menuItemModel->getItemsByGroupId($group->id);
        }

        return $result;
    }
}
