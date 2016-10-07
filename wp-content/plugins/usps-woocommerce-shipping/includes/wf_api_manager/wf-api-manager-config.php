<?php

$product_name = 'usps'; // name should match with 'Software Title' configured in server, and it should not contains white space
$product_version = '2.8.4';
$product_slug = 'usps-woocommerce-shipping/usps-woocommerce-shipping.php'; //product base_path/file_name
$serve_url = 'http://www.wooforce.com/';

//include api manager
include_once ( 'wf_api_manager.php' );
new WF_API_Manager($product_name, $product_version, $product_slug, $serve_url);
?>