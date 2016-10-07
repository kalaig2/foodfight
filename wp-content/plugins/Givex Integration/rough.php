<?php
/**
 * Created by PhpStorm.
 * User: krishna
 * Date: 30/7/16
 * Time: 3:21 PM
 */

//        global $post;
//        $terms = get_the_terms( "2245", 'product_cat' );
//        foreach ($terms as $term) {
//            echo $product_cat_id = $term->term_id;
//            break;
//        }

//        $product_cats = wp_get_post_terms( "2245", 'product_cat' );
//        echo "<pre>";print_r($product_cats['0']->slug);
//        print_r($eachItems['item_meta']);



add_action('admin_menu', 'add_givex_menu');

function add_givex_menu(){
    add_menu_page( 'Givex', 'Givex API', 'manage_options', 'givex-api', 'givex_init' );
}

function givex_init(){
    givex_handle_post();
    ?>
    <form method="post">
        <div >
            <label for="givex_number" class="col-sm-2 control-label">
                Enter Givex Number
            </label>
            <div >
                <input type="text" id="givex_number" name="givex_number"/>
                <span>Test card: 603628737462001019268</span>
            </div>
        </div>

        <?php submit_button('Check') ?>
    </form>
    <?php
}



function givex_handle_post(){
    // First check if the file appears on the _FILES array
    if(isset($_POST['givex_number'])){
        $givex_number = $_POST['givex_number'];
        get_card_balance($givex_number);
    }
}


function get_card_balance($givex_number){
    $params = array("en","69","30308",$givex_number);
    $data = array("jsonrpc"=>"2.0","id"=>"curltext","method"=>"909",
        "params"=>$params);
    $data_string = json_encode($data);
    $url = 'https://dev-dataconnect.givex.com:50104';
    $port = "50104";
    $curl = curl_init();
    $method = "POST";
    curl_setopt_array($curl, array(
        CURLOPT_PORT => $port,
        CURLOPT_URL => $url,
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
        $result = $result_array->result;
        if($result['1']==0){
            echo "Your card balance is $".$result['2'];die;
        }else{
            echo "Something went wrong";die;
        }
    }
}


//function elegance_referal_init()
//{
//    if(is_page('test')){
////        echo "dfdf";die;
//        $dir = plugin_dir_path( __FILE__ );
//        include($dir."frontend-form.php");
//        die();
//    }
//}
//
//add_action( 'wp', 'elegance_referal_init' );

//[foobar]
function foobar_func( $atts ){
    givex_handle_post();$dir = plugin_dir_path( __FILE__ );
    include($dir."CheckBalance.php");
}
add_shortcode( 'foobar', 'foobar_func' );


//function load_givex_admin_js(){
//    wp_enqueue_script('givex-ajax',plugin_dir_url(__FILE__).'js/givex-ajax.js',array('jQuery'));
//}
//
//add_action('admin_enqueue_scripts' , 'load_givex_admin_js');
