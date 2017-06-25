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
    echo '//TODO Add some settings';
}
#endregion
