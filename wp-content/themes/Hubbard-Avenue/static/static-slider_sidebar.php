<div class="slider-sidebar">
<?php

	global $switched;
    $site_id = mmi_opts_get('site_id');
    switch_to_blog($site_id);
    $number = 3;
    $args = array(
        'number'     => $number,
        'orderby'    => 'id',
        'order'      => 'ASC',
        'hide_empty' => true,  
    );
    $product_cat = get_terms( 'product_cat', $args );
    foreach( $product_cat as $cat) {
        $term_id = $cat->term_id;
        $term_name = $cat->name;
        $args = array(
            'posts_per_page' => 1,
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
        foreach($postslist as $list) {
?>
            <div class="span3">
                <?php $url = wp_get_attachment_url( get_post_thumbnail_id($list->ID), 'thumbnail' ); ?>
                <img src="<?php echo $url; ?>" />
                
                <a href="<?php echo $term_link; ?>" >
                    <h2><?php echo "View ".$term_name; ?> </h2>
                </a>
            </div>
<?php } } restore_current_blog(); ?>
</div>