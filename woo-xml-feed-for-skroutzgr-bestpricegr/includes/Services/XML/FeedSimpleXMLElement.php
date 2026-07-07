<?php

namespace Papaki\SkroutzBestPriceXMLFeed\Services\XML;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Exit if accessed directly
class FeedSimpleXMLElement extends \SimpleXMLElement {
    public function addCData( $cdata_text ) {
        $node = dom_import_simplexml( $this );
        $no   = $node->ownerDocument;
        $node->appendChild( $no->createCDATASection( $cdata_text ) );
    }
}
