<?php

/**
 * Sidebar Library - Handles sidebar rendering
 *
 * @package    App
 * @subpackage Libraries
 */

namespace App\Libraries;

use App\Services\MenuService;

class Sidebar
{
    protected $menuService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menuService = new MenuService();
    }

    /**
     * Render sidebar
     *
     * @param array $userPermissions User permissions
     * @return string Rendered sidebar HTML
     */
    public function render(array $userPermissions = [])
    {
        // Get menu structure
        $menuGroups = $this->menuService->getSidebarMenu($userPermissions);

        // Get view renderer
        $view = \Config\Services::renderer();

        // Render sidebar
        return $view->setVar('menuGroups', $menuGroups)
            ->setVar('menuService', $this->menuService)
            ->render('components/sidebar');
    }
}
