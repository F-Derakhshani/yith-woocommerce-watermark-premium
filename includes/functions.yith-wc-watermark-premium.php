<?php
if( !defined('ABSPATH'))
    exit;

if( ! function_exists( 'yit_wc_watermark_json_search_product_categories') ) {

    function yit_wc_watermark_json_search_product_categories( $x = '', $taxonomy_types = array('product_cat') ) {



            global $wpdb;
            $term = (string)urldecode(stripslashes(strip_tags($_GET['term'])));
            $term = "%" . $term . "%";

            $query_cat = $wpdb->prepare("SELECT {$wpdb->terms}.term_id,{$wpdb->terms}.name, {$wpdb->terms}.slug
                                   FROM {$wpdb->terms} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                                   WHERE {$wpdb->term_taxonomy}.taxonomy IN (%s) AND {$wpdb->terms}.slug LIKE %s", implode(",", $taxonomy_types), $term);

            $product_categories = $wpdb->get_results($query_cat);

            $to_json = array();

            foreach ( $product_categories as $product_category ) {

                $to_json[$product_category->term_id] = "#" . $product_category->term_id . "-" . $product_category->name;
            }

            wp_send_json( $to_json );


    }
}
add_action('wp_ajax_yith_json_search_product_categories',  'yit_wc_watermark_json_search_product_categories', 10);

if( !function_exists( 'yith_wcwat_locate_template' ) ) {
    /**
     * Locate the templates and return the path of the file found
     *
     * @param string $path
     * @param array $var
     * @return void
     * @since 1.0.0
     */
    function yith_wcwat_locate_template( $path, $var = NULL ){
        global $woocommerce;

        if( function_exists( 'WC' ) ){
            $woocommerce_base = WC()->template_path();
        }
        elseif( defined( 'WC_TEMPLATE_PATH' ) ){
            $woocommerce_base = WC_TEMPLATE_PATH;
        }
        else{
            $woocommerce_base = $woocommerce->plugin_path() . '/templates/';
        }

        $template_woocommerce_path =  $woocommerce_base . $path;
        $template_path = '/' . $path;
        $plugin_path = YWCWAT_TEMPLATE_PATH . '/' . $path;

        $located = locate_template( array(
            $template_woocommerce_path, // Search in <theme>/woocommerce/
            $template_path,             // Search in <theme>/
        ) );

        if( ! $located && file_exists( $plugin_path ) ){
            return apply_filters( 'yith_wcwat_locate_template', $plugin_path, $path );
        }

        return apply_filters( 'yith_wcwat_locate_template', $located, $path );
    }
}

if( !function_exists( 'yith_wcwat_get_template' ) ) {
    /**
     * Retrieve a template file.
     *
     * @param string $path
     * @param mixed $var
     * @param bool $return
     * @return void
     * @since 1.0.0
     */
    function yith_wcwat_get_template( $path, $var = null, $return = false ) {
        $located = yith_wcwat_locate_template( $path, $var );

        if ( $var && is_array( $var ) )
            extract( $var );

        if( $return )
        { ob_start(); }

        // include file located
        include( $located );

        if( $return )
        { return ob_get_clean(); }
    }
}



if( !function_exists( 'ywcwat_get_product_id_by_attach')){

    function ywcwat_get_product_id_by_attach( $attach_id ){

        global $wpdb;

        $result = $wpdb->get_results(

            "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts}
             WHERE {$wpdb->posts}.post_type IN ('product', 'product_variation')
             AND {$wpdb->posts}.ID IN (
                                  SELECT DISTINCT {$wpdb->postmeta}.post_id FROM {$wpdb->postmeta} INNER JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id= {$wpdb->posts}.ID
                                  WHERE ( ( {$wpdb->postmeta}.meta_key= '_thumbnail_id' AND {$wpdb->postmeta}.meta_value =$attach_id )
                                          OR  ( {$wpdb->postmeta}.meta_key='_product_image_gallery' AND {$wpdb->postmeta}.meta_value REGEXP '$attach_id') ) )
                ORDER BY {$wpdb->posts}.ID ASC" );



        return $result;
    }
}

if( !function_exists( 'ywcwat_get_all_product_img_gallery' ) ){

    function ywcwat_get_all_product_img_gallery(){

        global $wpdb;

        $result_gallery =  $wpdb->get_results( "SELECT DISTINCT {$wpdb->postmeta}.meta_value AS ID FROM {$wpdb->postmeta} INNER JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id= {$wpdb->posts}.ID
                                  WHERE {$wpdb->postmeta}.meta_key= '_product_image_gallery' AND {$wpdb->posts}.post_type ='product' AND {$wpdb->postmeta}.meta_value!='' ORDER BY {$wpdb->postmeta}.`meta_value` DESC");

      return $result_gallery;
    }
}

if( !function_exists( 'ywcwat_generate_backup_product_img_gallery' ) ){

    function ywcwat_generate_backup_product_img_gallery(){

        $result_gallery = ywcwat_get_all_product_img_gallery();

        foreach( $result_gallery as $gallery ){

            $attach_ids = explode(',', $gallery->ID );

            foreach ( $attach_ids as $attach_id ) {

                $file_path = get_attached_file( $attach_id );

                ywcwat_backup_file( $file_path );
            }
        }

    }
}

if( !function_exists( 'ywcwat_get_font_name' ) ) {

    function ywcwat_get_font_name()
    {

        $font_ext = apply_filters('ywcwat_font_types', array('ttf'));
        $font_dir = YWCWAT_DIR . '/assets/fonts/';
        $fonts_name = array();


            $fonts = (array)glob("$font_dir/*");

                foreach ($fonts as $font) {

                    $ext = pathinfo($font, PATHINFO_EXTENSION);

                    if (in_array($ext, $font_ext))
                        $fonts_name[] = $font;
                }
        return $fonts_name;
    }
}

if( !function_exists( 'ywcwat_Hex2RGB' ) ){

    function ywcwat_Hex2RGB( $color ){
        $color = str_replace( '#', '', $color );
        if ( strlen( $color ) != 6){ return array( 0,0,0 ); }
        $rgb = array();
        for ( $x=0;$x<3;$x++ ){
            $rgb[$x] = hexdec( substr( $color,( 2*$x ),2 ) );
        }
        return $rgb;
    }
}

if( !function_exists(('ywcwat_get_attach_id_by_product') ) ){

    function ywcwat_get_attach_id_by_product( $products ){

        global $wpdb;
        $query = $wpdb->prepare("SELECT DISTINCT {$wpdb->postmeta}.meta_value FROM {$wpdb->postmeta} INNER JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
                                  WHERE {$wpdb->postmeta}.meta_key IN ('_thumbnail_id', '_product_image_gallery') AND {$wpdb->postmeta}.meta_value!='' AND {$wpdb->postmeta}.post_id IN ( %d )", implode(',', $products) );

        return $wpdb->get_results( $query );
    }
}