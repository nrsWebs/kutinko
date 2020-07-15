<?php
$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface');
$currentStore = $storeManager->getStore();

$config = $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Config');
$main_js_url = $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data')->getMainJsUrl() . "/";
$base_url = $currentStore->getBaseUrl();
$baseMediaDir = $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data')->plugin_path_data();
$baseMediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

$rootDir = $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data')->getRootDir();

if (!defined('NBDESIGNER_BASE_URL')) {
    define('NBDESIGNER_BASE_URL', $base_url);
}

if (!defined('NBDESIGNER_ROOT_DIR')) {
    define('NBDESIGNER_ROOT_DIR', $rootDir);
}

if (!defined('NBDESIGNER_PLUGIN_URL')) {
    define('NBDESIGNER_PLUGIN_URL', $main_js_url);
}

if (!defined('NBDESIGNER_DATA_DIR')) {
    define('NBDESIGNER_DATA_DIR', $baseMediaDir);
}

if (!defined('NBDESIGNER_DATA_URL')) {
    define('NBDESIGNER_DATA_URL', $baseMediaUrl . 'nbdesigner');
}

if (!defined('NBDESIGNER_FONT_DIR')) {
    define('NBDESIGNER_FONT_DIR', NBDESIGNER_DATA_DIR . '/fonts');
}
if (!defined('NBDESIGNER_FONT_URL')) {
    define('NBDESIGNER_FONT_URL', NBDESIGNER_DATA_URL . '/fonts');
}
if (!defined('NBDESIGNER_ART_DIR')) {
    define('NBDESIGNER_ART_DIR', NBDESIGNER_DATA_DIR . '/cliparts');
}
if (!defined('NBDESIGNER_ART_URL')) {
    define('NBDESIGNER_ART_URL', NBDESIGNER_DATA_URL . '/cliparts');
}
if (!defined('NBDESIGNER_DOWNLOAD_DIR')) {
    define('NBDESIGNER_DOWNLOAD_DIR', NBDESIGNER_DATA_DIR . '/download');
}
if (!defined('NBDESIGNER_DOWNLOAD_URL')) {
    define('NBDESIGNER_DOWNLOAD_URL', NBDESIGNER_DATA_URL . '/download');
}
if (!defined('NBDESIGNER_TEMP_DIR')) {
    define('NBDESIGNER_TEMP_DIR', NBDESIGNER_DATA_DIR . '/temp');
}
if (!defined('NBDESIGNER_TEMP_URL')) {
    define('NBDESIGNER_TEMP_URL', NBDESIGNER_DATA_URL . '/temp');
}
if (!defined('NBDESIGNER_ADMINDESIGN_DIR')) {
    define('NBDESIGNER_ADMINDESIGN_DIR', NBDESIGNER_DATA_DIR . '/admindesign');
}
if (!defined('NBDESIGNER_ADMINDESIGN_URL')) {
    define('NBDESIGNER_ADMINDESIGN_URL', NBDESIGNER_DATA_URL . '/admindesign');
}
if (!defined('NBDESIGNER_PDF_DIR')) {
    define('NBDESIGNER_PDF_DIR', NBDESIGNER_DATA_DIR . '/pdfs');
}
if (!defined('NBDESIGNER_PDF_URL')) {
    define('NBDESIGNER_PDF_URL', NBDESIGNER_DATA_URL . '/pdfs');
}
if (!defined('NBDESIGNER_CUSTOMER_DIR')) {
    define('NBDESIGNER_CUSTOMER_DIR', NBDESIGNER_DATA_DIR . 'designs');
}
if (!defined('NBDESIGNER_PLUGIN_DIR')) {
    define('NBDESIGNER_PLUGIN_DIR', $baseMediaDir);
}

if (!defined('NBDESIGNER_AUTHOR_SITE')) {
    define('NBDESIGNER_AUTHOR_SITE', 'https://cmsmart.net/');
}
if (!defined('NBDESIGNER_SKU')) {
    define('NBDESIGNER_SKU', 'MG2X20');
}

/* =========== End Define Path  ============= */

function is_rtl()
{
    return false;
}

function serializeCorrector($serializedString)
{
    // at first, check if "fixing" is really needed at all. After that, security checkup.
    if (@unserialize($serializedString) !== true && preg_match('/^[aOs]:/', $serializedString)) {
        $serializedString = preg_replace_callback('/s\:(\d+)\:\"(.*?)\";/s', function ($matches) {
            return 's:' . strlen($matches[2]) . ':"' . $matches[2] . '";';
        }, $serializedString);
    }
    return $serializedString;
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id()
    {
        return session_id();
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in()
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $_objectManager->get('Magento\Customer\Model\Session');
        if (!$customerSession->isLoggedIn()) {
            return 0;
        } else {
            return 1;
        }
    }
}

if (!function_exists('nbdesigner_get_option')) {
    function nbdesigner_get_option($key)
    {
        return nbdesigner_get_default_setting($key);
    }
}

if (!function_exists('get_option')) {
    function get_option($key)
    {
        return nbdesigner_get_default_setting($key);
    }
}

if (!function_exists('nbdesigner_get_all_frontend_setting')) {
    function nbdesigner_get_all_frontend_setting()
    {
        $default = default_frontend_setting();
        foreach ($default as $key => $val) {
            $default[$key] = nbdesigner_get_option($key);
        }
        return $default;
    }
}
if ( ! function_exists( 'is_nbd_design_page' ) ) {
    function is_nbd_design_page(){
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $_objectManager->create('\Magento\Framework\App\RequestInterface');
        return $request->getParam('task') == 'create' || $request->getParam('task') == 'edit';
    }    
}

