<?php

/**
 * _sidebar_item.php - Single sidebar menu item component
 *
 * @package    App
 * @subpackage Views/Components
 */
?>

<li class="<?= $isActive ? 'active' : '' ?>">
    <a class="nav-link" href="<?= site_url($item->link) ?>">
        <?php if ($item->icon): ?>
            <i class="<?= esc($item->icon) ?>"></i>
        <?php endif; ?>
        <span><?= esc($item->title) ?></span>
    </a>
</li>