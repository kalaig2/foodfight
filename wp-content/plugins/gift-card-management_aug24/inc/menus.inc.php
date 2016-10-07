<?php

function gc_add_admin_menu() {
    add_submenu_page('options-general.php', 'Add gift card', 'Add gift card', 'manage_options', __FILE__ . '_display_contents', 'gc_display_contents');
}

?>