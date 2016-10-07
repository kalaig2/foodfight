<?php
/* Giftcard Payment Gateway Class */
class GiftCard extends WC_Payment_Gateway {

	// Setup our Gateway's id, description and other values
	function __construct() {

		// The global ID for this Payment method
		$this->id = "giftcard";

		// The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
		$this->method_title = __( "Giftcard Payment", 'giftcard' );

		// The description for this Payment Gateway, shown on the actual Payment options page on the backend
		$this->method_description = __( "Giftcard Payment Gateway Plug-in for WooCommerce", 'giftcard' );

		// The title to be used for the vertical tabs that can be ordered top to bottom
		$this->title = __( "Giftcard", 'giftcard' );

		// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		$this->icon = null;

		// Bool. Can be set to true if you want payment fields to show on the checkout 
		// if doing a direct integration, which we are doing in this case
		$this->has_fields = true;

		// Supports the default credit card form
		$this->supports = array( "products" );
		//$this->supports = $this->gift_card_form();
		

		// This basically defines your settings which are then loaded with init_settings()
		$this->init_form_fields();

		// After init_settings() is called, you can get the settings and load them into variables, e.g:
		// $this->title = $this->get_option( 'title' );
		$this->init_settings();
		
		// Turn these settings into variables we can use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}
		
		// Lets check for SSL
		add_action( 'admin_notices', array( $this,	'do_ssl_check' ) );
		
		// Save settings
		if ( is_admin() ) {
			// Versions over 2.0
			// Save our administration options. Since we are not going to be doing anything special
			// we have not defined 'process_admin_options' in this class so the method in the parent
			// class will be used instead
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}		
	} // End __construct()

