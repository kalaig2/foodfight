<?php
/*
	Plugin Name: USPS WooCommerce Shipping
	Plugin URI: http://www.wooforce.com
	Description: Obtain real time Shipping Rates, Print Shipping labels and Track Shipment via the USPS Shipping API.
	Version: 2.8.4
	Author: WooForce
	Author URI: http://www.wooforce.com
	https://www.usps.com/webtools/htm/Rate-Calculators-v1-5.htm
	https://www.usps.com/business/web-tools-apis/delivery-confirmation-domestic-shipping-label-api.htm
*/

function wf_usps_pre_activation_check(){
	//check if basic version is there
	if ( is_plugin_active('woo-usps-shipping-method/usps-woocommerce-shipping.php') ){
        deactivate_plugins( basename( __FILE__ ) );
		wp_die( __("Oops! You tried installing the premium version without deactivating and deleting the basic version. Kindly deactivate and delete USPS(Basic) Woocommerce Extension and then try again", "wf-usps-woocommerce-shipping" ), "", array('back_link' => 1 ));
	}
}
register_activation_hook( __FILE__, 'wf_usps_pre_activation_check' );

define("WF_USPS_ID", "wf_shipping_usps");

/**
 * Check if WooCommerce is active
 */
if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {

	/**
	 * WC_USPS class
	 */
	 
	if(! class_exists('USPS_WooCommerce_Shipping') ){
	
		class USPS_WooCommerce_Shipping {

			/**
			 * Constructor
			 */
			public function __construct() {
				add_action( 'init', array( $this, 'init' ) );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
				add_action( 'woocommerce_shipping_init', array( $this, 'shipping_init' ) );
				add_filter( 'woocommerce_shipping_methods', array( $this, 'add_method' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			}
			
			/**
			 * Localisation
			 */
			public function init() {
				if ( is_admin() ) {
					// WF Print Shipping Label.
					include_once ( 'includes/class-wf-shipping-usps-admin.php' );
					
					//include api manager
					include_once ( 'includes/wf_api_manager/wf-api-manager-config.php' );
					//load_plugin_textdomain( 'wf-usps-woocommerce-shipping', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
				}
			}

			/**
			 * Plugin page links
			 */
			public function plugin_action_links( $links ) {
				$plugin_links = array(
					'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wf_shipping_usps' ) . '">' . __( 'Settings', 'wf-usps-woocommerce-shipping' ) . '</a>',
					'<a href="http://support.wooforce.com">' . __( 'Support', 'wf-usps-woocommerce-shipping' ) . '</a>',
				);
				return array_merge( $plugin_links, $links );
			}

			/**
			 * Load gateway class
			 */
			public function shipping_init() {
				include_once( 'includes/class-wf-shipping-usps.php' );
			}

			/**
			 * Add method to WC
			 */
			public function add_method( $methods ) {
				$methods[] = 'WF_Shipping_USPS';
				return $methods;
			}

			/**
			 * Enqueue scripts
			 */
			public function scripts() {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'common-script', plugins_url( '/resources/js/wf_common.js', __FILE__ ), array( 'jquery' ) );
			}
		}
		new USPS_WooCommerce_Shipping();
	}
}
 