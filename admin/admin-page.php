<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu
add_action('admin_menu', 'CODEX_CUSTOM_ORDER_ADMIN_MENU');

function CODEX_CUSTOM_ORDER_ADMIN_MENU() {
    add_menu_page(
        'Custom Order',
        'Custom Order',
        'manage_options',
        'order-customizer',
        'CODEX_CUSTOM_ORDER_ADMIN_PAGE',
        'dashicons-admin-customizer',
        30
    );
    
    add_submenu_page(
        'order-customizer',
        'Product Management',
        'Product Management',
        'manage_options',
        'order-customizer-products',
        'CODEX_CUSTOM_ORDER_PRODUCTS_PAGE'
    );
    
    add_submenu_page(
        'order-customizer',
        'WooCommerce Settings',
        'WooCommerce Settings',
        'manage_options',
        'order-customizer-woocommerce',
        'CODEX_CUSTOM_ORDER_WOOCOMMERCE_PAGE'
    );
}

function CODEX_CUSTOM_ORDER_ADMIN_PAGE() {
    ?>
    <div class="wrap">
        <h1>Customizer Order Settings</h1>
        
        <div class="card">
            <h2>Shortcode Usage</h2>
            <p>Use the following shortcode to display the pedal customizer on any page or post:</p>
            <code>[CODEX_CUSTOM_ORDER]</code>
            
            <h3>Shortcode Parameters:</h3>
            <ul>
                <li><strong>show_title</strong> - Show/hide the main title (default: true)</li>
            </ul>
            
            <p>Example with parameters:</p>
            <code>[CODEX_CUSTOM_ORDER show_title="false"]</code>
        </div>
        
        <div class="card">
            <h2>WooCommerce Integration</h2>
            <?php if (class_exists('WooCommerce')): ?>
                <p style="color: green;">✅ WooCommerce is active and integrated!</p>
                <p>The customizer will automatically add products to the WooCommerce cart with all customization details.</p>               
            <?php else: ?>
                <p style="color: red;">❌ WooCommerce is not active</p>
                <p>Install and activate WooCommerce to enable cart and checkout functionality.</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Setup Instructions</h2>
            <ol>
                <li>Install and activate WooCommerce (recommended)</li>
                <li>Go to <strong>Product Management</strong> to add products for each customization section</li>
                <li>Use the shortcode <code>[CODEX_CUSTOM_ORDER]</code> on any page where you want the customizer to appear</li>
                <li>Orders will be added to WooCommerce cart and also saved to the database for tracking</li>
            </ol>
        </div>
        
        <div class="card">
            <h2>Quick Stats</h2>
            <?php
            global $wpdb;
            $materials_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}codex_custom_products WHERE category = 'material' AND status = 'active'");
            $shapes_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}codex_custom_products WHERE category = 'shape' AND status = 'active'");
            $cnc_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}codex_custom_products WHERE category = 'cnc_milling' AND status = 'active'");
            $powder_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}codex_custom_products WHERE category = 'powder_coating' AND status = 'active'");
            $orders_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pedal_orders");
            ?>
            <ul>
                <li><strong>Materials:</strong> <?php echo $materials_count; ?> active</li>
                <li><strong>Shapes:</strong> <?php echo $shapes_count; ?> active</li>
                <li><strong>CNC Templates:</strong> <?php echo $cnc_count; ?> active</li>
                <li><strong>Powder Coatings:</strong> <?php echo $powder_count; ?> active</li>
                <li><strong>Total Orders:</strong> <?php echo $orders_count; ?></li>
            </ul>
        </div>
    </div>
    <?php
}

