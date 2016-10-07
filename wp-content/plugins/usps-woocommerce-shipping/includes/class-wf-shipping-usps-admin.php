<?php
// WF: Shipping Label: Admin and related code.

define("SHIPPING_METHOD_USPS_TYPE_FLAT_RATE", "flat_rate");

class WF_Shipping_USPS_Admin {
    
    const TRACKING_URL = "https://tools.usps.com/go/TrackConfirmAction.action?tRef=fullpage&tLc=1&text28777=&tLabels=";
    
	private $usps_services = array (
					"Priority"			=> "Priority",
					"First-Class"		=> "First Class",
					"Standard Post"		=> "USPS Retail Ground",
					"Media Mail"		=> "Media Mail",
					"Library Mail"		=> "Library Mail",
				);
	private $usps_int_services = array (
					"ExpressMailIntl"		=> "Priority Mail Express International",
					"PriorityMailIntl"		=> "Priority Mail International",
					"FirstClassMailIntl"	=> "First Class Mail International",
				);
				
	private $debug;

	public $deliveryconfirmationv4_requests;
	
	public function __construct(){
		$this->wf_shipping_usps_init();

		//Print Shipping Label.
		if ( is_admin() ) { 
			add_action( 'add_meta_boxes', array( $this, 'wf_add_usps_metabox' ), 15 );
			add_action( 'admin_notices', array( $this, 'wf_admin_notice' ), 15 );
			
			// Shipment Tracking.
			add_action( 'woocommerce_process_shop_order_meta', array($this, 'wf_process_order_meta_fields_save'), 15 );
		}
		
		// Shipment Tracking - Customer Order Details Page.
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'wf_woocommerce_order_items_table' ) );
        add_action( 'woocommerce_email_order_meta', array( $this, 'wf_add_usps_tracking_info_to_email'), 20 );
		
		if ( isset( $_GET['wf_usps_getlabel'] ) ) {
			add_action( 'init', array( $this, 'wf_usps_getlabel' ), 15 );
		}
		else if ( isset( $_GET['wf_usps_reset_labels'] ) ) {
			add_action( 'init', array( $this, 'wf_usps_reset_labels' ), 15 );
		}
		else if ( isset( $_GET['wf_usps_printlabel'] ) ) {
			add_action( 'init', array( $this, 'wf_usps_printlabel' ), 15 );
		}
		else if ( isset( $_GET['wf_usps_fill_tracking_ids'] ) ) {
			add_action( 'init', array( $this, 'wf_usps_fill_tracking_ids' ), 15 );
		}

		// Shipment Tracking - Admin end.
		if ( isset( $_GET['wf_usps_track_shipment'] ) ) {
			add_action( 'init', array( $this, 'wf_usps_track_shipment' ), 15 );
		}
	}
	
	function wf_admin_notice(){
		global $pagenow;
		global $post;
		
		if( !isset( $_GET["wfuspsmsg"] ) && empty( $_GET["wfuspsmsg"] ) ) {
			return;
		}
	
		$wfuspsmsg = $_GET["wfuspsmsg"];
		
		switch ( $wfuspsmsg ) {
			case "0":
				echo '<div class="error"><p>USPS: Sorry, Unable to proceed.</p></div>';
				break;
			case "1":
			case "2":
				$wfuspsmsg = get_post_meta( $post->ID, 'wfuspsmsg', true);
				echo '<div class="error"><p>'.$wfuspsmsg.'</p></div>';
				break;
			case "3":
				$wfuspsmsg = get_post_meta( $post->ID, 'wfuspsmsg', true);
				echo '<div class="updated"><p>'.$wfuspsmsg.'</p></div>';
				break;
			case "4":
				echo '<div class="error"><p>USPS: Unable to track the shipment. Please cross check shipment id or try after some time.</p></div>';
				break;
			case "5":
				$wfuspsmsg = get_post_meta( $post->ID, 'wfuspsmsg', true);
				echo '<div class="updated"><p>'.$wfuspsmsg.'</p></div>';
				break;
			case "6":
				echo '<div class="updated"><p>USPS: Shipment tracking IDs are empty.</p></div>';
				break;
			case "7":
				echo '<div class="updated"><p>USPS: Labels are now ready for printing.</p></div>';
				break;
			case "8":
				echo '<div class="updated"><p>USPS: Unfortunately, unable to create labels this time.</p></div>';
				break;
			case "9":
				echo '<div class="updated"><p>USPS: All labels for this order is reset. You can re-create shipment now.</p></div>';
				break;
			case "10":
				echo '<div class="updated"><p>USPS: Shipment Label Tracking Numbers updated to Shipment Tracking Field.</p></div>';
				break;
			case "11":
				echo '<div class="error"><p>USPS: Sorry. International label printing for multiple packages are not supported currently.</p></div>';
				break;
			case "12":
				echo '<div class="error"><p>USPS: Unable to show tracking info. Please check the IDs and ensure those are properly formatted using comma.</p></div>';
				break;
			case "13":
				$wfuspsmsg = get_post_meta( $post->ID, 'wfuspsmsg', true);
				echo '<div class="error"><p>'.$wfuspsmsg.'</p></div>';
				break;
			default:
				break;
		}
	}

	function wf_shipping_usps_init() {
		// Load USPS Settings.
		$this->settings 				= get_option( 'woocommerce_'.WF_USPS_ID.'_settings', null ); 
		$this->disbleShipmentTracking	= isset( $this->settings['disbleShipmentTracking'] ) ? $this->settings['disbleShipmentTracking'] : 'TrueForCustomer';
		$this->fillShipmentTracking		= isset( $this->settings['fillShipmentTracking'] ) ? $this->settings['fillShipmentTracking'] : 'Manual';
		$this->disblePrintLabel			= isset( $this->settings['disblePrintLabel'] ) ? $this->settings['disblePrintLabel'] : '';
		$this->manual_weight_dimensions		= isset( $this->settings['manual_weight_dimensions'] ) ? $this->settings['manual_weight_dimensions'] : 'no';
		$this->weight_unit = 'LBS';
		$this->dim_unit    = 'IN';
		$this->debug       = isset( $this->settings['debug_mode'] ) && $this->settings['debug_mode'] == 'yes' ? true : false;
	}
	
	
	public function debug( $message, $type = 'notice' ) {
		if ( $this->debug ) {
			echo ( $message);
		}
	}
    
    function wf_add_usps_tracking_info_to_email( $order, $sent_to_admin = false, $plain_text = false ) {
        if( ( "True" == $this->disbleShipmentTracking ) || ( "TrueForCustomer" == $this->disbleShipmentTracking ) ) {
			return;
		}
        
        $order_id 		= $order->id;
		$shipment_id_cs	= get_post_meta( $order_id, 'usps_shipment_ids', true );
        
        if( $shipment_id_cs == '' ) {
			return;
		}
        else {
            $shipment_ids = explode(",", $shipment_id_cs);
		
			if( empty( $shipment_ids ) ) {
				return;
			}
			
			echo '<h3>'.__( 'Shipping Detail', 'wf-usps-woocommerce-shipping' ).'</h3>';
			$order_notice 	= __( 'Your order is shipped via USPS. To track shipment, please follow the shipment ID(s) ', 'wf-usps-woocommerce-shipping' );
			foreach ( $shipment_ids as $shipment_id ) {
				$order_notice 	.= '<a href="'.self::TRACKING_URL.$shipment_id.'" target="_blank">'.$shipment_id.'</a>'.' | ';
			}

			echo '<p>'.$order_notice.'</p></br>';
        }
	}

	function wf_woocommerce_order_items_table ( $order ) {
		if( ( "True" == $this->disbleShipmentTracking ) || ( "TrueForCustomer" == $this->disbleShipmentTracking ) ) {
			return;
		}
		
		$order_id 		= $order->id;
		$shipment_id_cs	= get_post_meta( $order_id, 'usps_shipment_ids', true );
		
		if( $shipment_id_cs == '' ) {
			return;
		}
		
		$shipment_ids = explode(",", $shipment_id_cs);
		
		if( empty( $shipment_ids ) ) {
			return;
		}
		
		// Load USPS Settings.
		$usps_settings	= get_option( 'woocommerce_'.WF_USPS_ID.'_settings', null ); 
		$usps_user_id	= !empty( $usps_settings['user_id'] ) ? $usps_settings['user_id'] : '570CYDTE1766'; 
		$response 		= $this->wf_get_trackv2_response( $shipment_ids, $usps_user_id );
		
		if( !isset( $response["body"] ) ) {
			return;
		}
		
		$xml_response 		= simplexml_load_string( $response['body'] );
		$trackinfo_array 	= $xml_response->TrackInfo;

		echo '<h3>Shipment Tracking</h3>';
		echo '<table class="shop_table wooforce_tracking_details">
			<thead>
				<tr>
					<th class="product-name">Shipment ID<br/>(Follow link for detailed status.)</th>
					<th class="product-total">Status</th>
				</tr>
			</thead>
			<tfoot>';

			foreach ( $trackinfo_array as $trackinfo ) {
				echo '<tr>';
				echo '<th scope="row">'.'<a href="'.self::TRACKING_URL.$trackinfo->attributes()->ID.'" target="_blank">'.$trackinfo->attributes()->ID.'</a></th>';
				if( isset($trackinfo->Error ) ){
					echo '<td><span>'.$trackinfo->Error->Description.' ['.$trackinfo->Error->Number.']'.'</span></td>';
				}
				else {
					echo '<td><span>'.$trackinfo->TrackSummary.'</span></td>';
				}
				echo '</tr>';
			}
			echo '</tfoot>
		</table>';
	}

	function wf_process_order_meta_fields_save( $post_id ){
		global $wpdb, $woocommerce;
		
		if(isset( $_POST['usps_shipment_ids'] )) {
			$shipment_ids = $_POST['usps_shipment_ids'];
			update_post_meta( $post_id, 'usps_shipment_ids', $shipment_ids );
		}
	}

	function wf_add_usps_metabox(){
		global $post;
		
		if ( !$post ) return;
		
		$order = $this->wf_load_order($post->ID);
		if ( !$order ) return; 

		$shipping_service_data	= $this->wf_get_shipping_service_data( $order ); 
		
		// Shipping method is not USPS. 
		//if( $shipping_service_data && ( WF_USPS_ID == $shipping_service_data['shipping_method'] ) ) {
		if ( $shipping_service_data ) {
			if( $this->disblePrintLabel != 'yes' ) {
				add_meta_box( 'CyDUSPS_metabox', __( 'USPS Shipment Label', 'wf-usps-woocommerce-shipping' ), array( $this, 'wf_usps_metabox_content' ), 'shop_order', 'side', 'default' );
			}

			if( $this->disbleShipmentTracking != 'True' ) {
				// Shipment Tracking
				add_meta_box( 'CyDUSPSTracking_metabox', __( 'USPS Shipment Tracking', 'wf-usps-woocommerce-shipping' ), array( $this, 'wf_usps_shipment_tracking_metabox_content' ), 'shop_order', 'side', 'default' );
			}
		}
	}

	function wf_usps_shipment_tracking_metabox_content(){
		global $post;
		$shipmentId 	= '';
		$shipment_ids 	= get_post_meta( $post->ID, 'usps_shipment_ids', true );
		
		?>
		
		<div class="add_label_id">
			<strong>Enter Tracking IDs (Comma Separated)</strong>
			<textarea rows="1" cols="25" class="input-text" id="usps_shipment_ids" name="usps_shipment_ids" type="text"><?php echo $shipment_ids; ?></textarea>
		</div>
		<?php
			//$tracking_url = admin_url( '/?post='.( $post->ID ) );
            $tracking_url = admin_url( '/post.php?post='.$post->ID.'&action=edit' );
		?>
			<a class="button button-primary usps_shipment_tracking tips" href="<?php echo $tracking_url; ?>" data-tip="<?php _e('Save/Show Tracking Info', 'wf-usps-woocommerce-shipping'); ?>"><?php _e('Save/Show Tracking Info', 'wf-usps-woocommerce-shipping'); ?></a><hr style="border-color:#0074a2">
			
			<script type="text/javascript">
				jQuery("a.usps_shipment_tracking").on("click", function() {
				   location.href = this.href + '&wf_usps_track_shipment=' + jQuery('#usps_shipment_ids').val().replace(/ /g,'');
				   return false;   
				});
			</script> 
		<?php
	}

	function wf_usps_track_shipment() {
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}
		
		$post_id 		= isset( $_GET['post'] ) ? $_GET['post'] : '';
		$shipment_id_cs	= isset( $_GET['wf_usps_track_shipment'] ) ? $_GET['wf_usps_track_shipment'] : '';
		
		if( empty( $post_id ) ) {
			$wfuspsmsg = 0;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
			exit;
		}

		// Load USPS Settings.
		$usps_settings 			= get_option( 'woocommerce_'.WF_USPS_ID.'_settings', null ); 
		$usps_user_id			= !empty( $usps_settings['user_id'] ) ? $usps_settings['user_id'] : '570CYDTE1766'; 
		$usps_debug        		= isset( $usps_settings['debug_mode'] ) && $usps_settings['debug_mode'] == 'yes' ? true : false;

		if( empty( $shipment_id_cs ) ) {
			update_post_meta( $post_id, 'usps_shipment_ids', $shipment_id_cs );
			$wfuspsmsg = 6;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
			exit;
		}
		
		$shipment_ids 		= preg_split( '@,@', $shipment_id_cs, NULL, PREG_SPLIT_NO_EMPTY );
		$shipment_id_cs 	= implode( ',',$shipment_ids );

		update_post_meta( $post_id, 'usps_shipment_ids', $shipment_id_cs );

		$shipment_ids 	= explode( ",", $shipment_id_cs );

		$response 		= $this->wf_get_trackv2_response( $shipment_ids, $usps_user_id );

		if( !isset( $response["body"] ) ) {
			$wfuspsmsg = 4;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
			exit;
		}

		$xml_response		= simplexml_load_string( $response['body'] );
		$message 			= '';
		$trackinfo_array	= $xml_response->TrackInfo;
		
		if( isset( $xml_response->Number ) ) {
			if($usps_debug) {
				$message .= $xml_response->Description.' ['.$xml_response->Number.']</br>';
			}
			else {
				$wfuspsmsg = '12';
				wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
				exit;
			}
		}
		
		foreach ( $trackinfo_array as $trackinfo ) {
			$message .= '<strong>'.$trackinfo->attributes()->ID.': </strong>';
			
			if( isset( $trackinfo->Error ) ) {
				$message .= $trackinfo->Error->Description.' ['.$trackinfo->Error->Number.']</br>';
			}
			else {
				$message .= $trackinfo->TrackSummary.'</br>';
			}
		}

		$wfuspsmsg = 5;
		update_post_meta( $post_id, 'wfuspsmsg', $message );
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
		exit;
	}
	
	function wf_get_trackv2_response( $shipment_ids, $usps_user_id ) {
		$tracking_api_uri	= 'http://production.shippingapis.com/ShippingAPI.dll';
		$request 			= $this->wf_trackv2_request($tracking_api_uri, $shipment_ids, $usps_user_id);

		$response = wp_remote_post( $tracking_api_uri,
			array(
				'timeout'   => 70,
				'sslverify' => 0,
				'body'      => $request
			)
		);

		return $response;
	}
	
	function wf_trackv2_request( $tracking_api_uri, $shipment_ids, $usps_user_id ) {
		$xml_request 	= '<?xml version="1.0" encoding="UTF-8" ?>';
		$xml_request 	.= '<TrackRequest USERID="'.$usps_user_id.'">';
		
		foreach ( $shipment_ids as $shipment_id ) {
			$xml_request .= '<TrackID ID="'.$shipment_id.'"></TrackID>';
		}
		
		$xml_request 	.= '</TrackRequest>';
		$request 		= $tracking_api_uri.'&API=TrackV2&XML='.str_replace( array( "\n", "\r" ), '', $xml_request );
		
		return $request;
	}
	
	function wf_usps_metabox_content(){
		global $post;
		$shipmentId = '';

		$usps_label_details_array 	= get_post_meta( $post->ID, 'usps_label_details_array', true );
		
		if(empty($usps_label_details_array )) {
			$href_url 				= admin_url( '/?wf_usps_getlabel='.base64_encode( $post->ID ) );
			$order 					= $this->wf_load_order( $post->ID );
			$shipping_method 		= $order->get_shipping_method();
			$default_service_type 	= $this->wf_get_usps_shipping_service_type( $shipping_method, $post->ID );

		?>
			<strong>Select Preferred Service:</strong>
			<img class="help_tip" style="float:none;" data-tip="<?php _e( 'Select a valid service for your shipment. To know details about the available services for required location/product contact USPS.', 'wf-usps-woocommerce-shipping' ); ?>" src="<?php echo WC()->plugin_url();?>/assets/images/help.png" height="16" width="16" />
		<?php
			if($order->shipping_country != 'US'){
				$this->usps_services=$this->usps_int_services;
			}
			echo '<ul><li class="wide"><select class="select" id="usps_manual_service">';
			foreach($this->usps_services as $service_code => $service_name){
				echo '<option value="'.$service_code.'" ' . selected($default_service_type, $service_code) . ' >'.$service_name.'</option>';
			}
			echo '</select></li>';
		
			if($this->manual_weight_dimensions == 'yes'){
		?>
				<li><strong>Weight:&nbsp;</strong><input type="text" id="manual_weight" size="3" />&nbsp;<?=$this->weight_unit;?><br>     
				<strong>&nbsp;Height:&nbsp;</strong><input type="text" id="manual_height" size="3" />&nbsp;<?=$this->dim_unit;?><br>
				<strong>&nbsp;&nbsp;Width:&nbsp;</strong><input type="text" id="manual_width" size="3" />&nbsp;<?=$this->dim_unit;?><br>
				<strong>Length:&nbsp;</strong><input type="text" id="manual_length" size="3" />&nbsp;<?=$this->dim_unit;?>
				</li>                                                      
		<?php
			}
		?>
			<li class="wide"><a class="button button-primary tips usps_create_shipment" href="<?php echo $href_url; ?>" data-tip="<?php _e('Create Shipment Label ', 'wf-usps-woocommerce-shipping'); ?>"><?php _e('Create Shipment Label ', 'wf-usps-woocommerce-shipping'); ?></a></li></ul><hr style="border-color:#0074a2">
		
			<script type="text/javascript">
				jQuery("a.usps_create_shipment").on("click", function() {
				   location.href = this.href + '&weight=' + jQuery('#manual_weight').val() +
					'&length=' + jQuery('#manual_length').val()
					+ '&width=' + jQuery('#manual_width').val()
					+ '&height=' + jQuery('#manual_height').val()
					+ '&wf_usps_selected_service=' + jQuery('#usps_manual_service').val();
				   return false;
				});
			</script>
		<?php 
		}
		else {
			foreach ( $usps_label_details_array as $shipment_id => $usps_label_details ) {
				$download_url = admin_url( '/?wf_usps_printlabel='.base64_encode( $post->ID.'|'.$shipment_id ) );
			?>
			    <?php if(isset($usps_label_details['ShipmentId'])){?>
				<strong><?php _e( 'Tracking No: ', 'wf-usps-woocommerce-shipping' ); ?></strong><a href="https://tools.usps.com/go/TrackConfirmAction.action?tRef=fullpage&tLc=1&text28777=&tLabels=<?php echo $shipment_id ?>" target="_blank"><?php echo $usps_label_details['ShipmentId']; ?></a><br/>
				<?php }?>
				<?php 
				if(isset($usps_label_details['package_info'])&&is_array($usps_label_details['package_info'])&&!empty($usps_label_details['package_info'])){
					$package_info=$usps_label_details['package_info'];
					$pack_items=$package_info['items'];
				?>
					</br><strong><?php _e('Package Info');?></strong>
					<hr>
					<?php if( isset ( $package_info['pack_id'] ) ){?>
						<?php printf( __( 'Box Name: %s', 'wf-usps-woocommerce-shipping' ),$package_info['pack_id']);?></br>
					<?php }?>
					<?php printf( __( 'Dimension: %1$s X %2$s X %3$s %4$s', 'wf-usps-woocommerce-shipping' ),$package_info['dimension']['length'],$package_info['dimension']['width'],$package_info['dimension']['height'],$package_info['units']['dimension']);?>
					</br><?php printf( __( 'Weight: %1$s %2$s', 'wf-usps-woocommerce-shipping' ),$package_info['dimension']['weight'],$package_info['units']['weight']);?>
					<?php
					if(isset($pack_items)&&is_array($pack_items)&&sizeof($pack_items)){
					?>
						</br><strong><?php _e('Items');?></strong>
						<?php foreach($pack_items as $pack_item){
							$product_name= isset ( $pack_item['variation_text'] ) ?$pack_item['product_name'].'<img class="help_tip" style="float:none;" data-tip="'.$pack_item['variation_text'].'" src="'.WC()->plugin_url().'/assets/images/help.png" height="16" width="16" />':$pack_item['product_name'];
							printf( __( '</br>Product: %s Qty: %s', 'wf-usps-woocommerce-shipping' ),$product_name,$pack_item['qty']);							
						}?>
						<br>
					<?php					
					}
					?>
				<?php
				}
				?>
				<a class="button button-primary tips" href="<?php echo $download_url; ?>" data-tip="<?php _e( 'Print Label ', 'wf-usps-woocommerce-shipping' );?>"><?php _e( 'Print Label ', 'wf-usps-woocommerce-shipping' ); ?></a><hr style="border-color:#0074a2">
			<?php
			}
			
			if( $this->disbleShipmentTracking != 'True' && 'Manual' == $this->fillShipmentTracking) {
				$href_url = admin_url( '/?wf_usps_fill_tracking_ids='.base64_encode( $post->ID ) );
				?>
					<strong><?php _e( 'Auto Populate Shipment Tracking.', 'wf-usps-woocommerce-shipping' ); ?></strong></br>
					<a class="button button-primary tips" href="<?php echo $href_url; ?>" data-tip="<?php _e( 'Fill Tracking Numbers', 'wf-usps-woocommerce-shipping' ); ?>"><?php _e( 'Fill Tracking Numbers', 'wf-usps-woocommerce-shipping' ); ?></a><hr style="border-color:#0074a2">
				<?php
			}

			$href_url = admin_url( '/?wf_usps_reset_labels='.base64_encode( $post->ID ) );
			?>
				<strong><?php _e( 'Clean up all order Labels.', 'wf-usps-woocommerce-shipping' ); ?></strong></br>
				<a class="button tips" href="<?php echo $href_url; ?>" data-tip="<?php _e( 'Void Shipment', 'wf-usps-woocommerce-shipping' ); ?>"><?php _e( 'Reset The Labels', 'wf-usps-woocommerce-shipping' ); ?></a><hr style="border-color:#0074a2">
			<?php
		}
	}
	
	function wf_usps_fill_tracking_ids() {
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}
		
		$query_string 				= explode( '|', base64_decode( $_GET['wf_usps_fill_tracking_ids'] ) );
		$post_id					= $query_string[0];
		$shipment_id_cs	 			= '';
		$usps_label_details_array 	= get_post_meta( $post_id, 'usps_label_details_array', true );

		foreach ( $usps_label_details_array as $shipment_id => $usps_label_details ) {
			$shipment_id_cs .= $shipment_id.',';
		}
		
		$shipment_id_cs = rtrim( $shipment_id_cs, ',' );
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&wf_usps_track_shipment='.$shipment_id_cs ) );

		exit;
	}
	
	function wf_usps_printlabel() {
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}
		
		$query_string 				= explode( '|', base64_decode( $_GET['wf_usps_printlabel'] ) );
		$post_id					= $query_string[0];
		$shipment_id				= $query_string[1];
		$usps_label_details_array 	= get_post_meta( $post_id, 'usps_label_details_array', true );
		
		if( empty( $usps_label_details_array ) ) {
			$wfuspsmsg = 0;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
			exit; 
		}
		
		$usps_label_details = $usps_label_details_array[ $shipment_id ];
		if( empty( $usps_label_details ) ) {
			$wfuspsmsg = 0;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
			exit; 
		}
		
		if( !isset($usps_label_details["LabelImage"]) || !isset($usps_label_details["LabelType"]) ) {
			$wfuspsmsg = 0;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
			exit; 
		}
		
		if( 'TIF' == $usps_label_details["LabelType"] ) {
			header( 'Content-Type: image/tif' );
			header( 'Content-disposition: attachment; filename="USPS-ShippingLabel-'.$post_id.'-'.$shipment_id.'.tif"' );
		}
		else {
			header( 'Content-Type: application/pdf' );
			header( 'Content-disposition: attachment; filename="USPS-ShippingLabel-'.$post_id.'-'.$shipment_id.'.pdf"' );
		}
		
		$binary_label = base64_decode( $usps_label_details["LabelImage"] );
		print($binary_label);
		exit;
	}

	function wf_usps_reset_labels() {
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}

		$query_string 				= explode( '|', base64_decode( $_GET['wf_usps_reset_labels'] ) );
		$post_id					= $query_string[0];
		$usps_label_details_array	= '';
		$client_side_reset 			= false;

		if( isset( $_GET['client_reset'] ) ) {
			$client_side_reset = true;
		}

		if(!$client_side_reset) {
			$current_page_uri	= $_SERVER['REQUEST_URI'];
			$href_url 			= $current_page_uri.'&client_reset';
			
			$message = 'All your labels generated for this order will be lost. If you still want to proceed with reset the labels then click <a class="button button-primary tips" href="'.$href_url.'" data-tip="Client Side Reset">Client Side Reset</a>';

			$wfuspsmsg = '13';
			update_post_meta( $post_id, 'wfuspsmsg', $message );
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
			exit;
		}

		update_post_meta( $post_id, 'usps_label_details_array', $usps_label_details_array );
		update_post_meta( $post_id, 'wf_usps_selected_service', '' );

		$wfuspsmsg = 9;
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
		exit;
	}

	function wf_usps_getlabel(){
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}

		$usps_api_uri        	= 'https://secure.shippingapis.com/ShippingAPI.dll';
		$wfuspsmsg 				= '';
		$query_string 			= explode( '|', base64_decode( $_GET['wf_usps_getlabel'] ) );

		$post_id			= $query_string[0];
		$order 				= $this->wf_load_order( $post_id );
		
		$wf_usps_selected_service	= isset( $_GET['wf_usps_selected_service'] ) ? $_GET['wf_usps_selected_service'] : '';
		update_post_meta( $post_id, 'wf_usps_selected_service', (string)$wf_usps_selected_service );

		// Load USPS Settings.
		$usps_settings 					= get_option( 'woocommerce_'.WF_USPS_ID.'_settings', null ); 
		$usps_printLabelType      		= isset( $usps_settings['printLabelType'] ) ? $usps_settings['printLabelType'] : 'PDF';
		$usps_fillShipmentTracking		= isset( $usps_settings['fillShipmentTracking'] ) ? $usps_settings['fillShipmentTracking'] : 'Manual';
		
		if( $order->shipping_country == 'US' ) {
			$requests = $this->wf_deliveryconfirmationv4_request( $order );
		}
		else {
			$requests = $this->wf_expressmailintl_request( $order );
			if(count($requests) > 1) {
				$wfuspsmsg = '11';
				wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
				exit;
			}
		}
		
		if( !$requests ) {
			$wfuspsmsg = 0;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
			exit;
		}		

		update_post_meta( $post_id, 'USPS Label Requests', $requests );		
		$usps_label_details			= array();
		$usps_label_details_array	= array();

		foreach ( $requests as $request_arr ) {
			$request=$request_arr['request_data'];
			$this->debug( 'USPS REQUEST: <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;"><xmp>' . print_r($request, true ) . '</xmp></pre>' );
			
			$response = wp_remote_post( $usps_api_uri,
				array(
					'timeout'   => 70,
					'sslverify' => 0,
					'body'      => $request
				)
			);
			
			$this->debug( 'USPS RESPONSE: <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' .  htmlspecialchars(print_r( $response, true ),ENT_IGNORE) . '</pre>' );
			
			if ( is_wp_error( $response ) ) {
				$wfuspsmsg = 2;
				update_post_meta( $post_id, 'wfuspsmsg', 'REQUEST FAILED - '.$response->get_error_message() );
				wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
				exit;
			}
			$api_name 		= $this->get_api_name( $request );
			$xml_response 	= simplexml_load_string( $response['body'] );
			
			$shipment_id							= '';
			$usps_label_details["ApiName"] 			= $api_name;
			$usps_label_details["LabelImage"]		= '';
			$usps_label_details["LabelType"]		= $usps_printLabelType;
			$usps_label_details["package_info"]		= $request_arr['package_info'];
			switch ( $api_name ){
				case "ExpressMailIntlCertify":
				case "ExpressMailIntl":
				case "PriorityMailIntlCertify":
				case "PriorityMailIntl":
				case "FirstClassMailIntlCertify":
				case "FirstClassMailIntl":
					if( "" == $xml_response->LabelImage ) {
						$api_message 	= (string) $xml_response->Description.' ['.(string) $xml_response->Number.', '.(string) $xml_response->Source.']';
						
						$wfuspsmsg = 2;
						update_post_meta( $post_id, 'wfuspsmsg', $api_message  );
						wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
						exit;
					}
					else {
						$usps_label_details["LabelImage"] 		= (string) $xml_response->LabelImage;
						$shipment_id							= (string) $xml_response->BarcodeNumber;
					}
					break;
				case "DelivConfirmCertifyV4":
				case "DeliveryConfirmationV4":
					if( "" == $xml_response->DeliveryConfirmationLabel ) {
						$api_message 	= (string) $xml_response->Description.' ['.(string) $xml_response->Number.', '.(string) $xml_response->Source.']';
						
						$wfuspsmsg = 2;
						update_post_meta( $post_id, 'wfuspsmsg', $api_message  );
						wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
						exit;
					}
					else {
						$usps_label_details["LabelImage"] 		= (string) $xml_response->DeliveryConfirmationLabel;
						$shipment_id							= (string) $xml_response->DeliveryConfirmationNumber;						
						// Seems like first 8 characters are additional details (xxx + location) other than actual tracking id.
						$shipment_id							= substr( $shipment_id, 8 );
						$usps_label_details["ShipmentId"]		= $shipment_id;
					}
					break;
				default:
					$wfuspsmsg = 0;
					
					wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
					exit;
			}
			
			if ( "" == $shipment_id ) {
				$wfuspsmsg = 0;
				wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
				exit;
			}
			
			$usps_label_details_array[$shipment_id]		= $usps_label_details;
		}

		if( empty( $usps_label_details_array ) ) {
			$wfuspsmsg = 8;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
			exit;
		}

		update_post_meta( $post_id, 'usps_label_details_array', $usps_label_details_array );
		$shipment_id_cs = '';
		
		$wfuspsmsg = 7;
		if( 'Auto' == $usps_fillShipmentTracking ) {
			if( "DelivConfirmCertifyV4" == $api_name || "DeliveryConfirmationV4" == $api_name ) {
				foreach ( $usps_label_details_array as $shipment_id => $usps_label_details ) {
					$shipment_id_cs .= $shipment_id.',';
				}
				$shipment_id_cs = rtrim( $shipment_id_cs, ',' );				
			}
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&wf_usps_track_shipment='.$shipment_id_cs ) );
		}
		else {
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfuspsmsg='.$wfuspsmsg ) );
		}
		exit;
	}
	
	function get_api_name( $request ){
		$parts = parse_url( $request );

		if( isset( $parts['path'] ) ){
			parse_str( $parts['path'], $query );
		}

		if( isset($query["API"] ) ){
			return $query["API"];
		}
		else{
			return '';
		}
	}
	
	function wf_expressmailintl_request( $order ) {
		$usps_api_uri        	= 'https://secure.shippingapis.com/ShippingAPI.dll';
		
		$shipping_first_name 	= $order->shipping_first_name;
		$shipping_last_name 	= $order->shipping_last_name;
		$shipping_company 		= $order->shipping_company;
		$shipping_address_1 	= $order->shipping_address_2;
		$shipping_address_2 	= $order->shipping_address_1;
		$shipping_city 			= $order->shipping_city;
		$shipping_postcode 		= $order->shipping_postcode;
		$shipping_country 		= $order->shipping_country;
		$shipping_state 		= $order->shipping_state;
		$billing_email 			= $order->billing_email;
		$billing_phone 			= $order->billing_phone;
        
		//$shipping_country_name	= isset( WC()->countries->countries[ $order->shipping_country ] ) ? WC()->countries->countries[ $order->shipping_country ] : $order->shipping_country;
        if ( !class_exists( 'WF_Shipping_USPS' ) ) {
	  		include_once 'class-wf-shipping-usps.php';
	  	}
		$wfsusps = new WF_Shipping_USPS();
        $shipping_country_name	= $wfsusps->get_country_name( $order->shipping_country );

		// Load USPS Settings.
		$usps_settings 					= get_option( 'woocommerce_'.WF_USPS_ID.'_settings', null ); 
		$usps_user_id					= !empty( $usps_settings['user_id'] ) ? $usps_settings['user_id'] : '570CYDTE1766'; 
		$usps_origin 					= isset( $usps_settings['origin'] ) ? $usps_settings['origin'] : '';
		$usps_disbleShipmentTracking	= isset( $usps_settings['disbleShipmentTracking'] ) ? $usps_settings['disbleShipmentTracking'] : 'TrueForCustomer';
		$usps_disblePrintLabel			= isset( $usps_settings['disblePrintLabel'] ) ? $usps_settings['disblePrintLabel'] : '';
		$usps_defaultPrintService  		= isset( $usps_settings['defaultPrintService'] ) ? $usps_settings['defaultPrintService'] : 'None';
		$usps_printLabelSize      		= isset( $usps_settings['printLabelSize'] ) ? $usps_settings['printLabelSize'] : 'Default';
		$usps_printLabelType      		= isset( $usps_settings['printLabelType'] ) ? $usps_settings['printLabelType'] : 'PDF';
		$usps_senderName 				= isset( $usps_settings['senderName'] ) ? $usps_settings['senderName'] : '';
		$usps_senderCompanyName 		= isset( $usps_settings['senderCompanyName'] ) ? $usps_settings['senderCompanyName'] : '';
		$usps_senderAddressLine1 		= isset( $usps_settings['senderAddressLine1'] ) ? $usps_settings['senderAddressLine1'] : '';
		$usps_senderAddressLine2 		= isset( $usps_settings['senderAddressLine2'] ) ? $usps_settings['senderAddressLine2'] : '';
		$usps_senderCity 				= isset( $usps_settings['senderCity'] ) ? $usps_settings['senderCity'] : '';
		$usps_senderState 				= isset( $usps_settings['senderState'] ) ? $usps_settings['senderState'] : '';
		$usps_senderEmail          		= isset( $usps_settings['senderEmail'] ) ? $usps_settings['senderEmail'] : '';
		$usps_senderPhone          		= isset( $usps_settings['senderPhone'] ) ? $usps_settings['senderPhone'] : '';
		$usps_packing_method			= isset( $usps_settings['packing_method'] ) ? $usps_settings['packing_method'] : 'per_item';
		$usps_manual_weight_dimensions	= isset( $usps_settings['manual_weight_dimensions'] ) ? $usps_settings['manual_weight_dimensions'] : 'no';
		
		$sendername_parts 		= explode( " ", $usps_senderName );
		$sender_lastname 		= array_pop( $sendername_parts );
		$sender_firstname 		= implode( " ", $sendername_parts );
		$shipmentErrorMessage 	= '';

		// This api call is for international (outside US) shipping.
		if( $order->shipping_country == 'US' ) {
			return false;
		}

		$shipping_method 	= $order->get_shipping_method( );
		$service_type = get_post_meta($order->id, 'wf_usps_selected_service', true);
		$request_type=$service_type;		

		if( $usps_manual_weight_dimensions == 'yes' ) {
			$package_data_array = $this->wf_get_package_data_manual( $order );
		}
		else {
			$package_data_array = $this->wf_get_package_data( $order );
		}

		if( empty( $package_data_array ) ) {
			return false;
		}
		
		$requests = array();
		foreach ( $package_data_array as $package_data ) {
			for ( $i = 0; $i < $package_data['BoxCount']; $i ++ ) {
				$xml_request = '<'.$request_type.'Request USERID="'.$usps_user_id.'">';
				$xml_request .= '<Option /><Revision>2</Revision>';
				
				if('Compact' == $usps_printLabelSize) {
					$xml_request .= '<ImageParameters>';
					$xml_request .= '<ImageParameter>'.'4BY6LABEL'.'</ImageParameter>';
					$xml_request .= '</ImageParameters>';
				}
				else {
					$xml_request .= '<ImageParameters />';
				}
				$xml_request .= '<FromFirstName>'.$sender_firstname.'</FromFirstName>';
				$xml_request .= '<FromMiddleInitial>'.''.'</FromMiddleInitial>';
				$xml_request .= '<FromLastName>'.$sender_lastname.'</FromLastName>';
				$xml_request .= '<FromFirm />';
				$xml_request .= '<FromAddress1>'.$usps_senderAddressLine1.'</FromAddress1>';
				$xml_request .= '<FromAddress2>'.$usps_senderAddressLine2.'</FromAddress2>';
				$xml_request .= '<FromCity>'.$usps_senderCity.'</FromCity>';
				$xml_request .= '<FromState>'.$usps_senderState.'</FromState>';
				$xml_request .= '<FromZip5>'.$usps_origin.'</FromZip5>';
				$xml_request .= '<FromZip4/>';
				$xml_request .= '<FromPhone>'.$usps_senderPhone.'</FromPhone>';
				$xml_request .= '<ToFirstName>'.$shipping_first_name.'</ToFirstName>';
				$xml_request .= '<ToLastName>'.$shipping_last_name.'</ToLastName>';
				$xml_request .= '<ToFirm></ToFirm>';
				$xml_request .= '<ToAddress1>'.$shipping_address_1.'</ToAddress1>';
				$xml_request .= '<ToAddress2>'.$shipping_address_2.'</ToAddress2>';
				$xml_request .= '<ToAddress3></ToAddress3>';
				$xml_request .= '<ToCity>'.$shipping_city.'</ToCity>';
				$xml_request .= '<ToProvince></ToProvince>';
				$xml_request .= '<ToCountry>'.$shipping_country_name.'</ToCountry>';
				$xml_request .= '<ToPostalCode>'.$shipping_postcode.'</ToPostalCode>';
				$xml_request .= '<ToPOBoxFlag>N</ToPOBoxFlag>';
				$xml_request .= '<ToPhone>'.$billing_phone.'</ToPhone>';
				$xml_request .= '<ToFax></ToFax>';
				$xml_request .= '<ToEmail>'.$billing_email.'</ToEmail>';
				if($service_type=='FirstClassMailIntl'){
					$xml_request .= '<FirstClassMailType>PARCEL</FirstClassMailType>';
				}else{
					$xml_request .= '<NonDeliveryOption>Return</NonDeliveryOption>';
					$xml_request .= '<Container>'.$package_data['Container'].'</Container>';
				}
				
				
				$xml_request .= '<ShippingContents>';
				
				if( isset( $package_data['Items'] ) ) {
					$item_quantity_map = $package_data['Items'];
					foreach ( $item_quantity_map as $item_id => $quantity ) {
						$product_data 		= wc_get_product( $item_id );
						$item_description 	= $product_data->post->post_excerpt ? $product_data->post->post_excerpt : $product_data->post->post_title;
						$item_price 		= $product_data->get_price();
						$item_weight_lbs 	= wc_get_weight( $product_data->get_weight(), 'lbs' );
						
						if( $usps_manual_weight_dimensions == 'yes' ) {
							$item_net_ounces = floor( $package_data[ 'WeightInOunces' ] / count( $item_quantity_map ) );
						}
						else {
							$item_net_ounces = ( $item_weight_lbs * $quantity * 16 );
						}

						$xml_request .= '<ItemDetail>';
						$xml_request .= '<Description>'.$item_description.'</Description>';
						$xml_request .= '<Quantity>'.$quantity.'</Quantity>';
						$xml_request .= '<Value>'.( $item_price * $quantity ).'</Value>';
						$xml_request .= '<NetPounds></NetPounds>';
						$xml_request .= '<NetOunces>'.$item_net_ounces.'</NetOunces>';
						$xml_request .= '<HSTariffNumber></HSTariffNumber>';
						$xml_request .= '<CountryOfOrigin></CountryOfOrigin>';
						$xml_request .= '</ItemDetail>';
					}
				}
				else {
					// TODO: All items will be part of show in case of API.
					$orderItems = $order->get_items();
					foreach( $orderItems as $orderItem ) {
						$item_id 			= $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];
						$quantity 			= $orderItem['qty'];
						$product_data   	= wc_get_product( $item_id );
						$item_description 	= $product_data->get_title();
						$weight 			= $product_data->get_weight();
						$item_price 		= $product_data->get_price();
						$item_weight_lbs	= wc_get_weight( $product_data->get_weight(), 'lbs' );
						
						if( $usps_manual_weight_dimensions == 'yes' ) {
							$item_net_ounces = floor( $package_data[ 'WeightInOunces' ] / count( $orderItems ) );
						}
						else {
							$item_net_ounces = ( $item_weight_lbs * $quantity * 16 );
						}
						
						$xml_request .= '<ItemDetail>';
						$xml_request .= '<Description>'.$item_description.'</Description>';
						$xml_request .= '<Quantity>'.$quantity.'</Quantity>';
						$xml_request .= '<Value>'.( $item_price * $quantity ).'</Value>';
						$xml_request .= '<NetPounds></NetPounds>';
						$xml_request .= '<NetOunces>'.$item_net_ounces.'</NetOunces>';
						$xml_request .= '<HSTariffNumber></HSTariffNumber>';
						$xml_request .= '<CountryOfOrigin></CountryOfOrigin>';
						$xml_request .= '</ItemDetail>';
					}
				}

				$xml_request .= '</ShippingContents>';
				$xml_request .= '<GrossPounds></GrossPounds>';
				$xml_request .= '<GrossOunces>'.$package_data[ 'WeightInOunces' ].'</GrossOunces>';
				$xml_request .= '<ContentType>Documents</ContentType>';
				$xml_request .= '<Agreement>Y</Agreement>';
				$xml_request .= '<Comments></Comments>';
				$xml_request .= '<ImageType>'.$usps_printLabelType.'</ImageType>';
				$xml_request .= '<ImageLayout>ALLINONEFILE</ImageLayout>';
				/*$xml_request .= '<POZipCode>'.$usps_origin.'</POZipCode>';*/
				$xml_request .= '<LabelDate />';
				$xml_request .= '<HoldForManifest>N</HoldForManifest>';
				if($service_type=='FirstClassMailIntl'){
					$xml_request .= '<Container>RECTANGULAR</Container>';
				}
				$xml_request .= '<Size>'.$package_data[ 'Size' ].'</Size>';
				$xml_request .= '<Length>'.$package_data[ 'Length' ].'</Length>';
				$xml_request .= '<Width>'.$package_data[ 'Width' ].'</Width>';
				$xml_request .= '<Height>'.$package_data[ 'Height' ].'</Height>';
				$xml_request .= '<Girth>'.$package_data[ 'Girth' ].'</Girth>';
				$xml_request .= '</'.$request_type.'Request>';
				$requests[] = array('request_data'=>$usps_api_uri.'&API='.$request_type.'&XML='.str_replace( array( "\n", "\r" ), '', $xml_request ));
			}
		}
		return $requests;
	}
	
	function wf_deliveryconfirmationv4_request($order) {
		$usps_api_uri        	= 'https://secure.shippingapis.com/ShippingAPI.dll';
		
		$shipping_first_name 	= $order->shipping_first_name;
		$shipping_last_name 	= $order->shipping_last_name;
		$shipping_company 		= $order->shipping_company;
		$shipping_address_1 	= $order->shipping_address_2;
		$shipping_address_2 	= $order->shipping_address_1;
		$shipping_city 			= $order->shipping_city;
		$shipping_postcode 		= $this->getZip4and5($order->shipping_postcode);
		$shipping_country 		= $order->shipping_country;
		$shipping_state 		= $order->shipping_state;
		$billing_email 			= $order->billing_email;
		$billing_phone 			=  $order->billing_phone;
		$shipmentErrorMessage 	= '';
		
		// Load USPS Settings.
		$usps_settings 					= get_option( 'woocommerce_'.WF_USPS_ID.'_settings', null ); 
		$usps_user_id					= !empty( $usps_settings['user_id'] ) ? $usps_settings['user_id'] : '570CYDTE1766'; 
		$usps_origin 					= isset( $usps_settings['origin'] ) ? $usps_settings['origin'] : '';
		$usps_disbleShipmentTracking	= isset( $usps_settings['disbleShipmentTracking'] ) ? $usps_settings['disbleShipmentTracking'] : 'TrueForCustomer';
		$usps_disblePrintLabel			= isset( $usps_settings['disblePrintLabel'] ) ? $usps_settings['disblePrintLabel'] : '';
		$usps_defaultPrintService  		= isset( $usps_settings['defaultPrintService'] ) ? $usps_settings['defaultPrintService'] : 'None';
		$usps_printLabelSize      		= isset( $usps_settings['printLabelSize'] ) ? $usps_settings['printLabelSize'] : 'Default';
		$usps_printLabelType      		= isset( $usps_settings['printLabelType'] ) ? $usps_settings['printLabelType'] : 'PDF';
		$usps_senderName 				= isset( $usps_settings['senderName'] ) ? $usps_settings['senderName'] : '';
		$usps_senderCompanyName 		= isset( $usps_settings['senderCompanyName'] ) ? $usps_settings['senderCompanyName'] : '';
		$usps_senderAddressLine1 		= isset( $usps_settings['senderAddressLine1'] ) ? $usps_settings['senderAddressLine1'] : '';
		$usps_senderAddressLine2 		= isset( $usps_settings['senderAddressLine2'] ) ? $usps_settings['senderAddressLine2'] : '';
		$usps_senderCity 				= isset( $usps_settings['senderCity'] ) ? $usps_settings['senderCity'] : '';
		$usps_senderState 				= isset( $usps_settings['senderState'] ) ? $usps_settings['senderState'] : '';
		$usps_senderEmail          		= isset( $usps_settings['senderEmail'] ) ? $usps_settings['senderEmail'] : '';
		$usps_senderPhone          		= isset( $usps_settings['senderPhone'] ) ? $usps_settings['senderPhone'] : '';
		$usps_packing_method			= isset( $usps_settings['packing_method'] ) ? $usps_settings['packing_method'] : 'per_item';
		$usps_manual_weight_dimensions	= isset( $usps_settings['manual_weight_dimensions'] ) ? $usps_settings['manual_weight_dimensions'] : 'no';

		// This api call is meant to be for US domestic shipping.
		if( $order->shipping_country != 'US' ) {
			return false;
		}

		$shipping_method = $order->get_shipping_method( );
		//$service_type_array = explode(' ',$shipping_method);
		$service_type = $this->wf_get_usps_shipping_service_type( $shipping_method, $order->id );
        $service_type = strtoupper ( $service_type );

		if( !$service_type ) {
			return false;
		}

		if( $usps_manual_weight_dimensions == 'yes' ) {
			$package_data_array = $this->wf_get_package_data_manual( $order );
		}
		else {
			$package_data_array = $this->wf_get_package_data( $order );
		}
		
		if( empty( $package_data_array ) ) {
			return false;
		}
		
		$requests = array();
		foreach ( $package_data_array as $package_data ) {
			for ( $i = 0; $i < $package_data[ 'BoxCount' ]; $i ++ ) {
				$xml_request = '<?xml version="1.0" encoding="UTF-8" ?>';
				$xml_request .= '<DeliveryConfirmationV4.0Request USERID="'.$usps_user_id.'" PASSWORD="">';
				$xml_request .= '<Revision>2</Revision>';
				
				if('Compact' == $usps_printLabelSize) {
					$xml_request .= '<ImageParameters>';
					$xml_request .= '<ImageParameter>'.'Barcode Only'.'</ImageParameter>';
					$xml_request .= '</ImageParameters>';
				}
				else {
					$xml_request .= '<ImageParameters />';
				}
				$xml_request .= '<FromName>'.$usps_senderName.'</FromName>';
				$xml_request .= '<FromFirm>'.$usps_senderCompanyName.'</FromFirm>';
				$sender_addressline2 = trim($usps_senderAddressLine2);
				$xml_request .= '<FromAddress1>'.$usps_senderAddressLine1.'</FromAddress1>';
				$xml_request .= '<FromAddress2>'.$usps_senderAddressLine2.'</FromAddress2>';
				$xml_request .= '<FromCity>'.$usps_senderCity.'</FromCity>';
				$xml_request .= '<FromState>'.$usps_senderState.'</FromState>';
				$xml_request .= '<FromZip5>'.$usps_origin.'</FromZip5>';
				$xml_request .= '<FromZip4/>';

				$xml_request .= '<ToName>'.$shipping_first_name.' '.$shipping_last_name.'</ToName>';
				$xml_request .= '<ToFirm>'.$shipping_company.'</ToFirm>';
				$xml_request .= '<ToAddress1>'.$shipping_address_1.'</ToAddress1>';
				$xml_request .= '<ToAddress2>'.$shipping_address_2.'</ToAddress2>';
				$xml_request .= '<ToCity>'.$shipping_city.'</ToCity>';
				$xml_request .= '<ToState>'.$shipping_state.'</ToState>';
				$xml_request .= '<ToZip5>'.$shipping_postcode[0].'</ToZip5>';
				$xml_request .= (!empty($shipping_postcode[1]))? '<ToZip4>'.$shipping_postcode[1].'</ToZip4>' : '<ToZip4 />';
				$xml_request .= '<ToPOBoxFlag></ToPOBoxFlag>';
				
				$xml_request .= '<WeightInOunces>'.$package_data['WeightInOunces'].'</WeightInOunces>';
				$xml_request .= '<ServiceType>'.$service_type.'</ServiceType>';
				$xml_request .= '<SeparateReceiptPage>False</SeparateReceiptPage>';
				$xml_request .= '<POZipCode>'.$usps_origin.'</POZipCode>';
				$xml_request .= '<ImageType>'.$usps_printLabelType.'</ImageType>';
				$xml_request .= '<AddressServiceRequested>False</AddressServiceRequested>';
				// Email Notifications.
				if (is_email($usps_senderEmail)) {
					$xml_request .= '<SenderEMail>'.$usps_senderEmail.'</SenderEMail>';
					if (is_email($billing_email)) {
						$xml_request .= '<RecipientEMail>'.$billing_email.'</RecipientEMail>';
					}
				}
				$xml_request .= '<HoldForManifest>N</HoldForManifest>';
				$xml_request .= '<Container>'.$package_data['Container'].'</Container>';
				$xml_request .= '<Size>'.$package_data['Size'].'</Size>';
				$xml_request .= '<Width>'.$package_data['Width'].'</Width>';
				$xml_request .= '<Length>'.$package_data['Length'].'</Length>';
				$xml_request .= '<Height>'.$package_data['Height'].'</Height>';
				$xml_request .= '<Girth>'.$package_data['Girth'].'</Girth>';
				$xml_request .= '<ReturnCommitments>true</ReturnCommitments>';
				$xml_request .= '</DeliveryConfirmationV4.0Request>';
				$requests[] = array('request_data'=>$usps_api_uri.'&API=DeliveryConfirmationV4&XML='.str_replace( array( "\n", "\r" ), '', $xml_request ),'package_info'=>isset($package_data['PackageInfo'])?$package_data['PackageInfo']:array());
			}
		}
		return $requests;
	}
	
	function wf_get_package_data( $order ) {
		$package 				= $this->wf_create_package( $order );
		$shipping_service_data	= $this->wf_get_shipping_service_data( $order );
		
		$usps_settings 					= get_option( 'woocommerce_'.WF_USPS_ID.'_settings', null ); 
		$usps_packing_method			= isset( $usps_settings['packing_method'] ) ? $usps_settings['packing_method'] : 'per_item';

		if( !$shipping_service_data ) {
			return false;
		}
		if ( !class_exists( 'WF_Shipping_USPS' ) ) {
	  		include_once 'class-wf-shipping-usps.php';
	  	}
		$wfsusps = new WF_Shipping_USPS();
		
		if( SHIPPING_METHOD_USPS_TYPE_FLAT_RATE == substr( $shipping_service_data['shipping_service'], 0, 9 ) ) { // Flat rate case.
			$flat_rate_boxes    = include( 'data-wf-flat-rate-boxes.php' );
			$package_data_array = $wfsusps->wf_get_flat_rate_box_data( $package, $flat_rate_boxes, $shipping_service_data['box_type'] );
		}
		else { // Api rate case.
			$package_data_array = $wfsusps->wf_get_api_rate_box_data( $package, $usps_packing_method );
		}
		return $package_data_array;
	}
	
	function wf_get_package_data_manual( $order ) {
		$package 				= $this->wf_create_package( $order );
		$shipping_service_data	= $this->wf_get_shipping_service_data( $order );
		
		if( !$shipping_service_data ) {
			return false;
		}
		
		$usps_settings 				= get_option( 'woocommerce_'.WF_USPS_ID.'_settings', null ); 
		$usps_packing_method		= isset( $usps_settings['packing_method'] ) ? $usps_settings['packing_method'] : 'per_item';
		
		$weight_unit = 'LBS';
		$dim_unit    = 'IN';
		
		$weight		= isset( $_GET['weight'] )	? $_GET['weight'] 	: false;
		$height		= isset( $_GET['height'] )	? $_GET['height'] 	: false;
		$width		= isset( $_GET['width'] )	? $_GET['width'] 	: false;
		$length		= isset( $_GET['length'] )	? $_GET['length']	: false;
		$size   	= 'REGULAR';
		
		if ( $height && $width && $length ) {
			$dimensions = array( $height, $width, $length );
			sort( $dimensions );
			
			if ( max( $dimensions ) > 12 ) {
				$size   = 'LARGE';
			}
			$girth 		= $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];
		}
		else {
			$dimensions = array( 0, 0, 0 );
			$girth      = 0;
		}
		
		$package_data_array	= array();
		$package_data = array();
		
		$package_data['BoxCount' ]			= 1;
		$package_data['WeightInOunces']		= $weight * 16;
		/* $package_data['POZipCode']			= $package['destination']['postcode']; */
		$package_data['Container']			= '';
		$package_data['Size']				= $size;
		$package_data['Length']				= $dimensions[2];
		$package_data['Width']				= $dimensions[1];
		$package_data['Height']				= $dimensions[0];
		$package_data['Girth']				= $girth;

		$package_data_array[] = $package_data;
		return $package_data_array;
	}
	
	function wf_get_usps_shipping_service_type( $shipping_method, $order_id = '' ){
		$usps_settings 				= get_option( 'woocommerce_'.WF_USPS_ID.'_settings', null ); 
		$usps_defaultPrintService  	= isset( $usps_settings['defaultPrintService'] ) ? $usps_settings['defaultPrintService'] : 'None';
		$wf_usps_selected_service	= '';

		if( $order_id != '' ) {
			$wf_usps_selected_service = get_post_meta( $order_id, 'wf_usps_selected_service', true);
		}
		
		if( '' != $wf_usps_selected_service ) {
			$shipping_method = $wf_usps_selected_service;
		}
        
		if ( $usps_defaultPrintService != "None" ) {
			$service_type = $usps_defaultPrintService;
		}
		else if( strpos( $shipping_method, "Priority" ) === 0 ) {
			$service_type = 'Priority';
		}
		else if( strpos( $shipping_method, "First-Class") === 0 || strpos($shipping_method, "First Class") === 0 ) {
			$service_type = 'First Class';
		}
		else if( strpos( $shipping_method, "Standard Post" ) === 0 ) {
			$service_type = 'Standard Post';
		}
		else if( strpos( $shipping_method, "Media Mail" ) === 0 ) {
			$service_type = 'Media Mail';
		}
		else if( strpos( $shipping_method, "Library Mail" ) === 0 ) {
			$service_type = 'Library Mail';
		}		
		else {
			// TODO: Improve by presenting combo box.
			$service_type = 'Priority';
		}
		
		return $service_type;
	}
	
	function wf_get_shipping_service_data( $order ){
		//TODO: Take the first shipping method. The use case of multiple shipping method for single order is not handled.
		$shipping_methods = $order->get_shipping_methods();
	
		if ( ! $shipping_methods ) {
			return false;
		}

		$shipping_method 			= array_shift( $shipping_methods );
		$shipping_service_tmp_data 	= explode( ':',$shipping_method['method_id'] );
		
		if( ( count( $shipping_service_tmp_data ) < 2) ){
			$shipping_service_data['shipping_method'] = WF_USPS_ID;
			$shipping_service_data['shipping_service'] = '';
		}
		else {
			$shipping_service_data['shipping_method'] 	= $shipping_service_tmp_data[0];
			$shipping_service_data['shipping_service'] 	= $shipping_service_tmp_data[1];
			
			if ( strpos( $shipping_method['method_id'],'express' ) !== false ) {
				$shipping_service_data['box_type'] = 'express';
			}
			else {
				$shipping_service_data['box_type'] = 'priority';
			}	
		}
		
		return $shipping_service_data;
	}
	
	function wf_create_package( $order ){
		$orderItems = $order->get_items();
		foreach( $orderItems as $orderItem )
		{
			$item_id 			= $orderItem[ 'variation_id' ] ? $orderItem[ 'variation_id' ] : $orderItem[ 'product_id' ];
			$product_data 		= wc_get_product( $item_id );
			$items[ $item_id ]	= array( 'data' => $product_data , 'quantity' => $orderItem[ 'qty' ] );
		}
		$package[ 'contents' ] 		= $items;
		$package[ 'destination' ] 	= array (
        'country' 		=> $order->shipping_country,
        'state' 		=> $order->shipping_state,
        'postcode' 		=> $order->shipping_postcode,
        'city' 			=> $order->shipping_city,
        'address' 		=> $order->shipping_address_1,
        'address_2' 	=> $order->shipping_address_2);
		
		return $package;
	}
	
	function wf_load_order( $orderId ){
		if ( !class_exists( 'WC_Order' ) ) {
			return false;
		}
		return new WC_Order( $orderId );      
	}
	
	function wf_user_check() {
		if ( is_admin() ) {
			return true;
		}
		return false;
	}
    
    function getZip4and5($ToZip){
           $Tozip_parts = array_map('trim', explode('-', $ToZip));
           return $Tozip_parts;
    }
}

new WF_Shipping_USPS_Admin();
