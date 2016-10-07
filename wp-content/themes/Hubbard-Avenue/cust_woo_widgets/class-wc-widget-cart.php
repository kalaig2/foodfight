<?php

class Custom_WooCommerce_Widget_Cart extends WC_Widget_Cart
{

    function widget($args, $instance)
    {
        if (apply_filters('woocommerce_widget_cart_is_hidden', is_cart())) {
            return;
        }

        $hide_if_empty = empty($instance['hide_if_empty']) ? 0 : 1;

        $this->widget_start($args, $instance);

        if ($hide_if_empty) {
            echo '<div class="hide_cart_widget_if_empty">';
        }

        // Insert cart widget placeholder - code in woocommerce.js will update this on page load
        echo '<div class="widget_shopping_cart_content"></div>';

        if ($hide_if_empty) {
            echo '</div>';
        }

        $this->widget_end($args);
    }

}
