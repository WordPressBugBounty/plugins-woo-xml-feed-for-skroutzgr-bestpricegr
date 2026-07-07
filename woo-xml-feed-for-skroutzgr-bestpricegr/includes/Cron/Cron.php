<?php

namespace Papaki\SkroutzBestPriceXMLFeed\Cron;

use Papaki\SkroutzBestPriceXMLFeed\Plugin;
use Papaki\SkroutzBestPriceXMLFeed\Traits\Singleton;
use Papaki\SkroutzBestPriceXMLFeed\Cron\Crons\GenerateXMLFeedsCron;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Cron {
    use Singleton;

    public function init(): void {
        GenerateXMLFeedsCron::register();

        // Self-heal scheduling across updates: register_activation_hook() only fires on
        // (re)activation, not on an in-place plugin update, so the renamed event would
        // otherwise never be scheduled. schedule() is idempotent (guards with
        // wp_next_scheduled()), so re-running it on every load is safe.
        add_action( 'init', [ GenerateXMLFeedsCron::class, 'schedule' ] );

        register_activation_hook( Plugin::file(), [ GenerateXMLFeedsCron::class, 'schedule' ] );
        register_deactivation_hook( Plugin::file(), [ GenerateXMLFeedsCron::class, 'unschedule' ] );
    }
}
