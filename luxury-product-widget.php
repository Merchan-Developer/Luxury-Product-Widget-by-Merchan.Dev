<?php
/*
Plugin Name: luxury-product-widget
Plugin URI: https://arturomerchan.com/
Description: Un widget de productos elegante y moderno para Elementor, compatible con WooCommerce. Integración mediante shortcode.
Version: 1.0
Author: Arturo Merchan | Merchan.Devb
Author URI: https://arturomerchan.com/
Text Domain: luxury-product-widget
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Register Widget
function register_luxury_product_widget() {
    if ( did_action( 'elementor/loaded' ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register( new \Elementor_Luxury_Product_Widget() );
    }
}
add_action( 'elementor/widgets/widgets_registered', 'register_luxury_product_widget' );

// Enqueue Styles and Scripts
function luxury_product_widget_assets() {
    wp_enqueue_style( 'luxury-product-widget-style', plugins_url( 'style.css', __FILE__ ) );
    wp_enqueue_script( 'luxury-product-widget-script', plugins_url( 'script.js', __FILE__ ), array('jquery'), false, true );
    wp_enqueue_script( 'magnific-popup', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js', array('jquery'), '1.1.0', true );
    wp_enqueue_style( 'magnific-popup-style', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css' );
    wp_enqueue_script( 'clipboard-js', 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js', array(), '2.0.6', true );
}
add_action( 'wp_enqueue_scripts', 'luxury_product_widget_assets' );

// Shortcode to Display Products
function luxury_product_widget_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'category' => '',
        'limit' => 4,
    ), $atts, 'luxury_product_widget' );

    ob_start();

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $atts['limit'],
        'product_cat' => $atts['category'],
        'post_status' => 'publish',
    );

    $loop = new WP_Query( $args );

    if ( $loop->have_posts() ) {
        echo '<div class="luxury-products">';
        while ( $loop->have_posts() ) {
            $loop->the_post();
            global $product;

            echo '<div class="luxury-product" data-product-id="' . esc_attr( $product->get_id() ) . '">';
            if ( $product->is_on_sale() ) {
                echo '<div class="sale-banner">' . esc_html__( 'Sale!', 'luxury-product-widget' ) . '</div>';
            }
            echo '<a href="#product-popup-' . esc_attr( $product->get_id() ) . '" class="open-popup-link">';
            echo get_the_post_thumbnail( $product->get_id(), 'medium' );
            echo '<h2 class="product-title">' . get_the_title() . '</h2>';
            echo '<div class="product-price">' . $product->get_price_html() . '</div>';
            echo '</a>';
            echo '</div>';

            // Popup content
            echo '<div id="product-popup-' . esc_attr( $product->get_id() ) . '" class="mfp-hide">';
            echo '<div class="product-popup-content">';
            echo '<img src="' . get_the_post_thumbnail_url( $product->get_id(), 'large' ) . '" alt="' . get_the_title() . '">';
            echo '<div class="product-popup-details">';
            echo '<h2 class="product-title">' . get_the_title() . '</h2>';
            echo '<div class="product-price">';
            if ( $product->is_on_sale() ) {
                echo '<del>' . wc_price( $product->get_regular_price() ) . '</del>';
                echo ' ' . wc_price( $product->get_sale_price() );
            } else {
                echo wc_price( $product->get_price() );
            }
            echo '</div>';
            echo '<div class="product-description">' . apply_filters( 'woocommerce_short_description', $post->post_excerpt ) . '</div>';
            echo '<div class="product-full-description">' . apply_filters( 'the_content', get_the_content() ) . '</div>';
            echo '<a href="' . esc_url( $product->add_to_cart_url() ) . '" class="button add-to-cart-button">' . esc_html__( 'Add to Cart', 'luxury-product-widget' ) . '</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>' . esc_html__( 'No products found', 'luxury-product-widget' ) . '</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'luxury_product_widget', 'luxury_product_widget_shortcode' );

// Add admin menu item
function luxury_product_widget_menu() {
    add_menu_page(
        __( 'Luxury Product Widget', 'luxury-product-widget' ),
        __( 'Luxury Products', 'luxury-product-widget' ),
        'manage_options',
        'luxury-product-widget',
        'luxury_product_widget_settings_page',
        'dashicons-products',
        20
    );
}
add_action( 'admin_menu', 'luxury_product_widget_menu' );

// Settings page content
function luxury_product_widget_settings_page() {
    if ( isset( $_POST['lpw_category'] ) && wp_verify_nonce( $_POST['lpw_nonce'], 'lpw_save_category' ) ) {
        $category = sanitize_text_field( $_POST['lpw_category'] );
        $shortcode = '[luxury_product_widget category="' . $category . '" limit="4"]';
        $saved_shortcodes = get_option( 'lpw_shortcodes', array() );
        $saved_shortcodes[] = array(
            'category' => $category,
            'shortcode' => $shortcode,
            'date' => current_time( 'mysql' ),
        );
        update_option( 'lpw_shortcodes', $saved_shortcodes );
    }

    if ( isset( $_GET['delete_shortcode'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'lpw_delete_shortcode_' . $_GET['delete_shortcode'] ) ) {
        $index = intval( $_GET['delete_shortcode'] );
        $saved_shortcodes = get_option( 'lpw_shortcodes', array() );
        if ( isset( $saved_shortcodes[$index] ) ) {
            unset( $saved_shortcodes[$index] );
            $saved_shortcodes = array_values( $saved_shortcodes );
            update_option( 'lpw_shortcodes', $saved_shortcodes );
        }
    }

    $saved_shortcodes = get_option( 'lpw_shortcodes', array() );

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Luxury Product Widget', 'luxury-product-widget' ); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'lpw_save_category', 'lpw_nonce' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Product Category', 'luxury-product-widget' ); ?></th>
                    <td><input type="text" name="lpw_category" value="" /></td>
                </tr>
            </table>
            <?php submit_button( __( 'Generate Shortcode', 'luxury-product-widget' ) ); ?>
        </form>

        <h2><?php esc_html_e( 'Generated Shortcodes', 'luxury-product-widget' ); ?></h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Category Name', 'luxury-product-widget' ); ?></th>
                    <th><?php esc_html_e( 'Shortcode', 'luxury-product-widget' ); ?></th>
                    <th><?php esc_html_e( 'Date Created', 'luxury-product-widget' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'luxury-product-widget' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $saved_shortcodes as $index => $shortcode_data ) : ?>
                    <tr>
                        <td><?php echo esc_html( $shortcode_data['category'] ); ?></td>
                        <td>
                            <input type="text" value="<?php echo esc_attr( $shortcode_data['shortcode'] ); ?>" readonly />
                            <button class="button copy-button" data-clipboard-text="<?php echo esc_attr( $shortcode_data['shortcode'] ); ?>"><?php esc_html_e( 'Copy', 'luxury-product-widget' ); ?></button>
                        </td>
                        <td><?php echo esc_html( $shortcode_data['date'] ); ?></td>
                        <td>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=luxury-product-widget&delete_shortcode=' . $index ), 'lpw_delete_shortcode_' . $index ); ?>" class="button"><span class="dashicons dashicons-trash"></span></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        jQuery(document).ready(function($) {
            var clipboard = new ClipboardJS('.copy-button');

            clipboard.on('success', function(e) {
                alert('<?php esc_html_e( 'Shortcode copied to clipboard!', 'luxury-product-widget' ); ?>');
                e.clearSelection();
            });

            clipboard.on('error', function(e) {
                alert('<?php esc_html_e( 'Failed to copy shortcode', 'luxury-product-widget' ); ?>');
            });
        });
    </script>
    <?php
}
?>

// Añadir créditos al final de la página del plugin
function luxury_product_widget_admin_footer() {
    ?>
    <div style="text-align: right; margin-top: 20px;">
        <p><?php esc_html_e( 'Desarrollado por', 'luxury-product-widget' ); ?> <a href="https://arturomerchan.com/" target="_blank" rel="noopener noreferrer">Merchan.Dev® 2025</a></p>
    </div>
    <?php
}
add_action( 'admin_footer', 'luxury_product_widget_admin_footer' );
