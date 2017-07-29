<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Implements premium features of YIT WooCommerce Watermark plugin
 *
 * @class   YITH_WC_Watermark
 * @package YITHEMES
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */
if( !class_exists( 'YITH_WC_Watermark_Premium' ) ) {

    class YITH_WC_Watermark_Premium extends YITH_WC_Watermark
    {

        /**
         * @var YITH_WC_Watermark_Premium single instance of class
         */
        protected static $_instance;


        public function __construct()
        {

            parent::__construct();

            add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
            add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );
            //add premium tabs
            add_filter( 'ywcwat_add_premium_tab', array( $this, 'add_premium_tab' ) );

            //add ajax action for load admin template
            add_action( 'wp_ajax_add_new_watermark_admin', array( $this, 'add_new_watermark_admin' ) );
            add_action( 'wp_ajax_nopriv_add_new_watermark_admin', array( $this, 'add_new_watermark_admin' ) );

            //add ajax action for add new watermark (in single product )
            add_action( 'wp_ajax_add_new_product_watermark_admin', array( $this, 'add_new_product_watermark_admin' ) );
            add_action( 'wp_ajax_nopriv_add_new_product_watermark_admin', array( $this, 'add_new_product_watermark_admin' ) );

            //add metaboxes in edit product
            add_filter( 'product_type_options', array( $this, 'add_product_watermark_option' ) );
            add_filter( 'woocommerce_product_write_panel_tabs', array( $this, 'print_watermark_panels' ), 98 );
            add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_watermark_meta' ), 20, 2 );

            add_filter( 'ywcwat_max_item_task', array( $this, 'remove_max_item_task' ) );

            if( is_admin() ) {
                //include admin style and script
                $this->include_premium_file();
                add_action( 'woocommerce_admin_field_watermark-insert-new', 'YITH_Watermark_Insert_New::output' );
                add_action( 'woocommerce_admin_field_watermark-apply', 'YITH_Watermark_Apply::output' );
                add_action( 'admin_enqueue_scripts', array( $this, 'include_premium_style_script' ) );
            }
        }

        /** return single instance of class
         * @author YITHEMES
         * @since 1.0.0
         * @return YITH_WC_Watermark_Premium
         */
        public static function get_instance()
        {

            if( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /** Register plugins for activation tab
         * @return void
         * @since    1.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_activation()
        {
            if( !class_exists( 'YIT_Plugin_Licence' ) ) {
                require_once YWCWAT_DIR . 'plugin-fw/licence/lib/yit-licence.php';
                require_once YWCWAT_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
            }
            YIT_Plugin_Licence()->register( YWCWAT_INIT, YWCWAT_SECRET_KEY, YWCWAT_SLUG );
        }

        /**
         * Register plugins for update tab
         *
         * @return void
         * @since    1.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_updates()
        {
            if( !class_exists( 'YIT_Upgrade' ) ) {
                require_once( YWCWAT_DIR . 'plugin-fw/lib/yit-upgrade.php' );
            }
            YIT_Upgrade()->register( YWCWAT_SLUG, YWCWAT_INIT );
        }

        /** add premium tab
         * @author YITHEMES
         * @since 1.0.0
         * @param $tabs
         * @return mixed
         */
        public function add_premium_tab( $tabs )
        {

            unset( $tabs['premium-landing'] );

            $tabs['watermark-list'] = __( 'Active Watermark', 'yith-woocommerce-watermark' );
            return $tabs;
        }

        /** include custom admin fields
         * @author YITHEMES
         * @since 1.0.0
         */
        public function include_premium_file()
        {

            include_once( YWCWAT_TEMPLATE_PATH . '/admin/watermark-insert-new.php' );
            include_once( YWCWAT_TEMPLATE_PATH . '/admin/watermark-apply.php' );
        }

        /** remove limit item for task
         * @author YITHEMES
         * @since 1.0.0
         * @param $max_item_task
         * @return string
         */
        public function remove_max_item_task( $max_item_task )
        {
            return '';
        }

        /** include style and script
         * @author YITHEMES
         * @since 1.0.0
         */
        public function include_premium_style_script()
        {


            if( isset( $_GET['page'] )  && 'yith_ywcwat_panel' == $_GET['page'] ) {

                wp_enqueue_script( 'ywcwat_premium_admin_script', YWCWAT_ASSETS_URL . 'js/ywcwat_premium_admin' . $this->_suffix . '.js', array( 'jquery', 'wp-color-picker' ), YWCWAT_VERSION, true );
                wp_enqueue_script( 'ywcwat_premium_enhanceselect', YWCWAT_ASSETS_URL . 'js/ywcwat_enhancedcselect' . $this->_suffix . '.js', array( 'jquery', 'select2' ), YWCWAT_VERSION, true );

                $ywcwat_localize_script = array(
                    'i18n_matches_1' => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce' ),
                    'i18n_matches_n' => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce' ),
                    'i18n_no_matches' => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
                    'i18n_ajax_error' => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
                    'i18n_input_too_short_1' => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
                    'i18n_input_too_short_n' => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
                    'i18n_input_too_long_1' => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
                    'i18n_input_too_long_n' => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
                    'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
                    'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
                    'i18n_load_more' => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
                    'i18n_searching' => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
                    'ajax_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
                    'search_categories_nonce' => wp_create_nonce( YWCWAT_SLUG . '_search-categories' ),
                    'plugin_nonce' => '' . YWCWAT_SLUG . '',


                );
                $shop_single = wc_get_image_size( 'shop_single' );
                $shop_catalog = wc_get_image_size( 'shop_catalog' );
                $shop_thumbnail = wc_get_image_size( 'shop_thumbnail' );
                $ywcwat_label_position = array(
                    'label_position' => array(
                        'top_left' => __( 'TOP LEFT', 'yith-woocommerce-watermark' ),
                        'top_center' => __( 'TOP CENTER', 'yith-woocommerce-watermark' ),
                        'top_right' => __( 'TOP RIGHT', 'yith-woocommerce-watermark' ),
                        'middle_left' => __( 'LEFT CENTER', 'yith-woocommerce-watermark' ),
                        'middle_center' => __( 'CENTER', 'yith-woocommerce-watermark' ),
                        'middle_right' => __( 'RIGHT CENTER', 'yith-woocommerce-watermark' ),
                        'bottom_left' => __( 'BOTTOM LEFT', 'yith-woocommerce-watermark' ),
                        'bottom_center' => __( 'BOTTOM CENTER', 'yith-woocommerce-watermark' ),
                        'bottom_right' => __( 'BOTTOM RIGHT', 'yith-woocommerce-watermark' )
                    ),
                    'delete_single_watermark' => array(
                        'confirm_delete_watermark' => __( 'Do you want to delete this watermark ?', 'yith-woocommerce-watermark' )
                    ),
                    'messages' => array(
                        'singular_success_image' => __( 'Image has been created', 'yith-woocommerce-watermark' ),
                        'plural_success_image' => __( 'Images have been created', 'yith-woocommerce-watermark' ),
                        'singular_error_image' => __( 'Image has not been created', 'yith-woocommerce-watermark' ),
                        'plural_error_image' => __( 'Images have not been created', 'yith-woocommerce-watermark' ),
                    ),
                    'attach_id' => $this->get_watermark_ids(),
                    'shop_single' => $shop_single,
                    'shop_catalog' => $shop_catalog,
                    'shop_thumbnail' => $shop_thumbnail,
                    'gd_version' => $this->get_gd_version()
                );

                wp_localize_script( 'ywcwat_premium_admin_script', 'ywcwat_premium', $ywcwat_label_position );
                wp_localize_script( 'ywcwat_premium_enhanceselect', 'ywcwat_enhanceselect', $ywcwat_localize_script );

                wp_enqueue_script( 'wc-enhanced-select' );
                wp_enqueue_style( 'ywcwat_load_font', YWCWAT_ASSETS_URL . '/fonts/ywcwat_load_fonts.css', array(), YWCWAT_VERSION );
            }
            
            
            global $post;
            if( isset( $post ) &&  ( 'product' == $post->post_type || 'product_variation' == $post->post_type ) ){
                $shop_single = wc_get_image_size( 'shop_single' );
                $shop_catalog = wc_get_image_size( 'shop_catalog' );
                $shop_thumbnail = wc_get_image_size( 'shop_thumbnail' );
                $ywcwat_label_position = array(
                    'label_position' => array(
                        'top_left' => __( 'TOP LEFT', 'yith-woocommerce-watermark' ),
                        'top_center' => __( 'TOP CENTER', 'yith-woocommerce-watermark' ),
                        'top_right' => __( 'TOP RIGHT', 'yith-woocommerce-watermark' ),
                        'middle_left' => __( 'LEFT CENTER', 'yith-woocommerce-watermark' ),
                        'middle_center' => __( 'CENTER', 'yith-woocommerce-watermark' ),
                        'middle_right' => __( 'RIGHT CENTER', 'yith-woocommerce-watermark' ),
                        'bottom_left' => __( 'BOTTOM LEFT', 'yith-woocommerce-watermark' ),
                        'bottom_center' => __( 'BOTTOM CENTER', 'yith-woocommerce-watermark' ),
                        'bottom_right' => __( 'BOTTOM RIGHT', 'yith-woocommerce-watermark' )
                    ),
                    'delete_single_watermark' => array(
                        'confirm_delete_watermark' => __( 'Do you want to delete this watermark ?', 'yith-woocommerce-watermark' )
                    ),
                    'messages' => array(
                        'singular_success_image' => __( 'Image has been created', 'yith-woocommerce-watermark' ),
                        'plural_success_image' => __( 'Images have been created', 'yith-woocommerce-watermark' ),
                        'singular_error_image' => __( 'Image has not been created', 'yith-woocommerce-watermark' ),
                        'plural_error_image' => __( 'Images have not been created', 'yith-woocommerce-watermark' ),
                    ),
                    'attach_id' => $this->get_watermark_ids(),
                    'shop_single' => $shop_single,
                    'shop_catalog' => $shop_catalog,
                    'shop_thumbnail' => $shop_thumbnail,
                    'gd_version' => $this->get_gd_version()
                );



                wp_enqueue_style( 'ywcwat_free_admin_style', YWCWAT_ASSETS_URL . 'css/ywcwat_admin.css', array(), YWCWAT_VERSION );
                wp_enqueue_script( 'ywcwat_premium_product_admin_script', YWCWAT_ASSETS_URL . 'js/ywcwat_admin_single_product' . $this->_suffix . '.js', array( 'jquery', 'wp-color-picker' ), YWCWAT_VERSION, true );
                wp_localize_script( 'ywcwat_premium_product_admin_script', 'ywcwat_premium', $ywcwat_label_position );
            }
        }

        /** return new watermark metabox in admin
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_new_watermark_admin()
        {

            if( isset( $_REQUEST['ywcwat_addnewwat'] ) && isset( $_REQUEST['ywcwat_unique_id'] ) ) {

                $params = array(
                    'option_id' => 'ywcwat_watermark_select',
                    'current_row' => $_REQUEST['ywcwat_addnewwat'],
                    'unique_id' => $_REQUEST['ywcwat_unique_id'],

                );

                $params['params'] = $params;

                wp_send_json( array( 'result' => yith_wcwat_get_template( 'single-watermark-template.php', $params, true ) ) );
            }
        }

        /** return new watermark metabox in edit product
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_new_product_watermark_admin()
        {

            if( isset( $_REQUEST['ywcwat_product_addnewwat'] ) ) {

                $optionid = $_REQUEST['ywcwat_product_option_id'];
                $current_row = $_REQUEST['ywcwat_product_addnewwat'];

                $params = array(
                    'option_id' => $optionid,
                    'current_row' => $current_row
                );

                $params['params'] = $params;

                wp_send_json( array( 'result' => yith_wcwat_get_template( 'single-product-watermark-template.php', $params, true ) ) );

            }
        }

        /** return attachment ids
         * @author YITHEMES
         * @since 1.0.0
         * @return mixed|string|void
         */
        public function get_ids_attach()
        {

            $attach_ids = ywcwat_get_all_product_attach();

            $ids = array();

            foreach ( $attach_ids as $attach_id )
                $ids[] = $attach_id->ID;

            $gallery_ids = ywcwat_get_all_product_img_gallery();

            foreach ( $gallery_ids as $gallery_id ) {

                $attach_ids = explode( ',', $gallery_id->ID );


                foreach ( $attach_ids as $attach_id ) {


                    if( !in_array( $attach_id, $ids ) ) {
                        $ids[] = $attach_id;
                    }
                }
            }


            return json_encode( $ids );

        }

        /** save image+watermark
         * overridden
         * @author YITHEMES
         * @param $filepath
         * @return string
         */
        public function save_image_with_watermark( $filepath, $attach_id = null, $size_name = "shop_single", $watermark = null )
        {


            if( is_file( $filepath ) ) {

                $original_image_details = getimagesize( $filepath );

                $type_img = preg_replace( '#image/#i', '', $original_image_details['mime'] );

                $acceptable_formats = array( 'jpeg', 'gif', 'png', 'jpg' );

                if( !in_array( $type_img, $acceptable_formats ) ) {

                    return 'format_incorrect';
                }
                $original_image = $this->createimagefrom( $filepath, $type_img );

                if( $watermark == null ) {
                    $watermarks = get_option( 'ywcwat_watermark_select' );
                }
                else {
                    $watermarks = $watermark;
                }


                foreach ( $watermarks as $key => $watermark ) {

                    if( $watermark['ywcwat_watermark_type'] == 'type_text' ) {

                        $overlay = $this->imagecreatefromtext( $original_image, $watermark );


                    }
                    else {

                        $watermark_path = get_attached_file( $watermark['ywcwat_watermark_id'] );

                        if( $watermark_path == false ) {
                            continue;
                        }

                        $watermark_image_details = getimagesize( $watermark_path );

                        $type_wat = preg_replace( '#image/#i', '', $watermark_image_details['mime'] );
                        $overlay = $this->createimagefrom( $watermark_path, $type_wat );
                    }
                    $watermark_category = isset( $watermark['ywcwat_watermark_category'] ) ? $watermark['ywcwat_watermark_category'] : array();

                    if( !empty( $watermark_category ) ) {

                        $products = ywcwat_get_product_id_by_attach( $attach_id );

                        $watermark_category = !is_array( $watermark_category ) ?  explode( ',', $watermark_category ) : $watermark_category;
                        foreach ( $products as $product ) {

                            $categories = wp_get_post_terms( $product->ID, 'product_cat', array( "fields" => "ids" ) );

                            if( count( array_intersect( $watermark_category, $categories ) )>0 ) {
                                $this->build_watermark_image( $original_image, $overlay, $size_name, $watermark );
                            }
                        }
                    }
                    else {

                        $this->build_watermark_image( $original_image, $overlay, $size_name, $watermark );

                    }
                }
                $quality_img = get_option( 'ywcwat_quality_jpg', 100 );
                if( $this->generateimagefrom( $original_image, $filepath, $type_img, $quality_img ) ) {
                    return 'create_watermark';
                }
                else {
                    return 'error_on_create_watermark';
                }


            }
            else {
                return 'file_not_exist';
            }
        }


        /**
         * @param $original_image
         * @param $overlay
         * @param $overlay_path
         * @param $size_name
         * @param $watermark
         */
        public function build_watermark_image( $original_image, $overlay, $size_name, $watermark )
        {


            $watermark_size = $watermark['ywcwat_watermark_sizes'];


            if( $size_name == $watermark_size && $original_image && $overlay ) {


                imagealphablending( $overlay, false );
                imagesavealpha( $overlay, true );

                $original_image_width = imagesx( $original_image );
                $original_image_height = imagesy( $original_image );
                $watermark_image_width = imagesx( $overlay );
                $watermark_image_height = imagesy( $overlay );

                $position_watermark = $watermark['ywcwat_watermark_position'];
                $margin_x_watermark = $watermark['ywcwat_watermark_margin_x'];
                $margin_y_watermark = $watermark['ywcwat_watermark_margin_y'];

                //scaled watermark
                if( $watermark_image_width>$original_image_width ) {

                    $coeff_ratio = $original_image_width / $watermark_image_width;


                    $watermark_image_width = intval( round( $watermark_image_width * $coeff_ratio ) );
                    $watermark_image_height = intval( round( $watermark_image_height * $coeff_ratio ) );

                    $wat_info = array();

                    $wat_info[] = imagesx( $overlay );
                    $wat_info[] = imagesy( $overlay );

                    $overlay = $this->resizeImage( $overlay, $watermark_image_width, $watermark_image_height, $wat_info );

                }

                switch ( $position_watermark ) {

                    case 'top_left':
                        $watermark_start_x = $margin_x_watermark;
                        $watermark_start_y = $margin_y_watermark;
                        break;
                    case 'top_center':
                        $watermark_start_x = ( $original_image_width / 2 )-( $watermark_image_width / 2 )+$margin_x_watermark;
                        $watermark_start_y = $margin_y_watermark;
                        break;
                    case 'top_right':
                        $watermark_start_x = $original_image_width-$watermark_image_width+$margin_x_watermark;
                        $watermark_start_y = $margin_y_watermark;
                        break;
                    case 'middle_left':
                        $watermark_start_x = $margin_x_watermark;
                        $watermark_start_y = ( $original_image_height / 2 )-( $watermark_image_height / 2 )+$margin_y_watermark;
                        break;
                    case 'middle_center':
                        $watermark_start_x = ( $original_image_width / 2 )-( $watermark_image_width / 2 )+$margin_x_watermark;
                        $watermark_start_y = ( $original_image_height / 2 )-( $watermark_image_height / 2 )+$margin_y_watermark;
                        break;
                    case 'middle_right':
                        $watermark_start_x = $original_image_width-$watermark_image_width+$margin_x_watermark;
                        $watermark_start_y = ( $original_image_height / 2 )-( $watermark_image_height / 2 )+$margin_y_watermark;
                        break;
                    case 'bottom_left':
                        $watermark_start_x = $margin_x_watermark;
                        $watermark_start_y = $original_image_height-$watermark_image_height-$margin_y_watermark;
                        break;
                    case 'bottom_center':
                        $watermark_start_x = ( $original_image_width / 2 )-( $watermark_image_width / 2 )+$margin_x_watermark;
                        $watermark_start_y = $original_image_height-$watermark_image_height-$margin_y_watermark;
                        break;

                    default:
                        /*position button right*/
                        $watermark_start_x = $original_image_width-$watermark_image_width-$margin_x_watermark;
                        $watermark_start_y = $original_image_height-$watermark_image_height-$margin_y_watermark;
                        break;

                }
                //imagecopy($original_image, $overlay, $watermark_start_x, $watermark_start_y, 0, 0, $watermark_image_width, $watermark_image_height);

                $repeat = ( isset( $watermark['ywcwat_watermark_repeat'] ) && ( $watermark['ywcwat_watermark_type'] !== 'type_text' ) );
                if( $repeat ) {
                    $this->repeat( $original_image, $overlay );
                }
                else {
                    imagecopy( $original_image, $overlay, $watermark_start_x, $watermark_start_y, 0, 0, $watermark_image_width, $watermark_image_height );
                }

            }
        }


        public function repeat( $original_image, $overlay )
        {

            $ww = imagesx( $overlay );
            $hh = imagesy( $overlay );
            $w = imagesx( $original_image );
            $h = imagesy( $original_image );

            $cur_w = 0;

            while ( $cur_w<$w ) {

                $cur_h = 0;
                while ( $cur_h<$h ) {
                    imagecopy( $original_image, $overlay, $cur_w, $cur_h, 0, 0, $ww, $hh );
                    $cur_h += $hh;
                }
                $cur_w += $ww;
            }
        }

        /**@author YITHEMES
         * @since 1.0.0
         * @param $watermark
         * @return resource
         */
        public function imagecreatefromtext( $original, $watermark )
        {

            $text = $watermark['ywcwat_watermark_text'];
            $font_name = YWCWAT_DIR . 'assets/fonts/' . $watermark['ywcwat_watermark_font'];

            $width = $watermark['ywcwat_watermark_width'];
            $height = $watermark['ywcwat_watermark_height'];
            $font_color = $watermark['ywcwat_watermark_font_color'];
            $bg_color = $watermark['ywcwat_watermark_bg_color'];
            $bg_opacity = $watermark['ywcwat_watermark_opacity'];

            $font_size = $watermark['ywcwat_watermark_font_size'];

            $width = round( imagesx( $original ) * ( $width / 100 ) );
            $height = round( imagesy( $original ) * ( $height / 100 ) );
            $line_height = $watermark['ywcwat_watermark_line_height'] == -1 ? $height / 2 : $watermark['ywcwat_watermark_line_height'];
            $angle = isset( $watermark['ywcwat_watermark_angle'] ) ? $watermark['ywcwat_watermark_angle'] : 0;
            $text_box_info = $this->calculateTextBox( $text, $font_name, $font_size, $angle );

            $img_only_text = imagecreatetruecolor( $width, $height );
            $bg_color = ywcwat_Hex2RGB( $bg_color );

            $bg_opacity = round( abs( ( ( (int)$bg_opacity-100 ) / 0.78740 ) ) );

            imagefill( $img_only_text, 0, 0, imagecolorallocatealpha( $img_only_text, $bg_color[0], $bg_color[1], $bg_color[2], $bg_opacity ) );

            $font_color = ywcwat_Hex2RGB( $font_color );

            $color = imagecolorallocate( $img_only_text, $font_color[0], $font_color[1], $font_color[2] );
            $this->write_multiline_text( $img_only_text, $font_name, $font_size, $color, $text, $line_height, $angle );
            //$this->image_multiline_text( $img_only_text,$font_size, $angle,0,$text_box_info['height'],$color,$font_name,$text, $width,100,$line_height );
            return $img_only_text;
        }

        private function calculateTextBox( $text, $fontFile, $fontSize, $fontAngle )
        {
            /************
             * simple function that calculates the *exact* bounding box (single pixel precision).
             * The function returns an associative array with these keys:
             * left, top:  coordinates you will pass to imagettftext
             * width, height: dimension of the image you have to create
             *************/
            $rect = imagettfbbox( $fontSize, $fontAngle, $fontFile, $text );
            $minX = min( array( $rect[0], $rect[2], $rect[4], $rect[6] ) );
            $maxX = max( array( $rect[0], $rect[2], $rect[4], $rect[6] ) );
            $minY = min( array( $rect[1], $rect[3], $rect[5], $rect[7] ) );
            $maxY = max( array( $rect[1], $rect[3], $rect[5], $rect[7] ) );


            return array(
                "left" => abs( $minX )-1,
                "top" => abs( $minY )-1,
                "width" => $maxX-$minX,
                "height" => $maxY-$minY,
                "box" => $rect
            );
        }

        /** split text in line
         * @author YITHEMES
         * @since 1.0.0
         * @param $font_size
         * @param $font
         * @param $text
         * @param $max_width
         * @return array
         */
        public function get_multiline_text( $font_size, $font, $text, $max_width, $angle )
        {
            $words = explode( " ", $text );
            $lines = array( $words[0] );
            $current_line = 0;
            for ( $i = 1; $i<count( $words ); $i++ ) {

                $dimension = $this->calculateTextBox( $lines[$current_line] . " " . $words[$i], $font, $font_size, $angle );

                $string_lenght = $dimension['width'];

                if( $string_lenght<$max_width ) {

                    $lines[$current_line] .= ' ' . $words[$i];
                }
                else {
                    $current_line++;
                    $lines[$current_line] = $words[$i];
                }
            }

            return $lines;
        }

        /** print single line in image
         * @author YITHEMES
         * @since 1.0.0
         * @param $image
         * @param $font
         * @param $font_size
         * @param $color
         * @param $text
         * @param $start_y
         * @param $line_height
         */
        public function write_multiline_text( $image, $font, $font_size, $color, $text, $line_height, $angle )
        {

            $image_w = imagesx( $image );
            $image_h = imagesy( $image );
            $lines = $this->get_multiline_text( $font_size, $font, $text, $image_w, $angle );
            $tot_line = count( $lines );

            foreach ( $lines as $line ) {

                $dim = $this->calculateTextBox( $line, $font, $font_size, $angle );

                if( $angle == 0 ) {

                    $text_width = $dim['width'];
                    $text_height = $dim['height'];
                    $text_height = $tot_line == 1 ? $text_height : $line_height;
                    $x = ceil( ( $image_w-$text_width ) / 2 );
                    $y = ceil( ( $image_h+$text_height ) / 2 )-( $tot_line-1 ) * $text_height;
                }
                else {

                    $dim = $dim['box'];
                    $x = ( $image_w / 2 )-( $dim[4]-$dim[0] ) / 2;
                    $y = ( ( $image_h / 2 )-( $dim[5]-$dim[1] ) / 2 );

                }
                $tot_line--;
                imagettftext( $image, $font_size, $angle, $x, $y, $color, $font, $line );


            }
        }


        /**
         * @author YITHEMES
         * @since 1.0.0
         * @param $im
         * @param $new_width
         * @param $new_height
         * @param $img_info
         * @return resource
         */
        private function resizeImage( $im, $new_width, $new_height, $img_info )
        {
            $newImg = imagecreatetruecolor( $new_width, $new_height );
            imagealphablending( $newImg, false );
            imagesavealpha( $newImg, true );
            $transparent = imagecolorallocatealpha( $newImg, 255, 255, 255, 127 );
            imagefilledrectangle( $newImg, 0, 0, $new_width, $new_height, $transparent );

            imagecopyresampled( $newImg, $im, 0, 0, 0, 0, $new_width, $new_height, $img_info[0], $img_info[1] );

            return $newImg;
        }

        /**return the current enable size
         * @author YITHEMES
         * @since 1.0.0
         * @return mixed|void
         */
        public function get_watermark_size( $watermark_id )
        {

            $watermark_option = get_option( 'ywcwat_watermark_select' );

            $current_key = '';
            foreach ( $watermark_option as $key => $option ) {


                if( $option['ywcwat_id'] == $watermark_id ) {

                    $current_key = $key;
                    continue;
                }
            }

            $this_watermark = $watermark_option[$current_key];

            $watermark_sizes = $this_watermark['ywcwat_watermark_sizes'];

            return $watermark_sizes;
        }

        /**@author YITHEMES
         * @since 1.0.0
         * @return array with woocommerce size name
         */
        public function get_woocommerce_size()
        {

            return array( 'shop_single', 'shop_catalog', 'shop_thumbnail', 'full' );
        }

        //single product : manage custom watermark

        /** add checkbox in product data header
         * @author YITHEMES
         * @since 1.0.0
         * @param $type_options
         * @return array
         */
        public function add_product_watermark_option( $type_options )
        {

            $watermark_option = array(
                'enable_watermark' => array(
                    'id' => '_ywcwat_product_enabled_watermark',
                    'wrapper_class' => '',
                    'label' => __( 'Watermark', 'yith-woocommerce-watermark' ),
                    'description' => __( 'Add custom watermark for this product', 'yith-woocommerce-watermark' ),
                    'default' => 'no'
                )
            );

            return array_merge( $type_options, $watermark_option );
        }

        /** print watermark tab in product data
         * @author YITHEMES
         * @since 1.0.0
         */
        public function print_watermark_panels()
        {

            ?>
            <style type="text/css">
                #woocommerce-product-data ul.wc-tabs .ywcwat_watermark_data_tab a:before {
                    content: '\e00c';
                    font-family: 'WooCommerce';
                    padding-right: 5px;

                }

            </style>
            <li class="ywcwat_watermark_data_tab show_if_custom_watermark_enabled">
                <a href="#ywcwat_watermark_data">
                    <?php _e( 'Watermark', 'yith-woocommerce-watermark' ); ?>
                </a>
            </li>


            <?php
            add_action( 'woocommerce_product_data_panels', array( $this, 'write_watermark_panels' ) );

        }

        /**include the watermark tab content
         * @author YITHEMES
         * @since 1.0.0
         */
        public function write_watermark_panels()
        {

            include_once( YWCWAT_TEMPLATE_PATH . 'metaboxes/product_watermark.php' );
        }

        /** save the watermark product meta
         * @author YITHEMES
         * @since 1.0.0
         * @param $post_id
         * @param $post
         */
        public function save_product_watermark_meta( $post_id, $post )
        {

            $product = wc_get_product( $post_id );
            if( isset( $_REQUEST['ywcwat_custom_watermark'] ) ) {

                $custom_watermark = $_REQUEST['ywcwat_custom_watermark'];
                yit_save_prop( $product, '_ywcwat_product_watermark', $custom_watermark );

            }
            else {
                yit_delete_prop( $product, '_ywcwat_product_watermark' );
            }

            if( isset( $_REQUEST['_ywcwat_product_enabled_watermark'] ) ) {

                yit_save_prop( $product, '_enable_watermark', 'yes' );
            }
            else {
                yit_delete_prop( $product, '_enable_watermark', 'no' );
            }


        }

        /**return gd version
         * @author YITHEMES
         * @since 1.0.0
         * @return mixed
         */
        public function get_gd_version()
        {
            $gd_version = gd_info();
            preg_match( '/\d/', $gd_version['GD Version'], $match );
            $gd_ver = $match[0];

            return $gd_ver;
        }

        /**when save a product, apply watermark
         * @author YITHEMES
         * @since 1.0.0
         */
        public function generate_watermark_after_change_image( $post_id, $attachment_id = -1 )
        {
            $post = get_post( $post_id );


            if( $post && ( $post->post_type == 'product' || $post->post_type == 'product_variation' ) ) {

                $product = wc_get_product( $post_id );
                $is_custom_enabled = yit_get_prop( $product, '_enable_watermark' ) == 'yes';

                $custom_watermark = yit_get_prop( $product, '_ywcwat_product_watermark', true );
                $watermark = null;


                if( $is_custom_enabled && !empty( $custom_watermark ) ) {
                    $watermark = $custom_watermark;
                }

                $product = wc_get_product( $post_id );

                $children = $product->get_children();

                $product_ids = array( $post_id );

                $product_ids = array_merge( $product_ids, $children );

                $attach_ids = ywcwat_get_attach_id_by_product( $product_ids );

                if( empty( $attach_ids ) && $attachment_id != -1 ) {
                    $object = new stdClass();
                    $object->meta_value = $attachment_id;
                    $attach_ids[] = $object;
                }
                foreach ( $attach_ids as $attach_id ) {

                    $single_attach = explode( ',', $attach_id->meta_value );

                    foreach ( $single_attach as $attach ) {

                        $fullsizepath = get_attached_file( $attach );
                        $backupfile = ywcwat_backup_file_name( $fullsizepath );

                        if( is_file( $fullsizepath ) ) {
                            if( !is_file( $backupfile ) ) {

                                ywcwat_backup_file( $fullsizepath );

                            }
                            $sizes_set = $this->get_woocommerce_size();

                            foreach ( $sizes_set as $size_name ) {

                                $result = $this->ywcwat_call_apply( $backupfile, $fullsizepath, $size_name, $attach, $watermark );
                            }
                        }
                    }

                }
            }
        }
    }
}