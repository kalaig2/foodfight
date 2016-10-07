<?php

/*
 * Plugin Name: Gift Card Manegement
 * Version: 1.0
 *
 */

// Set up our WordPress Plugin
function gc_check_WP_ver() {
    if (version_compare(get_bloginfo('version'), '3.1', '<')) {
        wp_die("You must update WordPress to use this plugin!");
    }

    if (get_option('gc_op_array') === false) {
        $options_array['gc_op_yt_username'] = '';
        $options_array['gc_op_version'] = '1';
        add_option('gc_op_array', $options_array);
    }
}

register_activation_hook(__FILE__, 'gc_check_WP_ver');

// Include or Require any files
include('inc/process.php');
include('inc/display-options.inc.php');
include('inc/menus.inc.php');

// Action & Filter Hooks
add_action('admin_menu', 'gc_add_admin_menu');

add_action('admin_post_gc_save_youtube_option', 'process_gc_youtube_options');

function my_enqueue($hook) {
    if (strpos($hook, "gift-card-management")) {
        wp_enqueue_style('plugin-style', plugins_url('css/style.css', __FILE__));
        wp_enqueue_script('gc_mgmt', plugins_url('js/gc_mgmt.js', __FILE__), array('jquery'), '1.0', true);
    }
}

add_action('admin_enqueue_scripts', 'my_enqueue');


add_action('wp_ajax_add_card', 'ajax_add_card');

function ajax_add_card() {
    global $wpdb;
    $card_number = $_POST['gc_number'];
    $table_name = $wpdb->prefix . "gift_cards";
    $result = $wpdb->get_row("SELECT `card_number` FROM $table_name WHERE `card_number` = '$card_number'");
    if (empty($result)) {
        $date = date('Y-m-d h:i:s', time());
        echo $date;
        $wpdb->insert($table_name, array('card_number' => $card_number, 'created_on' => $date, 'updated_on' => $date));
        exit("success");
    } else {
        exit("error");
    }
}

add_action('wp_ajax_list_cards', 'ajax_list_cards');

function ajax_list_cards() {
    global $wpdb;
    $table_name = $wpdb->prefix . "gift_cards";
    $results = $wpdb->get_results("SELECT `card_number`,`price`,`purchased_user` FROM $table_name");
    echo json_encode($results);
    exit();
}

?>
