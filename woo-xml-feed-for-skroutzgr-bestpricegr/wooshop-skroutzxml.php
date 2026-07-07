<?php

/* Plugin Name: Skroutz & Bestprice XML feed for WooCommerce
  Plugin URI: https://www.papaki.com
  Description: XML feed creator for Skroutz & BestPrice
  Version: 1.7.0.0
  Author: Papaki
  Author URI: https://www.papaki.com
  License: GPLv3 or later
  WC tested up to: 10.2.2
*/

/*
 Based on original plugin "Skroutz.gr & Bestprice.gr XML Feed for Woocommerce By emspace.gr" [https://wordpress.org/plugins/woo-xml-feed-skroutz-bestprice/]
 */

namespace Papaki\SkroutzBestPriceXMLFeed;

use Papaki\SkroutzBestPriceXMLFeed\Admin\Admin;
use Papaki\SkroutzBestPriceXMLFeed\Cron\Cron;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

spl_autoload_register( function ( $class ) {
    $prefix   = 'Papaki\\SkroutzBestPriceXMLFeed';
    $base_dir = plugin_dir_path( __FILE__ ) . 'includes/';

    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, $len );
    $file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

    if ( file_exists( $file ) ) {
        require $file;
    }
} );

Plugin::bootstrap( __FILE__, [ 'name' => 'Skroutz & Bestprice', 'textdomain' => 'skroutz-woocommerce-feed' ] );
Settings::get_instance();
Admin::get_instance();
Cron::get_instance();
