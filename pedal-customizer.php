<?php
/**
 * Plugin Name:  order customizer plugin
 * Plugin URI: 
 * Description: Enable fully customizable product orders directly through WooCommerce checkout. This plugin offers a responsive and intuitive interface, allowing users to select materials, shapes, finishes, and upload custom artwork. Perfect for bespoke manufacturing workflows. Crafted with precision by Codex.
 * Version: 1.0.0
 * Author: Codex
 * Author URI: 
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.6
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Text Domain: codex
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CODEX_CUSTOM_ORDER_VERSION', '1.0.0');
define('CODEX_CUSTOM_ORDER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CODEX_CUSTOM_ORDER_PLUGIN_PATH', plugin_dir_path(__FILE__));

class PedalCustomizer {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('CODEX_CUSTOM_ORDER', array($this, 'render_customizer'));
        add_action('wp_ajax_save_pedal_order', array($this, 'save_pedal_order'));
        add_action('wp_ajax_nopriv_save_pedal_order', array($this, 'save_pedal_order'));
        add_action('wp_ajax_upload_artwork', array($this, 'handle_artwork_upload'));
        add_action('wp_ajax_nopriv_upload_artwork', array($this, 'handle_artwork_upload'));
        add_action('wp_ajax_upload_product_image', array($this, 'handle_product_image_upload'));
        add_action('wp_ajax_add_to_cart_custom_pedal', array($this, 'add_to_cart_custom_pedal'));
        add_action('wp_ajax_nopriv_add_to_cart_custom_pedal', array($this, 'add_to_cart_custom_pedal'));
        add_action('wp_ajax_get_products_by_category', array($this, 'get_products_by_category'));
        add_action('wp_ajax_nopriv_get_products_by_category', array($this, 'get_products_by_category'));
        add_action('wp_ajax_debug_pedal_customizer', array($this, 'debug_pedal_customizer'));
        add_action('wp_ajax_nopriv_debug_pedal_customizer', array($this, 'debug_pedal_customizer'));
        
        // WooCommerce hooks
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_custom_data_to_cart'), 10, 2);
        add_filter('woocommerce_get_item_data', array($this, 'display_custom_data_in_cart'), 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'save_custom_data_to_order'), 10, 4);
        add_filter('woocommerce_cart_item_price', array($this, 'update_cart_item_price'), 10, 3);
        add_action('woocommerce_before_calculate_totals', array($this, 'update_cart_item_price_before_calculate'));
        
        // Display custom artwork in WooCommerce order details
        add_action('woocommerce_order_item_meta_end', array($this, 'display_custom_artwork_in_order'), 10, 3);
        add_action('woocommerce_admin_order_item_meta_end', array($this, 'display_custom_artwork_in_admin_order'), 10, 3);
        add_action('woocommerce_after_order_itemmeta', array($this, 'display_custom_artwork_in_admin_order'), 10, 3);
        
        // Create database tables on activation
        register_activation_hook(__FILE__, array($this, 'create_database_tables'));
        
        // Check for WooCommerce
        add_action('admin_notices', array($this, 'check_woocommerce'));
        
        // Enqueue media uploader for admin
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add upload directory filter
        add_filter('upload_dir', array($this, 'custom_upload_dir'));
        
        // Check and create tables if they don't exist
        add_action('admin_init', array($this, 'check_database_tables'));
        
        // Add custom styles to WooCommerce admin
        add_action('admin_head', array($this, 'add_woocommerce_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_woocommerce_admin_styles'));
    }
    
    public function init() {
        // Plugin initialization
        $this->create_default_product();
        $this->create_upload_directory();
    }
    
    public function check_database_tables() {
        global $wpdb;
        
        $products_table = $wpdb->prefix . 'codex_custom_products';
        $orders_table = $wpdb->prefix . 'pedal_orders';
        
        // Check if tables exist
        $products_exists = $wpdb->get_var("SHOW TABLES LIKE '$products_table'") == $products_table;
        $orders_exists = $wpdb->get_var("SHOW TABLES LIKE '$orders_table'") == $orders_table;
        
        if (!$products_exists || !$orders_exists) {
            $this->create_database_tables();
        }
    }
    
    public function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $pedal_dir = $upload_dir['basedir'] . '/pedal-customizer';
        
        if (!file_exists($pedal_dir)) {
            wp_mkdir_p($pedal_dir);
        }
        
        // Create artwork subdirectory
        $artwork_dir = $pedal_dir . '/artwork';
        if (!file_exists($artwork_dir)) {
            wp_mkdir_p($artwork_dir);
        }
        
        // Create products subdirectory
        $products_dir = $pedal_dir . '/products';
        if (!file_exists($products_dir)) {
            wp_mkdir_p($products_dir);
        }
    }
    
    public function custom_upload_dir($upload) {
        // Only modify upload directory for our plugin uploads
        if (isset($_POST['action']) && ($_POST['action'] === 'upload_artwork' || $_POST['action'] === 'upload_product_image')) {
            $subdir = $_POST['action'] === 'upload_artwork' ? '/pedal-customizer/artwork' : '/pedal-customizer/products';
            $upload['subdir'] = $subdir;
            $upload['path'] = $upload['basedir'] . $subdir;
            $upload['url'] = $upload['baseurl'] . $subdir;
        }
        return $upload;
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'pedal-customizer') !== false || strpos($hook, 'order-customizer') !== false) {
            wp_enqueue_media();
            wp_enqueue_script('pedal-admin-script', CODEX_CUSTOM_ORDER_PLUGIN_URL . 'assets/admin-script.js', array('jquery'), CODEX_CUSTOM_ORDER_VERSION, true);
            wp_localize_script('pedal-admin-script', 'pedal_admin_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pedal_admin_nonce')
            ));
        }
        
        // Also load styles on WooCommerce order pages
        if (strpos($hook, 'woocommerce_page_wc-orders') !== false || 
            strpos($hook, 'post.php') !== false || 
            strpos($hook, 'post-new.php') !== false) {
            wp_enqueue_style('pedal-customizer-admin-style', CODEX_CUSTOM_ORDER_PLUGIN_URL . 'assets/style.css', array(), CODEX_CUSTOM_ORDER_VERSION);
        }
    }
    
    public function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            echo '<div class="notice notice-error"><p><strong>Pedal Customizer:</strong> WooCommerce is required for this plugin to work properly.</p></div>';
        } else {
            // Check if default product is properly configured
            $product_id = get_option('CODEX_CUSTOM_ORDER_product_id');
            if (!$product_id) {
                echo '<div class="notice notice-warning"><p><strong>Pedal Customizer:</strong> Default product not configured. <a href="' . admin_url('admin.php?page=order-customizer-woocommerce') . '">Configure now</a></p></div>';
            } else {
                $product = wc_get_product($product_id);
                if (!$product || $product->get_status() !== 'publish') {
                    echo '<div class="notice notice-error"><p><strong>Pedal Customizer:</strong> Default product is invalid or not published. <a href="' . admin_url('admin.php?page=order-customizer-woocommerce') . '">Fix now</a></p></div>';
                }
            }
        }
    }
    
    public function create_default_product() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Check if default product exists
        $product_id = get_option('CODEX_CUSTOM_ORDER_product_id');
        if (!$product_id || !get_post($product_id)) {
            // Create default product
            $product = new WC_Product_Simple();
            $product->set_name('Custom Pedal Enclosure');
            $product->set_status('publish');
            $product->set_catalog_visibility('hidden');
            $product->set_price(75.00);
            $product->set_regular_price(75.00);
            $product->set_description('Customizable pedal enclosure with various options');
            $product->set_short_description('Custom pedal enclosure');
            $product->set_manage_stock(false);
            $product->set_stock_status('instock');
            $product->set_virtual(false);
            $product->set_downloadable(false);
            
            $product_id = $product->save();
            update_option('CODEX_CUSTOM_ORDER_product_id', $product_id);
            
            error_log('Pedal Customizer: Created default product with ID: ' . $product_id);
        } else {
            // Verify the existing product is still valid
            $product = wc_get_product($product_id);
            if (!$product || $product->get_status() !== 'publish') {
                // Recreate the product if it's invalid
                $product = new WC_Product_Simple();
                $product->set_name('Custom Pedal Enclosure');
                $product->set_status('publish');
                $product->set_catalog_visibility('hidden');
                $product->set_price(75.00);
                $product->set_regular_price(75.00);
                $product->set_description('Customizable pedal enclosure with various options');
                $product->set_short_description('Custom pedal enclosure');
                $product->set_manage_stock(false);
                $product->set_stock_status('instock');
                $product->set_virtual(false);
                $product->set_downloadable(false);
                
                $product_id = $product->save();
                update_option('CODEX_CUSTOM_ORDER_product_id', $product_id);
                
                error_log('Pedal Customizer: Recreated default product with ID: ' . $product_id);
            }
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('pedal-customizer-style', CODEX_CUSTOM_ORDER_PLUGIN_URL . 'assets/style.css', array(), CODEX_CUSTOM_ORDER_VERSION);
        wp_enqueue_script('pedal-customizer-script', CODEX_CUSTOM_ORDER_PLUGIN_URL . 'assets/script.js', array('jquery'), CODEX_CUSTOM_ORDER_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('pedal-customizer-script', 'pedal_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('CODEX_CUSTOM_ORDER_NONCE'),
            'cart_url' => class_exists('WooCommerce') ? wc_get_cart_url() : '',
            'checkout_url' => class_exists('WooCommerce') ? wc_get_checkout_url() : ''
        ));
        
        // Add custom styles for WooCommerce order display
        if (class_exists('WooCommerce') && (is_account_page() || is_admin())) {
            wp_add_inline_style('pedal-customizer-style', '
                .custom-artwork-display {
                    margin: 10px 0;
                    padding: 10px;
                    background: #f9f9f9;
                    border-radius: 4px;
                    border: 1px solid #e0e0e0;
                }
                .custom-artwork-display .artwork-preview {
                    text-align: center;
                }
                .custom-artwork-display .artwork-preview img {
                    display: block;
                    margin: 0 auto;
                }
                .custom-artwork-display .button {
                    margin-top: 5px;
                }
            ');
        }
    }
    
    public function render_customizer($atts) {
        $atts = shortcode_atts(array(
            'show_title' => 'true'
        ), $atts);
        
        ob_start();
        include CODEX_CUSTOM_ORDER_PLUGIN_PATH . 'templates/customizer-form.php';
        return ob_get_clean();
    }
    
    public function get_products_by_category() {
        check_ajax_referer('CODEX_CUSTOM_ORDER_NONCE', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'codex_custom_products';
        $category = sanitize_text_field($_POST['category']);
        
        $products = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE category = %s AND status = 'active' ORDER BY sort_order, name",
            $category
        ));
        
        wp_send_json_success($products);
    }

    public function debug_pedal_customizer() {
        check_ajax_referer('CODEX_CUSTOM_ORDER_NONCE', 'nonce');
        
        $debug_info = array(
            'woocommerce_active' => class_exists('WooCommerce'),
            'wc_cart_available' => function_exists('WC') && WC()->cart ? 'yes' : 'no',
            'default_product_id' => get_option('CODEX_CUSTOM_ORDER_product_id'),
            'default_product_exists' => false,
            'default_product_status' => 'N/A',
            'default_product_stock' => 'N/A',
            'default_product_purchasable' => 'N/A',
            'wc_errors' => array()
        );
        
        if (class_exists('WooCommerce')) {
            $product_id = get_option('CODEX_CUSTOM_ORDER_product_id');
            if ($product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $debug_info['default_product_exists'] = true;
                    $debug_info['default_product_status'] = $product->get_status();
                    $debug_info['default_product_stock'] = $product->get_stock_status();
                    $debug_info['default_product_purchasable'] = $product->is_purchasable() ? 'yes' : 'no';
                }
            }
            
            // Get WooCommerce errors
            $wc_errors = wc_get_notices('error');
            if (!empty($wc_errors)) {
                $debug_info['wc_errors'] = $wc_errors;
            }
        }
        
        wp_send_json_success($debug_info);
    }
    
    public function handle_product_image_upload() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'pedal_admin_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => 'No file uploaded or upload error'));
            return;
        }
        
        // Include required WordPress files
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        // Set upload overrides
        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'gif' => 'image/gif',
                'png' => 'image/png',
                'webp' => 'image/webp'
            )
        );
        
        // Handle the upload
        $movefile = wp_handle_upload($_FILES['product_image'], $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            wp_send_json_success(array(
                'url' => $movefile['url'],
                'filename' => basename($movefile['file']),
                'message' => 'Image uploaded successfully'
            ));
        } else {
            wp_send_json_error(array('message' => isset($movefile['error']) ? $movefile['error'] : 'Upload failed'));
        }
    }
    
    public function handle_artwork_upload() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'CODEX_CUSTOM_ORDER_NONCE')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['artwork']) || $_FILES['artwork']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => 'No file uploaded or upload error'));
            return;
        }
        
        // Include required WordPress files
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        // Set upload overrides with allowed file types
        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'gif' => 'image/gif',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'pdf' => 'application/pdf',
                'psd' => 'image/vnd.adobe.photoshop',
                'ai' => 'application/postscript',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'mp4' => 'video/mp4'
            )
        );
        
        // Handle the upload
        $movefile = wp_handle_upload($_FILES['artwork'], $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            wp_send_json_success(array(
                'file_url' => $movefile['url'],
                'file_name' => basename($movefile['file']),
                'message' => 'Artwork uploaded successfully'
            ));
        } else {
            wp_send_json_error(array('message' => isset($movefile['error']) ? $movefile['error'] : 'Upload failed'));
        }
    }
    
    public function add_to_cart_custom_pedal() {
        check_ajax_referer('CODEX_CUSTOM_ORDER_NONCE', 'nonce');
        
        if (!class_exists('WooCommerce')) {
            wp_send_json_error(array('message' => 'WooCommerce is not active. Please install and activate WooCommerce.'));
            return;
        }
        
        // Check if WooCommerce cart is available
        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(array('message' => 'WooCommerce cart is not available. Please refresh the page and try again.'));
            return;
        }
        
        $product_id = get_option('CODEX_CUSTOM_ORDER_product_id');
        if (!$product_id) {
            wp_send_json_error(array('message' => 'Default product not configured. Please go to Pedal Customizer → WooCommerce Settings and select a default product.'));
            return;
        }
        
        // Verify the product exists
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(array('message' => 'Selected product does not exist. Please check your WooCommerce Settings.'));
            return;
        }
        
        if (!$product->is_purchasable()) {
            wp_send_json_error(array('message' => 'Selected product is not purchasable. Please check if it\'s published and in stock.'));
            return;
        }
        
        // Validate required fields
        $required_fields = array('material', 'shape', 'size', 'cnc_milling', 'powder_coating');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array('message' => 'Missing required field: ' . ucfirst($field)));
                return;
            }
        }
        
        // Prepare custom data
        $custom_data = array(
            'material' => sanitize_text_field($_POST['material']),
            'shape' => sanitize_text_field($_POST['shape']),
            'size' => sanitize_text_field($_POST['size']),
            'cnc_milling' => sanitize_text_field($_POST['cnc_milling']),
            'powder_coating' => sanitize_text_field($_POST['powder_coating']),
            'custom_artwork' => sanitize_text_field($_POST['custom_artwork']),
            'custom_dimensions' => sanitize_text_field($_POST['custom_dimensions']),
            'total_price' => floatval($_POST['total_price']),
            'customer_email' => isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : ''
        );
        
        try {
            // Debug information
            error_log('Pedal Customizer Debug - Product ID: ' . $product_id);
            error_log('Pedal Customizer Debug - Product exists: ' . ($product ? 'yes' : 'no'));
            error_log('Pedal Customizer Debug - Product purchasable: ' . ($product && $product->is_purchasable() ? 'yes' : 'no'));
            error_log('Pedal Customizer Debug - Product status: ' . ($product ? $product->get_status() : 'N/A'));
            error_log('Pedal Customizer Debug - Product stock status: ' . ($product ? $product->get_stock_status() : 'N/A'));
            
            // Check if cart is initialized
            if (!WC()->cart) {
                wp_send_json_error(array('message' => 'WooCommerce cart is not initialized. Please refresh the page.'));
                return;
            }
            
            // Check if product is in stock
            if ($product->get_stock_status() === 'outofstock') {
                wp_send_json_error(array('message' => 'Selected product is out of stock. Please contact the store administrator.'));
                return;
            }
            
            // Check if product is published
            if ($product->get_status() !== 'publish') {
                wp_send_json_error(array('message' => 'Selected product is not published. Please contact the store administrator.'));
                return;
            }
            
            // Add to cart with more specific parameters
            $cart_item_key = WC()->cart->add_to_cart(
                $product_id,           // Product ID
                1,                     // Quantity
                0,                     // Variation ID (0 for simple products)
                array(),               // Variation data (empty for simple products)
                $custom_data           // Custom data
            );
            
            error_log('Pedal Customizer Debug - Cart item key: ' . ($cart_item_key ? $cart_item_key : 'false/empty'));
            
            if ($cart_item_key) {
                // Save order to our custom table as well
                $this->save_pedal_order_to_db($custom_data);
                
                wp_send_json_success(array(
                    'message' => 'Product added to cart successfully!',
                    'cart_url' => wc_get_cart_url(),
                    'checkout_url' => wc_get_checkout_url(),
                    'cart_count' => WC()->cart->get_cart_contents_count()
                ));
            } else {
                // Get WooCommerce errors
                $wc_errors = wc_get_notices('error');
                $error_message = 'Failed to add product to cart.';
                
                if (!empty($wc_errors)) {
                    $error_message .= ' WooCommerce errors: ' . implode(', ', $wc_errors);
                }
                
                wp_send_json_error(array('message' => $error_message));
            }
        } catch (Exception $e) {
            error_log('Pedal Customizer Exception: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Error adding to cart: ' . $e->getMessage()));
        }
    }
    
    public function save_pedal_order() {
        // Keep the original functionality for non-WooCommerce orders
        check_ajax_referer('CODEX_CUSTOM_ORDER_NONCE', 'nonce');
        
        $order_data = array(
            'material' => sanitize_text_field($_POST['material']),
            'shape' => sanitize_text_field($_POST['shape']),
            'size' => sanitize_text_field($_POST['size']),
            'cnc_milling' => sanitize_text_field($_POST['cnc_milling']),
            'powder_coating' => sanitize_text_field($_POST['powder_coating']),
            'custom_artwork' => sanitize_text_field($_POST['custom_artwork']),
            'custom_dimensions' => sanitize_text_field($_POST['custom_dimensions']),
            'total_price' => floatval($_POST['total_price']),
            'customer_email' => sanitize_email($_POST['customer_email'])
        );
        
        $result = $this->save_pedal_order_to_db($order_data);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Order saved successfully', 'order_id' => $result));
        } else {
            wp_send_json_error(array('message' => 'Failed to save order'));
        }
    }
    
    private function save_pedal_order_to_db($order_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pedal_orders';
        
        $order_data['order_date'] = current_time('mysql');
        
        $result = $wpdb->insert($table_name, $order_data);
        
        return $result ? $wpdb->insert_id : false;
    }
    
    public function add_custom_data_to_cart($cart_item_data, $product_id) {
        if (isset($_POST['pedal_custom_data'])) {
            $cart_item_data['pedal_custom_data'] = $_POST['pedal_custom_data'];
            $cart_item_data['unique_key'] = md5(microtime().rand());
        }
        return $cart_item_data;
    }
    
    public function display_custom_data_in_cart($item_data, $cart_item) {
        if (isset($cart_item['material'])) {
            $item_data[] = array(
                'key' => 'Material',
                'value' => ucfirst($cart_item['material'])
            );
        }
        
        if (isset($cart_item['shape'])) {
            $item_data[] = array(
                'key' => 'Shape',
                'value' => ucfirst($cart_item['shape'])
            );
        }
        
        if (isset($cart_item['size'])) {
            $item_data[] = array(
                'key' => 'Size',
                'value' => ucfirst($cart_item['size'])
            );
        }
        
        if (isset($cart_item['cnc_milling'])) {
            $item_data[] = array(
                'key' => 'CNC Milling',
                'value' => $this->get_cnc_name($cart_item['cnc_milling'])
            );
        }
        
        if (isset($cart_item['powder_coating'])) {
            $item_data[] = array(
                'key' => 'Powder Coating',
                'value' => $this->get_powder_coating_name($cart_item['powder_coating'])
            );
        }
        
        if (isset($cart_item['custom_artwork']) && !empty($cart_item['custom_artwork'])) {
            $item_data[] = array(
                'key' => 'Custom Artwork',
                'value' => $cart_item['custom_artwork']
            );
        }
        
        if (isset($cart_item['custom_dimensions']) && !empty($cart_item['custom_dimensions'])) {
            $dimensions = json_decode($cart_item['custom_dimensions'], true);
            if ($dimensions && (isset($dimensions['length']) || isset($dimensions['width']) || isset($dimensions['height']))) {
                $dim_text = sprintf('%smm x %smm x %smm', 
                    $dimensions['length'] ?? '0',
                    $dimensions['width'] ?? '0', 
                    $dimensions['height'] ?? '0'
                );
                $item_data[] = array(
                    'key' => 'Custom Dimensions',
                    'value' => $dim_text
                );
            }
        }
        
        return $item_data;
    }
    
    public function save_custom_data_to_order($item, $cart_item_key, $values, $order) {
        if (isset($values['material'])) {
            $item->add_meta_data('Material', ucfirst($values['material']));
        }
        
        if (isset($values['shape'])) {
            $item->add_meta_data('Shape', ucfirst($values['shape']));
        }
        
        if (isset($values['size'])) {
            $item->add_meta_data('Size', ucfirst($values['size']));
        }
        
        if (isset($values['cnc_milling'])) {
            $item->add_meta_data('CNC Milling', $this->get_cnc_name($values['cnc_milling']));
        }
        
        if (isset($values['powder_coating'])) {
            $item->add_meta_data('Powder Coating', $this->get_powder_coating_name($values['powder_coating']));
        }
        
        if (isset($values['custom_artwork']) && !empty($values['custom_artwork'])) {
            $item->add_meta_data('Custom Artwork', $values['custom_artwork']);
        }
        
        if (isset($values['custom_dimensions']) && !empty($values['custom_dimensions'])) {
            $dimensions = json_decode($values['custom_dimensions'], true);
            if ($dimensions && (isset($dimensions['length']) || isset($dimensions['width']) || isset($dimensions['height']))) {
                $dim_text = sprintf('%smm x %smm x %smm', 
                    $dimensions['length'] ?? '0',
                    $dimensions['width'] ?? '0', 
                    $dimensions['height'] ?? '0'
                );
                $item->add_meta_data('Custom Dimensions', $dim_text);
            }
        }
        
        if (isset($values['total_price'])) {
            $item->add_meta_data('Custom Total Price', '$' . number_format($values['total_price'], 2));
        }
    }
    
    public function update_cart_item_price($price, $cart_item, $cart_item_key) {
        if (isset($cart_item['total_price'])) {
            return wc_price($cart_item['total_price']);
        }
        return $price;
    }
    
    public function update_cart_item_price_before_calculate($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['total_price'])) {
                $cart_item['data']->set_price($cart_item['total_price']);
            }
        }
    }
    
    public function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create orders table
        $orders_table = $wpdb->prefix . 'pedal_orders';
        $orders_sql = "CREATE TABLE IF NOT EXISTS $orders_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            material varchar(100) DEFAULT '',
            shape varchar(100) DEFAULT '',
            size varchar(100) DEFAULT '',
            cnc_milling varchar(200) DEFAULT '',
            powder_coating varchar(100) DEFAULT '',
            custom_artwork varchar(255) DEFAULT '',
            custom_dimensions text DEFAULT '',
            total_price decimal(10,2) NOT NULL DEFAULT 0.00,
            order_date datetime DEFAULT CURRENT_TIMESTAMP,
            customer_email varchar(100) DEFAULT '',
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Create products table
        $products_table = $wpdb->prefix . 'codex_custom_products';
        $products_sql = "CREATE TABLE IF NOT EXISTS $products_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL DEFAULT '',
            category varchar(50) NOT NULL DEFAULT '',
            price decimal(10,2) NOT NULL DEFAULT 15.00,
            image_url varchar(500) DEFAULT '',
            description text DEFAULT '',
            status varchar(20) DEFAULT 'active',
            sort_order int DEFAULT 0,
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Execute the SQL
        $wpdb->query($orders_sql);
        $wpdb->query($products_sql);
        
        // Check if tables were created successfully
        $products_exists = $wpdb->get_var("SHOW TABLES LIKE '$products_table'") == $products_table;
        $orders_exists = $wpdb->get_var("SHOW TABLES LIKE '$orders_table'") == $orders_table;
        
        if ($products_exists && $orders_exists) {
            // Insert default products if table is empty
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $products_table");
            if ($count == 0) {
                $this->insert_default_products();
            }
        }
    }
    
    private function insert_default_products() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'codex_custom_products';
        
        $default_products = array(
            // Materials
            array('name' => 'Aluminum Enclosure', 'category' => 'material', 'price' => 15.00, 'sort_order' => 1, 'description' => 'High-quality aluminum enclosure'),
            array('name' => 'Plastic Enclosure', 'category' => 'material', 'price' => 10.00, 'sort_order' => 2, 'description' => 'Durable plastic enclosure'),
            array('name' => 'Steel Enclosure', 'category' => 'material', 'price' => 20.00, 'sort_order' => 3, 'description' => 'Heavy-duty steel enclosure'),
            
            // Shapes
            array('name' => 'Rectangle', 'category' => 'shape', 'price' => 15.00, 'sort_order' => 1, 'description' => 'Classic rectangular shape'),
            array('name' => 'Square', 'category' => 'shape', 'price' => 15.00, 'sort_order' => 2, 'description' => 'Compact square shape'),
            array('name' => 'Circular', 'category' => 'shape', 'price' => 15.00, 'sort_order' => 3, 'description' => 'Unique circular shape'),
            
            // CNC Milling
            array('name' => 'Standard Pedal Enclosure Template', 'category' => 'cnc_milling', 'price' => 15.00, 'sort_order' => 1, 'description' => 'Standard drilling template'),
            array('name' => 'Rectangle Enclosure with Predefined Hole Sizes', 'category' => 'cnc_milling', 'price' => 15.00, 'sort_order' => 2, 'description' => 'Rectangle with predefined holes'),
            array('name' => 'Circular Pedal Enclosure with Predefined Hole Placements', 'category' => 'cnc_milling', 'price' => 15.00, 'sort_order' => 3, 'description' => 'Circular with predefined holes'),
            
            // Powder Coating
            array('name' => 'Caveman Black', 'category' => 'powder_coating', 'price' => 15.00, 'sort_order' => 1, 'description' => 'Deep black finish'),
            array('name' => 'Pearl White', 'category' => 'powder_coating', 'price' => 15.00, 'sort_order' => 2, 'description' => 'Elegant pearl white'),
            array('name' => 'Black Jack', 'category' => 'powder_coating', 'price' => 15.00, 'sort_order' => 3, 'description' => 'Classic black jack finish'),
            array('name' => 'Zodiac White', 'category' => 'powder_coating', 'price' => 15.00, 'sort_order' => 4, 'description' => 'Pure zodiac white'),
            array('name' => 'Silk Satin Black', 'category' => 'powder_coating', 'price' => 15.00, 'sort_order' => 5, 'description' => 'Smooth silk satin black'),
        );
        
        foreach ($default_products as $product) {
            $wpdb->insert($table_name, $product);
        }
    }
    
    // Helper functions
    private function get_cnc_name($value) {
        $names = array(
            'standard' => 'Standard Pedal Enclosure Template',
            'rectangle_holes' => 'Rectangle Enclosure with Predefined Hole Sizes',
            'circular_holes' => 'Circular Pedal Enclosure with Predefined Hole Placements',
            'multi_hole' => 'Multi-Hole Drilling Template for Complex Pedal Designs',
            'square_standard' => 'Square Pedal Enclosure with Standard Holes',
            'compact' => 'Compact Pedal Enclosure (1590A) with Predefined Holes'
        );
        return isset($names[$value]) ? $names[$value] : $value;
    }
    
    private function get_powder_coating_name($value) {
        $names = array(
            'caveman_black' => 'Caveman Black',
            'pearl_white' => 'Pearl White',
            'black_jack' => 'Black Jack',
            'zodiac_white' => 'Zodiac White',
            'silk_satin_black' => 'Silk Satin Black',
            'soft_satin_white' => 'Soft Satin White',
            'spring_yellow' => 'Spring Yellow',
            'cosmic_yellow' => 'Cosmic Yellow',
            'spanish_gold' => 'Spanish Gold',
            'pumpkin_gold' => 'Pumpkin Gold',
            'anodized_red' => 'Anodized Red',
            'neon_pink' => 'Neon Pink',
            'tractor_green' => 'Tractor Green',
            'rifle_green' => 'Rifle Green',
            'altered_teal' => 'Altered Teal',
            'gojira' => 'Gojira',
            'rainbows_end' => 'Rainbow\'s End',
            'chameleon' => 'Chameleon Cherry Violet',
            'green_ice' => 'Green Ice'
        );
        return isset($names[$value]) ? $names[$value] : $value;
    }
    
    public function display_custom_artwork_in_order($item_id, $item, $order) {
        $custom_artwork = $item->get_meta('Custom Artwork');
        if ($custom_artwork && !empty($custom_artwork)) {
            $upload_dir = wp_upload_dir();
            $file_url = $upload_dir['baseurl'] . '/pedal-customizer/artwork/' . $custom_artwork;
            
            // Check if it's an image file
            $image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            $file_extension = strtolower(pathinfo($custom_artwork, PATHINFO_EXTENSION));
            
            if (in_array($file_extension, $image_extensions)) {
                echo '<div class="custom-artwork-display">';
                echo '<p><strong>Custom Artwork:</strong></p>';
                echo '<div class="artwork-preview">';
                echo '<img src="' . esc_url($file_url) . '" alt="Custom Artwork" style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; border-radius: 4px; margin: 5px 0;">';
                echo '<br><a href="' . esc_url($file_url) . '" target="_blank" class="button button-small">View Full Size</a>';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<p><strong>Custom Artwork:</strong> ' . esc_html($custom_artwork) . '</p>';
                echo '<a href="' . esc_url($file_url) . '" target="_blank" class="button button-small">View File</a>';
            }
        }
    }
    
    public function display_custom_artwork_in_admin_order($item_id, $item, $order) {
        $custom_artwork = $item->get_meta('Custom Artwork');
        if ($custom_artwork && !empty($custom_artwork)) {
            $upload_dir = wp_upload_dir();
            $file_url = $upload_dir['baseurl'] . '/pedal-customizer/artwork/' . $custom_artwork;
            
            // Check if it's an image file
            $image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            $file_extension = strtolower(pathinfo($custom_artwork, PATHINFO_EXTENSION));
            
            echo '<div class="custom-artwork-display" style="margin: 15px 0; padding: 15px; background: #f9f9f9; border-radius: 6px; border: 1px solid #e0e0e0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
            echo '<h4 style="margin: 0 0 10px 0; color: #23282d; font-size: 14px; font-weight: 600;">🎨 Custom Artwork</h4>';
            
            if (in_array($file_extension, $image_extensions)) {
                echo '<div class="artwork-preview" style="text-align: center;">';
                echo '<img src="' . esc_url($file_url) . '" alt="Custom Artwork" style="max-width: 300px; max-height: 200px; border: 2px solid #ddd; border-radius: 6px; margin: 10px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">';
                echo '<div style="margin-top: 10px;">';
                echo '<a href="' . esc_url($file_url) . '" target="_blank" class="button button-secondary" style="margin-right: 5px; text-decoration: none; padding: 5px 12px; font-size: 12px;">👁️ View Full Size</a>';
                echo '<a href="' . esc_url($file_url) . '" download="' . esc_attr($custom_artwork) . '" class="button button-secondary" style="text-decoration: none; padding: 5px 12px; font-size: 12px;">⬇️ Download</a>';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="artwork-preview" style="text-align: center;">';
                echo '<p style="margin: 5px 0; color: #666; font-size: 13px;"><strong>File:</strong> ' . esc_html($custom_artwork) . '</p>';
                echo '<div style="margin-top: 10px;">';
                echo '<a href="' . esc_url($file_url) . '" target="_blank" class="button button-secondary" style="margin-right: 5px; text-decoration: none; padding: 5px 12px; font-size: 12px;">👁️ View File</a>';
                echo '<a href="' . esc_url($file_url) . '" download="' . esc_attr($custom_artwork) . '" class="button button-secondary" style="text-decoration: none; padding: 5px 12px; font-size: 12px;">⬇️ Download</a>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
    }
    
    public function add_woocommerce_admin_styles() {
        echo '<style>
            .custom-artwork-display {
                margin: 10px 0;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 4px;
                border: 1px solid #e0e0e0;
            }
            .custom-artwork-display .artwork-preview {
                text-align: center;
            }
            .custom-artwork-display .artwork-preview img {
                display: block;
                margin: 0 auto;
            }
            .custom-artwork-display .button {
                margin-top: 5px;
            }
        </style>';
    }
    
    public function enqueue_woocommerce_admin_styles($hook) {
        // Load styles on WooCommerce order pages and admin pages
        if (strpos($hook, 'woocommerce') !== false || 
            strpos($hook, 'post.php') !== false || 
            strpos($hook, 'post-new.php') !== false ||
            strpos($hook, 'pedal-customizer') !== false) {
            wp_enqueue_style('pedal-customizer-admin-style', CODEX_CUSTOM_ORDER_PLUGIN_URL . 'assets/style.css', array(), CODEX_CUSTOM_ORDER_VERSION);
        }
    }
}

// Initialize the plugin
new PedalCustomizer();

// Include admin page
if (is_admin()) {
    include_once CODEX_CUSTOM_ORDER_PLUGIN_PATH . 'admin/admin-page.php';
}