<?php

class RegisterCard
{
    static $add_script;
    static function init() {
        add_shortcode('givex-registercard', array(__CLASS__, 'handle_shortcode'));
        add_action('init', array(__CLASS__, 'register_script'));
        add_action('wp_footer', array(__CLASS__, 'print_script'));
    }

    static function handle_shortcode($atts) {
        self::$add_script = true;
        $dir = plugin_dir_path( __FILE__ );
        include($dir."../views/register-card-view.php");
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

}

RegisterCard::init();