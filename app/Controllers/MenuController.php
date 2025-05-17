<?php

/**
 * Menu Controller - CRUD operations for menu management
 *
 * @package    App
 * @subpackage Controllers
 */

namespace App\Controllers;

use App\Models\MenuGroupModel;
use App\Models\MenuItemModel;
use App\Services\MenuService;

class MenuController extends BaseController
{
    protected $menuGroupModel;
    protected $menuItemModel;
    protected $menuService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menuGroupModel = new MenuGroupModel();
        $this->menuItemModel = new MenuItemModel();
        $this->menuService = new MenuService();
    }

    /**
     * Index - List menu groups
     *
     * @return string
     */
    public function index()
    {
        $data = [
            'groups' => $this->menuGroupModel->orderBy('order', 'ASC')->findAll()
        ];

        return view('menu/index', $data);
    }

    /**
     * Show menu items by group ID
     *
     * @param int $groupId
     * @return string
     */
    public function items($groupId)
    {
        $group = $this->menuGroupModel->find($groupId);

        if (!$group) {
            return redirect()->to('/menu')->with('error', 'Menu group not found');
        }

        $data = [
            'group' => $group,
            'items' => $this->menuItemModel->where('group_id', $groupId)
                ->orderBy('order', 'ASC')
                ->findAll()
        ];

        return view('menu/items', $data);
    }

    /**
     * Create new menu group
     *
     * @return string|ResponseInterface
     */
    public function createGroup()
    {
        if ($this->request->getMethod() === 'post') {
            $data = [
                'name' => $this->request->getPost('name'),
                'order' => $this->request->getPost('order'),
                'active' => $this->request->getPost('active') ? 1 : 0
            ];

            if ($this->menuGroupModel->save($data)) {
                // Clear menu cache
                $this->menuService->clearMenuCache();

                return redirect()->to('/menu')->with('success', 'Menu group created successfully');
            }

            return redirect()->back()->withInput()->with('errors', $this->menuGroupModel->errors());
        }

        return view('menu/group_form');
    }

    /**
     * Edit menu group
     *
     * @param int $id
     * @return string|ResponseInterface
     */
    public function editGroup($id)
    {
        $group = $this->menuGroupModel->find($id);

        if (!$group) {
            return redirect()->to('/menu')->with('error', 'Menu group not found');
        }

        if ($this->request->getMethod() === 'post') {
            $data = [
                'id' => $id,
                'name' => $this->request->getPost('name'),
                'order' => $this->request->getPost('order'),
                'active' => $this->request->getPost('active') ? 1 : 0
            ];

            if ($this->menuGroupModel->save($data)) {
                // Clear menu cache
                $this->menuService->clearMenuCache();

                return redirect()->to('/menu')->with('success', 'Menu group updated successfully');
            }

            return redirect()->back()->withInput()->with('errors', $this->menuGroupModel->errors());
        }

        return view('menu/group_form', ['group' => $group]);
    }

    /**
     * Delete menu group
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function deleteGroup($id)
    {
        $group = $this->menuGroupModel->find($id);

        if (!$group) {
            return redirect()->to('/menu')->with('error', 'Menu group not found');
        }

        if ($this->menuGroupModel->delete($id)) {
            // Clear menu cache
            $this->menuService->clearMenuCache();

            return redirect()->to('/menu')->with('success', 'Menu group deleted successfully');
        }

        return redirect()->to('/menu')->with('error', 'Failed to delete menu group');
    }

    /**
     * Create new menu item
     *
     * @param int $groupId
     * @return string|ResponseInterface
     */
    public function createItem($groupId)
    {
        $group = $this->menuGroupModel->find($groupId);

        if (!$group) {
            return redirect()->to('/menu')->with('error', 'Menu group not found');
        }

        if ($this->request->getMethod() === 'post') {
            $isHeader = $this->request->getPost('is_header') ? 1 : 0;

            $data = [
                'group_id' => $groupId,
                'title' => $this->request->getPost('title'),
                'is_header' => $isHeader,
                'order' => $this->request->getPost('order'),
                'active' => $this->request->getPost('active') ? 1 : 0
            ];

            // Add non-header specific fields
            if (!$isHeader) {
                $data['icon'] = $this->request->getPost('icon');
                $data['link'] = $this->request->getPost('link');
                $data['permission'] = $this->request->getPost('permission');
                $data['parent_id'] = $this->request->getPost('parent_id') ?: null;
            }

            if ($this->menuItemModel->save($data)) {
                // Clear menu cache
                $this->menuService->clearMenuCache();

                return redirect()->to('/menu/items/' . $groupId)->with('success', 'Menu item created successfully');
            }

            return redirect()->back()->withInput()->with('errors', $this->menuItemModel->errors());
        }

        $data = [
            'group' => $group,
            'parentOptions' => $this->menuItemModel->getParentOptions()
        ];

        return view('menu/item_form', $data);
    }
}
