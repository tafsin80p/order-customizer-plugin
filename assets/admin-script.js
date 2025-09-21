jQuery(document).ready(function($) {
    'use strict';
    
    // Image upload functionality for admin
    function openMediaUploader() {
        if (typeof wp !== 'undefined' && wp.media) {
            const mediaUploader = wp.media({
                title: 'Select Product Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                document.getElementById('image_url').value = attachment.url;
                updateImagePreview(attachment.url);
            });
            
            mediaUploader.open();
        } else {
            alert('WordPress media uploader not available. Please enter the image URL manually.');
        }
    }
    
    function uploadDirectImage(input) {
        if (input.files && input.files[0]) {
            const formData = new FormData();
            formData.append('product_image', input.files[0]);
            formData.append('action', 'upload_product_image');
            formData.append('nonce', pedal_admin_ajax.nonce);
            
            // Show loading
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '<p>Uploading...</p>';
            
            $.ajax({
                url: pedal_admin_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        document.getElementById('image_url').value = response.data.url;
                        updateImagePreview(response.data.url);
                        showMessage('Image uploaded successfully!', 'success');
                    } else {
                        showMessage('Upload failed: ' + response.data.message, 'error');
                        preview.innerHTML = '';
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Upload error:', error);
                    showMessage('Upload failed. Please try again.', 'error');
                    preview.innerHTML = '';
                }
            });
        }
    }
    
    function updateImagePreview(url) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '<img src="' + url + '" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">';
    }
    
    function showMessage(message, type) {
        const messageDiv = $('<div class="notice notice-' + (type === 'error' ? 'error' : 'success') + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after(messageDiv);
        
        setTimeout(function() {
            messageDiv.fadeOut();
        }, 5000);
    }
    
    // Make functions globally available
    window.openMediaUploader = openMediaUploader;
    window.uploadDirectImage = uploadDirectImage;
    window.updateImagePreview = updateImagePreview;
    
    // Update preview when URL is manually entered
    $(document).on('input', '#image_url', function() {
        const url = this.value;
        if (url && (url.startsWith('http') || url.startsWith('/'))) {
            updateImagePreview(url);
        } else {
            document.getElementById('image-preview').innerHTML = '';
        }
    });
    
    // Category filter functionality
    window.filterByCategory = function(category) {
        const url = new URL(window.location);
        if (category) {
            url.searchParams.set('category', category);
        } else {
            url.searchParams.delete('category');
        }
        window.location = url;
    };
});