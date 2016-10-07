<?php

/**
 * Plugin Name: Export Order Items for WooCommerce
 * Description: Export order items (products ordered) in CSV (Comma Seperated Values) format, with product, line item, order, and customer data.
 * Version: 1.0.5
 * Author: Potent Plugins
 * Author URI: http://potentplugins.com/?utm_source=product-sales-report-for-woocommerce&utm_medium=link&utm_campaign=wp-plugin-credit-link
 * License: GNU General Public License version 2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 */
// Add Export Gift Card Order Items to the WordPress admin
add_action('admin_menu', 'hm_xoiwc_admin_menu');

function hm_xoiwc_admin_menu()
{
    add_submenu_page('woocommerce', 'Export Gift Card Order Items', 'Export Gift Card Order Items', 'view_woocommerce_reports', 'hm_xoiwc', 'hm_xoiwc_page');
}

function hm_xoiwc_default_report_settings()
{
    return array(
        'report_time' => '30d',
        'report_start' => date('Y-m-d', current_time('timestamp') - (86400 * 31)),
        'report_end' => date('Y-m-d', current_time('timestamp') - 86400),
        'order_statuses' => array('wc-processing', 'wc-on-hold', 'wc-completed'),
        'orderby' => 'order_id',
        'orderdir' => 'asc',
        'fields' => array('product_id','card_number','product_name', 'quantity', 'order_date', 'billing_name', 'billing_email'),
        'include_header' => 1
    );
}

