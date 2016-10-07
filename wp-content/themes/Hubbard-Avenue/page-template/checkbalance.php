<?php
/*
  Template Name: checkbalance
 */
?>
<style>
.check-balance label { display: inline-block; font-size: 16px; margin-top: 50px;margin-left: 50px;margin-bottom: 20px;
}
.check-balance label { font-size:16px; margin-bottom: 10px; } 
.check-balance #card_number {height: 35px;margin-left: 10px;margin-top: 10px;width: 250px; }
.check-balance button, h1 #balance_result{margin-left: 50px;}
.check-balance .gift-title { text-align: center; margin-top: 40px;}
</style>
<?php get_header(); ?>
<?php
if (isset($_POST['card_number']) && $_POST['card_number'] !="") {

	$givex_number = $_POST['card_number'];
	if( !empty($givex_number) ) {
		$params = array(
			"en",
			"100",
			get_option('givex_user_id'),
			$givex_number
		);
		$data = array(
			"jsonrpc"=>"2.0",
			"id"=>"curltext",
			"method"=>"909",
			"params"=>$params
		);
		$data_string = json_encode($data);
		$curl = curl_init();
		$method = "POST";
		curl_setopt_array($curl, array(
			CURLOPT_PORT => get_option('givex_port'),
			CURLOPT_URL => get_option('givex_url'),
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

//		$response = curl_exec($curl);
		$i=0;$err=true;
		while ($i++ <3) {
			$response=curl_exec($curl);
			if ($response){
				$err=false;
				break;
			}else{
				$err = curl_error($curl);
			}
			if ($i<3) sleep($i);
		}

		if ($err) {
			$givex_response = "Something went wrong";
//			$givex_response = "cURL Error #:" .curl_errno($curl). $err;
			//echo "cURL Error #:" . $err;die;
		} else {
			$result_array = json_decode($response);
			//        print_R($result_array);die;
			$result = $result_array->result;
			if($result['1']==0){
				$givex_response = "Your card balance is $".$result['2'];
			}else{
				$givex_response = $result['2'];
			}
		}
		curl_close($curl);
	}
}else if(isset($_POST['card_number']) && trim($_POST['card_number']) ==""){
	$givex_response = "Please enter a givex number";
}

?>
<div class="motopress-wrapper content-holder clearfix">
    <div class="container">
        <div class="row">
        	<div class="check-balance">
        		<div class="span12">
        			<h1 class="gift-title">Gift card Balance</h1>
        		</div>
				<form id="checkbalance" method="post" action="">
				    <div>
				        <label for="givex_number" class="col-sm-2 control-label">
				            Enter Givex Number
				        </label>
			            <input type="text" class="input-medium form-control" id="card_number" name="card_number" />
			            <span>Test card: 603628737462001019268</span>
				    </div>
				    <div>
				        <div>
				            <button type="submit" class="btn btn-default" id="ff_check_balance" name="ff_check_balance">
				                Check Balance
				            </button>
				        </div>
				    </div>
				</form>
				<div>
				    <h1>
				        <span id="balance_result">
							<?php
							if(isset($givex_response)){
								echo $givex_response;
							}?>
						</span>
				    </h1>
				</div>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>

