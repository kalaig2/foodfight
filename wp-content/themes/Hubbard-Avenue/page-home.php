<?php
/**
 * Template Name: Home Page
 */
get_header();
?>
<div class="motopress-wrapper content-holder clearfix">
    <div class="container">
        <div class="row">
            <?php do_action('cherry_before_home_page_content'); ?>
            <div class="<?php echo apply_filters('cherry_home_layout', 'span12'); ?>" data-motopress-wrapper-file="page-home.php" data-motopress-wrapper-type="content">
                <div class="row">
                    <div class="<?php echo apply_filters('cherry_home_layout', 'span12'); ?>" data-motopress-type="static" data-motopress-static-file="static/static-slider.php">
                        <?php get_template_part("static/static-slider"); ?>
                    </div>
                <!-- Display Product Category Page-->
                <div class="product-category www">
                    <?php 
                    	$number = 3;
                        $args = array(
                            'number'     => $number,
                            'orderby'    => 'id',
                            'order'      => 'ASC', 
                            'hide_empty' => false,  
                        );
                        $product_categories = get_terms( 'product_cat', $args );
                        foreach ( $product_categories as $pro ) { 
                           $cat_thumb_id = get_woocommerce_term_meta( $pro->term_id, 'thumbnail_id', true );
                            $shop_catalog_img = wp_get_attachment_image_src( $cat_thumb_id, 'full' );
                             $term_link = get_term_link( $pro, 'product_cat' );
                             $term_desc = $pro->description;
                             $term_substr = substr($term_desc, 0,60); 
                        ?>
                        <div class="span4">
                            <a href="<?php echo $term_link; ?>" >
                                <image src="<?php echo $shop_catalog_img[0]; ?>" />
                            </a>
                            <h2><?php echo $pro->name; ?> </h2>
                            <p><?php echo $term_substr."..."; ?></p>
                            <a href="<?php echo $term_link; ?>" >
                                <span>View Products</span>
                            </a>
                        </div>
                    <?php 
                        } 
                        wp_reset_query();
                    ?>
                </div>

                <div class="row">
                    <div class="<?php echo apply_filters('cherry_home_layout', 'span12'); ?>" data-motopress-type="loop" data-motopress-loop-file="loop/loop-page.php">
                        <?php get_template_part("loop/loop-page"); ?>
                    </div>
                </div>

                <div class="product-category">
	                <?php get_template_part("static/static-subpage"); ?> 
           		</div>

            </div>
            <?php  do_action('cherry_after_home_page_content'); ?>
        </div>
    </div>    
</div>
<?php get_footer(); ?>

