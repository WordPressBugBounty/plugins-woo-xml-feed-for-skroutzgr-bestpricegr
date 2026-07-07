<?php

namespace Papaki\SkroutzBestPriceXMLFeed\Admin\Pages;

use Papaki\SkroutzBestPriceXMLFeed\Traits\Singleton;
use Papaki\SkroutzBestPriceXMLFeed\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class PluginMainPage {
    use Singleton;

    public function init(): void {
        add_action( 'admin_menu', [ $this, 'register' ] );
    }

    public function register(): void {
        add_submenu_page(
            Plugin::key(),
            __( 'Settings', Plugin::textdomain() ),
            __( 'Settings', Plugin::textdomain() ),
            'manage_options',
            Plugin::key(),
            [ $this, 'render' ]
        );
    }

    public function render(): void {
        echo '<div id="main">';
        echo '<div>';
        echo '</br>';
        echo '<h2>' . __( 'Settings for XML Feeds for Skroutz and Bestprice', Plugin::textdomain() ) . '</h2>';
        echo '</div>';

        global $woocommerce;
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        $taxonomies           = get_taxonomies();
        $meta_keys            = get_meta_keys();

        echo '<form method="post" action="options.php">';
        settings_fields( 'skroutz-group' );
        do_settings_sections( 'skroutz-group' );
        echo '<table class="form-table skroutz_bestprice">';
        echo '<tr valign="top">';
        echo '<th scope="row">' . __( 'When in Stock Availability', Plugin::textdomain() ) . '</th><td>';
        $options = get_option( 'instockavailability' );

        $items = array(
            __( 'Available in store / Delivery 1 to 3 days', Plugin::textdomain() ),
            __( 'Delivery 1 to 3 days', Plugin::textdomain() ),
            __( 'Delivery 4 to 10 days', Plugin::textdomain() ),
            __( 'Attribute: Availability', Plugin::textdomain() ),
            __( 'Custom Availability', Plugin::textdomain() ),
        );
        echo "<select id='drop_down1' name='instockavailability'>";
        foreach ($items as $key => $item) {
            $selected = ( $options == $key ) ? 'selected="selected"' : '';
            echo "<option value='" . esc_html( $key ) . "' $selected>" . esc_html( $item ) . "</option>";
        }
        echo "</select>";
        echo "</br></br> <em>" . __( 'Select <strong>Attribute: Availability</strong> only if you have added an attribute with name "Availability"', Plugin::textdomain() ) . "</em>";
        echo '</td>';
        echo '</tr>';


        echo '<tr valign="top">';
        echo '<th scope="row">' . __( 'If a Product is out of Stock or on backorder', Plugin::textdomain() ) . '</th>';
        echo '<td>';

        $options2 = get_option( 'ifoutofstock' );

        $items = array(
            __( 'Include as out of Stock or Upon Request', Plugin::textdomain() ),
            __( 'Exclude from feed', Plugin::textdomain() ),
            __( 'Delivery 1 to 3 days', Plugin::textdomain() ),
            __( 'Delivery 4 to 10 days', Plugin::textdomain() ),
            __( 'Attribute: Out of Stock Availability', Plugin::textdomain() ),
        );
        echo "<select id='drop_down2' name='ifoutofstock'>";
        foreach ($items as $key => $item) {
            $selected = ( $options2 == $key ) ? 'selected="selected"' : '';
            echo "<option value='" . esc_html( $key ) . "' $selected>" . esc_html( $item ) . "</option>";
        }
        echo "</select>";
        echo "</br></br> <em>"
            . __( '• Select <strong>Attribute: Out of Stock Availability</strong> only if you have added an attribute with name "OutOfStockAvailability"', Plugin::textdomain() )
            . __( '<br>• If you select  <strong>“Include as out of Stock or Upon Request” </strong>:<br>
    &emsp; At Skroutz it will show the option: “Delivery up to 30 days” (former Upon Order option).<br>
    &emsp; At BestPrice it will show either “Out of stock” or “Upon order”, depending on product availability status.', Plugin::textdomain() )
            . "</em>";

        echo '</td>';
        echo '</tr>';

        $include_tax = get_option( 'include_tax', false );

        echo '<tr valign="top">';
        echo '<th> <label for="include_tax">' . __( 'Auto Calculate Price with Tax', Plugin::textdomain() ) . '</label></th>';
        echo '<td><input style="margin-left:10px;" id="include_tax"  class="include_tax" type="checkbox" name="include_tax" value="1" ' . ( $include_tax == 1 ? "checked" : "" ) . ' /></td>';
        echo "</tr>";

        $group_variations = get_option( 'group_variations', false );

        echo '<tr valign="top">';
        echo '<th> <label for="group_variations">' . __( 'Split variable products by color', Plugin::textdomain() ) . '</label></th>';
        echo '<td><input style="margin-left:10px;" id="group_variations"  class="group_variations" type="checkbox" name="group_variations" value="1" ' . ( $group_variations == 1 ? "checked" : "" ) . ' /></td>';
        echo "</tr>";

        $custom_productId = get_option( 'custom_productId' );
        echo '<tr>';
        echo '<th> <label for="custom_product_id">' . __( 'Custom Product Id', Plugin::textdomain() ) . '</label></th>';
        echo '<td><select name="custom_productId" class="autocomplete" tabindex="-1">';
        echo "<option value='' " . selected( $selected, true, false ) . ">" . __( '-Default-', Plugin::textdomain() ) . "</option>";

        foreach ($meta_keys as $key => $metaKey) {
            $selected = false;
            if ( $custom_productId == $metaKey ) {
                $selected = true;
            }

            echo "<option value='" . esc_html( $metaKey ) . "' " . selected( $selected, true, false ) . ">" . esc_html( $metaKey ) . "</option>";
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';

        $custom_mpn = get_option( 'custom_mpn' );
        echo '<tr>';
        echo '<th> <label for="custom_mpn">' . __( 'MPN', Plugin::textdomain() ) . '</label></th>';
        echo '<td><select name="custom_mpn" class="autocomplete" tabindex="-1">';
        echo "<option value='' " . selected( $selected, true, false ) . ">" . __( '-Default-', Plugin::textdomain() ) . "</option>";

        foreach ($meta_keys as $key => $metaKey) {
            $selected = false;
            if ( $custom_mpn == $metaKey ) {
                $selected = true;
            }

            echo "<option value='" . esc_html( $metaKey ) . "' " . selected( $selected, true, false ) . ">" . esc_html( $metaKey ) . "</option>";
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';

        foreach ($attribute_taxonomies as $tax) {
            $term                                  = wc_attribute_taxonomy_name( $tax->attribute_name );
            $attribute_terms[ $tax->attribute_id ] = '';
            if ( taxonomy_exists( $term ) ) {
                $attribute_terms[ $tax->attribute_id ] = $term;
            }
        }

        $skroutz_atts_color = get_option( 'skroutz_atts_color', 'pa_color' );
        $skroutz_atts_size  = get_option( 'skroutz_atts_size', 'pa_size' );
        $skroutz_atts_manuf = get_option( 'skroutz_atts_manuf', 'pa_brand' );

        echo "<tr>";
        echo "<th>";
        echo '<label for="skroutz_atts_size">' . __( 'Size', Plugin::textdomain() ) . '</label>';
        echo "</th>";
        echo "<td>";
        echo '<select name="skroutz_atts_size">';
        echo "<option value='' " . selected( $selected, true, false ) . ">" . __( '-Empty-', Plugin::textdomain() ) . "</option>";
        foreach ($attribute_taxonomies as $tax) {
            $selected = false;
            if ( $skroutz_atts_size == $attribute_terms[ $tax->attribute_id ] ) {
                $selected = true;
            }

            echo "<option value='" . esc_html( $attribute_terms[ $tax->attribute_id ] ) . "' " . selected( $selected, true, false ) . ">" . esc_html( $tax->attribute_label ) . "</option>";
        }
        echo '</select>';
        echo "</td>";
        echo '</tr>';

        echo "<tr>";
        echo "<th>";
        echo '<label for="skroutz_atts_color">' . __( 'Color', Plugin::textdomain() ) . '</label>';
        echo "</th>";
        echo "<td>";
        echo '<select name="skroutz_atts_color">';
        echo "<option value='' " . selected( $selected, true, false ) . ">" . __( '-Empty-', Plugin::textdomain() ) . "</option>";

        foreach ($attribute_taxonomies as $tax) {
            $selected = false;
            if ( $skroutz_atts_color == $attribute_terms[ $tax->attribute_id ] ) {
                $selected = true;
            }

            echo "<option value='" . esc_html( $attribute_terms[ $tax->attribute_id ] ) . "' " . selected( $selected, true, false ) . ">" . esc_html( $tax->attribute_label ) . "</option>";
        }
        echo '</select>';

        echo "</td>";
        echo '</tr>';

        echo "<tr>";
        echo "<th>";
        echo '<label for="skroutz_atts_manuf">' . __( 'Manufacturer', Plugin::textdomain() ) . '</label>';

        echo "</th>";
        echo "<td>";
        echo '<select name="skroutz_atts_manuf">';
        if ( $skroutz_atts_manuf == '' ) {
            $selected = true;
        }
        echo "<option value='' " . selected( $selected, true, false ) . ">" . __( '-Empty-', Plugin::textdomain() ) . "</option>";
        $hasAttributeBrand = false;
        foreach ($attribute_taxonomies as $tax) {
            $selected = false;
            if ( $skroutz_atts_manuf == $attribute_terms[ $tax->attribute_id ] ) {
                $selected = true;
            }
            if ( $attribute_terms[ $tax->attribute_id ] === 'brand' || $attribute_terms[ $tax->attribute_id ] === 'pa_brand' ) {
                $hasAttributeBrand = true;
            }
            echo "<option value='" . esc_html( $attribute_terms[ $tax->attribute_id ] ) . "' " . selected( $selected, true, false ) . ">" . esc_html( $tax->attribute_label ) . "</option>";
        }
        if ( ! $hasAttributeBrand ) {
            foreach ($taxonomies as $tax) {
                $selected = false;
                if ( strpos( $tax, 'brand' ) === false ) continue;
                if ( $skroutz_atts_manuf == $tax ) {
                    $selected = true;
                }

                echo "<option value='" . esc_html( $tax ) . "' " . selected( $selected, true, false ) . ">" . esc_html( $tax ) . "</option>";
            }
        }
        echo '</select>';
        echo "</td>";
        echo '</tr>';

        echo '<tr valign="top">';
        echo '<th scope="row">' . __( 'Features for bestprice(Table of Features)', Plugin::textdomain() ) . '</th>';
        echo '<td>';

        $options3 = get_option( 'features' );

        echo "<select class='autocomplete' id='drop_down3' name='features[]' multiple='multiple'>";
        foreach ($attribute_taxonomies as $tax) {
            $selected = false;
            if ( is_array( $options3 ) && in_array( $attribute_terms[ $tax->attribute_id ], $options3 ) ) {
                $selected = true;
            }

            echo "<option value='" . esc_html( $attribute_terms[ $tax->attribute_id ] ) . "' " . selected( $selected, true, false ) . ">" . esc_html( $tax->attribute_label ) . "</option>";
        }
        echo "</select>";

        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th>' . __( 'Exclude categories from XML', Plugin::textdomain() ) . '</th>';
        echo '<td>';
        $cats_excluded = get_option( 'exclude_cats' );

        echo '<select class="autocomplete" id="cat_drop" name="exclude_cats[]" multiple="multiple">';
        $avail_cats = get_terms( 'product_cat', array( 'get' => 'all' ) );

        foreach ($avail_cats as $cat) {
            $selected = false;
            if ( is_array( $cats_excluded ) && in_array( $cat->term_id, $cats_excluded ) ) {
                $selected = true;
            }

            echo '<option value="' . $cat->term_id . '" ' . selected( $selected, true, false ) . ' >' . $cat->name . '</option>';
        }
        echo '</select>';
        echo '';
        echo '</td>';
        echo '</tr>';


        $enable_gtin  = get_option( 'enable_gtin' );
        $gtin_label   = get_option( 'gtin_label' );
        $gtin_value   = get_option( 'gtin_value' );
        $gtin_plugins = array(
            'hwp_product_gtin' => 'WooCommerce UPC, EAN, and ISBN',
            '_wpm_gtin_code'   => 'Product GTIN (EAN, UPC, ISBN) for WooCommerce',
        );
        $gtin_values  = array();
        foreach ($meta_keys as $key => $metaKey) {
            if ( strpos( $metaKey, 'gtin' ) !== false
                || strpos( $metaKey, 'ean' ) !== false
                || strpos( $metaKey, 'isbn' ) !== false
                || strpos( $metaKey, 'upc' ) !== false
                || strpos( $metaKey, 'barcode' ) !== false
                || strpos( $metaKey, 'mpn' ) !== false
            ) {
                if ( isset( $gtin_plugins[ $metaKey ] ) != null ) {
                    $gtin_values[ $metaKey ] = $gtin_plugins[ $metaKey ];
                } else {
                    $gtin_values[ $metaKey ] = $metaKey;
                }
            }
        }

        echo '<tr valign="top">';
        echo '<th> <label for="toggle_gtin">' . __( 'Enable GTIN Feed', Plugin::textdomain() ) . ' </label></th>';
        echo '<td><input style="margin-left:10px;" id="toggle_gtin" class="toggle_gtin" type="checkbox" name="enable_gtin" value="1" ' . ( $enable_gtin == 1 ? "checked" : "" ) . ' /></td>';

        echo "</tr>";
        echo '<tr class="gtin" style="' . ( $enable_gtin == 1 ? '' : 'display:none' ) . '" valign="top">';
        echo '<th>' . __( 'GTIN settings', Plugin::textdomain() ) . '</th>';
        echo '<td>';
        echo '<label>' . __( 'XML Tag Name', Plugin::textdomain() ) . ': ';
        echo '<select name="gtin_label">';
        echo '<option value="ean" ' . selected( 'ean' == $gtin_label, true, false ) . '>Ean</option>';
        echo '<option value="barcode"' . selected( 'barcode' == $gtin_label, true, false ) . '>Barcode</option>';
        echo '<option value="isbn"' . selected( 'isbn' == $gtin_label, true, false ) . '>ISBN</option>';
        echo '</select></label> &nbsp;&nbsp;';

        echo '<label>' . __( 'GTIN Source Plugin', Plugin::textdomain() ) . ': ';
        echo '<select name="gtin_value">';
        echo "<option value='' " . selected( $selected, true, false ) . ">" . __( '-Empty-', Plugin::textdomain() ) . "</option>";
        foreach ($gtin_values as $key => $gtin) {
            $selected = false;
            if ( $key == $gtin_value ) {
                $selected = true;
            }
            echo '<option value="' . esc_html( $key ) . '" ' . selected( $selected, true, false ) . '>' . esc_html( $gtin ) . '</option>';
        }
        echo '</select></label> &nbsp;&nbsp;';
        echo '</td>';
        echo '</tr>';

        echo ' </table>';
        submit_button();
        echo '</form>';

        if ( get_option( 'last_update' ) != "" ) {
            echo '<div class="feedsUrl" style="display: flex; flex-direction: column; max-width: 500px; margin: 0px 0 30px; padding: 10px 0; align-content: center;">';
            echo '<h3 style="margin: 0.3em 0;">' . __( 'XML Feed Urls:', Plugin::textdomain() ) . '</h3>';
            echo '<p style="">' . __( 'Last generated XML Feed time: ', Plugin::textdomain() ) . get_option( 'last_update' ) . '</p>';
            echo __( 'Skroutz XML Url: ', Plugin::textdomain() ) . ' <a href="' . wp_upload_dir()['baseurl'] . '/skroutz/skroutz.xml" target="_blank">' . wp_upload_dir()['baseurl'] . '/skroutz/skroutz.xml</a></br>';
            echo __( 'Bestprice XML Url: ', Plugin::textdomain() ) . ' <a href="' . wp_upload_dir()['baseurl'] . '/best-price/bp.xml" target="_blank">' . wp_upload_dir()['baseurl'] . '/best-price/bp.xml</a>';
            echo '</div>';
        }

        echo '<a class="button button-primary" href="' . get_admin_url() . 'admin.php?page=' . Plugin::key() . '_xml_create_page">' . __( 'Create XML Feeds', Plugin::textdomain() ) . '</a>';
        echo '</div>';

        wc_enqueue_js( '
$(".toggle_gtin").change(function() {
    if($(".toggle_gtin:checked").length) {
        $(".gtin").show();
    } else {
        $(".gtin").hide();
    }
});' );
    }
}
