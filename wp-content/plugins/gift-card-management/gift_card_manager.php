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
        wp_enqueue_style('bootstrap_css', plugins_url('css/bootstrap_css.css', __FILE__));
        wp_enqueue_script('bootstrap',plugins_url('js/bootstrap.js', __FILE__), array('jquery'), '3.3.7', true);
         wp_enqueue_script('gc_mgmt', plugins_url('js/gc_mgmt.js', __FILE__), array('jquery'), '1.0', true);
    }
}

add_action('admin_enqueue_scripts', 'my_enqueue');


add_action('wp_ajax_add_card', 'ajax_add_card');

function ajax_add_card() {
    global $wpdb;
    $card_number = $_POST['gc_number'];
    $no_of_card = $_POST['no_of_card'];
    $card_type = $_POST['card_type'];
    $table_name = $wpdb->prefix . "gift_cards";
    $result = $wpdb->get_row("SELECT `card_number` FROM $table_name WHERE `card_number` = '$card_number'");
    if (empty($result)) {
      for($i = 1;$i <= $no_of_card;$i++){
            $date = date('Y-m-d h:i:s', time());
            //$parts = str_split($str, 6);
            //print_r($parts);
            //$card_new = (string)number_format($card_number,0,'','');
            $result_new = $wpdb->get_row("SELECT `card_number` FROM $table_name WHERE `card_number` = '$card_number'");
            if (empty($result_new)) {
            $res = $wpdb->insert($table_name, array('card_number' => $card_number, 'card_type' =>$card_type,'created_on' => $date, 'updated_on' => $date));
            $str_arr = str_split($card_number, 5);
            $card_number_str = "";
            for ($j = 0;$j < count($str_arr);$j++) {
              if ($j != count($str_arr) - 1) {
                $card_number_str .= (string) $str_arr[$j];
              } else {
                $existing_length = strlen($str_arr[$j]);
                $new_card = (int) $str_arr[$j] + 1;
                $new_card = (string) $new_card;
                if ($existing_length >  strlen($new_card)) {
                  $new_card = str_pad($new_card,$existing_length,"0",STR_PAD_LEFT);
                } 
                $card_number_str .= $new_card;
              }
            }
            $card_number = $card_number_str;
            }
        }
        if($no_of_card <= 1)
            exit("Card has successfully Added.");
        else
            exit("Card's has successfully Added.");
    } else {
        exit("There is an error");
    }
}

add_action('wp_ajax_edit_card', 'ajax_edit_card');

function ajax_edit_card() {
    global $wpdb;
    $card_number = $_POST['card_num'];
    $price = $_POST['price'];
    $card_type = $_POST['card_type'];
    $id = $_POST['id'];
    $date = date('Y-m-d h:i:s', time());
    $table_name = $wpdb->prefix . "gift_cards";
    $result = $wpdb->update(
        $table_name,
        array(
            'card_number' => $card_number,  // string
            'price'       => $price,
            'card_type'   =>$card_type,
            'updated_on'  => $date
        ),
        array( 'id' => $id )
        /*array(
            '%s',   // value1
            '%d',    // value2
            '%s'
        )*/
    );
    if (is_numeric($result)) {
        exit("Card has successfully updated.");
    } else {
        exit("There is an error");
    }
}

add_action('wp_ajax_list_cards', 'ajax_list_cards');

function ajax_list_cards() {
    global $wpdb;
    $table_name = $wpdb->prefix . "gift_cards";
    $results = $wpdb->get_results("SELECT `id`,`card_number`,`card_type`,`price`,`active`,`givex_status` FROM $table_name");
    echo json_encode($results);
    exit();
}

add_action('wp_ajax_delete_card', 'ajax_delete_card');

function ajax_delete_card() {
    global $wpdb;
    $table_name = $wpdb->prefix . "gift_cards";
    $id = $_POST['id'];
    $result = $wpdb->delete(
        $table_name,
        array( 'id' => $id )
    );
    if ($result) {
        exit("Card has successfully deleted.");
    } else {
        exit("There is an error");
    }
}

?>
