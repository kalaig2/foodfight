<?php

function gc_display_contents() {
    ?>
    <div class="error" id="card_error">There is an error</div>
    <div class="success" id="card_success">Card number added successfully</div>
    <div>
        <h2>Gift Card Management</h2>
        <form>
            <input type="number" name="gc_number" id="gc_number"/><input type="button" value="Add card" id="add_card"/>
            <span id="loader_container"><img src="<?php echo dirname(plugin_dir_url(__FILE__)) ?>/images/loader.gif" /></span>
        </form>
    </div>       
    <div id="list_cards">
        <h2>Active Cards</h2>
        <table> 
            <tr><th>Card Numbers</th></tr>
        </table>
    </div>
    <?php
}
?>