function CODEX_CUSTOM_ORDER_PRODUCTS_PAGE() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'codex_custom_products';
    
    // Handle form submissions
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_product' && wp_verify_nonce($_POST['pedal_nonce'], 'pedal_product_action')) {
            // Validate required fields
            $name = sanitize_text_field($_POST['name']);
            $category = sanitize_text_field($_POST['category']);
            $price = floatval($_POST['price']);
            
            if (empty($name)) {
                echo '<div class="notice notice-error"><p>Product name is required.</p></div>';
            } elseif (empty($category)) {
                echo '<div class="notice notice-error"><p>Category is required.</p></div>';
            } elseif ($price < 0) {
                echo '<div class="notice notice-error"><p>Price must be a positive number.</p></div>';
            } else {
                $result = $wpdb->insert(
                    $table_name,
                    array(
                        'name' => $name,
                        'category' => $category,
                        'price' => $price,
                        'image_url' => esc_url_raw($_POST['image_url']),
                        'description' => sanitize_textarea_field($_POST['description']),
                        'status' => 'active',
                        'sort_order' => intval($_POST['sort_order'])
                    ),
                    array('%s', '%s', '%f', '%s', '%s', '%s', '%d')
                );
                
                if ($result) {
                    echo '<div class="notice notice-success"><p>Product added successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Error adding product: ' . $wpdb->last_error . '</p></div>';
                }
            }
        }
        
        if ($_POST['action'] === 'update_product' && wp_verify_nonce($_POST['pedal_nonce'], 'pedal_product_action')) {
            // Validate required fields
            $name = sanitize_text_field($_POST['name']);
            $category = sanitize_text_field($_POST['category']);
            $price = floatval($_POST['price']);
            
            if (empty($name)) {
                echo '<div class="notice notice-error"><p>Product name is required.</p></div>';
            } elseif (empty($category)) {
                echo '<div class="notice notice-error"><p>Category is required.</p></div>';
            } elseif ($price < 0) {
                echo '<div class="notice notice-error"><p>Price must be a positive number.</p></div>';
            } else {
                $result = $wpdb->update(
                    $table_name,
                    array(
                        'name' => $name,
                        'category' => $category,
                        'price' => $price,
                        'image_url' => esc_url_raw($_POST['image_url']),
                        'description' => sanitize_textarea_field($_POST['description']),
                        'status' => sanitize_text_field($_POST['status']),
                        'sort_order' => intval($_POST['sort_order'])
                    ),
                    array('id' => intval($_POST['product_id'])),
                    array('%s', '%s', '%f', '%s', '%s', '%s', '%d'),
                    array('%d')
                );
                
                if ($result !== false) {
                    echo '<div class="notice notice-success"><p>Product updated successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Error updating product: ' . $wpdb->last_error . '</p></div>';
                }
            }
        }
    }
    
    // Handle delete
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $wpdb->delete($table_name, array('id' => intval($_GET['id'])));
        echo '<div class="notice notice-success"><p>Product deleted successfully.</p></div>';
    }
    
    // Get current category filter
    $current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
    
    // Get products
    $where_clause = $current_category ? $wpdb->prepare("WHERE category = %s", $current_category) : "";
    $products = $wpdb->get_results("SELECT * FROM $table_name $where_clause ORDER BY category, sort_order, name");
    
    // Get product for editing
    $edit_product = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        $edit_product = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_GET['id'])));
    }
    ?>
    
    <div class="wrap">
        <h1>Product Management</h1>
        
        <!-- Category Filter -->
        <div class="tablenav top">
            <div class="alignleft actions">
                <select id="category-filter" onchange="filterByCategory(this.value)">
                    <option value="">All Categories</option>
                    <option value="material" <?php selected($current_category, 'material'); ?>>Materials</option>
                    <option value="shape" <?php selected($current_category, 'shape'); ?>>Shapes</option>
                    <option value="cnc_milling" <?php selected($current_category, 'cnc_milling'); ?>>CNC Milling</option>
                    <option value="powder_coating" <?php selected($current_category, 'powder_coating'); ?>>Powder Coating</option>
                </select>
                <a href="?page=order-customizer-products&action=add" class="button button-primary">Add New Product</a>
            </div>
        </div>
        
        <?php if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')): ?>
        <!-- Add/Edit Product Form -->
        <div class="card" style="max-width: 800px;">
            <h2><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
            
            <form method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('pedal_product_action', 'pedal_nonce'); ?>
                <input type="hidden" name="action" value="<?php echo $edit_product ? 'update_product' : 'add_product'; ?>">
                <?php if ($edit_product): ?>
                <input type="hidden" name="product_id" value="<?php echo $edit_product->id; ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="name">Product Name *</label></th>
                        <td>
                            <input type="text" id="name" name="name" value="<?php echo $edit_product ? esc_attr($edit_product->name) : ''; ?>" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="category">Category *</label></th>
                        <td>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="material" <?php echo ($edit_product && $edit_product->category === 'material') ? 'selected' : ''; ?>>Material</option>
                                <option value="shape" <?php echo ($edit_product && $edit_product->category === 'shape') ? 'selected' : ''; ?>>Shape</option>
                                <option value="cnc_milling" <?php echo ($edit_product && $edit_product->category === 'cnc_milling') ? 'selected' : ''; ?>>CNC Milling</option>
                                <option value="powder_coating" <?php echo ($edit_product && $edit_product->category === 'powder_coating') ? 'selected' : ''; ?>>Powder Coating</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="price">Price ($) *</label></th>
                        <td>
                            <input type="number" id="price" name="price" value="<?php echo $edit_product ? esc_attr($edit_product->price) : '15.00'; ?>" step="0.01" min="0" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="image_url">Product Image</label></th>
                        <td>
                            <div class="image-upload-container">
                                <input type="url" id="image_url" name="image_url" value="<?php echo $edit_product ? esc_attr($edit_product->image_url) : ''; ?>" class="regular-text" placeholder="https://example.com/image.jpg">
                                <div class="image-upload-buttons">
                                    <button type="button" class="button" onclick="openMediaUploader()">📁 Choose from Media Library</button>
                                    <button type="button" class="button" onclick="document.getElementById('direct_upload').click()">📤 Upload New Image</button>
                                </div>
                                <input type="file" id="direct_upload" accept="image/*" style="display: none;" onchange="uploadDirectImage(this)">
                                
                                <div id="image-preview" style="margin-top: 10px;">
                                    <?php if ($edit_product && $edit_product->image_url): ?>
                                        <img src="<?php echo esc_url($edit_product->image_url); ?>" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                    <?php endif; ?>
                                </div>
                                
                                <p class="description">
                                    <strong>Three ways to add an image:</strong><br>
                                    1. <strong>Enter URL:</strong> Paste a direct link to an image<br>
                                    2. <strong>Media Library:</strong> Choose from existing WordPress media<br>
                                    3. <strong>Upload New:</strong> Upload a new image file (JPG, PNG, GIF, WebP)
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description">Description</label></th>
                        <td>
                            <textarea id="description" name="description" rows="3" class="large-text"><?php echo $edit_product ? esc_textarea($edit_product->description) : ''; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sort_order">Sort Order</label></th>
                        <td>
                            <input type="number" id="sort_order" name="sort_order" value="<?php echo $edit_product ? esc_attr($edit_product->sort_order) : '0'; ?>" class="small-text">
                            <p class="description">Lower numbers appear first.</p>
                        </td>
                    </tr>
                    <?php if ($edit_product): ?>
                    <tr>
                        <th scope="row"><label for="status">Status</label></th>
                        <td>
                            <select id="status" name="status">
                                <option value="active" <?php selected($edit_product->status, 'active'); ?>>Active</option>
                                <option value="inactive" <?php selected($edit_product->status, 'inactive'); ?>>Inactive</option>
                            </select>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>">
                    <a href="?page=order-customizer-products" class="button">Cancel</a>
                </p>
            </form>
        </div>
        
        <?php else: ?>
        <!-- Products List -->
        <?php if (empty($products)): ?>
            <p>No products found. <a href="?page=order-customizer-products&action=add">Add your first product</a>.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if ($product->image_url): ?>
                                    <img src="<?php echo esc_url($product->image_url); ?>" alt="<?php echo esc_attr($product->name); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 12px; border-radius: 4px; border: 1px dashed #ccc;">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo esc_html($product->name); ?></strong></td>
                            <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $product->category))); ?></td>
                            <td>$<?php echo number_format($product->price, 2); ?></td>
                            <td>
                                <span class="<?php echo $product->status === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo esc_html(ucfirst($product->status)); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($product->sort_order); ?></td>
                            <td>
                                <a href="?page=order-customizer-products&action=edit&id=<?php echo $product->id; ?>">Edit</a> |
                                <a href="?page=order-customizer-products&action=delete&id=<?php echo $product->id; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <style>
    .status-active { color: green; font-weight: bold; }
    .status-inactive { color: red; }
    .card { padding: 20px; background: white; margin: 20px 0; }
    .image-upload-container { max-width: 400px; }
    .image-upload-buttons { margin: 10px 0; }
    .image-upload-buttons .button { margin-right: 10px; margin-bottom: 5px; }
    #image-preview img { border-radius: 4px; }
    .image-upload-container .description { 
        background: #f9f9f9; 
        padding: 10px; 
        border-radius: 4px; 
        border-left: 3px solid #0073aa; 
        margin-top: 10px; 
    }
    .image-upload-container .description strong { color: #0073aa; }
    #image_url { margin-bottom: 5px; }
    </style>
    <?php
}

