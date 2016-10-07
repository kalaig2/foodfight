<?php

	/*global $switched;
    $site_id = mmi_opts_get('site_id');*/
    $main_blog_id = get_current_blog_id();
    $rows = get_field( 'multisite' );
    foreach($rows as $row) {
        $post = $row['site_name'];
        if( !empty($post) ) {
            $pId = $post->ID;
            $site_id = get_field('ff_site_id',$pId);
            $blog_name = $post->post_title; ?>
            <div class="span12">
                <h2><?php echo $blog_name; ?></h2>
            </div>
            <?php    
                switch_to_blog($site_id);
                $number = 3;
                $args = array(
                    'number'     => $number,
                    'orderby'    => 'date',
                    'order'      => 'DESC',
                    'hide_empty' => false,  
                );
                $product_cat = get_terms( 'product_cat', $args );    
                if(!empty($product_cat)) {
                    foreach( $product_cat as $cat) {
                        $term_id = $cat->term_id;
                        $term_name = $cat->name;
                        $args = array(
                            'posts_per_page' => 1,
                            'orderby'   => 'date',
                            'post_type' => 'product',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'product_cat',
                                    'field' => 'term_id',
                                    'terms' => $term_id
                                )
                            )
                        );
                        $term_link = get_term_link( $cat, 'product_cat' );
                        $postslist = get_posts( $args );
                        foreach($postslist as $list) { ?>
                            <div class="span4">
                                <?php 
                                    $url = wp_get_attachment_url( get_post_thumbnail_id($list->ID), 'thumbnail' ); 
                                ?>
                                <a href="<?php echo $term_link; ?>" >
                                    <section class="home_img">
                                        <img src="<?php echo $url ?>" />
                                    </section>
                                    <h2><?php echo $list->post_title; ?> </h2>
                                    <span><?php echo "View ".$term_name; ?></span>
                                </a>
                            </div>
            <?php 
                        } 
                    } 
                } else { ?>
                    <div class=span12>
                        <h3>No Products Found</h3>
                    </div>
            <?php
                }
            switch_to_blog($main_blog_id); 
        } else { ?>
            <!--<div class=span12>
                <h3>No Products Found</h3>
            </div>-->
    <?php }
}
    switch_to_blog($main_blog_id); 