if (!function_exists('default_frontend_setting')) {

    function default_frontend_setting()
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Config');

        $str_color = "";
        $collection = $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data')->getColorCollection();
        foreach ($collection as $key => $c) {
            $str_color .= $c->getHex() . ":" . $c->getColorName() . ",";
        }

        $str_color = trim($str_color,",");

        $default = array(
            'nbdesigner_fix_domain_changed' => 'no',
            'nbdesigner_enable_text' => $config->EnableAddText() ? "yes" : "no",
            'nbdesigner_text_change_font' => 1,
            'nbdesigner_text_italic' => 1,
            'nbdesigner_text_bold' => 1,
            'nbdesigner_text_underline' => 0,
            'nbdesigner_text_through' => 0,
            'nbdesigner_text_overline' => 0,
            'nbdesigner_text_case' => 1,
            'nbdesigner_text_align_left' => 1,
            'nbdesigner_text_align_right' => 1,
            'nbdesigner_text_align_center' => 1,
            'nbdesigner_text_color' => 1,
            'nbdesigner_text_background' => 1,
            'nbdesigner_text_shadow' => 0,
            'nbdesigner_text_line_height' => 1,
            'nbdesigner_text_spacing' => 1,
            'nbdesigner_text_font_size' => 1,
            'nbdesigner_text_opacity' => 1,
            'nbdesigner_text_outline' => 1,
            'nbdesigner_text_proportion' => 1,
            'nbdesigner_text_rotate' => 1,
            'nbdesigner_default_text' => $config->getDefaultText() ? $config->getDefaultText() : "Add text here",
            'nbdesigner_default_font_subset' => 'all',
            'nbdesigner_enable_curvedtext' => 'yes',
            'nbdesigner_enable_text_free_transform' => 'no',
            'nbdesigner_default_font_sizes' => '6,8,10,12,14,16,18,21,24,28,32,36,42,48,56,64,72,80,88,96,104,120,144,288,576,1152',
            'nbdesigner_force_min_font_size' => 'no',
            'nbdesigner_enable_textpattern' => 'no',
            'nbdesigner_enable_clipart' => $config->EnableClipArt() ? "yes" : "no",
            'nbdesigner_clipart_change_path_color' => 1,           
            'nbdesigner_clipart_rotate' => 1,           
            'nbdesigner_clipart_opacity' => 1,
            'nbdesigner_enable_image' => $config->EnableAddImage() ? "yes" : "no",
            'nbdesigner_maxsize_upload' => $config->getUploadMaxSize(),
            'nbdesigner_minsize_upload' => $config->getUploadMinSize(),
            'nbdesigner_image_unlock_proportion' => 1,           
            'nbdesigner_image_shadow' => 0,           
            'nbdesigner_image_opacity' => 1,           
            'nbdesigner_image_grayscale' => 1,           
            'nbdesigner_image_invert' => 1,           
            'nbdesigner_image_sepia' => 1,           
            'nbdesigner_image_sepia2' => 1,           
            'nbdesigner_image_remove_white' => 1,      
            'nbdesigner_image_transparency' => 1,           
            'nbdesigner_image_tint' => 1,           
            'nbdesigner_image_blend' => 1,           
            'nbdesigner_image_brightness' => 1,           
            'nbdesigner_image_noise' => 1,         
            'nbdesigner_image_pixelate' => 1,         
            'nbdesigner_image_multiply' => 1,     
            'nbdesigner_image_blur' => 1,           
            'nbdesigner_image_sharpen' => 1,         
            'nbdesigner_image_emboss' => 1,         
            'nbdesigner_image_edge_enhance' => 1,          
            'nbdesigner_image_rotate' => 1,          
            'nbdesigner_image_crop' => 1,          
            'nbdesigner_image_shapecrop' => 1,  
            'nbdesigner_facebook_app_id' => $config->getFApiKey(),
            'nbdesigner_instagram_app_id' => '',
            'nbdesigner_dropbox_app_id' => '', 
            'nbdesigner_enable_upload_image' => $config->EnableUploadImage() ? "yes" : "no",
            'nbdesigner_enable_auto_fit_image' => 'no',
            'nbdesigner_upload_designs_php_logged_in' => $config->getLoginRequire() ? "yes" : "no",
            'nbdesigner_upload_multiple_images' => 'no',
            'nbdesigner_enable_image_url' => $config->EnableInsertImageUrl() ? "yes" : "no",
            'nbdesigner_enable_low_resolution_image' => 'no',
            'nbdesigner_enable_image_webcam' => $config->EnableInsertImageWebcame() ? "yes" : "no",
            'nbdesigner_enable_facebook_photo' => $config->EnableInsertImageFacebook() ? "yes" : "no",
            'nbdesigner_enable_instagram_photo' => $config->EnableInsertImageInstagram() ? "yes" : "no",
            'nbdesigner_enable_dropbox_photo' => $config->EnableInsertImageDropbox() ? "yes" : "no",
            'nbdesigner_enable_google_drive' => $config->EnableInsertImageGdriver() ? "yes" : "no",
            'nbdesigner_enable_svg_code' => 'no',
            'nbdesigner_upload_show_term' => $config->EnableInsertImageTerm() ? "yes" : "no",              
            'nbdesigner_max_upload_files_at_once' => 5,
            'nbdesigner_enable_draw' => $config->EnableFreedraw() ? "yes" : "no",
            'nbdesigner_draw_brush' => 1,          
            'nbdesigner_draw_brush_pencil' => 1,          
            'nbdesigner_draw_brush_circle' => 1,          
            'nbdesigner_draw_brush_spray' => 1,          
            'nbdesigner_draw_brush_pattern' => 0,          
            'nbdesigner_draw_brush_hline' => 0,          
            'nbdesigner_draw_brush_vline' => 0,          
            'nbdesigner_draw_brush_square' => 0,          
            'nbdesigner_draw_brush_diamond' => 0,          
            'nbdesigner_draw_brush_texture' => 0,          
            'nbdesigner_draw_shape' => 1, 
            'nbdesigner_draw_shape_rectangle' => 1, 
            'nbdesigner_draw_shape_circle' => 1, 
            'nbdesigner_draw_shape_triangle' => 1, 
            'nbdesigner_draw_shape_line' => 1, 
            'nbdesigner_draw_shape_polygon' => 1, 
            'nbdesigner_draw_shape_hexagon' => 1,
            'nbdesigner_enable_qrcode' => $config->EnableQRCode() ? "yes" : "no",
            'nbdesigner_enable_vcard' => $config->enableVcard() ? "yes" : "no",
            'nbdesigner_hide_element_tab' => 'no',
            'nbdesigner_hide_typo_section' => 'no',
            'nbdesigner_display_product_option' => $config->getDisplayProductOption() ? $config->getDisplayProductOption() : "1",
            'nbdesigner_hide_layer_tab' => 'no',
            'nbdesigner_show_all_template_sides' => 'no',
            'nbdesigner_display_template_mode' => '1',
            'nbdesigner_default_qrcode' => $config->getQRText(),
            'nbdesigner_dimensions_unit' => 'cm',
            'nbdesigner_show_all_color' => $config->getShowAllColor() ? "yes" : "no",
            'nbdesigner_default_color' => $config->getDefaultColor() ? $config->getDefaultColor() : '#cc324b',
            'nbdesigner_hex_names' => $str_color,        
            'nbdesigner_save_latest_design'  => 'yes',
            'nbdesigner_save_for_later'  => 'yes',
            'nbdesigner_share_design'  => 'yes',
            'nbdesigner_cache_uploaded_image'  => 'yes',
            'nbdesigner_upload_file_php_logged_in' => 'no',
            'nbdesigner_auto_add_cart_in_detail_page' => 'no',
            'allow_customer_download_after_complete_order' => 'no',
            'nbdesigner_download_design_png' => 0,
            'nbdesigner_download_design_pdf' => 0,
            'nbdesigner_download_design_svg' => 0,
            'nbdesigner_download_design_jpg' => 0,
            'nbdesigner_download_design_jpg_cmyk' => 0,
            'nbdesigner_download_design_upload_file' => 0,
            'nbdesigner_attach_design_png' => 1,
            'nbdesigner_attach_design_svg' => 0,
            'allow_customer_download_design_in_editor' => $config->enableAllowDownloadDesign() ? 1 : 0,
            'nbdesigner_download_design_in_editor_png' => $config->getPngDownload() ? 1 : 0,
            'nbdesigner_download_design_in_editor_pdf' => $config->getPdfDownload() ? 1 : 0,
            'nbdesigner_download_design_in_editor_jpg' => $config->getJpgDownload() ? 1 : 0,
            'nbdesigner_download_design_in_editor_svg' => $config->getSvgDownload() ? 1 : 0,
            'nbdesigner_pixabay_api_key' => '27347-23fd1708b1c4f768195a5093b',
            'nbdesigner_unsplash_api_key' => '5746b12f75e91c251bddf6f83bd2ad0d658122676e9bd2444e110951f9a04af8',
            'nbdesigner_enable_pixabay' => $config->EnableInsertImagePixabay() ? "yes" : "no",
            'nbdesigner_enable_unsplash' => $config->EnableInsertImageUnsplash() ? "yes" : "no",
            'nbdesigner_show_grid' => 'no',
            'nbdesigner_show_ruler' => 'no',
            'nbdesigner_show_product_dimensions' => 'no',
            'nbdesigner_show_bleed' => 'no',
            'nbdesigner_show_layer_size' => $config->enableShowLayerSize() ? "yes" : "no",
            'nbdesigner_show_warning_oos' => 'no',
            'nbdesigner_show_warning_ilr' => 'no',
            'nbdesigner_show_design_border' => 'no',
            'nbdesigner_hide_print_option_in_editor'    =>  'no',
            'nbdesigner_object_center_scaling'    =>  'no',
            'nbdesigner_disable_auto_load_template'    =>  'no',
            'nbdesigner_lazy_load_template'    =>  'no'
        );
        return $default;
    }
}

function nbd_get_value_from_serialize_data( $str, $key ){
    $arr = array();
    $value = 0;
    parse_str($str, $arr);   
    if( isset($arr[$key]) ) $value = $arr[$key];
    return $value;
}
function nbd_not_empty($value) {
    return $value == '0' || !empty($value);
}
function nbd_default_product_setting(){
    return apply_filters('nbdesigner_default_product_setting', array(
        'orientation_name' => 'Side 1',
        'img_src' => get_option('nbdesigner_default_background'),
        'img_overlay' => get_option('nbdesigner_default_overlay'),
        'real_width' => 8,
        'real_height' => 6,
        'real_left' => 1,
        'real_top' => 1,
        'area_design_top' => 100,
        'area_design_left' => 50,
        'area_design_width' => 400,
        'area_design_height' => 300,
        'img_src_top' => 50,
        'img_src_left' => 0,
        'img_src_width' => 500,
        'img_src_height' => 400,
        'product_width' => 10,    
        'product_height' => 8,
        'show_bleed' => 0,
        'bleed_top_bottom' => 0.3,
        'bleed_left_right' => 0.3,
        'show_safe_zone' => 0,
        'margin_top_bottom' => 0.3,
        'margin_left_right' => 0.3,
        'bg_type'   => 'image',
        'bg_color_value' => "#ffffff",
        'show_overlay' => 0,
        'include_overlay' => 1,
        'area_design_type' => 1,
        'version' => NBDESIGNER_NUMBER_VERSION
    )); 
}
function nbd_get_default_product_option(){
    return apply_filters('nbdesigner_default_product_option', array(
        'admindesign'   => 0,
        'global_template'   => 0,
        'global_template_cat'   => 0,
        'dpi'   => nbdesigner_get_option('nbdesigner_default_dpi'),
        'request_quote' =>  0,
        'allow_specify_dimension'   =>  0,
        'min_width'   =>  0,
        'max_width'   =>  0,
        'min_height'   =>  0,
        'max_height'   =>  0,
        'extra_price'   => 0,
        'type_price'   => 1,
        'type_dimension'   => 1,
        'dynamic_side'   => 0,
        'defined_dimension' => array(array('width' => 10, 'height' => 8, 'price' => 0))
    ));
}
function nbd_get_default_upload_setting(){
    return apply_filters('nbdesigner_default_product_upload', array(
        'number'   => nbdesigner_get_option('nbdesigner_number_file_upload'),
        'allow_type'   => nbdesigner_get_option('nbdesigner_allow_upload_file_type'),
        'disallow_type'   => nbdesigner_get_option('nbdesigner_disallow_upload_file_type'),
        'maxsize'   => nbdesigner_get_option('nbdesigner_maxsize_upload_file'),
        'minsize'   => nbdesigner_get_option('nbdesigner_minsize_upload_file'),
        'mindpi'   => nbdesigner_get_option('nbdesigner_mindpi_upload_file')
    ));    
}
function nbd_get_global_template_cat(){
    $cats = array();

    $url = "https://studio.cmsmart.net/v1/template";
    $postfields = array(
        'type' => 'get_template_cat'
    );
    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_HEADER => 0,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $postfields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => "Mozilla/4.0 (compatible;)"
    );
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    if(!curl_errno($ch)){
        $info = curl_getinfo($ch);
        if ($info['http_code'] == 200){
            $cats = json_decode($response)->data;
        }
    }
    else{
        $errmsg = curl_error($ch);
        echo $errmsg;
    }
    curl_close($ch);
    return $cats;
}
function nbd_update_config_default($designer_setting) {
    $default =  nbd_default_product_setting();    
    foreach ($designer_setting as $key => $setting){
        $designer_setting[$key] = array_merge($default, $setting);
    }
    return $designer_setting;
}
function getUrlPageNBD($page){
    global $wpdb;
    switch ($page) {
        case 'studio':
            $post = nbd_get_page_id( 'studio' );
            break;       
        case 'create':
            $post = nbd_get_page_id( 'create_your_own' );
            break;   
        case 'redirect':
            $post = nbd_get_page_id( 'logged' );
            break;   
        case 'designer':
            $post = nbd_get_page_id( 'designer' );
            break;
        case 'gallery':
            $post = nbd_get_page_id( 'gallery' );
            break;   
        case 'product_builder':
            $post = nbd_get_page_id( 'product_builder' );
            break;
        default :
            $post = nbd_get_page_id( $page );
            break;
    }
    return ($post) ? get_page_link($post) : '#';    
}
function nbd_update_hit_template( $template_id = false, $folder = '' ){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $connection= $resources->getConnection();
    $table_name =  $resources->getTableName('nbdesigner_templates');
    if( $template_id ){
        $sql = "SELECT * FROM {$table_name} WHERE id = {$template_id}";
        $tem = $connection->fetchRow($sql);
    }else if($folder != '') {
        $sql = "SELECT * FROM {$table_name} WHERE folder = '{$folder}'";
        $tem = $connection->fetchRow($sql);
        if( $tem ){
            $template_id = $tem['id'];
        }
    }
    if( $template_id ){
        $hit = $tem['hit'] ? $tem['hit'] + 1 : 1;
        $sql = "Update {$table_name} SET hit={$hit} WHERE id = {$template_id}";
        $connection->query($sql);
    }
}
function nbd_count_total_template( $product_id, $variation_id ){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $connection= $resources->getConnection();
    $table_name =  $resources->getTableName('nbdesigner_templates');

    $sql = "SELECT count(*) as total FROM $table_name WHERE product_id = '$product_id' ORDER BY created_date DESC";
    $results = $connection->fetchAll($sql);
    return $results[0]["total"];
}
function nbd_get_templates( $product_id, $variation_id, $template_id = '', $priority = false, $limit = false, $start = false ){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $connection= $resources->getConnection();
    $table_name =  $resources->getTableName('nbdesigner_templates');

    if( $template_id != '' ){
        $sql = "SELECT * FROM $table_name WHERE id = $template_id";
    }else {
        // if($priority) {
        //     $sql = "SELECT * FROM $table_name WHERE product_id = '$product_id' AND ( variation_id = '$variation_id' || variation_id = 0 ) AND priority = 1";
        // }else {
            if( $limit ){
                $sql = "SELECT * FROM $table_name WHERE product_id = '$product_id' AND publish = 1 ORDER BY created_date DESC LIMIT $limit";
            }else{
                $sql = "SELECT * FROM $table_name WHERE product_id = '$product_id' AND publish = 1 ORDER BY created_date DESC";                
            }           
        // }
    }
    if( $start ){
        $sql .= ' OFFSET ' . $start;
    }
    $results = $connection->fetchAll($sql);

    if( $priority && count( $results ) == 0 ) {
        $sql = "SELECT * FROM $table_name WHERE product_id = '$product_id' AND ( variation_id = '$variation_id' || variation_id = 0 ) AND publish = 1 ORDER BY created_date DESC LIMIT 1";
        $results = $connection->fetchAll($sql);
    }

    return $results;
}
function nbd_get_resource_templates($product_id, $variation_id, $limit = 20, $start = 0, $tags = false){
    $data = array();
    $templates = nbd_get_templates($product_id, $variation_id, '', false, $limit, $start);
    foreach ($templates as $tem){
        $path_preview = NBDESIGNER_CUSTOMER_DIR .'/'.$tem['folder']. '/preview';
        $listThumb = Nbdesigner_IO::get_list_images($path_preview);
        asort( $listThumb );
        if(count($listThumb)){
            $_temp = array();
            $_temp['id'] =  $tem['folder'];
            foreach($listThumb as $img){
                $_temp['src'][] = Nbdesigner_IO::wp_convert_path_to_url($img);
            }
            if( isset($tem['thumbnail']) ){
                $_temp['thumbnail'] = wp_get_attachment_url( $tem['thumbnail'] );
            }else{
                //$_temp['thumbnail'] = end($_temp['src']);
                $_temp['thumbnail'] = $_temp['src'][0];
            }
            $data[] = $_temp;
        }
    }
    if( !$tags ) return $data;
    return apply_filters( 'nbd_product_templates', $data, $templates );
}

