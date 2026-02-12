<?php
/**
 * Layout and content tweaks for WooCommerce single product pages
 */

/**
 * Change breadcrumb delimiter and place at top of page 
 */
function astra_child_woocommerce_breadcrumb_defaults( $defaults ) {
    $defaults['delimiter'] = ' <span class="breadcrumb-separator">&gt;</span> ';

    return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'astra_child_woocommerce_breadcrumb_defaults' );

function astra_child_single_product_breadcrumbs() {
    if ( function_exists( 'woocommerce_breadcrumb' ) && is_product() ) {
        echo '<div class="custom-product-breadcrumbs">';
        woocommerce_breadcrumb();
        echo '</div>';
    }
}
add_action( 'woocommerce_before_single_product', 'astra_child_single_product_breadcrumbs', 5 );

/**
 * Create two-column PDP layout
 */
function astra_child_product_layout_start() {
    if ( ! is_product() ) {
        return;
    }

    echo '<div class="custom-product-layout"><div class="custom-product-layout__images">';
}
add_action( 'woocommerce_before_single_product_summary', 'astra_child_product_layout_start', 5 );

function astra_child_product_layout_after_images() {
    if ( ! is_product() ) {
        return;
    }

    echo '</div><div class="custom-product-layout__summary">';
}
add_action( 'woocommerce_before_single_product_summary', 'astra_child_product_layout_after_images', 25 );

function astra_child_product_layout_end() {
    if ( ! is_product() ) {
        return;
    }

    echo '</div></div>';
}
add_action( 'woocommerce_single_product_summary', 'astra_child_product_layout_end', 65 );

/**
 * Add product type, separator image and in stock indicator below product title
 */
function astra_child_product_type_heading() {
    if ( ! is_product() ) {
        return;
    }

    global $product;

    if ( ! $product instanceof WC_Product ) {
        return;
    }

    $type_value = $product->get_attribute( 'pa_type' );

    if ( ! $type_value ) {
        $type_value = $product->get_attribute( 'type' );
    }

    if ( ! $type_value ) {
        return;
    }

    $arrow_url      = get_stylesheet_directory_uri() . '/assets/arrow.jpg';
    $stock_icon_url = get_stylesheet_directory_uri() . '/assets/ico-in-stock.png';
    $in_stock       = $product->is_in_stock();
    $stock_text     = $in_stock ? 'In Stock' : 'Out Of Stock';

    echo '<h2 class="product-type-heading">' . esc_html( $type_value ) . '</h2>';
    echo '<img class="product-type-separator" src="' . esc_url( $arrow_url ) . '" alt="" />';
    echo '<div class="product-stock-indicator">';
    echo '<img class="product-stock-indicator__icon" src="' . esc_url( $stock_icon_url ) . '" alt="" />';
    echo '<span class="product-stock-indicator__text">' . esc_html( $stock_text ) . '</span>';
    echo '</div>';
}
add_action( 'astra_woo_single_title_after', 'astra_child_product_type_heading', 10 );

/**
 * Add Item No before short description
 */
function astra_child_product_sku_line() {
    if ( ! is_product() ) {
        return;
    }

    global $product;

    if ( ! $product instanceof WC_Product ) {
        return;
    }

    $sku = $product->get_sku();

    if ( ! $sku ) {
        return;
    }

    echo '<p class="product-item-number">Item No: ' . esc_html( $sku ) . '</p>';
}
add_action( 'astra_woo_single_short_description_before', 'astra_child_product_sku_line', 10 );

/**
 * Add collapsible details objects for description, dimensions and lookbooks after the short description
 * Only loads if each is populated for each product
 */
function astra_child_product_details_panels() {
    if ( ! is_product() ) {
        return;
    }

    global $product;

    if ( ! $product instanceof WC_Product ) {
        return;
    }

    $description = $product->get_description();
    $dimensions  = $product->get_attribute( 'pa_dimensions' );
    $lookbooks   = $product->get_attribute( 'pa_lookbooks' );

    if ( ! $dimensions ) {
        $dimensions = $product->get_attribute( 'dimensions' );
    }

    if ( ! $lookbooks ) {
        $lookbooks = $product->get_attribute( 'lookbooks' );
    }

    if ( $description ) {
        echo '<details class="product-details product-details--description">';
        echo '<summary>Details</summary>';
        echo  '<div class="accordion-inner">' . wp_kses_post( wpautop( $description ) ) . '</div>';
        echo '</details>';
    }

    if ( $dimensions ) {
        echo '<details class="product-details product-details--dimensions">';
        echo '<summary>Dimensions</summary>';
        echo '<div class="accordion-inner"><p>' . esc_html( $dimensions ) . '</p></div>';
        echo '</details>';
    }

    if ( $lookbooks ) {
        echo '<details class="product-details product-details--lookbooks">';
        echo '<summary>Lookbooks</summary>';
        echo '<div class="accordion-inner">' . wp_kses_post( wpautop( $lookbooks ) ) . '</div>';
        echo '</details>';
    }
}
add_action( 'astra_woo_single_short_description_after', 'astra_child_product_details_panels', 10 );

/**
 * Remove un-needed WooCommerce tabs 
 */
function astra_child_remove_product_tabs() {
    if ( ! is_product() ) {
        return;
    }

    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
}
add_action( 'wp', 'astra_child_remove_product_tabs', 20 );
