<style>
    .entry-header,.entry-content{
        max-width: 100% !important;
    }
</style>

<?php
$dir = plugin_dir_path( __FILE__ );
include($dir."../header.php");?>

<form>
    <div>
        <label for="givex_number" class="col-sm-2 control-label">
            Amount
        </label>
        <div>
            <input type="text" class="input-medium form-control" id="givex_amount" placeholder="Ex:50.00"/>
        </div>
    </div>
    <div>
        <div>
            <button type="button" class="btn btn-default" id="register_card">
                Register
            </button>
        </div>
    </div>
</form>
<div>
    <h1>
        <span id="givex_balance"></span>
    </h1>
</div>
