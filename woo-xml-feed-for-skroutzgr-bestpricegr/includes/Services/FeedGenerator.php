<?php

namespace Papaki\SkroutzBestPriceXMLFeed\Services;

use Papaki\SkroutzBestPriceXMLFeed\Plugin;
use Papaki\SkroutzBestPriceXMLFeed\Services\XML\FeedSimpleXMLElement;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class FeedGenerator {
    public function generate(): array {
        //********* Start of initialization of xml files *****************/

        /*******************************
         ******** BESTPRICE.GR *********
         ******************************/
        if ( ! file_exists( wp_upload_dir()['basedir'] . '/best-price' ) ) {
            wp_mkdir_p( wp_upload_dir()['basedir'] . '/best-price' );
        }
        if ( ! file_exists( wp_upload_dir()['basedir'] . '/best-price/bp.xml' ) ) {
            touch( wp_upload_dir()['basedir'] . '/best-price/bp.xml' );
        }
        if ( file_exists( wp_upload_dir()['basedir'] . '/best-price/bp.xml' ) ) {
            $xmlFileBestprice = wp_upload_dir()['basedir'] . '/best-price/bp.xml';
        } else {
            echo "Could not create Bestprice file.";
        }

        $now = date( 'Y-n-j G:i' );

        $xmlBestprice = new FeedSimpleXMLElement( '<?xml version="1.0" encoding="utf-8"?><webstore/>' );
        $xmlBestprice->addChild( 'date', "$now" );
        $productsBestprice = $xmlBestprice->addChild( 'products' );
        $featureslist      = get_option( 'features', [] );

        /*******************************
         ******** SKROUTZ.GR *********
         ******************************/

        if ( ! file_exists( wp_upload_dir()['basedir'] . '/skroutz' ) ) {
            wp_mkdir_p( wp_upload_dir()['basedir'] . '/skroutz' );
        }

        if ( ! file_exists( wp_upload_dir()['basedir'] . '/skroutz/skroutz.xml' ) ) {
            touch( wp_upload_dir()['basedir'] . '/skroutz/skroutz.xml' );
        }

        if ( file_exists( wp_upload_dir()['basedir'] . '/skroutz/skroutz.xml' ) ) {
            $xmlFileSkroutz = wp_upload_dir()['basedir'] . '/skroutz/skroutz.xml';
        } else {
            echo "Could not create skroutz file.";
        }

        $xmlSkroutz = new FeedSimpleXMLElement( '<?xml version="1.0" encoding="utf-8"?><webstore/>' );
        $xmlSkroutz->addChild( 'created_at', "$now" );
        $productsSkroutz = $xmlSkroutz->addChild( 'products' );


        //********* End of initialization of xml files *****************/

        $xml_rows            = array();
        $instockavailability = get_option( 'instockavailability' );
        $avaibilities        = array(
            __( 'Available in store / Delivery 1 to 3 days', 'skroutz-woocommerce-feed' ),
            __( 'Delivery 1 to 3 days', 'skroutz-woocommerce-feed' ),
            __( 'Delivery 4 to 10 days', 'skroutz-woocommerce-feed' ),
            __( 'attribute', 'skroutz-woocommerce-feed' ),
        );

        $availabilityST           = $avaibilities[ $instockavailability ];
        $ifoutofstock             = get_option( 'ifoutofstock' );
        $availabilitiesOutOfStock = array(
            __( 'Include as out of Stock or Upon Request', 'skroutz-woocommerce-feed' ),
            __( 'Exclude from feed', 'skroutz-woocommerce-feed' ),
            __( 'Delivery 1 to 3 days', 'skroutz-woocommerce-feed' ),
            __( 'Delivery 4 to 10 days', 'skroutz-woocommerce-feed' ),
            __( 'Attribute: Out of Stock Availability', 'skroutz-woocommerce-feed' ),
        );
        $noavailabilityST         = $availabilitiesOutOfStock[ $ifoutofstock ];

        $format_price = false;
        if ( function_exists( 'wc_get_price_decimal_separator' ) && function_exists( 'wc_get_price_thousand_separator' ) && function_exists( 'wc_get_price_decimals' ) ) {
            $decimal_separator  = wc_get_price_decimal_separator();
            $thousand_separator = wc_get_price_thousand_separator();
            $decimals           = wc_get_price_decimals();
            $format_price       = true;
        }

        $skroutz_atts_color = get_option( 'skroutz_atts_color', 'pa_color' );
        $skroutz_atts_size  = get_option( 'skroutz_atts_size', 'pa_size' );
        $skroutz_atts_manuf = get_option( 'skroutz_atts_manuf', 'brand' );
        $enable_gtin        = get_option( 'enable_gtin', false );
        $include_tax        = get_option( 'include_tax', false );
        $group_variations   = get_option( 'group_variations', false );
        $gtin_label         = get_option( 'gtin_label', 'ean' );
        $gtin_value         = get_option( 'gtin_value', '' );
        $cats_excluded      = get_option( 'exclude_cats', [] );
        $custom_productId   = get_option( 'custom_productId' );
        $custom_mpn         = get_option( 'custom_mpn' );

        $i = 1;
        try {
            do {
                $query = new \WC_Product_Query(
                    array(
                        'status'   => array( 'publish' ),
                        'limit'    => 300,
                        'paginate' => true,
                        'page'     => $i,
                    ) );
                if ( count( $cats_excluded ) > 0 ) {
                    $query->set( 'tax_query', array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field'    => 'term_id',
                            'terms'    => $cats_excluded,
                            'operator' => ( 'NOT IN' ),
                        ),
                    ) );
                }

                $result         = $query->get_products();
                $color_term_ids = array();
                foreach ($result->products as $index => $prod) {
                    $availabilityST       = $avaibilities[ $instockavailability ];
                    $noavailabilityST     = $availabilitiesOutOfStock[ $ifoutofstock ];
                    $available_variations = array();
                    $attributes           = $prod->get_attributes();
                    $group_colors         = false;
                    $variable_products    = [];

                    $onfeed = $prod->get_meta( 'onfeed' );
                    if ( strcmp( strtolower( $onfeed ), "no" ) == 0 ) {
                        continue;
                    }

                    if ( $prod->get_type() == 'variable' ) {
                        $available_variations = $prod->get_available_variations();
                        $variation_prices     = $prod->get_variation_prices();
                        if ( isset( $attributes[ $skroutz_atts_color ] ) && ! empty( $attributes[ $skroutz_atts_color ] ) ) {
                            $group_colors = count( $attributes[ $skroutz_atts_color ]['data']['options'] ) >= 1 ? true : false;
                        }
                    }

                    $variation_atts = array( $skroutz_atts_color => array(), $skroutz_atts_size => array() );

                    foreach ($available_variations as $var) {
                        $var_product      = wc_get_product( $var['variation_id'] );
                        $var_stock_status = $var_product->get_stock_status();
                        if ( isset( $var_stock_status ) && $var_stock_status == 'outofstock' ) {
                            continue;
                        }
                        // old one - legacy
                        if ( isset( $var['stock_status'] ) && $var['stock_status'] == 'outofstock' ) {
                            continue;
                        }

                        $atts = $var['attributes'];

                        if ( isset( $atts[ 'attribute_' . $skroutz_atts_size ] ) && $atts[ 'attribute_' . $skroutz_atts_size ] != '' ) {
                            $variation_atts[ $skroutz_atts_size ][] = $atts[ 'attribute_' . $skroutz_atts_size ];
                        }

                        if ( isset( $atts[ 'attribute_' . $skroutz_atts_color ] ) && $atts[ 'attribute_' . $skroutz_atts_color ] != '' ) {
                            $variation_atts[ $skroutz_atts_color ][] = $atts[ 'attribute_' . $skroutz_atts_color ];
                        }
                        if ( $group_variations && $group_colors ) {
                            if ( isset( $var['attributes'][ 'attribute_' . $skroutz_atts_color ] ) ) {
                                if ( ! isset( $color_term_ids[ $var['attributes'][ 'attribute_' . $skroutz_atts_color ] ] ) ) {
                                    $color_term_ids[ $var['attributes'][ 'attribute_' . $skroutz_atts_color ] ] = get_term_by( 'slug', $var['attributes'][ 'attribute_' . $skroutz_atts_color ], $skroutz_atts_color )->term_id;
                                }
                                $varId = $prod->get_id() . '-' . $color_term_ids[ $var['attributes'][ 'attribute_' . $skroutz_atts_color ] ];
                                if ( ! isset( $variable_products[ $varId ] ) ) {
                                    $variable_products[ $varId ]['id']                  = $varId;
                                    $variable_products[ $varId ][ $skroutz_atts_color ] = $var['attributes'][ 'attribute_' . $skroutz_atts_color ];

                                    if ( isset( $var['attributes'][ 'attribute_' . $skroutz_atts_size ] ) ) {
                                        $variable_products[ $varId ][ $skroutz_atts_size ][] = $var['attributes'][ 'attribute_' . $skroutz_atts_size ];

                                    }

                                    if ( ! empty( $var['image'] ) ) {
                                        $variable_products[ $varId ]['image'] = $var['image']['url'];
                                    }
                                    $variable_products[ $varId ]['price']   = $var['display_price']; //needs to check for taxes
                                    $variable_products[ $varId ]['link']    = get_permalink( $prod->get_id() ) . '?attribute_' . $skroutz_atts_color . '=' . $var['attributes'][ 'attribute_' . $skroutz_atts_color ];
                                    $variable_products[ $varId ]['product'] = $var_product;

                                } else {
                                    if ( isset( $var['attributes'][ 'attribute_' . $skroutz_atts_size ] ) ) {
                                        $variable_products[ $varId ][ $skroutz_atts_size ][] = $var['attributes'][ 'attribute_' . $skroutz_atts_size ];
                                    }
                                    if ( ! isset( $variable_products[ $varId ]['image'] ) && ! empty( $var['image'] ) ) {
                                        $variable_products[ $varId ]['image'] = $var['image']['url'];
                                    }

                                    if ( $variable_products[ $varId ]['price'] > $var['display_price'] ) {
                                        $variable_products[ $varId ]['price']   = $var['display_price'];
                                        $variable_products[ $varId ]['product'] = $var_product;
                                    }
                                }

                            }
                        }
                    }
                    if ( ! empty( $variable_products ) ) {
                        foreach ($variable_products as $key => $product) {
                            $xml = $this->xml_schema( $product['product'], $group_variations, $instockavailability, $avaibilities, $availabilityST, $ifoutofstock, $availabilitiesOutOfStock, $noavailabilityST, $custom_productId, $custom_mpn, $skroutz_atts_color, $skroutz_atts_size, $skroutz_atts_manuf, $enable_gtin, $include_tax, $gtin_label, $gtin_value, $featureslist, $variation_atts, $attributes, $product, $prod );
                            $this->write_skroutz_xml( $xml, $productsSkroutz, $instockavailability, $ifoutofstock );
                            $this->write_bestprice_xml( $xml, $productsBestprice, $featureslist, $instockavailability, $ifoutofstock );
                        }
                        continue; // no need to do somthing more
                    }
                    $xml = $this->xml_schema( $prod, $group_variations, $instockavailability, $avaibilities, $availabilityST, $ifoutofstock, $availabilitiesOutOfStock, $noavailabilityST, $custom_productId, $custom_mpn, $skroutz_atts_color, $skroutz_atts_size, $skroutz_atts_manuf, $enable_gtin, $include_tax, $gtin_label, $gtin_value, $featureslist, $variation_atts, $attributes );
                    $this->write_skroutz_xml( $xml, $productsSkroutz, $instockavailability, $ifoutofstock );
                    $this->write_bestprice_xml( $xml, $productsBestprice, $featureslist, $instockavailability, $ifoutofstock );

                }
                $i++;
            } while ( $i <= $result->max_num_pages );
        }
        catch ( \Exception $e ) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        /**** end processes of xml creation  */
        echo '</br>' . __( 'SUCCESSFUL CREATION OF Skroutz XML', Plugin::textdomain() ) . '</br>';
        $xmlSkroutz->saveXML( $xmlFileSkroutz );
        echo __( 'The file is located at', Plugin::textdomain() ) . ' <a href="' . wp_upload_dir()['baseurl'] . '/skroutz/skroutz.xml" target="_blank">' . wp_upload_dir()['baseurl'] . '/skroutz/skroutz.xml</a>';

        echo '</br>' . __( 'SUCCESSFUL CREATION OF BestPrice XML', Plugin::textdomain() ) . '</br>';

        $xmlBestprice->saveXML( $xmlFileBestprice );
        echo __( 'The file is located at', Plugin::textdomain() ) . ' <a href="' . wp_upload_dir()['baseurl'] . '/best-price/bp.xml" target="_blank">' . wp_upload_dir()['baseurl'] . '/best-price/bp.xml</a>';

        update_option( 'last_update', date( 'd-m-Y H:i' ) );

        return $xml_rows;
    }

    protected function xml_schema( $prod, $group_variations, $instockavailability, $avaibilities, $availabilityST, $ifoutofstock, $availabilitiesOutOfStock, $noavailabilityST, $custom_productId, $custom_mpn, $skroutz_atts_color, $skroutz_atts_size, $skroutz_atts_manuf, $enable_gtin, $include_tax, $gtin_label, $gtin_value, $featureslist, $variation_atts, $attributes, $variable_extra = [], $parent = [] ) {
        $product_id = $prod->get_id();

        if ( ! empty( $custom_productId ) ) {
            $_id = get_post_meta( $prod->get_id(), $custom_productId, 1 );

            if ( ! empty( $_id ) ) {
                $product_id = $_id;
            }
        }


        $split_color_variation = false;

        if ( ! empty( $variable_extra ) ) {
            $split_color_variation = true;
            $product_id            = $variable_extra['id'] ? $variable_extra['id'] : $prod->get_id();
            $colorTerm             = get_term_by( 'slug', $variable_extra[ $skroutz_atts_color ], $skroutz_atts_color );
        }

        $format_price = false;
        if ( function_exists( 'wc_get_price_decimal_separator' ) && function_exists( 'wc_get_price_thousand_separator' ) && function_exists( 'wc_get_price_decimals' ) ) {
            $decimal_separator  = wc_get_price_decimal_separator();
            $thousand_separator = wc_get_price_thousand_separator();
            $decimals           = wc_get_price_decimals();
            $format_price       = true;
        }
        $xml_rows = array();

        $stockstatus_ds = $prod->get_stock_status();
        if ( ( strcmp( $stockstatus_ds, "outofstock" ) == 0 || strcmp( $stockstatus_ds, "onbackorder" ) == 0 ) & ( $ifoutofstock == 1 ) ) {
            return;
        }
        $onfeed = $prod->get_meta( 'onfeed' );

        if ( $split_color_variation && ! empty( $parent ) ) {
            $excludempn = $parent->get_meta( 'excludempn' );
        } else {
            $excludempn = $prod->get_meta( 'excludempn' );
        }

        if ( strcmp( $onfeed, "no" ) == 0 ) {
            return;
        }
        $xml_rows[ $product_id ] = array(
            'onfeed'      => $onfeed,
            'excludempn'  => $excludempn,
            'stockstatus' => $stockstatus_ds,
            'attributes'  => $attributes,
        );

        switch ($instockavailability) {
            case 3:
                $_product_attributes_ser_ds = $attributes;

                if ( is_serialized( $_product_attributes_ser_ds ) ) {
                    $_product_attributes = unserialize( $_product_attributes_ser_ds );
                    foreach ($_product_attributes as $key => $attr) {
                        if ( $attr['name'] == 'Διαθεσιμότητα' ) {
                            $availabilityST = $attr['value'];
                            break;
                        }
                    }
                } else if ( is_array( $_product_attributes_ser_ds ) && ! empty( $_product_attributes_ser_ds ) ) {

                    foreach ($_product_attributes_ser_ds as $key => $attr) {
                        if ( $key == 'pa_availability' ) {
                            if ( ! empty( $parent ) && $split_color_variation ) {
                                $availabilityST = $parent->get_attribute( $key );
                            } else {
                                $availabilityST = $prod->get_attribute( $key );
                            }
                            break;
                        }
                    }
                }
                break;
            case 4:
                $tmp_availability = $prod->get_meta( '_custom_availability' );
                if ( $tmp_availability != '' ) {
                    $availabilityST = $tmp_availability;
                }
                break;
            default:
                break;
        }
        $xml_rows[ $product_id ]['availabilityST'] = ( $availabilityST == 'attribute' || $availabilityST == 'χαρακτηριστικό' ) ? '' : $availabilityST;


        if ( $ifoutofstock == 4 ) {
            $_product_attributes_ser_ds = $attributes;

            if ( is_array( $_product_attributes_ser_ds ) && ! empty( $_product_attributes_ser_ds ) ) {

                foreach ($_product_attributes_ser_ds as $key => $attr) {
                    if ( $key == 'pa_outofstockavailability' ) {
                        $noavailabilityST = $prod->get_attribute( $key );
                        break;
                    }
                }
            }
        }
        $xml_rows[ $product_id ]['noavailabilityST'] = ( $noavailabilityST == 'Attribute: Out of Stock Availability' || $noavailabilityST == 'Ιδιότητα: Out of Stock Διαθεσιμότητα' ) ? '' : $noavailabilityST;
        $tax                                         = 0;
        if ( $include_tax ) {
            $price     = wc_get_price_excluding_tax( $prod );
            $price     = floatval( $price );
            $tax_rates = \WC_Tax::get_base_tax_rates( $prod->get_tax_class() );
            $taxes     = \WC_Tax::calc_tax( $price, $tax_rates, false, false );
            if ( ! empty( $tax_rates ) ) {
                foreach ($taxes as $taxes => $tax) {
                    $price  += $tax;
                }
            } else {
                $price = $prod->get_price();
            }

        } else {
            $price = $prod->get_price();
        }

        $xml_rows[ $product_id ]['price_raw'] = $price;
        if ( $format_price && $price != '' ) {
            $price = number_format( floatval( $price ), $decimals, $decimal_separator, $thousand_separator );
        }
        $xml_rows[ $product_id ]['price']     = addslashes( $price );
        $image_ds                             = get_the_post_thumbnail_url( $prod->get_id(), 'shop_catalog' );
        $xml_rows[ $product_id ]['image_ds']  = $image_ds;
        $image_big                            = get_the_post_thumbnail_url( $prod->get_id(), 'shop_single_image_size' );
        $xml_rows[ $product_id ]['image_big'] = $image_big;

        // sku and mpn
        $skus_ds                            = $prod->get_sku();
        $xml_rows[ $product_id ]['skus_ds'] = $skus_ds;

        if ( ! empty( $custom_mpn ) ) {
            $_mpn = get_post_meta( $prod->get_id(), $custom_mpn, 1 );
            if ( ! empty( $parent ) && $split_color_variation && empty( $_mpn ) ) {
                $_mpn = get_post_meta( $parent->get_id(), $custom_mpn, 1 );
            }

            $xml_rows[ $product_id ]['mpn'] = $_mpn;

        }

        $categories_ds                         = $prod->get_category_ids();
        $_weight_ds                            = $prod->get_weight();
        $_weight_ds                            = wc_get_weight( $_weight_ds, 'g', get_option( 'woocommerce_weight_unit' ) );
        $xml_rows[ $product_id ]['_weight_ds'] = $_weight_ds;

        if ( $enable_gtin && ! empty( $gtin_value ) ) {
            $val = get_post_meta( $prod->get_id(), $gtin_value, 1 );
            if ( ! empty( $parent ) && $split_color_variation && empty( $val ) ) {
                $val = get_post_meta( $parent->get_id(), $gtin_value, 1 );
            }
            $xml_rows[ $product_id ]['gtin'][ $gtin_label ] = ! empty( $val ) ? $val : '';
        }
        $gallery_ids = $prod->get_gallery_image_ids( 'view' );
        if ( ! empty( $gallery_ids ) ) {
            $xml_rows[ $product_id ]['additional_image'] = array();
            foreach ($gallery_ids as $id) {
                $xml_rows[ $product_id ]['additional_image'][] = wp_get_attachment_url( $id );
            }
        }
        $sizestring                       = '';
        $xml_rows[ $product_id ]['sizes'] = array();

        if ( $split_color_variation ) {

            if ( isset( $variable_extra['image'] ) && ! empty( $variable_extra['image'] ) ) {
                $var_image = $variable_extra['image'];
            } else {
                $var_image = wp_get_attachment_url( $prod->get_image_id() );
            }
            $xml_rows[ $product_id ]['image_big'] = $var_image;

            if ( isset( $variable_extra[ $skroutz_atts_size ] ) ) {
                $sizes_temp = array();

                foreach ($variable_extra[ $skroutz_atts_size ] as $size_term) {
                    $termObj      = get_term_by( 'slug', $size_term, $skroutz_atts_size );
                    $sizes_temp[] = $this->format_number_skroutz( $termObj->name );
                }

                if ( empty( $variable_extra[ $skroutz_atts_size ] ) || ( count( array_unique( $sizes_temp ) ) == 1 && ( $sizes_temp[0] == '' ) ) ) {

                    if ( isset( $attributes[ $skroutz_atts_size ] ) && $attributes[ $skroutz_atts_size ] != null ) {
                        $sizes      = $attributes[ $skroutz_atts_size ]->get_terms();
                        $sizes_temp = array();
                        foreach ($sizes as $i => $size_term) {
                            $sizes_temp[] = $this->format_number_skroutz( $size_term->name );
                        }
                    }

                }

                $xml_rows[ $product_id ]['sizes'] = array_unique( $sizes_temp );
                $sizestring                       = implode( ', ', $xml_rows[ $product_id ]['sizes'] );
            }
        } else {
            if ( count( $variation_atts[ $skroutz_atts_size ] ) > 0 ) {
                $sizes_temp = array();
                foreach ($variation_atts[ $skroutz_atts_size ] as $size_term) {
                    $termObj      = get_term_by( 'slug', $size_term, $skroutz_atts_size );
                    $sizes_temp[] = $this->format_number_skroutz( $termObj->name );
                }
                $xml_rows[ $product_id ]['sizes'] = array_unique( $sizes_temp );
                $sizestring                       = implode( ', ', $xml_rows[ $product_id ]['sizes'] );
            } else {
                if ( isset( $attributes[ $skroutz_atts_size ] ) && $attributes[ $skroutz_atts_size ] != null ) {
                    $sizes      = $attributes[ $skroutz_atts_size ]->get_terms();
                    $sizes_temp = array();
                    foreach ($sizes as $i => $size_term) {
                        $sizes_temp[] = $this->format_number_skroutz( $size_term->name );
                    }
                    $xml_rows[ $product_id ]['sizes'] = array_unique( $sizes_temp );
                    $sizestring                       = implode( ', ', $xml_rows[ $product_id ]['sizes'] );
                }
            }
        }
        $xml_rows[ $product_id ]['sizestring'] = $sizestring;
        $man                                   = '';

        if ( isset( $attributes[ $skroutz_atts_manuf ] ) && $attributes[ $skroutz_atts_manuf ] != null ) {
            $brands = $attributes[ $skroutz_atts_manuf ]->get_terms();
            foreach ($brands as $brand_term) {
                $man = $brand_term->name;
            }
        } else if ( $skroutz_atts_manuf !== 'brand' ) {
            $terms = wp_get_object_terms( $prod->get_id(), $skroutz_atts_manuf, array( "fields" => "all" ) );

            if ( ! is_wp_error( $terms ) ) {
                if ( ! empty( $terms ) ) {
                    $man = $terms[0]->name;
                } else {

                    if ( ! empty( $parent ) && $split_color_variation ) {
                        $terms = wp_get_object_terms( $parent->get_id(), $skroutz_atts_manuf, array( "fields" => "all" ) );
                        if ( ! is_wp_error( $terms ) ) {
                            if ( ! empty( $terms ) ) {
                                $man = $terms[0]->name;
                            }
                        }
                    }

                }
            }

        }

        $xml_rows[ $product_id ]['manufacturer'] = $man;
        $colorRes                                = '';
        $xml_rows[ $product_id ]['colors']       = array();

        if ( $split_color_variation ) {
            $xml_rows[ $product_id ]['colorstring'] = $colorTerm->name;
        } else {
            if ( count( $variation_atts[ $skroutz_atts_color ] ) > 0 ) {
                $colors_temp = array();
                foreach ($variation_atts[ $skroutz_atts_color ] as $color_term) {
                    $colorTerm     = get_term_by( 'slug', $color_term, $skroutz_atts_color );
                    $colors_temp[] = $colorTerm->name;
                }
                $xml_rows[ $product_id ]['colors'] = array_unique( $colors_temp );
                $colorRes                          = implode( ', ', $xml_rows[ $product_id ]['colors'] );
            } else {
                if ( isset( $attributes[ $skroutz_atts_color ] ) && $attributes[ $skroutz_atts_color ] != null ) {
                    $colors      = $attributes[ $skroutz_atts_color ]->get_terms();
                    $colors_temp = array();

                    foreach ($colors as $color_term) {
                        $colors_temp[] = $color_term->name;
                        // $colorRes .= $color_term->name . ', ';
                    }
                    $xml_rows[ $product_id ]['colors'] = array_unique( $colors_temp );
                    $colorRes                          = implode( ', ', $xml_rows[ $product_id ]['colors'] );

                }
            }
            $xml_rows[ $product_id ]['colorstring'] = $colorRes;
        }
        $xml_rows[ $product_id ]['terms'] = array();

        foreach ($featureslist as $key => $feature) {
            $xml_rows[ $product_id ]['terms'][ $key ] = array();
            if ( isset( $attributes[ $feature ] ) ) {
                $prod_terms = $attributes[ $feature ]->get_terms();
                if ( is_array( $prod_terms ) ) {
                    foreach ($prod_terms as $the_term) {
                        $xml_rows[ $product_id ]['terms'][ $feature ][ $the_term->slug ] = $the_term->name;
                    }
                }
            }
        }
        $xml_rows[ $product_id ]['categories'] = array();
        $category_path                         = '';
        $categories_list                       = array();

        $prod_category_tree = get_the_terms( $prod->get_id(), 'product_cat' );
        if ( empty( $prod_category_tree ) && $split_color_variation && ! empty( $parent ) ) {

            $prod_category_tree = get_the_terms( $parent->get_id(), 'product_cat' );
        }

        if ( ! empty( $prod_category_tree ) ) {
            array_push( $categories_list, __( 'Home', 'skroutz-woocommerce-feed' ) );
            $subcategories = array_filter( $prod_category_tree, function ( $term ) {
                return ( $term->parent != 0 );
            } );

            if ( ! empty( $subcategories ) ) {
                $only_one_cat = end( $subcategories );
            } else {
                $only_one_cat = end( $prod_category_tree );
            }

            $get_tree = array_reverse( get_ancestors( $only_one_cat->term_id, 'product_cat', 'taxonomy' ) );

            foreach ($get_tree as $key => $parentCat) {
                $term = get_term_by( 'id', $parentCat, 'product_cat' );
                array_push( $categories_list, $term->name );
            }
            array_push( $categories_list, $only_one_cat->name );
            $category_path                          = implode( ', ', $categories_list );
            $xml_rows[ $product_id ]['category_id'] = $only_one_cat->term_id;
        }
        $xml_rows[ $product_id ]['category_path'] = $category_path;
        $title                                    = str_replace( "'", " ", $prod->get_title() );
        $title                                    = str_replace( "&", "+", $title );
        $title                                    = strip_tags( $title );
        if ( $split_color_variation ) {
            $xml_rows[ $product_id ]['title'] = $title . ' ' . $colorTerm->name;
            $xml_rows[ $product_id ]['link']  = $variable_extra['link'];
        } else {
            $xml_rows[ $product_id ]['title'] = $title;
            $xml_rows[ $product_id ]['link']  = get_permalink( $prod->get_id() );
        }
        $backorder                            = $prod->get_backorders();
        $xml_rows[ $product_id ]['backorder'] = $backorder;
        $xml_rows[ $product_id ]['descr']     = $prod->get_short_description();
        if ( empty( $xml_rows[ $product_id ]['descr'] ) && ! empty( $parent ) ) {
            $xml_rows[ $product_id ]['descr'] = $parent->get_short_description();
        }
        return $xml_rows;
    }

    protected function format_number_skroutz( $pa_size ) {
        return str_replace( ',', '.', $pa_size );
    }

    protected function write_skroutz_xml( $prod, $products, $instockavailability, $ifoutofstock ) {
        foreach ($prod as $prod_id => $row) {
            $product = $products->addChild( 'product' );

            $product->mpn = NULL;

            if ( $row['excludempn'] != 'yes' ) {
                if ( isset( $row['mpn'] ) ) {
                    $product->mpn->addCData( $row['mpn'] );
                } else if ( addslashes( trim( $row['skus_ds'] ) ) != '' ) {
                    $product->mpn->addCData( $row['skus_ds'] );
                } else {
                    $product->mpn->addCData( $prod_id );
                }
            }
            if ( isset( $row['gtin'] ) ) {
                $label = array_keys( $row['gtin'] )[0];
                $product->addChild( $label )->addCData( $row['gtin'][ $label ] );
            }

            $product->addChild( 'uid', $prod_id );
            $product->name = NULL;
            $product->name->addCData( $row['title'] );
            $product->link = NULL;
            $product->link->addCData( $row['link'] );

            $product->image = NULL;
            $product->image->addCData( $row['image_big'] );

            $product->category = NULL;
            $product->category->addCData( $row['category_path'] );
            if ( isset( $row['additional_image'] ) ) {
                foreach ($row['additional_image'] as $id) {
                    $product->addChild( 'additional_image' )->addCData( $id );
                }
            }
            $product->addChild( 'price', $row['price'] );


            if ( strcmp( $row['stockstatus'], "instock" ) == 0 ) {
                $product->addChild( 'instock', "Y" );
                $product->addChild( 'availability', $row['availabilityST'] );
            } else {

                if ( strcmp( $row['backorder'], "notify" ) == 0 ) {
                    $product->addChild( 'instock', "N" );

                    if ( $ifoutofstock == 0 ) {
                        $product->addChild( 'availability', __( 'Delivery up to 30 days', 'skroutz-woocommerce-feed' ) );
                    } else {
                        if ( isset( $row['noavailabilityST'] ) ) {
                            $product->addChild( 'availability', $row['noavailabilityST'] );
                        } else {
                            $product->addChild( 'availability', __( 'Delivery up to 30 days', 'skroutz-woocommerce-feed' ) );
                        }
                    }
                } else if ( strcmp( $row['backorder'], "yes" ) == 0 ) {
                    $product->addChild( 'instock', "Y" );
                    $product->addChild( 'availability', $row['availabilityST'] );
                } else {
                    $product->addChild( 'instock', "N" );
                    if ( $ifoutofstock == 0 ) {
                        $product->addChild( 'availability', __( 'Delivery up to 30 days', 'skroutz-woocommerce-feed' ) );
                    } else {
                        if ( isset( $row['noavailabilityST'] ) ) {
                            $product->addChild( 'availability', $row['noavailabilityST'] );
                        } else {
                            $product->addChild( 'availability', __( 'Delivery up to 30 days', 'skroutz-woocommerce-feed' ) );
                        }
                    }
                }
            }
            $product->addChild( 'size', $row['sizestring'] );

            $product->manufacturer = NULL;
            $product->manufacturer->addCData( $row['manufacturer'] );

            if ( $row['colorstring'] != '' ) {
                $product->color = NULL;
                $product->color->addCData( $row['colorstring'] );
            }
            if ( $row['_weight_ds'] > 0 ) {
                $product->addChild( 'weight', $row['_weight_ds'] );
            }

            $product->description = NULL;
            $product->description->addCData( $row['descr'] );
        }
    }

    protected function write_bestprice_xml( $prod, $products, $featureslist, $instockavailability, $ifoutofstock ) {
        foreach ($prod as $prod_id => $row) {
            $product      = $products->addChild( 'product' );
            $product->mpn = NULL;


            if ( $row['excludempn'] != 'yes' ) {
                if ( isset( $row['mpn'] ) ) {
                    $product->mpn->addCData( $row['mpn'] );
                } else if ( addslashes( trim( $row['skus_ds'] ) ) != '' ) {
                    $product->mpn->addCData( $row['skus_ds'] );
                } else {
                    $product->mpn->addCData( $prod_id );
                }
            }

            if ( isset( $row['gtin'] ) ) {
                $label = array_keys( $row['gtin'] )[0];
                $product->addChild( $label )->addCData( $row['gtin'][ $label ] );
            }

            $product->addChild( 'productId', $prod_id );
            $product->name = NULL;
            $product->name->addCData( $row['title'] );
            $product->link = NULL;
            $product->link->addCData( $row['link'] );

            $product->image = NULL;
            $product->image->addCData( $row['image_big'] );

            $product->categoryPath = NULL;
            $product->categoryPath->addCData( $row['category_path'] );
            $product->addChild( 'categoryID', $row['category_id'] );
            $product->addChild( 'price', $row['price'] );
            $product->description = NULL;
            $product->description->addCData( $row['descr'] );

            if ( isset( $row['additional_image'] ) ) {
                foreach ($row['additional_image'] as $id) {
                    $product->addChild( 'additional_image' )->addCData( $id );
                }
            }

            if ( strcmp( $row['stockstatus'], "instock" ) == 0 ) {
                $product->addChild( 'instock', "Y" );
                $product->addChild( 'availability', $row['availabilityST'] );
            } else {
                if ( strcmp( $row['backorder'], "notify" ) == 0 ) {
                    $product->addChild( 'instock', "N" );

                    if ( $ifoutofstock == 0 ) {
                        $product->addChild( 'availability', __( 'Upon order', 'skroutz-woocommerce-feed' ) );
                    } else {
                        if ( isset( $row['noavailabilityST'] ) ) {
                            $product->addChild( 'availability', $row['noavailabilityST'] );
                        } else {
                            $product->addChild( 'availability', __( 'Upon order', 'skroutz-woocommerce-feed' ) );
                        }
                    }
                } else if ( strcmp( $row['backorder'], "yes" ) == 0 ) {
                    $product->addChild( 'instock', "Y" );
                    $product->addChild( 'availability', $row['availabilityST'] );
                } else {
                    $product->addChild( 'instock', "N" );

                    if ( $ifoutofstock == 0 ) {
                        if ( strcmp( $row['stockstatus'], "onbackorder" ) == 0 ) {
                            $product->addChild( 'availability', __( 'Upon order', 'skroutz-woocommerce-feed' ) );
                        } else {
                            $product->addChild( 'availability', __( 'Out of stock', 'skroutz-woocommerce-feed' ) );
                        }
                    } else {
                        if ( isset( $row['noavailabilityST'] ) ) {
                            $product->addChild( 'availability', $row['noavailabilityST'] );
                        } else {
                            if ( strcmp( $row['stockstatus'], "onbackorder" ) == 0 ) {
                                $product->addChild( 'availability', __( 'Upon order', 'skroutz-woocommerce-feed' ) );
                            } else {
                                $product->addChild( 'availability', __( 'Out of stock', 'skroutz-woocommerce-feed' ) );
                            }
                        }
                    }
                }
            }
            $product->addChild( 'size', $row['sizestring'] );
            $product->manufacturer = NULL;
            $product->manufacturer->addCData( $row['manufacturer'] );
            if ( $row['colorstring'] != '' ) {
                $product->color = NULL;
                $product->color->addCData( $row['colorstring'] );
            }
            if ( $row['_weight_ds'] > 0 ) {
                $product->addChild( 'weight', $row['_weight_ds'] );
            }


            $features = $product->addChild( 'features' );
            if ( $featureslist != null ) {
                foreach ($featureslist as $feature) {
                    if ( array_key_exists( $feature, $row['terms'] ) && array_key_exists( $feature, $row['attributes'] ) ) {
                        $attname            = $row['attributes'][ $feature ]->get_taxonomy_object()->attribute_name;
                        $features->$attname = NULL;
                        $features->$attname->addCData( implode( ', ', $row['terms'][ $feature ] ) );
                    }
                }
            }
        }
    }
}
