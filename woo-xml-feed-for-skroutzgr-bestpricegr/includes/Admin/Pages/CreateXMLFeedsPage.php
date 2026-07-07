<?php

namespace Papaki\SkroutzBestPriceXMLFeed\Admin\Pages;

use Papaki\SkroutzBestPriceXMLFeed\Services\FeedGenerator;
use Papaki\SkroutzBestPriceXMLFeed\Traits\Singleton;
use Papaki\SkroutzBestPriceXMLFeed\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CreateXMLFeedsPage {
    use Singleton;

    public function init(): void {
        add_action( 'admin_menu', [ $this, 'register' ] );
    }

    public function register(): void {
        add_submenu_page(
            Plugin::key(),
            __( 'Create XML Feeds', Plugin::textdomain() ),
            __( 'Create XML Feeds', Plugin::textdomain() ),
            'manage_options',
            Plugin::key() . '_xml_create_page',
            [ $this, 'render' ],
        );
    }

    public function render(): void {
        echo '<div>';
        echo '<h2>' . __( 'Create Feeds for Skroutz and Bestprice', Plugin::textdomain() ) . '</h2>';
        echo '</div>';

        settings_fields( 'skroutz-group' );
        do_settings_sections( 'skroutz-group' );

        $generator = new FeedGenerator();
        $generator->generate();
    }
}
