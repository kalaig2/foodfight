<?php

/**
 * WF_Shipping_USPS class.
 *
 * @extends WC_Shipping_Method
 */
class WF_Shipping_USPS extends WC_Shipping_Method {

	private $endpoint        = 'http://production.shippingapis.com/shippingapi.dll';
	//private $endpoint        = 'http://stg-production.shippingapis.com/ShippingApi.dll';
	private $default_user_id = '570CYDTE1766';
	private $domestic        = array( "US", "PR", "VI" );
	private $found_rates;
	private $package_info;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = WF_USPS_ID;
		$this->method_title       = __( 'USPS', 'wf-usps-woocommerce-shipping' );
		$this->method_description = __( 'The <strong>USPS</strong> extension obtains rates dynamically from the USPS API during cart/checkout.', 'wf-usps-woocommerce-shipping' );
		$this->services           = include( 'data-wf-services.php' );
		$this->flat_rate_boxes    = include( 'data-wf-flat-rate-boxes.php' );
		$this->flat_rate_pricing  = include( 'data-wf-flat-rate-box-pricing.php' );
		$this->init();
	}

    /**
     * is_available function.
     *
     * @param array $package
     * @return bool
     */
    public function is_available( $package ) {
        if ( "no" === $this->enabled ) {
            return false;
        }

        if ( 'specific' === $this->availability ) {
            if ( is_array( $this->countries ) && ! in_array( $package['destination']['country'], $this->countries ) ) {
                return false;
            }
        } elseif ( 'excluding' === $this->availability ) {
            if ( is_array( $this->countries ) && ( in_array( $package['destination']['country'], $this->countries ) || ! $package['destination']['country'] ) ) {
                return false;
            }
        }
        return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', true, $package );
    }

    /**
     * init function.
     *
     * @access public
     * @return void
     */
    private function init() {
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->enabled                  = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : $this->enabled;
		$this->title                    = isset( $this->settings['title'] ) ? $this->settings['title'] : $this->method_title;
		$this->availability             = isset( $this->settings['availability'] ) ? $this->settings['availability'] : 'all';
		$this->countries                = isset( $this->settings['countries'] ) ? $this->settings['countries'] : array();
		$this->origin                   = isset( $this->settings['origin'] ) ? $this->settings['origin'] : '';
		// WF Shipping Label: New fields - START
		$this->disbleShipmentTracking	= isset( $this->settings['disbleShipmentTracking'] ) ? $this->settings['disbleShipmentTracking'] : 'TrueForCustomer';
		$this->fillShipmentTracking		= isset( $this->settings['fillShipmentTracking'] ) ? $this->settings['fillShipmentTracking'] : 'Manual';
		$this->disblePrintLabel			= isset( $this->settings['disblePrintLabel'] ) ? $this->settings['disblePrintLabel'] : '';
		$this->manual_weight_dimensions	= isset( $this->settings['manual_weight_dimensions'] ) ? $this->settings['manual_weight_dimensions'] : 'no';
		$this->defaultPrintService      = isset( $this->settings['defaultPrintService'] ) ? $this->settings['defaultPrintService'] : 'None';
		$this->printLabelSize      		= isset( $this->settings['printLabelSize'] ) ? $this->settings['printLabelSize'] : 'Default';
		$this->printLabelType      		= isset( $this->settings['printLabelType'] ) ? $this->settings['printLabelType'] : 'PDF';
		$this->senderName        		= isset( $this->settings['senderName'] ) ? $this->settings['senderName'] : '';
		$this->senderCompanyName        = isset( $this->settings['senderCompanyName'] ) ? $this->settings['senderCompanyName'] : '';
		$this->senderAddressLine1       = isset( $this->settings['senderAddressLine1'] ) ? $this->settings['senderAddressLine1'] : '';
		$this->senderAddressLine2       = isset( $this->settings['senderAddressLine2'] ) ? $this->settings['senderAddressLine2'] : '';
		$this->senderCity               = isset( $this->settings['senderCity'] ) ? $this->settings['senderCity'] : '';
		$this->senderState              = isset( $this->settings['senderState'] ) ? $this->settings['senderState'] : '';
		$this->senderEmail              = isset( $this->settings['senderEmail'] ) ? $this->settings['senderEmail'] : '';
		$this->senderPhone              = isset( $this->settings['senderPhone'] ) ? $this->settings['senderPhone'] : '';
		// WF Shipping Label: New fields - END.
		$this->user_id                  = ! empty( $this->settings['user_id'] ) ? $this->settings['user_id'] : $this->default_user_id;
		$this->packing_method           = isset( $this->settings['packing_method'] ) ? $this->settings['packing_method'] : 'per_item';
		$this->boxes                    = isset( $this->settings['boxes'] ) ? $this->settings['boxes'] : array();
		$this->custom_services          = isset( $this->settings['services'] ) ? $this->settings['services'] : array();
		$this->offer_rates              = isset( $this->settings['offer_rates'] ) ? $this->settings['offer_rates'] : 'all';
		$this->fallback                 = ! empty( $this->settings['fallback'] ) ? $this->settings['fallback'] : '';
		$this->flat_rate_fee            = ! empty( $this->settings['flat_rate_fee'] ) ? $this->settings['flat_rate_fee'] : '';
		$this->mediamail_restriction    = isset( $this->settings['mediamail_restriction'] ) ? $this->settings['mediamail_restriction'] : array();
		$this->mediamail_restriction    = array_filter( (array) $this->mediamail_restriction );
		$this->unpacked_item_handling   = ! empty( $this->settings['unpacked_item_handling'] ) ? $this->settings['unpacked_item_handling'] : '';
		$this->enable_standard_services = isset( $this->settings['enable_standard_services'] ) && $this->settings['enable_standard_services'] == 'yes' ? true : false;
		$this->enable_flat_rate_boxes   = isset( $this->settings['enable_flat_rate_boxes'] ) ? $this->settings['enable_flat_rate_boxes'] : 'yes';
		$this->debug                    = isset( $this->settings['debug_mode'] ) && $this->settings['debug_mode'] == 'yes' ? true : false;
		$this->flat_rate_boxes          = apply_filters( 'usps_flat_rate_boxes', $this->flat_rate_boxes );
		$this->selected_flat_rate_boxes	= isset($this->settings['selected_flat_rate_boxes'])?$this->settings['selected_flat_rate_boxes']:array();
        $this->disable_commercial_rates = isset( $this->settings['disable_commercial_rates'] ) && $this->settings['disable_commercial_rates'] == 'yes' ? true : false;
		
		
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'test_user_id' ), -10 );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'clear_transients' ) );
		add_filter('usps_flat_rate_boxes', array($this,'filter_by_selected_flat_rate_boxes'));
	}

	/**
	 * environment_check function.
	 *
	 * @access public
	 * @return void
	 */
	private function environment_check() {

		$admin_page = version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ? 'wc-settings' : 'woocommerce_settings';

		if ( get_woocommerce_currency() != "USD" ) {
			echo '<div class="error">
				<p>' . sprintf( __( 'USPS requires that the <a href="%s">currency</a> is set to US Dollars.', 'wf-usps-woocommerce-shipping' ), admin_url( 'admin.php?page=' . $admin_page . '&tab=general' ) ) . '</p>
			</div>';
		}

		elseif ( ! in_array( WC()->countries->get_base_country(), $this->domestic ) ) {
			echo '<div class="error">
				<p>' . sprintf( __( 'USPS requires that the <a href="%s">base country/region</a> is the United States.', 'wf-usps-woocommerce-shipping' ), admin_url( 'admin.php?page=' . $admin_page . '&tab=general' ) ) . '</p>
			</div>';
		}

		elseif ( ! $this->origin && $this->enabled == 'yes' ) {
			echo '<div class="error">
				<p>' . __( 'USPS is enabled, but the origin postcode has not been set.', 'wf-usps-woocommerce-shipping' ) . '</p>
			</div>';
		}
	}

	/**
	 * admin_options function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options() {
		// Check users environment supports this method
		$this->environment_check();

		// Show settings
		parent::admin_options();
	}

	/**
	 * generate_services_html function.
	 */
	public function generate_services_html() {
		ob_start();
		include( 'html-wf-services.php' );
		return ob_get_clean();
	}

	/**
	 * generate_box_packing_html function.
	 */
	public function generate_box_packing_html() {
		ob_start();
		include( 'html-wf-box-packing.php' );
		return ob_get_clean();
	}

	/**
	 * validate_box_packing_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_box_packing_field( $key ) {
		$boxes = array();

		if ( isset( $_POST['boxes_outer_length'] ) ) {
			$boxes_name         = isset( $_POST['boxes_name'] ) ? $_POST['boxes_name'] : array();
			$boxes_outer_length = $_POST['boxes_outer_length'];
			$boxes_outer_width  = $_POST['boxes_outer_width'];
			$boxes_outer_height = $_POST['boxes_outer_height'];
			$boxes_inner_length = $_POST['boxes_inner_length'];
			$boxes_inner_width  = $_POST['boxes_inner_width'];
			$boxes_inner_height = $_POST['boxes_inner_height'];
			$boxes_box_weight   = $_POST['boxes_box_weight'];
			$boxes_max_weight   = $_POST['boxes_max_weight'];
			$boxes_is_letter    = isset( $_POST['boxes_is_letter'] ) ? $_POST['boxes_is_letter'] : array();

			for ( $i = 0; $i < sizeof( $boxes_outer_length ); $i ++ ) {

				if ( $boxes_outer_length[ $i ] && $boxes_outer_width[ $i ] && $boxes_outer_height[ $i ] && $boxes_inner_length[ $i ] && $boxes_inner_width[ $i ] && $boxes_inner_height[ $i ] ) {

					$boxes[] = array(
						'name'         => wc_clean( $boxes_name[ $i ] ),
						'outer_length' => floatval( $boxes_outer_length[ $i ] ),
						'outer_width'  => floatval( $boxes_outer_width[ $i ] ),
						'outer_height' => floatval( $boxes_outer_height[ $i ] ),
						'inner_length' => floatval( $boxes_inner_length[ $i ] ),
						'inner_width'  => floatval( $boxes_inner_width[ $i ] ),
						'inner_height' => floatval( $boxes_inner_height[ $i ] ),
						'box_weight'   => floatval( $boxes_box_weight[ $i ] ),
						'max_weight'   => floatval( $boxes_max_weight[ $i ] ),
						'is_letter'    => isset( $boxes_is_letter[ $i ] ) ? true : false
					);

				}

			}
		}

		return $boxes;
	}

	/**
	 * validate_services_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_services_field( $key ) {
		$services         = array();
		$posted_services  = $_POST['usps_service'];

		foreach ( $posted_services as $code => $settings ) {

			$services[ $code ] = array(
				'name'               => wc_clean( $settings['name'] ),
				'order'              => wc_clean( $settings['order'] )
			);

			foreach ( $this->services[$code]['services'] as $key => $name ) {
				$services[ $code ][ $key ]['enabled'] = isset( $settings[ $key ]['enabled'] ) ? true : false;
				$services[ $code ][ $key ]['adjustment'] = wc_clean( $settings[ $key ]['adjustment'] );
				$services[ $code ][ $key ]['adjustment_percent'] = wc_clean( $settings[ $key ]['adjustment_percent'] );
			}

		}

		return $services;
	}

	/**
	 * clear_transients function.
	 *
	 * @access public
	 * @return void
	 */
	public function clear_transients() {
		global $wpdb;

		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_usps_quote_%') OR `option_name` LIKE ('_transient_timeout_usps_quote_%')" );
	}

	public function generate_activate_box_html() {
	    ob_start();
	    $plugin_name = 'usps';
	    include( 'wf_api_manager/html/html-wf-activation-window.php' );
	    return ob_get_clean();
	}

    /**
     * init_form_fields function.
     *
     * @access public
     * @return void
     */
    public function init_form_fields() {
	    global $woocommerce;

	    $shipping_classes = array();
	    $classes = ( $classes = get_terms( 'product_shipping_class', array( 'hide_empty' => '0' ) ) ) ? $classes : array();		

	    foreach ( $classes as $class )
	    	$shipping_classes[ $class->term_id ] = $class->name;
			
		$rate_boxes	= include( 'data-wf-flat-rate-boxes.php' );
		$rate_box_names = array();
		foreach($rate_boxes as $rate_box_code => $rate_box){
			$rate_box_names[$rate_box_code] = $rate_box['name'];
		}

    	$this->form_fields  = array(
		   'licence'  => array(
				'type'            => 'activate_box'
			),
			'enabled'             => array(
				'title'           => __( 'Realtime Rates', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'checkbox',
				'label'           => __( 'Enable', 'wf-usps-woocommerce-shipping' ),
				'default'         => 'no',
                'description'     => __( 'Enable realtime rates on Cart/Checkout page.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
			'title'               => array(
				'title'           => __( 'Method Title', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'text',
				'description'     => __( 'This controls the title which the user sees during checkout.', 'wf-usps-woocommerce-shipping' ),
				'default'         => __( 'USPS', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
            'availability'        => array(
				'title'           => __( 'Method Available to', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'default'         => 'all',
				'class'           => 'availability',
				'options'         => array(
					'all'            => __( 'All Countries', 'wf-usps-woocommerce-shipping' ),
					'specific'       => __( 'Specific Countries', 'wf-usps-woocommerce-shipping' ),
				),
			),
            'countries'           => array(
				'title'           => __( 'Specific Countries', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'multiselect',
				'class'           => 'chosen_select',
				'css'             => 'width: 450px;',
				'default'         => '',
				'options'         => $woocommerce->countries->get_allowed_countries(),
			),
			'origin'              => array(
				'title'           => __( 'Origin Postcode', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'text',
				'description'     => __( 'Enter the postcode for the <strong>Shipper</strong>.', 'wf-usps-woocommerce-shipping' ),
				'default'         => '',
                'desc_tip'        => true
		    ),
            'enable_haszip'  => array(
				'title'           => __( 'Show Rates', 'wf-usps-woocommerce-shipping' ),
				'label'           => __( 'Enable', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'default'         => 'no',
                'options'         => array(
					'no'            => __( 'Whenever available', 'wf-usps-woocommerce-shipping' ),
					'yes'       => __( 'When destination zip is available.', 'wf-usps-woocommerce-shipping' ),
				),
				'description'     => __( 'If choosen option When destination zip is available, no rates will be shown until customer enters a ZIP / Post code is entered on cart page. On cart page, click Calculate Shipping and enter your zip code to see available shipping options.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
            'shippingrates'  => array(
				'title'           => __( 'Shipping Rates', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'default'         => 'ONLINE',
				'options'         => array(
					'ONLINE'      => __( 'Use Click-N-Ship Rates', 'wf-usps-woocommerce-shipping' ),
					'ALL'         => __( 'Use OFFLINE rates', 'wf-usps-woocommerce-shipping' ),
				),
				'description'     => __( 'Choose which rates to show your customers, Click-N-Ship (ONLINE) rates are normally cheaper than OFFLINE', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
            'api'           => array(
				'title'           => __( 'Common API Settings:', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'title',
				'description'     => sprintf( __( 'You can obtain a USPS user ID by %s.', 'wf-usps-woocommerce-shipping' ), '<a href="https://www.usps.com/">' . __( 'signing up on the USPS website', 'wf-usps-woocommerce-shipping' ) . '</a>' ),
		    ),
		    'user_id'           => array(
				'title'           => __( 'User ID', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'text',
				'description'     => __( 'Obtained from USPS after getting an account.', 'wf-usps-woocommerce-shipping' ),
				'default'         => '',
				'placeholder'     => $this->default_user_id,
                'desc_tip'        => true
		    ),
		    'debug_mode'  => array(
				'title'           => __( 'Debug Mode', 'wf-usps-woocommerce-shipping' ),
				'label'           => __( 'Enable', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'checkbox',
				'default'         => 'no',
				'description'     => __( 'Enable debug mode to show debugging information on your cart/checkout.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
            'api_tracking'           => array(
				'title'           => __( 'Tracking API Settings:', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'title',
		    ),
			'disbleShipmentTracking'    => array(
				'title'                 => __( 'Shipment Tracking', 'wf-usps-woocommerce-shipping' ),
				'type'                  => 'select',
				'default'               => 'False',
				'options'               => array(
					'TrueForCustomer'   => __( 'Disable for Customer', 'wf-usps-woocommerce-shipping' ),
					'False'             => __( 'Enable', 'wf-usps-woocommerce-shipping' ),
					'True'              => __( 'Disable', 'wf-usps-woocommerce-shipping' ),
				),
				'description'           => __( 'Selecting Disable for Customer option will hide shipment tracking info from Customers order details page.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'              => true
			),
			'fillShipmentTracking'      => array(
				'title'                 => __( 'Mode to Enter Tracking ID', 'wf-usps-woocommerce-shipping' ),
				'type'                  => 'select',
				'default'               => 'Auto',
				'options'               => array(
					'Manual'       		=> __( 'Manual', 'wf-usps-woocommerce-shipping' ),
					'Auto'         		=> __( 'Automatic', 'wf-usps-woocommerce-shipping' ),
				),
				'description'           => __( 'Choosing Automactic will auto fill tracking id while creating label.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'              => true
			),
            'api_label'           => array(
				'title'           => __( 'Label API Settings:', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'title',
				'description'     => sprintf( __( 'Below fields are required for label printing.', 'wf-usps-woocommerce-shipping' ), '<a href="https://www.usps.com/">' . __( 'signing up on the USPS website', 'wf-usps-woocommerce-shipping' ) . '</a>' ),
		    ),
            'disblePrintLabel'    => array(
				'title'           => __( 'Shipping Label', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'default'         => 'no',
                'options'               => array(
					'no'             => __( 'Enable', 'wf-usps-woocommerce-shipping' ),
					'yes'              => __( 'Disable', 'wf-usps-woocommerce-shipping' ),
				),
                'description'           => __( 'Enable or Disable Label Printing feature.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
			'manual_weight_dimensions' => array(
				'title'           => __( 'Manual Dimensions', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'default'         => 'no',
                'options'               => array(
					'yes'             => __( 'Enable', 'wf-usps-woocommerce-shipping' ),
					'no'              => __( 'Disable', 'wf-usps-woocommerce-shipping' ),
				),
				'description'     => __( 'Enabling it will give the provision to enter package dimensions and weight manually while printing label. Disabling it will enable automatic capturing of weights and dimensions for each of the order items. In this case, make sure dimensions and weight is set for each of your products.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
			'defaultPrintService'  => array(
				'title'            => __( 'Default Service', 'wf-usps-woocommerce-shipping' ),
				'type'             => 'select',
				'default'          => 'None',
				'options'          => array(
					'None'             => __( 'Automatic', 'wf-usps-woocommerce-shipping' ),
					'Priority'         => __( 'Priority', 'wf-usps-woocommerce-shipping' ),
					'First-Class'      => __( 'First Class', 'wf-usps-woocommerce-shipping' ),
					'Standard Post'    => __( 'Retail Ground', 'wf-usps-woocommerce-shipping' ),
					'Media Mail'       => __( 'Media Mail', 'wf-usps-woocommerce-shipping' ),
					'Library Mail'     => __( 'Library Mail', 'wf-usps-woocommerce-shipping' ),
				),
				'description'     => __( 'Set default service while printing label. The selected service will be chosen as default option for printing label.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
			'printLabelSize'      => array(
				'title'           => __( 'Print Label Size', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'default'         => 'yes',
				'options'         => array(
					'Default'         => __( 'Default', 'wf-usps-woocommerce-shipping' ),
					'Compact'         => __( 'Compact', 'wf-usps-woocommerce-shipping' ),
				),
				'description'     => __( 'Default size should be ~8x11. Compact means barcode only for domestic. ~4x6 for international.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
			'printLabelType'      => array(
				'title'           => __( 'Print Label Type', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'default'         => 'yes',
				'options'         => array(
					'PDF'         => __( 'PDF', 'wf-usps-woocommerce-shipping' ),
					'TIF'         => __( 'TIF', 'wf-usps-woocommerce-shipping' ),
				),
				'description'     => __( 'Set print label file type.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
			'senderName' => array(
				  'title'         => __( 'Sender Name', 'wf-usps-woocommerce-shipping' ),
				  'type'          => 'text',
				  'description'   => __( 'Name to be printed in the shipping label <strong>[ Required for Print Label ]</strong>', 'wf-usps-woocommerce-shipping' ),
				  'default'       => '',
                  'desc_tip'      => true
				  ),
			'senderCompanyName'   => array(
				  'title'         => __( 'Sender Company Name', 'wf-usps-woocommerce-shipping' ),
				  'type'          => 'text',
				  'description'   => __( 'Company Name to be printed in the shipping label <strong>[ Required for Print Label ]</strong>', 'wf-usps-woocommerce-shipping' ),
				  'default'       => '',
                  'desc_tip'      => true
				  ),
			'senderAddressLine1'  => array(
				  'title'         => __( 'Sender Address Line1', 'wf-usps-woocommerce-shipping' ),
				  'type'          => 'text',
				  'description'   => __( 'Address Line1 to be printed in the shipping label <strong>[ Required for Print Label ]</strong>', 'wf-usps-woocommerce-shipping' ),
				  'default'       => '',
                  'desc_tip'      => true
				  ),
			'senderAddressLine2'  => array(
				  'title'         => __( 'Sender Address Line2', 'wf-usps-woocommerce-shipping' ),
				  'type'          => 'text',
				  'description'   => __( 'Address Line2 to be printed in the shipping label <strong>[ Required for Print Label ]</strong>', 'wf-usps-woocommerce-shipping' ),
				  'default'       => '',
                  'desc_tip'      => true
				  ),
			'senderCity'          => array(
				  'title'         => __( 'Sender City', 'wf-usps-woocommerce-shipping' ),
				  'type'          => 'text',
				  'description'   => __( 'City to be printed in the shipping label <strong>[ Required for Print Label ]</strong>', 'wf-usps-woocommerce-shipping' ),
				  'default'       => '',
                  'desc_tip'      => true
				  ),
			'senderState'         => array(
				  'title'         => __( 'Sender State', 'wf-usps-woocommerce-shipping' ),
				  'type'          => 'text',
				  'description'   => __( 'State short code (Eg: CA) to be printed in the shipping label. <strong>[ Required for Print Label ]</strong>', 'wf-usps-woocommerce-shipping' ),
				  'default'       => '',
                  'desc_tip'      => true
				  ),
			'senderEmail'         => array(
				  'title'         => __( 'Sender Email', 'wf-usps-woocommerce-shipping' ),
				  'type'          => 'email',
				  'description'   => __( 'Enter Sender Email <strong>[ to trigger email notifications while creating Shipping Label. Optional Field. ]</strong>', 'wf-usps-woocommerce-shipping' ),
				  'default'       => '',
                  'desc_tip'      => true
				  ),
			'senderPhone'         => array(
				  'title'         => __( 'Sender Phone', 'wf-usps-woocommerce-shipping' ),
				  'type'          => 'text',
				  'description'   => __( 'Sender Phone <strong>[ Required for Print Label ]</strong>', 'wf-usps-woocommerce-shipping' ),
				  'default'       => '',
                  'desc_tip'      => true
				  ),// WF Shipping Label: New fields - END.
		    'rates'           => array(
				'title'           => __( 'Rates API Settings:', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'title',
				'description'     => __( 'The following settings determine the rates you offer your customers.', 'wf-usps-woocommerce-shipping' ),
		    ),
            'enable_standard_services'  => array(
				'title'           => __( 'Standard API Services', 'wf-usps-woocommerce-shipping' ),
				'label'           => __( 'Enable', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'checkbox',
				'default'         => 'yes',
				'description'     => __( 'Enable Standard Services from the API (Enable non-flat rate services).', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
            'disable_commercial_rates'  => array(
				'title'           => __( 'Rates Preference', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'default'         => 'no',
                'options'         => array(
					'yes'         => __( 'Only Retail Rates', 'wf-usps-woocommerce-shipping' ),
					'no'          => __( 'Commercial Rates', 'wf-usps-woocommerce-shipping' ),
				),
                'description'     => __( 'Choose Only Retail Rates to stop showing Commercial Rates to the Customer. Choose Commercial Rates to show Commercial Rates to customer when it is available for the shipping service. In this case, if Commercial Rates are not available for the shipping service, Retail Rates will be displayed', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => true
			),
			 'fallback' => array(
				'title'            => __( 'Fallback', 'wf-usps-woocommerce-shipping' ),
				'type'             => 'text',
				'description'      => __( 'If USPS returns no matching rates, offer this amount for shipping so that the user can still checkout. Leave blank to disable.', 'wf-usps-woocommerce-shipping' ),
				'default'          => '',
                 'desc_tip'        => true
			),
			'flat_rates'          => array(
				'title'           => __( 'Flat Rate:', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'title',
                'description' => __( 'Flat Rates are not supported for Label Printing.', 'wf-usps-woocommerce-shipping' ),
		    ),
		    'enable_flat_rate_boxes'  => array(
				'title'           => __( 'Boxes &amp; envelopes', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'default'         => 'yes',
				'class'           => 'enable_flat_rate_boxes',
				'options'         => array(
					'yes'         => __( 'Yes - Enable flat rate services', 'wf-usps-woocommerce-shipping' ),
					'no'          => __( 'No - Disable flat rate services', 'wf-usps-woocommerce-shipping' ),
					'priority'    => __( 'Enable Priority flat rate services only', 'wf-usps-woocommerce-shipping' ),
					'express'     => __( 'Enable Express flat rate services only', 'wf-usps-woocommerce-shipping' ),
				),
				'description'     => __( 'Enable this option to offer shipping using USPS Flat Rate services. Items will be packed into the boxes/envelopes and the customer will be offered a single rate from these.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'      => true
			),
			'selected_flat_rate_boxes'  => array(
				'title'           => __( 'Flat Rate Boxes', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'multiselect',
				'class'			  => 'multiselect chosen_select selected_flat_rate_boxes',
				'default'         => '',
				'options'         => $rate_box_names,
				'description'     => __( 'Select flat rate boxes. Leave it blank to select all.', 'wf-usps-woocommerce-shipping' ),
                'desc_tip'        => false,
			),			
			'flat_rate_express_title' => array(
				'title'           => __( 'Express Flat Rate Service', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'text',
				'description'     => 'Enter your custom name for Express Flat Rate Service',
				'default'         => '',
				'placeholder'     => 'Priority Mail Express Flat Rate&#0174;',
                'desc_tip'        => true
		    ),
		    'flat_rate_priority_title' => array(
				'title'           => __( 'Priority Flat Rate Service', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'text',
				'description'     => 'Enter your custom name for Priority Flat Rate Service',
				'default'         => '',
				'placeholder'     => 'Priority Mail Flat Rate&#0174;',
                'desc_tip'        => true
		    ),
		    'flat_rate_fee'           => array(
				'title' 		  => __( 'Flat Rate Fee', 'woocommerce' ),
				'type' 			  => 'text',
				'description'	  => __( 'Fee per-box excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'wf-usps-woocommerce-shipping' ),
				'default'		  => '',
                'desc_tip'        => true
			),
		    'standard_rates'           => array(
				'title'           => __( 'Services, Rates and Packing:', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'title',
		    ),
			'packing_method'  => array(
				'title'           => __( 'Parcel Packing', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
                'description'	  => __( 'Weight based: Regular sized items (< 12 inches) are grouped and quoted for weights only. Large items are quoted individually.', 'wf-usps-woocommerce-shipping' ),
				'default'         => '',
				'class'           => 'packing_method',
				'options'         => array(
					'per_item'       => __( 'Default: Pack items individually', 'wf-usps-woocommerce-shipping' ),
					'box_packing'    => __( 'Recommended: Pack into boxes with weights and dimensions', 'wf-usps-woocommerce-shipping' ),
					'weight_based'    => __( 'Weight based: Regular sized items (< 12 inches) are Grouped.', 'wf-usps-woocommerce-shipping' ),
				),
                'desc_tip'        => true
			),
			'boxes'  => array(
				'type'            => 'box_packing'
			),
			'unpacked_item_handling'   => array(
				'title'           => __( 'Unpacked item', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'description'     => '',
				'default'         => 'all',
				'options'         => array(
					''         => __( 'Get a quote for the unpacked item by itself', 'wf-usps-woocommerce-shipping' ),
					'ingore'   => __( 'Ignore the item - do not quote', 'wf-usps-woocommerce-shipping' ),
					'fallback' => __( 'Use the fallback price (above)', 'wf-usps-woocommerce-shipping' ),
					'abort'    => __( 'Abort - do not return any quotes for the standard services', 'wf-usps-woocommerce-shipping' ),
				),
		    ),
			'offer_rates'   => array(
				'title'           => __( 'Offer Rates', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'select',
				'description'     => '',
				'default'         => 'all',
				'options'         => array(
				    'all'         => __( 'Offer the customer all returned rates', 'wf-usps-woocommerce-shipping' ),
				    'cheapest'    => __( 'Offer the customer the cheapest rate only', 'wf-usps-woocommerce-shipping' ),
				),
		    ),
			'services'  => array(
				'type'            => 'services'
			),
			'mediamail_restriction'        => array(
				'title'           => __( 'Restrict Media Mail', 'wf-usps-woocommerce-shipping' ),
				'type'            => 'multiselect',
				'class'           => 'chosen_select',
				'css'             => 'width: 450px;',
				'default'         => '',
				'options'         => $shipping_classes,
				'custom_attributes'      => array(
					'data-placeholder' => __( 'No restrictions', 'wf-usps-woocommerce-shipping' ),
				)
			),
		);
    }
    
    function test_user_id() {
		if ( empty ( $_POST['woocommerce_usps_user_id'] ) ) {
			return;
		}

		$example_xml  = '<RateV4Request USERID="' . esc_attr( $_POST['woocommerce_usps_user_id'] ) . '">';
		$example_xml .= '<Revision>2</Revision>';
		$example_xml .= '<Package ID="1">';
		$example_xml .= '<Service>PRIORITY</Service>';
		$example_xml .= '<ZipOrigination>97201</ZipOrigination>';
		$example_xml .= '<ZipDestination>44101</ZipDestination>';
		$example_xml .= '<Pounds>1</Pounds>';
		$example_xml .= '<Ounces>0</Ounces>';
		$example_xml .= '<Container />';
		$example_xml .= '<Size>REGULAR</Size>';
		$example_xml .= '</Package>';
		$example_xml .= '</RateV4Request>';

		$response = wp_remote_post( $this->endpoint, array(
			'body'      => 'API=RateV4&XML=' . $example_xml
		) );

		if ( is_wp_error( $response ) ) {
			return;
		}
		if ( ! ( $xml = $this->get_parsed_xml( $response['body'] ) ) ) {
			return;
		}
		if ( ! is_object( $xml ) && ! is_a( $xml, 'SimpleXMLElement' ) ) {
			return;
		}

		// 80040B1A is an Authorization failure
		if ( '80040B1A' !== $xml->Number->__toString() ) {
			return;
		}

		echo '<div class="error">
			<p>' . __( 'The USPS User ID you entered is invalid. Please make sure you entered a valid ID (<a href="https://www.usps.com/business/web-tools-apis/welcome.htm">which can be obtained here</a>). Our User ID will be used instead.', 'wf-usps-woocommerce-shipping' ) . '</p>
		</div>';

		$_POST['woocommerce_usps_user_id'] = '';
    }
    
    /**
	 * Get Parsed XML response
	 * @param  string $xml
	 * @return string|bool
	 */
	private function get_parsed_xml( $xml ) {
		if ( ! class_exists( 'Wf_Safe_DOMDocument' ) ) {
			include_once( 'class-wf-safe-domdocument.php' );
		}

		libxml_use_internal_errors( true );

		$dom     = new Wf_Safe_DOMDocument;
		$success = $dom->loadXML( $xml );

		if ( ! $success ) {
			if ( $this->debug ) {
				trigger_error( 'wpcom_safe_simplexml_load_string(): Error loading XML string', E_USER_WARNING );
			}
			return false;
		}

		if ( isset( $dom->doctype ) ) {
			if ( $this->debug ) {
				trigger_error( 'wpcom_safe_simplexml_import_dom(): Unsafe DOCTYPE Detected', E_USER_WARNING );
			}
			return false;
		}

		return simplexml_import_dom( $dom, 'SimpleXMLElement' );
	}


    /**
     * calculate_shipping function.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping( $package=array() ) {
    	global $woocommerce;
        if(empty($package['destination']['postcode']) && $this->settings['enable_haszip'] == 'yes') return ;
		$this->rates               = array();
		$this->unpacked_item_costs = 0;
		$domestic                  = in_array( $package['destination']['country'], $this->domestic ) ? true : false;

    	$this->debug( __( 'USPS debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-usps-woocommerce-shipping' ) );

    	if ( $this->enable_standard_services ) {

	    	$package_requests = $this->get_package_requests( $package );
			$api              = $domestic ? 'RateV4' : 'IntlRateV2';
	    	libxml_use_internal_errors( true );

	    	if ( $package_requests ) {

	    		$request  = '<' . $api . 'Request USERID="' . $this->user_id . '">' . "\n";
	    		$request .= '<Revision>2</Revision>' . "\n";

	    		foreach ( $package_requests as $key => $package_request ) {
	    			$request .= $package_request['request_data'];
	    		}

	    		$request .= '</' . $api . 'Request>' . "\n";
	    		$request = 'API=' . $api . '&XML=' . str_replace( array( "\n", "\r" ), '', $request );

	    		$transient       = 'usps_quote_' . md5( $request );
				$cached_response = get_transient( $transient );

				$this->debug( 'USPS REQUEST: <pre>' . print_r( htmlspecialchars( $request ), true ) . '</pre>' );

				if ( $cached_response !== false ) {
					$response = $cached_response;

			    	$this->debug( 'USPS CACHED RESPONSE: <pre style="height: 200px; overflow:auto;">' . print_r( htmlspecialchars( $response ), true ) . '</pre>' );
				} else {
					$response = wp_remote_post( $this->endpoint,
			    		array(
							'timeout'   => 70,
							'sslverify' => 0,
							'body'      => $request
					    )
					);

					if ( is_wp_error( $response ) ) {
		    			$this->debug( 'USPS REQUEST FAILED' );

		    			$response = false;
		    		} else {
			    		$response = $response['body'];

			    		$this->debug( 'USPS RESPONSE: <pre style="height: 200px; overflow:auto;">' . print_r( htmlspecialchars( $response ), true ) . '</pre>' );

						set_transient( $transient, $response, DAY_IN_SECONDS * 30 );
					}
				}

	    		if ( $response ) {

					if ( ! ( $xml = $this->get_parsed_xml( $response ) ) ) {
						$this->debug( 'Failed loading XML', 'error' );
					}

					if ( ! is_object( $xml ) && ! is_a( $xml, 'SimpleXMLElement' ) ) {
						$this->debug( 'Invalid XML response format', 'error' );
					}
                    
                    // Our XML response is as we like it. Begin parsing.
					$usps_packages = $xml;

					if ( ! empty( $usps_packages ) ) {
                        foreach ( $usps_packages as $usps_package ) {
                            if ( ! $usps_package || ! is_object( $usps_package ) ) {
								continue;
							}
							// Get package data
							$data_parts = explode( ':', $usps_package->attributes()->ID );
							if ( count( $data_parts ) < 6 ) {
								continue;
							}

							list( $package_item_id, $cart_item_qty, $package_length, $package_width, $package_height, $package_weight ) = $data_parts;
                            
                            $quotes              = $usps_package->children();

                            if ( $this->debug ) {
                                $found_quotes = array();

                                foreach ( $quotes as $quote ) {
                                    if ( $domestic ) {
                                        $code = strval( $quote->attributes()->CLASSID );
                                        $name = strip_tags( htmlspecialchars_decode( (string) $quote->{'MailService'} ) );
                                    } else {
                                        $code = strval( $quote->attributes()->ID );
                                        $name = strip_tags( htmlspecialchars_decode( (string) $quote->{'SvcDescription'} ) );
                                    }

                                    if ( $name && $code ) {
                                        $found_quotes[ $code ] = $name;
                                    } elseif ( $name ) {
                                        $found_quotes[ $code . '-' . sanitize_title( $name ) ] = $name;
                                    }
                                }

                                if ( $found_quotes ) {
                                    ksort( $found_quotes );
                                    $found_quotes_html = '';
                                    foreach ( $found_quotes as $code => $name ) {
                                        if ( ! strstr( $name, "Flat Rate" ) ) {
                                            $found_quotes_html .= '<li>' . $code . ' - ' . $name . '</li>';
                                        }
                                    }
                                    $this->debug( 'The following quotes were returned by USPS: <ul>' . $found_quotes_html . '</ul> If any of these do not display, they may not be enabled in USPS settings.', 'success' );
                                }
                            }

                            // Loop our known services
                            foreach ( $this->services as $service => $values ) {

                                if ( $domestic && strpos( $service, 'D_' ) !== 0 ) {
                                    continue;
                                }

                                if ( ! $domestic && strpos( $service, 'I_' ) !== 0 ) {
                                    continue;
                                }

                                $rate_code = (string) $service;
                                $rate_id   = $this->id . ':' . $rate_code;
                                $rate_name = (string) $values['name'] . ' (' . $this->title . ')';
                                $rate_cost = null;
                                $svc_commitment = null;

                                foreach ( $quotes as $quote ) {

                                    if ( $domestic ) {
                                        $code = strval( $quote->attributes()->CLASSID );
                                    } else {
                                        $code = strval( $quote->attributes()->ID );
                                    }

                                    if ( $code !== "" && in_array( $code, array_keys( $values['services'] ) ) ) {

                                        if ( $domestic ) {
                                            if( $this->disable_commercial_rates ) {
                                                if( ( (float) $quote->{'Rate'} ) > 0.0 ) {
                                                    $cost = (float) $quote->{'Rate'} * $cart_item_qty;
                                                }
                                                else {
                                                    continue;
                                                }
                                            }
                                            else {
                                                if ( ! empty( $quote->{'CommercialRate'} ) ) {
                                                    $cost = (float) $quote->{'CommercialRate'} * $cart_item_qty;
                                                } else {
                                                    $cost = (float) $quote->{'Rate'} * $cart_item_qty;
                                                }
                                            }

                                        } else {

                                            if ( ! empty( $quote->{'CommercialPostage'} ) ) {
                                                $cost = (float) $quote->{'CommercialPostage'} * $cart_item_qty;
                                            } else {
                                                $cost = (float) $quote->{'Postage'} * $cart_item_qty;
                                            }

                                        }

                                        // Cost adjustment %
                                        $adjusted = false;
                                        if ( ! empty( $this->custom_services[ $rate_code ][ $code ]['adjustment_percent'] ) ) {
                                            $cost = round( $cost + ( $cost * ( floatval( $this->custom_services[ $rate_code ][ $code ]['adjustment_percent'] ) / 100 ) ), wc_get_price_decimals() );
                                        	$adjusted = true;
                                        }

                                        // Cost adjustment
                                        if ( ! empty( $this->custom_services[ $rate_code ][ $code ]['adjustment'] ) ) {
                                            $cost = round( $cost + floatval( $this->custom_services[ $rate_code ][ $code ]['adjustment'] ), wc_get_price_decimals() );
                                        	$adjusted = true;
                                        }
                                        // Enabled check
                                        if ( isset( $this->custom_services[ $rate_code ][ $code ] ) && empty( $this->custom_services[ $rate_code ][ $code ]['enabled'] ) ) {
                                            continue;
                                        }

                                        if ( $domestic ) {
                                            switch ( $code ) {
                                                // Handle first class - there are multiple d0 rates and we need to handle size retrictions because the USPS API doesn't do this.
                                                case "0" :
                                                    $service_name = strip_tags( htmlspecialchars_decode( (string) $quote->{'MailService'} ) );

                                                    if ( apply_filters( 'usps_disable_first_class_rate_' . sanitize_title( $service_name ), false) ) {
                                                        continue 2;
                                                    }
                                                break;
                                                // Media mail has restrictions - check here
                                                case "6" :
                                                    if ( sizeof( $this->mediamail_restriction ) > 0 ) {
                                                        $invalid = false;

                                                        foreach ( $package['contents'] as $package_item ) {
                                                            if ( ! in_array( $package_item['data']->get_shipping_class_id(), $this->mediamail_restriction ) ) {

                                                                // Checking if product is virutal. If it is,
                                                                // then don't skip media mail.
                                                                if( isset( $package_item['data']->virtual ) &&
                                                                   "yes" != $package_item['data']->virtual ) {
                                                                    $invalid = true;
                                                                }

                                                            }
                                                        }

                                                        if ( $invalid ) {
                                                            $this->debug( 'Skipping media mail' );
                                                        }

                                                        if ( $invalid ) {
                                                            continue 2;
                                                        }
                                                    }
                                                break;
                                            }
                                        }

                                        if ( $domestic && $package_length && $package_width && $package_height ) {
                                            switch ( $code ) {
                                                // Regional rate boxes need additonal checks to deal with USPS's API
                                                case "47" :
                                                    if ( ( $package_length > 10 || $package_width > 7 || $package_height > 4.75 ) && ( $package_length > 12.875 || $package_width > 10.9375 || $package_height > 2.365 ) ) {
                                                    continue 2;
                                                } else {
                                                    // Valid
                                                    break;
                                                }
                                                break;
                                                case "49" :
                                                    if ( ( $package_length > 12 || $package_width > 10.25 || $package_height > 5 ) && ( $package_length > 15.875 || $package_width > 14.375 || $package_height > 2.875 ) ) {
                                                        continue 2;
                                                    } else {
                                                        // Valid
                                                        break;
                                                    }
                                                break;
                                                case "58" :
                                                    if ( $package_length > 14.75 || $package_width > 11.75 || $package_height > 11.5 ) {
                                                        continue 2;
                                                    } else {
                                                        // Valid
                                                        break;
                                                    }
                                                break;
                                                // Handle first class - there are multiple d0 rates and we need to handle size retrictions because the API doesn't do this for us!
                                            case "0" :
                                                $service_name = strip_tags( htmlspecialchars_decode( (string) $quote->{'MailService'} ) );

                                                if ( strstr( $service_name, 'Postcards' ) ) {

                                                    if ( $package_length > 6 || $package_length < 5 ) {
                                                        continue 2;
                                                    }
                                                    if ( $package_width > 4.25 || $package_width < 3.5 ) {
                                                        continue 2;
                                                    }
                                                    if ( $package_height > 0.016 || $package_height < 0.007 ) {
                                                        continue 2;
                                                    }

                                                } elseif ( strstr( $service_name, 'Large Envelope' ) ) {

                                                    if ( $package_length > 15 || $package_length < 11.5 ) {
                                                        continue 2;
                                                    }
                                                    if ( $package_width > 12 || $package_width < 6 ) {
                                                        continue 2;
                                                    }
                                                    if ( $package_height > 0.75 || $package_height < 0.25 ) {
                                                        continue 2;
                                                    }

                                                } elseif ( strstr( $service_name, 'Letter' ) ) {

                                                    if ( $package_length > 11.5 || $package_length < 5 ) {
                                                        continue 2;
                                                    }
                                                    if ( $package_width > 6.125 || $package_width < 3.5 ) {
                                                        continue 2;
                                                    }
                                                    if ( $package_height > 0.25 || $package_height < 0.007 ) {
                                                        continue 2;
                                                    }

                                                } elseif ( strstr( $service_name, 'Parcel' ) ) {

                                                    $girth = ( $package_width + $package_height ) * 2;

                                                    if ( $girth + $package_length > 108 ) {
                                                        continue 2;
                                                    }

                                                } else {
                                                    continue 2;
                                                }
                                            break;
                                            }
                                        }

                                        if ( is_null( $rate_cost ) ) {
                                            $rate_cost = $cost;
                                            $svc_commitment = $quote->SvcCommitments;
                                        } elseif ( $cost < $rate_cost ) {
                                            $rate_cost = $cost;
                                            $svc_commitment = $quote->SvcCommitments;
                                        }
                                    }
                                }

                                if ( $rate_cost || ( $rate_cost==0 && !empty($adjusted) ) ) {
                                    if ( ! empty( $svc_commitment ) && strstr( $svc_commitment, 'days' ) ) {
                                        $rate_name .= ' (' . current( explode( 'days', $svc_commitment ) ) . ' days)';
                                    }
                                    $this->prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost );
                                }
                            }
						}

					} else {
						// No rates
						$this->debug( 'Invalid request; no rates returned', 'error' );
					}
				}
			}

			// Ensure rates were found for all packages
			if ( $this->found_rates ) {
				foreach ( $this->found_rates as $key => $value ) {
					if ( $value['packages'] < sizeof( $package_requests ) ) {
                        $this->debug( "Unsetting {$key} - too few packages.", 'error' );
						unset( $this->found_rates[ $key ] );
					}

					if ( $this->unpacked_item_costs && ! empty( $this->found_rates[ $key ] ) ) {
						$this->debug( sprintf( __( 'Adding unpacked item costs to rate %s', 'wf-usps-woocommerce-shipping' ), $key ) );
						$this->found_rates[ $key ]['cost'] += $this->unpacked_item_costs;
					}
				}
			}
		}

		// Flat Rate boxes quote
		if ( $this->enable_flat_rate_boxes == 'yes' || $this->enable_flat_rate_boxes == 'priority' ) {
			// Priority
			$flat_rate = $this->calculate_flat_rate_box_rate( $package, 'priority' );
			if ( $flat_rate )
				$this->found_rates[ $flat_rate['id'] ] = $flat_rate;
		}
		if ( $this->enable_flat_rate_boxes == 'yes' || $this->enable_flat_rate_boxes == 'express' ) {
			// Express
			$flat_rate = $this->calculate_flat_rate_box_rate( $package, 'express' );
			if ( $flat_rate )
				$this->found_rates[ $flat_rate['id'] ] = $flat_rate;
		}
			
		// Add rates
		if ( $this->found_rates ) {

			// Only offer one priority rate
			if ( isset( $this->found_rates['usps:D_PRIORITY_MAIL'] ) && isset( $this->found_rates['usps:flat_rate_box_priority'] ) ) {
				if ( $this->found_rates['usps:flat_rate_box_priority']['cost'] < $this->found_rates['usps:D_PRIORITY_MAIL']['cost']  ) {
					$this->debug( "Unsetting PRIORITY MAIL api rate - flat rate box is cheaper.", 'error' );
					unset( $this->found_rates['usps:D_PRIORITY_MAIL'] );
				} else {
					$this->debug( "Unsetting PRIORITY MAIL flat rate - api rate is cheaper.", 'error' );
					unset( $this->found_rates['usps:flat_rate_box_priority'] );
				}
			}

			if ( isset( $this->found_rates['usps:D_EXPRESS_MAIL'] ) && isset( $this->found_rates['usps:flat_rate_box_express'] ) ) {
				if ( $this->found_rates['usps:flat_rate_box_express']['cost'] < $this->found_rates['usps:D_EXPRESS_MAIL']['cost']  ) {
					$this->debug( "Unsetting PRIORITY MAIL EXPRESS api rate - flat rate box is cheaper.", 'error' );
					unset( $this->found_rates['usps:D_EXPRESS_MAIL'] );
				} else {
					$this->debug( "Unsetting PRIORITY MAIL EXPRESS flat rate - api rate is cheaper.", 'error' );
					unset( $this->found_rates['usps:flat_rate_box_express'] );
				}
			}

			if ( isset( $this->found_rates['usps:I_PRIORITY_MAIL'] ) && isset( $this->found_rates['usps:flat_rate_box_priority'] ) ) {
				if ( $this->found_rates['usps:flat_rate_box_priority']['cost'] < $this->found_rates['usps:I_PRIORITY_MAIL']['cost']  ) {
					$this->debug( "Unsetting PRIORITY MAIL api rate - flat rate box is cheaper.", 'error' );
					unset( $this->found_rates['usps:I_PRIORITY_MAIL'] );
				} else {
					$this->debug( "Unsetting PRIORITY MAIL flat rate - api rate is cheaper.", 'error' );
					unset( $this->found_rates['usps:flat_rate_box_priority'] );
				}
			}

			if ( isset( $this->found_rates['usps:I_EXPRESS_MAIL'] ) && isset( $this->found_rates['usps:flat_rate_box_express'] ) ) {
				if ( $this->found_rates['usps:flat_rate_box_express']['cost'] < $this->found_rates['usps:I_EXPRESS_MAIL']['cost']  ) {
					$this->debug( "Unsetting PRIORITY MAIL EXPRESS api rate - flat rate box is cheaper.", 'error' );
					unset( $this->found_rates['usps:I_EXPRESS_MAIL'] );
				} else {
					$this->debug( "Unsetting PRIORITY MAIL EXPRESS flat rate - api rate is cheaper.", 'error' );
					unset( $this->found_rates['usps:flat_rate_box_express'] );
				}
			}

			// Remove invalid rates/with zero value
			foreach ( $this->found_rates as $key => $rate ) {
				if($rate['cost']<=0){
					unset($this->found_rates[$key]);
				}
			}
			if ( $this->offer_rates == 'all' ) {

				uasort( $this->found_rates, array( $this, 'sort_rates' ) );

				foreach ( $this->found_rates as $key => $rate ) {
					$this->add_rate( $rate );
				}

			} else {

				$cheapest_rate = '';

				foreach ( $this->found_rates as $key => $rate ) {
					if ( ! $cheapest_rate || $cheapest_rate['cost'] > $rate['cost'] )
						$cheapest_rate = $rate;
				}

				$cheapest_rate['label'] = $this->title;

				$this->add_rate( $cheapest_rate );

			}

		// Fallback
		} elseif ( $this->fallback ) {
			$this->add_rate( array(
				'id' 	=> $this->id . '_fallback',
				'label' => $this->title,
				'cost' 	=> $this->fallback,
				'sort'  => 0
			) );
		}

    }

    /**
     * prepare_rate function.
     *
     * @access private
     * @param mixed $rate_code
     * @param mixed $rate_id
     * @param mixed $rate_name
     * @param mixed $rate_cost
     * @return void
     */
    private function prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost ) {

	    // Name adjustment
		if ( ! empty( $this->custom_services[ $rate_code ]['name'] ) )
			$rate_name = $this->custom_services[ $rate_code ]['name'];

		// Merging
		if ( isset( $this->found_rates[ $rate_id ] ) ) {
			$rate_cost = $rate_cost + $this->found_rates[ $rate_id ]['cost'];
			$packages  = 1 + $this->found_rates[ $rate_id ]['packages'];
		} else {
			$packages = 1;
		}

		// Sort
		if ( isset( $this->custom_services[ $rate_code ]['order'] ) ) {
			$sort = $this->custom_services[ $rate_code ]['order'];
		} else {
			$sort = 999;
		}

		$this->found_rates[ $rate_id ] = array(
			'id'       => $rate_id,
			'label'    => $rate_name,
			'cost'     => $rate_cost,
			'sort'     => $sort,
			'packages' => $packages
		);

    }

    /**
     * sort_rates function.
     *
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @return void
     */
    public function sort_rates( $a, $b ) {
		if ( $a['sort'] == $b['sort'] ) return 0;
		return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
    }

    /**
     * get_request function.
     *
     * @access private
     * @return void
     */
    private function get_package_requests( $package ) {

	    // Choose selected packing
    	switch ( $this->packing_method ) {
	    	case 'box_packing' :
	    		$requests = $this->box_shipping( $package );
	    	break;
	    	case 'weight_based' :
	    		$requests = $this->weight_based_shipping( $package );
	    	break;
	    	case 'per_item' :
	    	default :
	    		$requests = $this->per_item_shipping( $package );
	    	break;
    	}

    	return $requests;
    }

    /**
     * per_item_shipping function.
     *
     * @access private
     * @param mixed $package
     * @return void
     */
    private function per_item_shipping( $package ) {
	    global $woocommerce;

	    $requests = array();
	    $domestic = in_array( $package['destination']['country'], $this->domestic ) ? true : false;

    	// Get weight of order
    	foreach ( $package['contents'] as $item_id => $values ) {
			$packed_items=array();
    		if ( ! $values['data']->needs_shipping() ) {
    			$this->debug( sprintf( __( 'Product # is virtual. Skipping.', 'wf-usps-woocommerce-shipping' ), $item_id ) );
    			continue;
    		}
			
			$skip_product = apply_filters('wf_shipping_skip_product',false, $values, $package['contents']);
			if($skip_product){
				continue;
			}
			
    		if ( ! $values['data']->get_weight() ) {
	    		$this->debug( sprintf( __( 'Product # is missing weight. Using 1lb.', 'wf-usps-woocommerce-shipping' ), $item_id ) );

	    		$weight = 1;
    		} else {
    			$weight = wc_get_weight( $values['data']->get_weight(), 'lbs' );
    		}

    		$size   = 'REGULAR';

    		if ( $values['data']->length && $values['data']->height && $values['data']->width ) {

				$dimensions = array( wc_get_dimension( $values['data']->length, 'in' ), wc_get_dimension( $values['data']->height, 'in' ), wc_get_dimension( $values['data']->width, 'in' ) );

				sort( $dimensions );

				if ( max( $dimensions ) > 12 ) {
					$size   = 'LARGE';
				}

				$girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];
			} else {
				$dimensions = array( 0, 0, 0 );
				$girth      = 0;
			}

			if ( $domestic ) {
				$request  = '<Package ID="' . $this->generate_package_id( $item_id, $values['quantity'], $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Service>' . ( ! $this->settings['shippingrates'] ? 'ONLINE' : $this->settings['shippingrates'] ) . '</Service>' . "\n";
				$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</ZipOrigination>' . "\n";
				$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";

				if ( 'LARGE' === $size ) {
					$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				} else {
					$request .= '	<Container />' . "\n";
				}

				$request .= '	<Size>' . $size . '</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth,1 ) . '</Girth>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<ShipDate>' . date( "d-M-Y", ( current_time('timestamp') + (60 * 60 * 24) ) ) . '</ShipDate>' . "\n";
				
				$request .= '</Package>' . "\n";

			} else {

				$request  = '<Package ID="' . $this->generate_package_id( $item_id, $values['quantity'], $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<MailType>Package</MailType>' . "\n";
				$request .= '	<ValueOfContents>' . $values['data']->get_price() . '</ValueOfContents>' . "\n";
				$request .= '	<Country>' . $this->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";

				$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				
				$request .= '	<Size>' . $size . '</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth,1 ) . '</Girth>' . "\n";
				$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</OriginZip>' . "\n";
				$request .= '	<CommercialFlag>' . ( $this->settings['shippingrates'] == "ONLINE" ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
				$request .= '</Package>' . "\n";

			}
			$item_data=wc_get_product($item_id);
			if($item_data){// Front-end price call doesn't need this data
				$item_id=$item_data->variation_id?$item_data->variation_id:$item_data->id;
				$packed_items[$item_id]=array(
												'product_name'=>$item_data->get_title(),
												'qty'=>1
											);
				if($item_data->variation_id){
					$packed_items[$item_id]['variation_text']=$this->wf_get_variation_data_from_variation_id($item_data->variation_id);
				}
			}
			$package_info=array(
				'items'=>$packed_items,
				'dimension'=>array('length'=>$dimensions[2],'width'=>$dimensions[1],'height'=>$dimensions[0],'weight'=>$weight),
				'units'=>array('dimension'=>'in','weight'=>'lbs')
			);
			$requests[] = array('request_data'=>$request,'package_info'=>$package_info);
    	}
		return $requests;
    }

    /**
     * Generate shipping request for weights only
     * @param  array $package
     * @return array
     */
    private function weight_based_shipping( $package ) {
    	global $woocommerce;

		$requests                  = array();
		$domestic                  = in_array( $package['destination']['country'], $this->domestic ) ? true : false;
		$total_regular_item_weight = 0;

    	// Add requests for larger items
    	foreach ( $package['contents'] as $item_id => $values ) {
			
    		if ( ! $values['data']->needs_shipping() ) {
    			$this->debug( sprintf( __( 'Product #%d is virtual. Skipping.', 'wf-usps-woocommerce-shipping' ), $item_id ) );
    			continue;
    		}
			
			$skip_product = apply_filters('wf_shipping_skip_product',false, $values, $package['contents']);
			if($skip_product){
				continue;
			}

    		if ( ! $values['data']->get_weight() ) {
	    		$this->debug( sprintf( __( 'Product #%d is missing weight. Using 1lb.', 'wf-usps-woocommerce-shipping' ), $item_id ), 'error' );

	    		$weight = 1;
    		} else {
    			$weight = wc_get_weight( $values['data']->get_weight(), 'lbs' );
    		}

			$dimensions = array( wc_get_dimension( $values['data']->length, 'in' ), wc_get_dimension( $values['data']->height, 'in' ), wc_get_dimension( $values['data']->width, 'in' ) );

			sort( $dimensions );

			if ( max( $dimensions ) <= 12 ) {
				$total_regular_item_weight += ( $weight * $values['quantity'] );
    			continue;
			}
			
			$girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];

			if ( $domestic ) {
				$request  = '<Package ID="' . $this->generate_package_id( $item_id, $values['quantity'], $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Service>' . ( !$this->settings['shippingrates'] ? 'ONLINE' : $this->settings['shippingrates'] ) . '</Service>' . "\n";
				$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</ZipOrigination>' . "\n";
				$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				$request .= '	<Size>LARGE</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth,1 ) . '</Girth>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<ShipDate>' . date( "d-M-Y", ( current_time('timestamp') + (60 * 60 * 24) ) ) . '</ShipDate>' . "\n";
				$request .= '</Package>' . "\n";
			} else {
				$request  = '<Package ID="' . $this->generate_package_id( $item_id, $values['quantity'], $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<MailType>Package</MailType>' . "\n";
				$request .= '	<ValueOfContents>' . $values['data']->get_price() . '</ValueOfContents>' . "\n";
				$request .= '	<Country>' . $this->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";
				$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				$request .= '	<Size>LARGE</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth,1 ) . '</Girth>' . "\n";
				$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</OriginZip>' . "\n";
				$request .= '	<CommercialFlag>' . ( $this->settings['shippingrates'] == "ONLINE" ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
				$request .= '</Package>' . "\n";
			}
			// Bigger items
			$item_data=wc_get_product($item_id);
			$packed_items=array();
			if($item_data){// Front-end price call doesn't need this data
				$item_id=$item_data->variation_id?$item_data->variation_id:$item_data->id;
				$packed_items[$item_id]=array(
											'product_name'=>$item_data->get_title(),
											'qty'=>$values['quantity']
										);
				if($item_data->variation_id){
					$packed_items[$item_id]['variation_text']=$this->wf_get_variation_data_from_variation_id($item_data->variation_id);
				}
			}
			$package_info=array(
				'items'=>$packed_items,
				'dimension'=>array('length'=>$dimensions[2],'width'=>$dimensions[1],'height'=>$dimensions[0],'weight'=>$weight),
				'units'=>array('dimension'=>'in','weight'=>'lbs')
			);
			$requests[] = array('request_data'=>$request,'package_info'=>$package_info);
    	}

    	// Regular package
    	if ( $total_regular_item_weight > 0 ) {
    		$max_package_weight = ( $domestic || $package['destination']['country'] == 'MX' ) ? 70 : 44;
    		$package_weights    = array();

    		$full_packages      = floor( $total_regular_item_weight / $max_package_weight );
    		for ( $i = 0; $i < $full_packages; $i ++ )
    			$package_weights[] = $max_package_weight;

    		if ( $remainder = fmod( $total_regular_item_weight, $max_package_weight ) )
    			$package_weights[] = $remainder;

    		foreach ( $package_weights as $key => $weight ) {
				if ( $domestic ) {
					$request  = '<Package ID="' . $this->generate_package_id( 'regular_' . $key, 1, 0, 0, 0, 0 ) . '">' . "\n";
					$request .= '	<Service>' . ( !$this->settings['shippingrates'] ? 'ONLINE' : $this->settings['shippingrates'] ) . '</Service>' . "\n";
					$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</ZipOrigination>' . "\n";
					$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
					$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
					$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
					$request .= '	<Container />' . "\n";
					$request .= '	<Size>REGULAR</Size>' . "\n";
					$request .= '	<Machinable>true</Machinable> ' . "\n";
					$request .= '	<ShipDate>' . date( "d-M-Y", ( current_time('timestamp') + (60 * 60 * 24) ) ) . '</ShipDate>' . "\n";
					$request .= '</Package>' . "\n";
				} else {
					$request  = '<Package ID="' . $this->generate_package_id( 'regular_' . $key, 1, 0, 0, 0, 0 ) . '">' . "\n";
					$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
					$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
					$request .= '	<Machinable>true</Machinable> ' . "\n";
					$request .= '	<MailType>Package</MailType>' . "\n";
					$request .= '	<ValueOfContents>' . $values['data']->get_price() . '</ValueOfContents>' . "\n";
					$request .= '	<Country>' . $this->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";
					$request .= '	<Container />' . "\n";
					$request .= '	<Size>REGULAR</Size>' . "\n";
					$request .= '	<Width />' . "\n";
					$request .= '	<Length />' . "\n";
					$request .= '	<Height />' . "\n";
					$request .= '	<Girth />' . "\n";
					$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</OriginZip>' . "\n";
					$request .= '	<CommercialFlag>' . ( $this->settings['shippingrates'] == "ONLINE" ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
					$request .= '</Package>' . "\n";
				}

				$requests[] = array('request_data'=>$request);
			}
    	}
		return $requests;
    }

    /**
     * Generate a package ID for the request
     *
     * Contains qty and dimension info so we can look at it again later when it comes back from USPS if needed
     *
     * @return string
     */
    public function generate_package_id( $id, $qty, $length, $width, $height, $weight ) {
    	return implode( ':', array( $id, $qty, $length, $width, $height, $weight ) );
    }	

    /**
     * box_shipping function.
     *
     * @access private
     * @param mixed $package
     * @return void
     */
    private function box_shipping( $package ) {
	    global $woocommerce;
		$requests = array();
	    $domestic = in_array( $package['destination']['country'], $this->domestic ) ? true : false;

	  	if ( ! class_exists( 'WF_Boxpack' ) ) {
	  		include_once 'class-wf-packing.php';
	  	}

	    $boxpack = new WF_Boxpack();
		
	    // Define boxes
		foreach ( $this->boxes as $key => $box ) {

			$newbox = $boxpack->add_box( $box['outer_length'], $box['outer_width'], $box['outer_height'], $box['box_weight'] );

			$newbox->set_id( $key );
			$newbox->set_inner_dimensions( $box['inner_length'], $box['inner_width'], $box['inner_height'] );

			if ( $box['max_weight'] ) {
				$newbox->set_max_weight( $box['max_weight'] );
			}
		}

		// Define box size A
		if ( ! empty( $this->custom_services['D_PRIORITY_MAIL']['47']['enabled'] ) ) {
			$newbox = $boxpack->add_box( 10, 7, 4.75 );
			$newbox->set_id( 'Regional Rate Box A1' );
			$newbox->set_max_weight( 15 );
			$newbox = $boxpack->add_box( 12.875, 10.9375, 2.365 );
			$newbox->set_id( 'Regional Rate Box A2' );
			$newbox->set_max_weight( 15 );
		}

		// Define box size B
		if ( ! empty( $this->custom_services['D_PRIORITY_MAIL']['49']['enabled'] ) ) {
			$newbox = $boxpack->add_box( 12, 10.25, 5 );
			$newbox->set_id( 'Regional Rate Box B1' );
			$newbox->set_max_weight( 20 );
			$newbox = $boxpack->add_box( 15.875, 14.375, 2.875 );
			$newbox->set_id( 'Regional Rate Box B2' );
			$newbox->set_max_weight( 20 );
		}

		// Add items
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
				continue;
			}
			
			$skip_product = apply_filters('wf_shipping_skip_product',false, $values, $package['contents']);
			if($skip_product){
				continue;
			}

			if ( $values['data']->length && $values['data']->height && $values['data']->width ) {

				$dimensions = array( wc_get_dimension( $values['data']->length, 'in' ), wc_get_dimension( $values['data']->height, 'in' ), wc_get_dimension( $values['data']->width, 'in' ) );

			} else {
				$this->debug( sprintf( __( 'Product #%d is missing dimensions. Using 1x1x1.', 'wf-usps-woocommerce-shipping' ), $item_id ), 'error' );

				$dimensions = array( 1, 1, 1 );
			}
            
            if ( $values['data']->weight ) {
				$weight = wc_get_weight( $values['data']->get_weight(), 'lbs' );
			} else {
				$this->debug( sprintf( __( 'Product #%d is missing weight! Using 1lb.', 'wf-usps-woocommerce-shipping' ), $item_id ), 'error' );
				$weight = 1;
			}

			for ( $i = 0; $i < $values['quantity']; $i ++ ) {
				$boxpack->add_item(
					$dimensions[2],
					$dimensions[1],
					$dimensions[0],
					$weight,
					$values['data']->get_price(),
					$item_id //WF: Adding Item Id and Quantity as meta.
				);
			}
		}

		// Pack it
		$boxpack->pack();

		// Get packages
		$box_packages = $boxpack->get_packages();

		foreach ( $box_packages as $key => $box_package ) {

			if ( ! empty( $box_package->unpacked ) ) {
				$this->debug( 'Unpacked Item' );

				switch ( $this->unpacked_item_handling ) {
					case 'fallback' :
						// No request, just a fallback
						$this->unpacked_item_costs += $this->fallback;
						continue;
					break;
					case 'ignore' :
						// No request
						continue;
					break;
					case 'abort' :
						// No requests!
						return false;
					break;
				}
			} else {
				$this->debug( 'Packed ' . $this->get_box_name($box_package->id) );
			}
			$weight     = $box_package->weight;
    		$size       = 'REGULAR';
    		$dimensions = array( $box_package->length, $box_package->width, $box_package->height );

			sort( $dimensions );

			if ( max( $dimensions ) > 12 ) {
				$size   = 'LARGE';
			}

			$girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];
			
			// Getting package info
			$package_info=array();
			$packed_items=array();
			if ( ! empty( $box_package->packed ) && is_array( $box_package->packed ) ) {
				foreach( $box_package->packed as $item ) {					
					$item_data=wc_get_product($item->meta);
					if($item_data){// Front-end price call doesn't need this data
						$item_id=$item_data->variation_id?$item_data->variation_id:$item_data->id;
						if(!array_key_exists($item_id,$packed_items)){
							$packed_items[$item_id]=array(
														'product_name'=>$item_data->get_title(),
														'qty'=>1
													);
							if($item_data->variation_id){
								$packed_items[$item_id]['variation_text']=$this->wf_get_variation_data_from_variation_id($item_data->variation_id);
							}
							
						}else{
							$packed_items[$item_id]['qty']+=1;
						}
					}
				}
				$pack_dimensions = array( $box_package->length, $box_package->width, $box_package->height );
				sort( $pack_dimensions );
				$package_info=array(
					'pack_id'=>$this->get_box_name($box_package->id),
					'items'=>$packed_items,
					'dimension'=>array('length'=>$pack_dimensions[2],'width'=>$pack_dimensions[1],'height'=>$pack_dimensions[0],'weight'=>$box_package->weight),
					'units'=>array('dimension'=>'in','weight'=>'lbs')
				);
			}
			// EOF package info
			if ( $domestic ) {

				$request  = '<Package ID="' . $this->generate_package_id( $key, 1, $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Service>' . ( ! $this->settings['shippingrates'] ? 'ONLINE' : $this->settings['shippingrates'] ) . '</Service>' . "\n";
				$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</ZipOrigination>' . "\n";
				$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";

				if ( 'LARGE' === $size ) {
					$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				} else {
					$request .= '	<Container />' . "\n";
				}

				$request .= '	<Size>' . $size . '</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth, 1 ) . '</Girth>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<ShipDate>' . date( "d-M-Y", ( current_time('timestamp') + (60 * 60 * 24) ) ) . '</ShipDate>' . "\n";
				$request .= '</Package>' . "\n";

			} else {
				
				$request  = '<Package ID="' . $this->generate_package_id( $key, 1, $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<MailType>' . ( empty( $this->boxes[ $box_package->id ]['is_letter'] ) ? 'PACKAGE' : 'ENVELOPE' ) . '</MailType>' . "\n";
				$request .= '	<GXG><POBoxFlag>N</POBoxFlag><GiftFlag>N</GiftFlag></GXG>' . "\n";
				$request .= '	<ValueOfContents>' . number_format( $box_package->value, 2, '.', '' ) . '</ValueOfContents>' . "\n";
				$request .= '	<Country>' . $this->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";

				$request .= '	<Container>RECTANGULAR</Container>' . "\n";

				$request .= '	<Size>' . $size . '</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth, 1 ) . '</Girth>' . "\n";
				$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</OriginZip>' . "\n";
				$request .= '	<CommercialFlag>' . ( $this->settings['shippingrates'] == "ONLINE" ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
				$request .= '</Package>' . "\n";

			}
			
    		$requests[] = array('request_data'=>$request,'package_info'=>$package_info);
		}
		return $requests;
    }

    /**
     * get_country_name function.
     *
     * @access private
     * @return void
     */
    public function get_country_name( $code ) {
		$countries = apply_filters( 'usps_countries', array(
			'AF' => __( 'Afghanistan', 'wf-usps-woocommerce-shipping' ),
			'AX' => __( '&#197;land Islands', 'wf-usps-woocommerce-shipping' ),
			'AL' => __( 'Albania', 'wf-usps-woocommerce-shipping' ),
			'DZ' => __( 'Algeria', 'wf-usps-woocommerce-shipping' ),
			'AD' => __( 'Andorra', 'wf-usps-woocommerce-shipping' ),
			'AO' => __( 'Angola', 'wf-usps-woocommerce-shipping' ),
			'AI' => __( 'Anguilla', 'wf-usps-woocommerce-shipping' ),
			'AQ' => __( 'Antarctica', 'wf-usps-woocommerce-shipping' ),
			'AG' => __( 'Antigua and Barbuda', 'wf-usps-woocommerce-shipping' ),
			'AR' => __( 'Argentina', 'wf-usps-woocommerce-shipping' ),
			'AM' => __( 'Armenia', 'wf-usps-woocommerce-shipping' ),
			'AW' => __( 'Aruba', 'wf-usps-woocommerce-shipping' ),
			'AU' => __( 'Australia', 'wf-usps-woocommerce-shipping' ),
			'AT' => __( 'Austria', 'wf-usps-woocommerce-shipping' ),
			'AZ' => __( 'Azerbaijan', 'wf-usps-woocommerce-shipping' ),
			'BS' => __( 'Bahamas', 'wf-usps-woocommerce-shipping' ),
			'BH' => __( 'Bahrain', 'wf-usps-woocommerce-shipping' ),
			'BD' => __( 'Bangladesh', 'wf-usps-woocommerce-shipping' ),
			'BB' => __( 'Barbados', 'wf-usps-woocommerce-shipping' ),
			'BY' => __( 'Belarus', 'wf-usps-woocommerce-shipping' ),
			'BE' => __( 'Belgium', 'wf-usps-woocommerce-shipping' ),
			'PW' => __( 'Belau', 'wf-usps-woocommerce-shipping' ),
			'BZ' => __( 'Belize', 'wf-usps-woocommerce-shipping' ),
			'BJ' => __( 'Benin', 'wf-usps-woocommerce-shipping' ),
			'BM' => __( 'Bermuda', 'wf-usps-woocommerce-shipping' ),
			'BT' => __( 'Bhutan', 'wf-usps-woocommerce-shipping' ),
			'BO' => __( 'Bolivia', 'wf-usps-woocommerce-shipping' ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'wf-usps-woocommerce-shipping' ),
			'BA' => __( 'Bosnia and Herzegovina', 'wf-usps-woocommerce-shipping' ),
			'BW' => __( 'Botswana', 'wf-usps-woocommerce-shipping' ),
			'BV' => __( 'Bouvet Island', 'wf-usps-woocommerce-shipping' ),
			'BR' => __( 'Brazil', 'wf-usps-woocommerce-shipping' ),
			'IO' => __( 'British Indian Ocean Territory', 'wf-usps-woocommerce-shipping' ),
			'VG' => __( 'British Virgin Islands', 'wf-usps-woocommerce-shipping' ),
			'BN' => __( 'Brunei', 'wf-usps-woocommerce-shipping' ),
			'BG' => __( 'Bulgaria', 'wf-usps-woocommerce-shipping' ),
			'BF' => __( 'Burkina Faso', 'wf-usps-woocommerce-shipping' ),
			'BI' => __( 'Burundi', 'wf-usps-woocommerce-shipping' ),
			'KH' => __( 'Cambodia', 'wf-usps-woocommerce-shipping' ),
			'CM' => __( 'Cameroon', 'wf-usps-woocommerce-shipping' ),
			'CA' => __( 'Canada', 'wf-usps-woocommerce-shipping' ),
			'CV' => __( 'Cape Verde', 'wf-usps-woocommerce-shipping' ),
			'KY' => __( 'Cayman Islands', 'wf-usps-woocommerce-shipping' ),
			'CF' => __( 'Central African Republic', 'wf-usps-woocommerce-shipping' ),
			'TD' => __( 'Chad', 'wf-usps-woocommerce-shipping' ),
			'CL' => __( 'Chile', 'wf-usps-woocommerce-shipping' ),
			'CN' => __( 'China', 'wf-usps-woocommerce-shipping' ),
			'CX' => __( 'Christmas Island', 'wf-usps-woocommerce-shipping' ),
			'CC' => __( 'Cocos (Keeling) Islands', 'wf-usps-woocommerce-shipping' ),
			'CO' => __( 'Colombia', 'wf-usps-woocommerce-shipping' ),
			'KM' => __( 'Comoros', 'wf-usps-woocommerce-shipping' ),
			'CG' => __( 'Congo (Brazzaville)', 'wf-usps-woocommerce-shipping' ),
			'CD' => __( 'Congo (Kinshasa)', 'wf-usps-woocommerce-shipping' ),
			'CK' => __( 'Cook Islands', 'wf-usps-woocommerce-shipping' ),
			'CR' => __( 'Costa Rica', 'wf-usps-woocommerce-shipping' ),
			'HR' => __( 'Croatia', 'wf-usps-woocommerce-shipping' ),
			'CU' => __( 'Cuba', 'wf-usps-woocommerce-shipping' ),
			'CW' => __( 'Cura&Ccedil;ao', 'wf-usps-woocommerce-shipping' ),
			'CY' => __( 'Cyprus', 'wf-usps-woocommerce-shipping' ),
			'CZ' => __( 'Czech Republic', 'wf-usps-woocommerce-shipping' ),
			'DK' => __( 'Denmark', 'wf-usps-woocommerce-shipping' ),
			'DJ' => __( 'Djibouti', 'wf-usps-woocommerce-shipping' ),
			'DM' => __( 'Dominica', 'wf-usps-woocommerce-shipping' ),
			'DO' => __( 'Dominican Republic', 'wf-usps-woocommerce-shipping' ),
			'EC' => __( 'Ecuador', 'wf-usps-woocommerce-shipping' ),
			'EG' => __( 'Egypt', 'wf-usps-woocommerce-shipping' ),
			'SV' => __( 'El Salvador', 'wf-usps-woocommerce-shipping' ),
			'GQ' => __( 'Equatorial Guinea', 'wf-usps-woocommerce-shipping' ),
			'ER' => __( 'Eritrea', 'wf-usps-woocommerce-shipping' ),
			'EE' => __( 'Estonia', 'wf-usps-woocommerce-shipping' ),
			'ET' => __( 'Ethiopia', 'wf-usps-woocommerce-shipping' ),
			'FK' => __( 'Falkland Islands', 'wf-usps-woocommerce-shipping' ),
			'FO' => __( 'Faroe Islands', 'wf-usps-woocommerce-shipping' ),
			'FJ' => __( 'Fiji', 'wf-usps-woocommerce-shipping' ),
			'FI' => __( 'Finland', 'wf-usps-woocommerce-shipping' ),
			'FR' => __( 'France', 'wf-usps-woocommerce-shipping' ),
			'GF' => __( 'French Guiana', 'wf-usps-woocommerce-shipping' ),
			'PF' => __( 'French Polynesia', 'wf-usps-woocommerce-shipping' ),
			'TF' => __( 'French Southern Territories', 'wf-usps-woocommerce-shipping' ),
			'GA' => __( 'Gabon', 'wf-usps-woocommerce-shipping' ),
			'GM' => __( 'Gambia', 'wf-usps-woocommerce-shipping' ),
			'GE' => __( 'Georgia', 'wf-usps-woocommerce-shipping' ),
			'DE' => __( 'Germany', 'wf-usps-woocommerce-shipping' ),
			'GH' => __( 'Ghana', 'wf-usps-woocommerce-shipping' ),
			'GI' => __( 'Gibraltar', 'wf-usps-woocommerce-shipping' ),
			'GR' => __( 'Greece', 'wf-usps-woocommerce-shipping' ),
			'GL' => __( 'Greenland', 'wf-usps-woocommerce-shipping' ),
			'GD' => __( 'Grenada', 'wf-usps-woocommerce-shipping' ),
			'GP' => __( 'Guadeloupe', 'wf-usps-woocommerce-shipping' ),
			'GT' => __( 'Guatemala', 'wf-usps-woocommerce-shipping' ),
			'GG' => __( 'Guernsey', 'wf-usps-woocommerce-shipping' ),
			'GN' => __( 'Guinea', 'wf-usps-woocommerce-shipping' ),
			'GW' => __( 'Guinea-Bissau', 'wf-usps-woocommerce-shipping' ),
			'GY' => __( 'Guyana', 'wf-usps-woocommerce-shipping' ),
			'HT' => __( 'Haiti', 'wf-usps-woocommerce-shipping' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'wf-usps-woocommerce-shipping' ),
			'HN' => __( 'Honduras', 'wf-usps-woocommerce-shipping' ),
			'HK' => __( 'Hong Kong', 'wf-usps-woocommerce-shipping' ),
			'HU' => __( 'Hungary', 'wf-usps-woocommerce-shipping' ),
			'IS' => __( 'Iceland', 'wf-usps-woocommerce-shipping' ),
			'IN' => __( 'India', 'wf-usps-woocommerce-shipping' ),
			'ID' => __( 'Indonesia', 'wf-usps-woocommerce-shipping' ),
			'IR' => __( 'Iran', 'wf-usps-woocommerce-shipping' ),
			'IQ' => __( 'Iraq', 'wf-usps-woocommerce-shipping' ),
			'IE' => __( 'Ireland', 'wf-usps-woocommerce-shipping' ),
			'IM' => __( 'Isle of Man', 'wf-usps-woocommerce-shipping' ),
			'IL' => __( 'Israel', 'wf-usps-woocommerce-shipping' ),
			'IT' => __( 'Italy', 'wf-usps-woocommerce-shipping' ),
			'CI' => __( 'Ivory Coast', 'wf-usps-woocommerce-shipping' ),
			'JM' => __( 'Jamaica', 'wf-usps-woocommerce-shipping' ),
			'JP' => __( 'Japan', 'wf-usps-woocommerce-shipping' ),
			'JE' => __( 'Jersey', 'wf-usps-woocommerce-shipping' ),
			'JO' => __( 'Jordan', 'wf-usps-woocommerce-shipping' ),
			'KZ' => __( 'Kazakhstan', 'wf-usps-woocommerce-shipping' ),
			'KE' => __( 'Kenya', 'wf-usps-woocommerce-shipping' ),
			'KI' => __( 'Kiribati', 'wf-usps-woocommerce-shipping' ),
			'KW' => __( 'Kuwait', 'wf-usps-woocommerce-shipping' ),
			'KG' => __( 'Kyrgyzstan', 'wf-usps-woocommerce-shipping' ),
			'LA' => __( 'Laos', 'wf-usps-woocommerce-shipping' ),
			'LV' => __( 'Latvia', 'wf-usps-woocommerce-shipping' ),
			'LB' => __( 'Lebanon', 'wf-usps-woocommerce-shipping' ),
			'LS' => __( 'Lesotho', 'wf-usps-woocommerce-shipping' ),
			'LR' => __( 'Liberia', 'wf-usps-woocommerce-shipping' ),
			'LY' => __( 'Libya', 'wf-usps-woocommerce-shipping' ),
			'LI' => __( 'Liechtenstein', 'wf-usps-woocommerce-shipping' ),
			'LT' => __( 'Lithuania', 'wf-usps-woocommerce-shipping' ),
			'LU' => __( 'Luxembourg', 'wf-usps-woocommerce-shipping' ),
			'MO' => __( 'Macao S.A.R., China', 'wf-usps-woocommerce-shipping' ),
			'MK' => __( 'Macedonia', 'wf-usps-woocommerce-shipping' ),
			'MG' => __( 'Madagascar', 'wf-usps-woocommerce-shipping' ),
			'MW' => __( 'Malawi', 'wf-usps-woocommerce-shipping' ),
			'MY' => __( 'Malaysia', 'wf-usps-woocommerce-shipping' ),
			'MV' => __( 'Maldives', 'wf-usps-woocommerce-shipping' ),
			'ML' => __( 'Mali', 'wf-usps-woocommerce-shipping' ),
			'MT' => __( 'Malta', 'wf-usps-woocommerce-shipping' ),
			'MH' => __( 'Marshall Islands', 'wf-usps-woocommerce-shipping' ),
			'MQ' => __( 'Martinique', 'wf-usps-woocommerce-shipping' ),
			'MR' => __( 'Mauritania', 'wf-usps-woocommerce-shipping' ),
			'MU' => __( 'Mauritius', 'wf-usps-woocommerce-shipping' ),
			'YT' => __( 'Mayotte', 'wf-usps-woocommerce-shipping' ),
			'MX' => __( 'Mexico', 'wf-usps-woocommerce-shipping' ),
			'FM' => __( 'Micronesia', 'wf-usps-woocommerce-shipping' ),
			'MD' => __( 'Moldova', 'wf-usps-woocommerce-shipping' ),
			'MC' => __( 'Monaco', 'wf-usps-woocommerce-shipping' ),
			'MN' => __( 'Mongolia', 'wf-usps-woocommerce-shipping' ),
			'ME' => __( 'Montenegro', 'wf-usps-woocommerce-shipping' ),
			'MS' => __( 'Montserrat', 'wf-usps-woocommerce-shipping' ),
			'MA' => __( 'Morocco', 'wf-usps-woocommerce-shipping' ),
			'MZ' => __( 'Mozambique', 'wf-usps-woocommerce-shipping' ),
			'MM' => __( 'Myanmar', 'wf-usps-woocommerce-shipping' ),
			'NA' => __( 'Namibia', 'wf-usps-woocommerce-shipping' ),
			'NR' => __( 'Nauru', 'wf-usps-woocommerce-shipping' ),
			'NP' => __( 'Nepal', 'wf-usps-woocommerce-shipping' ),
			'NL' => __( 'Netherlands', 'wf-usps-woocommerce-shipping' ),
			'AN' => __( 'Netherlands Antilles', 'wf-usps-woocommerce-shipping' ),
			'NC' => __( 'New Caledonia', 'wf-usps-woocommerce-shipping' ),
			'NZ' => __( 'New Zealand', 'wf-usps-woocommerce-shipping' ),
			'NI' => __( 'Nicaragua', 'wf-usps-woocommerce-shipping' ),
			'NE' => __( 'Niger', 'wf-usps-woocommerce-shipping' ),
			'NG' => __( 'Nigeria', 'wf-usps-woocommerce-shipping' ),
			'NU' => __( 'Niue', 'wf-usps-woocommerce-shipping' ),
			'NF' => __( 'Norfolk Island', 'wf-usps-woocommerce-shipping' ),
			'KP' => __( 'North Korea', 'wf-usps-woocommerce-shipping' ),
			'NO' => __( 'Norway', 'wf-usps-woocommerce-shipping' ),
			'OM' => __( 'Oman', 'wf-usps-woocommerce-shipping' ),
			'PK' => __( 'Pakistan', 'wf-usps-woocommerce-shipping' ),
			'PS' => __( 'Palestinian Territory', 'wf-usps-woocommerce-shipping' ),
			'PA' => __( 'Panama', 'wf-usps-woocommerce-shipping' ),
			'PG' => __( 'Papua New Guinea', 'wf-usps-woocommerce-shipping' ),
			'PY' => __( 'Paraguay', 'wf-usps-woocommerce-shipping' ),
			'PE' => __( 'Peru', 'wf-usps-woocommerce-shipping' ),
			'PH' => __( 'Philippines', 'wf-usps-woocommerce-shipping' ),
			'PN' => __( 'Pitcairn', 'wf-usps-woocommerce-shipping' ),
			'PL' => __( 'Poland', 'wf-usps-woocommerce-shipping' ),
			'PT' => __( 'Portugal', 'wf-usps-woocommerce-shipping' ),
			'QA' => __( 'Qatar', 'wf-usps-woocommerce-shipping' ),
			'RE' => __( 'Reunion', 'wf-usps-woocommerce-shipping' ),
			'RO' => __( 'Romania', 'wf-usps-woocommerce-shipping' ),
			'RU' => __( 'Russia', 'wf-usps-woocommerce-shipping' ),
			'RW' => __( 'Rwanda', 'wf-usps-woocommerce-shipping' ),
			'BL' => __( 'Saint Barth&eacute;lemy', 'wf-usps-woocommerce-shipping' ),
			'SH' => __( 'Saint Helena', 'wf-usps-woocommerce-shipping' ),
			'KN' => __( 'Saint Kitts and Nevis', 'wf-usps-woocommerce-shipping' ),
			'LC' => __( 'Saint Lucia', 'wf-usps-woocommerce-shipping' ),
			'MF' => __( 'Saint Martin (French part)', 'wf-usps-woocommerce-shipping' ),
			'SX' => __( 'Saint Martin (Dutch part)', 'wf-usps-woocommerce-shipping' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'wf-usps-woocommerce-shipping' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'wf-usps-woocommerce-shipping' ),
			'SM' => __( 'San Marino', 'wf-usps-woocommerce-shipping' ),
			'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'wf-usps-woocommerce-shipping' ),
			'SA' => __( 'Saudi Arabia', 'wf-usps-woocommerce-shipping' ),
			'SN' => __( 'Senegal', 'wf-usps-woocommerce-shipping' ),
			'RS' => __( 'Serbia', 'wf-usps-woocommerce-shipping' ),
			'SC' => __( 'Seychelles', 'wf-usps-woocommerce-shipping' ),
			'SL' => __( 'Sierra Leone', 'wf-usps-woocommerce-shipping' ),
			'SG' => __( 'Singapore', 'wf-usps-woocommerce-shipping' ),
			'SK' => __( 'Slovakia', 'wf-usps-woocommerce-shipping' ),
			'SI' => __( 'Slovenia', 'wf-usps-woocommerce-shipping' ),
			'SB' => __( 'Solomon Islands', 'wf-usps-woocommerce-shipping' ),
			'SO' => __( 'Somalia', 'wf-usps-woocommerce-shipping' ),
			'ZA' => __( 'South Africa', 'wf-usps-woocommerce-shipping' ),
			'GS' => __( 'South Georgia/Sandwich Islands', 'wf-usps-woocommerce-shipping' ),
			'KR' => __( 'South Korea', 'wf-usps-woocommerce-shipping' ),
			'SS' => __( 'South Sudan', 'wf-usps-woocommerce-shipping' ),
			'ES' => __( 'Spain', 'wf-usps-woocommerce-shipping' ),
			'LK' => __( 'Sri Lanka', 'wf-usps-woocommerce-shipping' ),
			'SD' => __( 'Sudan', 'wf-usps-woocommerce-shipping' ),
			'SR' => __( 'Suriname', 'wf-usps-woocommerce-shipping' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'wf-usps-woocommerce-shipping' ),
			'SZ' => __( 'Swaziland', 'wf-usps-woocommerce-shipping' ),
			'SE' => __( 'Sweden', 'wf-usps-woocommerce-shipping' ),
			'CH' => __( 'Switzerland', 'wf-usps-woocommerce-shipping' ),
			'SY' => __( 'Syria', 'wf-usps-woocommerce-shipping' ),
			'TW' => __( 'Taiwan', 'wf-usps-woocommerce-shipping' ),
			'TJ' => __( 'Tajikistan', 'wf-usps-woocommerce-shipping' ),
			'TZ' => __( 'Tanzania', 'wf-usps-woocommerce-shipping' ),
			'TH' => __( 'Thailand', 'wf-usps-woocommerce-shipping' ),
			'TL' => __( 'Timor-Leste', 'wf-usps-woocommerce-shipping' ),
			'TG' => __( 'Togo', 'wf-usps-woocommerce-shipping' ),
			'TK' => __( 'Tokelau', 'wf-usps-woocommerce-shipping' ),
			'TO' => __( 'Tonga', 'wf-usps-woocommerce-shipping' ),
			'TT' => __( 'Trinidad and Tobago', 'wf-usps-woocommerce-shipping' ),
			'TN' => __( 'Tunisia', 'wf-usps-woocommerce-shipping' ),
			'TR' => __( 'Turkey', 'wf-usps-woocommerce-shipping' ),
			'TM' => __( 'Turkmenistan', 'wf-usps-woocommerce-shipping' ),
			'TC' => __( 'Turks and Caicos Islands', 'wf-usps-woocommerce-shipping' ),
			'TV' => __( 'Tuvalu', 'wf-usps-woocommerce-shipping' ),
			'UG' => __( 'Uganda', 'wf-usps-woocommerce-shipping' ),
			'UA' => __( 'Ukraine', 'wf-usps-woocommerce-shipping' ),
			'AE' => __( 'United Arab Emirates', 'wf-usps-woocommerce-shipping' ),
			'GB' => __( 'United Kingdom', 'wf-usps-woocommerce-shipping' ),
			'US' => __( 'United States', 'wf-usps-woocommerce-shipping' ),
			'UY' => __( 'Uruguay', 'wf-usps-woocommerce-shipping' ),
			'UZ' => __( 'Uzbekistan', 'wf-usps-woocommerce-shipping' ),
			'VU' => __( 'Vanuatu', 'wf-usps-woocommerce-shipping' ),
			'VA' => __( 'Vatican', 'wf-usps-woocommerce-shipping' ),
			'VE' => __( 'Venezuela', 'wf-usps-woocommerce-shipping' ),
			'VN' => __( 'Vietnam', 'wf-usps-woocommerce-shipping' ),
			'WF' => __( 'Wallis and Futuna', 'wf-usps-woocommerce-shipping' ),
			'EH' => __( 'Western Sahara', 'wf-usps-woocommerce-shipping' ),
			'WS' => __( 'Western Samoa', 'wf-usps-woocommerce-shipping' ),
			'YE' => __( 'Yemen', 'wf-usps-woocommerce-shipping' ),
			'ZM' => __( 'Zambia', 'wf-usps-woocommerce-shipping' ),
			'ZW' => __( 'Zimbabwe', 'woocommerce' )
		));

	    if ( isset( $countries[ $code ] ) ) {
		    return strtoupper( $countries[ $code ] );
	    } else {
		    return false;
	    }
    }

    /**
     * calculate_flat_rate_box_rate function.
     *
     * @access private
     * @param mixed $package
     * @return void
     */
    private function calculate_flat_rate_box_rate( $package, $box_type = 'priority' ) {
	    global $woocommerce;

	    $cost = 0;

	  	if ( ! class_exists( 'WF_Boxpack' ) )
	  		include_once 'class-wf-packing.php';

	    $boxpack  = new WF_Boxpack();
	    $domestic = in_array( $package['destination']['country'], $this->domestic ) ? true : false;
	    $added    = array();
		$this->flat_rate_boxes          = apply_filters( 'usps_flat_rate_boxes', $this->flat_rate_boxes );
	    // Define boxes
		foreach ( $this->flat_rate_boxes as $service_code => $box ) {

			if ( $box['box_type'] != $box_type )
				continue;

			$domestic_service = substr( $service_code, 0, 1 ) == 'd' ? true : false;

			if ( $domestic && $domestic_service || ! $domestic && ! $domestic_service ) {
				$newbox = $boxpack->add_box( $box['length'], $box['width'], $box['height'] );

				$newbox->set_max_weight( $box['weight'] );
				$newbox->set_id( $service_code );

				if ( isset( $box['volume'] ) && method_exists( $newbox, 'set_volume' ) ) {
					$newbox->set_volume( $box['volume'] );
				}

				if ( isset( $box['type'] ) && method_exists( $newbox, 'set_type' ) ) {
					$newbox->set_type( $box['type'] );
				}

				$added[] = $service_code . ' - ' . $box['name'] . ' (' . $box['length'] . 'x' . $box['width'] . 'x' . $box['height'] . ')';
			}
		}

		$this->debug( 'Calculating USPS Flat Rate with boxes: ' . implode( ', ', $added ) );

		// Add items
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() )
				continue;
			
			$skip_product = apply_filters('wf_shipping_skip_product',false, $values, $package['contents']);
			if($skip_product){
				continue;
			}

			if ( $values['data']->length && $values['data']->height && $values['data']->width && $values['data']->weight ) {

				$dimensions = array( wc_get_dimension( $values['data']->length, 'in' ), wc_get_dimension( $values['data']->height, 'in' ), wc_get_dimension( $values['data']->width, 'in' ) );

			} else {
				$this->debug( sprintf( __( 'Product #%d is missing dimensions! Using 1x1x1.', 'wf-usps-woocommerce-shipping' ), $item_id ), 'error' );

				$dimensions = array( 1, 1, 1 );
			}

			for ( $i = 0; $i < $values['quantity']; $i ++ ) {
				$boxpack->add_item(
					wc_get_dimension( $dimensions[2], 'in' ),
					wc_get_dimension( $dimensions[1], 'in' ),
					wc_get_dimension( $dimensions[0], 'in' ),
					wc_get_weight( $values['data']->get_weight(), 'lbs' ),
					$values['data']->get_price(),
					$item_id //WF: Adding Item Id and Quantity as meta.
				);
			}
		}

		// Pack it
		$boxpack->pack();

		// Get packages
		$flat_packages = $boxpack->get_packages();

		if ( $flat_packages ) {
			foreach ( $flat_packages as $flat_package ) {

				if ( isset( $this->flat_rate_boxes[ $flat_package->id ] ) ) {

					$this->debug( 'Packed ' . $flat_package->id . ' - ' . $this->flat_rate_boxes[ $flat_package->id ]['name'] );

					// Get pricing
					$box_pricing  = $this->settings['shippingrates'] == 'ONLINE' && isset( $this->flat_rate_pricing[ $flat_package->id ]['online'] ) ? $this->flat_rate_pricing[ $flat_package->id ]['online'] : $this->flat_rate_pricing[ $flat_package->id ]['retail'];

					if ( is_array( $box_pricing ) ) {
						if ( isset( $box_pricing[ $package['destination']['country'] ] ) ) {
							$box_cost = $box_pricing[ $package['destination']['country'] ];
						} else {
							$box_cost = $box_pricing['*'];
						}
					} else {
						$box_cost = $box_pricing;
					}

					// Fees
					if ( ! empty( $this->flat_rate_fee ) ) {
						$sym = substr( $this->flat_rate_fee, 0, 1 );
						$fee = $sym == '-' ? substr( $this->flat_rate_fee, 1 ) : $this->flat_rate_fee;

						if ( strstr( $fee, '%' ) ) {
							$fee = str_replace( '%', '', $fee );

							if ( $sym == '-' )
								$box_cost = $box_cost - ( $box_cost * ( floatval( $fee ) / 100 ) );
							else
								$box_cost = $box_cost + ( $box_cost * ( floatval( $fee ) / 100 ) );
						} else {
							if ( $sym == '-' )
								$box_cost = $box_cost - $fee;
							else
								$box_cost += $fee;
						}

						if ( $box_cost < 0 )
							$box_cost = 0;
					}

					$cost += $box_cost;

				} else {
					return; // no match
				}

			}

			if ( $box_type == 'express' ) {
				$label = ! empty( $this->settings['flat_rate_express_title'] ) ? $this->settings['flat_rate_express_title'] : ( $domestic ? '' : 'International ' ) . 'Priority Mail Express Flat Rate&#0174;';
			} else {
				$label = ! empty( $this->settings['flat_rate_priority_title'] ) ? $this->settings['flat_rate_priority_title'] : ( $domestic ? '' : 'International ' ) . 'Priority Mail Flat Rate&#0174;';
			}

			return array(
				'id' 	=> $this->id . ':flat_rate_box_' . $box_type,
				'label' => $label,
				'cost' 	=> $cost,
				'sort'  => ( $box_type == 'express' ? -1 : -2 )
			);
		}
    }
	
	
    public function debug( $message, $type = 'notice' ) {
    	if ( $this->debug && !is_admin()) { //WF: is_admin check added.
    		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
    			wc_add_notice( $message, $type );
    		} else {
    			global $woocommerce;
    			$woocommerce->add_message( $message );
    		}
		}
    }

	/**
     * wf_get_package_requests function.
     *
     * @access public
     * @return requests
     */
    public function wf_get_api_rate_box_data( $package, $packing_method ) {
	    $this->packing_method 	= $packing_method;
		$requests 				= $this->get_package_requests( $package );
		$package_data_array 	= array();
		
		if ( $requests ) {
			foreach ( $requests as $key => $request ) {
				$package_data 				= array();
				$xml_usps_package_data 		= simplexml_load_string($request['request_data']);
				$package_data['ID'] 		= $xml_usps_package_data->attributes()->ID;
				
				// PS: Some of PHP versions doesn't allow to combining below two line of code as one. 
				// id_array must have value at this point. Force setting it to 1 if it is not.
				$id_array 							= explode( ":", $xml_usps_package_data->attributes()->ID );
				$package_data[ 'BoxCount' ] 		= isset($id_array[1]) ? $id_array[1] : 1;
				$package_data[ 'WeightInOunces' ] 	= ( (float)$xml_usps_package_data->Pounds * 16 ) + (float)$xml_usps_package_data->Ounces;
				$package_data[ 'POZipCode' ] 		= $xml_usps_package_data->ZipDestination;
				$package_data[ 'Container' ] 		= $xml_usps_package_data->Container;
				$package_data[ 'Width' ] 			= $xml_usps_package_data->Width;
				$package_data[ 'Length' ] 			= $xml_usps_package_data->Length;
				$package_data[ 'Height' ] 			= $xml_usps_package_data->Height;
				$package_data[ 'Girth' ] 			= $xml_usps_package_data->Girth;
				$package_data[ 'Size' ] 			= $xml_usps_package_data->Size;
				$package_data[ 'PackageInfo' ]		= $request['package_info']?$request['package_info']:array();
				$package_data_array[] 				= $package_data; 
			}
		}
    	return $package_data_array;
    }
	
	/**
     * wf_get_flat_rate_box_data function.
     *
     * @access public
     * @return package_data_array
     */
	public function wf_get_flat_rate_box_data( $package, $flat_rate_boxes, $box_type = 'priority' ) {
	    global $woocommerce;

	  	if ( ! class_exists( 'WF_Boxpack' ) )
	  		include_once 'class-wf-packing.php';

	    $boxpack  = new WF_Boxpack();
	    $domestic = in_array( $package[ 'destination' ][ 'country' ], $this->domestic ) ? true : false;
	    $added    = array();

	    // Define boxes
		foreach ( $flat_rate_boxes as $service_code => $box ) {

			if ( $box['box_type'] != $box_type )
				continue;

			$domestic_service = substr( $service_code, 0, 1 ) == 'd' ? true : false;

			if ( $domestic && $domestic_service || ! $domestic && ! $domestic_service ) {
				$newbox = $boxpack->add_box( $box['length'], $box['width'], $box['height'] );

				$newbox->set_max_weight( $box['weight'] );
				$newbox->set_id( $service_code );

				if ( isset( $box['volume'] ) && method_exists( $newbox, 'set_volume' ) ) {
					$newbox->set_volume( $box['volume'] );
				}
				if ( isset( $box['type'] ) && method_exists( $newbox, 'set_type' ) ) {
					$newbox->set_type( $box['type'] );
				}

				$added[] = $service_code . ' - ' . $box['name'] . ' (' . $box['length'] . 'x' . $box['width'] . 'x' . $box['height'] . ')';
			}
		}

		// Add items
		foreach ( $package['contents'] as $item_id => $values ) {
			if ( ! $values['data']->needs_shipping() )
				continue;				
			
			$skip_product = apply_filters('wf_shipping_skip_product',false, $values, $package['contents']);
			if($skip_product){
				continue;
			}

			if ( $values['data']->length && $values['data']->height && $values['data']->width && $values['data']->weight ) {
				$dimensions = array( $values['data']->length, $values['data']->height, $values['data']->width );
			} else {
				//$this->debug( sprintf( __( 'Product #%d is missing dimensions! Using 1x1x1.', 'wf-usps-woocommerce-shipping' ), $item_id ), 'error' );
				$dimensions = array( 1, 1, 1 );
			}

			for ( $i = 0; $i < $values['quantity']; $i ++ ) {
				$boxpack->add_item(
					wc_get_dimension( $dimensions[2], 'in' ),
					wc_get_dimension( $dimensions[1], 'in' ),
					wc_get_dimension( $dimensions[0], 'in' ),
					wc_get_weight( $values['data']->get_weight(), 'lbs' ),
					$values['data']->get_price(),
					$item_id //WF: Adding Item Id and Quantity as meta.
				);
			}
		}

		// Pack it
		$boxpack->pack();
		// Get packages
		$flat_packages 		= $boxpack->get_packages();
		$package_data_array	= array();
		
		if ( $flat_packages ) {
			//Prepare the result in an array and return.
			foreach ( $flat_packages as $flat_package ) {
				if ( isset( $this->flat_rate_boxes[ $flat_package->id ] ) ) {
					$package_data = array();

					// WF: Get weight of order.
					$size   = 'REGULAR';
					if ( $this->flat_rate_boxes[ $flat_package->id ]["width" ] && 
							$this->flat_rate_boxes[ $flat_package->id ]["length"] && 
							$this->flat_rate_boxes[ $flat_package->id ]["height"] ) {
						$dimensions = array( wc_get_dimension( $this->flat_rate_boxes[ $flat_package->id ]["length"], 'in' ), wc_get_dimension( $this->flat_rate_boxes[ $flat_package->id ]["height" ], 'in' ), wc_get_dimension( $this->flat_rate_boxes[ $flat_package->id ]["width" ], 'in' ) );
						
						sort( $dimensions );
						if ( max( $dimensions ) > 12 ) {
							$size   = 'LARGE';
						}
						$girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];
					} else {
						$dimensions = array( 0, 0, 0 );
						$girth      = 0;
					}
 
					$boxpack_items = $flat_package->packed;

					$item_quantity_map = array();
					foreach ( $boxpack_items as $boxpack_item ) {
						$meta = $boxpack_item->meta;
						$item_quantity_map[$meta] = isset( $item_quantity_map[$meta] ) ? ( $item_quantity_map[$meta] + 1 ) : 1;
					}

					$package_data[ 'ID' ] 				= WF_USPS_ID.':flat_rate_box_'.$box_type.':'.$flat_package->id;
					$package_data[ 'BoxCount' ] 		= '1';
					$package_data[ 'WeightInOunces' ] 	= ($this->flat_rate_boxes[ $flat_package->id ]["weight" ] * 16);
					$package_data[ 'POZipCode' ] 		= $package['destination']['postcode'];
					$package_data[ 'Container' ] 		= '';
					$package_data[ 'Width' ] 			= $this->flat_rate_boxes[ $flat_package->id ]["width" ];
					$package_data[ 'Length' ] 			= $this->flat_rate_boxes[ $flat_package->id ]["length"];
					$package_data[ 'Height' ] 			= $this->flat_rate_boxes[ $flat_package->id ]["height"];
					$package_data[ 'Girth' ] 			= $girth;
					$package_data[ 'Size' ] 			= $size;
					$package_data[ 'Items' ] 			= $item_quantity_map;
					$package_data_array[] 				= $package_data;
				} else {
					return false; // no match
				}
			}
			return $package_data_array;
		}
		return false;
    }
	function wf_get_variation_data_from_variation_id( $item_id ) {
		$_product = new WC_Product_Variation( $item_id );
		$variation_data = $_product->get_variation_attributes();
		$variation_detail = woocommerce_get_formatted_variation( $variation_data, true );  // this will give all variation detail in one line
		// $variation_detail = woocommerce_get_formatted_variation( $variation_data, false);  // this will give all variation detail one by one
		return $variation_detail; // $variation_detail will return string containing variation detail which can be used to print on website
		// return $variation_data; // $variation_data will return only the data which can be used to store variation data
	}	
	public function filter_by_selected_flat_rate_boxes($boxes){
		if(!array($this->selected_flat_rate_boxes)){
			return $boxes;
		}
		if(empty($this->selected_flat_rate_boxes)){
			return $boxes;
		}
		$new_boxes = array();
		foreach($this->selected_flat_rate_boxes as $box_code){
			$new_boxes[$box_code]	=	$boxes[$box_code];
		}
		return $new_boxes;
	}
	
	/*	
	*	Get box name by box id
	*/
	private function get_box_name($package_id){
		// If the id is not present in boxes then package id will be treated as package name
		$package_name	=	$package_id;
		if(!empty($this->boxes) && is_array($this->boxes)){			
			if(array_key_exists($package_id,	$this->boxes)){
				// If the id is present in boxes but name is not set then package id will be treated as name
				$package_name	=	isset( $this->boxes[$package_id]['name'] )? $this->boxes[$package_id]['name']:$package_id;
			}			
		}		
		return $package_name;
	}
}
