<?php

if( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Implements free features of YIT WooCommerce Watermark plugin
 *
 * @class   YITH_WC_Watermark
 * @package YITHEMES
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */

if( !class_exists( 'YITH_WC_Watermark' ) ) {

    class YITH_WC_Watermark
    {

        /**
         * @var YITH_WC_Watermark single instance of class
         */
        protected static $_instance;
        /**
         * Panel object
         *
         * @var     /Yit_Plugin_Panel object
         * @since   1.0.0
         * @see     plugin-fw/lib/yit-plugin-panel.php
         */
        protected $_panel;

        /**
         * @var $_premium string Premium tab template file name
         */
        protected $_premium = 'premium.php';

        /**
         * @var string Premium version landing link
         */
        protected $_premium_landing_url = 'https://yithemes.com/themes/plugins/yith-woocommerce-watermark/';

        /**
         * @var string Plugin official documentation
         */
        protected $_official_documentation = 'https://yithemes.com/docs-plugins/yith-woocommerce-watermark/';

        /**
         * @var string plugin official live demo
         */
        protected $_premium_live_demo = 'http://plugins.yithemes.com/yith-woocommerce-watermark/';

        /**
         * @var string Yith WooCommerce Watermark panel page
         */
        protected $_panel_page = 'yith_ywcwat_panel';

        /**
         * @var string suffix for load minified js
         */
        protected $_suffix;

        public function __construct()
        {

            add_action( 'admin_notices', array( $this, 'show_message_to_user' ) );
            add_action( 'admin_init', array( $this, 'hide_message_for_user' ) );
            // Load Plugin Framework
            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );
            //Add action links
            add_filter( 'plugin_action_links_' . plugin_basename( YWCWAT_DIR . '/' . basename( YWCWAT_FILE ) ), array( $this, 'action_links' ) );
            //Add row meta
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
            //Add tab premium
            add_action( 'yith_wc_watermark_premium', array( $this, 'premium_tab' ) );
            //Add Yith Watermark menu
            add_action( 'admin_menu', array( $this, 'add_ywcwat_menu' ), 5 );

            //add ajax action for apply all watermark
            add_action( 'wp_ajax_yith_apply_all_watermark', array( $this, 'yith_apply_all_watermark' ) );
            add_action( 'wp_ajax_nopriv_yith_apply_all_watermark', array( $this, 'yith_apply_all_watermark' ) );

            //add ajax action for remove watermark
            add_action( 'wp_ajax_ywcwat_remove_watermark', array( $this, 'ywcwat_remove_watermark' ) );
            add_action( 'wp_ajax_nopriv_ywcwat_remove_watermark', array( $this, 'ywcwat_remove_watermark' ) );


            $this->_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            if( is_admin() ) {

                $this->__includes();
                //Add custom type in plugin option
                add_action( 'woocommerce_admin_field_watermark-select', 'YITH_Watermark_Select::output' );
                add_action( 'woocommerce_admin_field_custom-button', array( $this, 'show_backup_btn' ) );
                add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_free_scripts' ) );
            }

            //apply  watermark when save a product
            add_action( 'save_post', array( $this, 'generate_watermark_on_save_product' ), 10, 1 );
            add_filter( 'wp_generate_attachment_metadata', array( $this, 'generate_watermark_on_attach_image' ), 10, 2 );

        }

        /** return single instance of class
         * @author YITHEMES
         * @since 1.0.0
         * @return YITH_WC_Watermark
         */

        public static function get_instance()
        {

            if( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }


        /**include files
         * @author YITHEMES
         * @since 1.0.0
         */
        private function __includes()
        {

            include_once( YWCWAT_TEMPLATE_PATH . '/admin/watermark-select.php' );
        }

        public function plugin_fw_loader()
        {
            if( !defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if( !empty( $plugin_fw_data ) ) {
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
        }

        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @param $links | links plugin array
         *
         * @return   mixed Array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return mixed
         * @use plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links )
        {

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-watermark' ) . '</a>';

            $premium_live_text = defined( 'YWCWAT_FREE_INIT' ) ? __( 'Premium live demo', 'yith-woocommerce-watermark' ) : __( 'Live demo', 'yith-woocommerce-watermark' );

            $links[] = '<a href="' . $this->_premium_live_demo . '" target="_blank">' . $premium_live_text . '</a>';

            if( defined( 'YWCWAT_FREE_INIT' ) ) {
                $links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'yith-woocommerce-watermark' ) . '</a>';
            }

            return $links;
        }

        /**
         * plugin_row_meta
         *
         * add the action links to plugin admin page
         *
         * @param $plugin_meta
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         *
         * @return   Array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use plugin_row_meta
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status )
        {
            if( ( defined( 'YWCWAT_INIT' ) && ( YWCWAT_INIT == $plugin_file ) ) ||
                ( defined( 'YWCWAT_FREE_INIT' ) && ( YWCWAT_FREE_INIT == $plugin_file ) )
            ) {

                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-watermark' ) . '</a>';
            }

            return $plugin_meta;
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri()
        {
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing_url . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing_url . '?refer_id=1030585';
        }

        /**
         * Premium Tab Template
         *
         * Load the premium tab template on admin page
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  void
         */
        public function premium_tab()
        {
            $premium_tab_template = YWCWAT_TEMPLATE_PATH . '/admin/' . $this->_premium;
            if( file_exists( $premium_tab_template ) ) {
                include_once( $premium_tab_template );
            }
        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function add_ywcwat_menu()
        {
            if( !empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = apply_filters( 'ywcwat_add_premium_tab', array(
                'general-settings' => __( 'Settings', 'yith-woocommerce-watermark' ),
                'premium-landing' => __( 'Premium Version', 'yith-woocommerce-watermark' )
            ) );

            $args = array(
                'create_menu_page' => true,
                'parent_slug' => '',
                'page_title' => __( 'Watermark', 'yith-woocommerce-watermark' ),
                'menu_title' => __( 'Watermark', 'yith-woocommerce-watermark' ),
                'capability' => 'manage_options',
                'parent' => '',
                'parent_page' => 'yit_plugin_panel',
                'page' => $this->_panel_page,
                'admin-tabs' => $admin_tabs,
                'options-path' => YWCWAT_DIR . '/plugin-options'
            );

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
        }

        /** Set a limit in percentage for watermark size
         * @author YITHEMES
         * @since 1.0.1
         * @return mixed|void
         */
        public function get_perc_size()
        {

            return apply_filters( 'ywcwat_perc_size', 25 );
        }

        /** apply the watermark on 15 products at a time.
         * @author YITHEMES
         * @since 1.0.0
         * @return mixed|void
         */
        public function get_max_item_task()
        {
            return apply_filters( 'ywcwat_max_item_task', 15 );
        }

        /**include free style and free script in admim
         * @author YITHEMES
         * @since 1.0.0
         */
        public function admin_enqueue_free_scripts()
        {
            if( isset( $_GET['page'] )  && 'yith_ywcwat_panel' == $_GET['page'] ) {
                wp_enqueue_script( 'ywcwat_free_admin_script', YWCWAT_ASSETS_URL . 'js/ywcwat_admin' . $this->_suffix . '.js', array( 'jquery' ), YWCWAT_VERSION, true );
                wp_enqueue_script( 'jquery-ui-progressbar' );


                $size = wc_get_image_size( 'shop_single' );

                $perc_size = $this->get_perc_size();

                $max_w = round( ( $size['width'] * $perc_size ) / 100 );
                $max_h = round( ( $size['height'] * $perc_size ) / 100 );

                $ywcwat_params = array(
                    'ajax_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
                    'attach_id' => $this->get_ids_attach(),
                    'max_item_action' => $this->get_max_item_task(),
                    'perc_w' => intval( $max_w ),
                    'perc_h' => intval( $max_h ),
                    'messages' => array(
                        'complete_single_task' => __( 'The watermark has been applied to', 'yith-woocommerce-watermark' ),
                        'single_product' => __( 'Product', 'yith-woocommerce-watermark' ),
                        'on' => __( 'on', 'yith-woocommerce-watermark' ),
                        'more_product' => __( 'Products', 'yith-woocommerce-watermark' ),
                        'complete_all_task' => __( 'Completed', 'yith-woocommerce-watermark' ),
                        'error_watermark_sizes' => sprintf( '%s %s %s %s x %s .', __( 'You can\'t use images bigger than', 'yith-woocommerce-watermark' ),

                            $perc_size . '%',
                            __( 'of the size of the "Single Product image", that is', 'yith-woocommerce-watermark' ),
                            $max_w,
                            $max_h ),
                        'reset_confirm' => __( 'Images will be restored, are you sure? ', 'yith-woocommerce-watermark' ),
                        'singular_success_image' => __( 'Image has been deleted', 'yith-woocommerce-watermark' ),
                        'plural_success_image' => __( 'Images have been deleted', 'yith-woocommerce-watermark' ),
                        'singular_error_image' => __( 'Image has not been deleted', 'yith-woocommerce-watermark' ),
                        'plural_error_image' => __( 'Images have not been deleted', 'yith-woocommerce-watermark' ),
                    ),
                    'actions' => array(
                        'apply_all_watermark' => 'yith_apply_all_watermark',
                        'remove_watermark' => 'ywcwat_remove_watermark',
                        'change_thumbnail_image' => 'change_thumbnail_image'
                    )
                );

                wp_localize_script( 'ywcwat_free_admin_script', 'ywcwat_params', $ywcwat_params );
                wp_enqueue_style( 'ywcwat_free_admin_style', YWCWAT_ASSETS_URL . 'css/ywcwat_admin.css', array(), YWCWAT_VERSION );
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

            return json_encode( $ids );

        }

        /** call ajax, apply watermark to single attach
         * @author YITHEMES
         * @since 1.0.0
         */
        public function yith_apply_all_watermark()
        {

            if( isset( $_REQUEST['ywcwat_attach_id'] ) ) {


                $attach_id = $_REQUEST['ywcwat_attach_id'];
                $fullsizepath = get_attached_file( $attach_id );
                $backupfile = ywcwat_backup_file_name( $fullsizepath );

                if( is_file( $fullsizepath ) ) {
                    if( !is_file( $backupfile ) ) {

                        // copy( $fullsizepath, $backupfile );
                        ywcwat_backup_file( $fullsizepath );
                    }

                    $sizes_set = $this->get_woocommerce_size();


                    foreach ( $sizes_set as $size_name ) {


                        $result = $this->ywcwat_call_apply( $backupfile, $fullsizepath, $size_name, $attach_id, null );

                    }
                    wp_send_json( array( 'result' => $result ) );
                }
                else {
                    wp_send_json( array( 'result' => 'skip' ) );
                }
            }

        }

        /** resize the original image and call save image with watermark
         * @author YITHEMES
         * @since 1.0.0
         * @param $path
         * @param $size_name
         * @param $attach_id
         * @return string
         */
        public function ywcwat_call_apply( $backup_path, $path, $size_name, $attach_id, $watermark = null )
        {

            $new_path = '';

            if( $path == false ) {
                return 'empty_path';
            }
            if( $size_name != 'full' ) {

                $img = wp_get_image_editor( $backup_path );


                if( is_wp_error( $img ) ) {
                    return 'error_get_img_editor';
                }

                $size = wc_get_image_size( $size_name );

                $crop = isset( $size['crop'] ) && $size['crop'] == 1;
                $img->resize( $size['width'], $size['height'], $crop );

                $info = pathinfo( $path );

                $dir = $info['dirname'];
                $ext = $info['extension'];
                $suffix = $img->get_suffix();
                $name = wp_basename( $path, ".$ext" );
                $dest_file = trailingslashit( $dir ) . "{$name}-{$suffix}.{$ext}";


                $saved = $img->save( $dest_file );

                if( is_wp_error( $saved ) ) {
                    return 'error_save_img_resized';
                }

                $new_path = $saved['path'];
            }
            else {

                copy( $backup_path, $path );
                $new_path = $path;
            }


            $result = $this->save_image_with_watermark( $new_path, $attach_id, $size_name, $watermark );

            return $result;


        }


        /**restore all original image
         * @author YITHEMES
         * @since 1.0.0
         *
         */
        public function ywcwat_remove_watermark()
        {

            if( isset( $_REQUEST['ywcwat_remove_watermark'] ) ) {

                $count = array( 'success' => 0, 'error' => 0 );

                $wp_upload_dir = wp_upload_dir();
                $uploads_dir = $wp_upload_dir['basedir'];
                $backup_dir = $wp_upload_dir['basedir'] . '/' . YWCWAT_PRIVATE_DIR;

                $prefix = YWCWAT_BACKUP_FILE;

                foreach ( scandir( $backup_dir ) as $yfolder ) {
                    if( !( is_dir( "$backup_dir/$yfolder" ) && !in_array( $yfolder, array( '.', '..' ) ) ) ) {
                        continue;
                    }

                    $yfolder = basename( $yfolder );
                    foreach ( scandir( "$backup_dir/$yfolder" ) as $mfolder ) {
                        if( !( is_dir( "$backup_dir/$yfolder/$mfolder" ) && !in_array( $mfolder, array( '.', '..' ) ) ) ) {
                            continue;
                        }

                        $mfolder = basename( $mfolder );
                        $images = (array)glob( "$backup_dir/$yfolder/$mfolder/*.{jpg,png,gif}", GLOB_BRACE );
                        foreach ( $images as $image ) {

                            // $filename = str_replace( $prefix, '', $image );
                            $filename = basename( $image );
                            $dest_dir = "$uploads_dir/$yfolder/$mfolder/$filename";

                            if( copy( $image, $dest_dir ) ) {
                                $count['success']++;
                            }
                            else {
                                $count['error']++;
                            }
                        }
                    }
                }

                wp_send_json( array( 'success' => $count['success'], 'error' => $count['error'] ) );

            }
        }

        /** create new image from different type (by path)
         * @author YITHEMES
         * @since 1.0.0
         * @param $path
         * @param $type
         * @return bool|resource
         */
        protected function createimagefrom( $path, $type )
        {

            $original_image = false;
            switch ( $type ) {

                case 'jpeg' :
                case 'jpg':

                    $original_image = imagecreatefromjpeg( $path );
                    break;
                case 'gif':
                    $original_image = imagecreatefromgif( $path );
                    break;
                case 'png':
                    $original_image = imagecreatefrompng( $path );
                    break;
            }


            return $original_image;
        }

        /** generate new image from different type
         * @author YITHEMES
         * @param $original_image
         * @param $path
         * @param $type
         * @param $quality
         * @return bool
         */
        protected function generateimagefrom( $original_image, $path, $type, $quality )
        {


            $result = false;
            switch ( $type ) {

                case 'jpeg':
                case 'jpg' :
                    $result = imagejpeg( $original_image, $path, $quality );
                    break;
                case 'gif':
                    $result = imagegif( $original_image, $path );
                    break;
                case 'png':
                    /* conversion quality from jpeg (0-100)  to png(0-9)
                     *
                     */
                    $new_quality = ( $quality-100 ) / 11.111111;
                    $new_quality = round( abs( $new_quality ) );
                    $result = imagepng( $original_image, $path, $new_quality );
                    break;
            }

            return $result;
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
                $watermarks = get_option( 'ywcwat_watermark_select' );

                foreach ( $watermarks as $key => $watermark ) {

                    $watermark_path = get_attached_file( $watermark['ywcwat_watermark_id'] );
                    $overlay = imagecreatefrompng( $watermark_path );

                    $this->build_watermark_image( $original_image, $overlay, $size_name, $watermark );

                }

                if( $this->generateimagefrom( $original_image, $filepath, $type_img, 100 ) ) {
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

        /** print watermark in product image
         * @author YITHEMES
         * @since 1.0.0
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

                /*position button right*/
                $watermark_start_x = $original_image_width-$watermark_image_width-20;
                $watermark_start_y = $original_image_height-$watermark_image_height-20;

                imagecopy( $original_image, $overlay, $watermark_start_x, $watermark_start_y, 0, 0, $watermark_image_width, $watermark_image_height );

            }
        }

        /**return the current enable size
         * @author YITHEMES
         * @since 1.0.0
         * @return mixed|void
         */
        public function get_woocommerce_size()
        {

            return array( 'shop_single' );

        }

        /**return all watermark id
         * @author YITHEMES
         * @since 1.0.0
         * @return array
         */
        public function get_watermark_ids()
        {

            $watermark = get_option( 'ywcwat_watermark_select' );

            $ids = array();

            if( $watermark ) {
                foreach ( $watermark as $value ) {

                    $ids [] = $value['ywcwat_watermark_id'];
                }
            }
            return $ids;
        }

        /**when change featured image in edit product, apply watermark
         * @author YITHEMES
         * @since 1.0.0
         */
        public function generate_watermark_after_change_image( $post_id, $attachment_id = -1 )
        {
            $post_type = get_post_type( $post_id );

            if( $post_type == 'product' || $post_type == 'product_variation' ) {

                if( $attachment_id == -1 ) {
                    $attach_id = get_post_thumbnail_id( $post_id );
                }
                else {
                    $attach_id = $attachment_id;
                }
                $fullsizepath = get_attached_file( $attach_id );
                $backupfile = ywcwat_backup_file_name( $fullsizepath );

                if( is_file( $fullsizepath ) ) {

                    if( !is_file( $backupfile ) ) {

                        ywcwat_backup_file( $fullsizepath );
                    }

                    $sizes_set = $this->get_woocommerce_size();

                    foreach ( $sizes_set as $size_name )
                        $result = $this->ywcwat_call_apply( $backupfile, $fullsizepath, $size_name, $attach_id, null );
                }
            }

        }

        /**
         * @author YITHEMES
         * @since 1.0.11
         * @param $post_id
         */
        public function generate_watermark_on_save_product( $post_id )
        {

            $this->generate_watermark_after_change_image( $post_id );
        }

        /**
         * @author YITHEMES
         * @since 1.0.11
         * @param $metadata
         * @param $attachment_id
         * @return mixed
         */
        public function generate_watermark_on_attach_image( $metadata, $attachment_id )
        {

            if( isset( $_REQUEST['post_id'] ) && $_REQUEST['post_id'] != 0 ) {

                $post_id = $_REQUEST['post_id'];
                $this->generate_watermark_after_change_image( $post_id, $attachment_id );
            }

            return $metadata;
        }

        /**
         * @author YITHEMES
         * @since 1.0.7
         */
        public function show_message_to_user()
        {

            global $current_user;

            if( isset( $_GET['page'] ) && 'yith_ywcwat_panel' === $_GET['page'] ) {

                $user_id = $current_user->ID;

                $show_message = get_user_meta( $user_id, '_ywcwat_showmessage', true );
                $args = array(
                    'page' => 'yith_ywcwat_panel',
                    'show_notice' => 'no'
                );

                if( isset( $_GET['tab'] ) && 'watermark-list' === $_GET['tab'] ) {
                    $args['tab'] = 'watermark-list';
                }

                $url = esc_url( add_query_arg( $args, admin_url( 'admin.php' ) ) );

                if( $show_message === '' ) {

                    $upload_dir = wp_upload_dir();
                    $upload_dir = $upload_dir['basedir'];
                    $message = sprintf( '%s <strong>%s</strong>', __( 'From version 1.0.7 all your product backed up images are available at', 'yith-woocommerce-watermark' ), $upload_dir . '/yith_watermark_backup' );
                    ?>
                    <div class="notice notice-info" style="padding-right: 38px;position: relative;">
                        <p><?php echo $message; ?></p>
                        <a class="notice-dismiss" href="<?php echo $url; ?>" style="text-decoration: none;"></a>

                    </div>
                    <?php
                }

                if( isset( $_GET['bakup_success'] ) && 'yes' == $_GET['bakup_success'] ) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php _e( 'Backup completed!', 'yith-woocommerce-watermark' ); ?></p>
                    </div>
                    <?php
                }
            }
        }

        /**
         * @author YITHEMES
         * @since 1.0.7
         */
        public function hide_message_for_user()
        {

            global $current_user;

            $user_id = $current_user->ID;
            if( isset( $_GET['show_notice'] ) ) {

                update_user_meta( $user_id, '_ywcwat_showmessage', 'no' );
            }

        }

        /**
         * @author YITHEMES
         * @since 1.0.9
         */
        public function show_backup_btn()
        {

            wc_get_template( 'admin/custom-button.php', array(), '', YWCWAT_TEMPLATE_PATH );
        }
    }
}