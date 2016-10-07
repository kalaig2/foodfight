<?php
/**
 * Plugin Name: WooCommerce No Shipping Message
 * Description: Replaces "No shipping methods available ..." message with a provided text. Look for Settings -> Shipping -> No-shipping Message.
 * Version: 1.1.0
 * Author: dangoodman
 * Author URI: http://tablerateshipping.com
 * Requires at least: 4.1
 * Tested up to: 4.3
 */

call_user_func(function()
{
    define('WNSM_OPTION_ID', 'wnsm_no_shipping_message');

    if ($message = get_option(WNSM_OPTION_ID)) {
        foreach (array('woocommerce_cart_no_shipping_available_html', 'woocommerce_no_shipping_available_html') as $hook) {
            add_filter($hook, function() use($message) {
                return __($message, 'wc-no-shipping-methods');
            });
        }
    }

    if (is_admin()) {
        add_filter('woocommerce_shipping_settings', function ($settings) {
            $noShippingMessageField = array(
                'id' => WNSM_OPTION_ID,
                'type' => 'textarea',
                'title' => __('No-shipping Message', 'wc-no-shipping-methods'),
                'desc' => __('Message shown to the customer when no shipping methods available', 'wc-no-shipping-methods'),
                'default' => '',
                'css' => 'width:350px; height: 65px;',
            );

            $maybeSectionEnd = end($settings);
            $newFieldPosition = @$maybeSectionEnd['type'] == 'sectionend' ? -1 : count($settings);
            array_splice($settings, $newFieldPosition, 0, array($noShippingMessageField));

            return $settings;
        });

        add_filter('plugin_action_links_' . plugin_basename(wp_normalize_path(__FILE__)), function($links) {
            $settingsUrl = admin_url('admin.php?page=wc-settings&tab=shipping#' . urlencode(WNSM_OPTION_ID));
            array_unshift($links, '<a href="'.esc_html($settingsUrl).'">'.__('Edit message', 'wc-no-shipping-methods').'</a>');
            return $links;
        });
    }
});