function CODEX_CUSTOM_ORDER_WOOCOMMERCE_PAGE() {
    if (!class_exists('WooCommerce')) {
        echo '<div class="wrap"><h1>WooCommerce Settings</h1><p>WooCommerce is not installed or activated.</p></div>';
        return;
    }
    
    // Handle form submission
    if (isset($_POST['submit']) && wp_verify_nonce($_POST['CODEX_CUSTOM_ORDER_NONCE'], 'CODEX_CUSTOM_ORDER_SETTINGS')) {
        $product_id = intval($_POST['product_id']);
        update_option('CODEX_CUSTOM_ORDER_product_id', $product_id);
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    $current_product_id = get_option('CODEX_CUSTOM_ORDER_product_id');
    
    // Get all products
    $products = wc_get_products(array(
        'limit' => -1,
        'status' => 'publish'
    ));
    ?>
    
    <div class="wrap">
        <h1>WooCommerce Settings</h1>
        
        <div class="card">
            <h2>Default Product Configuration</h2>
            <p>Select the default WooCommerce product that will be used when customers add customizations to their cart.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('CODEX_CUSTOM_ORDER_SETTINGS', 'CODEX_CUSTOM_ORDER_NONCE'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="product_id">Default Product</label></th>
                        <td>
                            <select id="product_id" name="product_id" required>
                                <option value="">Select a Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product->get_id(); ?>" <?php selected($current_product_id, $product->get_id()); ?>>
                                        <?php echo esc_html($product->get_name()); ?> (ID: <?php echo $product->get_id(); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                This product will be added to the cart with all customization details as meta data.
                                <br><strong>Important:</strong> Make sure this product exists and is published.
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Save Settings">
                </p>
            </form>
        </div>
        
        <div class="card">
            <h2>How it Works</h2>
            <ol>
                <li>Customer customizes their pedal using the customizer interface</li>
                <li>When they click "Continue to Checkout", the selected product is added to WooCommerce cart</li>
                <li>All customization details are saved as product meta data</li>
                <li>Customer proceeds through normal WooCommerce checkout</li>
                <li>Order details include all customization information</li>
            </ol>
        </div>
        
        <div class="card">
            <h2>Troubleshooting</h2>
            <ul>
                <li><strong>No products available?</strong> Create a product in WooCommerce first</li>
                <li><strong>Add to cart not working?</strong> Make sure the selected product is published and in stock</li>
                <li><strong>Customization details not showing?</strong> Check that the product ID is correctly set above</li>
            </ul>
        </div>
    </div>
    
    <style>
    .card { padding: 20px; background: white; margin: 20px 0; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
    .form-table th { width: 200px; }
    </style>
    <?php
}