function nbd_get_template_by_folder( $folder ){
    $data = array();
    $path = NBDESIGNER_CUSTOMER_DIR .'/'.$folder;
    $data['fonts'] = nbd_get_data_from_json($path . '/used_font.json');
    $data['design'] = nbd_get_data_from_json($path . '/design.json'); 
    $data['config'] = nbd_get_data_from_json($path . '/config.json');
    return $data;
}

function nbd_get_language($code){
    $data = array();
    $data['mes'] = 'success';    
    $path = NBDESIGNER_PLUGIN_DIR . 'data/language.json';
    $path_data = NBDESIGNER_DATA_DIR . '/data/language.json';
    if(file_exists($path_data)) $path = $path_data;
    $list = json_decode(file_get_contents($path)); 
    $path_lang = NBDESIGNER_PLUGIN_DIR . 'data/language/'.$code.'.json';
    $path_data_lang = NBDESIGNER_DATA_DIR . '/data/language/'.$code.'.json';
    if(file_exists($path_data_lang)) $path_lang = $path_data_lang;
    $path_original_lang = NBDESIGNER_PLUGIN_DIR . 'data/language/en_US.json';
    if(!file_exists($path_lang)) $path_lang = $path_original_lang;
    $lang_original = json_decode(file_get_contents($path_original_lang)); 
    $lang = json_decode(file_get_contents($path_lang));
    if(is_array($lang)){
        $data_langs = (array)$lang[0];
        if(is_array($lang_original)){
            $data_langs_origin = (array)$lang_original[0];
            $data_langs = array_merge($data_langs_origin, $data_langs);
        }
        $data['langs'] = $data_langs;
        if(is_array( $data['langs'] )){
            asort($data['langs']);
        }
        $data['code'] = $code;
    }else{
        $data['mes'] = 'error';
    }
    if(is_array($list)){
        $data['cat'] = $list;
    }else{
        $data['mes'] = 'error';
    }    
    return $data;
}

function nbd_get_license_key(){
    $license = array(
        'key'   =>  ''
    );
    $_license = get_option('nbdesigner_license');
    if( $_license ){
        $license = (array) json_decode( $_license );
    }else{
        // $path = NBDESIGNER_DATA_DIR . '/license.json';
        $path = NBDESIGNER_ROOT_DIR . '/var/config/licenseOD.json';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $content_license = $objectManager->create('Netbaseteam\Onlinedesign\Helper\Data')->_readFile($path);
        if (isset($content_license) && $content_license != "") {
            $license = (array) json_decode(file_get_contents($path));
        }
    }
    return $license;
}
function nbd_check_license(){
    $license = nbd_get_license_key();
    $result = false;
    if( $license['key'] != '' ){
        $code = (isset($license["code"])) ? $license["code"] : 10;
        if(($code == 5) || ($code == 6)){
            $now = strtotime("now");
            $expiry_date = (isset($license["expiry-date"])) ? $license["expiry-date"] : 0;         
            if($expiry_date > $now){
                $salt = (isset($license['salt'])) ? $license['salt'] : 'somethingiswrong';
                $new_salt = md5($license['key'].'pro');
                if($salt == $new_salt) $result = true;
            }
        }
    }
    return $result;
}
function nbd_active_domain($license_key){
    $url = 'https://cmsmart.net/activedomain/netbase/WPP1074/'.$license_key.'/'.base64_encode(rtrim(get_bloginfo('wpurl'), '/'));
    $result = nbd_file_get_contents($url);
    if($result){
        $path_data = NBDESIGNER_ROOT_DIR . '/var/config/licenseOD.json';
        if (!file_exists(NBDESIGNER_ROOT_DIR . '/var/config')) {
            wp_mkdir_p(NBDESIGNER_ROOT_DIR . '/var/config');
        }
        $data = (array) json_decode($result);
        $data['key'] = $license_key;
        if($data['type'] == 'free') $data['number_domain'] = "5";
        $data['salt'] = md5($license_key.$data['type']);
        $license = json_encode($data);
        //file_put_contents($path_data, $license);
        update_option('nbdesigner_license', $license);
    }
}

function nbd_get_default_font(){
    $default_fonts =  json_decode( file_get_contents(NBDESIGNER_PLUGIN_DIR . 'data/default-font.json'));
    $subset = nbdesigner_get_option('nbdesigner_default_font_subset');
    $subsets = nbd_font_subsets();
    $font = 'Roboto';   
    foreach($subsets as $key => $sub){
        if( $key == $subset ) $font = $sub['default_font'];
    }
    foreach( $default_fonts as $f ){
        if( $f->name == $font ) return json_encode($f);
    }
}

function nbd_get_fonts( $return = false ){
    $gg_fonts = array();
    $custom_fonts = array();
    if(file_exists(NBDESIGNER_DATA_DIR . '/googlefonts.json')) {
        $_gg_fonts = file_get_contents(NBDESIGNER_DATA_DIR . '/googlefonts.json');
        $_gg_fonts = $_gg_fonts != '' ? $_gg_fonts : '[]';
        $gg_fonts = json_decode( $_gg_fonts );
    }
    if(file_exists(NBDESIGNER_DATA_DIR . '/fonts.json')) {
        $_custom_fonts = file_get_contents(NBDESIGNER_DATA_DIR . '/fonts.json');
        $_custom_fonts = $_custom_fonts != '' ? $_custom_fonts : '[]';
        $custom_fonts = json_decode( $_custom_fonts );
    }
    $fonts = array_merge($gg_fonts,$custom_fonts);
    if( $return ) return $fonts;
    echo json_encode($fonts);
}

