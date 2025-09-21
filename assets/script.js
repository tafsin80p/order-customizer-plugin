jQuery(document).ready(function ($) {
    "use strict";

    const PedalCustomizer = {
        config: {
            basePrices: {
                size: { small: 15.0, medium: 15.0, large: 15.0, custom: 15.0 },
                custom_artwork: 15.0,
            },
            currentSelection: {
                material: { id: null, name: null },
                shape: { id: null, name: null },
                size: "medium",
                cnc_milling: { id: null, name: null },
                powder_coating: { id: null, name: null },
                custom_artwork: null,
                custom_dimensions: null,
            },
            products: {
                material: [],
                shape: [],
                cnc_milling: [],
                powder_coating: [],
            },
        },

        init: function () {
            this.loadProducts();
            this.bindEvents();
            this.updateSummary();
            this.initFileUpload();
        },

        loadProducts: function () {
            const self = this;
            const categories = ["material", "shape", "cnc_milling", "powder_coating"];
            categories.forEach(function (category) {
                $.ajax({
                    url: pedal_ajax.ajax_url,
                    type: "POST",
                    data: {
                        action: "get_products_by_category",
                        nonce: pedal_ajax.nonce,
                        category: category,
                    },
                    success: function (response) {
                        if (response.success) {
                            self.config.products[category] = response.data;
                            self.renderProducts(category, response.data);
                            if (response.data.length > 0 && !self.config.currentSelection[category].id) {
                                self.config.currentSelection[category] = {
                                    id: response.data[0].id,
                                    name: response.data[0].name,
                                };
                                self.updateSummary();
                            }
                        }
                    },
                });
            });
        },

        renderProducts: function (category, products) {
            const container = $("#" + category + "-products");
            if (!container.length) return;

            let html = "";
            products.forEach(function (product, index) {
                const isSelected = index === 0 ? "selected" : "";
                const isChecked = index === 0 ? "checked" : "";
                if (category === "powder_coating") {
                    html += `
                        <div class="powder-color-item ${isSelected}" data-option="${category}" data-value="${product.id}" data-price="${product.price}">
                            <input type="radio" name="${category}" value="${product.id}" id="${category}-${product.id}" ${isChecked}>
                            <label for="${category}-${product.id}">
                                <div class="color-sample">
                                    ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}">` : '<div class="no-image">No Image</div>'}
                                </div>
                                <span class="color-name">${product.name}</span>
                            </label>
                        </div>`;
                } else {
                    html += `
                        <div class="option-item ${isSelected}" data-option="${category}" data-value="${product.id}" data-price="${product.price}">
                            <div class="option-image">
                                ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}">` : '<div class="no-image">No Image</div>'}
                            </div>
                            <div class="option-details">
                                <h4>${product.name}</h4>
                                <span class="price">$${parseFloat(product.price).toFixed(2)}</span>
                            </div>
                            <div class="radio-button">
                                <input type="radio" name="${category}" value="${product.id}" id="${category}-${product.id}" ${isChecked}>
                                <label for="${category}-${product.id}"></label>
                            </div>
                        </div>`;
                }
            });

            container.html(html);
        },

        bindEvents: function () {
            const self = this;

            $(document).on("change", 'input[type="radio"]', function () {
                self.handleOptionChange($(this));
            });

            $(document).on("click", ".powder-color-item", function () {
                self.handlePowderCoatingSelection($(this));
            });

            // Add click handler for powder color items to ensure proper selection
            $(document).on("click", ".powder-color-item", function (e) {
                e.preventDefault();
                self.handlePowderCoatingSelection($(this));
            });

            $(document).on("input", 'input[name^="custom_"]', function () {
                self.handleCustomDimensions();
            });

            $(document).on("change", 'input[name="size"]', function () {
                self.handleSizeChange($(this));
            });

            $(document).on("click", "#woocommerce-checkout-btn", function () {
                self.handleWooCommerceCheckout();
            });

            $(document).on("click", "#checkout-btn", function () {
                self.handleCheckout();
            });

            $(document).on("click", "#debug-btn", function () {
                self.debugCurrentSelection();
            });

            $(document).on("click", ".option-item", function () {
                const radio = $(this).find('input[type="radio"]');
                if (radio.length) {
                    radio.prop("checked", true).trigger("change");
                }
            });
        },

        handleOptionChange: function ($input) {
            const name = $input.attr("name");
            const value = $input.val();
            const selectedProduct = this.getProductById(name, value);
            this.config.currentSelection[name] = selectedProduct
                ? { id: selectedProduct.id, name: selectedProduct.name }
                : { id: value, name: null };

            $input.closest(".option-grid, .size-options, .powder-coating-grid")
                .find(".option-item, .size-item, .powder-color-item").removeClass("selected");

            $input.closest(".option-item, .size-item, .powder-color-item").addClass("selected");

            this.updateSummary();
        },

        handlePowderCoatingSelection: function ($item) {
            const value = $item.data("value");
            const radio = $item.find('input[type="radio"]');

            // Remove selected class from all powder color items
            $(".powder-color-item").removeClass("selected");
            
            // Add selected class to clicked item
            $item.addClass("selected");
            
            // Check the radio button
            radio.prop("checked", true);

            const selectedProduct = this.getProductById("powder_coating", value);
            this.config.currentSelection.powder_coating = selectedProduct
                ? { id: selectedProduct.id, name: selectedProduct.name }
                : { id: value, name: null };

            this.updateSummary();
        },

        handleCustomDimensions: function () {
            const length = $('input[name="custom_length"]').val();
            const width = $('input[name="custom_width"]').val();
            const height = $('input[name="custom_height"]').val();

            if (length || width || height) {
                this.config.currentSelection.custom_dimensions = {
                    length: length || 0,
                    width: width || 0,
                    height: height || 0,
                };
                this.config.currentSelection.size = "custom";
                
                // Update the size radio button to custom
                $('input[name="size"][value="custom"]').prop("checked", true);
                $('.size-item').removeClass('selected');
                $('input[name="size"][value="custom"]').closest('.size-item').addClass('selected');
                
                // Activate custom size section
                $('.custom-size-section').addClass('active');
            } else {
                this.config.currentSelection.custom_dimensions = null;
                // If no custom dimensions, revert to medium if no other size is selected
                if (this.config.currentSelection.size === "custom") {
                    this.config.currentSelection.size = "medium";
                    $('input[name="size"][value="medium"]').prop("checked", true);
                    $('.size-item').removeClass('selected');
                    $('input[name="size"][value="medium"]').closest('.size-item').addClass('selected');
                    
                    // Deactivate custom size section
                    $('.custom-size-section').removeClass('active');
                }
            }

            this.updateSummary();
        },

        handleSizeChange: function ($input) {
            const value = $input.val();
            this.config.currentSelection.size = value;
            
            // Handle custom size selection
            if (value === "custom") {
                $('.custom-size-section').addClass('active');
                // Focus on first custom dimension input
                $('input[name="custom_length"]').focus();
            } else {
                $('.custom-size-section').removeClass('active');
                // Clear custom dimensions when switching to standard sizes
                this.config.currentSelection.custom_dimensions = null;
                $('input[name="custom_length"]').val('');
                $('input[name="custom_width"]').val('');
                $('input[name="custom_height"]').val('');
            }
            
            this.updateSummary();
        },

        handleWooCommerceCheckout: function () {
            const self = this;

            // Validate required selections
            const requiredFields = ['material', 'shape', 'cnc_milling', 'powder_coating'];
            const missingFields = [];
            
            requiredFields.forEach(field => {
                if (!self.config.currentSelection[field] || !self.config.currentSelection[field].name) {
                    missingFields.push(field);
                }
            });
            
            if (missingFields.length > 0) {
                self.showMessage("Please select: " + missingFields.join(', '), "error");
                return;
            }

            const orderData = {
                action: "add_to_cart_custom_pedal",
                nonce: pedal_ajax.nonce,
                material: self.config.currentSelection.material.name,
                shape: self.config.currentSelection.shape.name,
                size: self.config.currentSelection.size,
                cnc_milling: self.config.currentSelection.cnc_milling.name,
                powder_coating: self.config.currentSelection.powder_coating.name,
                custom_artwork: self.config.currentSelection.custom_artwork || "",
                custom_dimensions: JSON.stringify(self.config.currentSelection.custom_dimensions || {}),
                total_price: self.getTotalPrice(),
            };

            console.log('Sending order data:', orderData);

            $("#woocommerce-checkout-btn").prop("disabled", true).text("Adding to Cart...");

            $.ajax({
                url: pedal_ajax.ajax_url,
                type: "POST",
                data: orderData,
                success: function (response) {
                    console.log('AJAX response:', response);
                    if (response.success) {
                        self.showMessage("Product added to cart successfully!", "success");
                        if ($(".cart-contents-count").length) {
                            $(".cart-contents-count").text(response.data.cart_count);
                        }
                        setTimeout(function () {
                            window.location.href = response.data.checkout_url;
                        }, 1500);
                    } else {
                        self.showMessage("Error adding to cart: " + (response.data ? response.data.message : 'Unknown error'), "error");
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', {xhr, status, error});
                    let errorMessage = "Error adding to cart. Please try again.";
                    
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage = "Error: " + xhr.responseJSON.data.message;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.data && response.data.message) {
                                errorMessage = "Error: " + response.data.message;
                            }
                        } catch (e) {
                            console.error('Failed to parse error response:', e);
                        }
                    }
                    
                    self.showMessage(errorMessage, "error");
                },
                complete: function () {
                    $("#woocommerce-checkout-btn").prop("disabled", false).text("Continue to Checkout");
                },
            });
        },

        handleCheckout: function () {
            const self = this;
            const orderData = {
                action: "save_pedal_order",
                nonce: pedal_ajax.nonce,
                material: self.config.currentSelection.material.name,
                shape: self.config.currentSelection.shape.name,
                size: self.config.currentSelection.size,
                cnc_milling: self.config.currentSelection.cnc_milling.name,
                powder_coating: self.config.currentSelection.powder_coating.name,
                custom_artwork: self.config.currentSelection.custom_artwork || "",
                custom_dimensions: JSON.stringify(self.config.currentSelection.custom_dimensions || {}),
                total_price: self.getTotalPrice(),
            };

            $("#checkout-btn").prop("disabled", true).text("Processing...");

            $.ajax({
                url: pedal_ajax.ajax_url,
                type: "POST",
                data: orderData,
                success: function (response) {
                    if (response.success) {
                        self.showMessage("Order submitted successfully! Order ID: " + response.data.order_id, "success");
                        self.resetForm();
                    } else {
                        self.showMessage("Error submitting order: " + response.data.message, "error");
                    }
                },
                error: function () {
                    self.showMessage("Error submitting order. Please try again.", "error");
                },
                complete: function () {
                    $("#checkout-btn").prop("disabled", false).text("Continue to Checkout");
                },
            });
        },

        updateSummary: function () {
            const s = this.config.currentSelection;

            const material = this.getProductById("material", s.material);
            $("#summary-material").text(material ? material.name : "N/A");
            $("#price-material").text(material ? "$" + parseFloat(material.price).toFixed(2) : "$0.00");

            const shape = this.getProductById("shape", s.shape);
            $("#summary-shape").text(shape ? shape.name : "N/A");
            $("#price-shape").text(shape ? "$" + parseFloat(shape.price).toFixed(2) : "$0.00");

            // Enhanced size display with custom dimensions
            const sizePrice = this.config.basePrices.size[s.size] || 0;
            let sizeText = this.getSizeName(s.size);
            
            if (s.size === "custom" && s.custom_dimensions) {
                const dims = s.custom_dimensions;
                sizeText = `Custom (${dims.length}mm x ${dims.width}mm x ${dims.height}mm)`;
            }
            
            $("#summary-size").text(sizeText);
            $("#price-size").text("$" + sizePrice.toFixed(2));

            const cnc = this.getProductById("cnc_milling", s.cnc_milling);
            $("#summary-cnc").text(cnc ? cnc.name : "N/A");
            $("#price-cnc").text(cnc ? "$" + parseFloat(cnc.price).toFixed(2) : "$0.00");

            const powder = this.getProductById("powder_coating", s.powder_coating);
            $("#summary-powder").text(powder ? powder.name : "N/A");
            $("#price-powder").text(powder ? "$" + parseFloat(powder.price).toFixed(2) : "$0.00");

            if (s.custom_artwork) {
                $("#artwork-summary").show();
                $("#summary-artwork").text(s.custom_artwork).addClass("link");
                $("#price-artwork").text("$" + this.config.basePrices.custom_artwork.toFixed(2));
            } else {
                $("#artwork-summary").hide();
            }

            this.calculateTotal();
        },

        calculateTotal: function () {
            const s = this.config.currentSelection;
            
            let total = 0;

            const m = this.getProductById("material", s.material);
            const sh = this.getProductById("shape", s.shape);
            const c = this.getProductById("cnc_milling", s.cnc_milling);
            const p = this.getProductById("powder_coating", s.powder_coating);

            total += m ? parseFloat(m.price) : 0;
            total += sh ? parseFloat(sh.price) : 0;
            total += this.config.basePrices.size[s.size] || 0;
            total += c ? parseFloat(c.price) : 0;
            total += p ? parseFloat(p.price) : 0;
            total += s.custom_artwork ? this.config.basePrices.custom_artwork : 0;

            $("#total-price").text("$" + total.toFixed(2) + " USD");
        },

        getProductById: function (category, idOrObj) {
            if (!idOrObj || !this.config.products[category]) return null;
            const id = typeof idOrObj === 'object' && idOrObj !== null ? idOrObj.id : idOrObj;
            return this.config.products[category].find(p => String(p.id) === String(id)) || null;
        },

        getSizeName: function (value) {
            const names = { small: "Small", medium: "Medium", large: "Large", custom: "Custom" };
            return names[value] || value;
        },

        getTotalPrice: function () {
            const totalText = $("#total-price").text();
            return parseFloat(totalText.replace(/[^0-9.]/g, ""));
        },

        showMessage: function (message, type) {
            const $msg = $('<div class="message ' + type + '">' + message + "</div>");
            $(".pedal-customizer-container").prepend($msg);
            setTimeout(() => $msg.fadeOut(() => $msg.remove()), 5000);
        },

        debugCurrentSelection: function() {
            const self = this;
            console.log('Current Selection:', this.config.currentSelection);
            console.log('Products:', this.config.products);
            console.log('WooCommerce Active:', typeof pedal_ajax !== 'undefined');
            console.log('AJAX URL:', pedal_ajax ? pedal_ajax.ajax_url : 'Not defined');
            console.log('Nonce:', pedal_ajax ? pedal_ajax.nonce : 'Not defined');
            
            // Call server-side debug endpoint
            $.ajax({
                url: pedal_ajax.ajax_url,
                type: "POST",
                data: {
                    action: "debug_pedal_customizer",
                    nonce: pedal_ajax.nonce
                },
                success: function(response) {
                    console.log('Server Debug Info:', response.data);
                    self.showMessage('Debug info logged to console. Check browser developer tools.', 'success');
                },
                error: function() {
                    console.log('Failed to get server debug info');
                }
            });
        },

        resetForm: function () {
            this.config.currentSelection = {
                material: { id: null, name: null },
                shape: { id: null, name: null },
                size: 'medium',
                cnc_milling: { id: null, name: null },
                powder_coating: { id: null, name: null },
                custom_artwork: null,
                custom_dimensions: null,
            };

            $('.option-item, .powder-color-item, .size-item').removeClass('selected');
            $('input[type="radio"]').prop('checked', false);
            
            // Reset custom dimensions inputs
            $('input[name="custom_length"]').val('');
            $('input[name="custom_width"]').val('');
            $('input[name="custom_height"]').val('');
            
            // Set medium size as default
            $('input[name="size"][value="medium"]').prop('checked', true);
            $('input[name="size"][value="medium"]').closest('.size-item').addClass('selected');

            this.removeFile();
            this.updateSummary();
        },

        initFileUpload: function () {
            const self = this;
            const $uploadArea = $("#artwork-upload");
            const $fileInput = $("#artwork-file");

            $uploadArea.on("click", function (e) {
                if (!$(e.target).hasClass("remove-file")) {
                    $fileInput.click();
                }
            });

            $fileInput.on("change", function () {
                const file = this.files[0];
                if (file) self.uploadFile(file);
            });

            $uploadArea.on("dragover", function (e) {
                e.preventDefault();
                $(this).addClass("dragover");
            }).on("dragleave", function (e) {
                e.preventDefault();
                $(this).removeClass("dragover");
            }).on("drop", function (e) {
                e.preventDefault();
                $(this).removeClass("dragover");
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) self.uploadFile(files[0]);
            });

            $(document).on("click", ".remove-file", function () {
                self.removeFile();
            });
        },

        uploadFile: function (file) {
            const self = this;
            const formData = new FormData();
            formData.append("artwork", file);
            formData.append("action", "upload_artwork");
            formData.append("nonce", pedal_ajax.nonce);
            $("#artwork-upload").addClass("loading");

            $.ajax({
                url: pedal_ajax.ajax_url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        self.showUploadedFile(response.data.file_name, response.data.file_url);
                        self.config.currentSelection.custom_artwork = response.data.file_name;
                        self.updateSummary();
                    } else {
                        self.showMessage("Error uploading file: " + response.data.message, "error");
                    }
                },
                error: function () {
                    self.showMessage("Error uploading file. Please try again.", "error");
                },
                complete: function () {
                    $("#artwork-upload").removeClass("loading");
                }
            });
        },

        showUploadedFile: function (fileName, fileUrl) {
            $("#uploaded-file .file-name").text(fileName);
            $("#uploaded-file").show();
            $(".upload-content").hide();
        },

        removeFile: function () {
            $("#uploaded-file").hide();
            $(".upload-content").show();
            $("#artwork-file").val("");
            this.config.currentSelection.custom_artwork = null;
            this.updateSummary();
        },
    };

    PedalCustomizer.init();
});
