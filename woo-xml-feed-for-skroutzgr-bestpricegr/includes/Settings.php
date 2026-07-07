<?php

namespace Papaki\SkroutzBestPriceXMLFeed;

use Papaki\SkroutzBestPriceXMLFeed\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Settings {
    use Singleton;

    public function init(): void {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_settings(): void {
        register_setting( 'skroutz-group', 'instockavailability', [ 'sanitize_callback' => [ $this, 'sanitize_options' ] ] );
        register_setting( 'skroutz-group', 'ifoutofstock', [ 'sanitize_callback' => [ $this, 'sanitize_options' ] ] );
        register_setting( 'skroutz-group', 'include_tax' );
        register_setting( 'skroutz-group', 'group_variations' );
        register_setting( 'skroutz-group', 'features', [ 'sanitize_callback' => [ $this, 'sanitize_options_multi' ] ] );
        register_setting( 'skroutz-group', 'skroutz_atts_color', [ 'sanitize_callback' => [ $this, 'sanitize_options' ] ] );
        register_setting( 'skroutz-group', 'skroutz_atts_manuf', [ 'sanitize_callback' => [ $this, 'sanitize_options' ] ] );
        register_setting( 'skroutz-group', 'skroutz_atts_size', [ 'sanitize_callback' => [ $this, 'sanitize_options' ] ] );
        register_setting( 'skroutz-group', 'enable_gtin' );
        register_setting( 'skroutz-group', 'gtin_label', [ 'sanitize_callback' => [ $this, 'sanitize_options' ] ] );
        register_setting( 'skroutz-group', 'gtin_value', [ 'sanitize_callback' => [ $this, 'sanitize_options' ] ] );
        register_setting( 'skroutz-group', 'exclude_cats', [ 'sanitize_callback' => [ $this, 'sanitize_options_multi' ] ] );
        register_setting( 'skroutz-group', 'custom_productId', [ 'sanitize_callback' => [ $this, 'sanitize_options' ] ] );
        register_setting( 'skroutz-group', 'custom_mpn', [ 'sanitize_callback' => [ $this, 'sanitize_options' ] ] );
        register_setting( 'skroutz-group', 'last_update', [ 'sanitize_callback' => [ $this, 'sanitize_options' ] ] );
    }

    public function sanitize_options( $input ) {
        return sanitize_text_field( $input );
    }

    public function sanitize_options_multi( $input ) {
        if ( is_array( $input ) ) {
            return array_map( 'sanitize_text_field', $input );
        }

        return [];
    }
}
