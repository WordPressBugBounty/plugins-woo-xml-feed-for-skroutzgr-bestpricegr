<?php

namespace Papaki\SkroutzBestPriceXMLFeed\Cron\Crons;

use Papaki\SkroutzBestPriceXMLFeed\Plugin;
use Papaki\SkroutzBestPriceXMLFeed\Services\FeedGenerator;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class GenerateXMLFeedsCron {
    protected static string $name = 'generate_xml_feed_event';
    protected static string $recurrence = 'hourly';

    public static function handle(): void {
        $generator = new FeedGenerator();
        $generator->generate();
    }

    public static function register(): void {
        $prefix = static::get_prefix();
        add_action( $prefix . static::$name, [ static::class, 'handle' ] );
    }

    public static function schedule(): void {
        $prefix = static::get_prefix();

        if ( ! wp_next_scheduled( $prefix . static::$name ) ) {
            wp_schedule_event( time(), static::$recurrence, $prefix . static::$name );
        }
    }

    public static function unschedule(): void {
        wp_clear_scheduled_hook( static::get_prefix() . static::$name );
    }

    protected static function get_prefix(): string {
        return Plugin::key() . '_';
    }
}
