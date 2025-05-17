<?php

/**
 * Menu Item Model
 *
 * @package App
 * @subpackage Models
 */

namespace App\Models;

use CodeIgniter\Model;

class MenuItemModel extends Model
{
    protected $table = 'menu_items';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'group_id',
        'parent_id',
        'title',
        'icon',
        'link',
        'is_header',
        'order',
        'permission',
        'active'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[100]',
        'group_id' => 'required|integer',
        'order' => 'required|integer',
        'active' => 'required|integer|in_list[0,1]',
        'is_header' => 'required|integer|in_list[0,1]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Get menu items by group ID
     *
     * @param int $groupId
     * @return array
     */
    public function getItemsByGroupId(int $groupId)
    {
        // Get all top-level items (headers and direct links)
        $items = $this->where('group_id', $groupId)
            ->where('parent_id', null)
            ->where('active', 1)
            ->orderBy('order', 'ASC')
            ->findAll();

        // Process each item
        foreach ($items as $key => $item) {
            // If it's a parent item with submenu, get its children
            if (!$item->is_header && $item->link == '#') {
                $items[$key]->submenu = $this->getSubmenuItems($item->id);
            }
        }

        return $items;
    }

    /**
     * Get submenu items for a parent item
     *
     * @param int $parentId
     * @return array
     */
    public function getSubmenuItems(int $parentId)
    {
        return $this->where('parent_id', $parentId)
            ->where('active', 1)
            ->orderBy('order', 'ASC')
            ->findAll();
    }

    /**
     * Get all parent menu items (for dropdowns in form)
     *
     * @return array
     */
    public function getParentOptions()
    {
        $parents = $this->where('parent_id', null)
            ->where('is_header', 0)
            ->where('link', '#')
            ->findAll();

        $options = ['' => '-- None --'];
        foreach ($parents as $parent) {
            $options[$parent->id] = $parent->title;
        }

        return $options;
    }

    /**
     * Check if a menu item has children
     *
     * @param int $itemId
     * @return bool
     */
    public function hasChildren(int $itemId)
    {
        return $this->where('parent_id', $itemId)->countAllResults() > 0;
    }
}
