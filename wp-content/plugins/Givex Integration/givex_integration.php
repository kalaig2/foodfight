<?php
/*
Plugin Name: Givex Integration
Description: A plugin to interact with www.givex.com
Author: Krishna Kumar
Version: 0.1
*/

add_action( 'admin_menu', 'add_givex_menu' );

function add_givex_menu() {
    add_menu_page( 'Givex Settings', 'Givex', 'manage_options', 'myplugin/myplugin-admin-page.php', 'givex_admin_page_content', 'dashicons-tickets', 6  );
}
add_action( 'admin_init', 'register_mysettings' );

function register_mysettings() {
    register_setting( 'givex-settings', 'givex_user_id' );
    register_setting( 'givex-settings', 'givex_password' );
    register_setting( 'givex-settings', 'givex_url' );
    register_setting( 'givex-settings', 'givex_port' );
}
function givex_admin_page_content(){
    ?>
    <div class="wrap">
        <h2>Givex Configuration</h2>
    </div>
    <form method="post" action="options.php">
        <?php settings_fields( 'givex-settings' ); ?>
        <?php do_settings_sections( 'givex-settings' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Givex User Id:</th>
                <td><input type="text" name="givex_user_id" value="<?php echo get_option( 'givex_user_id' ); ?>"/></td>
            </tr>
            <tr valign="top">
                <th scope="row">Givex Password:</th>
                <td><input type="text" name="givex_password" value="<?php echo get_option( 'givex_password' ); ?>"/></td>
            </tr>
            <tr valign="top">
                <th scope="row">Givex Url:</th>
                <td><input type="text" name="givex_url" value="<?php echo get_option( 'givex_url' ); ?>"/></td>
            </tr>
            <tr valign="top">
                <th scope="row">Givex Port:</th>
                <td><input type="text" name="givex_port" value="<?php echo get_option( 'givex_port' ); ?>"/></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    <p>
        <b>User id :</b>30308 <br>
        <b>Password :</b>7353 <br>
        <b>Url:</b> https://dev-dataconnect.givex.com <br>
        <b>Port :</b>50104
    </p>
    <?php
}

define("GIVEX_USER_ID","30308");
define("GIVEX_PASSWORD","7353");

$dir = plugin_dir_path( __FILE__ );

foreach (glob($dir."shortcodes/*.php") as $filename)
{
    include $filename;
}

function givex_global_vars() {

    global $givex_config;
    $givex_config = array(
        'url'  => "https://dev-dataconnect.givex.com",
        'port'  => "50104",
        'user_id'      => GIVEX_USER_ID,
        'password'      => GIVEX_PASSWORD,
        'language_code'      => "en",
        'transaction_code'    => "100"
    );

}
add_action( 'parse_query', 'givex_global_vars' );
//add_action( 'init', 'givex_global_vars' );

function set_curl_options($curl,$method,$data_string){
    curl_setopt_array($curl, array(
        CURLOPT_PORT => $GLOBALS['givex_config']['port'],
        CURLOPT_URL =>  $GLOBALS['givex_config']['url'],
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
}

function givex_handle_post(){
    if(isset($_POST['action'])){
        if( $_POST['action']=="givex_check_balance") {
            if (isset($_POST['givex_number'])) {
                $givex_number = $_POST['givex_number'];
                get_card_balance($givex_number);
            }
        }
        if($_POST['action']=="givex_get_history") {
            if (isset($_POST['givex_number'])) {
                $givex_number = $_POST['givex_number'];
                get_card_history($givex_number);
            }
        }
        if($_POST['action']=="givex_register_card") {
            if (isset($_POST['givex_amount'])) {
                $givex_amount = $_POST['givex_amount'];
                register_card($givex_amount);
            }
        }
        if($_POST['action']=="givex_transfer_balance") {
            if (isset($_POST['givex_from_number']) && isset($_POST['givex_to_number']) && isset($_POST['givex_amount'])) {
                $givex_from_number = $_POST['givex_from_number'];
                $givex_to_number = $_POST['givex_to_number'];
                $givex_amount = $_POST['givex_amount'];
                transfer_partial_balance($givex_from_number,$givex_to_number,$givex_amount);
            }
        }
        if($_POST['action']=="givex_redeem_card") {
            if (isset($_POST['givex_from_number']) && isset($_POST['givex_amount'])) {
                $givex_from_number = $_POST['givex_from_number'];
                $givex_amount = $_POST['givex_amount'];
                redeem_card($givex_from_number,$givex_amount);
            }
        }
        if($_POST['action']=="givex_activate_card") {
            if (isset($_POST['givex_from_number']) && isset($_POST['givex_amount'])) {
                $givex_from_number = $_POST['givex_from_number'];
                $givex_amount = $_POST['givex_amount'];
                activate_card($givex_from_number,$givex_amount);
            }
        }

        if($_POST['action']=="givex_increment_balance") {
            if (isset($_POST['givex_from_number']) && isset($_POST['givex_amount'])) {
                $givex_from_number = $_POST['givex_from_number'];
                $givex_amount = $_POST['givex_amount'];
                increment_balance($givex_from_number,$givex_amount);
            }
        }
        if($_POST['action']=="givex_account_details") {
            if (isset($_POST['givex_from_number']) ) {
                $givex_from_number = $_POST['givex_from_number'];
                get_account_details($givex_from_number);
            }
        }
    }

}


function get_card_balance($givex_number){
    givex_global_vars();
    $givex_number = $_POST['givex_number'];
    $params = array(
        $GLOBALS['givex_config']['language_code'],
        $GLOBALS['givex_config']['transaction_code'],
        $GLOBALS['givex_config']['user_id'],
        $givex_number);
    $data = array(
        "jsonrpc"=>"2.0",
        "id"=>"curltext",
        "method"=>"909",
        "params"=>$params
    );
    $data_string = json_encode($data);
    $curl = curl_init();
    $method = "POST";
    set_curl_options($curl,$method,$data_string);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;die;
    } else {
        $result_array = json_decode($response);
//        print_R($result_array);die;
        $result = $result_array->result;
        if($result['1']==0){
            echo "Your card balance is $".$result['2'];
        }else{
            echo $result['2'];die;
        }
    }
}
function get_account_details($givex_number){
    givex_global_vars();
    $params = array(
        $GLOBALS['givex_config']['language_code'],
        $GLOBALS['givex_config']['transaction_code'],
        $GLOBALS['givex_config']['user_id'],
        $givex_number);
    $data = array(
        "jsonrpc"=>"2.0",
        "id"=>"curltext",
        "method"=>"940",
        "params"=>$params
    );
    $data_string = json_encode($data);
    $curl = curl_init();
    $method = "POST";
    set_curl_options($curl,$method,$data_string);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;die;
    } else {
        $result_array = json_decode($response);
        print_R($result_array);die;
        $result = $result_array->result;
        if($result['1']==0){
            echo "Your card balance is $".$result['2'];
        }else{
            echo $result['2'];die;
        }
    }
}

function get_card_history($givex_number){
    givex_global_vars();
    $givex_number = $_POST['givex_number'];
    $params = array(
        $GLOBALS['givex_config']['language_code'],
        $GLOBALS['givex_config']['transaction_code'],
        $GLOBALS['givex_config']['user_id'],
        $givex_number,
        "",
        "",
        "Certificate");
    $data = array(
        "jsonrpc"=>"2.0",
        "id"=>"curltext",
        "method"=>"966",
        "params"=>$params
    );
    $data_string = json_encode($data);
    $curl = curl_init();
    $method = "POST";
    set_curl_options($curl,$method,$data_string);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;die;
    } else {
        $result_array = json_decode($response);
//        print_R($result_array);die;
        $result = $result_array->result;
        if($result['1']==0){
            print_r($result['5']);
        }else{
            echo $result['2'];die;
        }
    }
}
function register_card($givex_amount){
    givex_global_vars();
    $params = array(
        $GLOBALS['givex_config']['language_code'],
        $GLOBALS['givex_config']['transaction_code'],
        $GLOBALS['givex_config']['user_id'],
        $GLOBALS['givex_config']['password'],
        $givex_amount);
    $data = array(
        "jsonrpc"=>"2.0",
        "id"=>"curltext",
        "method"=>"904",
        "params"=>$params
    );
    $data_string = json_encode($data);
    $curl = curl_init();
    $method = "POST";
    set_curl_options($curl,$method,$data_string);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;die;
    } else {
        $result_array = json_decode($response);
        print_R($result_array);die;
        $result = $result_array->result;
        if($result['1']==0){
            print_r($result['5']);
        }else{
            echo $result['2'];die;
        }
    }

}

