<style>
    .entry-header,.entry-content{
        max-width: 100% !important;
    }

    ul.products li {
        width: 200px;
        display: inline-block;
        padding-top: 3px;
    }
    ul.products li .prod{
        border:none;
        width: 200px;
        display: inline-block;
        margin:3px;
    }
    .prod input {
        width: 130px;
        margin:3px;
    }
</style>

<?php
if($is_givex){

    $dir = plugin_dir_path( __FILE__ );
    include($dir."../header.php");
}
?>

<!--<form method="post">-->
    <div>
        <ul class="products">
            <li>
                <div class="prod">
                    <input type="checkbox">

                    <img width="200" height="100px" src="<?php echo plugins_url( 'images/AnniversaryEcard.jpg',dirname(__FILE__)) ?>">
                    <a href="#">Anniversary</a>
                    <div>
                        <label for="">$</label><input type="text" value="" placeholder="Ex:10.00">
                    </div>
                </div>
            </li>
            <li>
                <div class="prod">
                    <input type="checkbox">
                    <img width="200px" height="100px" src="<?php echo plugins_url( 'images/CongratulationsEcard.jpg',dirname(__FILE__)) ?>">
                    <a href="#">Congratulations</a>
                    <div>
                        <label for="">$</label><input type="text" value="" placeholder="Ex:10.00">
                    </div>
                </div>
            </li><!-- more list items -->
            <li>
                <div class="prod">
                    <input type="checkbox">
                <img width="200px" height="100px" src="<?php echo plugins_url( 'images/FoodFightEcard.jpg',dirname(__FILE__)) ?>">
                    <a href="#">FoodFightEcard</a>
                    <div>
                        <label for="">$</label><input type="text" value="" placeholder="Ex:10.00">
                    </div>
                </div>
            </li><!-- more list items -->
            </ul>
        <button>Buy</button>
    </div>
<!--</form>-->
