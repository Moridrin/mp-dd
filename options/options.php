<?php

if (!defined('ABSPATH')) {
    exit;
}

#region Menu Items
function mp_dd_add_menu()
{
    add_menu_page('MP D&D', 'MP D&D', 'edit_posts', 'mp_dd_settings', 'mp_dd_general_settings_page');
    add_submenu_page('mp_dd_settings', 'General', 'General', 'edit_posts', 'mp_dd_settings');
}

add_action('admin_menu', 'mp_dd_add_menu', 9);
#endregion

#region Page Content
function mp_dd_general_settings_page()
{
    $active_tab = "general";
    if (isset($_GET['tab'])) {
        $active_tab = $_GET['tab'];
    }
    ?>
    <div class="wrap">
        <h1>Events Options</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=<?= esc_html($_GET['page']) ?>&tab=general" class="nav-tab <?= $active_tab == 'general' ? 'active' : '' ?>">General</a>
            <a href="?page=<?= esc_html($_GET['page']) ?>&tab=remove_recursive" class="nav-tab <?= $active_tab == 'remove_recursive' ? 'active' : '' ?>">Remove Recursive</a>
        </h2>
        <?php
        /** @noinspection PhpIncludeInspection */
        require_once $active_tab . '.php';
        ?>
    </div>
    <?php
}
#endregion
