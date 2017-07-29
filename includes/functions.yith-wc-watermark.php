<?php
if( !defined('ABSPATH'))
    exit;

if( !function_exists( 'ywcwat_backup_file_name')){

    function ywcwat_backup_file_name( $original_file_name ){

        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        $backup_url   = $upload_dir . '/' .YWCWAT_PRIVATE_DIR ;
        $sub_directory = str_replace( $upload_dir, '', dirname( $original_file_name ) );
        $file_name = str_replace( YWCWAT_BACKUP_FILE, '', basename( $original_file_name ) );

        $backup_file_name = $backup_url . $sub_directory . '/' .  $file_name ;

        return $backup_file_name;
    }
}

if( !function_exists( 'ywcwat_is_previous_backup_exist')){

    function ywcwat_is_previous_backup_exist( $file_name ){

        $backup_file_name = dirname( $file_name ) . '/' . YWCWAT_BACKUP_FILE . basename( $file_name );

        if( file_exists( $backup_file_name ) ){
            return $backup_file_name;
        }
        else
            return '';
    }
}

if( !function_exists( 'ywcwat_backup_file' ) ){

    function ywcwat_backup_file( $original_file ){

        $original_file = str_replace( 'jpeg', 'jpg', $original_file );
        $file_name = ywcwat_is_previous_backup_exist( $original_file );

       
        if(  $file_name !=='' ){
            $original_file = $file_name;
        }

        $backup_file = ywcwat_backup_file_name( $original_file );

        if( is_file( $original_file ) && !is_file( $backup_file ) ){

            if( !is_dir( dirname( $backup_file ) ) ){
                
                wp_mkdir_p( dirname( $backup_file ) );
            }
            $result = copy( $original_file, $backup_file );

            //if exist delete old backup file ( previous plugin version )
          /*  if(  $file_name !=='' )
                unlink( $file_name );
*/
            return $result;
        }

        return false;
    }
}


if( !function_exists( 'ywcwat_get_all_product_attach')){

    function ywcwat_get_all_product_attach(){

        global $wpdb;

        /*$result = $wpdb->get_results(

            "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts}
             WHERE {$wpdb->posts}.post_type='attachment' AND post_mime_type LIKE 'image/%'
            AND {$wpdb->posts}.ID IN (
                                  SELECT DISTINCT {$wpdb->postmeta}.meta_value FROM {$wpdb->postmeta} INNER JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id= {$wpdb->posts}.ID
                                  WHERE {$wpdb->postmeta}.meta_key= '_thumbnail_id' AND {$wpdb->posts}.post_type IN ('product', 'product_variation')  )
                ORDER BY {$wpdb->posts}.ID ASC" );
        */

        $result = $wpdb->get_results(
            "SELECT DISTINCT pm.meta_value as ID
                            FROM {$wpdb->postmeta} AS pm
                            INNER JOIN {$wpdb->posts} AS pr
                            ON pm.post_id= pr.ID 
                            INNER JOIN {$wpdb->posts} AS at
                            ON pm.meta_value = at.ID
                            WHERE pm.meta_key= '_thumbnail_id'
                            AND pr.post_type IN ('product', 'product_variation')
                            AND at.post_type='attachment'
                            AND at.post_mime_type LIKE 'image/%'
                            ORDER BY `meta_value` ASC"
        );

        return $result;
    }
}

if( !function_exists( 'ywcwat_generate_backup' ) ){

    function ywcwat_generate_backup()
    {
        if ( isset( $_GET[ 'gen_backup' ] ) && 'yes' == $_GET[ 'gen_backup' ] ) {

            create_private_directory();
            $attach_ids = ywcwat_get_all_product_attach();

            foreach ( $attach_ids as $attach_id ) {


                $file_path = get_attached_file( $attach_id->ID );

                ywcwat_backup_file( $file_path );
            }

            if ( function_exists( 'ywcwat_generate_backup_product_img_gallery' ) ) {
                ywcwat_generate_backup_product_img_gallery();
            }

            $redirect_url = remove_query_arg('gen_backup');
            $redirect_url = add_query_arg( array('bakup_success'=>'yes'), $redirect_url );
            wp_redirect( esc_url_raw( $redirect_url ) );
            die;
        }
    }
}

add_action( 'admin_init', 'ywcwat_generate_backup' );

if( !function_exists('create_private_directory')) {
    /**
     * create a private directory (if not exist)
     * @author YITHEMES
     * @since 1.0.7
     */
    function create_private_directory()
    {

        $upload_dir = wp_upload_dir();
        $backup_url = $upload_dir[ 'basedir' ] . '/' . YWCWAT_PRIVATE_DIR;

        if ( !is_dir( $backup_url ) ) {

            wp_mkdir_p( $backup_url );
        }
        if ( !file_exists( $backup_url . '/.htaccess' ) ) {
            if ( $file_handle = @fopen( $backup_url . '/.htaccess', 'w' ) ) {
                fwrite( $file_handle, 'deny from all' );
                fclose( $file_handle );
            }
        }

        if ( !file_exists( $backup_url . '/index.html' ) ) {
            if ( $file_handle = @fopen( $backup_url . '/index.html', 'w' ) ) {
                fwrite( $file_handle, '' );
                fclose( $file_handle );
            }
        }
    }
}
add_action('admin_init','create_private_directory' );
