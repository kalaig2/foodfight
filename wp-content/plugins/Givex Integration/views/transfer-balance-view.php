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
            From
        </label>
        <div >
            <input type="text" class="input-medium form-control" id="givex_from_number" />
            <span style="color: grey">From card: 603628737462001019268</span>
        </div>
    </div>
    <div>
        <label for="givex_to_number" class="col-sm-2 control-label">
            To
        </label>
        <div >
            <input type="text" class="input-medium form-control" id="givex_to_number" />
            <span style="color: grey">To card: 603628496912001019294</span>
        </div>
    </div>
    <div>
        <label for="givex_amount" class="col-sm-2 control-label">
            Amount
        </label>
        <div >
            <input type="text" class="input-medium form-control" id="givex_amount" />
        </div>
    </div>
    <div>
        <div>
            <button type="button" class="btn btn-default" id="tranfer_balance">
                Transfer
            </button>
        </div>
    </div>
    <div>
        <p>
            Note:<br>
            CARD A 603628557262001019253 <br>
            CARD B 603628737462001019268 <br>
            CARD C 603628911922001019276 <br>
            CARD D 603628446622001019287 <br>
            CARD E 603628496912001019294
            <!--            CARD F 603628578642001019304-->
        </p>
    </div>
</form>
<div>
    <h1>
        <span id="givex_balance"></span>
    </h1>
</div>