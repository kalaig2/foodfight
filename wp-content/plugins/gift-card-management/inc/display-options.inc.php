<?php

function gc_display_contents()
{
    ?>
    <style>
        body { background: none; }
    </style>
    <div class="giftcard">
        <div class="success" id="card_success"></div>
        <div class="error" id="card_error"></div>
        <h2>Gift Card Management</h2>
        <form>
            <label for="gc_number">Enter Gift card number*</label>
            <input type="number" name="gc_number" id="gc_number" required/>
            <label for="card_type">Card Type</label>
            <select name="card_type" id="card_type">
                <?php
                $args = array(
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        'relation' => 'AND',
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'slug',
                            'terms' => 'gift-cards'
                        )
                    ),
                    'post_type' => 'product',
                    'orderby' => 'title,'
                );
                $products = new WP_Query($args);

                while ($products->have_posts()) {
                    $products->the_post();
                    if (get_the_title() == "Standard") {

                        if (have_rows('gift_card_images', get_the_id())) {

                            // loop through the rows of data
                            while (have_rows('gift_card_images', get_the_id())) {
                                the_row();

                                // display a sub field value
                                ?>
                                <option value="<?php the_sub_field('type', get_the_id()); ?>">
                                  <?php the_sub_field('type', get_the_id()); ?>
                                </option>
                                <?php
                            }
                        }
                    }
                }
                ?>
            </select>
            <label for="no_of_card">Number of Cards*</label>
            <input type="number" name="no_of_card" id="no_of_card" required/>
            <input type="button" value="Add card" id="add_card"/>
            <span id="loader_container"><img src="<?php echo dirname(plugin_dir_url(__FILE__)) ?>/images/loader.gif" /></span>
        </form>

        <div id="list_cards">
            <h2>Active Cards</h2>
            <table class="table table-condensed">
                <tr>
                    <th>Card Numbers</th>
                    <th>Price</th>
                    <th>Card Type</th>
                    <th>Availability</th>
                    <th>Givex Status</th>
                    <th>Action</th>
                </tr>
                <tr class="replace">
                </tr>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="myModal" class="modal fade giftcard-modal" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Gift Card</h4>
                </div>
                <div class="modal-card-values">
                    <table class="table table-condensed">
                        <tr>
                            <th>Card Numbers</th>
                            <th>Price</th>
                            <th>Card Type</th>
                            <th>Action</th>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
