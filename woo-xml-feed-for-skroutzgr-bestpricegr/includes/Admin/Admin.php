<?php

namespace Papaki\SkroutzBestPriceXMLFeed\Admin;

use Papaki\SkroutzBestPriceXMLFeed\Admin\Pages\CreateXMLFeedsPage;
use Papaki\SkroutzBestPriceXMLFeed\Admin\Pages\PluginMainPage;
use Papaki\SkroutzBestPriceXMLFeed\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Papaki\SkroutzBestPriceXMLFeed\Traits\Singleton;

class Admin {
    use Singleton;

    protected function init(): void {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
        add_action( 'admin_head', [ $this, 'select2jquery_inline' ] );

        PluginMainPage::get_instance();
        CreateXMLFeedsPage::get_instance();
    }

    /**
     * Add the plugin to the admin menu.
     *
     * @return void
     */
    public function admin_menu(): void {
        add_menu_page(
            Plugin::name(),
            Plugin::name(),
            'manage_options',
            Plugin::key(),
            null,
            Plugin::url( 'images/xml-icon.png' ),
            3,
        );
    }

    public function enqueue_admin_scripts( $hook ): void {
        if ( strpos( $hook, Plugin::key() ) === false ) {
            return;
        }

        wp_register_script( 'select2', "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js", null, false );
        wp_register_style( 'select2', "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css", null, false );

        wp_enqueue_style( 'select2' );
        wp_enqueue_script( 'select2' );
    }

    public function select2jquery_inline(): void {
        // Select2 is enqueued only on this plugin's admin pages (see enqueue_admin_scripts()).
        // Mirror that gate here so the inline .select2() call never runs on a screen without
        // the library, where it throws "select2 is not a function".
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, Plugin::key() ) === false ) {
            return;
        }
        ?>
        <style type="text/css">
            #main {
                height: 100%;
            }

            .autocomplete {
                width: 350px;
            }

            .gtin select {
                width: 150px;
            }

            .tablenav.top #doaction,
            #doaction2,
            #post-query-submit {
                margin: 0px 4px 0 4px;
            }

            .select2-search.select2-search--inline {}

        </style>
        <script type='text/javascript'>

            jQuery(function ($) {
                $('.skroutz_bestprice.form-table select').select2();

            });

        </script>
        <?php
    }
}