	// Build the administration fields for this specific Gateway
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Enable / Disable', 'giftcard' ),
				'label'		=> __( 'Enable this payment gateway', 'giftcard' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'title' => array(
				'title'		=> __( 'Title', 'giftcard' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Payment title the customer will see during the 				checkout process.', 'giftcard' ),
				'default'	=> __( 'Gift card', 'giftcard' ),
			),
			'description' => array(
				'title'		=> __( 'Description', 'giftcard' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Payment description the customer will see during 			the checkout process.', 'giftcard' ),
				'default'	=> __( 'Pay securely using your credit card.'
								,'giftcard' ),
				'css'		=> 'max-width:350px;'
			),
			'api_url' => array(
				'title'		=> __( 'Givex API Url', 'giftcard' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'This is the Givex API Url provided by Givex.', 'giftcard' ),
			),
			'port_num' => array(
				'title'		=> __( 'Givex Port Number', 'giftcard' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'This is the Port Number provided by Givex.', 'giftcard' ),
			),
			'user_id' => array(
				'title'		=> __( 'Givex User Id', 'giftcard' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'This is the User id provided by Givex.', 'giftcard' ),
			),
			'password' => array(
				'title'		=> __( 'Givex Password', 'giftcard' ),
				'type'		=> 'password',
				'desc_tip'	=> __( 'This is the password provided by Givex.', 'giftcard' ),
			),
			'environment' => array(
				'title'		=> __( 'Giftcard Test Mode', 'giftcard' ),
				'label'		=> __( 'Enable Test Mode', 'giftcard' ),
				'type'		=> 'checkbox',
				'description' => __( 'Place the payment gateway in test mode.', 'giftcard' ),
				'default'	=> 'no',
			)
		);		
	}

	public function check_givex_balance($givex_number) {
	    $user_id  = $this->user_id;
	    $api_url  = $this->api_url;
	    $port_num = $this->port_num;

	    $params = array(
	        "en",
	        "100",
	        $user_id,
	        $givex_number);
	    $data = array(
	        "jsonrpc" => "2.0",
	        "id" => "givexCardBalance",
	        "method" => "909",
	        "params" => $params
	    );
	    $data_string = json_encode($data);
	    $curl = curl_init();
	    $method = "POST";
	    curl_setopt_array($curl, array(
	        CURLOPT_PORT => $port_num,
	        CURLOPT_URL => $api_url,
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_ENCODING => "",
	        CURLOPT_MAXREDIRS => 10,
	        CURLOPT_TIMEOUT => 30,
	        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	        CURLOPT_CUSTOMREQUEST => $method,
	        CURLOPT_POSTFIELDS => $data_string,
	        CURLOPT_HTTPHEADER => array(
	            "cache-control: no-cache",
	            'Content-Length: ' . strlen($data_string)
	        ),
	    ));

	    $response = curl_exec($curl);
	    $err = curl_error($curl);

	    curl_close($curl);

	    if ($err) {
	        echo "cURL Error #:" . $err;
	        die;
	    } else {
	        $result_array = json_decode($response);
			$response_result = $result_array->result;
			return $response_result;
	        
	    }
	}

	public function redeem_givex_card($from,$amount){
//		$from = "60600631543100012476";
//		$amount= "10.00";
		$user_id  = $this->user_id;
	    $password = $this->password;
	    $api_url  = $this->api_url;
	    $port_num = $this->port_num;

	    givex_global_vars();
	    $params = array(
	        "en",// lang code
	        "100",// ttrans code
	        $user_id,//user id
	        $password,// password
	        $from,
	        $amount);
	    $data = array(
	        "jsonrpc"=>"2.0",
	        "id"=>"curltext",
	        "method"=>"901",
	        "params"=>$params
	    );
	    $data_string = json_encode($data);
	    $curl = curl_init();
	    $method = "POST";
	    curl_setopt_array($curl, array(
	        CURLOPT_PORT => $port_num,
	        CURLOPT_URL => $api_url,
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_ENCODING => "",
	        CURLOPT_MAXREDIRS => 10,
	        CURLOPT_TIMEOUT => 30,
	        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	        CURLOPT_CUSTOMREQUEST => $method,
	        CURLOPT_POSTFIELDS => $data_string,
	        CURLOPT_HTTPHEADER => array(
	            "cache-control: no-cache",
	            'Content-Length: ' . strlen($data_string)
	        ),
	    ));

	    $response = curl_exec($curl);
	    $err = curl_error($curl);
	    curl_close($curl);
	    if ($err) {
	        echo "cURL Error #:" . $err;die;
	    } else {
	        $result_array = json_decode($response);
			$response_result = $result_array->result;
	        return $response_result;
		/*
			- If the result code = 0 (success):
			- The fourth field will contain the Givex transaction reference.
			- The fifth field will be the balance left on the certificate.
			- The sixth field will be the expiration date.
			- The optional seventh field may contain a receipt message.
			- The optional eighth field contains the points added for SKU.
			- The optional ninth field contains the points balance for SKU.
			- If the result code = 9 (error: amount requested exceeds the card balance):
		*/
	    }
	}
	
	// Submit payment and handle response
	public function process_payment( $order_id ) {
		global $woocommerce;
		
		// Get this Order's information so that we know
		// who to charge and how much
		$customer_order = new WC_Order( $order_id );
		$card_balance_error_msg = null;
		if(isset($_POST[ $this->id.'-card-number']) && trim($_POST[ $this->id.'-card-number'])!=''){
			$gift_card_number = $_POST[ $this->id.'-card-number'];
			//$order->add_order_note(esc_html($_POST[ $this->id.'-admin-note']),1);
			$card_balance_response = $this->check_givex_balance($gift_card_number);
			$card_balance = 0;
			//Get total order
			$order_total = $customer_order->order_total;
			if ($card_balance_response[1] == 0) {
				$card_balance = $card_balance_response[2];
				if($card_balance < $order_total){
					$card_balance_error_msg =  "Insuffient Balance";
				}
			} else {
				if($card_balance_response[2]=="Cert not exist"){
					$card_balance_error_msg =  "Invalid gift card number";
				}else{
					$card_balance_error_msg =  $card_balance_response[2];
				}
			}

			if ( $card_balance >= $order_total && $card_balance_error_msg == null) {
				$redeem_response = $this->redeem_givex_card($gift_card_number,$order_total);
				//$redeem_response['1'] = 0;
				if($redeem_response['1'] == 0){
					$customer_order->add_order_note( __( 'Giftcard payment completed.', 'giftcard' ) );
					// Mark order as Paid
					$customer_order->payment_complete();
					// Empty the cart (Very important step)
					$woocommerce->cart->empty_cart();
					// Redirect to thank you page
					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $customer_order ),
					);
				}else{
					$response_reason_text = $redeem_response['4'];
					wc_add_notice( $response_reason_text, 'error' );
					// Add note to the order for your reference
					$customer_order->add_order_note( 'Error: '. $response_reason_text );
				}

			}else{
				wc_add_notice( $card_balance_error_msg, 'error' );
				// Add note to the order for your reference
				$customer_order->add_order_note( 'Error: '. $card_balance_error_msg);
			}

		}
	}

	public function payment_fields(){
		?>
		<fieldset>
			<p class="form-row form-row-wide">
				<label for="<?php echo $this->id; ?>-admin-note"><?php echo esc_attr($this->description); ?>
					<br>
				<span class="required">Gift Card Number*</span></label>
				<input id="<?php echo $this->id; ?>-card-number" class="input-text" type="text" name="<?php echo $this->id; ?>-card-number">
			</p>						
			<div class="clear"></div>
		</fieldset>
		<?php
	}
	
	// Validate fields
	public function validate_fields() {
		if($_POST[ $this->id.'-card-number']==""){
			wc_add_notice( "Please enter a gift card number", 'error' );
			return false;
		}else{
			return true;
		}
	}
	
	// Check if we are forcing SSL on checkout pages
	// Custom function not required by the Gateway
	public function do_ssl_check() {
		if( $this->enabled == "yes" ) {
			if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
				echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) ."</p></div>";	
			}
		}		
	}

} // End of GiftCard