function nbd_font_subsets(){
    return array(
        'all'   =>  array(
            'name'  =>  'All language',
            'preview_text'  =>  'Abc Xyz',
            'default_font'  =>  'Roboto'
        ),  
        'arabic'   =>  array(
            'name'  =>  'Arabic',
            'preview_text'  =>  'ءيوهن',
            'default_font'  =>  'Cairo'
        ),
        'bengali'   =>  array(
            'name'  =>  'Bengali',
            'preview_text'  =>  'অআইঈউ',
            'default_font'  =>  'Hind Siliguri'
        ),
        'cyrillic'   =>  array(
            'name'  =>  'Cyrillic',
            'preview_text'  =>  'БВГҐД',
            'default_font'  =>  'Roboto'
        ),
        'cyrillic-ext'   =>  array(
            'name'  =>  'Cyrillic Extended',
            'preview_text'  =>  'БВГҐД',
            'default_font'  =>  'Roboto'
        ),
        'chinese-simplified'   =>  array(
            'name'  =>  'Chinese (Simplified)',
            'preview_text'  =>  '一二三四五',
            'default_font'  =>  'ZCOOL XiaoWei'
        ), 
        'devanagari'   =>  array(
            'name'  =>  'Devanagari',
            'preview_text'  =>  'आईऊऋॠ',
            'default_font'  =>  'Noto Sans'
        ),
        'greek'   =>  array(
            'name'  =>  'Greek',
            'preview_text'  =>  'αβγδε',
            'default_font'  =>  'Roboto'
        ),
        'greek-ext'   =>  array(
            'name'  =>  'Greek Extended',
            'preview_text'  =>  'αβγδε',
            'default_font'  =>  'Roboto'
        ), 
        'gujarati'   =>  array(
            'name'  =>  'Gujarati',
            'preview_text'  =>  'આઇઈઉઊ',
            'default_font'  =>  'Shrikhand'
        ),
        'gurmukhi'   =>  array(
            'name'  =>  'Gurmukhi',
            'preview_text'  =>  'ਆਈਊਏਐ',
            'default_font'  =>  'Baloo Paaji'
        ),
        'hebrew'   =>  array(
            'name'  =>  'Hebrew',
            'preview_text'  =>  'אבגדה',
            'default_font'  =>  'Arimo'
        ),
        'japanese'   =>  array(
            'name'  =>  'Japanese',
            'preview_text'  =>  '一二三四五',
            'default_font'  =>  'Sawarabi Mincho'
        ),
        'kannada'   =>  array(
            'name'  =>  'Kannada',
            'preview_text'  =>  'ಅಆಇಈಉ',
            'default_font'  =>  'Baloo Tamma'
        ),   
        'khmer'   =>  array(
            'name'  =>  'Khmer',
            'preview_text'  =>  'កខគឃង',
            'default_font'  =>  'Hanuman'
        ),
        'korean'   =>  array(
            'name'  =>  'Korean',
            'preview_text'  =>  '가개갸거게',
            'default_font'  =>  'Nanum Gothic'
        ),
        'latin'   =>  array(
            'name'  =>  'Latin',
            'preview_text'  =>  'Abc Xyz',
            'default_font'  =>  'Roboto'
        ),
        'latin-ext'   =>  array(
            'name'  =>  'Latin Extended',
            'preview_text'  =>  'Abc Xyz',
            'default_font'  =>  'Roboto'
        ), 
        'malayalam'   =>  array(
            'name'  =>  'Malayalam',
            'preview_text'  =>  'അആഇഈഉ',
            'default_font'  =>  'Baloo Chettan'
        ),
        'myanmar'   =>  array(
            'name'  =>  'Myanmar',
            'preview_text'  =>  'ကခဂဃင',
            'default_font'  =>  'Padauk'
        ),
        'oriya'   =>  array(
            'name'  =>  'Oriya',
            'preview_text'  =>  'ଅଆଇଈଉ',
            'default_font'  =>  'Baloo Bhaina'
        ),
        'sinhala'   =>  array(
            'name'  =>  'Sinhala',
            'preview_text'  =>  'අආඇඈඉ',
            'default_font'  =>  'Abhaya Libre'
        ),
        'tamil'   =>  array(
            'name'  =>  'Tamil',
            'preview_text'  =>  'க்ங்ச்ஞ்ட்',
            'default_font'  =>  'Catamaran'
        ), 
        'telugu'   =>  array(
            'name'  =>  'Telugu',
            'preview_text'  =>  'అఆఇఈఉ',
            'default_font'  =>  'Gurajada'
        ),
        'thai'   =>  array(
            'name'  =>  'Thai',
            'preview_text'  =>  'กขคฆง',
            'default_font'  =>  'Kanit'
        ),
        'vietnamese'   =>  array(
            'name'  =>  'Vietnamese',
            'preview_text'  =>  'Abc Xyz',
            'default_font'  =>  'Roboto'
        )       
    );
}

function nbd_get_max_input_var(){
    return abs( intval( ini_get( 'max_input_vars' ) ) );
}
function nbd_check_cart_item_exist( $cart_item_key ){
    global $woocommerce;
    $check = false;
    foreach($woocommerce->cart->get_cart() as $key => $val ) {
        if( $cart_item_key ==  $key) return true;
    }
    return $check;
}
function nbd_die( $result ){
    echo json_encode($result);
    wp_die();
}
function nbd_exec($cmd) {
    $output = array();
    exec("$cmd 2>&1", $output);
    return $output;
}

function nbd_user_logged_in(){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $customerSession = $objectManager->get('Magento\Customer\Model\Session');
    return $customerSession->isLoggedIn() ? 1 : 0; 
}

if (!function_exists('nbdesigner_get_default_setting')) {

    function nbdesigner_get_default_setting($key = false)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Config');
        $helper = $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
        $thumb_size = $config->getThumbSize();
        $frontend = default_frontend_setting();
        $str_color = "";
        if (strpos($key, "nbdesigner_hex_names") !== false) {
            $collection = $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data')->getColorCollection();
            foreach ($collection as $c) {
                $str_color .= "#" . $c->getHex() . ":" . $c->getColorName() . ",";
            }
        }
        $nbd_setting = array_merge(array(
            'nbdesigner_button_label' => $config->getDesignLabel(),
            'nbdesigner_position_button_in_catalog' => 1,
            'nbdesigner_start_design_and_upload' => 'Start and upload design',
            'nbdesigner_position_button_product_detail' => 1,
            'nbdesigner_thumbnail_width' => isset($thumb_size[0]) ? $thumb_size[0] : 300,
            'nbdesigner_thumbnail_height' => isset($thumb_size[1]) ? $thumb_size[1] : 76,
            'nbdesigner_thumbnail_quality' => $config->getThumbQuality(),
            'nbdesigner_default_dpi' => $config->getDefaultDPI() ? $config->getDefaultDPI() : 96,
            'nbdesigner_show_in_cart' => 'yes',
            'nbdesigner_show_in_order' => 'yes',
            'nbdesigner_allow_upload_file_type' => $helper->getAllowFileType(),
            'nbdesigner_disallow_upload_file_type' => $helper->getDisallowFileType(),
            'nbdesigner_dimensions_unit' => $config->getUnit(),
            'nbdesigner_disable_on_smartphones' => $config->getHideOnMobile() ? "yes" : "no",
            'nbdesigner_upload_designs_php_logged_in' => $config->getLoginRequire() ? "yes" : "no",
            'nbdesigner_notifications' => 'yes',
            'nbdesigner_notifications_recurrence' => 'hourly',
            'nbdesigner_notifications_emails' => '',
            'nbdesigner_facebook_app_id' => $config->getFApiKey(),
            'nbdesigner_enable_text' => $config->EnableAddText() ? "yes" : "no",
            'nbdesigner_default_text' => $config->getDefaultText() ? $config->getDefaultText() : "Add text here",
            'nbdesigner_enable_curvedtext' => 'yes',
            'nbdesigner_enable_textpattern' => 'yes',
            'nbdesigner_enable_clipart' => $config->EnableClipArt() ? "yes" : "no",
            'nbdesigner_enable_image' => $config->EnableAddImage() ? "yes" : "no",
            'nbdesigner_enable_upload_image' => $config->EnableUploadImage() ? "yes" : "no",
            'nbdesigner_enable_image_webcam' => $config->EnableInsertImageWebcame() ? "yes" : "no",
            'nbdesigner_enable_facebook_photo' => $config->EnableInsertImageFacebook() ? "yes" : "no",
            'nbdesigner_upload_show_term' => $config->EnableInsertImageTerm() ? "yes" : "no",
            'nbdesigner_enable_image_url' => $config->EnableInsertImageUrl() ? "yes" : "no",
            'nbdesigner_upload_term' => $config->getImageTextTerm() ? $config->getImageTextTerm() : "Your term",
            'nbdesigner_enable_draw' => $config->EnableFreedraw() ? "yes" : "no",
            'nbdesigner_enable_qrcode' => $config->EnableQRCode() ? "yes" : "no",
            'nbdesigner_enable_vcard' => $config->enableVcard() ? "yes" : "no",
            'nbdesigner_default_qrcode' => $config->getQRText(),
            'nbdesigner_show_all_color' => $config->getShowAllColor() ? "yes" : "no",
            'nbdesigner_maxsize_upload' => $config->getUploadMaxSize(),
            'nbdesigner_minsize_upload' => $config->getUploadMinSize(),
            'nbdesigner_default_color' => $config->getDefaultColor() ? $config->getDefaultColor() : '#cc324b',
            'nbdesigner_hex_names' => $str_color,
            'nbdesigner_instagram_app_id' => '',
            'nbdesigner_printful_key' => '',
            'nbdesigner_design_layout'  =>  'm',
            'nbdesigner_position_pricing_in_detail_page' => 1,
            'nbdesigner_quantity_pricing_description' => '',
            'nbdesigner_template_width' => 500,
            'nbdesigner_auto_add_cart_in_detail_page' => 'no',
            'nbdesigner_class_design_button_catalog' => '',
            'nbdesigner_class_design_button_detail' => '',
            'nbdesigner_enable_send_mail_when_approve' => 'no',
            'nbdesigner_attachment_admin_email' => 'no',
            'nbdesigner_admin_emails' => '',
            'allow_customer_redesign_after_order' => 'yes',
            'nbd_force_upload_svg' => 'no',
            'nbdesigner_mindpi_upload' => $config->getMinDpiUpload(),
            'nbdesigner_hide_button_cart_in_detail_page'    =>  'no',
            'nbdesigner_google_api_key' => '',   
            'nbdesigner_google_client_id' => '',   
            'nbdesigner_enable_log' => 'no',
            'nbdesigner_cron_job_clear_w3_cache' => 'no',
            'nbdesigner_page_design_tool' => 1,
            'nbdesigner_create_your_own_page_id'    =>  '',
            'nbdesigner_designer_page_id'   =>  '',
            'nbdesigner_gallery_page_id'    =>  '',
            'nbdesigner_mindpi_upload_file' => 0,  
            'nbdesigner_allow_upload_file_type' => '',
            'nbdesigner_disallow_upload_file_type' => '',
            'nbdesigner_number_file_upload' => 1,
            'nbdesigner_maxsize_upload_file' => $config->getUploadMaxSize(),
            'nbdesigner_minsize_upload_file' => 0,        
            'nbdesigner_max_res_upload_file' => '',
            'nbdesigner_min_res_upload_file' => '',            
            'nbdesigner_allow_download_file_upload' => 'no',       
            'nbdesigner_create_preview_image_file_upload' => 'no',
            'nbdesigner_file_upload_preview_width' => 200,
            'nbdesigner_long_time_retain_upload_fies' => '',
            'nbdesigner_enable_download_pdf_before' => 'no',
            'nbdesigner_enable_download_pdf_after' => 'no',   
            'nbdesigner_enable_pdf_watermark' => 'yes',   
            'nbdesigner_pdf_watermark_type' => 1,
            'nbdesigner_pdf_watermark_image' => '',
            'nbdesigner_editor_logo' => '',
            'nbdesigner_truetype_fonts' => '',
            'nbdesigner_default_icc_profile' => 1,
            'nbdesigner_pdf_watermark_text' => '',
            'nbdesigner_bleed_stack' => 1,
            'nbdesigner_enable_perfect_scrollbar_js'    =>  'yes',
            'nbdesigner_enable_angular_js'    =>  'yes',
            'nbdesigner_enable_perfect_scrollbar_css'    =>  'yes',
            'nbdesigner_turn_off_persistent_cart' => 'no',
            'nbdesigner_enable_ajax_cart' => 'no',
            'nbdesigner_option_display' => '1',
            'nbdesigner_hide_add_cart_until_form_filled' => 'no',
            'nbdesigner_enable_clear_cart_button' => 'no',
            'nbdesigner_force_select_options' => 'no',
            'nbdesigner_show_options_in_archive_pages' => 'no',
            'nbdesigner_hide_table_pricing' => 'no',
            'nbdesigner_hide_option_swatch_label' => 'yes',
            'nbdesigner_change_base_price_html' => 'no',
            'nbdesigner_hide_zero_price' => 'no',
            'nbdesigner_tooltip_position' => 'top',
            'nbdesigner_hide_summary_options' => 'no',
            'nbdesigner_hide_options_in_cart' => 'no',
            'nbdesigner_hide_option_price_in_cart' => 'no',
            'nbdesigner_selector_increase_qty_btn' => '',
            'nbdesigner_site_force_login' => 'no',
            'nbdesigner_fix_lost_pdf_image' => 'no',
            'nbdesigner_redefine_K_PATH_FONTS' => 'yes',
            'nbdesigner_disable_nonce' => 'no',
            'nbdesigner_license' => ''
        ), $frontend);
        if (!$key) return $nbd_setting;
        return $nbd_setting[$key];
    }

}

