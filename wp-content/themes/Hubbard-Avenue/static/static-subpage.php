<?php

    /*global $switched;
    $site_id = mmi_opts_get('site_id');*/
    $main_blog_id = get_current_blog_id(); 
    $site_id      = 1; 
    switch_to_blog($site_id);
?>
    <div class="span12">
        <h2>Gift cards</h2>
    </div>
    <?php 
        $args = array(
                'posts_per_page' => 2,
                'orderby'   => 'date',
                'post_type' => 'product',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => 'gift-cards'
                    )
                )
            );
            $term_link = get_term_link( $cat, 'product_cat' );
            $postslist = get_posts( $args );
            foreach($postslist as $list) { 
                $gift_card_images = get_field('gift_card_images',$list->ID);
                $first_image = $gift_card_images[0]; 
                $gc_image    = $first_image['image']; 
                $gc_description = $list->post_excerpt; 
                $gc_substr = substr($gc_description, 0,60);?> 
                <div class="span4 subpage-category">
                    <a href="<?php echo get_permalink($list->ID); ?>">
                      <img src="<?php echo $gc_image['url']; ?>">
                    </a>
                    <h1><?php echo $list->post_title; ?></h1>
                    <p><?php echo $gc_substr."..."; ?></p>
                    <a href="<?php echo get_permalink($list->ID); ?>">
                      <span>Buy</span>
                    </a>
                </div>   
        <?php 
            } 
           wp_reset_postdata(); 
        ?>
    <?php
    switch_to_blog($main_blog_id); 
    
