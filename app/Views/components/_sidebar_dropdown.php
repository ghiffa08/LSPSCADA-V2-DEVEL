<?php

/**
 * _sidebar_dropdown.php - Dropdown menu component
 *
 * @package    App
 * @subpackage Views/Components
 */
?>

<li class="dropdown <?= $isActive ? 'active' : '' ?>">
    <a href="#" class="nav-link has-dropdown">
        <?php if ($item->icon): ?>
            <i class="<?= esc($item->icon) ?>"></i>
        <?php endif; ?>
        <span><?= esc($item->title) ?></span>
    </a>
    <ul class="dropdown-menu">
        <?php foreach ($item->submenu as $subitem): ?>
            <li class="<?= $menuService->isActiveMenuItem($subitem) ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url($subitem->link) ?>">
                    <?= esc($subitem->title) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</li>