function nbd_get_dpi($filename){
    if( class_exists('Imagick') ){
        $image = new Imagick($filename);
        $resolutions = $image->getImageResolution();
        $units = $image->getImageUnits();
        if( $units == 2 ){
            if (!empty($resolutions['y'])) {
                $resolutions['y'] = round($resolutions['y'] * 2.54, 2);
            }
            if (!empty($resolutions['x'])) {
                $resolutions['x'] = round($resolutions['x'] * 2.54, 2);
            }
        }
    }else{
        $a = fopen($filename,'r');
        $string = fread($a,20);
        fclose($a);

        $data = bin2hex(substr($string,14,4));
        $x = substr($data,0,4);
        $y = substr($data,4,4);
        $resolutions = array('x' => hexdec($x), 'y' => hexdec($y));
    }
    $resolutions['x'] = $resolutions['x'] != 0 ? $resolutions['x'] : 72;
    $resolutions['y'] = $resolutions['y'] != 0 ? $resolutions['y'] : 72;
    return $resolutions;
}

function nbd_get_image_thumbnail( $id ){
    $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $product = $_objectManager->create('Magento\Catalog\Model\Product')->load($id);
    $imageHelper  = $_objectManager->get('\Magento\Catalog\Helper\Image');
    $image_url = $imageHelper->init($product, 'product_thumbnail_image')->setImageFile($product->getFile())->resize(150, 150)->getUrl();
    return $image_url;
}

function get_template_by_folder( $folder ){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $customerSession = $objectManager->create('Magento\Customer\Model\Session');
    $resources = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $connection = $resources->getConnection();
    $table_name = $resources->getTableName('nbdesigner_templates');
    $sql         = "SELECT * FROM ". $table_name;
    if ( !empty($folder) ) {
        $sql    .= " WHERE folder = '" .  $folder . "'";
    }
    $result      = $connection->fetchAll($sql, 'ARRAY_A');
    return $result;
}


function nbd_get_product_info( $product_id, $variation_id, $nbd_item_key = '', $task, $task2 = '', $reference = '', $need_templates = false, $cart_item_key = '' )
{
    $path = '';
    $data = array();
    $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id;
    $_nbd_item_key = '';
    if( $_nbd_item_key && $task2 == '' && $nbd_item_key == '' ) $nbd_item_key = $_nbd_item_key;
    $lazy_load_default_template = nbdesigner_get_option('nbdesigner_lazy_load_template');
    $path = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key;

    $data['upload'] = array(
        "allow_type" => "",
        "disallow_type" => "",
        "number" => 1,
        "minsize" => 0,
        "maxsize" => 8,
        "mindpi" => 0
    );

    /* Path not exist in case add to cart before design, session has been init */
    if( $nbd_item_key == '' || !file_exists($path) ){
        $data['option'] = unserialize(get_post_meta($product_id, '_nbdesigner_option', true));
        $data['option']['layout'] = 'm';
        $data['option']['dpi'] = isset($data['option']['_nbdesigner_dpi']) ? $data['option']['_nbdesigner_dpi'] : 150;
        $data['option']['extra_price'] = 0;
        $data['option']['type_price'] = 1;
        $data['option']['request_quote'] = 0;
        if (!isset($data['option']['admindesign'])) $data['option']['admindesign'] = 0;
        if($variation_id > 0){
            $enable_variation = get_post_meta($variation_id, '_nbdesigner_variation_enable', true);
            $data['product'] = unserialize(get_post_meta($variation_id, '_designer_variation_setting', true));
            if ( !($enable_variation && isset($data['product'][0]))){
                $data['product'] = unserialize(get_post_meta($product_id, '_designer_setting', true));
            }
        }else {
            $data['product'] = unserialize(get_post_meta($product_id, '_designer_setting', true));
        }
    }else {
        $data['product'] = unserialize(file_get_contents($path . '/product.json'));
        $data['option'] = unserialize(file_get_contents($path . '/option.json'));
        if( file_exists($path . '/upload.json') ){
            $data['upload'] = unserialize(file_get_contents($path . '/upload.json'));
        }else{
            $data['upload'] = unserialize(get_post_meta($product_id, '_nbdesigner_upload', true));
        }
        $data['fonts'] = nbd_get_data_from_json($path . '/used_font.json');
        $data['config'] = nbd_get_data_from_json($path . '/config.json');
        if(isset($data['config']->product)){
            $data['product'] = $data['config']->product;
        }
        if($lazy_load_default_template == 'yes'){
            $data['lazy_load_design_folder'] = $nbd_item_key;
        }else{
            $data['design'] = nbd_get_data_from_json($path . '/design.json');
        }
    }
    $disable_auto_load_template = nbdesigner_get_option('nbdesigner_disable_auto_load_template');

    if( $data['option']['admindesign'] && $task == 'new' && $disable_auto_load_template != 'yes' ) {
        /* Get primary template or latest template for new design */
        $template = nbd_get_templates( $product_id, $variation_id, '', 1 );
        if( isset($template[0]) ){
            $template_path = NBDESIGNER_CUSTOMER_DIR . '/' . $template[0]['folder'];
            $data['fonts'] = nbd_get_data_from_json($template_path . '/used_font.json');
            $data['config'] = nbd_get_data_from_json($template_path . '/config.json');
            if($lazy_load_default_template == 'yes'){
                $data['lazy_load_design_folder'] = $template[0]['folder'];
            }else{
                $data['design'] = nbd_get_data_from_json($template_path . '/design.json');
            }
        }
        $data['is_template'] = 1;
    }
    if(  $reference != '' ){
        /* Get reference design, font and reference product setting */
        $ref_path = NBDESIGNER_CUSTOMER_DIR . '/' . $reference;
        if($lazy_load_default_template == 'yes'){
            $data['lazy_load_design_folder'] = $reference;
        }else{
            $data['design'] = nbd_get_data_from_json($ref_path . '/design.json');
        }
        $data['fonts'] = nbd_get_data_from_json($ref_path . '/used_font.json');
        $data['ref'] = unserialize(file_get_contents($ref_path . '/product.json'));
        $data['config_ref'] = nbd_get_data_from_json($ref_path . '/config.json');
        $data['is_reference'] = 1;
        nbd_update_hit_template( false, $reference );
    }
    // if( $data['upload']['allow_type'] == '' ) $data['upload']['allow_type'] = nbdesigner_get_option('nbdesigner_allow_upload_file_type');
    // if( $data['upload']['disallow_type'] == '' ) $data['upload']['disallow_type'] = nbdesigner_get_option('nbdesigner_disallow_upload_file_type');
    // $data['upload']['allow_type'] = preg_replace('/\s+/', '', strtolower( $data['upload']['allow_type']) );
    // $data['upload']['disallow_type'] = preg_replace('/\s+/', '', strtolower( $data['upload']['disallow_type']) );
    if( $need_templates ){
        $templates = nbd_get_resource_templates( $product_id, $variation_id );
        if( count($templates) ){
            $data['templates'] = $templates;
        }
    }
    $task            = (isset($_REQUEST['task']) && $_REQUEST['task'] != '') ? $_REQUEST['task'] : 'new';
    $design_type     = (isset($_REQUEST['design_type']) && $_REQUEST['design_type'] != '') ? $_REQUEST['design_type'] : '';
    if( $task == 'edit' && $design_type == 'template' ){
        $folder         = $_GET['nbd_item_key'];
        $templates      = get_template_by_folder( $folder );
        if( is_array($templates) && isset( $templates[0] ) ){
            $template               = $templates[0];
            $data['template']       = array(
                'tags'     => is_null( $template['tags'] ) ? '' : $template['tags'],
                'colors'     => is_null( $template['colors'] ) ? '' : $template['colors'],
                'name'     => $template['name']
            );
        }
    }
    return $data;
}

function nbd_get_data_from_json($path = '')
{
    if ($path != '' && file_exists($path)) {
        return json_decode(file_get_contents($path));
    }
    return '';
}

