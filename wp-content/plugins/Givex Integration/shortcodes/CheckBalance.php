<?php

class CheckBalance
{
    static $add_script;
    static $is_givex;
    static function init() {
        add_shortcode('givex-checkbalance', array(__CLASS__, 'handle_shortcode'));
        add_action('init', array(__CLASS__, 'register_script'));
        add_action('wp_footer', array(__CLASS__, 'print_script'));
    }

    static function handle_shortcode($atts) {
        self::$add_script = true;

        $is_givex = self::is_givex_active();

        $dir = plugin_dir_path( __FILE__ );
        include($dir."../views/check-balance-view.php");
    }

    static function register_script() {
        wp_register_script('givex-ajax', plugins_url('../js/givex-ajax.js', __FILE__), array('jquery'), '1.0', true);
        wp_localize_script('givex-ajax', 'givex_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    }
    static function print_script() {
        if ( ! self::$add_script )
            return;
        wp_print_scripts('givex-ajax');
    }

    static function is_givex_active(){
        global $post;
        $pattern = get_shortcode_regex();

        if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
            && array_key_exists( 2, $matches )
            && in_array( 'givex-checkbalance', $matches[2] ) )
        {
            return true;
        }else{
            return false;
        }

    }

}

CheckBalance::init();