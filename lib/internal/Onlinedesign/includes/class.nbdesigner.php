<?php

class Nbdesigner_Plugin
{
    public $textdomain;
    public $plugin_id;
    public $activedomain;
    public $removedomain;
    public function __construct() {
        $this->plugin_id = 'nbdesigner';
        $this->activedomain = 'activedomain/netbase/';
        $this->removedomain = 'removedomain/netbase/';   
    }
    public function init(){
        $this->hook();
        $this->schehule();
        $this->nbdesigner_lincense_notices();    
        add_action( 'nbdesigner_lincense_event', array($this, 'nbdesigner_lincense_event_action') ); 
        if (in_array('woocommerce/woocommerce.php',get_option('active_plugins')) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' )) {
            $this->woocommerce_hook();
        }
        if (is_admin()) {
            $this->admin_hook();           
            $this->ajax();           
            
        } else {    
            $this->frontend_hook();    
        }
    }
    public function ajax(){
        // Nbdesigner_EVENT => nopriv
        $ajax_events = array(
            'nbdesigner_add_font_cat' => false,
            'nbdesigner_add_art_cat' => false,
            'nbdesigner_add_google_font' => false,
            'nbdesigner_delete_font_cat' => false,
            'nbdesigner_delete_art_cat' => false,
            'nbdesigner_delete_font' => false,
            'nbdesigner_delete_art' => false,
            'nbdesigner_get_product_info' => true,
            'nbdesigner_get_qrcode' => true,
            'nbdesigner_get_facebook_photo' => true,
            'nbdesigner_get_art' => true,
            'nbdesigner_design_approve' => false,
            'nbdesigner_design_order_email' => false,
            'nbdesigner_customer_upload' => true,
            'nbd_upload_design_file' => true,
            'nbdesigner_get_font' => true,
            'nbdesigner_get_pattern' => true,
            'nbdesigner_get_info_license' => false,
            'nbdesigner_remove_license' => false,
            'nbdesigner_get_license_key' => false,
            'nbdesigner_get_language' => true,
            'nbdesigner_save_language' => false,
            'nbdesigner_create_language' => false,
            'nbdesigner_make_primary_design' => false,
            'nbdesigner_load_admin_design' => true,
            'nbdesigner_save_webcam_image' => true,
            'nbdesigner_migrate_domain' => false,
            'nbdesigner_restore_data_migrate_domain' => false,
            'nbdesigner_theme_check' => false,
            'nbdesigner_custom_css' => false,
            'nbdesigner_custom_js' => false,
            'nbdesigner_copy_image_from_url' => true,
            'nbdesigner_get_suggest_design' => true,
            'nbdesigner_save_design_to_pdf' => false,
            'nbdesigner_delete_language' => false,
            'nbdesigner_update_all_product' => false,
            'nbd_save_customer_design' => true,
            'nbd_remove_design_and_file' => true,
            'nbd_save_draft_design' => true,
            'nbd_update_customer_upload' => true,
            'nbd_save_cart_design' => true,
            'nbd_get_nbd_products' => true,
            'nbd_remove_cart_design' => true,
            'nbd_get_product_description' => true,
            'nbd_convert_files' => true,
            'nbd_frontend_download_pdf' => true,
            'nbd_frontend_download_jpeg' => true,
            'nbd_download_final_pdf' => true,
            'nbdesigner_update_variation_v180'  =>  false,
            'nbdesigner_update_all_template'  =>  false,
            'nbd_clear_transients'  =>  false,
            'nbd_create_pages'  =>  false,
            'nbd_get_product_config'  =>  true,
            'nbd_delete_order_design'  =>  false,
            'nbd_check_use_logged_in'  =>  true,
            'nbd_crop_image'  =>  true,
            /* cuz */
            'nbd_get_users_by_country_code'  =>  true,
            'nbd_get_resource' => true,
            'nbd_get_designs_in_cart' => true,
            'nbd_check_use_logged_in' => true,
            'nbd_save_for_later' => true,
            'nbd_get_user_designs' => true
        );
    foreach ($ajax_events as $ajax_event => $nopriv) {
            add_action('wp_ajax_' . $ajax_event, array($this, $ajax_event));
            if ($nopriv) {
                // NBDesigner AJAX can be used for frontend ajax requests
                add_action('wp_ajax_nopriv_' . $ajax_event, array($this, $ajax_event));
            }
        }
    }
    /**
     * Register new endpoint to use inside My Account page.
     *
     * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
     */
    public function add_endpoints() {
        foreach ( $this->query_vars as $var ){
            add_rewrite_endpoint($var, EP_ROOT | EP_PAGES);
        }
    }

    /**
     * Add new query var.
     *
     * @param array $vars
     * @return array
     */
    public function add_query_vars($vars) {
        foreach ( $this->query_vars as $var ){
            $vars[] = $var;
        }
        return $vars;
    }

    /**
     * Set endpoint title.
     *
     * @param string $title
     * @return string
     */
    public function endpoint_title($title) {
        global $wp_query;
        foreach ( $this->query_vars as $var ){
            $is_endpoint = isset($wp_query->query_vars[$var]);
            if ($is_endpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
                switch ( $var ) {
                    case 'my-designs': 
                        $title = __('My designs', 'web-to-print-online-designer');
                        break;
                    case 'view-design': 
                        $title = __('View design', 'web-to-print-online-designer');
                        break;     
                    case 'artist-info': 
                        $title = __('Artist info', 'web-to-print-online-designer');
                        break;                      
                }
                remove_filter('the_title', array($this, 'endpoint_title'));
            }
        }
        return $title;
    }

    /**
     * Insert the new endpoint into the My Account menu.
     *
     * @param array $items
     * @return array
     */
    public function new_menu_items($items) {
        // Remove the logout menu item.
        $logout = $items['customer-logout'];
        unset($items['customer-logout']);

        // Insert your custom endpoint.
        $items[self::$endpoint] = __('My designs', 'web-to-print-online-designer');

        // Insert back the logout item.
        $items['customer-logout'] = $logout;

        return $items;
    }

    /**
     * Endpoint HTML content.
     */
    public function endpoint_content_my_designs() {
        global $wp;
        $current_page = absint($wp->query_vars['my-designs']);
        if( !$current_page ) $current_page = 1;
        $user = wp_get_current_user();
        $user_id = $user->ID;
        $item_per_page = 10;
        $item_per_page = apply_filters('nbd_number_design_per_page', $item_per_page);        
        $designs = $this->get_my_designs($user_id, $current_page, $item_per_page);
        $number_design = $this->count_designs($user_id);
        ob_start();
        nbdesigner_get_template('mydesign/my-designs.php', array(
            'user' => $user, 
            'designs'  =>  $designs, 
            'total' => $number_design, 
            'item_per_page' =>  $item_per_page,
            'current_page' => $current_page ));
        $content = ob_get_clean();
        echo $content;
    }
    public function endpoint_content_view_design() {
        global $wp;
        $did = absint($wp->query_vars['view-design']);
        $user = wp_get_current_user();
        $user_id = $user->ID;        
        $design = $this->get_design($user_id, $did);
        ob_start();
        nbdesigner_get_template('mydesign/detail-design.php', array( 'design'  =>  $design, 'user_id' => $user_id ));
        $content = ob_get_clean();
        echo $content;        
    }
    public function endpoint_content_artist_info() {
        ob_start();
        nbdesigner_get_template('mydesign/edit-info.php', array( ));
        $content = ob_get_clean();
        echo $content;           
    }
    public function user_profile( $user ) {
        wp_nonce_field( 'nbd_user_profile_update', 'nbd_nonce' );
        require_once NBDESIGNER_PLUGIN_DIR . 'views/user-profile.php';
    }
    /**
     * Filter POST variables.
     *
     * @param string $var_name Name of the variable to filter.
     *
     * @return mixed
     */
    private function filter_input_post( $var_name ) {
        $val = filter_input( INPUT_POST, $var_name );
        if ( $val ) {
            return sanitize_text_field( $val );
        }
        return '';
    }    
    /**
     * Updates the user metas that (might) have been set on the user profile page.
     *
     * @param    int $user_id of the updated user.
     */
    public function process_user_option_update( $user_id ) {
        update_user_meta( $user_id, '_nbd_profile_updated', time() );

        $nonce_value = $this->filter_input_post( 'nbd_nonce' );

        if ( empty( $nonce_value ) ) { // Submit from alternate forms.
                return;
        }
        check_admin_referer( 'nbd_user_profile_update', 'nbd_nonce' );
        update_user_meta( $user_id, 'nbd_artist_name', $this->filter_input_post( 'nbd_artist_name' ) );
        update_user_meta( $user_id, 'nbd_artist_phone', $this->filter_input_post( 'nbd_artist_phone' ) );
        update_user_meta( $user_id, 'nbd_sell_design', $this->filter_input_post( 'nbd_sell_design' ) );
        update_user_meta( $user_id, 'nbd_create_design', $this->filter_input_post( 'nbd_create_design' ) );
        update_user_meta( $user_id, 'nbd_artist_banner', $this->filter_input_post( 'nbd_artist_banner' ) );
        update_user_meta( $user_id, 'nbd_artist_address', $this->filter_input_post( 'nbd_artist_address' ) );
        update_user_meta( $user_id, 'nbd_artist_facebook', $this->filter_input_post( 'nbd_artist_facebook' ) );
        update_user_meta( $user_id, 'nbd_artist_google', $this->filter_input_post( 'nbd_artist_google' ) );
        update_user_meta( $user_id, 'nbd_artist_twitter', $this->filter_input_post( 'nbd_artist_twitter' ) );
        update_user_meta( $user_id, 'nbd_artist_linkedin', $this->filter_input_post( 'nbd_artist_linkedin' ) );
        update_user_meta( $user_id, 'nbd_artist_youtube', $this->filter_input_post( 'nbd_artist_youtube' ) );
        update_user_meta( $user_id, 'nbd_artist_instagram', $this->filter_input_post( 'nbd_artist_instagram' ) );
        update_user_meta( $user_id, 'nbd_artist_flickr', $this->filter_input_post( 'nbd_artist_flickr' ) );
        update_user_meta( $user_id, 'nbd_artist_commission', $this->filter_input_post( 'nbd_artist_commission' ) );
        update_user_meta( $user_id, 'nbd_artist_description', $this->filter_input_post( 'nbd_artist_description' ) );
        $this->delete_designs_transient();
    }    
    public function nbd_update_artist_name(){
        if (!wp_verify_nonce($_POST['nbd_nonce'], 'nbd_artist_update') && NBDESIGNER_ENABLE_NONCE) {
            die('Security error');
        }       
        $result = array('flag' => 0, 'name' => '');
        $user_id = wp_get_current_user()->ID;
        if( update_user_meta( $user_id, 'nbd_artist_name', $this->filter_input_post( 'nbd_artist_name' ) )){
            $result['name'] = $this->filter_input_post( 'nbd_artist_name' );
            $result['flag'] = 1;
        }
        echo json_encode($result); wp_die();
    }
    public function nbd_update_artist_info(){
        if ((!wp_verify_nonce($_POST['_wpnonce'], 'nbd_artist_settings_nonce') && NBDESIGNER_ENABLE_NONCE) || !isset( $_POST['user_id'] )) {
            die('Security error');
        }     
        $user_id = get_current_user_id();
        if( $user_id != $_POST['user_id'] )  die('Security error');
        $list = array(
            'nbd_artist_name', 'nbd_artist_phone', 'nbd_artist_banner', 'nbd_artist_address', 'nbd_artist_facebook', 'nbd_artist_google', 'nbd_artist_twitter',
            'nbd_artist_linkedin', 'nbd_artist_youtube', 'nbd_artist_instagram', 'nbd_artist_flickr', 'nbd_artist_description'
        );
        foreach( $_POST as $key => $value ) {
            if( in_array( $key, $list ) ) {
                update_user_meta( $user_id, $key, $this->filter_input_post( $key ) );              
            }
        }
        $this->delete_designs_transient();
        wp_send_json(
            array( 
                'result' =>  1
            )
        );        
    }
    public function nbd_update_art(){
        if (!wp_verify_nonce($_POST['nbd_nonce'], 'nbd_artist_update') && NBDESIGNER_ENABLE_NONCE) {
            die('Security error');
        }       
        $result = array('flag' => 0);
        $price = $_POST['nbd-design-price'];
        $status = $_POST['nbd-design-status'];
        $id = $_POST['nbd-design-id'];
        global $wpdb;
        $table_name =  $wpdb->prefix . 'nbdesigner_mydesigns';
        $re = $wpdb->update($table_name, array(
            'price' => $price,
            'publish' => $status
        ), array( 'id' => $id));   
        if($re) $result = array('flag' => 1);
        echo json_encode($result); wp_die();
    }
    private function insert_my_designs($product_id, $variation_id, $folder){
        $created_date = new DateTime();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection= $resources->getConnection();

        $user_id = $customerSession->getCustomerId();
        $table_name =  $resources->getTableName('nbdesigner_mydesigns');
        $sql = "INSERT INTO " . $table_name . "(product_id, variation_id, price, folder, user_id, publish, created_date) VALUES ('".$product_id."', '".$variation_id."', '0', '".$folder."', '".$user_id."', '1', '".$created_date->format('Y-m-d H:i:s')."')";

        $connection->query($sql);
        return true;
    } 
    public function get_my_designs( $user_id, $current_page, $item_per_page ){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection= $resources->getConnection();

        $table_name =  $resources->getTableName('nbdesigner_mydesigns');

        $offset = ($current_page - 1) * $item_per_page;
        $sql = "SELECT * FROM {$table_name} WHERE user_id = {$user_id} ORDER BY created_date DESC LIMIT {$item_per_page} OFFSET {$offset}";
        $designs = $connection->fetchAll($sql); 

        return $designs;
    }
    public function get_design( $user_id, $did ){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection= $resources->getConnection();

        $table_name =  $resources->getTableName('nbdesigner_mydesigns');

        $sql = "SELECT * FROM {$table_name} WHERE user_id = {$user_id} AND id = {$did}";
        $designs = $connection->fetchAll($sql);
        return $designs[0];        
    }
    public static function get_template( $user_id, $tid ){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection= $resources->getConnection();

        $table_name =  $resources->getTableName('nbdesigner_templates');

        $sql = "SELECT * FROM {$table_name} WHERE user_id = {$user_id} AND id = {$tid}";
        $templates = $connection->fetchAll($sql);

        return $templates[0];        
    }    
    public function count_designs( $user_id ){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection= $resources->getConnection();

        $table_name =  $resources->getTableName('nbdesigner_mydesigns');

        $sql = "SELECT COUNT(*) FROM {$table_name} WHERE user_id = {$user_id}";

        $designs = $connection->fetchAll($sql);
        return $designs;           
    }
    
    public function nbd_get_user_designs(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection= $resources->getConnection();

        $user_id = $customerSession->getCustomerId();

        $result = array(
            'flag'   =>  1
        );        
        if( $user_id ){
            if( !isset($_POST['did']) ){
                $table_name =  $resources->getTableName('nbdesigner_mydesigns');
                if( isset($_POST['product_id']) ){
                    $product_id = $_POST['product_id'];
                    $variation_id = $_POST['variation_id'];
                    $sql = "SELECT * FROM {$table_name} WHERE user_id = {$user_id} AND product_id = {$product_id} AND variation_id = {$variation_id} ORDER BY created_date DESC";
                } else{
                    $sql = "SELECT * FROM {$table_name} WHERE user_id = {$user_id} ORDER BY created_date DESC";
                }
                $designs = $connection->fetchAll($sql); 

                foreach( $designs as $design ){
                    $path_preview = NBDESIGNER_CUSTOMER_DIR .'/'.$design['folder']. '/preview';
                    $listThumb = Nbdesigner_IO::get_list_images($path_preview);
                    $image = '';
                    if(count($listThumb)){
                        $image = Nbdesigner_IO::wp_convert_path_to_url(end($listThumb));
                        $result['designs'][] = array(
                            'id'    =>  $design['folder'],
                            'src'  =>  $image
                        );
                    }                  
                }
            }else {
                $folder = $_POST['did'];
                $result = nbd_get_template_by_folder( $folder );
                $result['flag'] = 1;           
            }  
        } else {
            $result['flag'] = 0; 
        }
        echo json_encode($result);
        die();        
    }
    public function hook(){
        add_action('plugins_loaded', array($this, 'translation_load_textdomain'));
        add_filter( 'cron_schedules', array($this, 'set_schedule'));      
        add_filter( 'query_vars', array($this, 'nbdesigner_add_query_vars_filter') );
        $position = nbdesigner_get_option('nbdesigner_position_button_product_detail');
        if($position == 4) add_shortcode( 'nbdesigner_button', array($this,'nbdesigner_button') );
        add_shortcode( 'nbd_loggin_redirect', array($this,'nbd_loggin_redirect_func') );
        add_action( 'template_redirect', array( $this, 'nbdesigner_editor_html' ) );    
        add_action('admin_head', array($this, 'nbdesigner_add_tinymce_editor'));
        add_action( 'woocommerce_init', array( $this, 'init_session' ) );
        
        add_filter( 'body_class', array($this, 'add_body_class'), 10, 1 );
        add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );
        
        /* REST API */
        //add_action( 'rest_api_init', array( $this, 'freedom' ) );
        
        /* Force login */
        if( 'yes' == nbdesigner_get_option('nbdesigner_site_force_login') ){
            add_action( 'template_redirect', array( $this, 'force_login' ), 1 );
            add_filter( 'rest_authentication_errors', 'forcelogin_rest_access', 99 );
        }
        
        /* Cron job flush W3 Total cache */
        if( 'yes' == nbdesigner_get_option('nbdesigner_cron_job_clear_w3_cache') ){
            add_action( 'wp', array( $this, 'w3tc_cache_flush') );
            add_action( 'nbd_w3_flush_cache', array($this, 'w3_flush_cache') ); 
        }
    }
    public function w3_flush_cache(){
        if (function_exists('w3tc_flush_all')){
            w3tc_flush_all();
        }
    }
    public function w3tc_cache_flush(){
    if ( ! wp_next_scheduled( 'nbd_w3_flush_cache' ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'every6hours', 'nbd_w3_flush_cache' );
    }
    }
    public function forcelogin_rest_access( $result ){
        if ( null === $result && ! is_user_logged_in() ) {
            return new WP_Error( 'rest_unauthorized', __( "Only authenticated users can access the REST API.", 'web-to-print-online-designer' ), array( 'status' => rest_authorization_required_code() ) );
        }
        return $result;        
    }
    public function force_login( ){
        // Exceptions for AJAX, Cron, or WP-CLI requests
        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            return;
        } 
        // Redirect unauthorized visitors
        if ( ! is_user_logged_in() ) {
            $url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
            $url .= '://' . $_SERVER['HTTP_HOST'];
            if ( strpos( $_SERVER['HTTP_HOST'], ':' ) === FALSE ) {
                $url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
            }
            $url .= $_SERVER['REQUEST_URI'];  
            $bypass = apply_filters( 'nbd_forcelogin_bypass', false, $url );
            $whitelist = apply_filters( 'nbd_forcelogin_whitelist', array(), $url );  
            if (preg_replace('/\?.*/', '', $url) != preg_replace('/\?.*/', '', wp_login_url()) && !in_array($url, $whitelist) && !$bypass) {
                $redirect_url = apply_filters('nbd_forcelogin_redirect', $url);
                wp_safe_redirect(wp_login_url($redirect_url), 302);
                exit;
            }
        }else{
            // Only allow Multisite users access to their assigned sites
            if ( ! is_user_member_of_blog() && ! current_user_can('setup_network') ) {
                wp_die( __( "You're not authorized to access this site.", 'web-to-print-online-designer' ), get_option('blogname') . ' &rsaquo; ' . __( "Error", 'web-to-print-online-designer' ) );
            }            
        }
    }
    private function is_request($type) {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX') ) && !defined('DOING_CRON');
        }
    }
    public function nbd_check_use_logged_in(){
        echo json_encode(
            array(
                'is_login'  =>  nbd_user_logged_in(),
                'nonce'  =>  wp_create_nonce('save-design'),
                'nonce_get'  =>  wp_create_nonce('nbdesigner-get-data')
            )
        );
        die();      
    }
    public function nbd_save_for_later(){ 
        $product_id = absint($_POST['product_id']);
        $variation_id = absint($_POST['variation_id']);
        $folder = $_POST['folder'];
        $design_folder = substr(md5(uniqid()),0,5).rand(1,100).time();
        $path = NBDESIGNER_CUSTOMER_DIR . '/' . $folder;
        $design_path = NBDESIGNER_CUSTOMER_DIR . '/' . $design_folder;
        Nbdesigner_IO::copy_dir( $path, $design_path );
        $result = array(
            'flag'  =>  1,
            'folder'  =>  $design_folder
        );
        $insert = $this->insert_my_designs($product_id, $variation_id, $design_folder);
        if( !$insert ) $result['flag'] = 0;
        echo json_encode( $result );
        die();   
    }
    public function nbd_delete_my_design(){  
        $design_id = absint($_POST['design_id']);
        global $wpdb;
        $re = $wpdb->delete("{$wpdb->prefix}nbdesigner_mydesigns", array('id' => $design_id));
        $result = array('flag' =>  0);
        if( $re ){
            $result['flag'] = 1;  
        }
        echo json_encode($result);
        die();          
    }
    public function init_session(){
        global $woocommerce;
        if ( $this->is_request( 'frontend' ) && $woocommerce->session != null ) {
            $woocommerce->session->set_customer_session_cookie(true);
        }
    }
    public function admin_hook(){    
        add_action('plugins_loaded', array($this, 'nbdesigner_user_role'));
        add_action('admin_menu', array($this, 'nbdesigner_menu'));
        add_action('add_meta_boxes', array($this, 'add_design_box'), 30);
        add_action('save_post', array($this, 'save_settings'));
        add_action('save_post', array($this, 'delete_nbd_product_transient'));
        add_action('delete_post', array($this, 'delete_nbd_product_transient'));
        add_filter('upload_mimes', array($this, 'upload_mimes'));
        add_filter('manage_product_posts_columns', array($this, 'nbdesigner_add_design_column'));
        add_action('manage_product_posts_custom_column', array($this, 'nbdesigner_display_posts_design'), 1, 2);
        add_filter('nbdesigner_notices', array($this, 'nbdesigner_notices'));     
        add_filter( 'set-screen-option', array($this, 'set_screen' ), 10, 3 );
        add_filter( 'parse_query', array($this, 'nbdesigner_admin_posts_filter') );
        add_filter( 'views_edit-product', array( $this, 'nbdesigner_product_sorting_nbd' ),30 );
        add_action('admin_enqueue_scripts', array($this, 'nbdesigner_admin_enqueue_scripts'), 30, 1);
        add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
        add_filter( 'admin_footer', array( $this, 'footer_notice' ), 1 );
        add_action( 'admin_init', array( $this, 'admin_redirects' ) );
    }
    public function frontend_hook(){
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        if ( isset( $_GET['download_nbd_design_file'] ) && isset( $_GET['order_id'] ) && isset( $_GET['order_item_id'] ) ) {
            add_action( 'init', array( $this, 'download_order_item_design_files' ) );
        }          
    }
    public function woocommerce_hook(){
        /* ADMIN */
        add_filter( 'woocommerce_admin_order_actions', array($this, 'add_nbdesinger_order_actions_button'), 30, 2 );
        
        /* FRONTEND */
        add_filter('woocommerce_cart_item_name', array($this, 'render_cart'), 1, 3);
        add_action('woocommerce_add_to_cart', array($this, 'set_nbd_session_cart'), 10, 6);
        if( ! is_woo_v3() ){        
            add_action('woocommerce_add_order_item_meta', array($this, 'nbdesigner_add_order_design_data'), 1, 3);
        }else{
            add_action('woocommerce_new_order_item', array($this, 'nbdesigner_add_new_order_item'), 1, 3);
        }          
        add_action('woocommerce_checkout_order_processed', array($this, 'set_nbd_order_data'), 1, 1);
        add_action('nbd_checkout_order_processed', array($this, 'set_nbd_order_data'), 1, 1);
        add_filter('woocommerce_locate_template', array($this, 'nbdesigner_locate_plugin_template'), 20, 3 );
        //add_filter( 'woocommerce_order_item_name', array($this, 'order_item_name'), 1, 2);
        //add_filter( 'woocommerce_order_item_quantity_html', array($this, 'nbdesigner_order_item_quantity_html'), 1, 2);
        add_filter( 'woocommerce_checkout_cart_item_quantity', array($this, 'nbd_checkout_cart_item_quantity'), 10, 3);
        add_filter( 'woocommerce_hidden_order_itemmeta', array($this, 'nbdesigner_hidden_custom_order_item_metada'));
        add_action( 'nbdesigner_admin_notifications_event', array($this, 'nbdesigner_admin_notifications_event_action') );
        add_action( 'woocommerce_cart_item_removed', array($this, 'nbdesigner_remove_cart_item_design'), 10, 2 );
        add_action( 'woocommerce_product_after_variable_attributes', array($this,'nbdesigner_variation_settings_fields'), 10, 3 );
        add_action( 'woocommerce_save_product_variation', array($this,'nbdesigner_save_variation_settings_fields'), 10, 2 );
        add_filter( 'woocommerce_add_cart_item_data', array($this, 'nbd_add_cart_item_data'), 10, 3 );   
        add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 1, 2 );
        add_filter( 'woocommerce_add_cart_item', array($this, 'nbd_add_cart_item'), 99999, 1 ); 
        add_filter( 'woocommerce_cart_item_price', array(&$this, 'change_cart_item_prices_text'), 10, 3 );
        add_filter( 'woocommerce_cart_item_subtotal', array(&$this, 'change_cart_item_prices_text'), 10, 3 );
        add_action( 'woocommerce_before_calculate_totals', array($this, 'nbd_before_calculate_totals'), 10, 1);
        //add_action('woocommerce_cart_loaded_from_session', array($this, 'cart_loaded_from_session'), 100);
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'nbd_before_add_to_cart_button' ) );
        //add_action( 'woocommerce_ordered_again', array( $this, 'nbd_ordered_again' ), 10, 1 );
        add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'nbd_order_again_cart_item_data' ), 10, 3 );
        $page_design = nbdesigner_get_option('nbdesigner_page_design_tool');
        if($page_design == 2){
            add_action('woocommerce_before_single_product', array($this, 'check_has_design'));   
            add_filter('woocommerce_add_to_cart_redirect', array($this, 'nbd_add_to_cart_redirect'));
        }else{
            add_filter( 'woocommerce_product_single_add_to_cart_text', array($this, 'add_to_cart_text'), 20, 2);
            $position = nbdesigner_get_option('nbdesigner_position_button_product_detail');
            if($position == 1){
                add_action('woocommerce_before_add_to_cart_button', array($this, 'nbdesigner_button'), 30);
            }else if($position == 2){
                add_action('woocommerce_before_add_to_cart_form', array($this, 'nbdesigner_button'), 30);
            }else if($position == 3){
                add_action('woocommerce_after_add_to_cart_form', array($this, 'nbdesigner_button'), 30);
            } 
        }
        $catalog_button_pos = nbdesigner_get_option('nbdesigner_position_button_in_catalog');
        if($catalog_button_pos == 2){
           add_action( 'woocommerce_after_shop_loop_item', array(&$this, 'add_catalog_nbdesign_button'), 20 );
        }elseif($catalog_button_pos == 1) {
            add_filter( 'woocommerce_loop_add_to_cart_link', array(&$this, 'nbdesigner_add_to_cart_shop_link'), 10, 2 );
        }
        if( get_option('nbdesigner_attachment_admin_email', false) == 'yes' ){
            add_filter('woocommerce_email_attachments', array(&$this, 'attach_design_to_admin_email'), 10, 3);
        }
        //add_filter('woocommerce_email_attachments', array(&$this, 'attach_design_to_admin_email2'), 10, 3);
        /** bulk action **/
        // add_action( 'woocommerce_before_add_to_cart_button', array(&$this, 'bulk_variation_field'), 9999 );
        if ( isset( $_POST['nbd-variation-value'] ) && $_POST['nbd-variation-value'] ) {
            add_action( 'wp_loaded', array( $this, 'process_add_bulk_variation' ), 99 );
        }   
        // add_filter('post_class', array( $this, 'add_post_class' ), 10,3);
        /** end. bulk action **/
        add_action('woocommerce_order_item_meta_end', array($this, 'woocommerce_order_item_meta_end'), 30, 3);
        add_action('woocommerce_order_details_after_order_table', array($this, 'woocommerce_order_details_after_order_table'), 30, 1);
        
        //add_action( 'woocommerce_order_status_completed', array($this, 'update_order_item_meta_data') );
        
        add_filter( 'woocommerce_login_redirect', array($this, 'login_redirect'), 10, 1 );
        add_action('woocommerce_registration_redirect', array($this, 'login_redirect'), 10, 1 );
        add_filter('woocommerce_add_to_cart_redirect', array($this, 'nbd_auto_add_to_cart_redirect'));
    }
    public function nbd_auto_add_to_cart_redirect($url){
        if( isset($_REQUEST['nbd-auto-add-to-cart-in-detail-page']) ){
            $url = esc_url( wc_get_cart_url() );
        }
        return $url;
    }
    public function footer_notice(){
        $valid_license = nbd_check_license();
        if( !$valid_license ){
            include(NBDESIGNER_PLUGIN_DIR .'views/license-notice.php');
        }
    }
    public function login_redirect( $redirect ){
        if( isset( $_GET['nbd_redirect'] ) ) $redirect = getUrlPageNBD('redirect');
        if( isset( $_GET['nbu_redirect'] ) ) $redirect = $_GET['nbu_redirect'];
        return $redirect;
    }
    public function nbd_order_again_cart_item_data( $arr, $item, $order ){
        $order_items = $order->get_items();
        foreach( $order_items AS $order_item_id => $_item ){    
            $nbd_item_key = wc_get_order_item_meta($order_item_id, '_nbd');
            $nbu_item_key = wc_get_order_item_meta($order_item_id, '_nbu'); 
            if( $item->get_id() == $order_item_id ){
                if( $nbd_item_key ){
                    $arr['nbd_item_meta_ds']['nbd'] = $nbd_item_key;
                }     
                if( $nbu_item_key ){
                    $arr['nbd_item_meta_ds']['nbu'] = $nbu_item_key;
                }     
            }
        }
        return $arr;
    }
    public function add_to_cart_text( $text, $product ){ 
        $option = unserialize(get_post_meta($product->get_id(), '_nbdesigner_option', true));  
        if($option['request_quote']){
            return __('Get a Quote', 'web-to-print-online-designer'); 
        }else {
            return $text;
        }            
    }
    public function nbdesigner_add_to_cart_shop_link($handler, $product){
        if(is_nbdesigner_product($product->get_id())){    
            $label = __('Start Design', 'web-to-print-online-designer');
            ob_start();            
            nbdesigner_get_template('loop/start-design.php', array('product' => $product, 'label' => $label, 'type' =>  $product->get_type()));
            $button = ob_get_clean();    
            return $button;
        }
        return $handler;
    }
    public function add_catalog_nbdesign_button(){
        global $product;
        if(is_nbdesigner_product($product->get_id())){  
            $label = __('Start Design', 'web-to-print-online-designer');   
            ob_start();            
            nbdesigner_get_template('loop/start-design.php', array('product' => $product, 'label' => $label, 'type' =>  $product->get_type()));
            $button = ob_get_clean();    
            echo $button;
        }
    }
    public function check_has_design(){
        global $product;
        $product_id = $product->get_id();
        if(is_nbdesigner_product($product_id)){
            $layout = nbd_get_product_layout($product_id);
            if($layout != 'v'){
                add_filter( 'woocommerce_product_single_add_to_cart_text', function(){
                    return __('Add to cart & start design', 'web-to-print-online-designer');               
                });     
            }
        }
    }
    public function add_body_class( $classes ){
        if( is_product() ){
            $product_id = get_the_ID();   
            if( is_nbdesigner_product( $product_id ) ){
                $classes[] = 'nbd-single-product-page';
                $option = unserialize(get_post_meta($product_id, '_nbdesigner_option', true));
                if($option){
                    $_layout = nbdesigner_get_option('nbdesigner_design_layout');
                    $layout = isset($option['layout']) ? $option['layout'] : $_layout;
                    if( $layout == 'm' ){
                        $classes[] = 'nbd-modern-layout';
                    }else if( $layout == 'v' ){
                        $classes[] = 'nbd-visual-layout';
                    }
                }
            }
        }
    return $classes;          
    }
    public function nbd_add_to_cart_redirect($url){
        if(!isset($_REQUEST['add-to-cart'])) return $url;
        $product_id = absint( $_REQUEST['add-to-cart'] );
        $variation_id = isset($_REQUEST['variation_id']) ? absint( $_REQUEST['variation_id'] ) : 0;
        $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id; 
        $layout = nbd_get_product_layout($product_id);
        if($layout == 'v'){
            return $url;
        }

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        if(is_nbdesigner_product($product_id)){  
            $cart_item_key = $session->get('nbd_last_item_cart');
            $url =  add_query_arg(array(
                    'task2'  => 'update',
                    'product_id'    =>  $product_id,
                    'view'    =>  $layout,
                    'cik'    =>  $cart_item_key
                    ),  getUrlPageNBD('create'));
            if( isset( $_REQUEST['nbds-ref'] ) && $_REQUEST['nbds-ref'] != '' ){
                $url = add_query_arg(array(
                   'reference'  => $_REQUEST['nbds-ref']
                   ),  $url); 
            }
            if( $cart_item_key ){
                $cart_item = WC()->cart->cart_contents[ $cart_item_key ];
                if( $cart_item ){
                    $cart_product = $cart_item['data'];
                    $product_permalink = $cart_product->get_permalink( $cart_item );
                    $att_query = parse_url( $product_permalink, PHP_URL_QUERY );
                    $url .= '&'.$att_query;
                }
            }
            if( $variation_id > 0 ) $url .= '&variation_id='.$variation_id;
            $session->unset('nbd_last_item_cart');
            $nbd_item_key = substr(md5(uniqid()),0,5).rand(1,100).time();
            $session->set('nbd_item_key_'.$nbd_item_cart_key, $nbd_item_key); 
            $session->set('nbu_item_key_'.$nbd_item_cart_key, $nbd_item_key); 
        }
        return $url;
    }
    public function change_cart_item_prices_text( $price, $cart_item, $cart_item_key ){
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        $nbd_session = $session->get($cart_item_key . '_nbd');   
        $nbu_session = $session->get($cart_item_key . '_nbu');   
        if( isset($nbd_session) || isset($nbu_session) ){
            $option = unserialize(get_post_meta($cart_item['product_id'], '_nbdesigner_option', true));
            if( $option['request_quote'] ){
                return '-';
            }
        }
        return $price;
    }
    public function nbd_before_add_to_cart_button(){
        $product_id = get_the_ID();   
        $is_nbupload = get_post_meta($product_id, '_nbdesigner_enable_upload', true);      
        if( $is_nbupload ){
            echo '<input type="hidden" class="nbd-upload" id="nbd-upload-files" name="nbd-upload-files" value="" />';
        }   
        if(isset($_GET['nbds-ref']) && $_GET['nbds-ref'] != ''){ ?>
            <input type="hidden" name="nbds-ref" value="<?php echo $_GET['nbds-ref']; ?>"/>
        <?php }
    }
    public function nbd_remove_cart_design(){  
        $type = $_POST['type'];
        $cart_item_key = $_POST['cart_item_key'];

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        if( $type == 'custom' ){
            $session->unset($cart_item_key . '_nbd');
            unset( WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']['nbd'] );
        }else {
            $session->unset($cart_item_key . '_nbu');
            unset( WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']['nbu'] );
        }
        if( count(WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']) == 0 ){
            unset(WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']);
        }
        WC()->cart->set_session();
        echo 'success';
        wp_die();
    }    
    public function nbd_before_calculate_totals( $cart_obj ){
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        foreach ( $cart_obj->get_cart() as $key => $cart_item_data ) {
            $product_id = $cart_item_data['product_id'];
            $variation_id = $cart_item_data['variation_id'];   
            $product_id = get_wpml_original_id( $product_id );  
            $variation_id = get_wpml_original_id( $variation_id );  
            $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id; 
            $nbd_session =$session->get($key . '_nbd');   
            $nbu_session = $session->get($key . '_nbu');    
            if( isset($nbd_session) || isset($nbu_session) ){
                $option = unserialize(get_post_meta($product_id, '_nbdesigner_option', true)); 
                if( isset($nbd_session) ) {
                    $path = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_session . '/config.json';
                    $config = nbd_get_data_from_json($path);
                    if( isset($option['additional_price']) && $option['additional_price'] != '' && $option['additional_price'] != 0 && isset( $config->origin_product ) ){
                        $num_additional_side = count($config->product) - count($config->origin_product);
                        $additional_price = floatval($option['additional_price']);
                        $option['extra_price'] = $num_additional_side * $additional_price;
                    }
                    if( isset( $config->custom_dimension ) && isset( $config->custom_dimension->price ) ){
                        $nbd_variation_price = $config->custom_dimension->price;
                    }
                }
                if( $option['request_quote'] || $option['extra_price'] || (isset($nbd_variation_price) && $nbd_variation_price != 0) ){
                    $initial_price = $session->get($key . '_nbd_initial_price');
                    if( $initial_price ){
                        $price = $initial_price;
                    }else {
                        $price = $cart_item_data['data']->get_price();
                        $session->set($key . '_nbd_initial_price', $price);
                    }  
                }
                if( $option['request_quote'] ){
                    $cart_item_data['data']->set_price( 0 );                       
                } else if( $option['extra_price'] || (isset($nbd_variation_price) && $nbd_variation_price != 0) ){
                    $decimals = wc_get_price_decimals();  
                    $new_price = $price;
                    if( $option['type_price'] == 1 ){
                        $new_price += $option['extra_price'];   
                    }else{
                        $new_price += $price * $option['extra_price'] / 100;       
                    }
                    if( (isset($nbd_variation_price) && $nbd_variation_price != 0) ) $new_price += $nbd_variation_price;
                    $new_price = round($new_price, $decimals);   
                    $cart_item_data['data']->set_price( $new_price ); 
                }                
            } else {
                /* Destroy get a quote or extra price when remove design */
                if ( $session->isset($key . '_nbd_initial_price') ) {
                    $initial_price = $session->get($key . '_nbd_initial_price');
                    $session->unset($key . '_nbd_initial_price');
                    $cart_item_data['data']->set_price( $initial_price ); 
                }
            }          
        }          
    }
    public function nbd_add_cart_item( $cart_item_data ){
        $product_id = $cart_item_data['product_id'];
        $variation_id = $cart_item_data['variation_id'];

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id; 
        $nbd_item_session = $session->get('nbd_item_key_'.$nbd_item_cart_key);
        $nbu_item_session = $session->get('nbu_item_key_'.$nbd_item_cart_key);
        if( isset($nbd_item_session) || isset($nbu_item_session) ){
            $option = unserialize(get_post_meta($product_id, '_nbdesigner_option', true)); 
            if( $option['request_quote'] ){
                $cart_item_data['data']->set_price( 0 );                
            }    
        }              
        return $cart_item_data;
    } 
    public function nbdesigner_admin_enqueue_scripts($hook){
        wp_register_script('angularjs', NBDESIGNER_PLUGIN_URL . 'assets/libs/angular-1.6.9.min.js', array('jquery'), '1.6.9');    
        wp_register_script('fontfaceobserver', NBDESIGNER_PLUGIN_URL . 'assets/libs/fontfaceobserver.js', array(), '2.0.13');            
        wp_register_style('nbd-general', NBDESIGNER_CSS_URL . 'nbd-general.css', array('dashicons'), NBDESIGNER_VERSION);
        wp_enqueue_style(array('nbd-general'));     
        if (($hook == 'post.php') || ($hook == 'post-new.php') || ($hook == 'toplevel_page_nbdesigner') ||
                ($hook == 'nbdesigner_page_nbdesigner_manager_product' ) || ($hook == 'toplevel_page_nbdesigner_shoper') || ($hook == 'nbdesigner_page_nbdesigner_frontend_translate') ||
                ($hook == 'nbdesigner_page_nbdesigner_manager_fonts') || ($hook == 'nbdesigner_page_nbdesigner_manager_arts') 
                 || ($hook == 'nbdesigner_page_nbdesigner_tools') || ($hook == 'nbdesigner_page_manage_color')) {
            wp_register_style('admin_nbdesigner', NBDESIGNER_CSS_URL . 'admin-nbdesigner.css', array('wp-color-picker'), NBDESIGNER_VERSION);
            wp_register_script('admin_nbdesigner', NBDESIGNER_JS_URL . 'admin-nbdesigner.js', array('jquery', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-autocomplete', 'wp-color-picker', 'jquery-ui-datepicker'), NBDESIGNER_VERSION);
            wp_localize_script('admin_nbdesigner', 'admin_nbds', array(
                'url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('nbdesigner_add_cat'),
                'mes_success' => 'Success!',
                'url_check' => NBDESIGNER_AUTHOR_SITE,
                'sku' => NBDESIGNER_SKU,       
                'url_gif' => NBDESIGNER_PLUGIN_URL . 'assets/images/loading.gif',
                'assets_images'  =>  NBDESIGNER_PLUGIN_URL . 'assets/images/',
                'nbds_lang' => nbd_get_i18n_javascript() ));                
            wp_enqueue_style(array('wp-pointer', 'wp-jquery-ui-dialog', 'admin_nbdesigner'));
            wp_enqueue_script(array('wp-pointer', 'wpdialogs', 'admin_nbdesigner'));                            
        }
        if($hook == 'admin_page_nbdesigner_detail_order' || $hook == 'nbdesigner_page_nbdesigner_manager_product'){
            wp_enqueue_media();
            wp_register_style(
                    'admin_nbdesigner_detail_order', 
                    NBDESIGNER_CSS_URL . 'detail_order.css', 
                    array('jquery-ui-style-css'), NBDESIGNER_VERSION);
            wp_register_style(
                    'jquery-ui-style-css', 
                    '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css', 
                    array(), '1.11.4');                
            wp_enqueue_style(array('admin_nbdesigner_detail_order','jquery-ui-style-css'));
            wp_register_script(
                    'admin_nbdesigner_detail_order_slider',
                    NBDESIGNER_JS_URL . 'owl.carousel.min.js', 
                    array('jquery', 'jquery-ui-tabs', 'jquery-ui-resizable', 'jquery-ui-draggable'), 
                    NBDESIGNER_VERSION);
            wp_enqueue_script(array('jquery', 'jquery-ui-tabs'));              
        }
        if($hook == 'nbdesigner_page_nbdesigner_manager_fonts'){
            wp_enqueue_script('angularjs');
            wp_enqueue_script('fontfaceobserver');
        }
        if($hook == 'nbdesigner_page_nbdesigner_frontend_translate'){
            wp_register_script('admin_nbdesigner_jeditable', NBDESIGNER_JS_URL . 'jquery.jeditable.js', array('jquery'));
            wp_enqueue_script('admin_nbdesigner_jeditable');
        }
        if($hook == 'nbdesigner_page_nbdesigner_tools'){
            wp_enqueue_style('admin_nbdesigner_codemirror', NBDESIGNER_PLUGIN_URL . 'assets/codemirror/codemirror.css');
            wp_enqueue_script( 'nbdesigner_codemirror_js', NBDESIGNER_PLUGIN_URL . 'assets/codemirror/codemirror.js' , array());
            wp_enqueue_script( 'nbdesigner_codemirror_css_js', NBDESIGNER_PLUGIN_URL . 'assets/codemirror/css.js' , array());
        }
        if($hook == 'nbdesigner_page_nbdesigner_admin_template' || $hook == 'nbdesigner_page_nbdesigner_manager_arts'
            || $hook == 'admin_page_nbdesigner_detail_order' || $hook == 'nbdesigner_page_nbdesigner_manager_fonts'   
            || $hook == 'nbdesigner_page_nbdesigner_tools' || $hook == 'nbdesigner_page_nbdesigner_frontend_translate' || $hook == 'nbdesigner_page_manage_color'  ){
            wp_enqueue_style('nbdesigner_sweetalert_css', NBDESIGNER_CSS_URL . 'sweetalert.css');
            wp_enqueue_script( 'nbdesigner_sweetalert_js', NBDESIGNER_JS_URL . 'sweetalert.min.js' , array('jquery'));
        }
        if($hook == 'toplevel_page_nbdesigner'){
            wp_enqueue_media();
            wp_enqueue_style('nbdesigner_settings_css', NBDESIGNER_CSS_URL . 'admin-settings.css', array(), NBDESIGNER_VERSION);
        }
        if(is_rtl()){
            wp_enqueue_style('nbd-rtl',NBDESIGNER_CSS_URL . 'nbd-rtl.css',array(), NBDESIGNER_VERSION);   
        }        
    }
    public function frontend_enqueue_scripts(){
        wp_register_style('nbdesigner', NBDESIGNER_CSS_URL . 'nbdesigner.css', array(), NBDESIGNER_VERSION);
        wp_enqueue_style('nbdesigner');    
        $depends = apply_filters('nbd_depend_js', array('jquery', 'jquery-blockui') );
        wp_register_script('nbdesigner', NBDESIGNER_JS_URL . 'nbdesigner.js', $depends, NBDESIGNER_VERSION);
        $args = apply_filters('nbd_js_object', array(
            'url' => admin_url('admin-ajax.php'),
            'sid' => session_id(),
            'nonce' => wp_create_nonce('save-design'),
            'nonce_get' => wp_create_nonce('nbdesigner-get-data'),
            'cart_url'   =>   esc_url( wc_get_cart_url() ),
            'hide_cart_button'  =>  nbdesigner_get_option('nbdesigner_hide_button_cart_in_detail_page'),
            'confirm_delete_design' => __('Are you sure you want to delete this design?', 'web-to-print-online-designer'),
            'delete_success' => __('Delete successfully!', 'web-to-print-online-designer')
            ));
        wp_localize_script('nbdesigner', 'nbds_frontend', $args);
        wp_enqueue_script('nbdesigner');
    }
    public static function plugin_activation() {
        if (version_compare($GLOBALS['wp_version'], NBDESIGNER_MINIMUM_WP_VERSION, '<')) {
            $message = sprintf(__('<p>Plugin <strong>not compatible</strong> with WordPress %s. Requires WordPress %s to use this Plugin.</p>', 'web-to-print-online-designer'), $GLOBALS['wp_version'], NBDESIGNER_MINIMUM_WP_VERSION);
            die($message);
        }
        if(version_compare(PHP_VERSION, NBDESIGNER_MINIMUM_PHP_VERSION, '<')){
            $message = sprintf(__('<p>Plugin <strong>not compatible</strong> with PHP %s. Requires PHP %s to use this Plugin.</p>', 'web-to-print-online-designer'), PHP_VERSION, NBDESIGNER_MINIMUM_PHP_VERSION);
            die($message);            
        }
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            $message = '<div class="error"><p>' . sprintf(__('WooCommerce is not active. Please activate WooCommerce before using %s.', 'web-to-print-online-designer'), '<b>Nbdesigner</b>') . '</p></div>';
            die($message);
        }
        $woocommer_data = get_plugin_data(WP_PLUGIN_DIR .'/woocommerce/woocommerce.php', false, false);
        if (version_compare ($woocommer_data['Version'] , NBDESIGNER_MINIMUM_WC_VERSION, '<')){
            $message = '<div class="error"><p>' . sprintf(__('WoooCommerce %s or greater is required', 'web-to-print-online-designer'), NBDESIGNER_MINIMUM_WC_VERSION) . '</p></div>';
            die($message);
        }
        
        /* Install */
        NBD_Install::create_pages();
        NBD_Install::create_tables();
        NBD_Install::init_files_and_folders();
        NBD_Install::insert_default_files();
        
        /* Update data */
        NBD_Update_Data::install_update();        
        
        $design_endpoint = new My_Design_Endpoint();
        $design_endpoint->add_endpoints();
        
        if( self::is_new_install() ){
            set_transient( '_nbd_activation_redirect', 1, 30 );
        }        
        
        update_option('nbdesigner_version_plugin', NBDESIGNER_VERSION);        
        do_action('nbd_installed');
        flush_rewrite_rules();
    }
    private static function is_new_install() {
        return is_null( get_option( 'nbdesigner_version_plugin', null ) );
    }    
    public function set_schedule($schedules){   
        if(!isset($schedules['hourly'])){
            $schedules['hourly'] = array('interval' => 60*60, 'display' => __('Once Hourly'));
        }
        if(!isset($schedules['every6hours'])){
            $schedules['every6hours'] = array('interval' => 6 * HOUR_IN_SECONDS, 'display' => __('Every 6 Hours', 'web-to-print-online-designer'));
        }
        return $schedules;
    }
    public function schehule(){
        $timestamp = wp_next_scheduled( 'nbdesigner_lincense_event' );
        if( $timestamp == false ){
            wp_schedule_event( time(), 'daily', 'nbdesigner_lincense_event' );
        }   
        $timestamp2 = wp_next_scheduled( 'nbdesigner_admin_notifications_event' );
        $notifications = get_option('nbdesigner_notifications', false);
        $recurrence = 'hourly';         
        if( $timestamp2 == false && $notifications === false){
            wp_schedule_event( time(), $recurrence, 'nbdesigner_admin_notifications_event' );
        }
        /* Schedule delete upload files */
        $retain_time = nbdesigner_get_option('nbdesigner_long_time_retain_upload_fies');
        if( absint($retain_time) > 0 ){
            $last_check_time = get_transient('nbd_last_time_check_upload_files');
            if( false === $last_check_time ){
                $last_check_time = time();
                set_transient( 'nbd_last_time_check_upload_files' , $last_check_time, DAY_IN_SECONDS );
                $this->delete_expired_upload_files( $retain_time );
            }
        }
    }
    public function nbdesigner_lincense_event_action(){
        //$path = NBDESIGNER_PLUGIN_DIR . 'data/license.json';   
        //$path_data = NBDESIGNER_DATA_CONFIG_DIR . '/license.json';
        //if(file_exists($path) || file_exists($path_data)){
            $license = $this->nbdesigner_check_license();
            $now = strtotime("now");
            if(isset($license['type']) && $license['type'] != 'free' && isset($license['expiry']) && $license['expiry'] < $now ){
                $result = $this->nbdesiger_request_license($license['key'], $this->activedomain);
                if($result){
                    $data = (array) json_decode($result);               
                    $data['key'] = $license['key'];
                    if($data['type'] == 'free') $data['number_domain'] = "5";
                    $data['salt'] = md5($license['key'].$data['type']);                   
                    //$this->nbdesigner_write_license(json_encode($data));  
                    update_option('nbdesigner_license', $data);
                }   
            }
        //}
        add_action( 'admin_notices', array( $this, 'nbdesigner_lincense_notices' ) );   
    }
    public function nbdesigner_admin_notifications_event_action(){       
        $notifications = get_option('nbdesigner_notifications', false);
        $owner_email = get_option('nbdesigner_notifications_emails', false);
        if($notifications != false){
            global $woocommerce;         
            if($notifications == 'yes'){   
                if( version_compare( $woocommerce->version, "2.2", ">=" ) ){
                    $post_status = array( 'wc-processing', 'wc-completed', 'wc-on-hold', 'wc-pending' );
                }else{
                    $post_status = 'publish';
                }                  
                $args = array(
                    'post_type' => 'shop_order',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'posts_per_page'=>-1,
                    'post_status' => $post_status,
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => '_nbdesigner_order_changed',
                            'value' => 1,
                        ),
                        array(
                            'key' => '_nbdesigner_upload_order_changed',
                            'value' => 1,
                        )
                    )
            ); 
                $post_orders = get_posts($args);   
                $orders = array();
                foreach ($post_orders AS $order) {
                    $the_order = new WC_Order($order->ID);
                    $orders[$order->ID] = $the_order->get_order_number();                    
                }
                if (count($orders)) {
                    foreach ($orders AS $order => $order_number) {
                        update_post_meta($order, '_nbdesigner_order_changed', 0);
                        update_post_meta($order, '_nbdesigner_upload_order_changed', 0);
                    }                    
                    $subject = __('New / Modified order(s)', 'web-to-print-online-designer');
                    $mailer = $woocommerce->mailer();
                    ob_start();
                    wc_get_template('emails/nbdesigner-admin-notifications.php', array(
                        'plugin_id' => 'nbdesigner',
                        'orders' => $orders,
                        'heading' => $subject
                    )); 
                    $emails = new WC_Emails();
                    $woo_recipient = $emails->emails['WC_Email_New_Order']->recipient;
                    if($owner_email == ''){
                        if(!empty($woo_recipient)) {
                            $user_email = esc_attr($woo_recipient);
                        } else {
                            $user_email = get_option( 'admin_email' );
                        }                        
                    }else{
                        $user_email = $owner_email;
                    }
                    $body = ob_get_clean();                  
                    if (!empty($user_email)) {                                            
                        $mailer->send($user_email, $subject, $body);
                    }                    
                }
            }
        }
    }
    public function nbdesigner_add_query_vars_filter($vars){
        $vars[] = "nbds-adid";
        $vars[] = "nbds-ref";
        return $vars;   
    }
    public function nbdesigner_admin_posts_filter($query){
        global $typenow;
        if ( 'product' == $typenow ) {
            if ( !empty($_GET['has_nbd']) ) {
                $query->query_vars['meta_key'] = '_nbdesigner_enable';
                $query->query_vars['meta_value']    = esc_sql($_GET['has_nbd']);
            }    
        }    
    }
    public function nbdesigner_product_sorting_nbd($views){
        global $wp_query;
        $class            = '';
        $query_string     = remove_query_arg(array( 'orderby', 'order' ));
        $query_string     = add_query_arg( 'has_nbd', urlencode('1'), $query_string );        
    $views['has_nbd'] = '<a href="' . esc_url( $query_string ) . '" class="' . esc_attr( $class ) . '">' . __( 'Has NBDesigner', 'web-to-print-online-designer') . '</a>';        
        return $views;
    }
    public function nbdesigner_lincense_notices(){            
        $license = $this->nbdesigner_check_license();
        if($license['status'] == 0){
            add_action( 'admin_notices', array( $this, 'nbdesigner_lincense_notices_content' ) );     
        } 
    }
    public function nbdesigner_lincense_notices_content(){
        //$mes = nbd_custom_notices('notices', 'You\'re using NBDesigner free version (full features and function but for max 5 products) or expired pro version. <br /><a class="nbd-notice-action" href="http://cmsmart.net/wordpress-plugins/woocommerce-online-product-designer-plugin" target="_blank">Please buy the Premium version here to use for all product </a>');
        $mes = nbd_custom_notices('notices', '<a class="nbd-license-notice" href="https://cmsmart.net/wordpress-plugins/woocommerce-online-product-designer-plugin?utm_source=backend&utm_medium=cpc&utm_campaign=wpol&utm_content=banner" target="_blank"><img src="'.NBDESIGNER_ASSETS_URL . 'images/lite_version.jpg" alt="Lite version"/></a>');
        $current_screen = get_current_screen();
        $nbd_pages = nbd_admin_pages();
        if ( isset( $current_screen->id ) && in_array( $current_screen->id, $nbd_pages ) ) printf($mes);
    }
    public function translation_load_textdomain() {  
        load_plugin_textdomain('web-to-print-online-designer', false, dirname(dirname( plugin_basename( __FILE__ ))) . '/langs/');
    }
    public static function plugin_deactivation() {
        wp_clear_scheduled_hook( 'nbdesigner_lincense_event' );
        wp_clear_scheduled_hook( 'nbdesigner_admin_notifications_event' );
        flush_rewrite_rules();
    }
    public function upload_mimes($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        $mimes['woff'] = 'application/x-font-woff';
        $mimes['ttf'] = 'application/x-font-ttf';
        $mimes['eps'] = 'application/postscript';
        return $mimes;
    }
    public function nbdesigner_settings(){
        $page_id = 'nbdesigner';
        $tabs = apply_filters('nbdesigner_settings_tabs', array(
            'general' => '<span class="dashicons dashicons-admin-generic"></span> ' . __('General', 'web-to-print-online-designer'),
            'appearance' => '<span class="dashicons dashicons-admin-appearance"></span> '. __('Appearance', 'web-to-print-online-designer'),            
            'frontend' => '<span class="dashicons dashicons-admin-customizer"></span> '. __('Design Tool', 'web-to-print-online-designer'),            
            'color' => '<span class="dashicons dashicons-art"></span> '. __('Colors', 'web-to-print-online-designer'),         
            'upload' => '<span class="dashicons dashicons-upload"></span> '. __('Upload design', 'web-to-print-online-designer'),         
            'output' => '<span class="dashicons dashicons-download"></span> '. __('Output', 'web-to-print-online-designer'),         
            'libraries' => '<span class="dashicons dashicons-paperclip"></span> '. __('Libraries', 'web-to-print-online-designer'),     
            'printing_option' => '<span class="dashicons dashicons-forms"></span> '. __('Printing Options', 'web-to-print-online-designer')         
        ));
        require_once(NBDESIGNER_PLUGIN_DIR . 'includes/settings/general.php');
        require_once(NBDESIGNER_PLUGIN_DIR . 'includes/settings/frontend.php');
        require_once(NBDESIGNER_PLUGIN_DIR . 'includes/settings/colors.php');
        require_once(NBDESIGNER_PLUGIN_DIR . 'includes/settings/upload.php');
        require_once(NBDESIGNER_PLUGIN_DIR . 'includes/settings/output.php');
        require_once(NBDESIGNER_PLUGIN_DIR . 'includes/settings/libraries.php');
        require_once(NBDESIGNER_PLUGIN_DIR . 'includes/settings/appearance.php');
        require_once(NBDESIGNER_PLUGIN_DIR . 'includes/settings/printing_option.php');
        do_action('nbdesigner_include_settings');
        $Nbdesigner_Settings = new Nbdesigner_Settings(array(
            'page_id' => $page_id,
            'tabs' => $tabs    
        ));   
        $blocks = apply_filters('nbdesigner_settings_blocks', array(
            'general' => array(
                'general-settings' => __('General Settings', 'web-to-print-online-designer'),
                'admin-notifications' => __('Notifications', 'web-to-print-online-designer'),
                'application' => __('Application', 'web-to-print-online-designer'),
                'nbd-pages' => __('NBD Pages', 'web-to-print-online-designer'),
                'tools' => __('Tools', 'web-to-print-online-designer'),
                'customization' => __('Customization', 'web-to-print-online-designer')
            ),
            'appearance' => array(
                'editor-settings' => __('Design editor', 'web-to-print-online-designer'),
                'modern-settings' => __('Modern layout', 'web-to-print-online-designer'),
                'product-settings' => __('Product', 'web-to-print-online-designer'),
                'category-settings' => __('Category', 'web-to-print-online-designer'),
                'cart-checkout-order-settings' => __('Cart, order, checkout', 'web-to-print-online-designer'),
                'misc-settings' => __('Misc', 'web-to-print-online-designer')
            ),            
            'frontend' => array(
                'tool-text' =>  __('Text Options', 'web-to-print-online-designer'),
                'tool-clipart' =>  __('Clipart Options', 'web-to-print-online-designer'),
                'tool-image' =>  __('Image Options', 'web-to-print-online-designer'),
                'tool-draw' =>  __('Free draw Options', 'web-to-print-online-designer'),
                'tool-qrcode' =>  __('Qr Code Options', 'web-to-print-online-designer'),
                'misc' =>  __('Misc', 'web-to-print-online-designer')
            ),
            'color' => array(
                'color-setting' =>  __('Setting color', 'web-to-print-online-designer')
            ),
            'upload' => array(
                'upload-settings' =>  __('Upload settings', 'web-to-print-online-designer')
            ),
            'output' => array(
                'output-settings' =>  __('Output general settings', 'web-to-print-online-designer'),
                'jpeg-settings' =>  __('JPG settings', 'web-to-print-online-designer')
            ),
            'libraries' => array(
                'js-settings' =>  __('Javacript settings', 'web-to-print-online-designer'),
                'css-settings' =>  __('CSS settings', 'web-to-print-online-designer')
            ), 
            'printing_option'   =>  array(
                'printing-general' => __('General', 'web-to-print-online-designer'),
                'printing-catalog' => __('Catalog', 'web-to-print-online-designer'),
                'printing-cart' => __('Cart', 'web-to-print-online-designer'),
                'printing-editor' => __('Design editor', 'web-to-print-online-designer')
            )
        ));  
        $Nbdesigner_Settings->add_blocks($blocks);
        $Nbdesigner_Settings->add_blocks_description(array());
        $frontend_options = Nbdesigner_Settings_Frontend::get_options();
        $general_options = Nbdesigner_Settings_General::get_options();
        $color_options = Nbdesigner_Settings_Colors::get_options();
        $upload_options = Nbdesigner_Settings_Upload::get_options();
        $output_options = Nbdesigner_Settings_Output::get_options();
        $libraries_options = Nbdesigner_Libraries::get_options();
        $appearance_options = Nbdesigner_Appearance_Settings::get_options();
        $printing_options = Nbdesigner_Printing_Options::get_options();
        $options = apply_filters('nbdesigner_settings_options', array(
            'general-settings' => $general_options['general-settings'],
            'admin-notifications' => $general_options['admin-notifications'],
            'application' => $general_options['application'],
            'nbd-pages' => $general_options['nbd-pages'],
            'customization' => $general_options['customization'],
            'tools' => $general_options['tools'],
            'tool-text' => $frontend_options['tool-text'],
            'tool-clipart' => $frontend_options['tool-clipart'],
            'tool-image' => $frontend_options['tool-image'],
            'tool-draw' => $frontend_options['tool-draw'],
            'tool-qrcode' => $frontend_options['tool-qrcode'],
            'misc' => $frontend_options['misc'],
            'color-setting' => $color_options['color-setting'],
            'upload-settings' => $upload_options['upload-settings'],
            'output-settings' => $output_options['output-settings'],
            'jpeg-settings' => $output_options['jpeg-settings'],
            'js-settings' => $libraries_options['js-settings'],
            'css-settings' => $libraries_options['css-settings'],
            'product-settings' => $appearance_options['product'],
            'editor-settings' => $appearance_options['editor'],
            'modern-settings' => $appearance_options['modern'],
            'category-settings' => $appearance_options['category'],
            'cart-checkout-order-settings' => $appearance_options['cart-checkout-order'],
            'misc-settings' => $appearance_options['misc'],
            'printing-general'  =>  $printing_options['general'],
            'printing-catalog'  =>  $printing_options['catalog'],
            'printing-cart'  =>  $printing_options['cart'],
            'printing-editor'  =>  $printing_options['editor']
        ));    
        foreach($options as $key => $option){
            $Nbdesigner_Settings->add_block_options( $key, $option);  
        }    
        do_action( 'nbdesigner_before_options_save', $page_id );
        if ( isset($_POST['nbdesigner_save_options_'.$page_id]) ) {
            check_admin_referer( $page_id.'_nonce' );             
            $Nbdesigner_Settings->save_options();
        }
        else if( isset($_POST['nbdesigner_reset_options_'.$page_id]) ) {
            check_admin_referer( $page_id.'_nonce' );
            $Nbdesigner_Settings->reset_options();
        } 
        add_action('nbdesigner_settings_header_start', array(&$this, 'display_license_key'));
        $Nbdesigner_Settings->output();    
    }
    public function admin_footer_text($footer_text){
        $current_screen = get_current_screen();
        $nbd_pages = nbd_admin_pages();
        if ( isset( $current_screen->id ) && in_array( $current_screen->id, $nbd_pages ) ){
            $footer_text = sprintf( __( 'If you <span style="color: #e25555;">♥</span> <strong>NBDesigner</strong> please leave us a %s&#9733;&#9733;&#9733;&#9733;&#9733;%s rating. A huge thanks in advance!', 'web-to-print-online-designer'), '<a href="https://wordpress.org/support/view/plugin-reviews/web-to-print-online-designer?filter=5#new-post" target="_blank" class="nbd-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'web-to-print-online-designer') . '">', '</a>' );
        }       
        return $footer_text;
    }
    public function display_license_key(){
        $license = $this->nbdesigner_check_license();
        $site_title = get_bloginfo( 'name' );
        $site_url = base64_encode(rtrim(get_bloginfo('wpurl'), '/'));   
        require_once(NBDESIGNER_PLUGIN_DIR . 'views/license-form.php');
    }
    public function nbdesigner_menu() { 
        $layout = nbdesigner_get_option('nbdesigner_design_layout');
        if( isset($_POST['nbdesigner_design_layout']) ){
            $layout = $_POST['nbdesigner_design_layout'];
        };
        if (current_user_can('manage_nbd_setting')) {
            add_menu_page('Nbdesigner', 'NBDesigner', 'manage_nbd_setting', 'nbdesigner', array($this, 'nbdesigner_settings'), NBDESIGNER_PLUGIN_URL . 'assets/images/logo-icon-r.svg', 26);
            $nbdesigner_manage = add_submenu_page(
                    'nbdesigner', __('NBDesigner Settings', 'web-to-print-online-designer'), __('Settings', 'web-to-print-online-designer'), 'manage_nbd_setting', 'nbdesigner', array($this, 'nbdesigner_settings')
            );
            add_action('load-'.$nbdesigner_manage, array('Nbdesigner_Helper', 'settings_helper'));
        }
        if(current_user_can('manage_nbd_product')){
            $product_hook = add_submenu_page(
                    'nbdesigner', __('Manager Products', 'web-to-print-online-designer'), __('Products', 'web-to-print-online-designer'), 'manage_nbd_product', 'nbdesigner_manager_product', array($this, 'nbdesigner_manager_product')
            );
            add_action( "load-$product_hook", array( $this, 'nbdesigner_template_screen_option' ));
            add_submenu_page(
                    '', __('Detail Design Order', 'web-to-print-online-designer'), __('Detail Design Order', 'web-to-print-online-designer'), 'manage_nbd_product', 'nbdesigner_detail_order', array($this, 'nbdesigner_detail_order')
            );
        }
        if(current_user_can('manage_nbd_art')){    
            add_submenu_page(
                    'nbdesigner', __('Manager Cliparts', 'web-to-print-online-designer'), __('Cliparts', 'web-to-print-online-designer'), 'manage_nbd_art', 'nbdesigner_manager_arts', array($this, 'nbdesigner_manager_arts')
            );
        }
        if(current_user_can('manage_nbd_font')){    
            add_submenu_page(
                    'nbdesigner', __('Manager Fonts', 'web-to-print-online-designer'), __('Fonts', 'web-to-print-online-designer'), 'manage_nbd_font', 'nbdesigner_manager_fonts', array($this, 'nbdesigner_manager_fonts')
            );
        }
        if(current_user_can('manage_nbd_language') && $layout == 'c'){  
            add_submenu_page(
                    'nbdesigner', __('Frontend Translate', 'web-to-print-online-designer'), __('Frontend Translate', 'web-to-print-online-designer'), 'manage_nbd_language', 'nbdesigner_frontend_translate', array($this, 'nbdesigner_frontend_translate')
            );             
        }
        do_action('nbd_menu');
        if (current_user_can('manage_nbd_tool')) {    
            add_submenu_page(
                    'nbdesigner', __('NBDesigner Tools', 'web-to-print-online-designer'), __('Tools', 'web-to-print-online-designer'), 'manage_nbd_tool', 'nbdesigner_tools', array($this, 'nbdesigner_tools')
            );  
//            add_submenu_page(
//                    'nbdesigner', __('NBDesigner Analytics', 'web-to-print-online-designer'), __('Analytics', 'web-to-print-online-designer'), 'administrator', 'nbdesigner_analytics', array($this, 'nbdesigner_analytics')
//            );            
        }
        $remote = get_transient('nbd_upgrade_news_web-to-print-online-designer');
        if( $remote ){
            add_submenu_page(
                'nbdesigner', __('NBDesigner Support', 'web-to-print-online-designer'), __('About', 'web-to-print-online-designer'), 'manage_nbd_setting', 'nbd_support', array($this, 'nbd_support')
            ); 
        }
    }
    public function nbdesigner_template_screen_option() {
        if(isset($_GET['view']) && $_GET['view'] == 'templates'){
            $option = 'per_page';
            $args   = array(
                'label'   => 'Templates',
                'default' => 10,
                'option'  => 'templates_per_page'
            );
            add_screen_option( $option, $args );            
        }
    }    
    public static function set_screen( $status, $option, $value ) {
        return $value;
    }
    public function nbdesigner_get_license_key(){       
        if (!wp_verify_nonce($_POST['nbdesigner_getkey_hidden'], 'nbdesigner-get-key') || !current_user_can('administrator')) {
            die('Security error');
        }        
        if(isset($_POST['nbdesigner'])){
            $data =$_POST['nbdesigner'];
            $email = base64_encode($data['email']);
            $domain = $data['domain'];
            $title = ($data['name'] != '') ? urlencode($data['name']) : urlencode($data['title']);
            $ip = base64_encode($this->nbdesigner_get_ip());
            $url = NBDESIGNER_AUTHOR_SITE.'subcrible/WPP1074/'.$email.'/'.$domain.'/'.$title .'/'.$ip;                  
            $result = nbd_file_get_contents($url);
            if(isset($result)) {
                echo $result;
            }else{
                echo 'Please try later!';
            }
            wp_die();
        }
    }
    public function nbdesigner_get_ip(){
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $ip;        
    }
    public static function nbdesigner_add_action_links($links){        
        $mylinks = array(
            'setting' => '<a href="' . admin_url('options-general.php?page=nbdesigner') . '">Settings</a>'
        );
        return array_merge($mylinks, $links);
    }
    public static function nbdesigner_plugin_row_meta( $links, $file ) {
        if($file == NBDESIGNER_PLUGIN_BASENAME){
            $row_meta = array(
                'livedemo' => '<a href="https://nbdesigner.cmsmart.net" target="_blank"><span class="dashicons  dashicons-laptop"></span>Live Demo</a>',
                'document' => '<a href="https://cmsmart.net/community/woocommerce-online-product-designer-plugin/userguides" target="_blank"><span class="dashicons dashicons-book-alt"></span>Document</a>',
                'support' => '<a href="https://cmsmart.net/support_ticket" target="_blank"><span class="dashicons  dashicons-businessman"></span>Support</a>',
                'community' => '<a href="https://cmsmart.net/community/woocommerce-online-product-designer-plugin" target="_blank"><span class="dashicons dashicons-groups"></span>Community</a>'
            );
            return array_merge( $links, $row_meta );
        }
        return (array) $links;
    }
    public function nbdesigner_manager_product() {
        if(isset($_GET['view']) && $_GET['view'] == "templates"){
            $pid = $_GET['pid'];
            $pro = wc_get_product($pid);            
            $templates_obj = new Product_Template_List_Table();  
            include_once(NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-admin-template.php');
        }else{
            $q = '';
            if( isset($_POST['q']) ){
                $q = $_POST['q'] != '' ? $_POST['q'] : '';
            }else if( isset($_GET['s']) && $_GET['s'] != '' ){
                $q =  $_GET['s'];
            }
            $products = get_transient( 'nbd_list_products' );
            $number_pro = get_transient( 'nbd_number_of_products' );
            $limit = apply_filters('nbd_admin_products_per_page', 20);
            if ( $q != '' || false === $products ) {
                $args_query = array(
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'meta_key' => '_nbdesigner_enable',
                    'orderby' => 'id',
                    'order' => 'DESC',
                    'posts_per_page'=> -1,
                    'meta_query' => array(
                        array(
                            'key' => '_nbdesigner_enable',
                            'value' => 1,
                        )
                    )
                );     
                if( $q != '' ) $args_query['s'] = $q;
                $products = get_posts($args_query);
                $number_pro = count( $products );
                if( $q == '' ){
                    set_transient( 'nbd_list_products' , $products, DAY_IN_SECONDS );  
                    set_transient( 'nbd_number_of_products' , $number_pro, DAY_IN_SECONDS );                      
                }
            }  
            $page = filter_input(INPUT_GET, "p", FILTER_VALIDATE_INT);
            if(isset($page)){
                $_tp = ceil($number_pro / $limit);
                if($page > $_tp) $page = $_tp;
                $products = array_slice($products, ($page-1)*$limit, $limit);
            }else{
                $page = 1;
                if($number_pro > $limit) $products = array_slice($products, ($page-1)*$limit, $limit);  
            }
            $pro = array();
            foreach ($products as $product) {
                $product_id = $product->ID;
                $_product = wc_get_product( $product_id );
                $pro[] = array(
                    'url'   => admin_url('post.php?post=' . absint( $product_id ) . '&action=edit'),
                    'img'   => $_product->get_image(),
                    'name'  => $product->post_title,
                    'id'    => absint( $product_id ),
                    'number_template'  =>  Product_Template_List_Table::count_product_template( $product_id )
                );
            }                       
            $url = admin_url('admin.php?page=nbdesigner_manager_product');
            if( $q != '' ) $url .= "&s={$q}";
            require_once NBDESIGNER_PLUGIN_DIR . 'includes/class.nbdesigner.pagination.php';
            $paging = new Nbdesigner_Pagination();
            $config = array(
                'current_page'  => isset($page) ? $page : 1, 
                'total_record'  => $number_pro,
                'limit'         => $limit,
                'link_full'     => $url.'&p={p}',
                'link_first'    => $url              
            );          
            $paging->init($config);            
            include_once(NBDESIGNER_PLUGIN_DIR . 'views/manager-product.php');
        }
    }
    public function delete_nbd_product_transient(){
        delete_transient( 'nbd_list_products' );
        delete_transient( 'nbd_number_of_products' );
        delete_transient( 'nbd_frontend_products' );
    }
    public function nbdesigner_add_font_cat() {
        $data = array(
                'mes'   =>  __('You do not have permission to add/edit font category!', 'web-to-print-online-designer'),
                'flag'  => 0
            );          
        $path = NBDESIGNER_DATA_DIR . '/font_cat.json';
        $list = $this->nbdesigner_read_json_setting($path);
        $cat = array(
            'name' => $_POST['name'],
            'id' => $_POST['id']
        );
        $this->nbdesigner_update_json_setting($path, $cat, $cat['id']);
        $data['mes'] = __('Category has been added/edited successfully!', 'web-to-print-online-designer');
        $data['flag'] = 1;        
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_add_art_cat() {    
        $data = array(
                'mes'   =>  __('You do not have permission to add/edit clipart category!', 'web-to-print-online-designer'),
                'flag'  => 0
            );          

        $path = NBDESIGNER_DATA_DIR . '/art_cat.json';
        $cat = array(
            'name' => sanitize_text_field($_POST['name']),
            'id' => $_POST['id']
        );
        $this->nbdesigner_update_json_setting($path, $cat, $cat['id']);
        $data['mes'] = __('Category has been added/edited successfully!', 'web-to-print-online-designer');
        $data['flag'] = 1;        
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_delete_font_cat() {
        $data = array(
                'mes'   =>  __('You do not have permission to delete font category!', 'web-to-print-online-designer'),
                'flag'  => 0
            );        

        $path = NBDESIGNER_DATA_DIR . '/font_cat.json';
        $id = $_POST['id'];
        $this->nbdesigner_delete_json_setting($path, $id, true);
        $font_path = NBDESIGNER_DATA_DIR . '/fonts.json';
        $this->nbdesigner_update_json_setting_depend($font_path, $id);
        $data['mes'] = __('Category has been delete successfully!', 'web-to-print-online-designer');
        $data['flag'] = 1;        
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_delete_art_cat() {
        $data = array(
                'mes'   =>  __('You do not have permission to delete clipart category!', 'web-to-print-online-designer'),
                'flag'  => 0
            );          

        $path = NBDESIGNER_DATA_DIR . '/art_cat.json';
        $id = $_POST['id'];
        $this->nbdesigner_delete_json_setting($path, $id, true);
        $art_path = NBDESIGNER_DATA_DIR . '/arts.json';
        $this->nbdesigner_update_json_setting_depend($art_path, $id);
        $data['mes'] = __('Category has been delete successfully!', 'web-to-print-online-designer');
        $data['flag'] = 1;        
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_get_list_google_font() {
        $path = NBDESIGNER_PLUGIN_DIR . 'data/listgooglefonts.json';
        $data = (array) $this->nbdesigner_read_json_setting($path);
        return json_encode($data);
    }
    public function nbdesigner_add_google_font() {
        $data = array(
                'mes'   =>  __('You do not have permission to add font!'),
                'flag'  => 0
            );        

        $gg_fonts = array();
        if( !isset($_POST['fonts']) ){
            die('Empty data');
        }else{
            $all_fonts = json_decode(file_get_contents(NBDESIGNER_PLUGIN_DIR. '/data/google-fonts-ttf.json'))->items;
            $fonts = json_decode(stripslashes($_POST['fonts']));
            foreach($fonts as $key => $font){
                $subset = 'all';
                $file = array('r' => 1);
                foreach( $all_fonts as $f ){
                    if( $font->name == $f->family ){
                        $subset = $f->subsets[0];
                        if( isset($f->files->italic) ){
                            $file['i'] = 1;
                        }
                        if( isset($f->files->{"700"}) ){
                            $file['b'] = 1;
                        }
                        if( isset($f->files->{"700italic"}) ){
                            $file['bi'] = 1;
                        }
                        break;
                    }
                }
                $gg_fonts[] = array(
                    "id"    =>  $key,
                    "name"    =>  $font->name,
                    "alias"    =>  $font->name,
                    "type"   =>  "google", 
                    "subset"   =>  $subset, 
                    "file"   =>  $file, 
                    "cat" => array("99")
                );             
            }
        }
        $path_font = NBDESIGNER_DATA_DIR . '/googlefonts.json';
        file_put_contents($path_font, json_encode($gg_fonts));
        $data['mes'] = __('The google fonts have been added successfully!');
        $data['flag'] = 1;        
        echo json_encode($data);

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cacheTypeList = $_objectManager->get('\Magento\Framework\App\Cache\TypeListInterface');
        $cacheTypeList->cleanType('full_page');

        wp_die();
    }
    public function nbdesigner_manager_fonts() {
        $notice = '';
        $font_id = 0;
        $cats = array("0");
        $current_font_cat_id = 0;
        $update = false;
        $list = $this->nbdesigner_read_json_setting(NBDESIGNER_DATA_DIR . '/fonts.json');   
        $cat = $this->nbdesigner_read_json_setting(NBDESIGNER_DATA_DIR . '/font_cat.json');
        $data_font_google = $this->nbdesigner_read_json_setting(NBDESIGNER_DATA_DIR . '/googlefonts.json');
        $list_all_google_font = $this->nbdesigner_get_list_google_font();
        $current_cat = filter_input(INPUT_GET, "cat_id", FILTER_VALIDATE_INT);
        $font = array(
            'file' => array()
        );
        if (is_array($cat))
            $current_font_cat_id = sizeof($cat);
        if (isset($_GET['id'])) {
            $font_id = $_GET['id'];
            $update = true;            
            if (isset($list[$font_id])) {
                $font_data = $list[$font_id];
                $cats = $font_data->cat;
                $font['url'] = $font_data->url;
                $font['id'] = $font_data->id;
                $font['type'] = $font_data->type;
                $font['alias'] = $font_data->alias;
                $font['subset'] = $font_data->subset;
                $font['file'] = (array)$font_data->file;
            }
        }
        if (isset($_POST[$this->plugin_id . '_hidden']) && wp_verify_nonce($_POST[$this->plugin_id . '_hidden'], $this->plugin_id) && current_user_can('edit_nbd_font')) {
            $font['name'] = esc_html($_POST['nbdesigner_font_name']);
            $font['subset'] = $_POST['subset'];
            $font['alias'] = isset($font['alias']) ? $font['alias'] : 'nbfont' . substr(md5(rand(0, 999999)), 0, 10);
            $font['cat'] = $cats;
            if (isset($_POST['nbdesigner_font_cat'])) $font['cat'] = $_POST['nbdesigner_font_cat'];
            $check = true;          
            if (isset($_FILES['font']) && array_filter($_FILES['font']['name']) && (!empty($font['file']['r']) || $_FILES['font']['name'][0] != '') && ($_POST['nbdesigner_font_name'] != '')) {
                foreach( $_FILES['font']['name'] as $key => $font_name ){
                    if( $font_name != '' ){
                        $uploaded_file_name = basename($font_name);                
                        $allowed_file_types = array('ttf');
                        $font['type'] = $this->nbdesigner_get_extension($uploaded_file_name);
                        if (Nbdesigner_IO::checkFileType($uploaded_file_name, $allowed_file_types)) {
                            switch ($key) {
                                case 1:
                                    $f_index = 'i';
                                    break;
                                case 2:
                                    $f_index = 'b';
                                    break;
                                case 3:
                                    $f_index = 'bi';
                                    break;
                                default:
                                    $f_index = 'r';
                            }                            
                            $prefix = $key ? $f_index : '';
                            $new_path_font = Nbdesigner_IO::create_file_path(NBDESIGNER_FONT_DIR, $font['alias'].$prefix, $font['type'], true);
                            if(move_uploaded_file($_FILES['font']["tmp_name"][$key], $new_path_font['full_path'].'.'.$font['type'])){
                                if( !$key ){
                                    $font['url'] = $new_path_font['date_path'] .'.'. $font['type'];                                
                                }
                                $font['file'][$f_index] = $new_path_font['date_path'] .'.'. $font['type'];                        
                            }else{
                                $check = false;
                                $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('error', __('Error while upload font.', 'web-to-print-online-designer')));
                                break;                            
                            }
                        }else{
                            $check = false;
                            $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('error', __('Incorrect file extensions.', 'web-to-print-online-designer')));
                            break;
                        }
                    }
                }
                if( $check ){
                    if ($update) {
                        $this->nbdesigner_update_list_fonts($font, $font_id);
                    } else {
                        $this->nbdesigner_update_list_fonts($font);
                    }
                    $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('success', __('Your font has been saved.', 'web-to-print-online-designer')));                    
                }
            } else if($update && ($_POST['nbdesigner_font_name'] != '')){
                $font_data->name = $_POST['nbdesigner_font_name'];
                $font_data->subset = $_POST['subset'];
                $font_data->cat = $font['cat'];
                if( isset($_POST['display_name']) ){
                    $font_data->display_name = $_POST['display_name'];
                }
                $this->nbdesigner_update_list_fonts($font_data, $font_id);
                $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('success', __('Your font has been saved.', 'web-to-print-online-designer')));                
            } else {
                $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('warning', __('Please choose font file or font name.', 'web-to-print-online-designer')));
            }
            $list = $this->nbdesigner_read_json_setting(NBDESIGNER_DATA_DIR . '/fonts.json');
            $cats = $font['cat'];
        }      
        $current_cat_id = 0;
        if(isset($current_cat)){
            $current_cat_id = $current_cat;
            $new_list = array();
            foreach($list as $art){    
                if(in_array((string)$current_cat, $art->cat)) $new_list[] = $art;
            }
            foreach($cat as $c){
                if($c->id == $current_cat){
                    $name_current_cat = $c->name;
                    break;
                } 
                $name_current_cat = 'uploaded';
            }
            $list = $new_list;             
        }else{
            $name_current_cat = 'uploaded';
        }
        $total = sizeof($list);
        //NBD_Update_Data::update_fonts();
        include_once(NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-manager-fonts.php');
    }
    /**
     * Analytics and statistics customer used product design
     * @since 1.5
     * 
    */    
    public function nbdesigner_analytics() {
        //TODO something analytics
        include_once(NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-analytics.php');
    }    
    public function nbdesigner_update_list_fonts($font, $id = null) {
        if (isset($id)) {
            $this->nbdesigner_update_font($font, $id);
            return;
        }
        $list_font = array();
        $path = NBDESIGNER_DATA_DIR . '/fonts.json';
        $list = $this->nbdesigner_read_json_setting($path);
        if (is_array($list)) {
            $list_font = $list;
            $id = sizeOf($list_font);
            $font['id'] = (string) $id;
        }
        $list_font[] = $font;
        $res = json_encode($list_font);
        file_put_contents($path, $res);
    }
    public function nbdesigner_update_list_arts($art, $id = null) {
        $path = NBDESIGNER_DATA_DIR . '/arts.json';
        if (isset($id)) {
            $this->nbdesigner_update_json_setting($path, $art, $id);
            return;
        }
        $list_art = array();
        $list = $this->nbdesigner_read_json_setting($path);
        if (is_array($list)) {
            $list_art = $list;
            $id = sizeOf($list_art);
            $art['id'] = (string) $id;
        }
        $list_art[] = $art;
        $res = json_encode($list_art);
        file_put_contents($path, $res);
    }
    public function nbdesigner_read_json_setting($fullname) {
        if (file_exists($fullname)) {
            $list = json_decode(file_get_contents($fullname));           
        } else {
            $list = '[]';
            file_put_contents($fullname, $list);
        }
        return $list;
    }
    public function nbdesigner_delete_json_setting($fullname, $id, $reindex = true) {
        $list = $this->nbdesigner_read_json_setting($fullname);
        if (is_array($list)) {
            array_splice($list, $id, 1);
            if ($reindex) {
                $key = 0;
                foreach ($list as $val) {
                    $val->id = (string) $key;
                    $key++;
                }
            }
        }
        $res = json_encode($list);
        file_put_contents($fullname, $res);
    }
    public function nbdesigner_update_json_setting($fullname, $data, $id) {
        $list = $this->nbdesigner_read_json_setting($fullname);
        if (is_array($list))
            $list[$id] = $data;
        else {
            $list = array();
            $list[] = $data;
        }
        $_list = array();
        foreach ($list as $val) {
            $_list[] = $val;
        }
        $res = json_encode($_list);
        file_put_contents($fullname, $res);
    }
    public function nbdesigner_update_json_setting_depend($fullname, $id) {
        $list = $this->nbdesigner_read_json_setting($fullname);
        if (!is_array($list)) return;
        foreach ($list as $val) {             
            if (!((sizeof($val) > 0))) continue;
            //if (!sizeof($val->cat)) break;           
            foreach ($val->cat as $k => $v) {
                if ($v == $id) {                   
                    array_splice($val->cat, $k, 1);
                    break;
                }
            }
            foreach ($val->cat as $k => $v) {
                if ($v > $id) {
                    $new_v = (string) --$v;
                    unset($val->cat[$k]);
                    array_splice($val->cat, $k, 0, $new_v);
                    //$val->cat[$k] = (string)--$v;                                     
                }
            }
        }
        $res = json_encode($list);
        file_put_contents($fullname, $res);
    }
    public function nbdesigner_delete_font() {
        $data = array(
                'mes'   =>  __('You do not have permission to delete font!', 'web-to-print-online-designer'),
                'flag'  => 0
            );        

        $id = $_POST['id'];
        $type = $_POST['type'];
        if ($type == 'custom') {
            $path = NBDESIGNER_DATA_DIR . '/fonts.json';
            $list = $this->nbdesigner_read_json_setting($path);
//            $file_font = $list[$id]->file;
//            if(file_exists($file_font)){
//                unlink($file_font);
//            }else{
//                $file_font = NBDESIGNER_FONT_DIR . $list[$id]->file;
//                unlink($file_font);
//            }
            
        } else
            $path = NBDESIGNER_DATA_DIR . '/googlefonts.json';
        $this->nbdesigner_delete_json_setting($path, $id);
        $data['mes'] = __('The font has been deleted successfully!', 'web-to-print-online-designer');
        $data['flag'] = 1;
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_delete_art() {
        $data = array(
                'mes'   =>  __('You do not have permission to delete clipart!', 'web-to-print-online-designer'),
                'flag'  => 0
            );

        $id = $_POST['id'];
        $path = NBDESIGNER_DATA_DIR . '/arts.json';
        $list = $this->nbdesigner_read_json_setting($path);
        $file_art = $list[$id]->file;
        if(file_exists($file_art)){
            unlink($file_art);
        }else{
            $file_art = NBDESIGNER_ART_DIR . $list[$id]->file;
            unlink($file_art);
        }        
        $this->nbdesigner_delete_json_setting($path, $id);
        $data['mes'] = __('Clipart has been deleted successfully!', 'web-to-print-online-designer');
        $data['flag'] = 1;
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_update_font($font, $id) {
        $path = NBDESIGNER_DATA_DIR . '/fonts.json';
        $this->nbdesigner_update_json_setting($path, $font, $id);
    }
    public function nbdesigner_notices($value = '') {
        return $value;
    }
    public function nbdesigner_custom_notices($command, $mes) {
        switch ($command) {
            case 'success':
                if (!isset($mes))
                    $mes = __('Your settings have been saved.', 'web-to-print-online-designer');
                $notice = '<div class="updated notice notice-success is-dismissible">
                                <p>' . $mes . '</p>
                                <button type="button" class="notice-dismiss">
                                    <span class="screen-reader-text">Dismiss this notice.</span>
                                </button>                 
                            </div>';
                break;
            case 'error':
                if (!isset($mes))
                    $mes = __('Irks! An error has occurred.', 'web-to-print-online-designer');
                $notice = '<div class="notice notice-error is-dismissible">
                                <p>' . $mes . '</p>
                                <button type="button" class="notice-dismiss">
                                    <span class="screen-reader-text">Dismiss this notice.</span>
                                </button>                 
                            </div>';
                break;
            case 'notices':
                if (!isset($mes))
                    $mes = __('Irks! An error has occurred.', 'web-to-print-online-designer');
                $notice = '<div class="notice notice-warning">
                                <p>' . $mes . '</p>               
                            </div>';
                break;             
            case 'warning':
                if (!isset($mes))
                    $mes = __('Warning.', 'web-to-print-online-designer');
                $notice = '<div class="notice notice-warning is-dismissible">
                                <p>' . $mes . '</p>
                                <button type="button" class="notice-dismiss">
                                    <span class="screen-reader-text">Dismiss this notice.</span>
                                </button>                 
                            </div>';
                break;
            default:
                $notice = '';
        }
        return $notice;
    }
    public function nbdesigner_manager_arts() {
        $notice = '';
        $current_art_cat_id = 0;
        $art_id = 0;
        $update = false;
        $cats = array("0");
        $list = $this->nbdesigner_read_json_setting(NBDESIGNER_DATA_DIR . '/arts.json');
        $cat = $this->nbdesigner_read_json_setting(NBDESIGNER_DATA_DIR . '/art_cat.json');
        $total = sizeof($list);
        $limit = 40;
        if (is_array($cat))
            $current_art_cat_id = sizeof($cat);
        if (isset($_GET['id'])) {
            $art_id = $_GET['id'];
            $update = true;
            if (isset($list[$art_id])) {
                $art_data = $list[$art_id];
                $cats = $art_data->cat;
            }
        }
        $page = filter_input(INPUT_GET, "p", FILTER_VALIDATE_INT);
        $current_cat = filter_input(INPUT_GET, "cat_id", FILTER_VALIDATE_INT);

        if (isset($_POST[$this->plugin_id . '_hidden']) && wp_verify_nonce($_POST[$this->plugin_id . '_hidden'], $this->plugin_id) && current_user_can('edit_nbd_art')) {
            $art = array();
            $art['id'] = $_POST['nbdesigner_art_id'];
            $art['cat'] = $cats;
            if (isset($_POST['nbdesigner_art_cat'])) $art['cat'] = $_POST['nbdesigner_art_cat'];
            if (isset($_FILES['svg'])) {
                $files = $_FILES['svg'];
                foreach ($files['name'] as $key => $value) {
                    $file = array(
                      'name'     => $files['name'][$key],
                      'type'     => $files['type'][$key],
                      'tmp_name' => $files['tmp_name'][$key],
                      'error'    => $files['error'][$key],
                      'size'     => $files['size'][$key]
                    );                    
                    $uploaded_file_name = basename($file['name']);
                    $allowed_file_types = array('svg', 'png', 'jpg', 'jpeg');
                    if (Nbdesigner_IO::checkFileType($uploaded_file_name, $allowed_file_types)) {
                        $upload_overrides = array('test_form' => false);
                        $uploaded_file = wp_handle_upload($file, $upload_overrides);
                        if (isset($uploaded_file['url'])) {
                            $new_path_art = Nbdesigner_IO::create_file_path(NBDESIGNER_ART_DIR, $uploaded_file_name);
                            $art['file'] = $uploaded_file['file'];
                            $art['url'] = $uploaded_file['url'];
                            $art['name'] = pathinfo($art['file'], PATHINFO_FILENAME);
                            if (!copy($art['file'], $new_path_art['full_path'])) {
                                $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('error', __('Failed to copy.', 'web-to-print-online-designer')));
                            }else{
                                $art['file'] = $new_path_art['date_path'];
                                $art['url'] = $new_path_art['date_path'];
                                //$art['url'] = NBDESIGNER_ART_URL . $new_path_art['date_path'];
                            }                                               
                            if ($update) {
                                $this->nbdesigner_update_list_arts($art, $art_id);
                            } else {
                                $this->nbdesigner_update_list_arts($art);
                            }
                            $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('success', __('Your art has been saved.', 'web-to-print-online-designer')));
                        } else {
                            $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('error', sprintf(__( 'Error while upload art, please try again! <a target="_blank" href="%s">Force upload SVG</a>', 'web-to-print-online-designer'), esc_url(admin_url('admin.php?page=nbdesigner&tab=general#nbdesigner_option_download_type')))));
                        }
                    } else {
                        $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('error', __('Incorrect file extensions.', 'web-to-print-online-designer')));
                    }
                }
            }
            $list = $this->nbdesigner_read_json_setting(NBDESIGNER_DATA_DIR . '/arts.json');
            $cats = $art['cat'];
            $total = sizeof($list);
            
        }
        $current_cat_id = 0;
        $name_current_cat = 'uploaded';
        if($total){
            if(isset($current_cat)){
                $current_cat_id = $current_cat;
                $new_list = array();
                foreach($list as $art){  
                    if(in_array((string)$current_cat, $art->cat)) $new_list[] = $art;
                    if(($current_cat == 0) && sizeof($art->cat) == 0) $new_list[] = $art;
                }
                foreach($cat as $c){
                    if($c->id == $current_cat){
                        $name_current_cat = $c->name;
                        break;
                    } 
                    $name_current_cat = 'uploaded';
                }
                $list = $new_list;
                $total = sizeof($list);               
            }else{
                $name_current_cat = 'uploaded';
            }
            if(isset($page)){
                $_tp = ceil($total / $limit);
                if($page > $_tp) $page = $_tp;
                $_list = array_slice($list, ($page-1)*$limit, $limit);
            }else{
                $_list = $list;
                if($total > $limit) $_list = array_slice($list, 0, $limit); 
            }
        } else{
            $_list = array();
        }        
        if(isset($current_cat)){
            $url = add_query_arg(array('cat_id' => $current_cat), admin_url('admin.php?page=nbdesigner_manager_arts'));
        }else{
            $url = admin_url('admin.php?page=nbdesigner_manager_arts');   
        }
        require_once NBDESIGNER_PLUGIN_DIR . 'includes/class.nbdesigner.pagination.php';
        $paging = new Nbdesigner_Pagination();
        $config = array(
            'current_page'  => isset($page) ? $page : 1, 
            'total_record'  => $total,
            'limit'         => $limit,
            'link_full'     => $url.'&p={p}',
            'link_first'    => $url              
        );          
        $paging->init($config);
        include_once(NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-manager-arts.php');
    }
    public function admin_success() {
        if (isset($_POST[$this->plugin_id . '_hidden']) && wp_verify_nonce($_POST[$this->plugin_id . '_hidden'], $this->plugin_id)){
            echo '<div class="updated notice notice-success is-dismissible">
                        <p>' . __('Your settings have been saved.', 'web-to-print-online-designer') . '</p>
                        <button type="button" class="notice-dismiss">
                            <span class="screen-reader-text">Dismiss this notice.</span>
                        </button>                 
                  </div>';
        }
    }
    public function add_design_box() {
        add_meta_box('nbdesigner_setting', __('Setting NBDesigner', 'web-to-print-online-designer'), array($this, 'setting_design'), 'product', 'normal', 'high');
        add_meta_box('nbdesigner_order', __('Customer Design', 'web-to-print-online-designer'), array($this, 'order_design'), 'shop_order', 'side', 'default');
    }
    public function nbdesigner_detail_order() {
        if(isset($_GET['order_id'])){
            $order_id = absint( $_GET['order_id'] );
            $order = new WC_Order($order_id);   
            $zip_files = array();
            $files = array();
            if(isset($_GET['download-type']) && ($_GET['download-type'] == 'png-hires' || $_GET['download-type'] == 'jpg-hires' || $_GET['download-type'] == 'pdf' || $_GET['download-type'] == 'png' || $_GET['download-type'] == 'jpg' || $_GET['download-type'] == 'cmyk' || $_GET['download-type'] == 'svg' || $_GET['download-type'] == 'ibg')){
                $type =$_GET['download-type'];
                $product_id = absint( $_GET['product_id'] );
                $variation_id = absint( $_GET['variation_id'] );
                $nbd_item_key = $_GET['nbd_item_key'];                
                $path = NBDESIGNER_CUSTOMER_DIR .'/'. $_GET['nbd_item_key'];
                if( $type != 'png' && $type != 'svg' && $type != 'ibg' && $type != 'png-hires' && $type != 'jpg-hires' && $type != 'pdf' ) $path = $path . '/' .$type;
                if( is_available_imagick() && ( $type == 'ibg' || $type == 'png-hires' || $type == 'jpg-hires' || $type == 'pdf' ) ){
                    $config = nbd_get_data_from_json($path . '/config.json');
                    nbd_export_pdfs( $nbd_item_key, false, false, 'no' );
                    $pdf_path = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key . '/customer-pdfs';
                    $list_pdf = Nbdesigner_IO::get_list_files_by_type($pdf_path, 1, 'pdf');
                    if( !isset($config->dpi) ){
                        $option = unserialize( file_get_contents( $path . '/option.json' ) );
                        $dpi = $option['dpi'];
                    }else{
                        $dpi = $config->dpi;
                    }
                    if(count($list_pdf)){
                        foreach($list_pdf as $key => $pdf){
                            if( $type == 'ibg' ){
                                NBD_Image::pdf2image($pdf, $dpi, 'png');
                                NBD_Image::pdf2image($pdf, $dpi, 'jpg');
                            }else if( $type == 'png-hires' ){
                                NBD_Image::pdf2image($pdf, $dpi, 'png');
                            }else if( $type == 'jpg-hires' ){
                                NBD_Image::pdf2image($pdf, $dpi, 'jpg');
                            }
                        }
                        if( $type == 'pdf'){
                            $files = $list_pdf;
                        }
                        $path = $pdf_path;
                    }
                }
                if( $type == 'svg' ){
                    $svg_path = $path . '/svg';  
                    if( !file_exists($svg_path) ){
                        $this->convert_svg_embed($path);
                    }
                    $files = Nbdesigner_IO::get_list_files_by_type($svg_path, 1, 'svg');  
                }else if( $type != 'pdf' ){
                    $files = Nbdesigner_IO::get_list_images($path, 1);   
                    if( $type == 'jpg-hires' ){
                        $files = Nbdesigner_IO::get_list_files_by_type($path, 1, 'jpg|jpeg');
                    }
                    if( $type == 'png-hires' ){
                        $files = Nbdesigner_IO::get_list_files_by_type($path, 1, 'png');
                    }
                }
                if(count($files) > 0){
                    foreach($files as $key => $file){
                        $zip_files[] = $file;
                    }
                } else{
                    $arr = array('nbd_item_key' => $nbd_item_key, 'order_id'    =>  $order_id, 'product_id' => $product_id, 'variation_id' => $variation_id);
                    $url = add_query_arg($arr, admin_url('admin.php?page=nbdesigner_detail_order'));      
                    wp_redirect( $url );
                    exit();
                }                 
                $pathZip = NBDESIGNER_DATA_DIR.'/download/customer-design-'.$_GET['nbd_item_key'].'-'.$type.'.zip';
                $nameZip = 'customer-design-'.$_GET['nbd_item_key'].'-'.$type.'.zip';
                nbd_zip_files_and_download($zip_files, $pathZip, $nameZip); 
                exit();
            }
            if(isset($_GET['download-all']) && ($_GET['download-all'] == 'true')){
                $products = $order->get_items();
                foreach($products AS $order_item_id => $product){                
                    if(wc_get_order_item_meta($order_item_id, '_nbd') || wc_get_order_item_meta($order_item_id, '_nbu')){
                        $nbd_item_key = wc_get_order_item_meta($order_item_id, '_nbd');
                        $nbu_item_key = wc_get_order_item_meta($order_item_id, '_nbu');
                        if( $nbd_item_key ){
                            $list_images = Nbdesigner_IO::get_list_images(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key, 1);
                            if(count($list_images) > 0){
                                foreach($list_images as $key => $image){
                                    $zip_files[] = $image;
                                }
                            }                            
                        }
                        if( $nbu_item_key ){
                            $files = Nbdesigner_IO::get_list_files( NBDESIGNER_UPLOAD_DIR .'/'. $nbu_item_key );
                            if(count($files) > 0){
                                foreach($files as $key => $file){
                                    $zip_files[] = $file;
                                }
                            }                             
                        }
                    }              
                }
                if( !count($zip_files) ){
                    exit();
                }else{
                    $pathZip = NBDESIGNER_DATA_DIR.'/download/customer-design-'.$_GET['order_id'].'.zip';
                    $nameZip = 'customer-design-'.$_GET['order_id'].'.zip';
                    nbd_zip_files_and_download($zip_files, $pathZip, $nameZip);                    
                }
            }
            if(isset($_GET['nbd_item_key'])){
                $license = $this->nbdesigner_check_license();
                $path = NBDESIGNER_CUSTOMER_DIR .'/'. $_GET['nbd_item_key'];
                $list_images = Nbdesigner_IO::get_list_images($path, 1);
                $_list_pdfs = Nbdesigner_IO::get_list_files($path . '/pdfs', 2);
                $list_design = array();
                $list_pdfs = array();
                $datas = unserialize(file_get_contents($path .'/product.json'));
                $option = unserialize(file_get_contents($path .'/option.json'));
                $path_config = $path . '/config.json';  
                $resources = (array)json_decode( file_get_contents( $path. '/design.json' ) );
                $fonts = array();
                if(file_exists( NBDESIGNER_DATA_DIR . '/fonts.json') ){
                    $fonts = (array)json_decode( file_get_contents( NBDESIGNER_DATA_DIR . '/fonts.json' ) );        
                }
                $config = nbd_get_data_from_json($path_config);
                if( isset( $config->product ) && count( $config->product ) ){
                    $datas = array();
                    foreach($config->product as $side){
                        $datas[] = (array)$side;
                    }
                };                    
                if( isset( $config->custom_dimension ) ){
                    $custom_side = absint( $config->custom_dimension->side );
                    $custom_width = $config->custom_dimension->width;
                    $custom_height = $config->custom_dimension->height;
                    $datas = $this->merge_product_config( $datas, $custom_width, $custom_height, $custom_side );
                }                  
                foreach ($_list_pdfs as $_pdf){
                    if(is_dir($_pdf)) continue;
                    $list_pdfs[] = array(
                        'title' => basename($_pdf),
                        'url'   =>  Nbdesigner_IO::wp_convert_path_to_url($_pdf)
                    );
                }
                foreach ($list_images as $img){
                    $name = basename($img);
                    $arr = explode('.', $name);
                    $_frame = explode('_', $arr[0]);
                    $frame = $_frame[1];
                    $list_design[$frame] = Nbdesigner_IO::wp_convert_path_to_url($img);
                }              
            }
        }
        require_once NBDESIGNER_PLUGIN_DIR .'views/detail-order.php';
    }
    public function setting_design() {
        $current_screen = get_current_screen();
        $post_id = get_the_ID();
        $_designer_setting = unserialize(get_post_meta($post_id, '_designer_setting', true));
        $enable = get_post_meta($post_id, '_nbdesigner_enable', true);  
        $enable_upload = get_post_meta($post_id, '_nbdesigner_enable_upload', true);  
        $upload_without_design = get_post_meta($post_id, '_nbdesigner_enable_upload_without_design', true);  
        $unit = nbdesigner_get_option('nbdesigner_dimensions_unit');
        if (isset($_designer_setting[0])){
            foreach ($_designer_setting as $key => $set ){
                $_designer_setting[$key] = array_merge(nbd_default_product_setting(), $set);
            }
            $designer_setting = $_designer_setting;
            if(! isset($designer_setting[0]['version']) || $_designer_setting[0]['version'] < 160) {
                $designer_setting = $this->update_config_product_160($designer_setting);              
            }
            if(! isset($designer_setting[0]['version']) || $_designer_setting[0]['version'] < 180) {
                $designer_setting = NBD_Update_Data::nbd_update_media_v180($designer_setting);
            }
        }else {
            $designer_setting = array();
            $designer_setting[0] = nbd_default_product_setting();           
        }
        $option = unserialize(get_post_meta($post_id, '_nbdesigner_option', true));
        $_option = nbd_get_default_product_option();
        if( !is_array($option) ){
            $option = array();
        }
        $option = array_merge($_option, $option);       
        $upload_setting = unserialize(get_post_meta($post_id, '_nbdesigner_upload', true));
        $_upload_setting = nbd_get_default_upload_setting();
        if( !is_array($upload_setting) ){
            $upload_setting = array();
        }   
        $upload_setting = array_merge($_upload_setting, $upload_setting);
        $designer_setting = nbd_update_config_default($designer_setting);
        include_once(NBDESIGNER_PLUGIN_DIR . 'views/metabox-design-setting.php');
    }
    public function update_config_product_160($designer_setting){
        $newSetting = array();
        $default =  nbd_default_product_setting();    
        foreach ($designer_setting as $key => $setting){
            $setting160 = array();     
            $scale = 1;
            if(($setting['img_src_width'] > $setting['img_src_height']) && ($setting['img_src_width'] < 300)) $scale = 300/ $setting['img_src_width'];
            if(($setting['img_src_width'] < $setting['img_src_height']) && ($setting['img_src_height'] < 300)) $scale = 300/ $setting['img_src_height'];
            $ratio150 = 300 / $setting['area_design_width'] / $scale * $setting['real_width'];
            $product_width = round($ratio150 * $setting['img_src_width'] * $scale / 300, 2);
            $product_height = round($ratio150 * $setting['img_src_height'] * $scale / 300, 2);
            $area_design_width = round($setting['area_design_width'] * $scale * 5/3);
            $area_design_height = round($setting['area_design_height'] * $scale * 5/3);
            $old_real_top = $ratio150 * $setting['area_design_top'] * $scale / 300;
            $old_real_left = $ratio150 * $setting['area_design_left'] * $scale / 300;
            $ratio = 500 / $product_height;
            $img_src_top = 0;
            $img_src_left = round(($product_height - $product_width) * $ratio / 2, 2);
            $img_src_height = 500;
            $img_src_width = round($product_width * $ratio);
            $real_top = round($old_real_top, 2);
            $real_left = round($old_real_left - ($product_height - $product_width) / 2, 2);
            if($product_width > $product_height){
                $img_src_top = round(($product_width - $product_height) * $ratio / 2, 2);
                $img_src_left = 0;
                $img_src_width = 500;
                $img_src_height = round($product_height * $ratio);
                $real_left = round($old_real_left, 2);
                $real_top = round($old_real_top - ($product_width - $product_height) / 2, 2);
            }
            $area_design_left = round($setting['area_design_left'] * $scale * 5/3);
            $area_design_top = round($setting['area_design_top'] * $scale * 5/3);
            $setting160['product_width'] = $product_width;
            $setting160['product_height'] = $product_height;
            $setting160['real_width'] = $setting['real_width'];
            $setting160['real_height'] = $setting['real_height'];
            $setting160['real_left'] = $real_left;
            $setting160['real_top'] = $real_top;
            $setting160['area_design_left'] = $area_design_left;
            $setting160['area_design_top'] = $area_design_top;
            $setting160['area_design_width'] = $area_design_width;
            $setting160['area_design_height'] = $area_design_height;
            $setting160['img_src_top'] = $img_src_top;
            $setting160['img_src_left'] = $img_src_left;
            $setting160['img_src_height'] = $img_src_height;
            $setting160['img_src_width'] = $img_src_width;
            $setting160['img_src'] = $setting['img_src'];
            $setting160['orientation_name'] = $setting['orientation_name'];   
            $setting160 = array_merge($default, $setting160);
            $newSetting[$key] = $setting160;
        }
        return $newSetting;
    }
    public function nbdesigner_update_all_product(){
        if (!wp_verify_nonce($_POST['_nbdesigner_update_product'], 'nbdesigner-update-product') || !current_user_can('administrator')) {
            die('Security error');
        }         
        $args_query = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'meta_key' => '_nbdesigner_enable',
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page'=> -1,
            'meta_query' => array(
                array(
                    'key' => '_nbdesigner_enable',
                    'value' => 1,
                )
            )
        ); 
        $posts = get_posts($args_query);   
        $result = array('flag' => 1);
        if(is_array($posts)){    
            foreach ($posts as $post){
                $product = wc_get_product($post->ID);       
                if( $product->is_type( 'variable' ) ) { 
                    $variations = $product->get_available_variations( false );
                    foreach ($variations as $variation){
                        $vid = $variation['variation_id'];
                        $designer_setting = unserialize(get_post_meta($vid, '_designer_setting'.$vid, true));
//                        if(! isset($designer_setting[0]['version']) || $designer_setting[0]['version'] < 160) {
//                            $newSetting = $this->update_config_product_160($designer_setting);
//                            update_post_meta($vid, '_designer_setting'.$vid, serialize($newSetting));                     
//                        } 
                        if(! isset($designer_setting[0]['version']) || $designer_setting[0]['version'] < 170) {
                            $setting_design = $this->update_config_product_170($designer_setting);
                            update_post_meta($vid, '_designer_variation_setting', serialize($setting_design));
                        }
                    }
                }
                $pid = $post->ID;
                $designer_setting = unserialize(get_post_meta($pid, '_designer_setting', true));
//                if(! isset($designer_setting[0]['version']) || $designer_setting[0]['version'] < 160) {
//                    $newSetting = $this->update_config_product_160($designer_setting);
//                    update_post_meta($pid, '_designer_setting', serialize($newSetting));                  
//                }  
                if(! isset($designer_setting[0]['version']) || $designer_setting[0]['version'] < 170) {
                    $dpi = get_post_meta($pid, '_nbdesigner_dpi', true);
                    $option = nbd_get_default_product_option();
                    $option['dpi'] = $dpi;
                    update_post_meta($pid, '_nbdesigner_option', serialize($option)); 
                    $setting_upload = nbd_get_default_upload_setting();
                    update_post_meta($pid, '_nbdesigner_upload', $setting_upload);
                    $setting_design = $this->update_config_product_170($designer_setting);
                    update_post_meta($pid, '_designer_setting', serialize($setting_design));
                }                   
            }
        }
        echo json_encode($result);
        wp_die();
    }
    public function update_config_product_170($designer_setting){
        foreach($designer_setting as $key => $val ){
            $designer_setting[$key] = array_merge(nbd_default_product_setting(), $val);
        }
        return $designer_setting;
    }
    public function order_design($post) {
        $order_id = $post->ID;
        $order = new WC_Order($order_id);         
        $products = $order->get_items();
        $_data_designs = unserialize(get_post_meta($order_id, '_nbdesigner_design_file', true));
        if(isset($_data_designs) && is_array($_data_designs))    $data_designs = $_data_designs;
        $_data_uploads = unserialize(get_post_meta($order_id, '_nbdesigner_upload_file', true));
        if(isset($_data_uploads) && is_array($_data_uploads))    $data_uploads = $_data_uploads;        
        $has_design = get_post_meta($order_id, '_nbd', true);
        $has_upload = get_post_meta($order_id, '_nbu', true);
        include_once(NBDESIGNER_PLUGIN_DIR . 'views/box-order-metadata.php');
    }
    public function nbd_delete_order_design(){
        check_admin_referer('approve-design-email', 'nonce');
        $result = array(
            'flag'  =>  false
        );
        $order_id = $_POST['order_id'];
        $order = wc_get_order( $order_id );
        foreach( $order->get_items() as $item_id => $item_product ){
            $result['flag'] = $result['flag'] || wc_delete_order_item_meta($item_id, '_nbd');
            $result['flag'] = $result['flag'] || wc_delete_order_item_meta($item_id, '_nbu');        
        }
        $result['flag'] = $result['flag'] || delete_post_meta($order_id, '_nbd') || delete_post_meta($order_id, '_nbu');
        wp_send_json($result);
    }
    public function nbdesigner_allow_create_product($id){
        $args = array(
            'post_type'  => 'product',
            'meta_key' => '_nbdesigner_enable',
            'meta_value' => 1
        );
        $query = new WP_Query($args);
        $pros = get_posts($args);
        $list = array();
        foreach ($pros as $pro){
            $list[] = $pro->ID;
        }        
        $count = $query->found_posts;
        $license = $this->nbdesigner_check_license();
        if(!isset($license['key'])) return false;
        if(in_array($id, $list)) return true;
        $salt = md5($license['key'].'pro'); 
        if(($salt != $license['salt']) && ($count > 500)) return false;
        return true;
    }
    public function nbdesigner_get_info_license(){
        if (!wp_verify_nonce($_POST['_nbdesigner_license_nonce'], 'nbdesigner-active-key') || !current_user_can('administrator')) {
            die('Security error');
        } 
        $result = array();
        if(isset($_POST['nbdesigner']['license'])) {
            $license = $_POST['nbdesigner']['license'];            
            $result_from_json = $this->nbdesiger_request_license($license, $this->activedomain);
            $data = (array)json_decode($result_from_json);            
            if(isset($data)) {
                switch ($data["code"]) {
                    case -1 :
                        $result['mes'] = __('Missing necessary information!', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;
                    case 0 :
                        $result['mes'] = __('Incorrect information, check again license key', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;     
                    case 1 :
                        $result['mes'] = __('Incorrect License key', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;
                    case 2 :
                        $result['mes'] = __('License key is locked ', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break; 
                    case 3 :
                        $result['mes'] = __('License key have expired', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;
                    case 4 :
                        $result['mes'] = __('Link your website incorrect', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;     
                    case 5 :
                        $result['mes'] = __('License key can using', 'web-to-print-online-designer');
                        $result['flag'] = 1;
                        break;
                    case 6 :
                        $result['mes'] = __('Domain has been added successfully', 'web-to-print-online-designer');
                        $result['flag'] = 1;
                        break;     
                    case 7 :
                        $result['mes'] = __('Exceed your number of domain license', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;
                    case 8 :
                        $result['mes'] = __('Unsuccessfully active license key', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;                     
                }            
                $data['key'] = $license;
                $data['salt'] = md5($license.$data['type']);
                if($data['type'] == 'free') $data['number_domain'] = "5";
                if(($data["code"] == 5) || ($data["code"] == 6)){
                    //$this->nbdesigner_write_license(json_encode($data));  
                    update_option('nbdesigner_license', json_encode($data));
                }                      
            }else{
                $result['mes'] = __('Try again later!', 'web-to-print-online-designer');
            }
        }else {
            $result['mes'] = __('Please fill your license!', 'web-to-print-online-designer');
        }
        echo json_encode($result);
        wp_die();
    }
    public function nbdesigner_remove_license(){
        if (!wp_verify_nonce($_POST['_nbdesigner_license_nonce'], 'nbdesigner-active-key') || !current_user_can('administrator')) {
            die('Security error');
        }           
        $result = array();
        $result['flag'] = 0;
        $path = NBDESIGNER_PLUGIN_DIR . 'data/license.json';
        $path_data = NBDESIGNER_DATA_CONFIG_DIR . '/license.json';
        if(file_exists($path_data)) {
            $path = $path_data;
        }
        $license = $this->nbdesigner_check_license();
        $key = (isset($license['key'])) ? $license['key'] : '';
        $_request = $this->nbdesiger_request_license($key, $this->removedomain);  
        if(isset($_request)){              
            $request = (array)json_decode($_request);                   
            switch ($request["code"]) {
                case -1:
                    $result['mes'] = __('Missing necessary information', 'web-to-print-online-designer');
                    break;
                case 0:
                    $result['mes'] = __('Incorrect information', 'web-to-print-online-designer');
                    break;
                case 1:
                    $result['mes'] = __('Incorrect License key', 'web-to-print-online-designer');
                    break;
                case 2: 
                    if(file_exists($path)) unlink($path);                        
                    $result['mes'] = __('Remove license key Successfully', 'web-to-print-online-designer');
                    $result['flag'] = 1;
                    delete_option('nbdesigner_license');
                    break;  
                case 3:
                    $result['mes'] = __('Remove license key Unsuccessfully!', 'web-to-print-online-designer');
                    break;                    
            }
        }     
        echo json_encode($result);
        wp_die();        
    }
    private function nbdesiger_request_license($license, $task){
        $url_root = base64_encode(rtrim(get_bloginfo('wpurl'), '/'));   
        $url = NBDESIGNER_AUTHOR_SITE.$task.NBDESIGNER_SKU.'/'.$license.'/'.$url_root;          
        return nbd_file_get_contents($url);
    }
    private function nbdesigner_write_license($license){
        $path_data = NBDESIGNER_DATA_CONFIG_DIR . '/license.json';
        if (!file_exists(NBDESIGNER_DATA_CONFIG_DIR)) {
            wp_mkdir_p(NBDESIGNER_DATA_CONFIG_DIR);
        }         
        file_put_contents($path_data, $license);
        update_option('nbdesigner_license', $license);
    }
    private function nbdesigner_check_license(){
//        $path_data = NBDESIGNER_DATA_CONFIG_DIR . '/license.json';
//        $path = NBDESIGNER_PLUGIN_DIR . 'data/license.json';
//        if(file_exists($path_data)) $path = $path_data;
        $result = array();
        $result['status'] = 1;
        
        $data = nbd_get_license_key();
        
        if( $data['key'] == '' ){
            $result['status'] = 0;
        }else{
            //$data = (array) json_decode(file_get_contents($path));
            $code = (isset($data["code"])) ? $data["code"] : 10;
            if(($code != 5) && ($code != 6)){
                $result['status'] = 0;
            }
            $now = strtotime("now");
            $expiry_date = (isset($data["expiry-date"])) ? $data["expiry-date"] : 0;
            $result['expiry'] = $expiry_date;
            if($expiry_date < $now){
                $result['status'] = 0;             
            }
            if(isset($data['key'])) $result['key'] = $data['key'];
            if(isset($data['type'])) {                  
                $result['type'] = $data['type'];
                $license = (isset($data['key'])) ? $data['key'] : 'somethingiswrong';
                $salt = (isset($data['salt'])) ? $data['salt'] : 'somethingiswrong';
                $new_salt = md5($license.'pro');                    
                if($salt == $new_salt){
                    $result['type'] = $data['type'];
                }else{
                    $result['type'] = 'free';
                    $result['status'] = 0;    
                } 
                $result['salt'] = $salt;
            }
        }
        return $result;
    }
    public function save_settings($post_id) {
        if (!isset($_POST['nbdesigner_setting_box_nonce']) || !wp_verify_nonce($_POST['nbdesigner_setting_box_nonce'], 'nbdesigner_setting_box')
            || !(current_user_can('administrator') || current_user_can('shop_manager'))) {
            return $post_id;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }
        $post = $_POST;
        $post = apply_filters('nbd_post_settings', $post);
        $enable = $post['_nbdesigner_enable']; 
        $enable_upload = $post['_nbdesigner_enable_upload'];
        $upload_without_design = $post['_nbdesigner_enable_upload_without_design']; 
        $option = serialize($post['_nbdesigner_option']);
        $setting_design = serialize($post['_designer_setting']);  
        $setting_upload = serialize($post['_designer_upload']);  
        if(!$this->nbdesigner_allow_create_product($post_id)) return;
        update_post_meta($post_id, '_designer_setting', $setting_design);
        update_post_meta($post_id, '_nbdesigner_option', $option);
        update_post_meta($post_id, '_nbdesigner_upload', $setting_upload);
        update_post_meta($post_id, '_nbdesigner_enable_upload_without_design', $upload_without_design);
        update_post_meta($post_id, '_nbdesigner_enable', $enable);
        update_post_meta($post_id, '_nbdesigner_enable_upload', $enable_upload);
    }
    public function nbdesigner_get_extension($file_name) {
        $filetype = explode('.', $file_name);
        $file_exten = $filetype[count($filetype) - 1];
        return $file_exten;
    }
    public function nbdesigner_button() {
        /* Quick view */
        if( isset($_REQUEST['wc-api']) && $_REQUEST['wc-api'] == 'NBO_Quick_View' ){
            return '';
        }
        if( (isset($_REQUEST['wc-api']) && $_REQUEST['wc-api'] == 'WC_Quick_View') || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'yith_load_product_quick_view') ){
            global $product;
            $pid = $product->get_id();
            $is_nbdesign = get_post_meta($pid, '_nbdesigner_enable', true); 
            if($is_nbdesign){
                $url = esc_url( get_permalink($pid) );
                $type = $product->get_type();
                $_enable_upload = get_post_meta($pid, '_nbdesigner_enable_upload', true);
                $_enable_upload_without_design = get_post_meta($pid, '_nbdesigner_enable_upload_without_design', true);
                $label = apply_filters('nbd_start_design_label', __('Start Design', 'web-to-print-online-designer'));
                if( $_enable_upload ){
                    $label = apply_filters('nbd_start_design_and_upload_label', __('Start and upload design', 'web-to-print-online-designer'));
                }
                if( $_enable_upload_without_design ){
                    $label = apply_filters('nbd_upload_design_label', __('Upload Design', 'web-to-print-online-designer'));
                } 
                $option = unserialize(get_post_meta($pid, '_nbdesigner_option', true));
                $layout = isset($option['layout']) ? $option['layout'] : 'm';
                if( $layout == 'v'){
                    $url = $product->get_permalink();
                }else{
                    $url =  add_query_arg(array(
                        'product_id'    =>  $pid,
                        'view'    =>  $layout
                        ),  getUrlPageNBD('create'));
                }
                ?>
                <p>
                    <a class="button alt nbdesign-button" href="<?php echo $url; ?>" >
                        <?php echo $label; ?>
                    </a>
                </p>
        <?php
            }
            return;
        }
        if(isset($_POST['action'])) return ''; /*Hidden button Start Design on third-party plugin as Quick view*/
        $pid = get_the_ID();   
        $pid = get_wpml_original_id($pid);       
        $is_nbdesign = get_post_meta($pid, '_nbdesigner_enable', true); 
        $variation_id = 0;
        if ($is_nbdesign) {
            $product = wc_get_product($pid);
            $type = $product->get_type();
            $option = unserialize(get_post_meta($pid, '_nbdesigner_option', true));
            $layout = isset($option['layout']) ? $option['layout'] : 'm';
            if($layout == 'v') return;
            /* Multi language with WPML */
            if( count($_REQUEST) ){
                $attributes = array();
                $layout = nbdesigner_get_option('nbdesigner_design_layout');
                if($layout == "c"){
                    foreach ($_REQUEST as $key => $value){
                        if (strpos($key, 'attribute_') === 0) {
                            $attributes[$key] = $value;
                        }
                    }
                    if( count($attributes) ){
                        if (class_exists('WC_Data_Store')) {
                            $data_store = WC_Data_Store::load('product');
                            $variation_id = $data_store->find_matching_product_variation($product, $attributes);
                        }else{
                            $variation_id = $product->get_matching_variation($attributes);
                        }
                    }    
                }
            }           
            $site_url = site_url();
            if ( class_exists('SitePress') ) {
                $site_url = home_url();
            }            
            $src = add_query_arg(array('action' => 'nbdesigner_editor_html', 'product_id' => $pid), $site_url . '/');    
            if(isset($_POST['variation_id']) &&  $_POST['variation_id'] != ''){
                $src .= '&variation_id='. absint($_POST['variation_id']);
            }
            if( isset($_GET['nbds-ref']) ){
                $src .= '&reference='. $_GET['nbds-ref'];
//                if( isset($_GET['nbds-ref-temp']) ){
//                    $session->set('nbd_item_key_'.$pid . '_0', $_GET['nbds-ref']);
//                }
            }

            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

            if( isset($_GET['nbo_cart_item_key']) && $_GET['nbo_cart_item_key'] !='' ){
                $nbd_item_key = $session->get($_GET['nbo_cart_item_key'] . '_nbd');
                if( $nbd_item_key ) {
                    $src .= '&task=edit&nbd_item_key='. $nbd_item_key . '&cik=' . $_GET['nbo_cart_item_key'];
                }
            }
            if( $variation_id != 0 ){
                $src .= '&variation_id='. $variation_id;
            }
            if( $type == 'variable' && isset($option['bulk_variation']) && $option['bulk_variation'] == 1 ){
                $src .= '&variation_id=0';
            }   
            $extra_price = '';
            if( $option['extra_price'] && ! $option['request_quote'] ){
                $extra_price = $option['type_price'] == 1 ? wc_price($option['extra_price']) : $option['extra_price'] . ' %';
            }
            ob_start();
            nbdesigner_get_template( 'single-product/nbd-section.php', array( 'src' => $src,  'extra_price' => $extra_price, 'pid' =>  $pid, 'option' => $option ) );
            $content = ob_get_clean();  
            $position = nbdesigner_get_option('nbdesigner_position_button_product_detail');
            if( $position == 4 ){
                return $content;
            }else{
                echo $content;
            }
        }
    }
    public function nbd_loggin_redirect_func(){
        if(is_admin()) return;
        ob_start();
        nbdesigner_get_template( 'auth-wp.php' );
        $content = ob_get_clean();         
        echo $content;        
    }
    public function nbd_get_nbd_products(){
        $result = array();
        $nbd_products = get_transient('nbd_frontend_products');
        if( false === $nbd_products ){
            $products = $this->get_all_product_has_design();
            foreach ($products as $pro){
                $product = wc_get_product($pro->ID);
                $type = $product->get_type();
                $image = get_the_post_thumbnail_url($pro->ID, 'post-thumbnail');
                if( !$image ) $image = wc_placeholder_img_src();            
                $result[] = array(
                    'product_id'    =>  $pro->ID,
                    'name'  => $pro->post_title,
                    'src'   =>  $image,
                    'type'  =>  $type,
                    'url'   => get_permalink($pro->ID)
                );
            }
            set_transient( 'nbd_frontend_products' , $result ); 
        }else{
            $result = $nbd_products;
        }
        echo json_encode($result);
        wp_die();
    }
    public function nbd_get_product_description(){
        $product_id = $_POST['product_id'];
        $result = $this->_get_product_description($product_id);
        wp_send_json( $result );
    }
    public function _get_product_description( $product_id ){
        $product = wc_get_product( $product_id );
        ob_start();
        $image = get_the_post_thumbnail_url($product_id, 'post-thumbnail');
        $image = $image ? $image : wc_placeholder_img_src();  
        nbdesigner_get_template('product-info.php', array(
            'id' =>  $product_id,
            'title' =>  $product->get_title(),
            'description' =>  $product->get_description(),
            'short_description' =>  $product->get_short_description(),
            'price' =>  $product->get_price(),
            'permalink' =>  $product->get_permalink(),
            'image' =>  $image
        ));         
        $html = ob_get_clean();        
        $result = array(
            'product_id' =>  $product_id,
            'html' =>  $html,
            'type'  =>  $product->get_type()
        );
        return $result;
    }
    public function get_all_product_has_design(){
        $args_query = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'meta_key' => '_nbdesigner_enable',
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page'=> -1,
            'meta_query' => array(
                array(
                    'key' => '_nbdesigner_enable',
                    'value' => 1,
                )
            )
        ); 
        $posts = get_posts($args_query);  
        return $posts;
    }
    public function nbd_get_variation_form( $product_id ){
        $_product = wc_get_product( $product_id );
        $attributes = $_product->get_variation_attributes();
        $available_variations = $_product->get_available_variations();
        $selected_attributes = $_product->get_default_attributes();
        $attribute_keys = array_keys( $attributes );  
        ob_start();
        nbdesigner_get_template('product-variation-form.php', array(
            'attributes' => $attributes,
            'available_variations' => $available_variations,
            'selected_attributes' => $selected_attributes,
            'attribute_keys' => $attribute_keys,
            '_product' => $_product
         ));         
        $html = ob_get_clean();
        return $html;
    }
    public function nbdesigner_get_product_info()
    {
//        if (!wp_verify_nonce($_POST['nonce'], 'save-design') && NBDESIGNER_ENABLE_NONCE) {
//            die('Security error');
//        }
        $data = array();
        $product_id = (isset($_POST['product_id']) &&  $_POST['product_id'] != '') ? absint($_POST['product_id']) : '';
        $variation_id = (isset($_POST['variation_id']) &&  $_POST['variation_id'] != '') ? absint($_POST['variation_id']) : 0;
        $template_id = (isset($_POST['template_id']) &&  $_POST['template_id'] != '') ? $_POST['template_id'] : '';
        $need_templates = (isset($_POST['need_templates']) &&  $_POST['need_templates'] != '') ? true : false;
        if( $template_id != '' ){
            $path = NBDESIGNER_CUSTOMER_DIR . '/' . $template_id;
            $data['fonts'] = nbd_get_data_from_json($path . '/used_font.json');
            $data['design'] = nbd_get_data_from_json($path . '/design.json');
            $data['config'] = nbd_get_data_from_json($path . '/config.json');
            nbd_update_hit_template( false, $template_id );
        }else {
            //   if($product_id) $product_id = get_wpml_original_id($product_id);
            //   if($variation_id) $variation_id = get_wpml_original_id($variation_id);
            $data = nbd_get_product_info( $product_id, $variation_id, '', 'new', '', '', $need_templates );
        }
        if( isset( $_POST['attach_product_info'] ) && $_POST['attach_product_info'] == 1 ){
            if( isset( $_POST['product_type'] ) && $_POST['product_type'] == 'variable' ){
                $data['variation_form'] = $this->nbd_get_variation_form($product_id);
            }
            $data['product_info'] = $this->_get_product_description($product_id);
        }
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_save_partial_customer_design(){
        $result = array();
        $force_new_folder = true;
        $result['flag'] = 0;       
        $sid = esc_html($_POST['sid']);
        $pid = $_POST['product_id'];  
        $flag = (int)$_POST['flag'];  
        $order = 'nb_order';
        if(!$flag){
            $config = str_replace('\"', '"', $_POST['config']);
            $fonts = str_replace('\"', '"', $_POST['fonts']);            
        }
        if(isset($_POST['image'])){
            $data = $_POST['image']['img'];
            if(!$flag){
                $json = str_replace('\"', '"', $_POST['image']['json']);    
                $json = str_replace('\\\\', '\\', $json);                   
            }
        } else{
            die('Incorect data!');
        }        
        $uid = get_current_user_id(); 
        $iid = $sid;
        if($uid > 0) $iid = $uid;    
        $path = NBDESIGNER_CUSTOMER_DIR . '/' . $iid . '/' . $order . '/' . $pid;
        $path_thumb = $path . '/thumbs';
        if(!$flag){      
            $json_file = $path . '/design.json';
            $json_config = $path . '/config.json';
            $json_used_font = $path . '/used_font.json';    
            file_put_contents($json_file, $json);
            file_put_contents($json_config, $config);
            file_put_contents($json_used_font, $fonts);            
        }
        if(file_exists($path) && !$flag){
            Nbdesigner_IO::delete_folder($path);
        }    
        if (!file_exists($path) ) {
            if (wp_mkdir_p($path)) {
                if (!file_exists($path_thumb))
                    if (!wp_mkdir_p($path_thumb)) {
                        $result['mes'] = __('Your server not allow creat folder', 'web-to-print-online-designer');
                    }
            } else {
                $result['mes'] = __('Your server not allow creat folder', 'web-to-print-online-designer');
            }
        } 
        $configs = unserialize(get_post_meta($pid, '_designer_setting', true));      
        $thumb_width = nbdesigner_get_option('nbdesigner_thumbnail_width');
        $thumb_height = nbdesigner_get_option('nbdesigner_thumbnail_height');
        $thumb_quality = nbdesigner_get_option('nbdesigner_thumbnail_quality');  
        if($configs[$flag]["area_design_width"] > $configs[$flag]["area_design_height"]){
            $thumb_height = round($thumb_width * $configs[$flag]['area_design_height'] / $configs[$flag]['area_design_width']);
        }else {
            $thumb_width = round($thumb_height * $configs[$flag]['area_design_width'] / $configs[$flag]['area_design_height']);
        }         
        foreach ($data as $key => $val) {
            $temp = explode(';base64,', $val);
            $buffer = base64_decode($temp[1]);
            $full_name = $path . '/' . $key . '.png';
            if (Nbdesigner_IO::save_data_to_file($full_name, $buffer)) {
                $image = wp_get_image_editor($full_name);
                if (!is_wp_error($image)) {
                    $thumb_file = $path_thumb . '/' . $key . '.png';
                    $image->resize($thumb_width, $thumb_height, 1);
                    $image->set_quality($thumb_quality);
                    if ($image->save($thumb_file, 'image/png')) $result['link'] = Nbdesigner_IO::secret_image_url($thumb_file);
                }                
            } else {
                $result['mes'] = __('Your server not allow writable file', 'web-to-print-online-designer');
            }            
        }       
        $result['flag'] = "success";
        echo json_encode($result);
        wp_die();        
    }
    private function store_design_data($nbd_item_key, $data, $product_config, $product_option, $product_upload){
        $path = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key;
        if(file_exists($path.'_old')) Nbdesigner_IO::delete_folder($path.'_old');
        if(file_exists($path)) rename($path, $path.'_old');
        if ( wp_mkdir_p($path) ) {
            file_put_contents($path . '/product.json', serialize($product_config));
            file_put_contents($path . '/option.json', $product_option);
            file_put_contents($path . '/upload.json', $product_upload);
            foreach ($data as $key => $val) {
                if($key == 'design'){
                    $full_name = $path . '/design.json';
                }else if($key == 'used_font'){
                    $full_name = $path . '/used_font.json';                  
                }else if($key == 'config'){
                    $full_name = $path . '/config.json';
                }else if( $key != 'template_thumb' ){
                    if( strpos($key, '_svg') > 0 ){
                        $ext = 'svg';
                    }else {
                        $ext = explode('/', $val["type"])[1];
                    }
                    $full_name = $path . '/' . $key . '.' .$ext;              
                }   
                if( $key != 'template_thumb' ){
                    if ( !move_uploaded_file($val["tmp_name"],$full_name) ) {
                        return false;
                    }
                }
            }
        } else {
            Nbdesigner_DebugTool::wirite_log('Your server not allow creat folder', 'save design');
            rename($path.'_old', $path);
            return false;
        }
        return true;
    }
    private function validate_url($url) {
        $path = parse_url($url, PHP_URL_PATH);
        $encoded_path = array_map('urlencode', explode('/', $path));
        $url = str_replace($path, implode('/', $encoded_path), $url);

        return filter_var($url, FILTER_VALIDATE_URL) ? true : false;
    }
    private function create_preview_design($path_src, $path_dst, $product_config, $width, $height, $limit = false){
        if(!file_exists($path_dst)){
            wp_mkdir_p($path_dst);
        }          
        $scale = $width / 500;
        $count = 0;
        foreach ($product_config as $key => $val){
            $p_img = $path_src . '/frame_' . $key . '.png';
            $design_format = 'png';
            if(file_exists($path_src . '/frame_' . $key . '.jpeg')){
                $p_img = $path_src . '/frame_' . $key . '.jpeg';
                $design_format = 'jpeg';                
            }           
            if(file_exists($p_img)){
                $bg_width = $val["img_src_width"] * $scale; 
                $bg_height = $val["img_src_height"] * $scale;
                $ds_width = $val["area_design_width"] * $scale;
                $ds_height = $val["area_design_height"] * $scale;
                if($design_format == 'png'){
                    $image_design = NBD_Image::nbdesigner_resize_imagepng($p_img, $ds_width, $ds_height);
                }else{
                    $image_design = NBD_Image::nbdesigner_resize_imagejpg($p_img, $ds_width, $ds_height);
                }
                $image_product_ext = pathinfo($val["img_src"]);
                if($val["bg_type"] == 'image'){
                    $path_img_src  = Nbdesigner_IO::convert_url_to_path($val["img_src"]);
                    if($image_product_ext['extension'] == "png"){
                        $image_product = NBD_Image::nbdesigner_resize_imagepng($path_img_src, $bg_width, $bg_height);
                    }else{
                        if($this->validate_url($val["img_src"])) {
                            $image_product = NBD_Image::nbdesigner_resize_imagejpg($val["img_src"], $bg_width, $bg_height);
                        } else {
                            $image_product = NBD_Image::nbdesigner_resize_imagejpg($path_img_src, $bg_width, $bg_height);
                        }
                    }
                }
                if( $val["show_overlay"] == '1' ){
                    $path_img_overlay  = Nbdesigner_IO::convert_url_to_path($val["img_overlay"]);
                    $overlay_ext = pathinfo($val["img_overlay"]);
                    $over_ext = strtolower($overlay_ext['extension']);
                    if($over_ext == "png"){
                        $image_overlay = NBD_Image::nbdesigner_resize_imagepng($path_img_overlay, $ds_width, $ds_height);
                    }else if($over_ext == "jpg" || $over_ext == "jpeg"){
                        $image_overlay = NBD_Image::nbdesigner_resize_imagejpg($path_img_overlay, $ds_width, $ds_height);
                    }else{
                        $val["show_overlay"] = '0';
                    }
                }
                if($design_format == 'png'){
                    $image = imagecreatetruecolor($bg_width, $bg_height);
                    imagesavealpha($image, true);
                    $color = imagecolorallocatealpha($image, 255, 255, 255, 127);
                    imagefill($image, 0, 0, $color);                    
                }else{
                    $image = imagecreatetruecolor($bg_width, $bg_height);
                    $bg = imagecolorallocate( $image, 255, 255, 255 );
                    imagefilledrectangle($image,0,0,$bg_width,$bg_height,$bg);                    
                }
                if($val["bg_type"] == 'image'){
                    imagecopy($image, $image_product, 0, 0, 0, 0, $bg_width, $bg_height);
                } else if($val["bg_type"] == 'color'){
                    $_color = hex_code_to_rgb($val["bg_color_value"]);
                    $color = imagecolorallocate($image, $_color[0], $_color[1], $_color[2]);
                    imagefilledrectangle($image, 0, 0, $bg_width, $bg_height, $color);
                } else if($design_format == 'jpeg') {
                    $color = imagecolorallocate($image, 255, 255, 255);
                    imagefilledrectangle($image, 0, 0, $bg_width, $bg_height, $color);                    
                }
                imagecopy($image, $image_design, ($val["area_design_left"]-$val["img_src_left"]) * $scale, ($val["area_design_top"]-$val["img_src_top"]) * $scale, 0, 0, $ds_width, $ds_height);
                if( $val["show_overlay"] == '1' && $image_overlay ){
                    imagecopy($image, $image_overlay, ($val["area_design_left"]-$val["img_src_left"]) * $scale, ($val["area_design_top"]-$val["img_src_top"]) * $scale, 0, 0, $ds_width, $ds_height);
                }
                if($design_format == 'png'){
                    imagepng($image, $path_dst. '/frame_' . $key . '.png');
                }else{
                    imagejpeg($image, $path_dst. '/frame_' . $key . '.jpeg');
                }
                imagedestroy($image);
                imagedestroy($image_design);
                $count++;
            }
            if( $limit && $count == $limit ) break;
        }
    }
    public function create_mockup_preview($path, $option, $product_config){
        $mockup_images = array();
        if( isset($option['mockup_preview']) ){
            $mockup_path = $path.'/mockup_preview';
            if( !file_exists($mockup_path) ){
                wp_mkdir_p($mockup_path);
            }
            $preview_images = Nbdesigner_IO::get_list_images($path.'/preview', 1);
            foreach($option['mockup_preview'] as $key => $mockup){
                $src = get_attached_file( $mockup['sid'] );
                if( $src ){
                    $name = sanitize_file_name($mockup['name']);
                    $m_path = $mockup_path . '/' . $name . '.png';
                    $image = imagecreatetruecolor(1000, 1000);
                    $color = imagecolorallocatealpha($image, 255, 255, 255, 127);
                    imagefill($image, 0, 0, $color);
                    $ext = strtolower( pathinfo($src, PATHINFO_EXTENSION ) );
                    if( $ext == 'jpg' || $ext == 'jpeg' ){
                        $s_image = imagecreatefromjpeg($src);
                    }else{
                        $s_image = imagecreatefrompng($src);
                    }
                    list($width, $height) = getimagesize($src);
                    imagecopyresized($image, $s_image, 2 * $mockup['s_left'], 2 * $mockup['s_top'], 0, 0, 2 * $mockup['s_width'], 2 * $mockup['s_height'], $width, $height);
                    list($p_width, $p_height) = getimagesize($preview_images[0]);
                    $scaleX = 2 * $mockup['width'] / $p_width;
                    $scaleY = 2 * $mockup['height'] / $p_height;
                    if( ($p_width / $p_height) > ($mockup['width'] / $mockup['height']) ){
                        $new_width = 2 * $mockup['width'];
                        $new_height = $p_height * $scaleX;
                    }else{
                        $new_height = 2 * $mockup['height'];
                        $new_width = $p_width * $scaleY;
                    }
                    switch( $mockup['anchor'] ){
                        case 'nw':
                            $new_top = 2 * $mockup['top'];
                            $new_left = 2 * $mockup['left'];
                            break;
                        case 'n':
                            $new_top = 2 * $mockup['top'];
                            $new_left = 2 * $mockup['left'] + $mockup['width'] - $new_width / 2;
                            break;
                        case 'ne':
                            $new_top = 2 * $mockup['top'];
                            $new_left = 2 * $mockup['left'] + 2 * $mockup['width'] - $new_width;
                            break;
                        case 'w':
                            $new_left = 2 * $mockup['left'];
                            $new_top = 2 * $mockup['top'] + $mockup['height'] - $new_height / 2;
                            break;
                        case 'c':
                            $new_left = 2 * $mockup['left'] + $mockup['width'] - $new_width / 2;
                            $new_top = 2 * $mockup['top'] + $mockup['height'] - $new_height / 2;
                            break;
                        case 'e':
                            $new_left = 2 * $mockup['left'] + 2 * $mockup['width'] - $new_width;
                            $new_top = 2 * $mockup['top'] + $mockup['height'] - $new_height / 2;
                            break;
                        case 'sw':
                            $new_left = 2 * $mockup['left'];
                            $new_top = 2 * $mockup['top'] + 2 * $mockup['height'] - $new_height;
                            break;
                        case 's':
                            $new_left = 2 * $mockup['left'] + $mockup['width'] - $new_width / 2;
                            $new_top = 2 * $mockup['top'] + 2 * $mockup['height'] - $new_height;
                            break;
                        case 'se':
                            $new_left = 2 * $mockup['left'] + 2 * $mockup['width'] - $new_width;
                            $new_top = 2 * $mockup['top'] + 2 * $mockup['height'] - $new_height;
                            break;
                    }
                    $p_image = imagecreatefrompng($preview_images[0]);
                    imagecopyresized($image, $p_image, $new_left, $new_top, 0, 0, $new_width, $new_height, $p_width, $p_height);
                    imagepng($image, $m_path );
                    imagedestroy($p_image);
                    imagedestroy($image);
                    imagedestroy($s_image);
                    $mockup_images[] = array(
                        'name'  =>  $mockup['name'],
                        'src'   =>  Nbdesigner_IO::wp_convert_path_to_url( $m_path ),
                        'path'  =>  'mockup_preview/' . $name . '.png'
                    );
                }
            }
            foreach($preview_images as $image){
                $path_parts = pathinfo($image);
                $key = substr($path_parts['filename'], 6);
                $mockup_images[] = array(
                    'name'  =>  $product_config[$key]['orientation_name'],
                    'src'   =>  Nbdesigner_IO::wp_convert_path_to_url( $image ),
                    'path'  =>  'preview/' . $path_parts['basename']
                );
            }
        }
        return $mockup_images;
    }
    /**
     * Save design, product configuration into NBDESIGNER_CUSTOMER_DIR <br />
     * Create folder based on the microtime or replace old folder when edit design <br />
     * Store folder name into $session  
     */
    public function nbd_save_cart_design(){
        $result = array(
            'flag'  =>  'success'
        );
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        do_action('before_nbd_save_cart_design', $_POST);
        $product_id = absint($_POST['product_id']);    
        $variation_id = (isset($_POST['variation_id']) && $_POST['variation_id'] != '') ? absint($_POST['variation_id']) : 0;  
        $enable_upload_without_design = (isset($_POST['enable_upload_without_design']) && $_POST['enable_upload_without_design'] != '') ? absint($_POST['enable_upload_without_design']) : 1; 
        /* Save custom design */
        $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id;        
        $nbd_item_session = $session->get('nbd_item_key_'.$nbd_item_cart_key);  
        $nbd_item_key = isset($nbd_item_session) ? $nbd_item_session : substr(md5(uniqid()),0,5).rand(1,100).time();
        $path = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key;
        if($variation_id > 0){
            $product_config = unserialize(get_post_meta($variation_id, '_designer_variation_setting', true));
            $enable_variation = get_post_meta($variation_id, '_nbdesigner_variation_enable', true);              
            if ( !($enable_variation && isset($product_config[0]))){
                $product_config = unserialize(get_post_meta($product_id, '_designer_setting', true)); 
            }
        }else {
            $product_config = unserialize(get_post_meta($product_id, '_designer_setting', true)); 
        } 
        $product_option = get_post_meta($product_id, '_nbdesigner_option', true); 
        $product_upload = get_post_meta($product_id, '_nbdesigner_upload', true); 
        $add_file = false;
        if( isset($_POST['task2']) && $_POST['task2'] == 'add_file' ) {
            $enable_upload_without_design = 2;
            $add_file = true;
        }
        if( $enable_upload_without_design == 1 ){
            $save_status = $this->store_design_data($nbd_item_key, $_FILES, $product_config, $product_option, $product_upload);     
            $width = absint(nbdesigner_get_option('nbdesigner_thumbnail_width')) ? absint(nbdesigner_get_option('nbdesigner_thumbnail_width')) : 300;
            if(false != $save_status){
                $path_config = $path . '/config.json';
                $config = nbd_get_data_from_json($path_config);
                if( count( $config->product ) ){
                    $product_config = array();
                    foreach($config->product as $side){
                        $product_config[] = (array)$side;
                    }
                }; 
                if( isset( $config->custom_dimension ) ){
                    $custom_side = absint( $config->custom_dimension->side );
                    $custom_width = $config->custom_dimension->width;
                    $custom_height = $config->custom_dimension->height;
                    $product_config = $this->merge_product_config( $product_config, $custom_width, $custom_height, $custom_side );
                }
                $this->create_preview_design($path, $path.'/preview', $product_config, $width, $width);
                do_action('after_nbd_create_preview_design', $nbd_item_key);
                $session->set('nbd_item_key_'.$nbd_item_cart_key, $nbd_item_key); 
            } else {
                $result['flag'] = 'failure';
            }
        }
        /* Save upload design */
        if( isset($_POST['nbd_file']) && $_POST['nbd_file'] != '' ){
            $nbu_item_session = $session->get('nbu_item_key_'.$nbd_item_cart_key);
            if( $nbu_item_session ){
                $this->update_files_upload( $_POST['nbd_file'], $nbu_item_session );
//                if( !$add_file ){
//                    $session->set('nbu_item_key_'.$nbd_item_cart_key, $nbu_item_session);             
//                }
            }
        }
        if( isset($_POST['task2']) && $_POST['task2'] != '' ){          
            /* Destroy nbd_item session and init cart session */
            if( $_POST['task2'] != 'cuz' ){
                $cart_item_key = $_POST['cart_item_key'];
                $session->unset('nbd_item_key_'.$nbd_item_cart_key);
                $session->unset('nbu_item_key_'.$nbd_item_cart_key);
                if( isset(WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']) ) WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds'] = array();
                if( !$add_file ){
                    $session->set($cart_item_key. '_nbd', $nbd_item_key);
                    WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']['nbd'] = $nbd_item_key;
                }
                if( isset($_POST['nbd_file']) && $_POST['nbd_file'] != '' ){
                    $session->set($cart_item_key. '_nbu', $nbu_item_session);
                    WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']['nbu'] = $nbu_item_session;
                }
                WC()->cart->set_session();
            }
        }else {
            /* Add to cart directly in custom page */
            $quantity = ( isset($_POST['qty']) && absint($_POST['qty']) > 0 ) ? absint($_POST['qty']) : 1;
            $product_status    = get_post_status( $product_id );
            if ( false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id ) && 'publish' === $product_status ) {
                do_action( 'woocommerce_ajax_added_to_cart', $product_id );
            } else {
                $result['flag'] = 'failure';
                $result['mes'] = __('Error adding to the cart', 'web-to-print-online-designer');
            }              
        }
        do_action('after_nbd_save_cart_design', $_POST, $result);
        echo json_encode($result);
        wp_die();         
    }
    public function nbd_save_draft_design(){
        $result = array(
            'flag'  =>  0
        );
        $stored = false;
        $product_id = absint($_POST['product_id']);  
        $variation_id = (isset($_POST['variation_id']) && $_POST['variation_id'] != '') ? absint($_POST['variation_id']) : 0;
        $product_option = get_post_meta($product_id, '_nbdesigner_option', true); 
        $draft_folder = substr(md5(uniqid()),0,5).rand(1,100).time();
        if( isset($_POST['draft_folder']) && $_POST['draft_folder'] != '' ){
            $draft_folder = $_POST['draft_folder'];
            $stored = true;
        }
        if($variation_id > 0){
            $product_config = unserialize(get_post_meta($variation_id, '_designer_variation_setting', true));
            $enable_variation = get_post_meta($variation_id, '_nbdesigner_variation_enable', true);              
            if ( !($enable_variation && isset($product_config[0]))){
                $product_config = unserialize(get_post_meta($product_id, '_designer_setting', true)); 
            }                   
        }else {
            $product_config = unserialize(get_post_meta($product_id, '_designer_setting', true)); 
        } 
        $product_upload = get_post_meta($product_id, '_nbdesigner_upload', true);     
        $save_status = $this->store_design_data($draft_folder, $_FILES, $product_config, $product_option, $product_upload); 
        if( $save_status ){
            $path = NBDESIGNER_CUSTOMER_DIR . '/' . $draft_folder;
            $width = absint(nbdesigner_get_option('nbdesigner_thumbnail_width')) ? absint(nbdesigner_get_option('nbdesigner_thumbnail_width')) : 300;
            $this->create_preview_design($path, $path.'/preview', $product_config, $width, $width);
            if( $stored ){
                $result['flag'] = 'success';
                $result['draft_folder'] = $draft_folder;
            }else{
                global $wpdb;
                $created_date = new DateTime();
                $user_id = wp_get_current_user()->ID;
                $table_name =  $wpdb->prefix . 'nbdesigner_mydesigns';
                $re = $wpdb->insert($table_name, array(
                    'product_id' => $product_id,
                    'variation_id' => $variation_id,            
                    'price' => 0,
                    'folder' => $draft_folder,
                    'user_id' => $user_id,
                    'publish'   =>  1,
                    'created_date' => $created_date->format('Y-m-d H:i:s')
                ));
                if( $re ){
                    $result['flag'] = 'success';
                    $result['draft_folder'] = $draft_folder;
                }
            }
        }
        wp_send_json($result);
    }
    public function nbd_save_customer_design()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $objectManager->create('\Magento\Framework\Session\SessionManager');
        $helperConfig = $objectManager->get('Netbaseteam\Onlinedesign\Helper\Config');


        $result = array(
            'flag'  =>  'failure',
            'link'  =>  ''
        );

        /* Get data from $_POST */
        $product_id = absint($_POST['product_id']);
        $variation_id = '';
        $product_option = get_post_meta($product_id, '_nbdesigner_option', true);
        $cart_item_key = (isset($_POST['cart_item_key']) && $_POST['cart_item_key'] != '') ? $_POST['cart_item_key'] : '';
        $task = (isset($_POST['task']) && $_POST['task'] != '') ? $_POST['task'] : 'new';
        $task2 = (isset($_POST['task2']) && $_POST['task2'] != '') ? $_POST['task2'] : '';
        $auto_add_to_cart = 'no';
        $design_type = (isset($_POST['design_type']) && $_POST['design_type'] != '') ? $_POST['design_type'] : '';
        $generate_mockup = (isset($_POST['generate_mockup']) && $_POST['generate_mockup'] != '') ? true : false;
        $nbd_item_cart_key = $product_id; 

        if(isset($_POST['nbd_item_key']) && $_POST['nbd_item_key'] != ''){
            /* Edit design 
             * In case edit template, $design_type = 'template'
             */
            $nbd_item_key = $_POST['nbd_item_key'];
//            if( ($design_type == '') && ( !nbd_check_cart_item_exist($cart_item_key) || !$session->isset($cart_item_key.'_nbd') ) ) {
//                $result['mes'] = 'Item not exist in cart';
//                nbd_die( $result );
//            }
        }else {
            /* Create new design */
            $nbd_item_session = $session->getData('visitor_data')['session_id'];
            if( ($task == 'new' && $task2 == 'update' ) || $task == 'create' || !isset($nbd_item_session) ){
                $nbd_item_key = substr(md5(uniqid()),0,5).rand(1,100).time();
            }else {
                $nbd_item_key = $nbd_item_session;
            }
        }
        $path = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key;
        if( $task == 'new' || $task == 'create' ){
            $product_config = unserialize(get_post_meta($product_id, '_designer_setting', true)); 
        }else{
            $product_config = unserialize(file_get_contents($path . '/product.json'));
        }
        $att_swatch = (isset($_POST['att_swatch']) && $_POST['att_swatch'] != '') ? $_POST['att_swatch'] : '';
        // $product_config = apply_filters('nbd_save_customer_design_product_config', $product_config, $product_option, $att_swatch);
        $product_upload = get_post_meta($product_id, '_nbdesigner_upload', true);        
        $save_status = $this->store_design_data($nbd_item_key, $_FILES, $product_config, $product_option, $product_upload);     
        $width = absint(nbdesigner_get_option('nbdesigner_thumbnail_width')) ? absint(nbdesigner_get_option('nbdesigner_thumbnail_width')) : 300; 
        if( $task == 'create' || $design_type == 'template' ){
            $width = absint(nbdesigner_get_option('nbdesigner_template_width')) ? absint(nbdesigner_get_option('nbdesigner_template_width')) : 300;
        }
        if(false != $save_status){
            /* todo edit $product_config if has custom dimension */
            $path_config = $path . '/config.json';
            $config = nbd_get_data_from_json($path_config);            
            if( count( $config->product ) ){
                $product_config = array();
                foreach($config->product as $side){
                    $product_config[] = (array)$side;
                }
            };             
            if( isset( $config->custom_dimension ) ){
                $custom_side = absint( $config->custom_dimension->side );
                $custom_width = $config->custom_dimension->width;
                $custom_height = $config->custom_dimension->height;
                $product_config = $this->merge_product_config( $product_config, $custom_width, $custom_height, $custom_side );
            }
            $this->create_preview_design($path, $path.'/preview', $product_config, $width, $width);
            if( isset($_POST['share']) ){
                Nbdesigner_IO::copy_dir( $path, $path.'s' );
                $result['sfolder'] = $nbd_item_key.'s';
            }
            if($generate_mockup){
                $result['mockups'] = $this->create_mockup_preview($path, $option, $product_config);
            }
            $result['image'] = array();
            $images = Nbdesigner_IO::get_list_images($path.'/preview', 1);
            foreach($images as $image){
                $filename = pathinfo($image, PATHINFO_FILENAME);
                $result['image'][$filename] = Nbdesigner_IO::wp_convert_path_to_url($image);
                ksort($result['image']);
            }
            $result['flag'] = 'success';   
            $result['folder'] = $nbd_item_key;   

            if( $task == 'new' ){
                if( $task2 == 'update' ){
                    // $session->set($cart_item_key. '_nbd', $nbd_item_key);
                    // if( isset(WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']) ) WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds'] = array();
                    // WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']['nbd'] = $nbd_item_key;
                    // WC()->cart->set_session();
                    // $session->unset('nbd_item_key_'.$nbd_item_cart_key);
                }else{
                    $session->set('nbd_item_key_'.$nbd_item_cart_key, $nbd_item_key);
                    $session->setNbdesignerPid($product_id);
                    $session->setNbdesignerJson(json_encode(Nbdesigner_IO::get_list_images($path.'/preview', 1)));
                }
            }

            if( $task == 'create' ){
                if( $design_type == 'art' ){
                    My_Design_Endpoint::nbdesigner_insert_table_my_design($product_id, $variation_id, $nbd_item_key );
                }else{
                    // if(!can_edit_nbd_template()){
                    //     $result['mes'] = __('You have not permission to create or edit template', 'web-to-print-online-designer'); echo json_encode($result); wp_die();
                    // }else{
                        $this->nbdesigner_insert_table_templates($product_id, $variation_id, $nbd_item_key, 0, 1, 0);
                        $result['close_window'] = 1;
                    // }
                }
            }
            // if( $task == 'new' && $auto_add_to_cart == 'yes' ){
            //     if( nbd_add_to_cart( $product_id, $variation_id, 1 ) ){
            //         $result['added'] = 1;
            //     }
            // }
            if( $task == 'edit' && isset($_POST['order_id']) && $design_type == 'edit_order' ){
                $order_id = absint($_POST['order_id']);
                update_post_meta($order_id, '_nbdesigner_order_changed', 1);
            }
            // if( $task == 'edit' && (isset($_POST['cart_item_key']) && $_POST['cart_item_key'] != '')  ){
            //     if( isset($_POST['qty']) && absint($_POST['qty']) > 0 ){
            //         global $woocommerce;
            //         $woocommerce->cart->set_quantity($cart_item_key, absint($_POST['qty']));
            //     }
            // }
        }
        header('Content-Type: application/json');

        echo json_encode($result);

        $cacheTypeList = $objectManager->get('\Magento\Framework\App\Cache\TypeListInterface');
        $cacheTypeList->cleanType('full_page');
                
        die();   
    }
    public function nbd_remove_design_and_file(){
        $product_id = absint($_POST['product_id']);
        $variation_id = (isset($_POST['variation_id']) && $_POST['variation_id'] != '') ? absint($_POST['variation_id']) : 0; 
        $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id;

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        $session->get('nbd_item_key_'.$nbd_item_cart_key);
        $session->get('nbu_item_key_'.$nbd_item_cart_key);
        echo 'success';
        wp_die(); 
    }
    public function merge_product_config( $product_config, $width, $height, $number_side ){
        foreach ( $product_config as $key => $side ) {
            $product_config[$key]["real_width"] = $product_config[$key]["product_width"] = $width;
            $product_config[$key]["real_height"] = $product_config[$key]["product_height"] = $height;            
            if( $width >= $height ) {
                $ratio = 500 / $width;
                $product_config[$key]["img_src_width"] = $product_config[$key]["area_design_width"] = 500;               
                $product_config[$key]["img_src_left"] = $product_config[$key]["area_design_left"] = 0;
                $product_config[$key]["area_design_height"] = $product_config[$key]["img_src_height"] = round( $ratio * $height );
                $product_config[$key]["img_src_top"] = $product_config[$key]["area_design_top"] = round( 250 - $product_config[$key]["area_design_height"] / 2 );
            }else {
                $ratio = 500 / $height;
                $product_config[$key]["img_src_height"] = $product_config[$key]["area_design_height"] = 500;
                $product_config[$key]["img_src_top"] = $product_config[$key]["area_design_top"] = 0;
                $product_config[$key]["area_design_width"] = $product_config[$key]["img_src_width"] = round( $ratio * $width );                
                $product_config[$key]["img_src_left"] = $product_config[$key]["area_design_left"] = round( 250 - $product_config[$key]["area_design_width"] / 2 );
            }
        }
        $len = count($product_config);
        if( $len >= $number_side ){
            array_splice($product_config, $number_side, $len - $number_side);
        }else {
            for( $i = $len; $i < $number_side; $i++ ){
                $product_config[$i] = $product_config[0];
            }
        } 
        return $product_config;
    }
    public function nbdesigner_user_role(){
        $capabilities = array(
            1   =>    'manage_nbd_tool',
            2   =>    'manage_nbd_art',
            3   =>    'manage_nbd_language',
            4   =>    'manage_nbd_template',
            5   =>    'manage_nbd_product',
            6   =>    'manage_nbd_analytic',
            7   =>    'manage_nbd_design_store',
            8   =>    'manage_nbd_font',
            9   =>    'export_nbd_font',
            10   =>    'export_nbd_art',
            11   =>    'export_nbd_product',
            12   =>    'export_nbd_language',
            13   =>    'import_nbd_font',
            14   =>    'import_nbd_art',
            15   =>    'import_nbd_product',
            16   =>    'import_nbd_language',
            17   =>    'edit_nbd_font',
            18   =>    'edit_nbd_art',
            19   =>    'edit_nbd_language',
            20   =>    'edit_nbd_template',
            21   =>    'delete_nbd_font',
            22   =>    'delete_nbd_art',
            23   =>    'delete_nbd_language',
            24   =>    'delete_nbd_template',
            25   =>    'sell_nbd_design',
            26   =>    'update_nbd_data',
            27   =>    'manage_nbd_setting'
        );
        $admin_role = get_role('administrator');
        if (null != $admin_role) {
            foreach ($capabilities as $cap){
                $admin_role->add_cap($cap);    
            }     
        }
        $shop_manager_role = get_role('shop_manager');
        if (null != $shop_manager_role) {
            foreach ($capabilities as $cap){
                $shop_manager_role->add_cap($cap);    
            }           
        }
        $shop_capabilities = $shop_manager_role->capabilities;
        $shop_capabilities['export_nbd_font'] = false;
        $shop_capabilities['export_nbd_art'] = false;
        $shop_capabilities['export_nbd_product'] = false;
        $shop_capabilities['export_nbd_language'] = false;        
        $shop_capabilities['import_nbd_font'] = false;
        $shop_capabilities['import_nbd_art'] = false;
        $shop_capabilities['import_nbd_product'] = false;
        $shop_capabilities['import_nbd_language'] = false;     
        $shop_capabilities['edit_nbd_font'] = false;
        $shop_capabilities['edit_nbd_language'] = false;
        $shop_capabilities['edit_nbd_template'] = false;
        $shop_capabilities['edit_nbd_art'] = false;        
        $shop_capabilities['delete_nbd_font'] = false;
        $shop_capabilities['delete_nbd_art'] = false;
        $shop_capabilities['delete_nbd_language'] = false;
        $shop_capabilities['delete_nbd_template'] = false;          
        $shop_capabilities['update_nbd_data'] = false;          
        $nbd_viewer = add_role('nbdesigner_viewer', 'NBDesigner Viewer', $shop_manager_role->capabilities);
        if (null === $nbd_viewer) {
            $nbd_viewer = get_role('nbdesigner_viewer');           
            $nbd_viewer->remove_cap('export_nbd_font');
            $nbd_viewer->remove_cap('export_nbd_art');
            $nbd_viewer->remove_cap('export_nbd_product');
            $nbd_viewer->remove_cap('export_nbd_language');
            $nbd_viewer->remove_cap('import_nbd_font');
            $nbd_viewer->remove_cap('import_nbd_art');
            $nbd_viewer->remove_cap('import_nbd_product');
            $nbd_viewer->remove_cap('import_nbd_language');
            $nbd_viewer->remove_cap('edit_nbd_font');
            $nbd_viewer->remove_cap('edit_nbd_art');
            $nbd_viewer->remove_cap('edit_nbd_language');
            $nbd_viewer->remove_cap('edit_nbd_template');
            $nbd_viewer->remove_cap('delete_nbd_font');
            $nbd_viewer->remove_cap('delete_nbd_art');
            $nbd_viewer->remove_cap('delete_nbd_language');
            $nbd_viewer->remove_cap('delete_nbd_template');
            $nbd_viewer->remove_cap('update_nbd_data');
        }
    }
    /**
     * Insert table templates
     * @since 1.5.0
     * 
     * @global type $wpdb
     * @param int $product_id
     * @param varchar $folder
     * @param int $priority
     * @param int $publish
     * @param int $private
     */
    private function nbdesigner_insert_table_templates($product_id, $variation_id, $folder, $priority, $publish = 1, $private = 0){
        // global $wpdb;
        // $created_date = new DateTime();
        // $user_id = 1;
        // $table_name =  $wpdb->prefix . 'nbdesigner_templates';
        // $wpdb->insert($table_name, array(
        //     'product_id' => $product_id,
        //     'variation_id' => $variation_id,
        //     'folder' => $folder,
        //     'user_id' => $user_id,
        //     'created_date' => $created_date->format('Y-m-d H:i:s'),
        //     'publish' => $publish,
        //     'private' => $private,
        //     'priority' => $priority
        // ));
        // return true;

        $created_date = new DateTime();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection= $resources->getConnection();

        $user_id = $customerSession->getCustomerId();
        $table_name =  $resources->getTableName('nbdesigner_templates');
        $sql = "INSERT INTO " . $table_name . "(product_id, variation_id, folder, user_id, publish, private, priority, created_date) VALUES ('".$product_id."', '".$variation_id."', '".$folder."', '".$user_id."', '".$publish."', '".$private."', '".$priority."', '".$created_date->format('Y-m-d H:i:s')."')";

        $connection->query($sql);
        return true;
    }
    public function nbdesigner_save_webcam_image(){
        $result = array();
        $img = $_POST['image'];
        $data = base64_decode($img);
        $full_name = NBDESIGNER_DATA_DIR . '/temp/' . time() .'.png';
        $success = file_put_contents($full_name, $data);
        if($success){
            $result['flag'] = 'success';        
            $result['url'] = Nbdesigner_IO::wp_convert_path_to_url( $full_name );
        }else{
            $result['flag'] = 'false';
        }
        echo json_encode($result);
        wp_die();        
    }
    private function nbdesigner_create_thumbnail_design($from_path, $to_path, $pid, $vid = 0, $_width = 500, $_height = 500){
        if($vid > 0){
            $configs = unserialize(get_post_meta($vid, '_designer_variation_setting', true));     
            if( !isset($configs[0]) ){
                $configs = unserialize(get_post_meta($pid, '_designer_setting', true));        
            }
        }else {
            $configs = unserialize(get_post_meta($pid, '_designer_setting', true));        
        }
        $path_preview = $to_path;
        if(!file_exists($path_preview)){
            wp_mkdir_p($path_preview);
        }
        foreach ($configs as $key => $val){
            $p_img = $from_path . '/frame_' . $key . '.png';
            if(file_exists($p_img)){
                $image_design = NBD_Image::nbdesigner_resize_imagepng($p_img, $val["area_design_width"], $val["area_design_height"]);
                $image_product_ext = pathinfo($val["img_src"]);
                if($val["bg_type"] == 'image'){
                    if($image_product_ext['extension'] == "png"){
                        $image_product = NBD_Image::nbdesigner_resize_imagepng($val["img_src"], $val["img_src_width"], $val["img_src_height"]);
                    }else{
                        $image_product = NBD_Image::nbdesigner_resize_imagejpg($val["img_src"], $val["img_src_width"], $val["img_src_height"]);
                    }     
                }
                $image = imagecreatetruecolor($_width, $_height);
                imagesavealpha($image, true);
                $color = imagecolorallocatealpha($image, 255, 255, 255, 127);
                imagefill($image, 0, 0, $color);
                if($val["bg_type"] == 'image'){
                    imagecopy($image, $image_product, $val["img_src_left"], $val["img_src_top"], 0, 0, $val["img_src_width"], $val["img_src_height"]);
                } else if($val["bg_type"] == 'color'){
                    $_color = hex_code_to_rgb($val["bg_color_value"]);
                    $color = imagecolorallocate($image, $_color[0], $_color[1], $_color[2]);
                    imagefilledrectangle($image, $val["img_src_left"], $val["img_src_top"], $val["img_src_left"] + $val["img_src_width"], $val["img_src_top"] + $val["img_src_height"], $color);
                }
                imagecopy($image, $image_design, $val["area_design_left"], $val["area_design_top"], 0, 0, $val["area_design_width"], $val["area_design_height"]);
                imagepng($image, $path_preview. '/frame_' . $key . '.png');
                imagedestroy($image);
                imagedestroy($image_design);
            }
        }
    }  
    public function set_nbd_order_data($order_id) {
        global $woocommerce;
        $items = $woocommerce->cart->get_cart();
        $has_nbd = false;
        $has_nbu = false;
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        foreach($items as $item => $values){
            /* custom design */
            $nbd_session = $session->get($item . '_nbd');
            if(isset($nbd_session)){
                $session->unset($item . '_nbd'); 
                $src = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_session;
                $dst = NBDESIGNER_CUSTOMER_DIR . '/' . $order_id.'_'.$nbd_session;
                if( $nbd_session != '' && file_exists($src) ){
                    Nbdesigner_IO::copy_dir($src, $dst);
                    $has_nbd = true;                    
                }
            }
            /* upload design */
            $nbu_session = $session->get($item . '_nbu');
            if(isset($nbu_session)){
                $session->unset($item . '_nbu'); 
                $has_nbu = true;
            } 
            $session->unset($item . '_nbd_initial_price');
        }
        if($has_nbd) {
            add_post_meta($order_id, '_nbdesigner_order_changed', 1);
            add_post_meta($order_id, '_nbd', 1);
        }
        if($has_nbu) {
            add_post_meta($order_id, '_nbdesigner_upload_order_changed', 1);
            add_post_meta($order_id, '_nbu', 1);
        }        
    }
    public function nbdesigner_display_posts_design($column, $post_id) {
        if ($column == 'design') {
            $is_design = get_post_meta($post_id, '_nbdesigner_enable', true);
            echo '<input type="checkbox" disabled', ( $is_design ? ' checked' : ''), '/>';
        }
    }
    public function nbdesigner_add_design_column($columns) {
        return array_merge($columns, array('design' => __('Design', 'web-to-print-online-designer')));
    }
    public function render_cart($title = null, $cart_item = null, $cart_item_key = null) {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        if ($cart_item_key && ( is_cart() || is_checkout() )) {
            $nbd_session = $session->get($cart_item_key . '_nbd'); 
            $nbu_session = $session->get($cart_item_key . '_nbu'); 
            if( isset($cart_item['nbd_item_meta_ds']) ){
                if( isset($cart_item['nbd_item_meta_ds']['nbd']) ) $nbd_session = $cart_item['nbd_item_meta_ds']['nbd'];
                if( isset($cart_item['nbd_item_meta_ds']['nbu']) ) $nbu_session = $cart_item['nbd_item_meta_ds']['nbu'];
            }
            $_show_design = nbdesigner_get_option('nbdesigner_show_in_cart');
            $show_edit_link = apply_filters('nbd_show_edit_design_link_in_cart', true, $cart_item);
            $product_id = $cart_item['product_id'];
            $variation_id = $cart_item['variation_id'];
            $product_id = get_wpml_original_id( $product_id );
            $is_nbdesign = get_post_meta($product_id, '_nbdesigner_enable', true);
            $_enable_upload = get_post_meta($product_id, '_nbdesigner_enable_upload', true);
            $_enable_upload_without_design = get_post_meta($product_id, '_nbdesigner_enable_upload_without_design', true);
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );            
            if ($is_nbdesign && $_show_design == 'yes') {
                if($nbd_session || $nbu_session){
                    $html = is_checkout() ? $title . ' &times; <strong>' . $cart_item['quantity'] .'</strong>' : $title;         
                }else{
                    $html = $title;
                }
                $layout = nbd_get_product_layout( $product_id );
                if( isset($nbd_session) ){
                    $id = 'nbd' . $cart_item_key;
                    //$redirect_url = is_cart() ? wc_get_cart_url().'#'.$id : wc_get_checkout_url().'#'.$id;                     
                    $redirect = is_cart() ? 'cart' : 'checkout';
                    $html .= '<div style="display: block;" id="'.$id.'" class="nbd-custom-dsign">';
                    $remove_design = is_cart() ? '<a class="remove nbd-remove-design" style="display: inline-block;" href="javascript:void(0)" onclick="NBDESIGNERPRODUCT.remove_design(\'custom\', \''.$cart_item_key.'\')">&times;</a>' : '';
                    $html .= '<p>'. __('Custom design', 'web-to-print-online-designer') .$remove_design.'</p>';
                    $list = Nbdesigner_IO::get_list_images(NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_session . '/preview');
                    asort($list);
                    foreach ($list as $img) {
                        $src = Nbdesigner_IO::convert_path_to_url($img) . '?&t=' . round(microtime(true) * 1000);
                        $html .= '<img style="max-width: 100%; display: inline-block; margin-right: 15px; margin-bottom: 15px; border: 1px solid #ddd; background: #ddd;" src="' . $src . '"/>';
                        //$html .= '<span class="nbd-cart-form"><input class="nbd-cart-design-url" tyle="text" value="'.$src.'" /><a href="javascript:void(0)" onclick="NBDESIGNERPRODUCT.copy_source( this )" class="button">'. __('Copy', 'web-to-print-online-designer') .'</a></span>';
                    }
                    if( $show_edit_link ){
                        $link_edit_design = add_query_arg(
                            array(
                                'task'  =>  'edit',
                                'product_id'    =>  $product_id,
                                'nbd_item_key'  =>  $nbd_session,
                                'cik'   =>  $cart_item_key,
                                'view'   =>  $layout,
                                //'rd'    =>  urlencode($redirect_url)), 
                                'rd'    =>  $redirect),
                            getUrlPageNBD('create'));
                        if( $product_permalink ){
                            $att_query = parse_url( $product_permalink, PHP_URL_QUERY );
                            $link_edit_design .= '&'.$att_query;
                        }    
                        if( $layout == 'v' ){
                            $link_edit_design = add_query_arg(
                                array(
                                    'nbdv-task' =>  'edit',
                                    'task'  =>  'edit',
                                    'product_id'    =>  $product_id,
                                    'nbd_item_key'  =>  $nbd_session,
                                    'cik'   =>  $cart_item_key,
                                    //'rd'    =>  urlencode($redirect_url)), 
                                    'rd'    =>  $redirect),
                                $product_permalink );
                        }
                        if($cart_item['variation_id'] > 0){
                            $link_edit_design .= '&variation_id=' . $cart_item['variation_id'];
                        }
                        $html .= '<br /><a class="button nbd-edit-design" href="'.$link_edit_design.'">'. __('Edit design', 'web-to-print-online-designer') .'</a>';
                    }
                    $html .= '</div>';
                }else if( $is_nbdesign && !$_enable_upload_without_design && $show_edit_link ){
                    $id = 'nbd' . $cart_item_key;
                    //$redirect_url = is_cart() ? wc_get_cart_url().'#'.$id : wc_get_checkout_url().'#'.$id;  
                    $redirect = is_cart() ? 'cart' : 'checkout';
                    $link_create_design = add_query_arg(
                        array(
                            'task'  =>  'new',
                            'task2'  =>  'update',
                            'product_id'    =>  $product_id,
                            'variation_id'  =>  $variation_id,
                            'cik'   =>  $cart_item_key,
                            'view'   =>  $layout,
                            //'rd'    =>  urlencode($redirect_url)), 
                            'rd'    =>  $redirect),
                        getUrlPageNBD('create'));
                    if( $layout == 'v' ){
                        $link_create_design = add_query_arg(
                            array(
                                'nbdv-task' =>  'new',
                                'task'  =>  'new',
                                'task2'  =>  'update',
                                'product_id'    =>  $product_id,
                                'variation_id'  =>  $variation_id,
                                'cik'   =>  $cart_item_key,
                                'view'   =>  $layout,
                                //'rd'    =>  urlencode($redirect_url)), 
                                'rd'    =>  $redirect),
                            $product_permalink );
                    }
                    if( $product_permalink ){
                        $att_query = parse_url( $product_permalink, PHP_URL_QUERY );
                        $link_create_design .= '&'.$att_query;
                    }                    
                    $html .= '<div style="display: block;" class="nbd-cart-upload-file">';
                    $html .=    '<a class="button nbd-create-design" href="'.$link_create_design.'">'. __('Add design', 'web-to-print-online-designer') .'</a>';
                    $html .= '</div>';
                }
                if( isset( $nbu_session ) ){
                    $id = 'nbu' . $cart_item_key;
                    //$redirect_url = is_cart() ? wc_get_cart_url().'#'.$id : wc_get_checkout_url().'#'.$id;    
                    $redirect = is_cart() ? 'cart' : 'checkout';
                    $html .= '<div style="display: block;" id="'.$id.'" class="nbd-cart-upload-file">';
                    $remove_upload = is_cart() ? '<a class="remove" style="display: inline-block;" href="javascript:void(0)" onclick="NBDESIGNERPRODUCT.remove_design(\'upload\', \''.$cart_item_key.'\')">&times;</a>' : '';
                    $html .= '<p>'. __('Upload file', 'web-to-print-online-designer') .$remove_upload.'</p>';
                    $files = Nbdesigner_IO::get_list_files(NBDESIGNER_UPLOAD_DIR . '/' . $nbu_session);
                    $create_preview = nbdesigner_get_option('nbdesigner_create_preview_image_file_upload');
                    foreach ($files as $file) {
                        $ext = pathinfo( $file, PATHINFO_EXTENSION );
                        $src = Nbdesigner_IO::get_thumb_file( pathinfo( $file, PATHINFO_EXTENSION ), '');
                        $file_url = Nbdesigner_IO::wp_convert_path_to_url( $file );
                        if(  $create_preview == 'yes' && ( $ext == 'png' || $ext == 'jpg' || $ext == 'pdf' ) ){
                            $dir = pathinfo( $file, PATHINFO_DIRNAME );
                            $filename = pathinfo( $file, PATHINFO_BASENAME );
                            if( file_exists($dir.'_preview/'.$filename) ){
                                $src = Nbdesigner_IO::wp_convert_path_to_url( $dir.'_preview/'.$filename );
                            }else if( $ext == 'pdf' && file_exists($dir.'_preview/'.$filename.'.jpg' ) ){
                                $src = Nbdesigner_IO::wp_convert_path_to_url( $dir.'_preview/'.$filename.'.jpg' );
                            }else{
                                $src = Nbdesigner_IO::get_thumb_file( $ext, '' );
                            }
                        }else {
                            $src = Nbdesigner_IO::get_thumb_file( $ext, '' );
                        }                                           
                        $html .= '<div style="width: 120px;display: inline-block; margin: 0 15px 15px 0; text-align: center; padding: 10px; border: 1px solid #ddd;"><a target="_blank" href='.$file_url.'><img style="max-width: 100%;" src="' . $src . '"/></a><p style="width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; height: 30px; line-height: 30px;">'. basename($file).'</p></div>';
                    }
                    if( $show_edit_link ){
                        $link_reup_design = add_query_arg(
                            array(
                                'task'  =>  'reup',
                                'product_id'    =>  $product_id,
                                'nbu_item_key'  =>  $nbu_session,
                                'cik'   =>  $cart_item_key,
                                //'rd'    =>  urlencode($redirect_url)),
                                'rd'    =>  $redirect),
                            getUrlPageNBD('create'));
                        if($cart_item['variation_id'] > 0){
                            $link_reup_design .= '&variation_id=' . $cart_item['variation_id'];
                        }                    
                        $html .= '<br /><a class="button nbd-reup-design" href="'.$link_reup_design.'">'. __('Reupload design', 'web-to-print-online-designer') .'</a>';
                    }
                    $html .= '</div>';
                }else if( $_enable_upload && $show_edit_link ){
                    $id = 'nbd' . $cart_item_key;
                    //$redirect_url = is_cart() ? wc_get_cart_url().'#'.$id : wc_get_checkout_url().'#'.$id;      
                    $redirect = is_cart() ? 'cart' : 'checkout';
                    $link_create_design = add_query_arg(
                        array(
                            'task'  =>  'new',
                            'task2'  =>  'add_file',
                            'product_id'    =>  $product_id,
                            'variation_id'  =>  $variation_id,
                            'cik'   =>  $cart_item_key,
                            //'rd'    =>  urlencode($redirect_url)), 
                            'rd'    =>  $redirect),
                        getUrlPageNBD('create'));
                    $html .= '<div style="display: block;" class="nbd-cart-upload-file">';
                    $html .=    '<a class="button nbd-upload-design" href="'.$link_create_design.'">'. __('Upload design', 'web-to-print-online-designer') .'</a>';
                    $html .= '</div>';                    
                }         
                $option = unserialize(get_post_meta($product_id, '_nbdesigner_option', true)); 
                if( isset($nbd_session) ) {
                    $path = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_session . '/config.json';
                    $config = nbd_get_data_from_json($path);
                    if( isset( $config->custom_dimension ) && isset( $config->custom_dimension->price ) ){
                        $nbd_variation_price = $config->custom_dimension->price;
                    }
                }                
                if( ((isset($nbd_variation_price) && $nbd_variation_price != 0) || $option['extra_price']) && ! $option['request_quote'] ){
                    $decimals = wc_get_price_decimals();
                    $extra_price = $option['extra_price'] ? $option['extra_price'] : 0;
                    if( (isset($nbd_variation_price) && $nbd_variation_price != 0) ) {
                        $extra_price = $option['type_price'] == 1 ? wc_price($extra_price + $nbd_variation_price) : $extra_price . ' % + ' . wc_price($nbd_variation_price);
                    }else {
                        $extra_price = $option['type_price'] == 1 ? wc_price($extra_price) : $extra_price . '%';
                    }
                    $html .= '<p id="nbx'.$cart_item_key.'">'. __('Extra price for design','web-to-print-online-designer').' + '. $extra_price .'</p>';
                }
                echo $html;
            } else {
                echo $title;
            }
        }else{
            echo $title;
        }
    }
    /**
     * Set session cart if manual add to cart <br />
     * Store last cart_item_key into session if add to cart before start design
     * @since 1.7.0
     * 
     * @param text $cart_item_key 
     * @param int $product_id
     * @param int $quantity
     * @param int $variation_id
     */
    public function set_nbd_session_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) { 
        $product_id = get_wpml_original_id($product_id);
        $recent_variation_id = $variation_id;
        $variation_id = get_wpml_original_id($variation_id);
        $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id; 
        $recent_nbd_item_cart_key = ($recent_variation_id > 0) ? $product_id . '_' . $recent_variation_id : $product_id;

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        /* add to cart in custom design page */
        if(is_nbdesigner_product($product_id)){
            $session->set('nbd_last_item_cart', $cart_item_key);
        }
        /* custom design */
        $nbd_session = $session->get('nbd_item_key_'.$nbd_item_cart_key);
        $recent_nbd_session = $session->get('nbd_item_key_'.$recent_nbd_item_cart_key);
        if( $recent_nbd_session ){
            $nbd_session = $recent_nbd_session;
        }
        $_enable_upload_without_design = get_post_meta($product_id, '_nbdesigner_enable_upload_without_design', true);  
        if(isset($nbd_session) && $nbd_session != ''){
            $session->set($cart_item_key. '_nbd', $nbd_session);
            $session->unset('nbd_item_key_'.$nbd_item_cart_key);
            if( $_enable_upload_without_design ) $session->unset($cart_item_key. '_nbd');
        }else{
//            $nbd_session2 = $session->get('nbd_item_key_'.$product_id . '_0');
//            if(isset($nbd_session2) && $nbd_session2 != ''){
//                if( !$_enable_upload_without_design ){
//                    $session->set($cart_item_key. '_nbd', $nbd_session2);
//                    $session->unset('nbd_item_key_'.$product_id . '_0');                    
//                }
//            }
        }
        /* up design */
        $nbu_session = $session->get('nbu_item_key_'.$nbd_item_cart_key);
        if(isset($nbu_session) && $nbu_session != ''){
            $session->set($cart_item_key. '_nbu', $nbu_session);
            $session->unset('nbu_item_key_'.$nbd_item_cart_key);
            
            $_enable_upload = get_post_meta($product_id, '_nbdesigner_enable_upload', true);  
            if( !$_enable_upload ) $session->unset($cart_item_key. '_nbu');            
        }   
        /* order again */
        if( isset( $cart_item_data['_nbd'] ) ){
            $session->set($cart_item_key. '_nbd', $cart_item_data['_nbd']);
        }
        if( isset( $cart_item_data['_nbu'] ) ){
            $session->set($cart_item_key. '_nbu', $cart_item_data['_nbu']);
        }  
    }
    public function nbdesigner_remove_cart_item_design($removed_cart_item_key, $instance){
        //$line_item = $instance->removed_cart_contents[ $removed_cart_item_key ];
        /* custom design */
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        $nbd_session = $session->get($removed_cart_item_key.'_nbd');      
        if($nbd_session){  
            $session->unset($removed_cart_item_key . '_nbd');          
        }  
        /* up design */
        $nbu_session = $session->get($removed_cart_item_key.'_nbu');
        if($nbu_session){  
            $session->unset($removed_cart_item_key . '_nbu');          
        }
        /* remove session extra price */
        if( $session->isset($removed_cart_item_key . '_nbd_initial_price') ){
            $session->unset($removed_cart_item_key . '_nbd_initial_price');
        }
    }
    public function nbdesigner_add_new_order_item($item_id, $item, $order_id){
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        if (isset($item->legacy_cart_item_key)){
            /* custom design */
            if ($session->isset($item->legacy_cart_item_key . '_nbd') || isset($item->legacy_values["nbd_item_meta_ds"]["nbd"])) {
                $nbd_session = $session->get($item->legacy_cart_item_key. '_nbd');
                if( isset($item->legacy_values["nbd_item_meta_ds"]["nbd"]) ) $nbd_session = $item->legacy_values["nbd_item_meta_ds"]["nbd"];
                wc_add_order_item_meta($item_id, "_nbd", $nbd_session);
            }
            /* up design */
            if ($session->isset($item->legacy_cart_item_key . '_nbu') || isset($item->legacy_values["nbd_item_meta_ds"]["nbu"])) {
                $nbu_session = $session->get($item->legacy_cart_item_key. '_nbu');
                if( isset($item->legacy_values["nbd_item_meta_ds"]["nbu"]) ) $nbu_session = $item->legacy_values["nbd_item_meta_ds"]["nbu"];
                wc_add_order_item_meta($item_id, "_nbu", $nbu_session);
            }    
            if( $session->isset($item->legacy_cart_item_key . '_nbd_initial_price') ){
                $product_id = $item->legacy_values['product_id'];
                $product_id = get_wpml_original_id( $product_id );      
                $option = unserialize(get_post_meta($product_id, '_nbdesigner_option', true));
                if( isset($nbd_session) ) {
                    $path = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_session . '/config.json';
                    $config = nbd_get_data_from_json($path);
                    if( isset( $config->custom_dimension ) && isset( $config->custom_dimension->price ) ){
                        $nbd_variation_price = $config->custom_dimension->price;
                    }
                }                 
                if( ((isset($nbd_variation_price) && $nbd_variation_price != 0) || $option['extra_price']) && ! $option['request_quote'] ){
                    $extra_price = $option['extra_price'] ? $option['extra_price'] : 0;
                    if( (isset($nbd_variation_price) && $nbd_variation_price != 0) ) {
                        $extra_price = $option['type_price'] == 1 ? wc_price($extra_price + $nbd_variation_price) : $extra_price . '% + ' . wc_price($nbd_variation_price);
                    }else {
                        $extra_price = wc_price($extra_price);
                    }
                    wc_add_order_item_meta($item_id, "_nbd_extra_price", $extra_price);
                }
            }
        }
    }    
    public function nbdesigner_add_order_design_data($item_id, $values, $cart_item_key) {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        /* custom design */
        if ($session->isset($cart_item_key . '_nbd')) {
            wc_add_order_item_meta($item_id, "_nbd", $session->get($cart_item_key . '_nbd'));
        }
        /* up design */
        if ($session->isset($cart_item_key . '_nbu')) {
            wc_add_order_item_meta($item_id, "_nbu", $session->get($cart_item_key . '_nbu'));
        }        
    }
    public function nbdesigner_design_approve(){
        check_admin_referer('approve-designs', '_nbdesigner_approve_nonce');    
        $order_id = $_POST['nbdesigner_design_order_id'];   
        $_design_action = $_POST['nbdesigner_order_file_approve'];       
        $response['mes'] = '';   
        if (is_numeric($order_id) && ( isset($_POST['_nbdesigner_design_file']) && is_array($_POST['_nbdesigner_design_file']) || isset($_POST['_nbdesigner_upload_file']) && is_array($_POST['_nbdesigner_upload_file']) ) ) {
            $has_design = $has_upload = $update_design = $update_upload = false;
            if( isset($_POST['_nbdesigner_design_file']) ){
                $_design_file = $_POST['_nbdesigner_design_file'];   
                $has_design = true;
            }
            if( isset($_POST['_nbdesigner_upload_file']) ){
                $_upload_file = $_POST['_nbdesigner_upload_file'];   
                $has_upload = true;
            }                    
            $design_data = unserialize(get_post_meta($order_id, '_nbdesigner_design_file', true));     
            $upload_data = unserialize(get_post_meta($order_id, '_nbdesigner_upload_file', true));   
            if( $has_design ){
                if(is_array($design_data)){
                    foreach($_design_file as $val){    
                        $check = false;
                        foreach($design_data as $key => $status){
                            $_key = str_replace('nbds_', '', trim($key));
                            if($_key == $val){
                                $design_data[$key] = $_design_action;  
                                $check = true;
                            }
                        }   
                        if(!$check) $design_data['nbds_'.$val] = $_design_action; 
                    }
                }else{
                    $design_data = array();
                    foreach ($_design_file as $val){
                        $design_data['nbds_'.$val] = $_design_action;
                    }
                } 
                $design_data = serialize($design_data);  
                $update_design = update_post_meta($order_id, '_nbdesigner_design_file', $design_data);
            }
            if( $has_upload ){
                if(is_array($upload_data)){
                    foreach($_upload_file as $val){    
                        $check = false;
                        foreach($upload_data as $key => $status){
                            $_key = str_replace('nbds_', '', trim($key));
                            if($_key == $val){
                                $upload_data[$key] = $_design_action;  
                                $check = true;
                            }
                        }   
                        if(!$check) $upload_data['nbds_'.$val] = $_design_action; 
                    }
                }else{
                    $upload_data = array();
                    foreach ($_upload_file as $val){
                        $upload_data['nbds_'.$val] = $_design_action;
                    }
                } 
                $upload_data = serialize($upload_data);   
                $update_upload = update_post_meta($order_id, '_nbdesigner_upload_file', $upload_data);
            }              
            if ( $update_design || $update_upload ){
                update_post_meta($order_id, '_nbdesigner_order_changed', 0);
                $response['mes'] = 'success';
            }else{
                update_post_meta($order_id, '_nbdesigner_order_changed', 0);
                $response['mes'] = __('You don\'t change anything? Or an error occured saving the data, please refresh this page and check if changes took place.', 'web-to-print-online-designer');
            }
        } else {
            $response['mes'] = __('You haven\'t chosen a item.', 'web-to-print-online-designer');
        }
        echo json_encode($response);
        wp_die();
    }
    public function nbdesigner_design_order_email(){
        check_admin_referer('approve-design-email', '_nbdesigner_design_email_nonce');  
        $response['success'] = 0;
        if (empty($_POST['nbdesigner_design_email_order_content'])) {
            $response['error'] = __('The reason cannot be empty', 'web-to-print-online-designer');        
        } elseif (!is_numeric($_POST['nbdesigner_design_email_order_id'])) {
            $response['error'] = __('Error while sending mail', 'web-to-print-online-designer');
        } 
        if (empty($response['error'])) {
            $message = $_POST['nbdesigner_design_email_order_content'];
            $order = new WC_Order($_POST['nbdesigner_design_email_order_id']);  
            $reason = ($_POST['nbdesigner_design_email_reason'] == 'approved')?__('Your design accepted', 'web-to-print-online-designer'): 'Your design rejected';
            $send_email = $this->nbdesigner_send_email($order, $reason, $message);
            if ($send_email)
                $response['success'] = 1;
            else
                $response['error'] = __('Error while sending mail', 'web-to-print-online-designer');            
        }
        echo json_encode($response);
        wp_die();        
    }
    public function nbdesigner_send_email($order, $reason, $message){
        global $woocommerce;
        $user_email = $order->get_billing_email();
        if (!empty($user_email)) {
            $mailer = $woocommerce->mailer();
            ob_start();
            wc_get_template('emails/nbdesigner-approve-order-design.php', array(
                'plugin_id' => 'nbdesigner',
                'order' => $order,
                'reason' => $reason,
                'message' => $message,
                'my_order_url' => $order->get_view_order_url(),
            ));
            $body = ob_get_clean();
            $subject = $reason . ' - Order ' . $order->get_order_number();              
            $mailer->send($user_email, $subject, $body);
            
            //Send mail to admin
            $notifications = get_option('nbdesigner_enable_send_mail_when_approve', false);
            if( $notifications == 'yes' && $reason == 'Your design accepted' ){                   
                ob_start();
                wc_get_template('emails/notify-admin-when-approve-design.php', array(
                    'order_number' => $order->get_order_number(),
                    'order_id' => $order->get_id()
                )); 
                $subject = __('Approve designs - Order ','web-to-print-online-designer') . $order->get_order_number();
                $owner_email = get_option('nbdesigner_admin_emails', false);
                $emails = new WC_Emails();  
                $woo_recipient = $emails->emails['WC_Email_New_Order']->recipient;
                if($owner_email == ''){
                    if(!empty($woo_recipient)) {
                        $user_email = esc_attr($woo_recipient);
                    } else {
                        $user_email = get_option( 'admin_email' );
                    }                        
                }else{
                    $user_email = $owner_email;
                }
                $body = ob_get_clean();    
                 
                $products = $order->get_items();
                $attachment = array();
                if( get_option('nbdesigner_attachment_admin_email', false) == 'yes' ){
                    foreach($products AS $order_item_id => $product){        
                        $nbd_item_key = wc_get_order_item_meta($order_item_id, '_nbd');
                        if( $nbd_item_key ){
                            $list_images = Nbdesigner_IO::get_list_images(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key, 1);
                            foreach ($list_images as $image) {
                                $attachment[] = $image;
                            }                        
                        }
                    }
                }
                if (!empty($user_email)) {                                            
                    $mailer->send($user_email, $subject, $body, '', $attachment);
                }       
            }                       
            return true;
        } else {
            return false;
        }
    }
    public function update_order_item_meta_data( $order_id ){
        $order = new WC_Order($order_id);
        $order_items = $order->get_items();
        foreach($order_items AS $order_item_id => $order_item){ 
            $nbd_item_key = wc_get_order_item_meta($order_item_id, '_nbd');
            if($nbd_item_key){
                $watermark = false;
                $showBleed = false;
                $force = true;
                $files = nbd_export_pdfs( $nbd_item_key, $watermark, $force, $showBleed );
                $urls = '';
                foreach($files as $key => $file){
                    $urls .= ($key > 0 ? '|' : '') . Nbdesigner_IO::wp_convert_path_to_url($file);
                };
                wc_update_order_item_meta($order_item_id, '_pdfs', $urls);
            }
            $nbu_item_key = wc_get_order_item_meta($order_item_id, '_nbu');
            if($nbu_item_key){
                $files = Nbdesigner_IO::get_list_files(NBDESIGNER_UPLOAD_DIR . '/' . $nbu_item_key);
                foreach($files as $key => $file){
                    $urls .= ($key > 0 ? '|' : '') . Nbdesigner_IO::wp_convert_path_to_url($file);
                };                
                wc_update_order_item_meta($order_item_id, '_nbd_upload_files', $urls);
            }
        }        
    }
    public function attach_design_to_admin_email2($attachments, $type, $object){
        if( 'new_order' === $type ){
            $products = $object->get_items();
            $has_design = false;
            foreach($products AS $order_item_id => $product){        
                $nbd_item_key = wc_get_order_item_meta($order_item_id, '_nbd');
                if( $nbd_item_key ){
                    $watermark = false;
                    $showBleed = true;
                    $force = true;
                    $files = nbd_export_pdfs( $nbd_item_key, $watermark, $force, $showBleed );    
                    foreach($files as $key => $file){
                        $zip_files[] = $file;
                    };
                    $has_design = true;
                }
            }
            if($has_design){
                $pathZip = NBDESIGNER_DATA_DIR.'/download/customer-design-'.time().'.zip';
                nbd_zip_files_and_download($zip_files, $pathZip, 'Canvas.zip', array(), false);
                $attachments[] = $pathZip;
            }
        }
        return $attachments;        
    }
    public function attach_design_to_admin_email($attachments, $type, $object){
        //if( 'new_order' === $type || $type == 'customer_completed_order' ){
        if( 'new_order' === $type ){
            $products = $object->get_items();
            foreach($products AS $order_item_id => $product){        
                $nbd_item_key = wc_get_order_item_meta($order_item_id, '_nbd');
                if( $nbd_item_key ){
                    $list_images = array();
                    if( nbdesigner_get_option('nbdesigner_attach_design_svg') == 1 ){
                        $list_images = Nbdesigner_IO::get_list_files_by_type(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key, 1, 'svg');
                    }
                    if( nbdesigner_get_option('nbdesigner_attach_design_png') == 1 ){
                        $list_images = array_merge( Nbdesigner_IO::get_list_images(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key, 1), $list_images );
                    }
                    foreach ($list_images as $image) {
                        $attachments[] = $image;
                    }                        
                }
            }            
        }
        return $attachments;
    }
    public function nbdesigner_locate_plugin_template($template, $template_name, $template_path){
        global $woocommerce;
        $_template = $template;
        if ( ! $template_path ) $template_path = $woocommerce->template_url;
        $plugin_path  = NBDESIGNER_PLUGIN_DIR . 'templates/';
        $template = locate_template(array(
            $template_path . $template_name,
            $template_name
        ));
        if ( ! $template && file_exists( $plugin_path . $template_name ) )
            $template = $plugin_path . $template_name;
        if ( ! $template )
          $template = $_template;
        return $template;
    }
    /**
     * @deprecated 1.9.0
     * @since 1.8.0
     */    
    public function order_item_name($item_name, $item){
        $show_design_in_order = nbdesigner_get_option('nbdesigner_show_in_order');
        if(( isset($item["item_meta"]["_nbd"]) || isset($item["item_meta"]["_nbu"]) ) && $show_design_in_order == 'yes'){
            $html = '';
            if( isset($item["item_meta"]["_nbd"]) ){
                $nbd_item_key = $item["item_meta"]["_nbd"]; 
                $images = Nbdesigner_IO::get_list_images(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key .'/preview', 1);   
                $approve_status = unserialize(get_post_meta($item['order_id'], '_nbdesigner_design_file', true));
                $index = 'nbds_'.$item->get_id();
                $notice = '';
                $id = 'nbd' . $item->get_id();
                $redirect_url = wc_get_endpoint_url( 'view-order', $item['order_id'], wc_get_page_permalink( 'myaccount' ) ) . '#' . $id;
                $product_id = $item['product_id'];
                $product_id = get_wpml_original_id( $product_id ); 
                $redesign_link = add_query_arg(
                    array(
                        'task'  =>  'edit',
                        'product_id'    =>  $product_id,
                        'oid'   =>  $item['order_id'],
                        'item_id'   =>  $id,
                        'design_type'  =>  'edit_order',
                        //'rd'    => urlencode( $redirect_url ),
                        'rd'    => 'order',
                        'nbd_item_key'  =>  $nbd_item_key), 
                    getUrlPageNBD('create'));  
                if($item['variation_id'] > 0){
                    $redesign_link .= '&variation_id=' . $item['variation_id'];
                }            
                $html .= '<div id="'.$id.'" class="nbd-order-upload-file">';
                $html .= '<p>'.__('Custom design', 'web-to-print-online-designer').'</p>';
                foreach ($images as $img){      
                    $src = Nbdesigner_IO::convert_path_to_url($img) . '?&t=' . round(microtime(true) * 1000);   
                    $html .= '<img style="max-width: 100%; border: 1px solid #ddd; display: inline-block; margin-right: 15px; margin-bottom: 15px; background: #ddd;" src="'.$src.'" />';
                }
                if(isset($approve_status[$index]) && $approve_status[$index] == "decline"){
                    $notice = "<small style='color:red;'>". __('(Rejected! Click ', 'web-to-print-online-designer')."<a href='".$redesign_link."' target='_blank'>". __('here ', 'web-to-print-online-designer'). "</a>". __(' to design again', 'web-to-print-online-designer')."!)</small>";
                }
                if(isset($approve_status[$index]) && $approve_status[$index] == "accept"){
                    $notice = __('<small> (Approved!)</small>', 'web-to-print-online-designer');
                }
                $html .= '<p>'.$notice.'</p>';
                if(nbdesigner_get_option('allow_customer_redesign_after_order') == 'yes'){
                    $html .= '<p><a class="button" href="'. $redesign_link .'">'. __('Edit design', 'web-to-print-online-designer') .'</a></p>';
                }      
                if( !is_admin() && (nbdesigner_get_option('nbdesigner_enable_download_pdf_before') == 'yes'
                        || nbdesigner_get_option('nbdesigner_enable_download_pdf_after') == 'yes')){
                    $order_id = $item["order_id"];
                    if( is_checkout() || is_account_page() ){
                        $order = wc_get_order($order_id);
                        $action_download = ($order->get_status() == 'completed' && nbdesigner_get_option('nbdesigner_enable_download_pdf_after') == 'yes' ) ? 'nbd_download_final_pdf' : 'download_pdf_in_order';                    
                        $html .= '<p class="nbd-order-download-pdf">';
                        $html .=    '<a data-order="'.$order_id.'" data-nbd-item="'.$nbd_item_key.'" href="'.wc_get_endpoint_url( 'view-order', $order_id, wc_get_page_permalink( 'myaccount' ) ).'" data-action="'. $action_download .'" data-loading-img="'.NBDESIGNER_PLUGIN_URL.'assets/images/loading2.gif" class="button alt nbd-order-download-pdf-action">';
                        $html .=        '<span>'.__('Download PDF', 'web-to-print-online-designer').'</span>';
                        $html .=    '</a>';
                        $html .= '</p>';
                    } else {
                        $html .= '<a href="'.wc_get_endpoint_url( 'view-order', $order_id, wc_get_page_permalink( 'myaccount' ) ).'">'. __('Download PDF', 'web-to-print-online-designer') .'</a>';
                    }
                }
                $html .= '<div>';
            }
            if( isset($item["item_meta"]["_nbu"]) ){
                $nbu_item_key = $item["item_meta"]["_nbu"]; 
                $files = Nbdesigner_IO::get_list_files( NBDESIGNER_UPLOAD_DIR .'/'. $nbu_item_key );     
                $notice = '';
                $id = 'nbu' . $item->get_id();
                $approve_status = unserialize(get_post_meta($item['order_id'], '_nbdesigner_upload_file', true));
                $index = 'nbds_'.$item->get_id();                
                $redirect_url = wc_get_endpoint_url( 'view-order', $item['order_id'], wc_get_page_permalink( 'myaccount' ) ) . '#' . $id;
                $product_id = $item['product_id'];
                $product_id = get_wpml_original_id( $product_id );                 
                $reup_link = add_query_arg(
                    array(
                        'task'  =>  'reup',
                        'product_id'    =>  $product_id,
                        'design_type'  =>  'edit_order',
                        'item_id'   =>  $id,
                        'oid'   =>  $item['order_id'],
                        //'rd'    => urlencode( $redirect_url ),
                        'rd'    => 'order',
                        'nbu_item_key'  =>  $nbu_item_key), 
                    getUrlPageNBD('create'));  
                if($item['variation_id'] > 0){
                    $reup_link .= '&variation_id=' . $item['variation_id'];
                } 
                $html .= '<div id="'.$id.'" class="nbd-order-upload-file">';
                $html .= '<p>'.__('Upload file', 'web-to-print-online-designer').'</p>';
                $create_preview = nbdesigner_get_option('nbdesigner_create_preview_image_file_upload');
                foreach ($files as $file){      
                    $ext = pathinfo( $file, PATHINFO_EXTENSION );
                    $src = Nbdesigner_IO::get_thumb_file( pathinfo( $file, PATHINFO_EXTENSION ), '');
                    if(  $create_preview == 'yes' && ( $ext == 'png' || $ext == 'jpg' ) ){
                        $dir = pathinfo( $file, PATHINFO_DIRNAME );
                        $filename = pathinfo( $file, PATHINFO_BASENAME );
                        if( file_exists($dir.'_preview/'.$filename) ){
                            $src = Nbdesigner_IO::wp_convert_path_to_url( $dir.'_preview/'.$filename );
                        }else{
                            $src = Nbdesigner_IO::get_thumb_file( $ext, '' );
                        }
                    }else {
                        $src = Nbdesigner_IO::get_thumb_file( $ext, '' );
                    }       
                    $html .= '<div style="width: 120px;display: inline-block; margin: 0 15px 15px 0; text-align: center; padding: 10px; border: 1px solid #ddd;"><img style="max-width: 100%;" src="' . $src . '"/><p style="width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; height: 30px; line-height: 30px;">'. basename($file).'</p></div>';
                }   
                if(isset($approve_status[$index]) && $approve_status[$index] == "decline"){
                    $notice = "<small style='color:red;'>". __('(Rejected! Click ', 'web-to-print-online-designer')."<a href='".$reup_link."' target='_blank'>". __('here ', 'web-to-print-online-designer'). "</a>". __(' to design again', 'web-to-print-online-designer')."!)</small>";
                }
                if(isset($approve_status[$index]) && $approve_status[$index] == "accept"){
                    $notice = __('<small> (Approved!)</small>', 'web-to-print-online-designer');
                }
                $html .= '<p>'.$notice.'</p>';                
                if(nbdesigner_get_option('allow_customer_redesign_after_order') == 'yes'){
                    $html .= '<br /><a class="button" href="'. $reup_link .'">'. __('Reupload design', 'web-to-print-online-designer') .'</a>';
                }                 
                $html .= '<div>';
            }
            if(  isset($item["item_meta"]["_nbd_extra_price"]) ){
                $html .= '<p>'. __('Extra price for design','web-to-print-online-designer').' + '.$item["item_meta"]["_nbd_extra_price"].'</p>';
            }           
            $link = get_permalink( $item['product_id']);   
            $item_name = sprintf( '<a href="%s">%s</a>&times;<strong class="product-quantity">%s</strong>%s', $link, $item['name'], $item['qty'], $html );
        }
        return $item_name;
    }
    public function nbdesigner_order_item_quantity_html($strong, $item){
        $show_design_in_order = nbdesigner_get_option('nbdesigner_show_in_order');
        if($show_design_in_order == 'yes'){
            if(isset($item["item_meta"]["_nbd"]) || isset($item["item_meta"]["_nbu"]))  return '';
        }
        return ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>';
    }
    public function nbd_checkout_cart_item_quantity( $quantity_html, $cart_item, $cart_item_key ){
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        $show_design_in_cart = nbdesigner_get_option('nbdesigner_show_in_cart');
        if($show_design_in_cart == 'yes'){
            if($session->isset($cart_item_key . '_nbd') || $session->isset($cart_item_key . '_nbu'))  return '';
        }        
        return $quantity_html;
    }
    public function nbdesigner_hidden_custom_order_item_metada($order_items){
        $order_items[] = '_nbdesigner_has_design';
        $order_items[] = '_nbdesigner_folder_design';
        $order_items[] = '_nbdesigner_version_design';
        $order_items[] = '_nbdesign_order';
        $order_items[] = '_nbdesign_order_item_id';
        $order_items[] = '_nbd';
        $order_items[] = '_nbu';
        $order_items[] = '_nbd_extra_price';
        $order_items[] = '_pdfs';
        $order_items[] = '_nbd_upload_files';
        return $order_items;
    }
    public function nbd_upload_design_file(){
        $result = array(
            'flag'  =>  0,
            'mes'   =>  '',
            'src'   =>  ''
        );
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        $product_id = $_POST['product_id'];
        $variation_id = $_POST['variation_id'];
        $task = $_POST['task'];
        $first_time = $_POST['first_time'];
        $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id;          
        $upload_setting = unserialize(get_post_meta($product_id, '_nbdesigner_upload', true));
        if( $upload_setting['allow_type'] == '' ) $upload_setting['allow_type'] = nbdesigner_get_option('nbdesigner_allow_upload_file_type');
        if( $upload_setting['disallow_type'] == '' ) $upload_setting['disallow_type'] = nbdesigner_get_option('nbdesigner_disallow_upload_file_type');        
        $allow_ext = explode(',', preg_replace('/\s+/', '', strtolower( $upload_setting['allow_type']) ) );      
        $disallow_ext = explode(',', preg_replace('/\s+/', '', strtolower( $upload_setting['disallow_type']) ) );
        $ext = strtolower( $this->nbdesigner_get_extension( $_FILES['file']["name"] ) );
        $ext = $ext == 'jpeg' ? 'jpg' : $ext;
        $max_size = $upload_setting['maxsize'] * 1024 * 1024;
        $minsize = $upload_setting['minsize'] * 1024 * 1024;
        $checkSize = $checkExt = $checkDPI = false;
        if( $upload_setting['mindpi'] > 0 && ( $ext == 'jpg' || $ext == 'jpeg' ) ){
            $dpi = nbd_get_dpi($_FILES['file']["tmp_name"]);  
            if($dpi['x'] < $upload_setting['mindpi']) $checkDPI = true;
        }
        if( (count($allow_ext) && $allow_ext[0] != '' && !in_array(strtolower($ext), $allow_ext)) 
                || (count($disallow_ext) && $disallow_ext[0] != '' && in_array(strtolower($ext), $disallow_ext)) ) {
            $checkExt = true;
        }
        if( $minsize > $_FILES['file']["size"] || ($max_size != 0 && $max_size < $_FILES['file']["size"]) ){
            $checkSize = true;
        }
        if( $checkSize || $checkExt || $checkDPI ){
            if( $checkSize ) $result['mes'] = __('File size too small or large!', 'web-to-print-online-designer');     
            if( $checkExt ) $result['mes'] = __('Extension not allowed!', 'web-to-print-online-designer');     
            if( $checkDPI ) $result['mes'] = __('Min DPI required for JPG', 'web-to-print-online-designer') . ' ' . $upload_setting['mindpi'];    
        }else{ 
            if(isset($_POST['nbu_item_key']) && $_POST['nbu_item_key'] != ''){
                /* reup */
                $nbu_item_key = $_POST['nbu_item_key'];
            }else {   
                $nbu_item_session = $session->get('nbu_item_key_'.$nbd_item_cart_key);  
                $nbu_item_key = isset($nbu_item_session) ? $nbu_item_session : substr(md5(uniqid()),0,5).rand(1,100).time();
            }
            $path_dir = NBDESIGNER_UPLOAD_DIR . '/' .$nbu_item_key; 
            $new_name = sanitize_file_name($_FILES['file']["name"]);
            $new_name = nbd_alias($new_name);
            $path = $path_dir . '/' .$new_name;
            if ( !file_exists($path_dir) ) wp_mkdir_p($path_dir);
            $image_exts = array(
                0   =>  'jpg',
                1   =>  'jpeg',
                2   =>  'png'
            );     
            if( $task == 'reup' && $first_time == 1 ){
                if(file_exists($path_dir.'_old')) Nbdesigner_IO::delete_folder($path_dir.'_old');
                if(file_exists($path_dir)){
                    rename($path_dir, $path_dir.'_old');          
                    wp_mkdir_p($path_dir);
                }
                //maybe implement other method
            }
            if(move_uploaded_file( $_FILES['file']["tmp_name"], $path )){
                $result['mes'] = __('Upload success !', 'web-to-print-online-designer');  
                /** Allow create preview for images **/
                if( nbdesigner_get_option('nbdesigner_create_preview_image_file_upload') == 'yes' ){                    
                    $path_preview = $path_dir . '_preview/';
                    $preview_file = Nbdesigner_IO::wp_convert_path_to_url( $path_preview.$new_name );
                    if(!file_exists($path_preview)){
                        wp_mkdir_p($path_preview);
                    }
                    $preview_width = nbdesigner_get_option('nbdesigner_file_upload_preview_width');
                    if( $ext == 'png' ){
                        NBD_Image::nbdesigner_resize_imagepng($path, $preview_width, $preview_width, $path_preview.$new_name);
                    }else if( $ext == 'jpg' ){
                        NBD_Image::nbdesigner_resize_imagejpg($path, $preview_width, $preview_width, $path_preview.$new_name);
                    }else if( is_available_imagick() && $ext == 'pdf' ){
                        $jpg_preview = $path_preview.$new_name.'.jpg';
                        NBD_Image::imagick_convert_pdf_to_jpg($path, $jpg_preview );
                        if(file_exists($jpg_preview) ){
                            $preview_file = Nbdesigner_IO::wp_convert_path_to_url( $jpg_preview );
                        }else{
                            $preview_file = Nbdesigner_IO::get_thumb_file( $ext, $path );
                        }
                    } else{
                        $preview_file = Nbdesigner_IO::get_thumb_file( $ext, $path );
                    }
                    $src = $preview_file;
                }else{
                    $src = Nbdesigner_IO::get_thumb_file( $ext, $path );
                }
                $result['src'] = $src;
                $result['name'] = $new_name;
                $result['flag'] = 1;
                if( $task == 'new' && $first_time == 1 ) $session->set('nbu_item_key_'.$nbd_item_cart_key, $nbu_item_key);    
            }else{
                $result['mes'] = __('Error occurred with file upload!', 'web-to-print-online-designer');            
            }                           
        }  
        echo json_encode($result);
        wp_die();        
    }
    public function nbdesigner_customer_upload(){       
        $allow_extension = array('jpg','jpeg','png','gif','svg');
        $max_size = nbdesigner_get_option('nbdesigner_maxsize_upload');
        $min_dpi = nbdesigner_get_option('nbdesigner_mindpi_upload');
        $allow_max_size = $max_size * 1024 * 1024;
        $result =   true;
        $res = array();
        $size   =   $_FILES['file']["size"];
        $name   =   $_FILES['file']["name"];    
        $ext = $this->nbdesigner_get_extension($name);
        $new_name = strtotime("now").substr(md5(rand(1111,9999)),0,8).'.'.$ext;        
        if(empty($name)) {
            $result = false;
            $res['mes'] = __('Error occurred with file upload!', 'web-to-print-online-designer');            
        }
        if($size > $allow_max_size){
            $result = false;
            $res['mes'] = __('Too large file !', 'web-to-print-online-designer');                
        }
        $check = Nbdesigner_IO::checkFileType($name, $allow_extension);
        if(!$check){
            $result = false;
            $res['mes'] = __('Invalid file format!', 'web-to-print-online-designer');
        }   
        if($ext != 'svg' && $min_dpi && $min_dpi > 0) {
            $dpi = nbd_get_dpi($_FILES['file']["tmp_name"]); 
            if($dpi['x'] < $min_dpi) {
                $result = nbdesigner_get_option('nbdesigner_enable_low_resolution_image') == 'yes' ? true : false;
                $res['mes'] = __('Image resolution too low!', 'web-to-print-online-designer');                
                $res['ilr'] = 1;
            }
        }          
        $path = Nbdesigner_IO::create_file_path(NBDESIGNER_TEMP_DIR, $new_name);
        if($result){
            if(move_uploaded_file($_FILES['file']["tmp_name"],$path['full_path'])){
                $res['mes'] = __('Upload success !', 'web-to-print-online-designer');                 
            }else{
                $result = false;
                $res['mes'] = __('Error occurred with file upload!', 'web-to-print-online-designer');            
            }
        }
        if($result){
            $res['src'] = NBDESIGNER_TEMP_URL.$path['date_path'];
            $res['flag'] = 1;
        }else{
            $res['flag'] = 0;
        }           
        echo json_encode($res);
        wp_die();
    }
    public function nbdesigner_get_qrcode(){
        $result = array();
        $result['flag'] = 0;
        if (!wp_verify_nonce($_REQUEST['nonce'], 'save-design') && NBDESIGNER_ENABLE_NONCE) {
            die('Security error');
        } 
        if(isset($_REQUEST['data'])){
            $content = $_REQUEST['data'];
            include_once NBDESIGNER_PLUGIN_DIR.'includes/class-qrcode.php';
            $qr = new Nbdesigner_Qrcode();
            $qr->setText($content);
            $image = $qr->getImage(500);
            $file_name = strtotime("now") . '.png';
            $full_name = NBDESIGNER_DATA_DIR . '/temp/'. $file_name;
            if(Nbdesigner_IO::save_data_to_file($full_name, $image)){
                $result['flag'] = 1;
                $result['src'] =  content_url().'/uploads/nbdesigner/temp/'.$file_name;
            };          
        }
        echo json_encode($result);
        wp_die();
    }
    public function nbdesigner_get_facebook_photo(){       
        $result = array();
        $_accessToken = $_POST['accessToken'];
        require_once NBDESIGNER_PLUGIN_DIR.'includes/class.nbdesigner.facebook.php';
        echo json_encode($result);
        wp_die();
    }
    public function _nbdesigner_get_art(){
        if (!wp_verify_nonce($_REQUEST['nonce'], 'nbdesigner-get-data') && NBDESIGNER_ENABLE_NONCE) {
            die('Security error');
        }   
        $result = array();
        $path = NBDESIGNER_DATA_DIR . '/cliparts';
        $cats = Nbdesigner_IO::get_list_folder($path, 1);
        foreach ($cats as $key => $cat){
            $result['cat'][] = array(
                'name'  => basename($cat),
                'id'    => $key                
            );
            $list = Nbdesigner_IO::get_list_files($path . '/' . basename($cat), 1);
            $arts = preg_grep('/\.(svg)(?:[\?\#].*)?$/i', $list);
            foreach($arts as $k => $art) {
                $result['arts'][] = array(
                    'name'  => basename($art),
                    'id'    => $k,
                    'cat'   => array($key),
                    'file'  => '',
                    'url'   => Nbdesigner_IO::wp_convert_path_to_url($art)
                );
            }               
        }       
        $result['flag'] = 1;
  
        echo json_encode($result);
        wp_die();         
    }
    public function nbdesigner_get_art(){
        if (!wp_verify_nonce($_REQUEST['nonce'], 'nbdesigner-get-data') && NBDESIGNER_ENABLE_NONCE) {
            die('Security error');
        }       
        $result = array();
        $path_cat = NBDESIGNER_DATA_DIR . '/art_cat.json';
        $path_art = NBDESIGNER_DATA_DIR . '/arts.json';
        $result['flag'] = 1;
        $result['cat'] = $this->nbdesigner_read_json_setting($path_cat);
        $result['arts'] = $this->nbdesigner_read_json_setting($path_art);   
        echo json_encode($result);
        wp_die();        
    }
    public function _nbdesigner_get_font(){
        if (!wp_verify_nonce($_REQUEST['nonce'], 'nbdesigner-get-data') && NBDESIGNER_ENABLE_NONCE) {
            die('Security error');
        }   
        $result = array();
        $path = NBDESIGNER_DATA_DIR . '/fonts';
        $cats = Nbdesigner_IO::get_list_folder($path, 1);
        foreach ($cats as $key => $cat){
            $result['cat'][] = array(
                'name'  => basename($cat),
                'id'    => $key                
            );
            $list = Nbdesigner_IO::get_list_files($path . '/' . basename($cat), 1);
            $arts = preg_grep('/\.(ttf|woff)(?:[\?\#].*)?$/i', $list);
            foreach($arts as $k => $art) {
                $result['fonts'][] = array(
                    'name'  => pathinfo($art, PATHINFO_FILENAME ),
                    'id'    => $k,
                    'cat'   => array($key),
                    'alias' => 'nbfont' . substr(md5(rand(0, 999999)), 0, 10),
                    'file'  => '',
                    'url'   => Nbdesigner_IO::wp_convert_path_to_url($art)
                );
            }               
        }      
        $path_google_font = NBDESIGNER_DATA_DIR . '/googlefonts.json';
        $result['google_font'] = $this->nbdesigner_read_json_setting($path_google_font);
        $result['flag'] = 1;  
        echo json_encode($result);
        wp_die();         
    }    
    public function nbdesigner_get_font(){          
        if (!wp_verify_nonce($_REQUEST['nonce'], 'nbdesigner-get-data') && NBDESIGNER_ENABLE_NONCE) {
            die('Security error');
        }   
        $result = array();
        $path_cat = NBDESIGNER_DATA_DIR . '/font_cat.json';
        $path_font = NBDESIGNER_DATA_DIR . '/fonts.json';
        $path_google_font = NBDESIGNER_DATA_DIR . '/googlefonts.json';
        $result['flag'] = 1;
        $result['cat'] = $this->nbdesigner_read_json_setting($path_cat);
        $result['fonts'] = $this->nbdesigner_read_json_setting($path_font);         
        $result['google_font'] = $this->nbdesigner_read_json_setting($path_google_font);            
        echo json_encode($result);
        wp_die();        
    }
    public function nbdesigner_get_pattern(){           
        if (!wp_verify_nonce($_REQUEST['nonce'], 'nbdesigner-get-data') && NBDESIGNER_ENABLE_NONCE) {
            die('Security error');
        }   
        $result = array();
        $path = NBDESIGNER_PLUGIN_DIR. 'data/pattern.json';
        $result['flag'] = 1;
        $result['data'] = $this->nbdesigner_read_json_setting($path);           
        echo json_encode($result);
        wp_die();        
    }

    public function nbd_get_resource(){
        $flag = 1;
        $data = array();
        if (!wp_verify_nonce($_REQUEST['nonce'], 'nbdesigner-get-data') && NBDESIGNER_ENABLE_NONCE) {
            //todo something
        }else{     
            $rq_type = $_REQUEST['type'];
            switch ($rq_type) {
                case 'typography':
                    $path = $_REQUEST['task'] == 'typography' ? NBDESIGNER_PLUGIN_DIR . '/data/typography/typography.json' : NBDESIGNER_PLUGIN_DIR . '/data/typography/typo.json';
                    if(file_exists($path) ) $data = json_decode( file_get_contents($path) );
                    break;  
                case 'get_typo':
                    $path = NBDESIGNER_PLUGIN_DIR . '/data/typography/store/'.$_REQUEST['folder'];
                    $data['font'] = json_decode( file_get_contents($path.'/used_font.json') );
                    $data['design'] = json_decode( file_get_contents($path.'/design.json') );
                    break;                         
                case 'clipart':
                    $path_cat = NBDESIGNER_DATA_DIR . '/art_cat.json';
                    $path_art = NBDESIGNER_DATA_DIR . '/arts.json'; 
                    $data['cat'] = $data['arts'] = array();
                    if( file_exists($path_cat) ){
                        $_cat = file_get_contents($path_cat);
                        $data['cat'] = $_cat == '' ? array() : json_decode($_cat);
                    }
                    if( file_exists($path_art) ){
                        $_art = file_get_contents($path_art);
                        $data['arts'] = $_art == '' ? array() : json_decode($_art);
                    }
                    break;
                case 'save_typography':
                    $path = NBDESIGNER_PLUGIN_DIR . 'data/typography/typo.json';
                    $folder = substr(md5(uniqid()),0,5).rand(1,100).time();
                    $store_path = NBDESIGNER_PLUGIN_DIR . 'data/typography/store/' . $folder;
                    if( !file_exists($store_path) ) wp_mkdir_p ($store_path);
                    $design_path = $store_path . '/design.json';
                    $used_font_path = $store_path . '/used_font.json';
                    $preview_path = $store_path . '/preview.png';
                    $list_typo = array();
                    if(file_exists($path) ) $list_typo = json_decode(file_get_contents ($path));
                    foreach ($_FILES as $key => $val) {
                        switch($key){
                            case 'design': 
                                $full_name = $design_path;
                                break;
                            case 'used_font': 
                                $full_name = $used_font_path;
                                break;     
                            case 'frame_0': 
                                $full_name = $preview_path;
                                break;                                
                        };
                        if ( !move_uploaded_file($val["tmp_name"],$full_name) ) {      
                            $flag = 0;
                        }                      
                    };
                    if( $flag ){
                        $id = isset($_REQUEST['id']) ? absint($_REQUEST['id'] - 1) : count($list_typo);
                        $new_typo = array(
                            'id' => $id,
                            'folder'    =>  $folder
                        );
                        $exist_id = -1;
                        foreach ($list_typo as $index => $typo){
                            if( $typo->id == $id ){
                                $exist_id = $index; break;
                            }
                        }
                        if($exist_id > -1){
                            $list_typo[$exist_id] = $new_typo;
                        }else{
                            $list_typo[] = $new_typo;
                        }
                        file_put_contents($path, json_encode($list_typo));
                        $data['typo'] = $list_typo;
                    }
                    break;
                case 'google_font':
                    $all_gg_fonts = json_decode(file_get_contents(NBDESIGNER_PLUGIN_DIR. '/data/google-fonts-ttf.json'))->items;
                    $font_name = $_REQUEST['font_name'];
                    $subset = 'all';
                    $file = array('r' => 1);
                    $flag = 0;
                    foreach( $all_gg_fonts as $f ){
                        if( $font_name == $f->family || str_replace(" ","",$font_name) == $f->family ){
                            if( str_replace(" ","",$font_name) == $f->family ) $font_name = str_replace(" ","",$font_name);
                            $subset = $f->subsets[0];
                            if( isset($f->files->italic) ){
                                $file['i'] = 1;
                            }
                            if( isset($f->files->{"700"}) ){
                                $file['b'] = 1;
                            }
                            if( isset($f->files->{"700italic"}) ){
                                $file['bi'] = 1;
                            }  
                            $flag = 1;
                            break;
                        }
                    }
                    $data = array(
                        "id"    =>  99,
                        "name"    =>  $font_name,
                        "alias"    =>  $font_name,
                        "type"   =>  "google", 
                        "subset"   =>  $subset, 
                        "file"   =>  $file, 
                        "cat" => array("99")
                    );
                    break;
                case 'save_user_design':
                    global $wpdb;
                    $created_date = new DateTime();
                    $user_id = wp_get_current_user()->ID;
                    $table_name =  $wpdb->prefix . 'nbdesigner_user_designs';
                    $folder = substr(md5(uniqid()),0,5).rand(1,100).time();
                    $store_path = NBDESIGNER_DATA_DIR . '/designs/' . $folder;
                    if( !file_exists($store_path) ) wp_mkdir_p ($store_path);
                    $design_path = $store_path . '/design.json';
                    $preview_path = $store_path . '/preview.png';
                    foreach ($_FILES as $key => $val) {
                        switch($key){
                            case 'design': 
                                $full_name = $design_path;
                                break; 
                            case 'preview': 
                                $full_name = $preview_path;
                                break;                                
                        };
                        if ( !move_uploaded_file($val["tmp_name"],$full_name) ) {      
                            $flag = 0;
                        }
                    }
                    if( $flag ){
                        $rerult = $wpdb->insert($table_name, array(
                            'folder' => $folder,
                            'user_id' => $user_id,
                            'created_date' => $created_date->format('Y-m-d H:i:s')
                        ));
                        if($rerult){
                            $data['preview'] = Nbdesigner_IO::wp_convert_path_to_url( $preview_path );
                            $data['id'] = $wpdb->insert_id;
                        }else{
                            $flag = 0;
                        }
                    }
                    break;
                case 'load_user_designs':
                    global $wpdb;
                    $user_id = wp_get_current_user()->ID;
                    $table_name =  $wpdb->prefix . 'nbdesigner_user_designs';
                    $templates = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE user_id = {$user_id} ORDER BY created_date DESC", 'ARRAY_A' );
                    if( $templates ){
                        $data['user_designs'] = array();
                        foreach ($templates as $template){
                            $data['user_designs'][] = array(
                                'id' => $template['id'],
                                'preview' =>  Nbdesigner_IO::wp_convert_path_to_url( NBDESIGNER_DATA_DIR . '/designs/' . $template['folder'] . '/preview.png' )
                            );
                        }
                    }else{
                        $flag = 0;
                    }
                    break;
                case 'load_user_design':
                    global $wpdb;
                    $template_id = absint($_POST['template_id']);
                    $user_id = wp_get_current_user()->ID;
                    $table_name =  $wpdb->prefix . 'nbdesigner_user_designs';
                    $template = $wpdb->get_row( "SELECT * FROM {$table_name} WHERE id = {$template_id}" );
                    if( $template ){
                        $store_path = NBDESIGNER_DATA_DIR . '/designs/' . $template->folder;
                        $design_path = $store_path . '/design.json';
                        $data['design'] = json_decode( file_get_contents($design_path) );
                    }else{
                        $flag = 0;
                    }
                    break;
                case 'delete_user_design':
                    global $wpdb;
                    $template_id = absint($_POST['template_id']);
                    $table_name =  $wpdb->prefix . 'nbdesigner_user_designs';
                    $result = $wpdb->delete("$table_name", array('id' => $template_id));
                    if( !$result ) $flag = 0;
                    break;
                case 'get_mockup':    
                    $_mockups = $_REQUEST['mockups'];
                    $folder = $_REQUEST['folder'];
                    $mockups = explode('|', $_mockups);
                    $path = $path = NBDESIGNER_CUSTOMER_DIR . '/' . $folder . '/';
                    $files = array();
                    foreach($mockups as $mockup){
                        if(file_exists($path.$mockup)){
                            $files[] = $path.$mockup;
                        }
                    }
                    $pathZip = NBDESIGNER_DATA_DIR.'/download/customer-design-'.time().'.zip';
                    if(count($files)){
                        nbd_zip_files_and_download($files, $pathZip, 'mockups.zip', array(), false);
                        $data['url'] = Nbdesigner_IO::wp_convert_path_to_url( $pathZip );
                    }else{
                        $flag = 0;
                    }
                    break;
            }
        }
        $this->wp_send_json(
            array( 
                'flag' =>  $flag, 
                'data'  =>  $data
            )
        );
    }

    public function nbd_get_designs_in_cart(){
        if( !isset($_POST['did']) ){

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $session = $objectManager->create('\Magento\Framework\Session\SessionManager');
            $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

            // global $woocommerce; 
            // $items = $woocommerce->cart->get_cart();
            // $result = array(
            //     'flag'   =>  1,
            //     'designs'   =>  array()
            // );
            // foreach($items as $cart_item_key => $values) { 
            //     $nbd_session = WC()->session->get($cart_item_key. '_nbd');
            //     if($nbd_session){
            //         $path_preview = NBDESIGNER_CUSTOMER_DIR .'/'.$nbd_session. '/preview';
            //         $listThumb = Nbdesigner_IO::get_list_images($path_preview);
            //         $image = '';
            //         if(count($listThumb)){
            //             $image = Nbdesigner_IO::wp_convert_path_to_url(end($listThumb));
            //             $result['designs'][] = array(
            //                 'id'    =>  $nbd_session,
            //                 'src'  =>  $image
            //             );
            //         }  
            //     }
            // }


            $items = $cart->getQuote()->getAllItems();
            $result = array(
                'flag'   =>  1,
                'designs'   =>  array()
            );
            foreach($items as $cart_item_key => $values) { 
                $nbd_session = $session->getData('visitor_data')['session_id'];
                if($nbd_session){
                    $path_preview = NBDESIGNER_CUSTOMER_DIR .'/'.$nbd_session. '/preview';
                    $listThumb = Nbdesigner_IO::get_list_images($path_preview);
                    $image = '';
                    if(count($listThumb)){
                        $image = Nbdesigner_IO::wp_convert_path_to_url(end($listThumb));
                        $result['designs'][] = array(
                            'id'    =>  $nbd_session,
                            'src'  =>  $image
                        );
                    }  
                }
            }
        } else { 
            $folder = $_POST['did'];
            $result = nbd_get_template_by_folder( $folder );
            $result['flag'] = 1;
        }
        echo json_encode($result);
        wp_die();
    }

    protected function wp_send_json( $response, $status_code = null ) {
        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
        if ( null !== $status_code ) {
            status_header( $status_code );
        }
        echo json_encode( $response );
        die;
    }

    private function zip_files_and_download($file_names, $archive_file_name, $nameZip){
        if(file_exists($archive_file_name)){
            unlink($archive_file_name);
        }        
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($archive_file_name, ZIPARCHIVE::CREATE )!==TRUE) {
              exit("cannot open <$archive_file_name>\n");
            }
            foreach( $file_names as $file ) {
                $path_arr = explode('/', $file);
                $name = $path_arr[count($path_arr) - 2].'_'.$path_arr[count($path_arr) - 1]; 
                $zip->addFile($file, $name);
            }
            $zip->close();
        }else{
            require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
            $archive = new PclZip($archive_file_name);
            foreach($file_names as $file){
                $path_arr = explode('/', $file);
                $dir = dirname($file).'/';                
                $archive->add($file, PCLZIP_OPT_REMOVE_PATH, $dir, PCLZIP_OPT_ADD_PATH, $path_arr[count($path_arr) - 2]);               
            }            
        }
        if ( !is_file( $archive_file_name ) ){
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
            exit;
        } elseif ( !is_readable( $archive_file_name ) ){
            header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
            exit;
        } else {
            header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
            header("Pragma: public");
            header("Expires: 0");
            header("Accept-Ranges: bytes");
            header("Connection: keep-alive");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-type: application/zip");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=\"".$nameZip."\"");
            header('Content-Length: '.filesize($archive_file_name));
            header("Content-Transfer-Encoding: binary");
            ob_clean();
            @readfile($archive_file_name);
            exit;    
        }      
    }
    /**
     * 
     * Create pages: Re-design, Admin-design, Studio if they're not exist.
     * 
     * @global type $wpdb
     */
    public static function nbdesigner_add_custom_page(){
        /* Create Studio page */
        $studio_page_id = nbd_get_page_id( 'studio' );
        if ( $studio_page_id == -1 || !get_post($studio_page_id) ){        
            $post = array(
                'post_name' => NBDESIGNER_PAGE_STUDIO,
                'post_status' => 'publish',
                'post_title' => __('Designer Studio', 'web-to-print-online-designer'),
                'post_type' => 'page',
                'post_author'    => 1,
                'comment_status' => 'closed',                
                'post_date' => date('Y-m-d H:i:s')
            );      
            $studio_page_id = wp_insert_post($post, false);
            update_option( 'nbdesigner_studio_page_id', $studio_page_id );
        }      
        
        /* Create design your own page */   
        $create_your_own_page_id = nbd_get_page_id( 'create_your_own' );
        if ( $create_your_own_page_id == -1|| !get_post($create_your_own_page_id) ){        
            $post = array(
                'post_name' => NBDESIGNER_PAGE_CREATE_YOUR_OWN,
                'post_status' => 'publish',
                'post_title' => __('Create your own', 'web-to-print-online-designer'),
                'post_type' => 'page',
                'post_author'    => 1,
                'comment_status' => 'closed',                
                'post_date' => date('Y-m-d H:i:s')
            );      
            $create_your_own_page_id = wp_insert_post($post, false);    
            update_option( 'nbdesigner_create_your_own_page_id', $create_your_own_page_id );    
        }      
        
        /* Create redirect login page */  
        $nbd_redirect_logged_page_id = nbd_get_page_id( 'logged' );
        if ( $nbd_redirect_logged_page_id == -1 || !get_post($nbd_redirect_logged_page_id) ){        
            $post = array(
                'post_name' => 'nbd-logged',
                'post_status' => 'publish',
                'post_title' => __('Welcome to NBDesigner', 'web-to-print-online-designer'),
                'post_type' => 'page',
                'post_author'    => 1,
                'post_content'    => '[nbd_loggin_redirect]',
                'comment_status' => 'closed',                
                'post_date' => date('Y-m-d H:i:s')
            );      
            $nbd_redirect_logged_page_id = wp_insert_post($post, false);
            update_option( 'nbdesigner_logged_page_id', $nbd_redirect_logged_page_id ); 
        }  
        
        /* Create gallery page */  
        $nbd_gallery_page_id = nbd_get_page_id( 'gallery' );
        if ( $nbd_gallery_page_id == -1 || !get_post($nbd_gallery_page_id) ){        
            $post = array(
                'post_name' => 'templates',
                'post_status' => 'publish',
                'post_title' => __('Gallery', 'web-to-print-online-designer'),
                'post_type' => 'page',
                'post_author'    => 1,
                'post_content'    => '[nbdesigner_gallery row="6" pagination="true" per_row="5" ][/nbdesigner_gallery]',
                'comment_status' => 'closed',                
                'post_date' => date('Y-m-d H:i:s')
            );      
            $nbd_gallery_page_id = wp_insert_post($post, false);    
            update_option( 'nbdesigner_gallery_page_id', $nbd_gallery_page_id ); 
        }  
        
        /* Create designer page */  
        $nbd_redirect_designer_page_id = nbd_get_page_id( 'designer' );
        if ( $nbd_redirect_designer_page_id == -1 || !get_post($nbd_redirect_designer_page_id) ){        
            $post = array(
                'post_name' => 'designer',
                'post_status' => 'publish',
                'post_title' => __('Designer', 'web-to-print-online-designer'),
                'post_type' => 'page',
                'post_author'    => 1,
                'post_content'    => '',
                'comment_status' => 'closed',                
                'post_date' => date('Y-m-d H:i:s')
            );      
            $nbd_redirect_designer_page_id = wp_insert_post($post, false);  
            update_option( 'nbdesigner_designer_page_id', $nbd_redirect_designer_page_id );         
        }  
    }
    public function add_display_post_states( $post_states, $post ){
        if ( nbd_get_page_id( 'gallery' ) === $post->ID ) {
            $post_states['nbd_gallery_page'] = __( 'NBD Gallery Page', 'woocommerce' );
        }
        if ( nbd_get_page_id( 'create_your_own' ) === $post->ID ) {
            $post_states['nbd_create_your_own_page'] = __( 'NBD Design Editor Page', 'woocommerce' );
        }
        if ( nbd_get_page_id( 'designer' ) === $post->ID ) {
            $post_states['nbd_designer_page'] = __( 'NBD Designer gallery Page', 'woocommerce' );
        }   
        if ( nbd_get_page_id( 'product_builder' ) === $post->ID ) {
            $post_states['nbd_product_builder_page'] = __( 'NBD Product builder Page', 'woocommerce' );
        }
        return $post_states;
    }
    public function nbdesigner_frontend_translate(){
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        $languages = wp_get_available_translations();
        $path = NBDESIGNER_PLUGIN_DIR . 'data/language.json';  
        $path_data = NBDESIGNER_DATA_CONFIG_DIR . '/language.json';
        if(file_exists($path_data)) $path = $path_data;  
        $path_lang = NBDESIGNER_PLUGIN_DIR . 'data/language/en_US.json';
        $path_data_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language/en_US.json';
        if(file_exists($path_data_lang)) $path_lang = $path_data_lang;  
        $list = json_decode(file_get_contents($path));     
        $lang = json_decode(file_get_contents($path_lang)); 
        if(is_array($lang)){
            $langs = (array)$lang[0];
            asort($langs);
        }
        require(NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-translate.php');
    }
    public function nbdesigner_save_language(){
        $data = array(
            'mes'   =>  __('You do not have permission to edit language!', 'web-to-print-online-designer'),
            'flag'  => 0
        );                 
        if(isset($_POST['langs'])){
            $langs = array();
            $langs[0] = $_POST['langs'];
        }
        if(isset($_POST['code'])){
            $code = $_POST['code'];
        } 
        if(isset($langs) && isset($code)){
            $path_lang = NBDESIGNER_PLUGIN_DIR . 'data/language/'.$code.'.json';
            $path_data = NBDESIGNER_DATA_CONFIG_DIR . '/language/'.$code.'.json';
            if(file_exists($path_data)){
                $path_lang = $path_data;                   
            }else{
                if($code = "en_US") {
                    $path_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language/en_US.json';
                }
            } 
            foreach ($langs[0] as $key => $lang){
                //$lang = preg_replace('#\\\\{2,}#','\\', $lang);
                //$lang = str_replace('\\\\','\\', $lang);
                $lang = stripslashes($lang);
                $langs[0][$key] = strip_tags($lang);
            }   
            $res = json_encode($langs);
            file_put_contents($path_lang, $res);   
            $data['mes'] =  __('Update language success!', 'web-to-print-online-designer');
            $data['flag'] = 1;
        }else{
            $data['mes'] = __('Update language failed!', 'web-to-print-online-designer');
        }
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_get_language($code){        
        if(!isset($code)){
            $code = "en";
        }else if(isset($_POST['code'])) {
            $code = $_POST['code'];          
        }
        $data = nbd_get_language($code);
        echo json_encode($data);
        wp_die();
    }    
    public function nbdesigner_delete_language(){
        $data = array(
            'mes'   =>  __('You do not have permission to delete language!', 'web-to-print-online-designer'),
            'flag'  => 0
        );          
        $code = $_POST['code'];
        $index = $_POST['index'];
        $path_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language/'.$code.'.json';
        $path_data_cat_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language.json';         
        $cats = json_decode(file_get_contents($path_data_cat_lang)); 
        $primary_lang_code = $cats[0]->code;
        $path_primary_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language/'.$primary_lang_code.'.json';
        if(!file_exists($path_primary_lang)){
            $path_primary_lang = NBDESIGNER_PLUGIN_DIR . 'data/language/'.$primary_lang_code.'.json';
        }
        if($index != 0){
            if(unlink($path_lang)) {
                $data['flag'] = 1;
                $this->nbdesigner_delete_json_setting($path_data_cat_lang, $index);
                $data['mes'] = __('Delete language success!', 'web-to-print-online-designer');
                $langs = json_decode(file_get_contents($path_primary_lang)); 
                $data['langs'] = (array)$langs[0];
            }            
        }else {
            $data['mes'] = __('Oops! Can not delete primary language!', 'web-to-print-online-designer');
        }
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_create_language(){    
        $data = array(
            'mes'   =>  __('You do not have permission to create language!', 'web-to-print-online-designer'),
            'flag'  => 0
        );        
        if (!wp_verify_nonce($_POST['nbdesigner_newlang_hidden'], 'nbdesigner-new-lang') || !current_user_can('edit_nbd_language')) {
            echo json_encode($data);
            wp_die();
        } 
        if(isset($_POST['nbdesigner_codelang']) && isset($_POST['nbdesigner_namelang'])){           
            $code = sanitize_text_field($_POST['nbdesigner_codelang']);
            $path_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language/'.$code.'.json';
            $path_original_lang = NBDESIGNER_PLUGIN_DIR . 'data/language/en_US.json';
            $path_original_data_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language/en_US.json';
            if(file_exists($path_original_data_lang)) $path_original_lang = $path_original_data_lang;
            $path_cat_lang = NBDESIGNER_PLUGIN_DIR . 'data/language.json';
            $path_data_cat_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language.json';
            if(file_exists($path_data_cat_lang)) $path_cat_lang = $path_data_cat_lang;            
            $cats = json_decode(file_get_contents($path_cat_lang)); 
            $lang = json_decode(file_get_contents($path_original_lang)); 
            $_cat = array();
            $_cat['id'] = 1;
            if(is_array($cats)){                 
                foreach($cats as $cat){                  
                    if($cat->code == $code){
                        $code .=  rand(1,100);
                    }
                    $_cat['id'] = $cat->id;
                }
                $_cat['id'] += 1;
            }else{
                $data['mes'] = 'error';
                echo json_encode($data);
                wp_die();                
            } 
            if(is_array($lang)){
                $data['langs'] = (array)$lang[0];
                $data['code'] = $code;
                $_cat['code'] = $code; 
                $data['name'] = sanitize_text_field($_POST['nbdesigner_namelang']);
                $_cat['name'] = $data['name'];
                if (!copy($path_original_lang, $path_lang)) {
                    $data['mes'] = 'error';
                }else{
                    array_push($cats, $_cat);                  
                    file_put_contents($path_cat_lang, json_encode($cats));   
                    file_put_contents($path_data_cat_lang, json_encode($cats));   
                }
                $data['mes'] = 'Your language has been created successfully!';
                $data['flag'] = 1;
            }else{
                $data['mes'] = 'error';
            }            
        }  
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_editor_html(){
        $task = (isset($_GET['task']) && $_GET['task'] != '' ) ? $_GET['task'] : '';
        $task2 = (isset($_GET['task2']) && $_GET['task2'] != '' ) ? $_GET['task2'] : '';
        $product_id = (isset($_GET['product_id']) && absint($_GET['product_id']) != 0 ) ? $_GET['product_id'] : 0;
        if( $product_id == 0 ) return;
        $layout = nbd_get_product_layout($product_id);
        $view = (isset($_GET['view']) && $_GET['view'] != '' ) ? $_GET['view'] : $layout;
        if( $view == 'm' || $view == 'v' ){
            $path = NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-frontend-modern.php';
        } else {
            $path = NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-frontend-template.php';
        }
        if($task == 'reup' || $task2 == 'add_file' || $view == 'c'){
            $path = NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-frontend-template.php';
        }
        if(is_nbd_design_page()){
            include($path);exit();
        }else{
            if ( ( ! defined('DOING_AJAX') || ! DOING_AJAX ) && ( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'nbdesigner_editor_html' ) ) return;
            include($path);exit();            
        }
    }
    public function nbdesigner_make_primary_design(){
        $result = array();
        if(isset($_POST['id']) && isset($_POST['folder']) && isset($_POST['task'])){
            $pid = $_POST['id'];
            $folder = $_POST['folder'];
            $task = $_POST['task'];
            $check = true;
            if($task == 'primary'){
                $path_primary = NBDESIGNER_DATA_DIR . '/admindesign/' . $pid . '/primary'; 
                $path_primary_old = NBDESIGNER_DATA_DIR . '/admindesign/' . $pid . '/primary_old'; 
                $path_primary_new = NBDESIGNER_DATA_DIR . '/admindesign/' . $pid . '/' .$folder; 
                if(!rename($path_primary, $path_primary_old)) $check = false; 
                if(!rename($path_primary_new, $path_primary)) $check = false; 
                if(!rename($path_primary_old, $path_primary_new)) $check = false;                 
            }
            if( $check ) $result['mes'] = 'success'; else $result['mes'] = 'error';             
        }else{
            $result['mes'] = 'Invalid data';
        }  
        echo json_encode($result);
        wp_die();
    }
    public function nbdesigner_load_admin_design(){
        $result = array();
        if(isset($_POST['product_id'])){
            $product_id = absint($_POST['product_id']);
            $variation_id = absint($_POST['variation_id']);
            $list_design = array();
            $templates = nbd_get_templates($product_id, $variation_id);
            foreach ($templates as $tem){
                $path_preview = NBDESIGNER_CUSTOMER_DIR .'/'.$tem['folder']. '/preview';
                $listThumb = Nbdesigner_IO::get_list_images($path_preview);
                $image = '';
                if(count($listThumb)){
                    $image = Nbdesigner_IO::wp_convert_path_to_url(end($listThumb));
                    $list_design[] = array(
                        'id'    =>  $tem['folder'],  
                        'src'   =>  $image
                    );
                }                
            }
            $result['data'] = $list_design;
            $result['mes'] = 'success';
        }else{
            $result['mes'] = 'Invalid data';
        }
        echo json_encode($result);
        wp_die();        
    }
    public function nbdesigner_tools(){
        $custom_css = Nbdesigner_DebugTool::get_custom_css();
        $custom_js = Nbdesigner_DebugTool::get_custom_js();
        if(isset($_REQUEST['action']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'remove_log')){
            unlink(NBDESIGNER_LOG_DIR . '/debug.log');
            wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=nbdesigner_tools' ) ) );
            exit();
        }
        include_once(NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-tools.php');
    }
    public function nbd_support(){
        $remote = get_transient('nbd_upgrade_news_web-to-print-online-designer');
        $nbd_news = json_decode($remote['body']);
        include_once(NBDESIGNER_PLUGIN_DIR . 'views/support.php');
    }
    public function nbdesigner_variation_settings_fields($loop, $variation_data, $variation){
        $vid = $variation->ID;
        $enable = get_post_meta($vid, '_nbdesigner_variation_enable', true);
        $default =  nbd_default_product_setting();    
        $designer_setting = array();
        $designer_setting[0] = $default;
        $dpi = get_post_meta($vid, '_nbdesigner_dpi', true);
        if($dpi == "") $dpi = nbdesigner_get_option('nbdesigner_default_dpi');
        $unit = nbdesigner_get_option('nbdesigner_dimensions_unit');     
        $_designer_setting = unserialize(get_post_meta($vid, '_designer_variation_setting', true));
        if (isset($_designer_setting[0])){
            $designer_setting = $_designer_setting;
            if(! isset($designer_setting[0]['version']) || $_designer_setting[0]['version'] < 160) {
                $designer_setting = $this->update_config_product_160($designer_setting);
            }
            if(! isset($designer_setting[0]['version']) || $_designer_setting[0]['version'] < 180) {
                $designer_setting = NBD_Update_Data::nbd_update_media_v180($designer_setting);
            }            
        }
        $designer_setting = nbd_update_config_default($designer_setting);
        include(NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-box-design-setting-variation.php');
    }
    public function nbdesigner_save_variation_settings_fields($post_id){
        if(!(current_user_can('administrator') || current_user_can('shop_manager')) || !isset($_POST['_nbdesigner_enable'.$post_id])){
            return $post_id;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }  
        $var = get_post($post_id);
        $enable = $_POST['_nbdesigner_enable'.$post_id]; 
        $setting = serialize($_POST['_designer_setting'.$post_id]);  
        if(!$this->nbdesigner_allow_create_product($var->post_parent)) return;
        update_post_meta($post_id, '_nbdesigner_variation_enable', $enable);
        update_post_meta($post_id, '_designer_variation_setting', $setting);
    }    
    public function nbdesigner_copy_image_from_url(){ 
        $url = $_POST['url'];
        $ext = $this->nbdesigner_get_extension($url);
        $allow_extension = array('jpg','jpeg','png','gif');
        if(!in_array(strtolower($ext), $allow_extension)) $ext = 'png';
        $new_name = strtotime("now").substr(md5(rand(1111,9999)),0,8).'.'.$ext;
        $path = NBDESIGNER_TEMP_DIR.'/'.$new_name;
        $res['flag'] = 0;
        $res['src'] = NBDESIGNER_TEMP_URL.'/'.$new_name;
        if( isset( $_POST['gapi'] ) && strpos($url, 'drive.google.com/file') ){     
            $param = $_POST['gapi'];
            $oAuthToken = $param['oAuthToken'];
            $fileId = $param['fileId'];
            $new_name = strtotime("now").substr(md5(rand(1111,9999)),0,8).'_'.$param['name']; 
            $path = Nbdesigner_IO::create_file_path(NBDESIGNER_TEMP_DIR, $new_name);
            $getUrl = 'https://www.googleapis.com/drive/v2/files/' . $fileId . '?alt=media';     
            $authHeader = 'Authorization: Bearer ' . $oAuthToken;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $getUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($authHeader));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $data = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            if( !$error ){
                file_put_contents($path['full_path'], $data);   
                $res['src'] = NBDESIGNER_TEMP_URL.$path['date_path'];
                $res['flag'] = 1;                
            }
        }else{
            if(@copy($url, $path)){
                $res['flag'] = 1;
            }else{
                $response = wp_remote_get($url);
                if (is_wp_error($response)){
                    echo json_encode($res);
                    wp_die();                    
                }else{
                    $result = @file_put_contents($path, $response['body']);
                    unset($response['body']);
                    if ($result === false) {
                        $res['flag'] = 0;
                    }else{
                        $res['flag'] = 1;
                    }
                }
                
            }     
            
        }
        echo json_encode($res);
        wp_die();
    }
    public function nbdesigner_add_tinymce_editor(){
        global $typenow;
        // check user permissions
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) return;
        $post_types = get_post_types();
        if (!is_array($post_types)) $post_types = array('post', 'page');
        // verify the post type
        if (!in_array($typenow, $post_types)) return;
        // check if WYSIWYG is enabled
        if (get_user_option('rich_editing') == 'true') {
            add_filter('mce_external_plugins', array($this, 'nbdesigner_add_tinymce_shortcode_editor_plugin'));
            add_filter('mce_buttons', array($this, 'nbdesigner_add_tinymce_shortcode_editor_button'));
        }
        add_action('in_admin_footer', array($this, 'nbdesigner_add_tiny_mce_shortcode_dialog'));
    }   
    public function nbdesigner_add_tinymce_shortcode_editor_plugin($plugin_array){
        $plugin_array['nbdesigner_button'] = NBDESIGNER_JS_URL . 'nbdesigner-tinymce-shortcode.js';     
        return $plugin_array;        
    }
    public function nbdesigner_add_tinymce_shortcode_editor_button($buttons){   
        array_push($buttons, "nbdesigner_button");      
        return $buttons;         
    }    
    public function nbdesigner_add_tiny_mce_shortcode_dialog(){
        include (NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-shortcode-dialog.php');
    }
    public function nbdesigner_get_suggest_design(){          
        $result = array(
            'mes'   =>  'success',
            'flag'  =>  1
        );

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        if(isset($_POST['product_id']) && isset($_POST['products']) && isset($_POST['variation_id'])){    
            $product_id = $_POST['product_id'];
            $products = $_POST['products'];
            $variation_id = $_POST['variation_id'];
            $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id; 
            $nbd_item_key = $session->get('nbd_item_key_'.$nbd_item_cart_key); 
            $path_src = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key;
            if( isset($nbd_item_key) && is_array($products) ){
                $result['nbd_item_key'] = $nbd_item_key;
                foreach ($products as $tg_product_id){
                    $tg_variation_id = nbd_get_default_variation_id( $tg_product_id );
                    if($tg_variation_id > 0){         
                        $product_config = unserialize(get_post_meta($tg_variation_id, '_designer_variation_setting', true));
                        $enable_variation = get_post_meta($tg_variation_id, '_nbdesigner_variation_enable', true);              
                        if ( !($enable_variation && isset($product_config[0]))){
                            $product_config = unserialize(get_post_meta($tg_product_id, '_designer_setting', true)); 
                        }
                    }else {
                        $product_config = unserialize(get_post_meta($tg_product_id, '_designer_setting', true)); 
                    } 
                    $ref_product_config = unserialize(file_get_contents($path_src . '/product.json'));
                    foreach ($product_config as $key => $_config){
                        if( isset($ref_product_config[$key]) ){
                            $product_config[$key]['img_src'] = is_numeric( $product_config[$key]['img_src'] ) ? wp_get_attachment_url( $product_config[$key]['img_src'] ) : $product_config[$key]['img_src'];
                            $ref_width = $ref_product_config[$key]['area_design_width'];
                            $ref_height = $ref_product_config[$key]['area_design_height'];
                            $_width = $product_config[$key]['area_design_width'];
                            $_height = $product_config[$key]['area_design_height'];
                            if($ref_width/$ref_height > $_width/$_height){
                                $product_config[$key]['area_design_height'] = round( $_width * $ref_height / $ref_width );
                                $product_config[$key]['area_design_top'] = round( 250 - $product_config[$key]['area_design_height'] / 2 );
                            }else {
                                $product_config[$key]['area_design_width'] = round( $_height * $ref_width / $ref_height );
                                $product_config[$key]['area_design_left'] = round( 250 - $product_config[$key]['area_design_width'] / 2 );                                
                            }
                        }
                    }
                    $path_dst = NBDESIGNER_SUGGEST_DESIGN_DIR . '/' . substr(md5(uniqid()),0,10);
                    $this->create_preview_design($path_src, $path_dst, $product_config, 300, 300, 1);
                    $list = Nbdesigner_IO::get_list_images( $path_dst, $level = 1 );
                    $result['images'][$tg_product_id][] = Nbdesigner_IO::wp_convert_path_to_url($list[0]);
                }
            }else{
                $result['mes'] = __('Missing product!', 'web-to-print-online-designer');
                $result['flag'] = 0;                    
            }
        }else{
            $result['mes'] = __('Missing information!', 'web-to-print-online-designer');
            $result['flag'] = 0;            
        }        
        echo json_encode($result);
        wp_die();
    }
    /**
     * Allow multi design.
     * Add Product To Cart Multiple Times But As Different items.
     * since 1.5.0
     * 
     */
    public function nbd_add_cart_item_data($cart_item_data, $product_id,  $variation_id) {
        /* Force individual cart item */
        $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id;

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        $nbd_session = $session->get('nbd_item_key_'.$nbd_item_cart_key);
        $nbu_session = $session->get('nbu_item_key_'.$nbd_item_cart_key);
        if (isset( $nbd_session ) || ( !isset( $nbd_session ) && isset( $nbu_session ) )) {
            $unique_cart_item_key = md5(microtime() . rand());
            $cart_item_data['unique_key'] = $unique_cart_item_key;
        }
        /* Update list files upload */
        if( $nbu_session && isset($_POST['nbd-upload-files']) && $_POST['nbd-upload-files'] != '' ){
            $files = $_POST['nbd-upload-files'];
            $this->update_files_upload( $files, $nbu_session );
        }
        if( isset($nbd_session) ) $cart_item_data['nbd_item_meta_ds']['nbd'] = $nbd_session;
        if( isset($nbu_session) ) $cart_item_data['nbd_item_meta_ds']['nbu'] = $nbu_session;
        return $cart_item_data;
    }
    public function get_cart_item_from_session($cart_item, $values){
        if ( isset( $values['nbd_item_meta_ds'] ) ) {
            $cart_item['nbd_item_meta_ds'] = $values['nbd_item_meta_ds'];
        }
        return $cart_item;
    }
    public function update_files_upload( $files, $nbu_session = '' ){
        $files = explode('|', $files);
        $path = NBDESIGNER_UPLOAD_DIR . '/' . $nbu_session;
        $list_files = Nbdesigner_IO::get_list_files($path);
        foreach ($list_files as $file){
            $filename = basename( $file );
            if( !in_array( $filename, $files )){
                unlink($path. '/' .$filename);
            }
        }
    }
    public function nbd_update_customer_upload(){
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        $cart_item_key = $_POST['cart_item_key'];
        $design_type = (isset($_POST['design_type']) && $_POST['design_type'] != '') ? $_POST['design_type'] : '';
        if( ($design_type == '') && ( !nbd_check_cart_item_exist($cart_item_key) || !$session->isset($cart_item_key.'_nbu') ) ) {
            echo 'Item not exist in cart';
            wp_die();
        }
        $this->update_files_upload( $_POST['nbd_file'], $_POST['nbu_item_key'] );
        if( isset($_POST['order_id']) && $design_type == 'edit_order' ){
            $order_id = absint($_POST['order_id']);
            update_post_meta($order_id, '_nbdesigner_upload_order_changed', 1);
        }
        echo 'success';
        wp_die();
    }
    public function nbdesigner_migrate_domain(){
        Nbdesigner_DebugTool::update_data_migrate_domain();
    }
    public function nbdesigner_restore_data_migrate_domain(){
        Nbdesigner_DebugTool::restore_data_migrate_domain();
    }
    public function nbdesigner_theme_check(){
        Nbdesigner_DebugTool::theme_check_hook();
    }
    public function nbdesigner_custom_css(){
        Nbdesigner_DebugTool::save_custom_css();
    }   
    public function nbdesigner_custom_js(){
        Nbdesigner_DebugTool::save_custom_js();
    }
    public function nbdesigner_save_design_to_pdf(){       
        require_once('tcpdf/tcpdf.php');     
        //require_once(NBDESIGNER_PLUGIN_DIR.'includes/fpdi/autoload.php');  
        $pdfs = $_POST['pdf'];
        $force = $_POST['force_same_format'];
        $from_type = 3;
        $order_id = $_POST['order_id'];
        $img = $pdfs[0]['customer-design'];
        $nbdArrKey = explode('/', $img);

        $key = array_search ('designs', $nbdArrKey);

        $nbd_item_key = $nbdArrKey[$key+1];
        $dpi = '150';
        $img_ext = array('jpg','jpeg','png');
        $svg_ext = array('svg');
        $eps_ext = array('eps','ai');     
        $pdf_ext = array('pdf');
        /* Add custom font */

        $used_font_path = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key. '/used_font.json';
        $used_font = json_decode( file_get_contents($used_font_path) );
        $datas = unserialize(file_get_contents(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key .'/product.json'));
        $option = unserialize(file_get_contents(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key .'/option.json'));
        $layout = isset( $option['layout'] ) ? $option['layout'] : 'm';
        $has_custom_font = false;
        if( $from_type == 3 ){
            /* From svg */
            $path_font = array();
            foreach( $used_font as $font ){
                $font_name = $font->name;
                if( $font->type == 'google' ){
                    $path_font = nbd_download_google_font($font_name);;
                }else{
                    $has_custom_font = true;
                    $_font = nbd_get_font_by_alias($font->alias);
                    foreach( $_font->file as $key => $font_file ){
                        $path_font[$key] = NBDESIGNER_FONT_DIR . '/' . $font_file;                       
                    }
                }
                $true_type = $this->nbd_get_truetype_fonts();
                if (in_array($font_name, $true_type)) {
                    foreach($path_font as $pfont){
                        $fontname = TCPDF_FONTS::addTTFfont($pfont, 'TrueType', '', 32);
                    }
                }else{
                    foreach($path_font as $pfont){
                        $fontname = TCPDF_FONTS::addTTFfont($pfont, '', '', 32);
                    }       
                }
            } 
            if($has_custom_font) {
                //todo something to change font-family
            }            
        }
        if(!is_array($pdfs)) die('Security error');
        $result = array();
        if($force){
            $mTop = $pdfs[0]["margin-top"];
            $mBottom = $pdfs[0]["margin-bottom"];
            $pdf_format = $pdfs[0]["format"];   
            $mLeft = $pdfs[0]["margin-left"];
            $mRight = $pdfs[0]["margin-right"];  
            $bgWidth = $pdfs[0]['product-width'];        
            $bgHeight = $pdfs[0]['product-height'];
            $include_bg = isset($pdfs[0]['include_background']) ? $pdfs[0]['include_background'] : 1;
            if( $include_bg == 0 ){
                $bgWidth = $pdfs[0]['cd-width'];
                $bgHeight = $pdfs[0]['cd-height'];
                $mLeft = $mRight = $mBottom = $mTop = 0;
            }
            if($pdf_format == '-1'){
                $pWidth = $bgWidth + $mLeft + $mRight;
                $pHeight = $bgHeight + $mTop + $mBottom;
                $pdf_format = array($pWidth, $pHeight);
                if($pWidth > $pHeight){
                    $orientation = "L";
                }else {
                    $orientation = "P";
                }
            }
            /* In case edit exist pdf file */
            //$pdf = new Fpdi\TcpdfFpdi($orientation, 'mm', $pdf_format, true, 'UTF-8', false);
            $pdf = new TCPDF($orientation, 'mm', $pdf_format, true, 'UTF-8', false);
            $pdf->SetMargins($mLeft, $mTop, $mRight, true);     
            $pdf->SetCreator( get_site_url() );
            $pdf->SetTitle(get_bloginfo( 'name' ));
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);       
            $pdf->SetAutoPageBreak(TRUE, 0);   
            $pdf->SetFont('helvetica', '', 14, '', false);
        }

        foreach($pdfs as $key => $_pdf){
            $customer_design = $_pdf['customer-design'];    
            $bTop = (float)$_pdf['bleed-top'];
            $bLeft = (float)$_pdf['bleed-left'];
            $bRight = (float)$_pdf['bleed-right'];
            $bBottom = (float)$_pdf['bleed-bottom'];        
            $bgWidth = (float)$_pdf['product-width'];        
            $bgHeight = (float)$_pdf['product-height'];   
            $showBleed = $_pdf['show-bleed-line'];   
            $orientation = $_pdf['orientation'];
            $mTop = (float)$_pdf["margin-top"];
            $mLeft = (float)$_pdf["margin-left"];
            $mRight = (float)$_pdf["margin-right"];
            $mBottom = (float)$_pdf["margin-bottom"];
            $cdTop = (float)$_pdf["cd-top"];
            $cdLeft = (float)$_pdf["cd-left"];
            $cdWidth = (float)$_pdf["cd-width"];
            $cdHeight = (float)$_pdf["cd-height"];    
            $background = $_pdf['background'];
            $pdf_format = $_pdf['format'];       
            $bg_type = $_pdf['bg_type'];
            $bg_color_value = $_pdf['bg_color_value'];
            $includeBg = isset($_pdf['include_background']) ? $_pdf['include_background'] : 1;
            $includeBg = ( $bg_type == 'image' ) ? $includeBg : 1;
            if( $includeBg == 0 ){
                $bgWidth = $cdWidth;
                $bgHeight = $cdHeight;
                $mLeft = $mRight = $mBottom = $mTop = $cdTop = $cdLeft = 0;
            }
            if($bg_type == 'image'){
                $path_bg = Nbdesigner_IO::convert_url_to_path( $background );
            }
            if($customer_design != ''){
                $path_cd = Nbdesigner_IO::convert_url_to_path( $customer_design );
            }            
            if($pdf_format == '-1'){
                $pWidth = $bgWidth + $mLeft + $mRight;
                $pHeight = $bgHeight + $mTop + $mBottom;
                $pdf_format = array($pWidth, $pHeight);
                if($pWidth > $pHeight){
                    $orientation = "L";
                }else {
                    $orientation = "P";
                }
            }  
            if(!$force){
                //$pdf = new Fpdi\TcpdfFpdi($orientation, 'mm', $pdf_format, true, 'UTF-8', false);
                $pdf = new TCPDF($orientation, 'mm', $pdf_format, true, 'UTF-8', false);
                $pdf->SetMargins($mLeft, $mTop, $mRight, true);     
                $pdf->SetCreator( get_site_url() );
                $pdf->SetTitle(get_bloginfo( 'name' ));
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);       
                $pdf->SetAutoPageBreak(TRUE, 0); 
                $pdf->SetFont('helvetica', '', 14, '', false);
            }         
            $pdf->AddPage($orientation, $pdf_format);
            
            if($bg_type == 'image'){
                if( $includeBg ){
                    $check_img = Nbdesigner_IO::checkFileType(basename($path_bg), $img_ext);
                    $check_svg = Nbdesigner_IO::checkFileType(basename($path_bg), $svg_ext);
                    $check_eps = Nbdesigner_IO::checkFileType(basename($path_bg), $eps_ext);
                    $check_pdf = Nbdesigner_IO::checkFileType(basename($path_bg), $pdf_ext);
                    $ext = pathinfo($path_bg);
                    if($check_img){
                        $pdf->Image($path_bg,$mLeft, $mTop, $bgWidth, $bgHeight, '', '', '', false, '');
                    }
                    if($check_svg){
                        $pdf->ImageSVG($path_bg, $mLeft,$mTop, $bgWidth, $bgHeight, '', '', '', 0, true);
                    }     
                    if($check_eps){
                       $pdf->ImageEps($path_bg, $mLeft,$mTop, $bgWidth, $bgHeight, '', true, '', '', 0, true);
                    }  
                    if( $check_pdf ){
                        $pdf->setSourceFile( $path_bg );
                        $pdf->tplId = $pdf->importPage(1);
                        $pdf->useImportedPage($pdf->tplId, 0, 0, $pWidth);
                    }
                }
            }elseif($bg_type == 'color') {
                $pdf->Rect($mLeft, $mTop,  $bgWidth, $bgHeight, 'F', '', hex_code_to_rgb($bg_color_value));
            }
            if($showBleed == 'yes' && nbdesigner_get_option('nbdesigner_bleed_stack') == 1){
                $pdf->Line(0, $mTop + $bTop, $mLeft + $bLeft, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line(0, $mTop + $bgHeight - $bBottom, $mLeft + $bLeft, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($bgWidth + $mLeft - $bRight, $mTop + $bTop, $bgWidth + $mLeft + $mRight, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($bgWidth + $mLeft - $bRight, $mTop + $bgHeight - $bBottom, $bgWidth + $mLeft + $mRight, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($mLeft + $bLeft, 0, $mLeft + $bLeft, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($mLeft + $bLeft, $mTop + $bgHeight - $bBottom, $mLeft + $bLeft, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($mLeft + $bgWidth - $bRight, 0, $mLeft + $bgWidth - $bRight, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($mLeft + $bgWidth - $bRight, $mTop + $bgHeight - $bBottom, $mLeft + $bgWidth - $bRight, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
            }
            if($customer_design != ''){
                if( $from_type != 3 ){
                    $_path_cd = $path_cd;
                    $image_name = pathinfo($path_cd, PATHINFO_FILENAME);
                    if( $from_type == 1 ) $_path_cd = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key. '/jpg/' . $image_name .'.jpg';
                    if( $from_type == 2 ) $_path_cd = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key. '/cmyk/' . $image_name .'.jpg';
                    $pdf->Image($_path_cd, $mLeft + $cdLeft, $mTop + $cdTop, $cdWidth,$cdHeight, '', '', '', false, '');  
                } else if( $from_type == 3 ){
                    $svg = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key. '/frame_'. $key .'_svg.svg';
                    /* Maybe convert url in svg to path */
                    if( nbdesigner_get_option( 'nbdesigner_fix_lost_pdf_image', 'no' ) == 'yes' ){
                        $svg = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key. '/svgpath/frame_'. $key .'_svg.svg';
                        $this->convert_svg_url(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key .'/', 'frame_'. $key .'_svg.svg');
                    }
                    $pdf->ImageSVG($svg, $mLeft + $cdLeft, $mTop + $cdTop, $cdWidth,$cdHeight, '', '', '', 0, true);  
                }
            }
            if($showBleed == 'yes' && nbdesigner_get_option('nbdesigner_bleed_stack') == 2){
//                $pdf->Line(0, $mTop + $bTop, $mLeft, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
//                $pdf->Line(0, $mTop + $bgHeight - $bBottom, $mLeft, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
//                $pdf->Line($bgWidth + $mLeft, $mTop + $bTop, $bgWidth + $mLeft + $mRight, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
//                $pdf->Line($bgWidth + $mLeft, $mTop + $bgHeight - $bBottom, $bgWidth + $mLeft + $mRight, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
//                $pdf->Line($mLeft + $bLeft, 0, $mLeft + $bLeft, $mTop, array('color' => array(0,0,0), 'width' => 0.05));
//                $pdf->Line($mLeft + $bLeft, $mTop + $bgHeight, $mLeft + $bLeft, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
//                $pdf->Line($mLeft + $bgWidth - $bRight, 0, $mLeft + $bgWidth - $bRight, $mTop, array('color' => array(0,0,0), 'width' => 0.05));
//                $pdf->Line($mLeft + $bgWidth - $bRight, $mTop + $bgHeight, $mLeft + $bgWidth - $bRight, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
                
                $pdf->Line(0, $mTop + $bTop, $mLeft + $bLeft, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line(0, $mTop + $bgHeight - $bBottom, $mLeft + $bLeft, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($bgWidth + $mLeft - $bRight, $mTop + $bTop, $bgWidth + $mLeft + $mRight, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($bgWidth + $mLeft - $bRight, $mTop + $bgHeight - $bBottom, $bgWidth + $mLeft + $mRight, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($mLeft + $bLeft, 0, $mLeft + $bLeft, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($mLeft + $bLeft, $mTop + $bgHeight - $bBottom, $mLeft + $bLeft, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($mLeft + $bgWidth - $bRight, 0, $mLeft + $bgWidth - $bRight, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                $pdf->Line($mLeft + $bgWidth - $bRight, $mTop + $bgHeight - $bBottom, $mLeft + $bgWidth - $bRight, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
            }
            if( $_pdf['show_overlay'] == 1 && $_pdf['include_overlay'] == 1 && $layout != 'c' ){
                $overlay = get_attached_file( $_pdf['img_overlay'] );
                if( $overlay ){
                    $check_over_img = Nbdesigner_IO::checkFileType(basename($overlay), $img_ext);
                    if( $check_over_img ){
                        $pdf->Image($overlay, $mLeft + $cdLeft, $mTop + $cdTop, $cdWidth,$cdHeight, '', '', '', false, '');
                    }
                }
            }
            if(!$force){
                $folder = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key. '/pdfs';
                if(!file_exists($folder)){
                    wp_mkdir_p($folder);
                }
                $output_file = $folder .'/' .$_pdf['name']. $key .'.pdf';
                $pdf->Output($output_file, 'F');              
                $result[] = array(
                    'link' => Nbdesigner_IO::wp_convert_path_to_url($output_file),
                    'title' => $_pdf['name']
                );
            }
        }
        if($force){
            $folder = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key. '/pdfs';
            if(!file_exists($folder)){
                wp_mkdir_p($folder);
            }
            $output_file = $folder .'/'. $order_id .'_all' .'.pdf';
            $pdf->Output($output_file, 'F');              
            $result[] = array(
                'link' => Nbdesigner_IO::wp_convert_path_to_url($output_file),
                'title' => $order_id
            );                
        }        
        echo json_encode($result);
        wp_die();
    }

    public function nbd_get_truetype_fonts(){
        $true_type = array('Bruum FY', 'CitadelScriptStd', 'DIN Next LT Pro Light', 'DIN Next LT Pro Medium', 'DIN Next LT Pro Regular', 'Gudea', 'Abel', 'Abril Fatface', 'Acme', 'Advent Pro', 'Aguafina Script', 'Aladin', 'Allura', 'Almendra', 'Almendra Display', 'Almendra SC', 'Amiri', 'Antic', 'Antic Didone', 'Anonymous Pro', 'Antic Slab', 'Arbutus', 'Architects Daughter', 'Aref Ruqaa', 'Arizonia', 'Asset', 'Asul', 'Average', 'Average Sans', 'Averia Gruesa Libre', 'Averia Libre', 'Averia Sans Libre', 'Averia Serif Libre', 'Bad Script', 'Balthazar', 'Belgrano', 'Bilbo', 'Bilbo Swash', 'Boogaloo', 'Bowlby One', 'Bree Serif', 'Bubblegum Sans', 'Bubbler One', 'Buenard', 'Butcherman', 'Cagliostro', 'Cambo', 'Cantarell', 'Cardo', 'Caudex', 'Ceviche One', 'Changa One', 'Chango', 'Chau Philomene One', 'Chela One', 'Cherry Swash', 'Chicle', 'Cinzel', 'Cinzel Decorative', 'Coiny', 'Condiment', 'Contrail One', 'Convergence', 'Cookie', 'Corben', 'Covered By Your Grace', 'Creepster', 'Crete Round', 'Croissant One', 'Damion', 'Dawning of a New Day', 'Days One', 'Delius', 'Delius Swash Caps', 'Delius Unicase', 'Della Respira', 'Devonshire', 'Diplomata', 'Diplomata SC', 'Dorsa', 'Dr Sugiyama', 'Economica', 'Enriqueta', 'Erica One', 'Esteban', 'Euphoria Script', 'Ewert', 'Exo', 'Fanwood Text', 'Farsan', 'Faster One', 'Fauna One', 'Fenix', 'Felipa', 'Fjord One', 'Flamenco', 'Fredericka the Great', 'Fredoka One', 'Fresca', 'Fugaz One', 'Gafata', 'Galdeano', 'Geostar', 'Geostar Fill', 'Germania One', 'Glass Antiqua', 'Goblin One', 'Graduate', 'Gravitas One', 'Great Vibes', 'Handlee', 'Harmattan', 'Herr Von Muellerhoff', 'Holtwood One SC', 'IM Fell DW Pica', 'IM Fell DW Pica SC', 'IM Fell Double Pica', 'IM Fell Double Pica SC', 'IM Fell English', 'IM Fell English SC', 'IM Fell French Canon', 'IM Fell French Canon SC', 'IM Fell Great Primer', 'IM Fell Great Primer SC', 'Imprima', 'Inika', 'Italiana', 'Italianno', 'Jockey One', 'omhuria', 'Joti One', 'Jomhuria', 'Julee', 'Just Me Again Down Here', 'Katibeh', 'Kavivanar', 'Keania One', 'Kelly Slab', 'Kite One', 'Knewave', 'Kotta One', 'Kreon', 'Krona One', 'Leckerli One', 'Ledger', 'Lekton', 'Lemon', 'Lilita One', 'Lily Script One', 'Linden Hill', 'Love Ya Like A Sister ', 'Lovers Quarrel', 'Lusitana', 'Lustria', 'Macondo', 'Macondo Swash Caps', 'Magra', 'Marck Script', 'Marko One', 'Marvel', 'Mate', 'Mate SC', 'Medula One', 'Meera Inimai', 'Merienda', 'Merienda One', 'Mina', 'Mirza', 'Miss Fajardose', 'Modern Antiqua', 'Monofett', 'Monoton', 'Monsieur La Doulaise', 'Montaga', 'Montserrat', 'Montserrat Subrayada', 'Mountains of Christmas', 'Mr Bedfort', 'Mr Dafoe', 'Mr De Haviland', 'Mrs Saint Delafield', 'Mrs Sheppards', 'Niconne', 'Nixie One', 'Nobile', 'Norican', 'Nosifer', 'Offside', 'Oldenburg', 'Oleo Script', 'Oleo Script Swash Caps', 'Orbitron', 'Overlock', 'Overlock SC', 'Ovo', 'Paprika', 'Passero One', 'Passion One', 'Pathway Gothic One', 'Piedra', 'Pinyon Script', 'Pirata One', 'Playball', 'Poiret One', 'Poller One', 'Poly', 'Pompiere', 'Poppins', 'Port Lligat Sans', 'Port Lligat Slab', 'Preahvihear', 'Qwigley', 'Rambla', 'Ranga', 'Reem Kufi', 'Rammetto One', 'Ribeye Marrow', 'Righteous', 'Rochester', 'Rosarivo', 'Rouge Script', 'Ruda', 'Rufina', 'Ruge Boogie', 'Ruluko', 'Ruslan Display', 'Russo One', 'Ruthie', 'Sail A', 'Salsa', 'Sanchez', 'Sancreek', 'Sarina', 'Shadows Into Light Two', 'Short Stack', 'Signika Negative', 'Sintony', 'Smokum', 'Snippet', 'Sofia', 'Sonsie One', 'Sorts Mill Goudy', 'Spirax', 'Squada One', 'Strait', 'Sunflower', 'Swanky and Moo Moo', 'Text Me One', 'Tinyhust', 'The Girl Next Door', 'Titan One', 'Trochut', 'Trykker', 'Tulpen One', 'Unica One', 'Unlock', 'Vast Shadow', 'Viga', 'Voltaire', 'Wellfleet', 'Wendy One', 'Zeyada', 'Yellowtail');
        $true_type_setting = nbdesigner_get_option('nbdesigner_truetype_fonts');
        if( $true_type_setting != '' ){
            $true_type_setting = preg_split('/\r\n|[\r\n]/', $true_type_setting);
        }else{
            $true_type_setting = array();
        }
        return array_merge($true_type, $true_type_setting);
    }

    /**
     * @deprecated 1.9.0
     * @since 1.8.0
     */    
    public function nbd_frontend_download_pdf( $final = false, $nbd_item_key = '', $return = false ){ 
        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        if( $order_id ) {
            $order = wc_get_order($order_id);
            if( $order->get_status() == 'completed' ) $final = true;
        }
        $nbd_item_key = $nbd_item_key == '' ? $_POST['nbd_item_key'] : $nbd_item_key;
        $folder = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key. '/customer-pdfs';
        $output_file = $final ? $folder .'/'. $nbd_item_key .'_final.pdf' : $folder .'/'. $nbd_item_key .'.pdf';
        if( 1 /*!file_exists($output_file)*/ ){
            require_once(NBDESIGNER_PLUGIN_DIR.'includes/tcpdf/tcpdf.php');
            $path = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key;
            $datas = unserialize(file_get_contents($path .'/product.json'));
            $option = unserialize(file_get_contents($path .'/option.json'));
            $config = json_decode(file_get_contents($path .'/config.json'));
            $layout = isset( $option['layout'] ) ? $option['layout'] : 'm';
            if( count( $config->product ) ){
                $product_config = array();
                foreach($config->product as $key => $side){
                    $product_config[] = (array)$side;
                }
                $datas = $product_config;
            };
            $used_font_path = $path. '/used_font.json';
            $used_font = json_decode( file_get_contents($used_font_path) );             
            $path_font = array();
            foreach( $used_font as $font ){
                $font_name = $font->name;
                if( $font->type == 'google' ){
                    $path_font = nbd_download_google_font($font_name);;
                }else{
                    $has_custom_font = true;
                    $_font = nbd_get_font_by_alias($font->alias);
                    foreach( $_font->file as $key => $font_file ){
                        $path_font[$key] = NBDESIGNER_FONT_DIR . '/' . $font_file;                       
                    }
                }
                $true_type = $this->nbd_get_truetype_fonts();
                if (in_array($font_name, $true_type)) {
                    foreach($path_font as $pfont){
                        $fontname = TCPDF_FONTS::addTTFfont($pfont, 'TrueType', '', 32);
                    }          
                }else{
                    foreach($path_font as $pfont){
                        $fontname = TCPDF_FONTS::addTTFfont($pfont, '', '', 32);
                    }       
                }
            }            
            $dpi = $option['dpi'];
            $pdfs = array();
            $unit = get_option('nbdesigner_dimensions_unit');
            if(!$unit) $unit = "cm";   
            $unitRatio = 10;
            $cm2Px = 37.795275591;
            $mm2Px = $cm2Px / 10;
            switch ($unit) {
                case 'mm':
                    $unitRatio = 1;
                    break;
                case 'in':
                    $unitRatio = 25.4;
                    break;
                default:
                    $unitRatio = 10;
                    break;
            }   
            $list_images = Nbdesigner_IO::get_list_images($path, 1);
            foreach ($list_images as $img){
                $name = basename($img);
                $arr = explode('.', $name);
                $_frame = explode('_', $arr[0]);
                $frame = $_frame[1];
                $list_design[$frame] = $img;
            }
            foreach($datas as $key => $data){
                $contentImage = '';
                if(isset($list_design[$key])) $contentImage = $list_design[$key];                
                $proWidth = $data['product_width'];
                $proHeight = $data['product_height'];
                $bgTop = 0;
                $bgLeft = 0;
                $includeBg = isset($data['include_background']) ? $data['include_background'] : 1;
                if($includeBg == 0){
                    $proWidth = $data['real_width'];
                    $proHeight = $data['real_height'];
                    $data['real_top'] = 0;
                    $data['real_left'] = 0;
                }
                if($proWidth > $proHeight){
                    $bgRatio = 500 / $proWidth;
                    $bgWidth = 500;
                    $bgHeight = round($proHeight * $bgRatio);
                    $offsetLeft = 0;
                    $offsetTop = round((500 - $bgHeight) / 2);  
                    $scale = round(500 / ($unitRatio * $proWidth * $mm2Px), 2);
                }else{
                    $bgRatio = 500 / $proHeight;
                    $bgHeight = 500;
                    $bgWidth = round($proWidth * $bgRatio);
                    $offsetTop = 0;
                    $scale = round(500 / ($unitRatio * $proHeight * $mm2Px), 2);    
                }

                $pdfs[$key]['background'] = $data['img_src'];
                $pdfs[$key]['bg_type'] = $data['bg_type'];
                $pdfs[$key]['bg_color_value'] = $data['bg_color_value'];
                $pdfs[$key]['bg-top'] = $bgTop;
                $pdfs[$key]['bg-left'] = $bgLeft;
                $pdfs[$key]['bg-height'] = $bgHeight;
                $pdfs[$key]['bg-width'] = $bgWidth;
                $pdfs[$key]['cd-top'] = $data['real_top'] * $unitRatio;
                $pdfs[$key]['cd-left'] = $data['real_left'] * $unitRatio;
                $pdfs[$key]['cd-width'] = $data['real_width'] * $unitRatio;
                $pdfs[$key]['cd-height'] = $data['real_height'] * $unitRatio;
                $pdfs[$key]['customer-design'] = $contentImage;
                $pdfs[$key]['product-width'] = round($proWidth * $unitRatio, 2);
                $pdfs[$key]['product-height'] = round($proHeight * $unitRatio, 2);
                $pdfs[$key]['margin-top'] = $pdfs[$key]['margin-right'] = $pdfs[$key]['margin-bottom'] = $pdfs[$key]['margin-left'] = 0;
                $pdfs[$key]['bleed-top'] = $pdfs[$key]['bleed-bottom'] = $unitRatio * $data['bleed_top_bottom'];      
                $pdfs[$key]['bleed-left'] = $pdfs[$key]['bleed-right'] = $unitRatio * $data['bleed_left_right'];      
            }
            $mTop = $pdfs[0]["margin-top"];
            $mBottom = $pdfs[0]["margin-bottom"];
            $mLeft = $pdfs[0]["margin-left"];
            $mRight = $pdfs[0]["margin-right"];  
            $bgWidth = $pdfs[0]['product-width'];        
            $bgHeight = $pdfs[0]['product-height'];             
            $pWidth = $bgWidth + $mLeft + $mRight;
            $pHeight = $bgHeight + $mTop + $mBottom;
            $pdf_format = array($pWidth, $pHeight);
            if($pWidth > $pHeight){
                $orientation = "L";
            }else {
                $orientation = "P";
            }
            $pdf = new TCPDF($orientation, 'mm', $pdf_format, true, 'UTF-8', false);
            $pdf->SetMargins($mLeft, $mTop, $mRight, true);     
            $pdf->SetCreator( get_site_url() );
            $pdf->SetTitle(get_bloginfo( 'name' ));
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);       
            $pdf->SetAutoPageBreak(TRUE, 0);   

            foreach($pdfs as $key => $_pdf){         
                $customer_design = $_pdf['customer-design'];    
                $bTop = (float)$_pdf['bleed-top'];
                $bLeft = (float)$_pdf['bleed-left'];
                $bRight = (float)$_pdf['bleed-right'];
                $bBottom = (float)$_pdf['bleed-bottom'];    
                $showBleed = 'no'; 
                $cdTop = (float)$_pdf["cd-top"];
                $cdLeft = (float)$_pdf["cd-left"];
                $cdWidth = (float)$_pdf["cd-width"];
                $cdHeight = (float)$_pdf["cd-height"];               
                $background = $_pdf['background'];     
                $bg_type = $_pdf['bg_type'];       
                $bg_color_value = $_pdf['bg_color_value'];  
                if($bg_type == 'image'){
                    $path_bg = (absint($background) > 0 ) ? get_attached_file($background) : Nbdesigner_IO::convert_url_to_path( $background );
                }  
                
                $mTop = $_pdf["margin-top"];
                $mBottom = $_pdf["margin-bottom"];
                $mLeft = $_pdf["margin-left"];
                $mRight = $_pdf["margin-right"];  
                $bgWidth = $_pdf['product-width'];        
                $bgHeight = $_pdf['product-height'];             
                $pWidth = $bgWidth + $mLeft + $mRight;
                $pHeight = $bgHeight + $mTop + $mBottom;
                $pdf_format = array($pWidth, $pHeight);
                if($pWidth > $pHeight){
                    $orientation = "L";
                }else {
                    $orientation = "P";
                }
                
                $pdf->AddPage($orientation, $pdf_format);             
                if( $bg_type == 'image' && $path_bg && $includeBg == 1 ){
                    $img_ext = array('jpg','jpeg','png');
                    $svg_ext = array('svg');
                    $eps_ext = array('eps','ai');
                    $check_img = Nbdesigner_IO::checkFileType(basename($path_bg), $img_ext);
                    $check_svg = Nbdesigner_IO::checkFileType(basename($path_bg), $svg_ext);
                    $check_eps = Nbdesigner_IO::checkFileType(basename($path_bg), $eps_ext);
                    $ext = pathinfo($path_bg);
                    if($check_img){
                        $pdf->Image($path_bg,$mLeft, $mTop, $bgWidth, $bgHeight, '', '', '', false, '');
                    }
                    if($check_svg){
                        $pdf->ImageSVG($path_bg, $mLeft,$mTop, $bgWidth, $bgHeight, '', '', '', 0, true);
                    }     
                    if($check_eps){
                       $pdf->ImageEps($path_bg, $mLeft,$mTop, $bgWidth, $bgHeight, '', true, '', '', 0, true);
                    }                 
                }elseif($bg_type == 'color') {
                    $pdf->Rect($mLeft, $mTop,  $bgWidth, $bgHeight, 'F', '', hex_code_to_rgb($bg_color_value));
                }
                if($showBleed == 'yes' && nbdesigner_get_option('nbdesigner_bleed_stack') == 1){
                    $pdf->Line(0, $mTop + $bTop, $mLeft + $bLeft, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line(0, $mTop + $bgHeight - $bBottom, $mLeft + $bLeft, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($bgWidth + $mLeft - $bRight, $mTop + $bTop, $bgWidth + $mLeft + $mRight, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($bgWidth + $mLeft - $bRight, $mTop + $bgHeight - $bBottom, $bgWidth + $mLeft + $mRight, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($mLeft + $bLeft, 0, $mLeft + $bLeft, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($mLeft + $bLeft, $mTop + $bgHeight - $bBottom, $mLeft + $bLeft, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($mLeft + $bgWidth - $bRight, 0, $mLeft + $bgWidth - $bRight, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($mLeft + $bgWidth - $bRight, $mTop + $bgHeight - $bBottom, $mLeft + $bgWidth - $bRight, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
                }                
                if($customer_design != ''){
                    $svg = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key. '/frame_'. $key .'_svg.svg';
                    $pdf->ImageSVG($svg, $mLeft + $cdLeft, $mTop + $cdTop, $cdWidth,$cdHeight, '', '', '', 0, true);                     
                }
                if($showBleed == 'yes' && nbdesigner_get_option('nbdesigner_bleed_stack') == 2){
//                    $pdf->Line(0, $mTop + $bTop, $mLeft, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
//                    $pdf->Line(0, $mTop + $bgHeight - $bBottom, $mLeft, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
//                    $pdf->Line($bgWidth + $mLeft, $mTop + $bTop, $bgWidth + $mLeft + $mRight, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
//                    $pdf->Line($bgWidth + $mLeft, $mTop + $bgHeight - $bBottom, $bgWidth + $mLeft + $mRight, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
//                    $pdf->Line($mLeft + $bLeft, 0, $mLeft + $bLeft, $mTop, array('color' => array(0,0,0), 'width' => 0.05));
//                    $pdf->Line($mLeft + $bLeft, $mTop + $bgHeight, $mLeft + $bLeft, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
//                    $pdf->Line($mLeft + $bgWidth - $bRight, 0, $mLeft + $bgWidth - $bRight, $mTop, array('color' => array(0,0,0), 'width' => 0.05));
//                    $pdf->Line($mLeft + $bgWidth - $bRight, $mTop + $bgHeight, $mLeft + $bgWidth - $bRight, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
                    
                    $pdf->Line(0, $mTop + $bTop, $mLeft + $bLeft, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line(0, $mTop + $bgHeight - $bBottom, $mLeft + $bLeft, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($bgWidth + $mLeft - $bRight, $mTop + $bTop, $bgWidth + $mLeft + $mRight, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($bgWidth + $mLeft - $bRight, $mTop + $bgHeight - $bBottom, $bgWidth + $mLeft + $mRight, $mTop + $bgHeight - $bBottom, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($mLeft + $bLeft, 0, $mLeft + $bLeft, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($mLeft + $bLeft, $mTop + $bgHeight - $bBottom, $mLeft + $bLeft, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($mLeft + $bgWidth - $bRight, 0, $mLeft + $bgWidth - $bRight, $mTop + $bTop, array('color' => array(0,0,0), 'width' => 0.05));
                    $pdf->Line($mLeft + $bgWidth - $bRight, $mTop + $bgHeight - $bBottom, $mLeft + $bgWidth - $bRight, $mTop + $bgHeight + $mBottom, array('color' => array(0,0,0), 'width' => 0.05));
                }
                if( $datas[$key]['show_overlay'] == 1 && $datas[$key]['include_overlay'] == 1 && $layout != 'c' ){
                    $overlay = Nbdesigner_IO::convert_url_to_path( $datas[$key]['img_overlay'] );
                    $check_over_img = Nbdesigner_IO::checkFileType(basename($overlay), array('jpg','jpeg','png'));
                    if( $check_over_img ){
                        $pdf->Image($overlay, $mLeft + $cdLeft, $mTop + $cdTop, $cdWidth,$cdHeight, '', '', '', false, '');
                    }
                }
                $enable_watermark = nbdesigner_get_option('nbdesigner_enable_pdf_watermark');
                if( ($enable_watermark == 'yes' || $enable_watermark == 'before')  && !$final ){
                    $watermark_type = nbdesigner_get_option('nbdesigner_pdf_watermark_type');
                    if($watermark_type == 1){
                        $watermark_image = nbdesigner_get_option('nbdesigner_pdf_watermark_image');
                        $watermark_file = get_attached_file($watermark_image);
                        if( $watermark_file ){
                            $myPageWidth = $pdf->getPageWidth();
                            $myPageHeight = $pdf->getPageHeight();                        
                            list($watermark_width, $watermark_height) = getimagesize($watermark_file);
                            $watermark_width = $watermark_width/72*25.4;
                            $watermark_height = $watermark_height/72*25.4;
                            $myX = ( $myPageWidth - $watermark_width )/2 > 0 ? ( $myPageWidth - $watermark_width )/2 : 0;
                            $myY = ( $myPageHeight - $watermark_height )/2 > 0 ? ( $myPageHeight - $watermark_height )/2 : 0;
                            $pdf->SetAlpha(0.2);
                            $pdf->StartTransform();
                            $pdf->Rotate(45, $myPageWidth / 2, $myPageHeight / 2);
                            $pdf->Image($watermark_file, $myX, $myY, '', '', '', '', '', false);
                            $pdf->StopTransform();
                            $pdf->SetAlpha(1);
                        }
                    }else{
                        $watermark_text = nbdesigner_get_option('nbdesigner_pdf_watermark_text');
                        $vfont = "helvetica";
                        $vfontsize = 20;
                        $vfontbold = "B";         
                        $widthtext = $pdf->GetStringWidth(trim($watermark_text), $vfont, $vfontbold, $vfontsize, false );
                        $widthtextcenter = round(($widthtext * sin(deg2rad(45))) / 2 ,0);
                        $myPageWidth = $pdf->getPageWidth();
                        $myPageHeight = $pdf->getPageHeight();
                        $myX = ( $myPageWidth - $widthtext )/2 > 0 ? ( $myPageWidth - $widthtext )/2 : 0;
                        $myY = $myPageHeight / 2;                
                        $pdf->SetAlpha(0.2);
                        $pdf->StartTransform();
                        $pdf->Rotate(45, $myPageWidth / 2, $myPageHeight / 2);
                        $pdf->SetFont($vfont, $vfontbold, $vfontsize);
                        $pdf->Text($myX, $myY ,trim($watermark_text));
                        $pdf->StopTransform();
                        $pdf->SetAlpha(1);
                    }
                }
            }
            if(!file_exists($folder)){
                wp_mkdir_p($folder);
            }
            $pdf->Output($output_file, 'F');  
        }
        $result[] = array(
            'link' => Nbdesigner_IO::wp_convert_path_to_url($output_file),
            'title' => $nbd_item_key,
            'flag'  =>  1
        );   
        if( $return ){
            return $result;
        }else{
            echo json_encode($result);
            wp_die();             
        }  
    }
    public function nbd_frontend_download_jpeg(){
        $result[] = array(
            0 =>   array(
                'link' => '',
                'flag' => 0
            )
        );
        $nbd_item_key = $_POST['nbd_item_key'];
        if( $nbd_item_key == '' ){
            echo json_encode($result);
            wp_die();
        }
        $this->nbd_frontend_download_pdf( false, $nbd_item_key, true );
        $pdf_path = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key . '/customer-pdfs';
        $config = nbd_get_data_from_json(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key . '/config.json');
        $pdf = $pdf_path .'/'. $nbd_item_key .'.pdf';
        $document = new Imagick($pdf);
        $number_pages = $document->getNumberImages();
        for( $i = 0; $i < $number_pages; $i++ ){
            $im = new Imagick();
            $im->setResolution($config->dpi, $config->dpi);
            $im->readImage( $pdf . '['. $i .']' );           
            $im->setImageFormat('jpg');
            $im->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH);
            $im->setResolution($config->dpi, $config->dpi);
            $im->setImageBackgroundColor('white');
            $im->setImageAlphaChannel(11);
            $profiles = $im->getImageProfiles('*', false); 
            $has_icc_profile = (array_search('icc', $profiles) !== false);
            if ($has_icc_profile === false) {
                $icc_rgb = file_get_contents( NBDESIGNER_PLUGIN_DIR . 'data/icc/RGB/sRGB.icc' ); 
                $im->profileImage('icc', $icc_rgb);
                unset($icc_rgb); 
            }
            $list_icc = nbd_get_icc_cmyk_list_file();
            $default_icc = nbdesigner_get_option('nbdesigner_default_icc_profile');
            if( $list_icc[$default_icc] != '' ){
                $icc = NBDESIGNER_PLUGIN_DIR . 'data/icc/CMYK/' . $list_icc[$default_icc];
                if( file_exists($icc) ){
                    $icc_profile = file_get_contents( $icc );
                    $im->profileImage('icc', $icc_profile);
                    unset($icc_profile);
                }
            }
            // this will drop down the size of the image dramatically (removes all profiles) 
            //$im->stripImage();
            $im->writeImage(str_replace(".pdf", "_". $i .".jpg", $pdf));
            $im->clear();
            $im->destroy();
        }
        $document->clear();
        $document->destroy();
        $files = Nbdesigner_IO::get_list_files_by_type( $pdf_path, 1, 'jpg|jpeg' );
        $pathZip = NBDESIGNER_DATA_DIR.'/download/customer-design-'.$nbd_item_key.'-'.time().'.zip';
        nbd_zip_files_and_download($files, $pathZip, 'customer-design.zip', array(), false); 
        $result[0]['link'] = NBDESIGNER_DATA_URL.'/download/customer-design-'.$nbd_item_key.'-'.time().'.zip';
        $result[0]['flag'] = 1;
        echo json_encode($result);
        wp_die();
    }
    /**
     * @deprecated 1.9.0
     * @since 1.8.0
     */
    public function nbd_download_final_pdf(){   
        $nbd_item_key = $_POST['nbd_item_key'];
        $folder = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key .'/pdfs';
        $result = array(
             'flag'  =>  1
        );        
        if( file_exists($folder) ) {
            $list_file = Nbdesigner_IO::get_list_files($folder);
            $list = preg_grep('/\.(pdf)(?:[\?\#].*)?$/i', $list_file);
            foreach ( $list as $pdf ){
                $result['pdf'][] = Nbdesigner_IO::wp_convert_path_to_url($pdf);
            }            
        }else{
            $pdfs = $this->nbd_frontend_download_pdf( true, $nbd_item_key, true );
            foreach ($pdfs as $pdf){
                $result['pdf'][] = $pdf['link'];
            }
        }
        echo json_encode($result);
        wp_die();          
    }
    public function convert_svg_embed( $path ){
        $svgs = Nbdesigner_IO::get_list_files_by_type($path, 1, 'svg');
        $svg_path = $path . '/svg';
        if( !file_exists($svg_path) ) wp_mkdir_p($svg_path);
        foreach ( $svgs as $svg ){
            $svg_name = pathinfo($svg, PATHINFO_BASENAME);
            $new_svg_path = $svg_path.'/'.$svg_name;
            $xdoc = new DomDocument;
            $xdoc->Load($svg);
            /* Embed images */
            $images = $xdoc->getElementsByTagName('image');
            for ($i = 0; $i < $images->length; $i++) {
                $tagName = $xdoc->getElementsByTagName('image')->item($i);
                $attribNode = $tagName->getAttributeNode('xlink:href');
                $img_src = $attribNode->value;
                if(strpos($img_src, "data:image")!==FALSE)
                continue;
                $type = pathinfo($img_src, PATHINFO_EXTENSION);
                $type = ($type =='svg' ) ? 'svg+xml' : $type;
                $path_image = Nbdesigner_IO::convert_url_to_path($img_src);
                $data = nbd_file_get_contents($path_image);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $tagName->setAttribute('xlink:href', $base64);
            }
            /* Embed fonts */
            $text_elements = $xdoc->getElementsByTagName('text');
            for ($i = 0; $i < $text_elements->length; $i++) {
                $tagName = $xdoc->getElementsByTagName('text')->item($i);
                $attribNode = $tagName->getAttributeNode('font-family');
                $font_family = $attribNode->value;
                $font = nbd_get_font_by_alias($font_family);              
                if( $font ){
                    $tagName->setAttribute('font-family', $font->name);
                }
            }
            $new_svg = $xdoc->saveXML();
            file_put_contents($new_svg_path, $new_svg);            
        }
    }
    public function convert_svg_url($path, $file){
        $svg_path = $path . '/svgpath';
        if( !file_exists($svg_path) ) wp_mkdir_p($svg_path);
        $new_svg_path = $svg_path.'/'.$file;
        $xdoc = new DomDocument;
        $xdoc->Load($path.$file);
        $images = $xdoc->getElementsByTagName('image');
        for ($i = 0; $i < $images->length; $i++) {
            $tagName = $xdoc->getElementsByTagName('image')->item($i);
            $attribNode = $tagName->getAttributeNode('xlink:href');
            $img_src = $attribNode->value;
            if(strpos($img_src, "data:image")!==FALSE)
            continue;
            $type = pathinfo($img_src, PATHINFO_EXTENSION);
            $type = ($type =='svg' ) ? 'svg+xml' : $type;
            $path_image = Nbdesigner_IO::convert_url_to_path($img_src);
            $tagName->setAttribute('xlink:href', $path_image);
        }
        $new_svg = $xdoc->saveXML();
        file_put_contents($new_svg_path, $new_svg); 
    }
    public function nbd_convert_files(){
        if (!wp_verify_nonce($_POST['_wpnonce'], 'nbd_jpg_nonce') && !wp_verify_nonce($_POST['_wpnonce'], 'nbd_cmyk_nonce')) {
            die('Security error');
        }   
        $type = $_POST['type'];
        $nbd_item = $_POST['nbd_item'];
        $result = array();
        $path = NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item;
        $path_jpg = $path . '/jpg/';
        if( $type == 'jpg'){
            $dpi = $_POST['jpg_dpi'];
            $path_png = $path . '/png/';
            if( !file_exists($path_png) ){
                Nbdesigner_IO::mkdir($path_png);            
            }else{
                Nbdesigner_IO::delete_folder($path_png);  
                Nbdesigner_IO::mkdir($path_png);     
            }
            if( !file_exists($path_jpg) ){
                Nbdesigner_IO::mkdir($path_jpg);            
            }else{
                Nbdesigner_IO::delete_folder($path_jpg);   
                Nbdesigner_IO::mkdir($path_jpg);  
            }     
            $list =  Nbdesigner_IO::get_list_images($path, 1);
            foreach ($list as $image){
                $image_name = pathinfo($image, PATHINFO_FILENAME);
                $png_with_white_bg = $path_png . $image_name .'.png';  
                $jpg = $path_jpg . $image_name .'.jpg';
                NBD_Image::imagick_add_white_bg($image, $png_with_white_bg);
                NBD_Image::imagick_convert_png2jpg_without_bg($png_with_white_bg, $jpg);
                NBD_Image::imagick_resample($jpg, $jpg, $dpi);                  
            }  
        }else if( $type == 'cmyk'){
            $path_cmyk = $path . '/cmyk/';
            if( !file_exists($path_cmyk) ){
                Nbdesigner_IO::mkdir($path_cmyk);            
            }else{
                Nbdesigner_IO::delete_folder($path_cmyk);   
                Nbdesigner_IO::mkdir($path_cmyk);  
            }             
            $icc_index = $_POST['icc'];
            $list_icc = nbd_get_icc_cmyk_list_file();
            $icc = $list_icc[$icc_index];
            $icc_file = NBDESIGNER_PLUGIN_DIR . 'data/icc/CMYK/' . $icc;
            $list =  Nbdesigner_IO::get_list_images($path_jpg, 1);
            foreach ($list as $image){
                $image_name = pathinfo($image, PATHINFO_FILENAME);
                $cmyk = $path_cmyk . $image_name .'.jpg';
                NBD_Image::imagick_convert_rgb_to_cymk($image, $cmyk);
                if($icc_index){
                    NBD_Image::imagick_change_icc_profile($cmyk, $cmyk, $icc_file);
                }                
            }
        }
        $result['flag'] = 1;
        echo json_encode( $result );
        wp_die();
    }
    public function add_nbdesinger_order_actions_button($actions, $the_order){
        $id = is_woo_v3() ?  $the_order->get_id() : $the_order->id ;      
        $has_design = get_post_meta($id, '_nbd', true);
        if($has_design && is_admin()){
            $actions['view_nbd'] = array(
                'url'       => admin_url( 'post.php?post='.$id.'&action=edit' ).'#nbdesigner_order',
                'name'      => __( 'Has Design', 'web-to-print-online-designer'),
                'action'    => "has-nbd"
            );            
        } 
        return $actions;
    }
    public function nbdesigner_update_variation_v180(){
        NBD_Update_Data::update_vatiation_config_v180();
    }
    public function nbd_clear_transients(){
        Nbdesigner_Helper::clear_transients();
    }
    public function nbd_create_pages(){
        if (!wp_verify_nonce($_POST['_nbdesigner_update_product'], 'nbd-create-pages') || !current_user_can('administrator')) {
            die('Security error');
        }         
        NBD_Install::create_pages();
        NBD_Install::create_tables();
        NBD_Install::insert_default_files();
        wp_send_json(
            array(
                'flag'  =>  1
            )
        ); 
    }
    // /** bulk action **/
    // public function add_post_class($classes, $class, $post_id){
    //     if (is_admin()) { 
    //         return $classes;
    //     }        
    //     $product_id = get_the_ID();
    //     $product_id = get_wpml_original_id($product_id);
    //     $is_nbdesign = get_post_meta($product_id, '_nbdesigner_enable', true); 
    //     if($is_nbdesign){
    //         $layout = nbdesigner_get_option('nbdesigner_design_layout');
    //         if($layout == 'c'){
    //             $option = unserialize(get_post_meta($product_id, '_nbdesigner_option', true));
    //             $product = wc_get_product($product_id);
    //             $type = $product->get_type();
    //             if( $type == 'variable' && isset($option['bulk_variation']) && $option['bulk_variation'] == 1 ){
    //                 $classes[] = 'nbd_bulk_variation';
    //             }
    //         }
    //     }   
    //     return $classes;
    // }
    // public function bulk_variation_field(){
    //     $product_id = get_the_ID();
    //     $product_id = get_wpml_original_id($product_id);
    //     $is_nbdesign = get_post_meta($product_id, '_nbdesigner_enable', true); 
    //     if(!$is_nbdesign){
    //         return;
    //     } else {
    //         $layout = nbdesigner_get_option('nbdesigner_design_layout');
    //         if( $layout == 'c' ){
    //             $option = unserialize(get_post_meta($product_id, '_nbdesigner_option', true));
    //             $product = wc_get_product($product_id);
    //             $type = $product->get_type();
    //             if( $type == 'variable' && isset($option['bulk_variation']) && $option['bulk_variation'] == 1 ){
    //                 $variations = get_nbd_variations( $product_id, true );
    //                 if( count($variations) ){
    //                     $atts = array(
    //                         'variations'    =>  $variations
    //                     );
    //                     ob_start();
    //                     nbdesigner_get_template('single-product/variation-bulk.php', $atts);
    //                     $bulk_variation_form = ob_get_clean();                      
    //                     echo $bulk_variation_form;
    //                 }
    //             }
    //         }
    //     }       
    // }
    public function process_add_bulk_variation(){
        if ( empty( $_REQUEST['nbd-variation-value'] ) && $_REQUEST['nbd-variation-value'] == '' ) {
            return;
        }
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $_objectManager->create('\Magento\Framework\Session\SessionManager');

        $product_id     = $_POST['product_id']; 
        $added_count  = 0;
        $failed_count = 0;        
        $success_message = '';
        $error_message   = '';        
        $_variations = explode('|', $_REQUEST['nbd-variation-value']);
        $adding_to_cart = wc_get_product( $product_id );
        $added_variations = array();
        $nbd_item_session_key = $product_id . '_0'; 
        $nbd_session = $session->get('nbd_item_key_'.$nbd_item_session_key);
        $nbu_session = $session->get('nbu_item_key_'.$nbd_item_session_key);
        foreach ( $_variations as $_variation ) {
            $var = explode('_', $_variation);
            $variation_id = absint( $var[0] );
            $quantity = absint( $var[1] );
            $variations         = array();
            if( $quantity > 0 && $variation_id > 0) {
                $variation_data = wc_get_product_variation_attributes( $variation_id );
                $attributes     = $adding_to_cart->get_attributes();
                foreach ( $attributes as $attribute ) {
                    if ( ! $attribute['is_variation'] ) {
                        continue;
                    }    
                    $taxonomy = 'attribute_' . sanitize_title( $attribute['name'] );
                    $valid_value = isset( $variation_data[ $taxonomy ] ) ? $variation_data[ $taxonomy ] : '';
                    $variations[ $taxonomy ] = $valid_value;                    
                }
                $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id,$quantity, $variation_id, $variations );
                if ( $passed_validation ) {
                    $nbd_variation_session_key = $product_id . '_' . $variation_id;
                    if(isset($nbd_session)){
                        $session->set('nbd_item_key_'.$nbd_variation_session_key, $nbd_session);                        
                    }
                    if(isset($nbu_session)){
                        $session->set('nbu_item_key_'.$nbd_variation_session_key, $nbu_session);                        
                    }                    
                    $added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );
                }  
                if ( $added ) {
                    $added_count ++;
                    $added_variations[] = $variation_id;
                } else {
                    $failed_count ++;
                }                
            }else{
                $failed_count++;
                continue;
            }
        }
        if(isset($nbd_session)){
            $session->unset('nbd_item_key_'.$nbd_item_session_key);                   
        }    
        if(isset($nbu_session)){
            $session->unset('nbu_item_key_'.$nbd_item_session_key);                   
        }        
        if ( $added_count ) {
            nbd_bulk_variations_add_to_cart_message( $added_count );
        }
        if ( $failed_count ) {
            wc_add_notice( sprintf( __( 'Unable to add %s to the cart.  Please check your quantities and make sure the item is available and in stock', 'web-to-print-online-designer' ), $failed_count ), 'error' );
        }    
        if ( ! $added_count && ! $failed_count ) {
            wc_add_notice( __( 'No product quantities entered.', 'web-to-print-online-designer' ), 'error' );
        }     
        if ( $failed_count === 0 && wc_notice_count( 'error' ) === 0 ) {
            if ( $url = apply_filters( 'woocommerce_add_to_cart_redirect', false ) ) {
                wp_safe_redirect( $url );
                exit;
            } elseif ( get_option( 'woocommerce_cart_redirect_after_add' ) === 'yes' ) {
                wp_safe_redirect( wc_get_cart_url() );
                exit;
            }
        }        
    }
    public function woocommerce_order_item_meta_end($item_id, $item, $order){
        $nbd_item_key = wc_get_order_item_meta($item_id, '_nbd');
        $nbu_item_key = wc_get_order_item_meta($item_id, '_nbu');
        if( $nbd_item_key || $nbu_item_key ){
            $show_design_in_order = nbdesigner_get_option('nbdesigner_show_in_order');
            if(( isset($item["item_meta"]["_nbd"]) || isset($item["item_meta"]["_nbu"]) ) && $show_design_in_order == 'yes'){
                $html = '';
                if( isset($item["item_meta"]["_nbd"]) ){
                    $nbd_item_key = $item["item_meta"]["_nbd"]; 
                    $images = Nbdesigner_IO::get_list_images(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key .'/preview', 1);   
                    $approve_status = unserialize(get_post_meta($item['order_id'], '_nbdesigner_design_file', true));
                    $index = 'nbds_'.$item->get_id();
                    $notice = '';
                    $id = 'nbd' . $item->get_id();
                    $redirect_url = wc_get_endpoint_url( 'view-order', $item['order_id'], wc_get_page_permalink( 'myaccount' ) ) . '#' . $id;
                    $product_id = $item['product_id'];
                    $product_id = get_wpml_original_id( $product_id ); 
                    $layout = nbd_get_product_layout($product_id);
                    $redesign_link = add_query_arg(
                        array(
                            'task'  =>  'edit',
                            'product_id'    =>  $product_id,
                            'view'    =>  $layout,
                            'oid'   =>  $item['order_id'],
                            'item_id'   =>  $id,
                            'design_type'  =>  'edit_order',
                            //'rd'    => urlencode( $redirect_url ),
                            'rd'    => 'order',
                            'nbd_item_key'  =>  $nbd_item_key), 
                        getUrlPageNBD('create')); 
                    if($layout == 'v'){
                        $redesign_link = add_query_arg(
                            array(
                                'nbdv-task' =>  'edit',
                                'task'  =>  'edit',
                                'product_id'    =>  $product_id,
                                'oid'   =>  $item['order_id'],
                                'item_id'   =>  $id,
                                'design_type'  =>  'edit_order',
                                //'rd'    => urlencode( $redirect_url ),
                                'rd'    => 'order',
                                'nbd_item_key'  =>  $nbd_item_key), 
                            get_permalink($product_id) ); 
                    }
                    if($item['variation_id'] > 0){
                        $redesign_link .= '&variation_id=' . $item['variation_id'];
                    }            
                    $html .= '<div id="'.$id.'" class="nbd-order-upload-file">';
                    $html .= '<p>'.__('Custom design', 'web-to-print-online-designer').'</p>';
                    foreach ($images as $img){      
                        $src = Nbdesigner_IO::convert_path_to_url($img) . '?&t=' . round(microtime(true) * 1000);   
                        $html .= '<img style="max-width: 100%; border: 1px solid #ddd; display: inline-block; margin-right: 15px; margin-bottom: 15px; background: #ddd;" src="'.$src.'" />';
                    }
                    if(isset($approve_status[$index]) && $approve_status[$index] == "decline"){
                        $notice = "<small style='color:red;'>". __('(Rejected! Click ', 'web-to-print-online-designer')."<a href='".$redesign_link."' target='_blank'>". __('here ', 'web-to-print-online-designer'). "</a>". __(' to design again', 'web-to-print-online-designer')."!)</small>";
                    }
                    if(isset($approve_status[$index]) && $approve_status[$index] == "accept"){
                        $notice = __('<small> (Approved!)</small>', 'web-to-print-online-designer');
                    }
                    $html .= '<p>'.$notice.'</p>';
                    if(nbdesigner_get_option('allow_customer_redesign_after_order', 'yes') == 'yes'){
                        $show_edit_link = apply_filters('nbd_show_edit_design_link_in_pay_for_order', true, $item, $item_id, $order);
                        if( $show_edit_link ) $html .= '<p><a class="button" href="'. $redesign_link .'">'. __('Edit design', 'web-to-print-online-designer') .'</a></p>';
                    }
                    $html .= '<div>';
                }
                if( isset($item["item_meta"]["_nbu"]) ){
                    $nbu_item_key = $item["item_meta"]["_nbu"]; 
                    $files = Nbdesigner_IO::get_list_files( NBDESIGNER_UPLOAD_DIR .'/'. $nbu_item_key );     
                    $notice = '';
                    $id = 'nbu' . $item->get_id();
                    $approve_status = unserialize(get_post_meta($item['order_id'], '_nbdesigner_upload_file', true));
                    $index = 'nbds_'.$item->get_id();                
                    $redirect_url = wc_get_endpoint_url( 'view-order', $item['order_id'], wc_get_page_permalink( 'myaccount' ) ) . '#' . $id;
                    $product_id = $item['product_id'];
                    $product_id = get_wpml_original_id( $product_id );                 
                    $reup_link = add_query_arg(
                        array(
                            'task'  =>  'reup',
                            'product_id'    =>  $product_id,
                            'design_type'  =>  'edit_order',
                            'oid'   =>  $item['order_id'],
                            'item_id'   =>  $id,
                            //'rd'    => urlencode( $redirect_url ),
                            'rd'    => 'order',
                            'nbu_item_key'  =>  $nbu_item_key), 
                        getUrlPageNBD('create'));  
                    if($item['variation_id'] > 0){
                        $reup_link .= '&variation_id=' . $item['variation_id'];
                    } 
                    $html .= '<div id="'.$id.'" class="nbd-order-upload-file">';
                    $html .= '<p>'.__('Upload file', 'web-to-print-online-designer').'</p>';
                    $create_preview = nbdesigner_get_option('nbdesigner_create_preview_image_file_upload');
                    foreach ($files as $file){      
                        $ext = pathinfo( $file, PATHINFO_EXTENSION );
                        $src = Nbdesigner_IO::get_thumb_file( pathinfo( $file, PATHINFO_EXTENSION ), '');
                        if(  $create_preview == 'yes' && ( $ext == 'png' || $ext == 'jpg' || $ext == 'pdf'  ) ){
                            $dir = pathinfo( $file, PATHINFO_DIRNAME );
                            $filename = pathinfo( $file, PATHINFO_BASENAME );
                            if( file_exists($dir.'_preview/'.$filename) ){
                                $src = Nbdesigner_IO::wp_convert_path_to_url( $dir.'_preview/'.$filename );
                            }else if( $ext == 'pdf' && file_exists($dir.'_preview/'.$filename.'.jpg' ) ){
                                $src = Nbdesigner_IO::wp_convert_path_to_url( $dir.'_preview/'.$filename.'.jpg' );
                            }else{
                                $src = Nbdesigner_IO::get_thumb_file( $ext, '' );
                            }
                        }else {
                            $src = Nbdesigner_IO::get_thumb_file( $ext, '' );
                        }       
                        $html .= '<div class="nbd-order-item-upload"><img style="max-width: 100%;" src="' . $src . '"/><p class="nbd-order-item-upload-name">'. basename($file).'</p></div>';
                    }   
                    if(isset($approve_status[$index]) && $approve_status[$index] == "decline"){
                        $notice = "<small style='color:red;'>". __('(Rejected! Click ', 'web-to-print-online-designer')."<a href='".$reup_link."' target='_blank'>". __('here ', 'web-to-print-online-designer'). "</a>". __(' to design again', 'web-to-print-online-designer')."!)</small>";
                    }
                    if(isset($approve_status[$index]) && $approve_status[$index] == "accept"){
                        $notice = __('<small> (Approved!)</small>', 'web-to-print-online-designer');
                    }
                    $html .= '<p>'.$notice.'</p>';                
                    if(nbdesigner_get_option('allow_customer_redesign_after_order') == 'yes'){
                        $show_edit_link = apply_filters('nbd_show_edit_design_link_in_pay_for_order', true, $item, $item_id, $order);
                        if( $show_edit_link ) $html .= '<br /><a class="button" href="'. $reup_link .'">'. __('Reupload design', 'web-to-print-online-designer') .'</a>';
                    }
                    $html .= '<div>';
                }
                if(  isset($item["item_meta"]["_nbd_extra_price"]) ){
                    $html .= '<p>'. __('Extra price for design','web-to-print-online-designer').' + '.$item["item_meta"]["_nbd_extra_price"].'</p>';
                }
            }
            echo  $html;  
            /* Allow the customer download design an files */
            if( nbdesigner_get_option('allow_customer_download_after_complete_order') == 'yes' ||  nbdesigner_get_option('allow_customer_download_after_complete_order') == 'complete' ){
                $order_status = $order->get_status();
                if( $order_status != 'completed' && nbdesigner_get_option('allow_customer_download_after_complete_order') == 'complete' ){
                    echo '';
                }else{
                    ob_start();
                    nbdesigner_get_template('order/download-section.php', array(
                        'item_id' =>  $item_id,
                        'nbd_item_key'  =>  $nbd_item_key,
                        'nbu_item_key'  =>  $nbu_item_key,
                        'order_id' =>  $order->get_id()
                    ));         
                    echo ob_get_clean();     
                }
            } 
        }
    }
    public function woocommerce_order_details_after_order_table( $order ){
        ob_start();
        nbdesigner_get_template('order/download-pdf-option.php', array());         
        echo ob_get_clean();          
    }
    public function download_order_item_design_files(){
        $order_id = $_GET['order_id'];
        $order_item_id = $_GET['order_item_id'];
        $nbd_item_key = isset($_GET['nbd_item_key']) ? $_GET['nbd_item_key'] : '';
        $nbu_item_key = isset($_GET['nbu_item_key']) ? $_GET['nbu_item_key'] : '';
        $type = isset($_GET['type']) ? $_GET['type'] : 'png';
        $force = ( isset($_GET['multi_file']) && $_GET['multi_file'] == 'no' )  ? true : false;
        $showBleed = ( isset($_GET['bleed']) && $_GET['bleed'] == 'yes' ) ? 'yes' : 'no';
        if( nbd_check_order_permission( $order_id ) ){
            nbd_download_product_designs( $order_id, $order_item_id, $nbd_item_key, $nbu_item_key, $type, $force, $showBleed  );        
        }else{
            wp_redirect(wc_get_endpoint_url( 'view-order', $order_id, wc_get_page_permalink( 'myaccount' ) ));
            exit();
        }
    }   
    public function admin_redirects(){
        if ( get_transient( '_nbd_activation_redirect' ) ) {
            delete_transient('_nbd_activation_redirect');
            if ((!empty($_GET['page']) && in_array($_GET['page'], array('nbd-setup')) ) || is_network_admin()) {
                return;
            }
            wp_safe_redirect(admin_url('index.php?page=nbd-setup'));
            exit;
        }
    }
    public function nbd_get_product_config(){
        echo 'config';
        wp_die();
    }
    public function nbd_crop_image(){
        $url = $_POST['url'];
        $startX = $_POST['startX'];
        $startY = $_POST['startY'];
        $width = $_POST['width'];
        $height = $_POST['height'];
        $path = Nbdesigner_IO::convert_url_to_path($url);
        $path_parts = pathinfo($path);
        $new_path = $path_parts['dirname'].'/'.$path_parts['filename'].'_c'.time().'.'.$path_parts['extension'];
        NBD_Image::crop_image($path, $new_path, $startX, $startY, $width, $height, strtolower($path_parts['extension']));
        if( file_exists($new_path) ){
            @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
            echo json_encode(
                array(
                    'flag'  =>  1,
                    'url'   =>   Nbdesigner_IO::convert_path_to_url($new_path)
                )     
            );
            die();
        }else{
            @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
            echo json_encode(
                array(
                    'flag'  =>  0,
                )     
            );
            die();           
        }
    }
    public function delete_expired_upload_files( $days ){
        $retain_time = $days * 60 * 60 * 24;
        $folders = array_diff(scandir(NBDESIGNER_UPLOAD_DIR), array('.', '..'));
        foreach ($folders as $folder) {
            $path = NBDESIGNER_UPLOAD_DIR.'/'.$folder;
            if (is_dir( $path ) === true) {
                if( (time()-filemtime($path)) > $retain_time ){
                    Nbdesigner_IO::delete_folder( $path );
                }
            }
        }
    }
    public function nbd_get_users_by_country_code(){
        $country_code = $_REQUEST['country_code'];
        $users = nbd_get_users_by_country_code($country_code);
        wp_send_json($users);
    }
}