function hex_code_to_rgb($code)
{
    list($r, $g, $b) = sscanf($code, "#%02x%02x%02x");
    $rgb = array($r, $g, $b);
    return $rgb;
}

function getDesignPath($oid, $pid)
{
    $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $order = $_objectManager->create("Magento\Sales\Model\Order")->load($oid);
    $order->getAllVisibleItems();
    $orderItems = $order->getItemsCollection()->addAttributeToSelect('*')->load();
    foreach ($orderItems as $item) {
        if ($item->getParentItemId() == null || $item->getParentItemId() == "") {
            if ($item->getId() == $_GET["order_item_id"]) {
                $data_design = $item->getNbdesignerJson();
                $folder = $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data')->getSessionFolderFromPath($data_design);
            }
        }
    }

    $path = NBDESIGNER_CUSTOMER_DIR . '/' . $folder . '/nb_order/' . $pid . '/';
    return $path;
}

/* =========== Nbdesigner_IO  ============= */

class Nbdesigner_IO {
    public function __construct() {
        //TODO
    }
    public static function wp_mkdir_p($path) {
        if(@mkdir($path) or file_exists($path)) return true;
        return (wp_mkdir_p(dirname($path)) and mkdir($path));
    }
    public static function convert_svg_embed($path)
    {
        $svgs = Nbdesigner_IO::get_list_files_by_type($path, 1, 'svg');
        $svg_path = $path . '/svg';
        if (!file_exists($svg_path)) Nbdesigner_IO::wp_mkdir_p($svg_path);
        foreach ($svgs as $svg) {
            $svg_name = pathinfo($svg, PATHINFO_BASENAME);
            $new_svg_path = $svg_path . '/' . $svg_name;
            $xdoc = new \DomDocument();
            $xdoc->Load($svg);
            /* Embed images */
            $images = $xdoc->getElementsByTagName('image');
            for ($i = 0; $i < $images->length; $i++) {
                $tagName = $xdoc->getElementsByTagName('image')->item($i);
                $attribNode = $tagName->getAttributeNode('xlink:href');
                $img_src = $attribNode->value;
                if (strpos($img_src, "data:image") !== FALSE)
                    continue;
                $type = pathinfo($img_src, PATHINFO_EXTENSION);
                $type = ($type == 'svg') ? 'svg+xml' : $type;
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
                $font = Nbdesigner_IO::nbd_get_font_by_alias($font_family);
                if ($font) {
                    $tagName->setAttribute('font-family', $font->name);
                }
            }
            $new_svg = $xdoc->saveXML();
            file_put_contents($new_svg_path, $new_svg);
        }
    }
    public static function nbd_get_font_by_alias( $alias ){
        $fonts = array();
        if(file_exists( NBDESIGNER_DATA_DIR . '/fonts.json') ){
            $fonts = (array)json_decode( file_get_contents( NBDESIGNER_DATA_DIR . '/fonts.json' ) );
        }
        foreach ($fonts as $font) {
            if ($font->alias == $alias) {
                return $font;
            }
        }
        return false;
    }
    public static function nbdesigner_list_download_svg($path, $level = 2)
    {
        $list = array();
        $_list = Nbdesigner_IO::nbdesigner_list_files($path .'/svg', $level);
        $list = preg_grep('/\.(jpg|jpeg|png|gif|svg)(?:[\?\#].*)?$/i', $_list);
        return $list;
    }
    public static function nbdesigner_list_files($folder = '', $levels = 100)
    {
        if (empty($folder)) {
            return false;
        }

        if (!$levels) {
            return false;
        }

        $files = array();
        if ($dir = @opendir($folder)) {
            while (($file = readdir($dir)) !== false) {
                if (in_array($file, array('.', '..'))) {
                    continue;
                }
                if (is_dir($folder . '/' . $file)) {
                    $files2 = self::nbdesigner_list_files($folder . '/' . $file, $levels - 1);
                    if ($files2) {
                        $files = array_merge($files, $files2);
                    } else {
                        $files[] = $folder . '/' . $file . '/';
                    }
                } else {
                    $files[] = $folder . '/' . $file;
                }
            }
        }
        @closedir($dir);
        return $files;
    }

    /**
     * Get all images in folder by level
     * 
     * @param string $path path folder
     * @param int $level level scan dir
     * @return array Array path images in folder
     */
    public static function get_list_images($path, $level = 100){
        $list = array();
        $_list = self::get_list_files($path, $level);
        $list = preg_grep('/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i', $_list);
        return $list;        
    }
    public static function get_list_files_by_type($path, $level = 100, $type){
        $list = array();
        $_list = self::get_list_files($path, $level);
        $list = preg_grep('/\.(' . $type . ')(?:[\?\#].*)?$/i', $_list);
        return $list;        
    }
    public function nbdesigner_list_download($path, $level = 2)
    {
        $list = array();
        $_list = Nbdesigner_IO::nbdesigner_list_files($path, $level);
        $list = preg_grep('/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i', $_list);
        return $list;
    }

    public static function get_list_files($folder = '', $levels = 100) {
        if (empty($folder))
            return false;
        if (!$levels)
            return false;        
        $files = array();
        if ($dir = @opendir($folder)) {
            while (($file = readdir($dir) ) !== false) {
                if (in_array($file, array('.', '..')))
                    continue;
                if (is_dir($folder . '/' . $file)) {
                    $files2 = self::get_list_files($folder . '/' . $file, $levels - 1);
                    if ($files2)
                        $files = array_merge($files, $files2);
                    else
                        $files[] = $folder . '/' . $file . '/';
                } else {
                    $files[] = $folder . '/' . $file;
                }
            }
        }
        @closedir($dir);
        return $files;
    }
    public static function get_list_folder($folder = '', $levels = 100){
        if (empty($folder)) return false;    
        if (!$levels) return false;          
        $folders = array();
        if ($dir = @opendir($folder)) {
            while (($file = readdir($dir) ) !== false) {
                if (in_array($file, array('.', '..')))
                    continue;
                if (is_dir($folder . '/' . $file)) {
                    $folders2 = self::get_list_folder($folder . '/' . $file, $levels - 1);
                    if ($folders2){
                        $folders = array_merge($folders, $folders2);
                    }else {
                        $folders[] = $folder . '/' . $file . '/';
                    }
                }    
            }
        }
        @closedir($dir);
        return $folders;        
    }
    public static function delete_folder($path) {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));
            foreach ($files as $file) {
                self::delete_folder(realpath($path) . '/' . $file);
            }
            return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }
        return false;
    } 
    public static function copy_dir($src, $dst) {
        if (file_exists($dst)) self::delete_folder($dst);
        if (is_dir($src)) {
            wp_mkdir_p($dst);
            $files = scandir($src);
            foreach ($files as $file){
                if ($file != "." && $file != "..") self::copy_dir("$src/$file", "$dst/$file");
            }
        } else if (file_exists($src)) copy($src, $dst);
    } 
    public static function mkdir( $dir ){
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }        
    }
    public static function clear_file($path){
        $f = @fopen($path, "r+");
        if ($f !== false) {
            ftruncate($f, 0);
            fclose($f);
        }        
    }
    public static function create_index_html($path){
        if (!file_exists($path)) {
            $content = __('Silence is golden.', 'web-to-print-online-designer');
            file_put_contents($path, $content);
        }
    }
    public static function create_file_path($upload_path, $filename, $ext='', $force_override = false){
    $date_path = '';
        if (!file_exists($upload_path))
            mkdir($upload_path);
        $year = @date() === false ? gmdate('Y') : date('Y');
        $date_path .= '/' . $year . '/';
        if (!file_exists($upload_path . $date_path))
            mkdir($upload_path . $date_path);
        $month = @date() === false ? gmdate('m') : date('m');
        $date_path .= $month . '/';
        if (!file_exists($upload_path . $date_path))
            mkdir($upload_path . $date_path);
        $day = @date() === false ? gmdate('d') : date('d');
        $date_path .= $day . '/';
        if (!file_exists($upload_path . $date_path))
            mkdir($upload_path . $date_path);
        $file_path = $upload_path . $date_path . $filename;
        $file_counter = 1;
        $real_filename = $filename;
        if($force_override){
            if(file_exists($file_path . '.' . $ext) ) unlink($file_path . '.' . $ext);
        }else{
            while (file_exists($file_path . '.' . $ext)) {
                $real_filename = $file_counter . '-' . $filename;
                $file_path = $upload_path . $date_path . $real_filename;
                $file_counter++;
            }
        }
        return array(
            'full_path' => $file_path,
            'date_path' => $date_path . $real_filename
        );
    }   
    public static function secret_image_url($file_path){
        $type = pathinfo($file_path, PATHINFO_EXTENSION);
        $data = file_get_contents($file_path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);   
        return $base64;        
    }   
    /**
     * @deprecated 1.7.0 <br />
     * From 1.7.0 alternate by function wp_convert_path_to_url( $path )
     * @param type $path
     * @return string url
     */
    public static function convert_path_to_url($path){
        $path_dir = explode('media', $path);
        return NBDESIGNER_BASE_URL . 'pub/media/' . $path_dir[1];
    }
    /**
     * @deprecated 1.7.0
     * From 1.7.0 alternate by WP function wp_make_link_relative( $url )
     * @param type $url
     * @return path
     */
    public static function convert_url_to_path($url){
        $basedir = NBDESIGNER_DATA_DIR;
        $arr = explode('/', $basedir);
        $upload = $arr[count($arr) - 1];
        $arr_url = explode('nbdesigner/' . $upload, $url);
        return $basedir . trim(isset($arr_url[1]) ? $arr_url[1] : $arr_url[0], "/");
    }

    public static function wp_convert_path_to_url( $path = '' ){
        $baseUrl = NBDESIGNER_BASE_URL;
        $rootDir = NBDESIGNER_ROOT_DIR;
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $elementTemplate = $_objectManager->get('Magento\Framework\View\Element\Template');

        $url = str_replace(
            $rootDir,
            $baseUrl,
            $path
        );
        return $elementTemplate->escapeUrl($url);
//        $url = self::convert_path_to_url($path);
//        return $url;
    }
    public static function save_data_to_file($path, $data){
        if (!$fp = fopen($path, 'w')) {
            return FALSE;
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
        return TRUE;        
    }
    public static function checkFileType($file_name, $arr_mime) {
        $check = false;
        $filetype = explode('.', $file_name);
        $file_exten = $filetype[count($filetype) - 1];
        if (in_array(strtolower($file_exten), $arr_mime)) $check = true;
        return $check;
    }   
    public static function get_thumb_file( $ext, $path = '' ){
        $thumb = '';
        switch ( $ext ) {
            case 'jpg': 
            case 'jpeg': 
                $thumb = NBDESIGNER_ASSETS_URL . 'images/file_type/jpg.png';
                break;
            case 'png': 
                $thumb = NBDESIGNER_ASSETS_URL . 'images/file_type/png.png';
                break;             
            case 'psd': 
                $thumb = NBDESIGNER_ASSETS_URL . 'images/file_type/psd.png';
                break;       
            case 'pdf': 
                $thumb = NBDESIGNER_ASSETS_URL . 'images/file_type/pdf.png';
                break;
            case 'ai': 
                $thumb = NBDESIGNER_ASSETS_URL . 'images/file_type/ai.png';
                break;       
            case 'eps': 
                $thumb = NBDESIGNER_ASSETS_URL . 'images/file_type/eps.png';
                break;     
            case 'zip': 
                $thumb = NBDESIGNER_ASSETS_URL . 'images/file_type/zip.png';
                break; 
            case 'svg': 
                $thumb = NBDESIGNER_ASSETS_URL . 'images/file_type/svg.png';
                break;             
            default: 
                $thumb = NBDESIGNER_ASSETS_URL . 'images/file_type/file.png';
                break;             
        }
        return $thumb;
    }
    public static function read_json_setting($fullname) {
        if (file_exists($fullname)) {
            $list = json_decode(file_get_contents($fullname));           
        } else {
            $list = '[]';
            file_put_contents($fullname, $list);
        }
        return $list;
    }
    public static function delete_json_setting($fullname, $id, $reindex = true) {
        $list = self::read_json_setting($fullname);
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
    public static function update_json_setting($fullname, $data, $id) {
        $list = self::read_json_setting($fullname);
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
    public static function update_json_setting_depend($fullname, $id) {
        $list = self::read_json_setting($fullname);
        if (!is_array($list)) return;
        foreach ($list as $val) {             
            if (!((sizeof($val) > 0))) continue;       
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
                }
            }
        }
        $res = json_encode($list);
        file_put_contents($fullname, $res);
    }    
}

