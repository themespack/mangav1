jQuery(document).ready(function($) {
    'use strict';
    
    // ===== EXTERNAL URL FUNCTIONALITY =====
    $('.add-external-url').click(function() {
        $('#external-url-modal').show();
    });
    
    $('#cancel-external-image').click(function() {
        $('#external-url-modal').hide();
        $('#external-image-url').val('');
        $('#external-image-alt').val('');
        $('#external-image-preview').hide();
    });
    
    // Preview external image
    $('#external-image-url').on('input', function() {
        const url = $(this).val();
        if (url && isValidImageUrl(url)) {
            $('#external-image-preview img').attr('src', url);
            $('#external-image-preview').show();
        } else {
            $('#external-image-preview').hide();
        }
    });
    
    // Add external image
    $('#add-external-image').click(function() {
        const url = $('#external-image-url').val();
        const alt = $('#external-image-alt').val() || 'Manga Page';
        
        if (!url || !isValidImageUrl(url)) {
            alert('Please enter a valid image URL');
            return;
        }
        
        addExternalPage(url, alt);
        $('#external-url-modal').hide();
        $('#external-image-url').val('');
        $('#external-image-alt').val('');
        $('#external-image-preview').hide();
    });
    
    function addExternalPage(url, alt) {
        const container = $('#manga-pages-container');
        const index = container.find('.manga-page-item').length;
        
        const pageHtml = `
            <div class="manga-page-item" data-page-type="external" data-page-index="${index}">
                <div class="page-thumbnail">
                    <img src="${url}" alt="${alt}" loading="lazy">
                    <div class="page-type-badge external">External</div>
                </div>
                <input type="hidden" name="manga_pages[${index}][type]" value="external">
                <input type="text" name="manga_pages[${index}][url]" value="${url}" class="external-url-input" placeholder="Image URL">
                <input type="hidden" name="manga_pages[${index}][alt]" value="${alt}">
                <div class="page-actions">
                    <button type="button" class="move-up button-small">↑</button>
                    <button type="button" class="move-down button-small">↓</button>
                    <button type="button" class="edit-page button-small">✎</button>
                    <button type="button" class="remove-page button-small">×</button>
                </div>
                <div class="page-number">${index + 1}</div>
            </div>
        `;
        
        container.append(pageHtml);
        updatePageNumbers();
    }
    
    function isValidImageUrl(url) {
        return /\.(jpg|jpeg|png|gif|webp)$/i.test(url) || 
               url.includes('imgur.com') || 
               url.includes('cloudinary.com') ||
               url.includes('i.ibb.co') ||
               url.includes('postimg.cc');
    }
    
    // ===== UPLOAD FUNCTIONALITY =====
    $('.upload-manga-pages').click(function() {
        const frame = wp.media({
            title: 'Select Manga Pages',
            button: {
                text: 'Add Pages'
            },
            multiple: true,
            library: {
                type: 'image'
            }
        });
        
        frame.on('select', function() {
            const attachments = frame.state().get('selection').toJSON();
            
            attachments.forEach(function(attachment) {
                addUploadedPage(attachment);
            });
        });
        
        frame.open();
    });
    
    function addUploadedPage(attachment) {
        const container = $('#manga-pages-container');
        const index = container.find('.manga-page-item').length;
        
        const pageHtml = `
            <div class="manga-page-item" data-page-type="upload" data-page-index="${index}">
                <div class="page-thumbnail">
                    <img src="${attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url}" alt="${attachment.alt}" loading="lazy">
                    <div class="page-type-badge upload">Upload</div>
                </div>
                <input type="hidden" name="manga_pages[${index}][type]" value="upload">
                <input type="hidden" name="manga_pages[${index}][id]" value="${attachment.id}">
                <div class="page-actions">
                    <button type="button" class="move-up button-small">↑</button>
                    <button type="button" class="move-down button-small">↓</button>
                    <button type="button" class="edit-page button-small">✎</button>
                    <button type="button" class="remove-page button-small">×</button>
                </div>
                <div class="page-number">${index + 1}</div>
            </div>
        `;
        
        container.append(pageHtml);
        updatePageNumbers();
    }
    
    // ===== PAGE MANAGEMENT =====
    
    // Edit external URL
    $(document).on('click', '.edit-page', function() {
        const pageItem = $(this).closest('.manga-page-item');
        const pageType = pageItem.data('page-type');
        
        if (pageType === 'external') {
            const urlInput = pageItem.find('.external-url-input');
            const currentUrl = urlInput.val();
            const newUrl = prompt('Edit image URL:', currentUrl);
            
            if (newUrl && newUrl !== currentUrl && isValidImageUrl(newUrl)) {
                urlInput.val(newUrl);
                pageItem.find('.page-thumbnail img').attr('src', newUrl);
            }
        } else if (pageType === 'upload') {
            // Open media library to replace uploaded image
            const frame = wp.media({
                title: 'Replace Image',
                button: {
                    text: 'Replace'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                
                pageItem.find('input[name*="[id]"]').val(attachment.id);
                pageItem.find('.page-thumbnail img').attr('src', 
                    attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url
                );
            });
            
            frame.open();
        }
    });
    
    // Remove page
    $(document).on('click', '.remove-page', function() {
        if (confirm('Are you sure you want to remove this page?')) {
            $(this).closest('.manga-page-item').remove();
            updatePageNumbers();
        }
    });
    
    // Move pages up/down
    $(document).on('click', '.move-up', function() {
        const item = $(this).closest('.manga-page-item');
        const prev = item.prev('.manga-page-item');
        if (prev.length) {
            item.insertBefore(prev);
            updatePageNumbers();
        }
    });
    
    $(document).on('click', '.move-down', function() {
        const item = $(this).closest('.manga-page-item');
        const next = item.next('.manga-page-item');
        if (next.length) {
            item.insertAfter(next);
            updatePageNumbers();
        }
    });
    
    // Clear all pages
    $('.clear-all-pages').click(function() {
        if (confirm('Are you sure you want to remove all pages?')) {
            $('#manga-pages-container').empty();
            updatePageNumbers();
        }
    });
    
    function updatePageNumbers() {
        $('#manga-pages-container .manga-page-item').each(function(index) {
            $(this).find('.page-number').text(index + 1);
            $(this).attr('data-page-index', index);
            
            // Update input names
            $(this).find('input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, `[${index}]`);
                    $(this).attr('name', newName);
                }
            });
        });
        
        $('#total-pages').text($('#manga-pages-container .manga-page-item').length);
    }
    
    // ===== SORTABLE FUNCTIONALITY =====
    if ($.fn.sortable) {
        $('#manga-pages-container').sortable({
            items: '.manga-page-item',
            cursor: 'move',
            opacity: 0.8,
            placeholder: 'page-placeholder',
            update: function() {
                updatePageNumbers();
            },
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            }
        });
    }
    
    // ===== CHAPTER MANAGEMENT =====
    
    // Add new chapter
    $('#add-chapter-btn').click(function() {
        const template = $('.chapter-template .chapter-row').clone();
        $('.chapters-list').append(template);
        
        // Hide no-chapters message
        $('.no-chapters').hide();
        
        // Focus on chapter number input
        template.find('.chapter-number').focus();
    });
    
    // Remove chapter
    $(document).on('click', '.remove-chapter', function() {
        if (confirm('Are you sure you want to remove this chapter?')) {
            $(this).closest('.chapter-row').remove();
            
            // Show no-chapters message if no chapters left
            if ($('.chapters-list .chapter-row').length === 0) {
                $('.no-chapters').show();
            }
        }
    });
    
    // Make chapters sortable
    if ($.fn.sortable) {
        $('.chapters-list').sortable({
            items: '.chapter-row',
            handle: '.chapter-handle',
            cursor: 'move',
            opacity: 0.8,
            placeholder: 'chapter-placeholder'
        });
    }
    
    // ===== AUTO-SAVE FUNCTIONALITY =====
    let autoSaveTimeout;
    
    $(document).on('input', '.external-url-input, .chapter-number, .chapter-title', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            // Auto-save draft
            if (typeof wp !== 'undefined' && wp.autosave) {
                wp.autosave.server.triggerSave();
            }
        }, 3000);
    });
    
    // ===== IMAGE VALIDATION =====
    $(document).on('input', '.external-url-input', function() {
        const input = $(this);
        const url = input.val();
        const img = input.closest('.manga-page-item').find('.page-thumbnail img');
        
        if (url && isValidImageUrl(url)) {
            img.attr('src', url);
            input.removeClass('invalid');
        } else if (url) {
            input.addClass('invalid');
        }
    });
    
    // ===== BULK OPERATIONS =====
    
    // Select all pages
    let selectAllPages = false;
    $(document).on('click', '#select-all-pages', function() {
        selectAllPages = !selectAllPages;
        $('.manga-page-item').toggleClass('selected', selectAllPages);
        $(this).text(selectAllPages ? 'Deselect All' : 'Select All');
    });
    
    // Select individual pages
    $(document).on('click', '.manga-page-item', function(e) {
        if (e.ctrlKey || e.metaKey) {
            $(this).toggleClass('selected');
        }
    });
    
    // Bulk delete selected pages
    $(document).on('click', '#delete-selected-pages', function() {
        const selectedPages = $('.manga-page-item.selected');
        if (selectedPages.length > 0) {
            if (confirm(`Are you sure you want to delete ${selectedPages.length} selected pages?`)) {
                selectedPages.remove();
                updatePageNumbers();
            }
        } else {
            alert('No pages selected');
        }
    });
    
    // ===== KEYBOARD SHORTCUTS =====
    $(document).keydown(function(e) {
        // Only work when not typing in inputs
        if ($(e.target).is('input, textarea, select')) return;
        
        switch(e.which) {
            case 46: // Delete key
                e.preventDefault();
                $('.manga-page-item.selected').each(function() {
                    $(this).find('.remove-page').click();
                });
                break;
            case 65: // A key (Select All)
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    $('#select-all-pages').click();
                }
                break;
            case 85: // U key (Upload)
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    $('.upload-manga-pages').click();
                }
                break;
            case 69: // E key (External)
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    $('.add-external-url').click();
                }
                break;
        }
    });
    
    // ===== PROGRESS INDICATOR =====
    function showProgress(message) {
        if ($('.admin-progress').length === 0) {
            $('body').append('<div class="admin-progress"><span></span></div>');
        }
        $('.admin-progress span').text(message);
        $('.admin-progress').show();
    }
    
    function hideProgress() {
        $('.admin-progress').hide();
    }
    
    // ===== VALIDATION =====
    $('form').on('submit', function() {
        let isValid = true;
        const errors = [];
        
        // Validate chapter details
        if ($('#manga_id').length && !$('#manga_id').val()) {
            errors.push('Please select a manga');
            isValid = false;
        }
        
        if ($('#chapter_number').length && !$('#chapter_number').val()) {
            errors.push('Please enter a chapter number');
            isValid = false;
        }
        
        // Validate external URLs
        $('.external-url-input').each(function() {
            const url = $(this).val();
            if (url && !isValidImageUrl(url)) {
                errors.push('Invalid image URL: ' + url);
                isValid = false;
            }
        });
        
        if (!isValid) {
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
            return false;
        }
        
        showProgress('Saving...');
        return true;
    });
    
    // ===== AJAX FUNCTIONS =====
    
    // Reset views
    $('#reset-views').click(function() {
        if (confirm('Are you sure you want to reset all view counts?')) {
            const mangaId = $('#post_ID').val();
            
            $.ajax({
                url: manga_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'reset_manga_views',
                    manga_id: mangaId,
                    nonce: manga_admin_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Views reset successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred');
                }
            });
        }
    });
    
    // Refresh stats
    $('#refresh-stats').click(function() {
        location.reload();
    });
    
    // ===== UTILITY FUNCTIONS =====
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Get image dimensions
    function getImageDimensions(url, callback) {
        const img = new Image();
        img.onload = function() {
            callback(this.width, this.height);
        };
        img.onerror = function() {
            callback(0, 0);
        };
        img.src = url;
    }
    
    // ===== INITIALIZATION =====
    
    // Initialize tooltips (if available)
    if ($.fn.tooltip) {
        $('[title]').tooltip();
    }
    
    // Initialize page numbers on load
    updatePageNumbers();
    
    // Add CSS for admin styles
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .manga-page-item.selected {
                border-color: #007cba !important;
                box-shadow: 0 0 5px rgba(0,123,186,0.5);
            }
            
            .external-url-input.invalid {
                border-color: #dc3545 !important;
                background-color: #fff5f5;
            }
            
            .admin-progress {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 20px 40px;
                border-radius: 8px;
                z-index: 999999;
                display: none;
            }
            
            .page-placeholder {
                height: 200px;
                background: #f0f0f0;
                border: 2px dashed #ddd;
                border-radius: 4px;
                margin-bottom: 15px;
            }
            
            .chapter-placeholder {
                height: 60px;
                background: #f0f0f0;
                border: 2px dashed #ddd;
                border-radius: 4px;
                margin-bottom: 15px;
            }
        `)
        .appendTo('head');
    
    // Hide progress on page load complete
    $(window).on('load', function() {
        hideProgress();
    });
    
    console.log('Manga Admin JS initialized successfully');
});