// This function generates the Export Gift Card Order Items page HTML
function hm_xoiwc_page()
{

    $savedReportSettings = get_option('hm_xoiwc_report_settings');

    $reportSettings = (empty($savedReportSettings) ?
                    hm_xoiwc_default_report_settings() :
                    array_merge(hm_xoiwc_default_report_settings(), $savedReportSettings[0]
    ));

    $fieldOptions = array(
        'product_id' => 'Product ID',
        'card_number' => 'Card Number',
        'product_sku' => 'Product SKU',
        'product_name' => 'Product Name',
        'product_categories' => 'Product Categories',
        'order_id' => 'Order ID',
        'order_status' => 'Order Status',
        'order_date' => 'Order Date/Time',
        'quantity' => 'Line Item Quantity',
        'line_subtotal' => 'Line Item Gross',
        'line_total' => 'Line Item Gross After Discounts',
        'billing_name' => 'Billing Name',
        'billing_phone' => 'Billing Phone',
        'billing_email' => 'Billing Email',
        'billing_address' => 'Billing Address',
        'shipping_name' => 'Shipping Name',
        'shipping_phone' => 'Shipping Phone',
        'shipping_email' => 'Shipping Email',
        'shipping_address' => 'Shipping Address',
    );


// Print header
    echo('
		<div class="wrap">
			<h2>Export Gift Card Order Items</h2>
	');

// Check for WooCommerce
    if (!class_exists('WooCommerce')) {
        echo('<div class="error"><p>This plugin requires that WooCommerce is installed and activated.</p></div></div>');
        return;
    } else if (!function_exists('wc_get_order_types')) {
        echo('<div class="error"><p>The Export Gift Card Order Items plugin requires WooCommerce 2.2 or higher. Please update your WooCommerce install.</p></div></div>');
        return;
    }

// Print form
    echo('<form action="" method="post">
				<input type="hidden" name="hm_xoiwc_do_export" value="1" />
		');
    wp_nonce_field('hm_xoiwc_do_export');
    echo('
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="hm_xoiwc_field_report_time">Report Period:</label>
						</th>
						<td>
							<select name="report_time" id="hm_xoiwc_field_report_time">
								<option value="0d"' . ($reportSettings['report_time'] == '0d' ? ' selected="selected"' : '') . '>Today</option>
								<option value="1d"' . ($reportSettings['report_time'] == '1d' ? ' selected="selected"' : '') . '>Yesterday</option>
								<option value="7d"' . ($reportSettings['report_time'] == '7d' ? ' selected="selected"' : '') . '>Last 7 days</option>
								<option value="30d"' . ($reportSettings['report_time'] == '30d' ? ' selected="selected"' : '') . '>Last 30 days</option>
								<option value="all"' . ($reportSettings['report_time'] == 'all' ? ' selected="selected"' : '') . '>All time</option>
								<option value="custom"' . ($reportSettings['report_time'] == 'custom' ? ' selected="selected"' : '') . '>Custom date range</option>
							</select>
						</td>
					</tr>
					<tr valign="top" class="hm_xoiwc_custom_time">
						<th scope="row">
							<label for="hm_xoiwc_field_report_start">Start Date:</label>
						</th>
						<td>
							<input type="date" name="report_start" id="hm_xoiwc_field_report_start" value="' . $reportSettings['report_start'] . '" />
						</td>
					</tr>
					<tr valign="top" class="hm_xoiwc_custom_time">
						<th scope="row">
							<label for="hm_xoiwc_field_report_end">End Date:</label>
						</th>
						<td>
							<input type="date" name="report_end" id="hm_xoiwc_field_report_end" value="' . $reportSettings['report_end'] . '" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hm_xoiwc_field_orderby">Sort By:</label>
						</th>
						<td>
							<select name="orderby" id="hm_xoiwc_field_orderby">
								<option value="product_id"' . ($reportSettings['orderby'] == 'product_id' ? ' selected="selected"' : '') . '>Product ID</option>
								<option value="order_id"' . ($reportSettings['orderby'] == 'order_id' ? ' selected="selected"' : '') . '>Order ID</option>
							</select>
							<select name="orderdir">
								<option value="asc"' . ($reportSettings['orderdir'] == 'asc' ? ' selected="selected"' : '') . '>ascending</option>
								<option value="desc"' . ($reportSettings['orderdir'] == 'desc' ? ' selected="selected"' : '') . '>descending</option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label>Show Orders With Status:</label>
						</th>
						<td>');
    foreach (wc_get_order_statuses() as $status => $statusName) {
        echo('<label><input type="checkbox" name="order_statuses[]"' . (in_array($status, $reportSettings['order_statuses']) ? ' checked="checked"' : '') . ' value="' . $status . '" /> ' . $statusName . '</label><br />');
    }
    echo('</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label>Report Fields:</label>
						</th>
						<td id="hm_xoiwc_report_field_selection">');
    $fieldOptions2 = $fieldOptions;
    foreach ($reportSettings['fields'] as $fieldId) {
        if (!isset($fieldOptions2[$fieldId]))
            continue;
        echo('<label><input type="checkbox" name="fields[]" checked="checked" value="' . $fieldId . '"' . (in_array($fieldId, array('variation_id', 'variation_attributes')) ? ' class="variation-field"' : '') . ' /> ' . $fieldOptions2[$fieldId] . '</label>');
        unset($fieldOptions2[$fieldId]);
    }
    foreach ($fieldOptions2 as $fieldId => $fieldDisplay) {
        echo('<label><input type="checkbox" name="fields[]" value="' . $fieldId . '"' . (in_array($fieldId, array('variation_id', 'variation_attributes')) ? ' class="variation-field"' : '') . ' /> ' . $fieldDisplay . '</label>');
    }
    unset($fieldOptions2);
    echo('</td>
					</tr>
					<tr valign="top">
						<th scope="row" colspan="2" class="th-full">
							<label>
								<input type="checkbox" name="include_header"' . (empty($reportSettings['include_header']) ? '' : ' checked="checked"') . ' />
								Include header row
							</label>
						</th>
					</tr>
				</table>');
    echo('<p class="submit">
					<button type="submit" class="button-primary">Export</button>
				</p>
			</form>');


    $potent_slug = 'export-order-items-for-woocommerce';

    echo('
		</div>

		<script type="text/javascript" src="' . plugins_url('js/export-order-items.js', __FILE__) . '"></script>
	');
}

// Hook into WordPress init; this function performs report generation when
// the admin form is submitted
add_action('init', 'hm_xoiwc_on_init');

function hm_xoiwc_on_init()
{
    global $pagenow;

// Check if we are in admin and on the report page
    if (!is_admin())
        return;
    if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'hm_xoiwc' && !empty($_POST['hm_xoiwc_do_export'])) {

// Verify the nonce
        check_admin_referer('hm_xoiwc_do_export');

        $newSettings = array_intersect_key($_POST, hm_xoiwc_default_report_settings());
        foreach ($newSettings as $key => $value)
            if (!is_array($value))
                $newSettings[$key] = htmlspecialchars($value);

// Update the saved report settings
        $savedReportSettings = get_option('hm_xoiwc_report_settings');
        $savedReportSettings[0] = array_merge(hm_xoiwc_default_report_settings(), $newSettings);


        update_option('hm_xoiwc_report_settings', $savedReportSettings);

// Check if no fields are selected
        if (empty($_POST['fields']))
            return;

// Assemble the filename for the report download
        $filename = 'Order Items Export - ';
        if (!empty($_POST['cat']) && is_numeric($_POST['cat'])) {
            $cat = get_term($_POST['cat'], 'product_cat');
            if (!empty($cat->name))
                $filename .= addslashes(html_entity_decode($cat->name)) . ' - ';
        }
        $filename .= date('Y-m-d', current_time('timestamp')) . '.csv';

// Send headers
      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename="' . $filename . '"');

// Output the report header row (if applicable) and body
        $stdout = fopen('php://output', 'w');
        if (!empty($_POST['include_header']))
            hm_xoiwc_export_header($stdout);
        hm_xoiwc_export_body($stdout);

        exit;
    }
}

// This function outputs the report header row
function hm_xoiwc_export_header($dest, $return = false)
{
    $header = array();

    foreach ($_POST['fields'] as $field) {
        switch ($field) {
            case 'product_id':
                $header[] = 'Product ID';
                break;
            case 'card_number':
                $header[] = 'Card Number';
                break;
            case 'order_id':
                $header[] = 'Order ID';
                break;
            case 'order_status':
                $header[] = 'Order Status';
                break;
            case 'order_date':
                $header[] = 'Order Date/Time';
                break;
            case 'product_sku':
                $header[] = 'Product SKU';
                break;
            case 'product_name':
                $header[] = 'Product Name';
                break;
            case 'product_categories':
                $header[] = 'Product Categories';
                break;
            case 'billing_name':
                $header[] = 'Billing Name';
                break;
            case 'billing_phone':
                $header[] = 'Billing Phone';
                break;
            case 'billing_email':
                $header[] = 'Billing Email';
                break;
            case 'billing_address':
                $header[] = 'Billing Address';
                break;
            case 'shipping_name':
                $header[] = 'Shipping Name';
                break;
            case 'shipping_phone':
                $header[] = 'Shipping Phone';
                break;
            case 'shipping_email':
                $header[] = 'Shipping Email';
                break;
            case 'shipping_address':
                $header[] = 'Shipping Address';
                break;
            case 'quantity':
                $header[] = 'Line Item Quantity';
                break;
            case 'line_subtotal':
                $header[] = 'Line Item Gross';
                break;
            case 'line_total':
                $header[] = 'Line Item Gross After Discounts';
                break;
        }
    }

    if ($return)
        return $header;
    fputcsv($dest, $header);
}

// This function generates and outputs the report body rows
function hm_xoiwc_export_body($dest, $return = false)
{
    global $woocommerce, $wpdb;

// Calculate report start and end dates (timestamps)
    switch ($_POST['report_time']) {
        case '0d':
            $end_date = strtotime('midnight', current_time('timestamp'));
            $start_date = $end_date;
            break;
        case '1d':
            $end_date = strtotime('midnight', current_time('timestamp')) - 86400;
            $start_date = $end_date;
            break;
        case '7d':
            $end_date = strtotime('midnight', current_time('timestamp')) - 86400;
            $start_date = $end_date - (86400 * 7);
            break;
        case 'custom':
            $end_date = strtotime('midnight', strtotime($_POST['report_end']));
            $start_date = strtotime('midnight', strtotime($_POST['report_start']));
            break;
        default: // 30 days is the default
            $end_date = strtotime('midnight', current_time('timestamp')) - 86400;
            $start_date = $end_date - (86400 * 30);
    }

// Assemble order by string
    $orderby = (in_array($_POST['orderby'], array('order_id')) ? $_POST['orderby'] : 'product_id');
    $orderby .= ' ' . ($_POST['orderdir'] == 'asc' ? 'ASC' : 'DESC');

// Create a new WC_Admin_Report object
    include_once($woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php');
    $wc_report = new WC_Admin_Report();
    $wc_report->start_date = $start_date;
    $wc_report->end_date = $end_date;

// Order status filter
    $wcOrderStatuses = wc_get_order_statuses();
    $orderStatuses = array();
    foreach ($_POST['order_statuses'] as $orderStatus) {
        if (isset($wcOrderStatuses[$orderStatus]))
            $orderStatuses[] = substr($orderStatus, 3);
    }

// Get report data

    $reportData = array(
        '_product_id' => array(
            'type' => 'order_item_meta',
            'order_item_type' => 'line_item',
            'function' => '',
            'name' => 'product_id'
        ),
        'order_id' => array(
            'type' => 'order_item',
            'order_item_type' => 'line_item',
            'function' => '',
            'name' => 'order_id'
        )
    );

    if (in_array('quantity', $_POST['fields'])) {
        $reportData['_qty'] = array(
            'type' => 'order_item_meta',
            'order_item_type' => 'line_item',
            'function' => '',
            'name' => 'quantity'
        );
    }
    if (in_array('line_subtotal', $_POST['fields'])) {
        $reportData['_line_subtotal'] = array(
            'type' => 'order_item_meta',
            'order_item_type' => 'line_item',
            'function' => '',
            'name' => 'line_subtotal'
        );
    }

    if (in_array('line_total', $_POST['fields'])) {
        $reportData['_line_total'] = array(
            'type' => 'order_item_meta',
            'order_item_type' => 'line_item',
            'function' => '',
            'name' => 'line_total'
        );
    }

    if (in_array('order_status', $_POST['fields'])) {
        $reportData['post_status'] = array(
            'type' => 'post_data',
            'name' => 'order_status',
            'function' => '',
        );
    }
    if (in_array('order_date', $_POST['fields'])) {
        $reportData['post_date'] = array(
            'type' => 'post_data',
            'name' => 'order_date',
            'function' => '',
        );
    }
    if (in_array('billing_name', $_POST['fields'])) {
        $reportData['_billing_first_name'] = array(
            'type' => 'meta',
            'name' => 'billing_first_name',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_billing_last_name'] = array(
            'type' => 'meta',
            'name' => 'billing_last_name',
            'join_type' => 'LEFT',
            'function' => '',
        );
    }
    if (in_array('billing_phone', $_POST['fields'])) {
        $reportData['_billing_phone'] = array(
            'type' => 'meta',
            'name' => 'billing_phone',
            'join_type' => 'LEFT',
            'function' => '',
        );
    }
    if (in_array('billing_email', $_POST['fields'])) {
        $reportData['_billing_email'] = array(
            'type' => 'meta',
            'name' => 'billing_email',
            'join_type' => 'LEFT',
            'function' => '',
        );
    }
    if (in_array('billing_address', $_POST['fields'])) {
        $reportData['_billing_address_1'] = array(
            'type' => 'meta',
            'name' => 'billing_address_1',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_billing_address_2'] = array(
            'type' => 'meta',
            'name' => 'billing_address_2',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_billing_city'] = array(
            'type' => 'meta',
            'name' => 'billing_city',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_billing_state'] = array(
            'type' => 'meta',
            'name' => 'billing_state',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_billing_postcode'] = array(
            'type' => 'meta',
            'name' => 'billing_postcode',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_billing_country'] = array(
            'type' => 'meta',
            'name' => 'billing_country',
            'join_type' => 'LEFT',
            'function' => '',
        );
    }
    if (in_array('shipping_name', $_POST['fields'])) {
        $reportData['_shipping_first_name'] = array(
            'type' => 'meta',
            'name' => 'shipping_first_name',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_shipping_last_name'] = array(
            'type' => 'meta',
            'name' => 'shipping_last_name',
            'join_type' => 'LEFT',
            'function' => '',
        );
    }
    if (in_array('shipping_phone', $_POST['fields'])) {
        $reportData['_shipping_phone'] = array(
            'type' => 'meta',
            'name' => 'shipping_phone',
            'join_type' => 'LEFT',
            'function' => '',
        );
    }
    if (in_array('shipping_email', $_POST['fields'])) {
        $reportData['_shipping_email'] = array(
            'type' => 'meta',
            'name' => 'shipping_email',
            'join_type' => 'LEFT',
            'function' => '',
        );
    }
    if (in_array('shipping_address', $_POST['fields'])) {
        $reportData['_shipping_address_1'] = array(
            'type' => 'meta',
            'name' => 'shipping_address_1',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_shipping_address_2'] = array(
            'type' => 'meta',
            'name' => 'shipping_address_2',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_shipping_city'] = array(
            'type' => 'meta',
            'name' => 'shipping_city',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_shipping_state'] = array(
            'type' => 'meta',
            'name' => 'shipping_state',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_shipping_postcode'] = array(
            'type' => 'meta',
            'name' => 'shipping_postcode',
            'join_type' => 'LEFT',
            'function' => '',
        );
        $reportData['_shipping_country'] = array(
            'type' => 'meta',
            'name' => 'shipping_country',
            'join_type' => 'LEFT',
            'function' => '',
        );
    }

// Avoid max join size error
    $wpdb->query('SET SQL_BIG_SELECTS=1');

// Based on woocoommerce/includes/admin/reports/class-wc-report-sales-by-product.php
    $sold_products = $wc_report->get_order_report_data(array(
        'data' => $reportData,
        'query_type' => 'get_results',
        'group_by' => '',
        'order_by' => $orderby,
        'limit' => (!empty($_POST['limit_on']) && is_numeric($_POST['limit']) ? $_POST['limit'] : ''),
        'filter_range' => ($_POST['report_time'] != 'all'),
        'order_types' => wc_get_order_types('order_count'),
        'order_status' => $orderStatuses
    ));


// Output report rows
    foreach ($sold_products as $product) {
    	global $wpdb;
		$table_name = $wpdb->prefix . 'gift_cards';

        $row = array();

        $term_list = wp_get_post_terms($product->product_id, 'product_cat', array('fields' => 'ids'));
        $cat_id = (int) $term_list[0];
        $term = get_term($cat_id, 'product_cat');

        if ($term->slug == "gift-cards") {
          $card_number = $wpdb->get_row( "SELECT `card_number` FROM $table_name WHERE `order_id` = '$product->order_id'" );

            foreach ($_POST['fields'] as $field) {
                switch ($field) {
                    case 'product_id':
                        $row[] = $product->product_id;
                        break;
                    case 'card_number':
                        $row[] = $card_number->card_number."\t";
                        break;
                    case 'order_id':
                        $row[] = $product->order_id;
                        break;
                    case 'order_status':
                        $row[] = wc_get_order_status_name($product->order_status);
                        break;
                    case 'order_date':
                        $row[] = $product->order_date;
                        break;
                    case 'product_sku':
                        $row[] = get_post_meta($product->product_id, '_sku', true);
                        break;
                    case 'product_name':
                        $row[] = html_entity_decode(get_the_title($product->product_id));
                        break;
                    case 'product_categories':
                        $terms = get_the_terms($product->product_id, 'product_cat');
                        if (empty($terms)) {
                            $row[] = '';
                        } else {
                            $categories = array();
                            foreach ($terms as $term)
                                $categories[] = $term->name;
                            $row[] = implode(', ', $categories);
                        }
                        break;
                    case 'billing_name':
                        $row[] = $product->billing_first_name . ' ' . $product->billing_last_name;
                        break;
                    case 'billing_phone':
                        $row[] = $product->billing_phone;
                        break;
                    case 'billing_email':
                        $row[] = $product->billing_email;
                        break;
                    case 'billing_address':
                        $addressComponents = array();
                        if (!empty($product->billing_address_1))
                            $addressComponents[] = $product->billing_address_1;
                        if (!empty($product->billing_address_2))
                            $addressComponents[] = $product->billing_address_2;
                        if (!empty($product->billing_city))
                            $addressComponents[] = $product->billing_city;
                        if (!empty($product->billing_state))
                            $addressComponents[] = $product->billing_state;
                        if (!empty($product->billing_postcode))
                            $addressComponents[] = $product->billing_postcode;
                        if (!empty($product->billing_country))
                            $addressComponents[] = $product->billing_country;
                        $row[] = implode(', ', $addressComponents);
                        break;
                    case 'shipping_name':
                        $row[] = $product->shipping_first_name . ' ' . $product->shipping_last_name;
                        break;
                    case 'shipping_phone':
                        $row[] = $product->shipping_phone;
                        break;
                    case 'shipping_email':
                        $row[] = $product->shipping_email;
                        break;
                    case 'shipping_address':
                        $addressComponents = array();
                        if (!empty($product->shipping_address_1))
                            $addressComponents[] = $product->shipping_address_1;
                        if (!empty($product->shipping_address_2))
                            $addressComponents[] = $product->shipping_address_2;
                        if (!empty($product->shipping_city))
                            $addressComponents[] = $product->shipping_city;
                        if (!empty($product->shipping_state))
                            $addressComponents[] = $product->shipping_state;
                        if (!empty($product->shipping_postcode))
                            $addressComponents[] = $product->shipping_postcode;
                        if (!empty($product->shipping_country))
                            $addressComponents[] = $product->shipping_country;
                        $row[] = implode(', ', $addressComponents);
                        break;
                    case 'quantity':
                        $row[] = $product->quantity;
                        break;
                    case 'line_subtotal':
                        $row[] = $product->line_subtotal;
                        break;
                    case 'line_total':
                        $row[] = $product->line_total;
                        break;
                }
            }
            if ($return)
                $rows[] = $row;
            else
                fputcsv($dest, $row);
        }
    }
    if ($return)
        return $rows;
}

add_action('admin_enqueue_scripts', 'hm_xoiwc_admin_enqueue_scripts');

function hm_xoiwc_admin_enqueue_scripts()
{
    wp_enqueue_style('hm_xoiwc_admin_style', plugins_url('css/export-order-items.css', __FILE__));
    wp_enqueue_style('pikaday', plugins_url('css/pikaday.css', __FILE__));
    wp_enqueue_script('moment', plugins_url('js/moment.min.js', __FILE__));
    wp_enqueue_script('pikaday', plugins_url('js/pikaday.js', __FILE__));
}

// Schedulable email report hook
add_filter('pp_wc_get_schedulable_email_reports', 'hm_xoiwc_add_schedulable_email_reports');

function hm_xoiwc_add_schedulable_email_reports($reports)
{
    $reports['hm_xoiwc'] = array(
        'name' => 'Export Gift Card Order Items',
        'callback' => 'hm_xoiwc_run_scheduled_report',
        'reports' => array(
            'last' => 'Last used settings'
        )
    );
    return $reports;
}

function hm_xoiwc_run_scheduled_report($reportId, $start, $end, $args = array(), $output = false)
{
    $savedReportSettings = get_option('hm_xoiwc_report_settings');
    if (!isset($savedReportSettings[0]))
        return false;
    $prevPost = $_POST;
    $_POST = $savedReportSettings[0];
    $_POST['report_time'] = 'custom';
    $_POST['report_start'] = date('Y-m-d', $start);
    $_POST['report_end'] = date('Y-m-d', $end);
    $_POST = array_merge($_POST, array_intersect_key($args, $_POST));

    if ($output) {
        echo('<table><thead><tr>');
        foreach (hm_xoiwc_export_header(null, true) as $heading) {
            echo("<th>$heading</th>");
        }
        echo('</tr></thead><tbody>');
        foreach (hm_xoiwc_export_body(null, true) as $row) {
            echo('<tr>');
            foreach ($row as $cell)
                echo('<td>' . htmlspecialchars($cell) . '</td>');
            echo('</tr>');
        }
        echo('</tbody></table>');
        $_POST = $prevPost;
        return;
    }

    $filename = get_temp_dir() . '/Order Items Export.csv';
    $out = fopen($filename, 'w');
    if (!empty($_POST['include_header']))
        hm_xoiwc_export_header($out);
    hm_xoiwc_export_body($out);
    fclose($out);

    $_POST = $prevPost;

    return $filename;
}

?>