class NBD_Image {
    public static function nbdesigner_resize_imagepng($file, $w, $h, $path = ''){
        if (strpos($file, 'default.png') !== false) {
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface');
            $imageDefault =  $_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data')->getImageDefault();
            $currentStore = $storeManager->getStore();
            $file = $imageDefault;
        }
        list($width, $height) = getimagesize($file);
        if( $path != '' ) $h = round( $w / $width * $height );
        $src = imagecreatefrompng($file);
        $dst = imagecreatetruecolor($w, $h);
        imagesavealpha($dst, true);
        $color = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefill($dst, 0, 0, $color);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $width, $height);
        imagedestroy($src);
        if( $path == '' ){
            return $dst;   
        } else{
            imagepng($dst, $path );
            imagedestroy($dst);
        }
    }
    public static function nbdesigner_resize_imagejpg($file, $w, $h, $path = '') {
        list($width, $height) = getimagesize($file);
        if( $path != '' ) $h = round( $w / $width * $height );
        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($w, $h);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $width, $height);
        imagedestroy($src);
        if( $path == '' ){
            return $dst;   
        } else{
            imagejpeg($dst, $path );
            imagedestroy($dst);
        }
    }      
    public static function convert_png_to_jpg($input_file){
        $output_file = pathinfo($input_file) . '/'. basename($filename, '.png') . ".jpeg";
        $input = imagecreatefrompng($input_file);
        list($width, $height) = getimagesize($input_file);
        $output = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($output,  255, 255, 255);
        imagefilledrectangle($output, 0, 0, $width, $height, $white);
        imagecopy($output, $input, 0, 0, 0, 0, $width, $height);
        imagejpeg($output, $output_file);        
    }
    public function resample($image, $height, $width, $format = 'jpeg', $dpi = 300){
        if (!$image) {
            throw new \Exception('Attempting to resample an empty image');
        }
        if (gettype($image) !== 'resource') {
            throw new \Exception('Attempting to resample something which is not a resource');
        }
        //Use truecolour image to avoid any issues with colours changing
        $tmp_img = imagecreatetruecolor($width, $height);
        //Resample the image to be ready for print
        if (!imagecopyresampled($tmp_img, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image))) {
            throw new \Exception("Unable to resample image");
        }
        //Massive hack to get the image as a jpeg string but there is no php function which converts
        //a GD image resource to a JPEG string
        ob_start();
        imagejpeg($tmp_img, null, 100);
        $image = ob_get_contents();
        ob_end_clean();
        //change the JPEG header to 300 pixels per inch
        $image = substr_replace($image, pack("Cnn", 0x01, $dpi, $dpi), 13, 5);
        return $image;
    } 
    public static function gd_resample( $input_file, $ouput_file, $dpi ){
        $source = imagecreatefromjpeg($input_file);
        list($width, $height) = getimagesize($filename);
        $image = self::resample( $source, $height, $width, $dpi );
        file_put_contents( $ouput_file, $image );
    }
    public static function imagick_add_white_bg( $input_file, $ouput_file ){       
        try {
            $image = new Imagick( $input_file );
            $bg = new IMagick();
            $bg->newImage($image->getImageWidth(), $image->getImageHeight(), new ImagickPixel("white"));
            $bg->setImageBackgroundColor('#FFFFFF');
            $bg->compositeImage($image, IMagick::COMPOSITE_DEFAULT, 0, 0);     
            $bg->writeImage( $ouput_file );  
            $image->destroy(); 
            $bg->destroy(); 
        } catch (Exception $e) {
            die('Error when creating a thumbnail: ' . $e->getMessage());
        }
    }
    public static function imagick_convert_png_to_jpg( $input_file, $ouput_file ){
        try {
            $image = new Imagick( $input_file );
            $flattened = new IMagick();
            $flattened->newImage($image->getImageWidth(), $image->getImageHeight(), new ImagickPixel("white"));
            $flattened->compositeImage($image, IMagick::COMPOSITE_OVER, 0, 0);
            $flattened->setImageFormat("jpg");
            $flattened->writeImage( $ouput_file );  
            $image->destroy(); 
            $flattened->destroy(); 
        } catch( Exception $e ){
            die('Error when creating a thumbnail: ' . $e->getMessage());
        }  
    }    
    public static function imagick_convert_png2jpg_without_bg( $input_file, $ouput_file ){
        try {
            $image = new Imagick( $input_file );
            $image->setImageFormat("jpg");
            $image->writeImage( $ouput_file );  
            $image->destroy();             
        } catch (Exception $e) {
            die('Error when creating a thumbnail: ' . $e->getMessage());
        }
    }
    public static function imagick_convert_rgb_to_cymk( $input_file, $ouput_file ){
        try {
            $image = new Imagick( $input_file );
            $image->stripImage();
            $image->transformimagecolorspace(\Imagick::COLORSPACE_CMYK);
            $image->writeImage( $ouput_file );
            $image->destroy(); 
        } catch( Exception $e ){
            die('Error when creating a thumbnail: ' . $e->getMessage());
        }        
    }
    public static function imagick_resample( $input_file, $ouput_file, $dpi ){
        try {
            $image = new Imagick();
            //$image->setResolution($dpi,$dpi);
            $image->readImage($input_file);
            $image->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH);
            $image->setImageResolution($dpi,$dpi);
            $image->writeImage($ouput_file);
            $image->destroy(); 
        } catch( Exception $e ){
            die('Error when creating a thumbnail: ' . $e->getMessage());
        }
    }
    public static function imagick_change_icc_profile( $input_file, $ouput_file, $icc  ){
        try {
            $image = new Imagick( $input_file );
            $image->stripImage ();
            $icc_profile = file_get_contents( $icc ); 
            $image->profileImage('icc', $icc_profile); 
            unset($icc_profile); 
            $image->writeImage( $ouput_file );
            $image->destroy();
        } catch( Exception $e ){
            die('Error when creating a thumbnail: ' . $e->getMessage());
        }            
    } 
    public static function imagick_convert_pdf_to_jpg( $input_file, $ouput_file ){
        try {          
            $image = new Imagick();
            $image->setResolution(72,72);
            $image->readimage( $input_file.'[0]' );           
            $image->setImageFormat('jpeg');
            $image->writeImage( $ouput_file );
            $image->clear(); 
            $image->destroy();
        } catch( Exception $e ){
            die('Error when creating a thumbnail: ' . $e->getMessage());
        }        
    }
    public static function crop_image( $input_file, $ouput_file, $startX, $startY, $width, $height, $ext ){
        if( is_available_imagick() ){
            try {          
                $image = new Imagick( $input_file );
                $image->cropImage($width, $height, $startX, $startY);          
                $image->writeImage( $ouput_file );
                $image->clear(); 
                $image->destroy();
            } catch( Exception $e ){
                die('Error when creating a thumbnail: ' . $e->getMessage());
            }              
        }else{
            $src = $ext == 'png' ? imagecreatefrompng($input_file) : imagecreatefromjpeg($input_file);
            $dst = imagecrop($src, ['x' => $startX, 'y' => $startY, 'width' => $width, 'height' => $height]);
            if ($dst !== FALSE) {
                if($ext == 'png'){
                    imagepng($dst, $ouput_file );
                }else{
                    imagejpeg($dst, $ouput_file );            
                }
            }
            imagedestroy($dst); 
            imagedestroy($src);
        }
    }
    public static function pdf2image($path, $dpi = 300, $type = 'png'){
        $ext = $type == 'png' ? 'png32' : 'jpg';
        $document = new Imagick($path);
        $number_pages = $document->getNumberImages();
        for( $i = 0; $i < $number_pages; $i++ ){
            $im = new Imagick();
            $im->setResolution($dpi, $dpi);
            $im->readImage( $path . '['. $i .']' );           
            $im->setImageFormat('jpg');
            $im->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH);
            $im->setResolution($dpi, $dpi);
            if($type == 'jpg'){
                $im->setImageBackgroundColor('white');
                if ($im->getImageAlphaChannel()){
                    $im->setImageAlphaChannel(11);
                }
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
            }
            // this will drop down the size of the image dramatically (removes all profiles) 
            //$im->stripImage();
            $im->writeImage(str_replace(".pdf", "_". $i . "." . $type, $path));
            $im->clear();
            $im->destroy();
        }
        $document->clear();
        $document->destroy();
    }
}
class NBD_Download {
    public static function init() {
    }
    /**
     * Force download - this is the default method.
     * @param  string $file_path
     * @param  string $filename
     */
    public static function download_file( $file_path, $filename ) {
        $parsed_file_path = self::parse_file_path( $file_path );
        self::download_headers( $file_path, $filename );
        if ( ! self::readfile_chunked( $parsed_file_path['file_path'] ) ) {
            if ( $parsed_file_path['remote_file'] ) {
                self::download_file_redirect( $file_path );
            } else {
                self::download_error( __( 'File not found', 'web-to-print-online-designer' ) );
            }
        }
        exit;
    }
    /**
     * Redirect to a file to start the download.
     * @param  string $file_path
     * @param  string $filename
     */
    public static function download_file_redirect( $file_path, $filename = '' ) {
        header( 'Location: ' . $file_path );
        exit;
    }
    /**
     * Parse file path and see if its remote or local.
     * @param  string $file_path
     * @return array
     */
    public static function parse_file_path($file_path) {
        $wp_uploads = wp_upload_dir();
        $wp_uploads_dir = $wp_uploads['basedir'];
        $wp_uploads_url = $wp_uploads['baseurl'];
        /**
         * Replace uploads dir, site url etc with absolute counterparts if we can.
         * Note the str_replace on site_url is on purpose, so if https is forced
         * via filters we can still do the string replacement on a HTTP file.
         */
        $replacements = array(
            $wp_uploads_url => $wp_uploads_dir,
            network_site_url('/', 'https') => ABSPATH,
            str_replace('https:', 'http:', network_site_url('/', 'http')) => ABSPATH,
            site_url('/', 'https') => ABSPATH,
            str_replace('https:', 'http:', site_url('/', 'http')) => ABSPATH,
        );
        $file_path = str_replace(array_keys($replacements), array_values($replacements), $file_path);
        $parsed_file_path = parse_url($file_path);
        $remote_file = true;

        // See if path needs an abspath prepended to work
        if (file_exists(ABSPATH . $file_path)) {
            $remote_file = false;
            $file_path = ABSPATH . $file_path;
        } elseif ('/wp-content' === substr($file_path, 0, 11)) {
            $remote_file = false;
            $file_path = realpath(WP_CONTENT_DIR . substr($file_path, 11));
            // Check if we have an absolute path
        } elseif ((!isset($parsed_file_path['scheme']) || !in_array($parsed_file_path['scheme'], array('http', 'https', 'ftp')) ) && isset($parsed_file_path['path']) && file_exists($parsed_file_path['path'])) {
            $remote_file = false;
            $file_path = $parsed_file_path['path'];
        }
        return array(
            'remote_file' => $remote_file,
            'file_path' => $file_path,
        );
    }

