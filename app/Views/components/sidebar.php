<?php

/**
 * sidebar.php - Main sidebar component
 *
 * @package    App
 * @subpackage Views/Components
 */
?>

<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <!-- Sidebar Brand -->
        <div class="sidebar-brand">
            <a href="<?= site_url('/') ?>"><?= esc(config('App')->appName) ?></a>
        </div>

        <!-- Sidebar Brand for Small Screens -->
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="<?= site_url('/') ?>"><?= esc(substr(config('App')->appName, 0, 3)) ?></a>
        </div>

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <?php foreach ($menuGroups as $group) : ?>
                <?php foreach ($group as $item) : ?>
                    <?php
                    // Get the required permissions for the menu item.
                    // If the permission property is empty or not set, it's a public item.
                    $requiredPermissions = !empty($item->permission) ? explode(',', $item->permission) : [];

                    // Check if the menu item should be displayed.
                    // It's displayed if:
                    // 1. No permissions are required (it's public).
                    // 2. The logged-in user is in one of the required groups.
                    if (empty($requiredPermissions) || in_groups($requiredPermissions)) :
                    ?>
                        <?php if ($item->is_header) : ?>
                            <?= view('components/_sidebar_header', ['item' => $item]) ?>

                        <?php elseif (isset($item->submenu) && !empty($item->submenu)) : ?>
                            <?= view('components/_sidebar_dropdown', [
                                'item' => $item,
                                'isActive' => $menuService->isActiveMenuItem($item)
                            ]) ?>

                        <?php else : ?>
                            <?= view('components/_sidebar_item', [
                                'item' => $item,
                                'isActive' => $menuService->isActiveMenuItem($item)
                            ]) ?>
                        <?php endif; ?>

                    <?php endif; // End of permission check 
                    ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </aside>
</div>