function transfer_partial_balance($from,$to,$amount){

    givex_global_vars();
    $params = array(
        $GLOBALS['givex_config']['language_code'],
        $GLOBALS['givex_config']['transaction_code'],
        $GLOBALS['givex_config']['user_id'],
        $GLOBALS['givex_config']['password'],
        $from,
        $to,
        $amount);
    $data = array(
        "jsonrpc"=>"2.0",
        "id"=>"curltext",
        "method"=>"932",
        "params"=>$params
    );
    $data_string = json_encode($data);
    $curl = curl_init();
    $method = "POST";
    set_curl_options($curl,$method,$data_string);
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;die;
    } else {
        $result_array = json_decode($response);
        print_R($result_array);die;
        $result = $result_array->result;
        if($result['1']==0){
            echo "Your card balance is $".$result['2'];
        }else{
            echo $result['2'];die;
        }
    }
}
function redeem_card($from,$amount){

    givex_global_vars();
    $params = array(
        $GLOBALS['givex_config']['language_code'],
        $GLOBALS['givex_config']['transaction_code'],
        $GLOBALS['givex_config']['user_id'],
        $GLOBALS['givex_config']['password'],
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
    set_curl_options($curl,$method,$data_string);
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;die;
    } else {
        $result_array = json_decode($response);
//        print_R($result_array);die;
        $result = $result_array->result;
        if($result['1']==0){
            echo "Successfully redeemed.Your card balance is  $".$result['3'];
        }else{
            echo $result['2'];die;
        }
    }
}
function activate_card($from,$amount){

    givex_global_vars();
    $params = array(
        $GLOBALS['givex_config']['language_code'],
        $GLOBALS['givex_config']['transaction_code'],
        $GLOBALS['givex_config']['user_id'],
        $GLOBALS['givex_config']['password'],
        $from,
        $amount);
    $data = array(
        "jsonrpc"=>"2.0",
        "id"=>"curltext",
        "method"=>"906",
        "params"=>$params
    );
    $data_string = json_encode($data);
    $curl = curl_init();
    $method = "POST";
    set_curl_options($curl,$method,$data_string);
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;die;
    } else {
        $result_array = json_decode($response);
//        print_R($result_array);die;
        $result = $result_array->result;
        if($result['1']==0){
            echo "Successfully Activated.Your card balance is  $".$result['3'];
        }else{
            echo $result['2'];die;
        }
    }
}

function increment_balance($from,$amount){
    givex_global_vars();
    $params = array(
        $GLOBALS['givex_config']['language_code'],
        $GLOBALS['givex_config']['transaction_code'],
        $GLOBALS['givex_config']['user_id'],
        $GLOBALS['givex_config']['password'],
        $from,
        $amount);
    $data = array(
        "jsonrpc"=>"2.0",
        "id"=>"curltext",
        "method"=>"906",
        "params"=>$params
    );
    $data_string = json_encode($data);
    $curl = curl_init();
    $method = "POST";
    set_curl_options($curl,$method,$data_string);
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;die;
    } else {
        $result_array = json_decode($response);
//        print_R($result_array);die;
        $result = $result_array->result;
        if($result['1']==0){
            echo "Successfully Added.Your card balance is  $".$result['3'];
        }else{
            echo $result['2'];die;
        }
    }
}


add_action('init', 'givex_handle_post');