    /**
     * Get content type of a download.
     * @param  string $file_path
     * @return string
     * @access private
     */
    private static function get_download_content_type($file_path) {
        $file_extension = strtolower(substr(strrchr($file_path, "."), 1));
        $ctype = "application/force-download";
        foreach (get_allowed_mime_types() as $mime => $type) {
            $mimes = explode('|', $mime);
            if (in_array($file_extension, $mimes)) {
                $ctype = $type;
                break;
            }
        }
        return $ctype;
    }
    /**
     * Set headers for the download.
     * @param  string $file_path
     * @param  string $filename
     * @access private
     */
    private static function download_headers($file_path, $filename) {
        self::check_server_config();
        self::clean_buffers();
        nocache_headers();
        header("X-Robots-Tag: noindex, nofollow", true);
        header("Content-Type: " . self::get_download_content_type($file_path));
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
        header("Content-Transfer-Encoding: binary");
        if ($size = @filesize($file_path)) {
            header("Content-Length: " . $size);
        }
    }


    /**
     * Check and set certain server config variables to ensure downloads work as intended.
     */
    private static function check_server_config() {
        wc_set_time_limit(0);
        if (function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime() && version_compare(phpversion(), '5.4', '<')) {
            set_magic_quotes_runtime(0);
        }
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 'Off');
        @session_write_close();
    }
    /**
     * Clean all output buffers.

     * Can prevent errors, for example: transfer closed with 3 bytes remaining to read.
     *
     * @access private
     */
    private static function clean_buffers() {
        if (ob_get_level()) {
            $levels = ob_get_level();
            for ($i = 0; $i < $levels; $i++) {
                @ob_end_clean();
            }
        } else {
            @ob_end_clean();
        }
    }
    /**
     * readfile_chunked.
     *
     * Reads file in chunks so big downloads are possible without changing PHP.INI - http://codeigniter.com/wiki/Download_helper_for_large_files/.
     *
     * @param   string $file
     * @return  bool Success or fail
     */
    public static function readfile_chunked($file) {
        $chunksize = 1024 * 1024;
        $handle = @fopen($file, 'r');

        if (false === $handle) {
            return false;
        }
        while (!@feof($handle)) {
            echo @fread($handle, $chunksize);

            if (ob_get_length()) {
                ob_flush();
                flush();
            }
        }
        return @fclose($handle);
    }
    public static function ie_nocache_headers_fix($headers) {
        if (is_ssl() && !empty($GLOBALS['is_IE'])) {
            $headers['Cache-Control'] = 'private';
            unset($headers['Pragma']);
        }
        return $headers;
    }
    /**
     * Die with an error message if the download fails.
     * @param  string $message
     * @param  string  $title
     * @param  integer $status
     * @access private
     */
    private static function download_error($message, $title = '', $status = 404) {
        if (!strstr($message, '<a ')) {
            $message .= ' <a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="wc-forward">' . esc_html__('Go to shop', 'woocommerce') . '</a>';
        }
        wp_die($message, $title, array('response' => $status));
    }
}
NBD_Download::init();

function is_available_imagick(){
    if(!class_exists("Imagick")) return false;
    return true;
}

function nbd_get_icc_cmyk_list(){
    return array(
        0   =>  'Don\'t Color Manage',
        1   =>  'Coated FOGRA27',
        2   =>  'Coated FOGRA39',
        3   =>  'Coated GRACoL 2006',
        4   =>  'Japan Color 2001 Coated',
        5   =>  'Japan Color 2001 Uncoated',
        6   =>  'Japan Color 2002 Newspaper',
        7   =>  'Japan Color 2003 Web Coated',
        8   =>  'Japan Web Coated',
        9   =>  'Uncoated FOGRA29',
        10   =>  'US Web Coated SWOP',
        11   =>  'US Web Uncoated',
        12   =>  'Web Coated FOGRA28',
        13   =>  'Web Coated SWOP 2006 Grade 3',
        14   =>  'Web Coated SWOP 2006 Grade 5'
    );
}

function nbd_get_icc_cmyk_list_file(){
    return array(
        0   =>  '',
        1   =>  'CoatedFOGRA27.icc',
        2   =>  'CoatedFOGRA39.icc',
        3   =>  'CoatedGRACoL2006.icc',
        4   =>  'JapanColor2001Coated.icc',
        5   =>  'JapanColor2001Uncoated.icc',
        6   =>  'JapanColor2002Newspaper.icc',
        7   =>  'JapanColor2003WebCoated.icc',
        8   =>  'JapanWebCoated.icc',
        9   =>  'UncoatedFOGRA29.icc',
        10   =>  'USWebCoatedSWOP.icc',
        11   =>  'USWebUncoated.icc',
        12   =>  'WebCoatedFOGRA28.icc',
        13   =>  'WebCoatedSWOP2006Grade3.icc',
        14   =>  'WebCoatedSWOP2006Grade5.icc'
    );
}

function nbd_file_get_contents($url){
    $response = wp_remote_get( $url );
    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
        $result   = trim($response['body']);
        return $result;
    }
    if(ini_get('allow_url_fopen')){
        $checkPHP = version_compare(PHP_VERSION, '5.6.0', '>=');
        if (is_ssl() && $checkPHP) {
            $result = file_get_contents($url, false, stream_context_create(array('ssl' => 
                array('verify_peer' => false, 'verify_peer_name' => false)))); 
        }else{
            $result = file_get_contents($url);    
        }             
    }else{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION, 3); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);                        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);          
        if(false === $result){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            $result = curl_exec($ch);
            curl_close($ch);          
        }
    }
    return $result;
}
function nbd_download_google_font( $font_name = ''){
    $path_dst = array(
        'r' =>  NBDESIGNER_FONT_DIR . '/' . $font_name . '.ttf'
    );
    if( !file_exists($path_dst['r']) ){
        $google_font_path = NBDESIGNER_PLUGIN_DIR . '/data/google-fonts-ttf.json';
        $fonts = json_decode( file_get_contents($google_font_path) );
        $items = $fonts->items;
        foreach ( $items as $item ){
            if( $item->family == $font_name ){
                $font = $item->files;
                break;
            }
        }
        $path_src = isset($font->regular) ? $font->regular : reset($font);
        copy($path_src, $path_dst['r']);
        if( isset($font->italic) ){
            $path_dst['i'] = NBDESIGNER_FONT_DIR . '/' . $font_name . 'i.ttf';
            copy($font->italic, $path_dst['i']);
        }
        if( isset($font->{"700"}) ){
            $path_dst['b'] = NBDESIGNER_FONT_DIR . '/' . $font_name . 'b.ttf';
            copy($font->{"700"}, $path_dst['b']);
        }
        if( isset($font->{"700italic"}) ){
            $path_dst['bi'] = NBDESIGNER_FONT_DIR . '/' . $font_name . 'bi.ttf';
            copy($font->{"700italic"}, $path_dst['bi']);
        }
    }
    return $path_dst;
}
/* =========== End Nbdesigner_IO  ============= */
?>