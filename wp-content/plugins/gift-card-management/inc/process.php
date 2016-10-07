<?php

function process_jk_youtube_options() {
    if (!current_user_can('manage_options')) {
        wp_die('You are not allowed to be on this page.');
    }
    // Check that nonce field
    check_admin_referer('jk_op_verify');

    $options = get_option('jk_op_array');

    if (isset($_POST['youtube_link'])) {
        $options['jk_op_yt_username'] = sanitize_text_field($_POST['youtube_link']);
    }

    update_option('jk_op_array', $options);

    wp_redirect(admin_url('options-general.php?page=wpHTMLDisplayEx/inc/menus.inc.php_display_contents&m=1'));
    exit;
}

?>