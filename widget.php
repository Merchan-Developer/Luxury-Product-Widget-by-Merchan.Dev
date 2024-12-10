<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Elementor_Luxury_Product_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'luxury_product';
    }

    public function get_title() {
        return __( 'Luxury Product', 'luxury-product-widget' );
    }

    public function get_icon() {
        return 'eicon-woocommerce';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'luxury-product-widget' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'category',
            [
                'label' => __( 'Category', 'luxury-product-widget' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
            ]
        );

        $this->add_control(
            'limit',
            [
                'label' => __( 'Number of Products', 'luxury-product-widget' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        echo do_shortcode('[luxury_product_widget category="' . esc_attr( $settings['category'] ) . '" limit="' . esc_attr( $settings['limit'] ) . '"]');
    }

    protected function _content_template() {
        ?>
        <#
        var shortcode = '[luxury_product_widget category="' + settings.category + '" limit="' + settings.limit + '"]';
        print(shortcode);
        #>
        <?php
    }

}
?>

/*Arturo Merchan | Merchan.Dev® 2025*/