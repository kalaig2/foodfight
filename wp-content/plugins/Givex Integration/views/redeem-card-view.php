<style>
    .entry-header,.entry-content{
        max-width: 100% !important;
    }
</style>

<?php
$dir = plugin_dir_path( __FILE__ );
include($dir."../header.php");?>

<form id="givex-transfer-balance-form" method="post" >
    <div>
        <label for="givex_from_number" class="col-sm-2 control-label">
            Enter Card Number
        </label>
        <div >
            <input type="text" class="input-medium form-control" id="givex_from_number" />
            <span style="color: grey">From card: 603628737462001019268</span>
        </div>
    </div>
    <div>
        <label for="givex_to_number" class="col-sm-2 control-label">
            Amount
        </label>
        <div >
            <input type="text" class="input-medium form-control" id="givex_amount"  placeholder="Ex:10.00"/>
        </div>
    </div>

    <div>
        <div>
            <button type="button" class="btn btn-default" id="redeem_card">
                Redeem
            </button>
            <button type="button" class="btn btn-default" id="activate_card">
                Activate card
            </button>
            <button type="button" class="btn btn-default" id="increment_balance">
                Add money
            </button>
        </div>
    </div>

</form>
<div>
    <h1>
        <span id="givex_balance"></span>
    </h1>
</div>