<?php

/**
 * Menu Service - Handles menu data processing and permission checks
 *
 * @package    App
 * @subpackage Services
 */

namespace App\Services;

use App\Models\MenuGroupModel;
use App\Models\MenuItemModel;

class MenuService
{
    protected $menuGroupModel;
    protected $menuItemModel;
    protected $menuCache = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menuGroupModel = new MenuGroupModel();
        $this->menuItemModel = new MenuItemModel();
    }

    /**
     * Get sidebar menu with permission filtering
     *
     * @param array $userPermissions User permissions array
     * @return array Menu structure
     */
    public function getSidebarMenu(array $userPermissions = [])
    {
        // Get menu from cache if available
        if ($this->menuCache !== null) {
            return $this->filterByPermissions($this->menuCache, $userPermissions);
        }

        // Get menu from database
        $menuGroups = $this->menuGroupModel->getActiveGroupsWithItems();

        // Cache the menu
        $this->menuCache = $menuGroups;

        // Filter by permissions
        return $this->filterByPermissions($menuGroups, $userPermissions);
    }

    /**
     * Filter menu items by user permissions
     *
     * @param array $menuGroups Menu groups data
     * @param array $userPermissions User permissions
     * @return array Filtered menu
     */
    protected function filterByPermissions(array $menuGroups, array $userPermissions)
    {
        // If no permissions provided or user is admin, return all menu items
        if (empty($userPermissions) || in_array('admin', $userPermissions)) {
            return $menuGroups;
        }

        $filteredMenu = [];

        foreach ($menuGroups as $groupId => $items) {
            $filteredItems = [];

            foreach ($items as $item) {
                // If it's a header, always include it
                if ($item->is_header) {
                    $filteredItems[] = $item;
                    continue;
                }

                // Check item permission
                if (empty($item->permission) || in_array($item->permission, $userPermissions)) {
                    // If it has submenu, filter that too
                    if (isset($item->submenu)) {
                        $filteredSubmenu = [];

                        foreach ($item->submenu as $subitem) {
                            if (empty($subitem->permission) || in_array($subitem->permission, $userPermissions)) {
                                $filteredSubmenu[] = $subitem;
                            }
                        }

                        // Only include parent if it has visible children
                        if (!empty($filteredSubmenu)) {
                            $item->submenu = $filteredSubmenu;
                            $filteredItems[] = $item;
                        }
                    } else {
                        $filteredItems[] = $item;
                    }
                }
            }

            // Only include group if it has visible items
            if (!empty($filteredItems)) {
                $filteredMenu[$groupId] = $filteredItems;
            }
        }

        return $filteredMenu;
    }

    /**
     * Check if a menu item is active based on current URL
     *
     * @param object $item Menu item
     * @return bool
     */
    public function isActiveMenuItem($item)
    {
        $currentUrl = current_url(true)->getPath();

        // Direct match
        if (isset($item->link) && $item->link && $item->link != '#') {
            return rtrim($currentUrl, '/') == rtrim($item->link, '/');
        }

        // Check submenu items
        if (isset($item->submenu)) {
            foreach ($item->submenu as $subitem) {
                if ($subitem->link && rtrim($currentUrl, '/') == rtrim($subitem->link, '/')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Clear menu cache
     */
    public function clearMenuCache()
    {
        $this->menuCache = null;
    }
}
