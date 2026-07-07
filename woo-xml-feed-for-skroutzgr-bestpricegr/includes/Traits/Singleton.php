<?php

namespace Papaki\SkroutzBestPriceXMLFeed\Traits;

trait Singleton {
    protected static $instance;

    final public static function get_instance() {
        if ( null === static::$instance ) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    final public function __construct() {
        $this->init();

        if ( method_exists( static::class, 'after' ) ) {
            $this->after();
        }
    }

    protected function init(): void {
    }
}
