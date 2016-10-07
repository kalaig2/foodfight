<?php
/*
  Template Name: Giftcards
 */
?>
<?php get_header(); ?>
<div class="motopress-wrapper content-holder clearfix">
    <div class="container">
        <div class="row">
        	<div class="product-category">
        		<div class="span12">
        			<h1 class="gift-title">The Gift Card Shop</h1>
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
				        	$gc_image    = $first_image['image']; ?> 
				        	<div class="span4 subpage-category">
                                <a href="<?php echo get_permalink($list->ID); ?>">
				        		  <img src="<?php echo $gc_image['url']; ?>">
				        		  <h1><?php echo $list->post_title; ?></h1>
				        		  <span>Buy</span>
				        		</a>
				        	</div>   
           			<?php 
                        } 
                       wp_reset_postdata(); 
                    ?>
			</div>
        </div>
    </div>
</div>

<?php get_footer(); ?>