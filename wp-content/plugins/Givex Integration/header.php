<?php
?>
<style>
    .entry-header,.entry-content{
        max-width: 100% !important;
    }
    .entry-content ul li {
        display: inline;
        padding: 6px;
    }
</style>
<ul>
    <?php
    $pages = get_pages();
    foreach ( $pages as $page ) {
        if(substr( $page->post_content, 0, 7 ) === "[givex-"){?>
           <li><a href="<?php echo get_page_link( $page->ID ); ?>"><?php echo $page->post_title; ?></a></li>
            <?php
        }
    }
    ?>
</ul>

