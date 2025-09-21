# Pedal Customizer WordPress Plugin with WooCommerce Integration

A comprehensive WordPress plugin for pedal enclosure customization with full WooCommerce integration, interactive options, and order management.

## Features

- **Interactive Customization Interface**: Users can select materials, shapes, sizes, CNC milling options, and powder coating colors
- **WooCommerce Integration**: Seamlessly adds customized products to WooCommerce cart
- **File Upload**: Custom artwork upload functionality
- **Real-time Price Calculation**: Dynamic pricing based on selected options
- **Order Management**: Save orders to database with admin interface
- **Responsive Design**: Works on all device sizes
- **Shortcode Support**: Easy integration with any page or post

## Custom Artwork Viewing

### Plugin Database Orders
- **Location**: Pedal Customizer → Orders in WordPress admin
- **Features**:
  - View custom artwork images directly in order details modal
  - Download artwork files
  - Preview images with full-size viewing option
  - Support for various file formats (JPG, PNG, GIF, PDF, PSD, AI, etc.)
  - Visual indicator in orders table showing which orders have artwork

### WooCommerce Orders
- **Location**: WooCommerce → Orders in WordPress admin
- **Features**:
  - Custom artwork displayed directly in order line items
  - Image preview for supported formats
  - Download links for all file types
  - Full-size image viewing
  - Works in both admin and customer order views

### File Storage
- Custom artwork files are stored in: `/wp-content/uploads/pedal-customizer/artwork/`
- Supported formats: JPEG, PNG, GIF, MP4, PDF, PSD, AI, Word, PPT
- Files are organized and easily accessible for admin review

## Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+
- **WooCommerce 3.0+** (Required for cart/checkout functionality)

## Installation

1. Upload the `pedal-customizer-plugin` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Install and activate WooCommerce if not already installed
4. Copy your product images to the `assets/images/` directory (see image requirements below)
5. Configure the default product in the admin settings
6. Use the shortcode `[pedal_customizer]` on any page where you want the customizer to appear

## WooCommerce Integration

### How It Works

1. **Product Creation**: The plugin automatically creates a default "Custom Pedal Enclosure" product
2. **Customization**: Users customize their pedal using the interactive interface
3. **Add to Cart**: When users click "Add to Cart & Checkout", the product is added to WooCommerce cart with all customization details
4. **Checkout**: Users proceed through the normal WooCommerce checkout process
5. **Order Details**: All customization information is saved as order meta data

### Admin Configuration

1. Go to **Pedal Customizer → WooCommerce Settings**
2. Select or create a default product for customizations
3. Configure product pricing and details in WooCommerce

### Cart Integration

The plugin adds the following information to cart items:
- Material selection
- Shape and size
- CNC milling template
- Powder coating color
- Custom artwork filename
- Custom dimensions (if specified)
- Total calculated price

## Shortcode Usage

### Basic Usage
```
[pedal_customizer]
```

### With Parameters
```
[pedal_customizer show_title="false"]
```

### Parameters
- `show_title` - Show/hide the main title and description (default: true)

## Image Requirements

The plugin requires product images to be placed in the `assets/images/` directory. See `assets/images/placeholder.txt` for a complete list of required images.

### Required Images:

#### Enclosure Materials:
- `aluminum-enclosure.png`
- `plastic-enclosure.png`
- `steel-enclosure.png`

#### Shapes:
- `rectangle-shape.png`
- `square-shape.png`
- `circular-shape.png`

#### CNC Templates:
- `cnc-standard.png`
- `cnc-rectangle.png`
- `cnc-circular.png`

#### Powder Coating Colors:
- `powder-caveman_black.png` through `powder-green_ice.png` (19 total colors)

## Admin Interface

The plugin includes a comprehensive admin interface:

### Main Settings Page
- Shortcode usage instructions
- WooCommerce integration status
- Setup instructions

### Orders Page
- View all customization orders
- Order details modal
- Delete orders functionality
- Note: WooCommerce orders are managed separately in WooCommerce → Orders

### WooCommerce Settings Page
- Configure default product
- View current product settings
- Integration instructions

## Customization

### Pricing
Modify prices in the JavaScript configuration object in `assets/script.js`:

```javascript
basePrices: {
    material: {
        aluminum: 15.00,
        plastic: 10.00,
        steel: 20.00
    },
    // ... other pricing options
}
```

### Styling
Customize the appearance by modifying `assets/style.css`. The plugin uses CSS custom properties for easy theming.

### Adding Options
To add new customization options:

1. Update the PHP template (`templates/customizer-form.php`)
2. Add corresponding JavaScript handling (`assets/script.js`)
3. Update the WooCommerce integration functions
4. Add new images to the assets directory

## WooCommerce Hooks Used

- `woocommerce_add_cart_item_data` - Add custom data to cart items
- `woocommerce_get_item_data` - Display custom data in cart
- `woocommerce_checkout_create_order_line_item` - Save custom data to orders
- `woocommerce_cart_item_price` - Update cart item prices
- `woocommerce_before_calculate_totals` - Update prices before calculation

## Fallback Functionality

If WooCommerce is not installed:
- Orders are saved to the plugin's custom database table
- Basic order management is available in the admin
- Users see a notice that WooCommerce is recommended

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Troubleshooting

### WooCommerce Not Detected
- Ensure WooCommerce is installed and activated
- Check for plugin conflicts
- Verify WooCommerce version compatibility

### Images Not Loading
- Verify images are uploaded to the correct directory
- Check file permissions
- Ensure image filenames match the requirements

### Cart Issues
- Clear WooCommerce cache
- Check for theme conflicts
- Verify WooCommerce settings

## Support

For support and customization requests, please contact the plugin developer.

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release with WooCommerce integration
- Interactive customization interface
- Real-time pricing
- File upload functionality
- Admin order management
- Responsive design