<div id="pedal-customizer" class="pedal-customizer-container">
    <div class="customizer-layout">
        <div class="customizer-main">
            <form id="pedal-customizer-form" class="customizer-form">
                <!-- Select Your Pedal Enclosure -->
                <section class="form-section">
                    <h3 class="section-title">Select Your Pedal Enclosure</h3>
                    <div class="option-grid enclosure-grid" id="material-products">
                        <!-- Products will be loaded dynamically -->
                    </div>
                </section>

                <!-- Choose Shape & Size -->
                <section class="form-section">
                    <h3 class="section-title">Choose Shape & Size</h3>
                    <div class="shape-size-container">
                        <!-- Shape Options -->
                        <div class="option-grid shape-grid" id="shape-products">
                            <!-- Products will be loaded dynamically -->
                        </div>

                        <!-- Size Options -->
                        <div class="size-options">
                            <div class="size-item">
                                <input type="radio" name="size" value="small" id="size-small">
                                <label for="size-small">
                                    <span class="size-name">Small</span>
                                    <span class="size-dimensions">(120mm x 80mm x 40mm)</span>
                                </label>
                            </div>
                            <div class="size-item">
                                <input type="radio" name="size" value="medium" id="size-medium" checked>
                                <label for="size-medium">
                                    <span class="size-name">Medium</span>
                                    <span class="size-dimensions">(59mm x 94mm x 32mm)</span>
                                </label>
                            </div>
                            <div class="size-item">
                                <input type="radio" name="size" value="large" id="size-large">
                                <label for="size-large">
                                    <span class="size-name">Large</span>
                                    <span class="size-dimensions">(170mm x 130mm x 40mm)</span>
                                </label>
                            </div>
                            <div class="size-item">
                                <input type="radio" name="size" value="custom" id="size-custom">
                                <label for="size-custom">
                                    <span class="size-name">Custom</span>
                                    <span class="size-dimensions">(Enter your dimensions)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Custom Size -->
                        <div class="custom-size-section">
                            <h4>Custom <span class="custom-price">+$15.00</span></h4>
                            <div class="custom-inputs">
                                <div class="input-group">
                                    <label>Length (mm)</label>
                                    <input type="number" name="custom_length" min="1" placeholder="Enter length"
                                        class="custom-dimension-input">
                                </div>
                                <div class="input-group">
                                    <label>Width (mm)</label>
                                    <input type="number" name="custom_width" min="1" placeholder="Enter width"
                                        class="custom-dimension-input">
                                </div>
                                <div class="input-group">
                                    <label>Height (mm)</label>
                                    <input type="number" name="custom_height" min="1" placeholder="Enter height"
                                        class="custom-dimension-input">
                                </div>
                            </div>
                            <div class="custom-size-note">
                                <small>💡 Enter your custom dimensions above to automatically select Custom size</small>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- CNC Milling -->
                <section class="form-section">
                    <h3 class="section-title">CNC Milling</h3>
                    <div class="cnc-grid" id="cnc_milling-products">
                        <!-- Products will be loaded dynamically -->
                    </div>
                </section>

                <!-- Powder Coating -->
                <section class="form-section">
                    <h3 class="section-title">Powder Coating <span class="section-price">+$15.00</span></h3>
                    <div class="powder-coating-grid" id="powder_coating-products">
                        <!-- Products will be loaded dynamically -->
                    </div>
                </section>

                <!-- Custom Artwork -->
                <section class="form-section">
                    <h3 class="section-title image-title">Custom Artwork <span class="section-price">+$15.00</span></h3>
                    <div class="artwork-upload-area" id="artwork-upload">
                        <div class="upload-content">
                            <div class="upload-icon">
                                <!-- SVG Folder Icon -->
                                <svg width="68" height="50" viewBox="0 0 68 50" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M53.1328 49.7227H42.1422H39.1827H38.5435V34.9806H43.3649C44.5877 34.9806 45.3102 33.5911 44.5877 32.5907L35.2088 19.6132C34.6114 18.7795 33.3747 18.7795 32.7773 19.6132L23.3984 32.5907C22.6759 33.5911 23.3846 34.9806 24.6212 34.9806H29.4426V49.7227H28.8034H25.8439H13.1026C5.80793 49.3197 0 42.4975 0 35.1056C0 30.0063 2.76502 25.56 6.86392 23.1563C6.48876 22.142 6.29424 21.0582 6.29424 19.9188C6.29424 14.7084 10.5043 10.4983 15.7148 10.4983C16.8402 10.4983 17.924 10.6929 18.9383 11.068C21.9534 4.67651 28.4561 0.244141 36.0147 0.244141C45.7965 0.258035 53.8553 7.74721 54.7724 17.2928C62.2893 18.585 68 25.5461 68 33.4244C68 41.8445 61.4418 49.1391 53.1328 49.7227Z"
                                        fill="#411530" />
                                </svg>
                            </div>
                            <div class="upload-text">
                                <span class="main-text">Drag & drop files or <label for="artwork-file"
                                        class="browse-link">Browse</label></span>
                                <span class="format-text">Supported formats: JPEG, PNG, GIF, MP4, PDF, PSD, AI, Word,
                                    PPT</span>
                            </div>
                        </div>
                        <input type="file" id="artwork-file" name="artwork"
                            accept=".jpg,.jpeg,.png,.gif,.mp4,.pdf,.psd,.ai,.doc,.docx,.ppt,.pptx"
                            style="display: none;">
                        <div class="uploaded-file" id="uploaded-file" style="display: none;">
                            <span class="file-name"></span>
                            <button type="button" class="remove-file">×</button>
                        </div>
                    </div>
                </section>

            </form>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="order-summary">
            <div class="summary-card">
                <h3 class="summary-title">Review Your Custom Order</h3>

                <div class="summary-items">
                    <div class="summary-item">
                        <div class="item-info">
                            <span class="item-label">Material</span>
                            <span class="item-value" id="summary-material">Loading...</span>
                        </div>
                        <span class="item-price" id="price-material">$0.00</span>
                    </div>

                    <div class="summary-item">
                        <div class="item-info">
                            <span class="item-label">Shape</span>
                            <span class="item-value" id="summary-shape">Loading...</span>
                        </div>
                        <span class="item-price" id="price-shape">$0.00</span>
                    </div>

                    <div class="summary-item">
                        <div class="item-info">
                            <span class="item-label">Size</span>
                            <span class="item-value" id="summary-size">Medium</span>
                        </div>
                        <span class="item-price" id="price-size">$15.00</span>
                    </div>

                    <div class="summary-item">
                        <div class="item-info">
                            <span class="item-label">CNC Milling</span>
                            <span class="item-value" id="summary-cnc">Loading...</span>
                        </div>
                        <span class="item-price" id="price-cnc">$0.00</span>
                    </div>

                    <div class="summary-item">
                        <div class="item-info">
                            <span class="item-label">Powder Coating</span>
                            <span class="item-value" id="summary-powder">Loading...</span>
                        </div>
                        <span class="item-price" id="price-powder">$0.00</span>
                    </div>

                    <div class="summary-item" id="artwork-summary" style="display: none;">
                        <div class="item-info">
                            <span class="item-label">Custom Artwork</span>
                            <span class="item-value" id="summary-artwork"></span>
                        </div>
                        <span class="item-price" id="price-artwork">$15.00</span>
                    </div>
                </div>

                <div class="summary-separator"></div>

                <div class="summary-total">
                    <span class="total-label">Subtotal</span>
                    <span class="total-price" id="total-price">$0.00 USD</span>
                </div>

                <?php if (class_exists('WooCommerce')): ?>
                <button type="button" id="woocommerce-checkout-btn" class="checkout-button">
                    Continue to Checkout
                </button>
                <button type="button" id="debug-btn" class="button" style="margin-top: 10px; background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                    Debug Info
                </button>
                <?php else: ?>
                <button type="button" id="checkout-btn" class="checkout-button">
                    Continue to Checkout
                </button>
                <p style="color: #ff6b6b; font-size: 12px; margin-top: 8px; text-align: center;">
                    WooCommerce not detected. Orders will be saved to database only.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.no-image {
    width: 100%;
    height: 100%;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: #666;
}

