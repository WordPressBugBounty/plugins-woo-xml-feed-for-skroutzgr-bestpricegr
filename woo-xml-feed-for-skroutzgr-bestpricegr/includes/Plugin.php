<?php

namespace Papaki\SkroutzBestPriceXMLFeed;
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use RuntimeException;

class Plugin {
    private static ?self $instance = null;

    private string $file;
    private string $path;
    private string $url;
    private string $basename;
    private string $textdomain;
    private string $key;
    private string $name;

    private function __construct( array $args ) {
        $this->file       = $args['file'];
        $this->path       = $args['path'];
        $this->url        = $args['url'];
        $this->basename   = $args['basename'];
        $this->textdomain = $args['textdomain'];
        $this->key        = $args['key'];
        $this->name       = $args['name'];

        add_action( 'init', [ $this, 'languages' ] );
        add_action( 'before_woocommerce_init', [ $this, 'declare_woocommerce_compatibility' ] );
    }

    /**
     * Declare compatibility with WooCommerce High-Performance Order Storage (HPOS).
     *
     * @return void
     */
    public function declare_woocommerce_compatibility(): void {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->file, true );
        }
    }

    public static function bootstrap( string $file, array $args ): void {
        $args['file'] = $file;
        $args['path'] = untrailingslashit( plugin_dir_path( $file ) );
        $args['url']  = untrailingslashit( plugin_dir_url( $file ) );

        $args['basename']   = $args['basename'] ?? wp_basename( $args['path'] );
        $args['textdomain'] = $args['textdomain'] ?? wp_basename( $args['path'] );
        $args['key']        = sanitize_key( $args['name'] ?? $args['basename'] );
        $args['name']       = ucfirst( trim( $args['name'] ?? $args['basename'] ) );

        self::$instance = new self( $args );
    }

    public function languages(): void {
        load_plugin_textdomain( $this->textdomain, false, $this->basename . '/languages' );
    }

    /**
     * Return the instance of the plugin.
     *
     * @return Plugin
     */
    public static function get_instance(): self {
        // If the instance is null, throw an exception.
        if ( ! self::$instance ) {
            throw new RuntimeException( 'Plugin not initialized.' );
        }

        // Return the instance.
        return self::$instance;
    }

    /**
     * Return the file of the plugin.
     *
     * @return string
     */
    public static function file(): string {
        return self::get_instance()->file;
    }

    /**
     * Return the basename of the plugin.
     *
     * @return string
     */
    public static function basename(): string {
        return self::get_instance()->basename;
    }

    /**
     * Return the textdomain of the plugin.
     *
     * @return string
     */
    public static function textdomain(): string {
        return self::get_instance()->textdomain;
    }

    /**
     * Return the key of the plugin.
     *
     * @return string
     */
    public static function key(): string {
        return self::get_instance()->key;
    }

    /**
     * Return the name of the plugin.
     *
     * @return string
     */
    public static function name(): string {
        return self::get_instance()->name;
    }

    /**
     * Return the path of the plugin.
     *
     * @param string $append The string to append to the path.
     *
     * @return string
     */
    public static function path( string $append = '' ): string {
        return trailingslashit( self::get_instance()->path ) . $append;
    }

    /**
     * Return the URL of the plugin.
     *
     * @param string|null $append The string to append to the URL.
     *
     * @return string
     */
    public static function url( ?string $append = null ): string {
        return trailingslashit( self::get_instance()->url ) . $append;
    }
}