/* Powder Coating Item Styling */
.powder-color-item {
    cursor: pointer;
    padding: 10px 5px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    transition: all 0.3s ease;
    position: relative;
    text-align: center;
}

.powder-color-item:hover {
    border-color: #d1512d;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(209, 81, 45, 0.15);
}

.powder-color-item.selected {
    border-color: #d1512d;
    box-shadow: 0 4px 12px rgba(209, 81, 45, 0.2);
    transform: translateY(-2px);
}

.powder-color-item.selected::before {
    content: "✓";
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    background: #d1512d;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.powder-color-item input[type="radio"] {
    display: none;
}

.powder-color-item .color-sample {
    width: 100%;
    height: 80px;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.powder-color-item .color-sample img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    cursor: pointer;
}

.powder-color-item .color-name {
    font-size: 12px;
    font-weight: 500;
    color: #2a2a2a;
    display: block;
    margin-top: 5px;
    cursor: pointer;
}

.powder-color-item.selected .color-name {
    color: #d1512d;
    font-weight: 600;
}

/* Additional powder coating enhancements */
.powder-color-item:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(209, 81, 45, 0.1);
}

.powder-color-item.selected:active {
    transform: translateY(-1px);
}

/* Animation for selection */
@keyframes powderSelect {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }

    100% {
        transform: scale(1);
    }
}

.powder-color-item.selected {
    animation: powderSelect 0.3s ease-in-out;
}

/* Custom Size Section Styling */
.custom-size-section {
    margin-top: 20px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    border: 2px solid #e0e0e0;
    transition: all 0.3s ease;
    opacity: 0.7;
}

.custom-size-section.active {
    opacity: 1;
    border-color: #d1512d;
    background: #fff3f0;
    box-shadow: 0 2px 8px rgba(209, 81, 45, 0.1);
}

.custom-size-section:hover {
    border-color: #d1512d;
    box-shadow: 0 2px 8px rgba(209, 81, 45, 0.1);
}

.custom-size-section h4 {
    margin: 0 0 15px 0;
    color: #2a2a2a;
    font-size: 16px;
    font-weight: 600;
}

.custom-price {
    color: #d1512d;
    font-weight: 500;
}

.custom-inputs {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin-bottom: 10px;
}

.input-group {
    display: flex;
    flex-direction: column;
}

.input-group label {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
    font-weight: 500;
}

.custom-dimension-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.custom-dimension-input:focus {
    outline: none;
    border-color: #d1512d;
    box-shadow: 0 0 0 2px rgba(209, 81, 45, 0.1);
}

.custom-dimension-input::placeholder {
    color: #999;
    font-size: 12px;
}

.custom-size-note {
    margin-top: 10px;
    padding: 8px 12px;
    background: #e8f4fd;
    border-radius: 4px;
    border-left: 3px solid #0073aa;
}

.custom-size-note small {
    color: #0073aa;
    font-size: 11px;
    line-height: 1.4;
}

/* Size selection styling */
.size-item {
    cursor: pointer;
    transition: all 0.3s ease;
}

.size-item:hover {
    background: #f5f5f5;
}

.size-item.selected {
    background: #fff3f0;
    border-color: #d1512d;
}
</style>