jQuery(document).ready(function($) {
    'use strict';
    
    // Fix Progress Bar Function
    function updateProgressBar() {
        const scrollTop = $(window).scrollTop();
        const docHeight = $(document).height() - $(window).height();
        const progress = Math.min((scrollTop / docHeight) * 100, 100);
        
        // Pastikan progress bar tidak melebihi 100%
        $('.progress-bar').css('width', progress + '%');
        
        // Debug log untuk troubleshooting
        if (typeof console !== 'undefined' && console.log) {
            console.log('Progress:', progress + '%');
        }
    }

    // Improved scroll handler
    $(window).scroll(function() {
        // Throttle scroll events untuk performance
        clearTimeout(window.scrollTimeout);
        window.scrollTimeout = setTimeout(updateProgressBar, 10);
    });

    // Initialize progress bar
    $(document).ready(function() {
        // Pastikan progress bar dimulai dari 0
        $('.progress-bar').css('width', '0%');
        
        // Update saat halaman dimuat
        updateProgressBar();
    });

    
    // ===== IMAGE ERROR HANDLING =====
    function initImageErrorHandling() {
        $('.manga-page').on('error', function() {
            const img = $(this);
            const src = img.attr('src');
            
            console.log('Image failed to load:', src);
            
            // Create error placeholder
            const errorDiv = $(`
                <div class="image-error" data-original-src="${src}">
                    <div class="error-content">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Gambar tidak dapat dimuat</p>
                        <small>URL: ${src}</small>
                        <button class="retry-btn" onclick="retryLoadImage(this)">
                            <i class="fas fa-redo"></i> Coba Lagi
                        </button>
                    </div>
                </div>
            `);
            
            img.replaceWith(errorDiv);
        });
        
        // Preload images for better performance
        $('.manga-page').each(function(index) {
            if (index < 3) { // Preload first 3 images
                const img = new Image();
                img.src = $(this).attr('src');
            }
        });
    }
    
    // Global function to retry loading images
    window.retryLoadImage = function(button) {
        const errorDiv = $(button).closest('.image-error');
        const originalSrc = errorDiv.data('original-src');
        const pageNumber = errorDiv.index() + 1;
        
        // Add timestamp to force reload
        const newSrc = originalSrc + (originalSrc.includes('?') ? '&' : '?') + 't=' + Date.now();
        
        const newImg = $(`
            <img src="${newSrc}" 
                 alt="Page ${pageNumber}" 
                 class="manga-page"
                 data-page="${pageNumber}"
                 loading="lazy">
        `);
        
        // Replace error div with new image
        errorDiv.replaceWith(newImg);
        
        // Reinitialize error handling for new image
        newImg.on('error', function() {
            initImageErrorHandling();
        });
    };
    
    // ===== KEYBOARD NAVIGATION =====
    function initKeyboardNavigation() {
        $(document).keydown(function(e) {
            // Only work in reader pages
            if (!$('.manga-reader').length) return;
            
            // Don't trigger if user is typing in input fields
            if ($(e.target).is('input, textarea, select')) return;
            
            switch(e.which) {
                case 37: // Left arrow - Previous chapter
                    e.preventDefault();
                    const prevBtn = $('.prev-chapter')[0];
                    if (prevBtn && !$(prevBtn).prop('disabled')) {
                        prevBtn.click();
                    }
                    break;
                case 39: // Right arrow - Next chapter
                    e.preventDefault();
                    const nextBtn = $('.next-chapter')[0];
                    if (nextBtn && !$(nextBtn).prop('disabled')) {
                        nextBtn.click();
                    }
                    break;
                case 38: // Up arrow - Scroll up
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: $(window).scrollTop() - $(window).height() * 0.8
                    }, 300);
                    break;
                case 40: // Down arrow - Scroll down
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: $(window).scrollTop() + $(window).height() * 0.8
                    }, 300);
                    break;
                case 36: // Home - Go to top
                    e.preventDefault();
                    $('html, body').animate({scrollTop: 0}, 500);
                    break;
                case 35: // End - Go to bottom
                    e.preventDefault();
                    $('html, body').animate({scrollTop: $(document).height()}, 500);
                    break;
                case 70: // F key - Fullscreen
                    e.preventDefault();
                    toggleFullscreen();
                    break;
                case 77: // M key - Toggle reading mode
                    e.preventDefault();
                    $('#reading-mode-toggle').click();
                    break;
                case 27: // ESC - Exit fullscreen
                    e.preventDefault();
                    if (document.fullscreenElement) {
                        document.exitFullscreen();
                    }
                    break;
            }
        });
    }
    
    // ===== IMAGE PRELOADING =====
    function preloadImages() {
        const currentImages = $('.manga-page');
        const totalPages = currentImages.length;
        
        // Preload next few images for better performance
        currentImages.each(function(index) {
            if (index < Math.min(5, totalPages)) { // Preload first 5 images
                const img = new Image();
                img.src = $(this).attr('src');
                
                // Log preload status
                img.onload = function() {
                    console.log(`Preloaded image ${index + 1}/${totalPages}`);
                };
                
                img.onerror = function() {
                    console.warn(`Failed to preload image ${index + 1}: ${img.src}`);
                };
            }
        });
    }
    
    // ===== READING MODE TOGGLE =====
    function initReadingMode() {
        $('#reading-mode-toggle').click(function() {
            const reader = $('.manga-reader');
            const button = $(this);
            
            reader.toggleClass('single-page-mode');
            
            if (reader.hasClass('single-page-mode')) {
                button.html('<i class="fas fa-list"></i> Mode Semua Halaman');
                initSinglePageMode();
            } else {
                button.html('<i class="fas fa-th-large"></i> Mode Halaman Tunggal');
                destroySinglePageMode();
            }
            
            // Save preference
            localStorage.setItem('reading_mode', reader.hasClass('single-page-mode') ? 'single' : 'all');
        });
        
        // Load saved preference
        const savedMode = localStorage.getItem('reading_mode');
        if (savedMode === 'single') {
            $('#reading-mode-toggle').click();
        }
    }
    
    // ===== SINGLE PAGE MODE =====
    let currentPageIndex = 0;
    let totalPages = 0;
    
    function initSinglePageMode() {
        const pages = $('.manga-page, .image-error');
        totalPages = pages.length;
        currentPageIndex = 0;
        
        // Hide all pages except first
        pages.hide().eq(0).show();
        
        // Add navigation controls
        addSinglePageControls();
        updatePageInfo();
    }
    
    function destroySinglePageMode() {
        $('.manga-page, .image-error').show();
        $('.single-page-controls').remove();
    }
    
    function addSinglePageControls() {
        const controls = `
            <div class="single-page-controls">
                <button class="page-nav-btn" id="prev-page" disabled>
                    <i class="fas fa-chevron-left"></i> Sebelumnya
                </button>
                <span class="page-info">
                    Halaman <span id="current-page">1</span> dari <span id="total-pages">${totalPages}</span>
                </span>
                <button class="page-nav-btn" id="next-page">
                    Selanjutnya <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;
        
        $('.manga-pages').after(controls);
        
        // Add event listeners
        $('#prev-page').click(function() {
            if (currentPageIndex > 0) {
                showPage(currentPageIndex - 1);
            }
        });
        
        $('#next-page').click(function() {
            if (currentPageIndex < totalPages - 1) {
                showPage(currentPageIndex + 1);
            }
        });
        
        // Add swipe support for mobile
        let startX = 0;
        let startY = 0;
        
        $('.manga-pages').on('touchstart', function(e) {
            startX = e.originalEvent.touches[0].clientX;
            startY = e.originalEvent.touches[0].clientY;
        });
        
        $('.manga-pages').on('touchend', function(e) {
            const endX = e.originalEvent.changedTouches[0].clientX;
            const endY = e.originalEvent.changedTouches[0].clientY;
            const diffX = startX - endX;
            const diffY = startY - endY;
            
            // Only trigger if horizontal swipe is more significant than vertical
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                if (diffX > 0) {
                    // Swipe left - next page
                    $('#next-page').click();
                } else {
                    // Swipe right - previous page
                    $('#prev-page').click();
                }
            }
        });
    }
    
    function showPage(index) {
        if (index < 0 || index >= totalPages) return;
        
        $('.manga-page, .image-error').hide().eq(index).show();
        currentPageIndex = index;
        updatePageInfo();
        
        // Scroll to top of page
        $('html, body').animate({scrollTop: $('.manga-pages').offset().top - 100}, 300);
    }
    
    function updatePageInfo() {
        $('#current-page').text(currentPageIndex + 1);
        $('#prev-page').prop('disabled', currentPageIndex === 0);
        $('#next-page').prop('disabled', currentPageIndex === totalPages - 1);
    }
    
    // ===== CHAPTER SELECTOR =====
    function initChapterSelector() {
        $('.chapter-selector').change(function() {
            const selectedChapter = $(this).val();
            if (selectedChapter) {
                window.location.href = selectedChapter;
            }
        });
    }
    
    // ===== FULLSCREEN FUNCTIONALITY =====
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.log('Fullscreen not supported:', err);
                showNotification('Fullscreen tidak didukung oleh browser ini', 'error');
            });
        } else {
            document.exitFullscreen();
        }
    }
    
    // Listen for fullscreen changes
    document.addEventListener('fullscreenchange', function() {
        if (document.fullscreenElement) {
            showNotification('Mode fullscreen aktif. Tekan ESC untuk keluar', 'info');
        }
    });
    
    // ===== ZOOM FUNCTIONALITY =====
    let zoomLevel = 1;
    const maxZoom = 3;
    const minZoom = 0.5;
    
    function initZoomControls() {
        const zoomControls = `
            <div class="zoom-controls">
                <button id="zoom-out" class="zoom-btn">
                    <i class="fas fa-search-minus"></i>
                </button>
                <span class="zoom-level">${Math.round(zoomLevel * 100)}%</span>
                <button id="zoom-in" class="zoom-btn">
                    <i class="fas fa-search-plus"></i>
                </button>
                <button id="zoom-reset" class="zoom-btn">
                    <i class="fas fa-compress"></i>
                </button>
            </div>
        `;
        
        $('.reader-navigation .nav-center').append(zoomControls);
        
        $('#zoom-in').click(function() {
            if (zoomLevel < maxZoom) {
                zoomLevel += 0.25;
                applyZoom();
            }
        });
        
        $('#zoom-out').click(function() {
            if (zoomLevel > minZoom) {
                zoomLevel -= 0.25;
                applyZoom();
            }
        });
        
        $('#zoom-reset').click(function() {
            zoomLevel = 1;
            applyZoom();
        });
        
        // Mouse wheel zoom
        $('.manga-pages').on('wheel', function(e) {
            if (e.ctrlKey) {
                e.preventDefault();
                
                if (e.originalEvent.deltaY < 0) {
                    // Zoom in
                    if (zoomLevel < maxZoom) {
                        zoomLevel += 0.1;
                        applyZoom();
                    }
                } else {
                    // Zoom out
                    if (zoomLevel > minZoom) {
                        zoomLevel -= 0.1;
                        applyZoom();
                    }
                }
            }
        });
    }
    
    function applyZoom() {
        $('.manga-page').css('transform', `scale(${zoomLevel})`);
        $('.zoom-level').text(`${Math.round(zoomLevel * 100)}%`);
        
        // Update button states
        $('#zoom-in').prop('disabled', zoomLevel >= maxZoom);
        $('#zoom-out').prop('disabled', zoomLevel <= minZoom);
        
        // Save zoom preference
        localStorage.setItem('reader_zoom', zoomLevel);
    }
    
    // ===== READING SETTINGS =====
    function initReadingSettings() {
        const settingsBtn = `
            <button id="reading-settings-btn" class="nav-btn">
                <i class="fas fa-cog"></i> Pengaturan
            </button>
        `;
        
        $('.reader-navigation .nav-right').prepend(settingsBtn);
        
        $('#reading-settings-btn').click(function() {
            showSettingsModal();
        });
    }
    
    function showSettingsModal() {
        const modal = `
            <div class="settings-modal-overlay">
                <div class="settings-modal">
                    <h3>Pengaturan Pembaca</h3>
                    <div class="settings-content">
                        <div class="setting-group">
                            <label>Mode Pembaca:</label>
                            <select id="reading-mode-setting">
                                <option value="all">Semua Halaman</option>
                                <option value="single">Halaman Tunggal</option>
                            </select>
                        </div>
                        
                        <div class="setting-group">
                            <label>Kualitas Gambar:</label>
                            <select id="image-quality-setting">
                                <option value="low">Rendah (Cepat)</option>
                                <option value="medium">Sedang</option>
                                <option value="high">Tinggi</option>
                            </select>
                        </div>
                        
                        <div class="setting-group">
                            <label>Auto-scroll:</label>
                            <input type="checkbox" id="auto-scroll-setting">
                            <span>Aktifkan scroll otomatis</span>
                        </div>
                        
                        <div class="setting-group">
                            <label>Kecepatan Scroll:</label>
                            <input type="range" id="scroll-speed" min="1" max="10" value="5">
                            <span class="range-value">5</span>
                        </div>
                    </div>
                    
                    <div class="settings-actions">
                        <button class="btn btn-primary" id="save-settings">Simpan</button>
                        <button class="btn btn-secondary" id="close-settings">Tutup</button>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modal);
        
        // Load current settings
        loadSettings();
        
        // Event handlers
        $('#save-settings').click(function() {
            saveSettings();
            $('.settings-modal-overlay').fadeOut(() => $('.settings-modal-overlay').remove());
        });
        
        $('#close-settings, .settings-modal-overlay').click(function(e) {
            if (e.target === this) {
                $('.settings-modal-overlay').fadeOut(() => $('.settings-modal-overlay').remove());
            }
        });
        
        $('#scroll-speed').on('input', function() {
            $('.range-value').text($(this).val());
        });
    }
    
    function loadSettings() {
        const settings = {
            readingMode: localStorage.getItem('reading_mode') || 'all',
            imageQuality: localStorage.getItem('image_quality') || 'high',
            autoScroll: localStorage.getItem('auto_scroll') === 'true',
            scrollSpeed: localStorage.getItem('scroll_speed') || '5'
        };
        
        $('#reading-mode-setting').val(settings.readingMode);
        $('#image-quality-setting').val(settings.imageQuality);
        $('#auto-scroll-setting').prop('checked', settings.autoScroll);
        $('#scroll-speed').val(settings.scrollSpeed);
        $('.range-value').text(settings.scrollSpeed);
    }
    
    function saveSettings() {
        const settings = {
            readingMode: $('#reading-mode-setting').val(),
            imageQuality: $('#image-quality-setting').val(),
            autoScroll: $('#auto-scroll-setting').is(':checked'),
            scrollSpeed: $('#scroll-speed').val()
        };
        
        localStorage.setItem('reading_mode', settings.readingMode);
        localStorage.setItem('image_quality', settings.imageQuality);
        localStorage.setItem('auto_scroll', settings.autoScroll);
        localStorage.setItem('scroll_speed', settings.scrollSpeed);
        
        // Apply settings immediately
        applySettings(settings);
        
        showNotification('Pengaturan disimpan', 'success');
    }
    
    function applySettings(settings) {
        // Apply reading mode
        const currentMode = $('.manga-reader').hasClass('single-page-mode') ? 'single' : 'all';
        if (settings.readingMode !== currentMode) {
            $('#reading-mode-toggle').click();
        }
        
        // Apply auto-scroll
        if (settings.autoScroll) {
            startAutoScroll(parseInt(settings.scrollSpeed));
        } else {
            stopAutoScroll();
        }
    }
    
    // ===== AUTO-SCROLL FUNCTIONALITY =====
    let autoScrollInterval;
    
    function startAutoScroll(speed) {
        stopAutoScroll(); // Clear any existing interval
        
        const scrollAmount = speed * 2; // Adjust scroll amount based on speed
        
        autoScrollInterval = setInterval(function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                // Reached bottom, stop auto-scroll
                stopAutoScroll();
                return;
            }
            
            $('html, body').animate({
                scrollTop: $(window).scrollTop() + scrollAmount
            }, 100);
        }, 1000); // Scroll every second
    }
    
    function stopAutoScroll() {
        if (autoScrollInterval) {
            clearInterval(autoScrollInterval);
            autoScrollInterval = null;
        }
    }
    
    // ===== BOOKMARK CURRENT CHAPTER =====
    function initChapterBookmark() {
        const bookmarkBtn = `
            <button id="bookmark-chapter-btn" class="nav-btn">
                <i class="far fa-bookmark"></i> Bookmark
            </button>
        `;
        
        $('.reader-navigation .nav-center').append(bookmarkBtn);
        
        $('#bookmark-chapter-btn').click(function() {
            const mangaId = $('.manga-reader').data('manga-id');
            const chapterNumber = $('.manga-reader').data('chapter-number');
            
            if (mangaId && chapterNumber) {
                $.ajax({
                    url: mangastream_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bookmark_chapter',
                        manga_id: mangaId,
                        chapter_number: chapterNumber,
                        nonce: mangastream_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            const btn = $('#bookmark-chapter-btn');
                            const icon = btn.find('i');
                            
                            if (response.data.action === 'added') {
                                icon.removeClass('far').addClass('fas');
                                btn.addClass('bookmarked');
                                showNotification('Chapter dibookmark', 'success');
                            } else {
                                icon.removeClass('fas').addClass('far');
                                btn.removeClass('bookmarked');
                                showNotification('Bookmark dihapus', 'info');
                            }
                        }
                    },
                    error: function() {
                        showNotification('Gagal memproses bookmark', 'error');
                    }
                });
            }
        });
    }
    
    // ===== COMMENTS TOGGLE =====
    function initCommentsToggle() {
        const commentsBtn = `
            <button id="toggle-comments-btn" class="nav-btn">
                <i class="fas fa-comments"></i> Komentar
            </button>
        `;
        
        $('.reader-navigation .nav-center').append(commentsBtn);
        
        $('#toggle-comments-btn').click(function() {
            if ($('#comments-section').length === 0) {
                loadComments();
            } else {
                $('#comments-section').slideToggle();
            }
        });
    }
    
    function loadComments() {
        const chapterId = $('.manga-reader').data('chapter-id');
        
        if (!chapterId) return;
        
        $.ajax({
            url: mangastream_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'load_chapter_comments',
                chapter_id: chapterId,
                nonce: mangastream_ajax.nonce
            },
            beforeSend: function() {
                showNotification('Memuat komentar...', 'info');
            },
            success: function(response) {
                if (response.success) {
                    const commentsSection = `
                        <div id="comments-section" class="chapter-comments">
                            <h3>Komentar Chapter</h3>
                            <div class="comments-content">
                                ${response.data.comments}
                            </div>
                            <div class="comment-form-container">
                                ${response.data.form}
                            </div>
                        </div>
                    `;
                    
                    $('.reader-container').append(commentsSection);
                    $('#comments-section').hide().slideDown();
                } else {
                    showNotification('Gagal memuat komentar', 'error');
                }
            },
            error: function() {
                showNotification('Terjadi kesalahan saat memuat komentar', 'error');
            }
        });
    }
    
    // ===== NOTIFICATION SYSTEM =====
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        $('.reader-notification').remove();
        
        const notification = $(`
            <div class="reader-notification notification-${type}">
                <span>${message}</span>
                <button class="close-notification">&times;</button>
            </div>
        `);
        
        $('body').append(notification);
        
        // Auto hide after 3 seconds
        setTimeout(() => {
            notification.fadeOut(() => notification.remove());
        }, 3000);
        
        // Manual close
        notification.find('.close-notification').click(() => {
            notification.fadeOut(() => notification.remove());
        });
    }
    
    // ===== READING PROGRESS TRACKING =====
    function initReadingProgress() {
        if ($('.manga-reader').length > 0) {
            const mangaId = $('.manga-reader').data('manga-id');
            const chapterNumber = $('.manga-reader').data('chapter-number');
            
            if (mangaId && chapterNumber) {
                // Update progress when user scrolls to 80% of page
                let progressUpdated = false;
                
                $(window).scroll(function() {
                    if (!progressUpdated && $(window).scrollTop() + $(window).height() >= $(document).height() * 0.8) {
                        $.ajax({
                            url: mangastream_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'update_reading_progress',
                                manga_id: mangaId,
                                chapter_number: chapterNumber,
                                nonce: mangastream_ajax.nonce
                            }
                        });
                        progressUpdated = true;
                    }
                });
            }
        }
    }
    
    // ===== PERFORMANCE MONITORING =====
    function initPerformanceMonitoring() {
        let loadTimes = [];
        
        $('.manga-page').on('load', function() {
            const loadTime = performance.now();
            loadTimes.push(loadTime);
            
            // Log slow loading images
            if (loadTime > 3000) {
                console.warn('Slow image load detected:', this.src, loadTime + 'ms');
            }
        });
        
        // Monitor memory usage (if available)
        if (performance.memory) {
            setInterval(() => {
                const memory = performance.memory;
                if (memory.usedJSHeapSize > 100 * 1024 * 1024) { // 100MB
                    console.warn('High memory usage detected:', memory.usedJSHeapSize / 1024 / 1024 + 'MB');
                }
            }, 30000); // Check every 30 seconds
        }
    }
    
    // ===== INITIALIZATION =====
    function initReader() {
        // Core functionality
        initKeyboardNavigation();
        preloadImages();
        initReadingMode();
        initChapterSelector();
        initImageErrorHandling();
        
        // Enhanced features
        initZoomControls();
        initReadingSettings();
        initChapterBookmark();
        initCommentsToggle();
        initReadingProgress();
        initPerformanceMonitoring();
        
        // Load saved zoom level
        const savedZoom = localStorage.getItem('reader_zoom');
        if (savedZoom) {
            zoomLevel = parseFloat(savedZoom);
            applyZoom();
        }
        
        // Load and apply saved settings
        const savedSettings = {
            readingMode: localStorage.getItem('reading_mode') || 'all',
            imageQuality: localStorage.getItem('image_quality') || 'high',
            autoScroll: localStorage.getItem('auto_scroll') === 'true',
            scrollSpeed: localStorage.getItem('scroll_speed') || '5'
        };
        
        applySettings(savedSettings);
        
        console.log('Reader initialized successfully');
    }
    
    // ===== EVENT HANDLERS =====
    
    // Handle page visibility change (pause auto-scroll when tab is not active)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoScroll();
        } else {
            const autoScroll = localStorage.getItem('auto_scroll') === 'true';
            if (autoScroll) {
                const speed = localStorage.getItem('scroll_speed') || '5';
                startAutoScroll(parseInt(speed));
            }
        }
    });
    
    // Handle window resize
    $(window).resize(function() {
        // Recalculate progress bar on resize
        updateProgressBar();
        
        // Adjust zoom if needed
        if (zoomLevel !== 1) {
            applyZoom();
        }
    });
    
    // Handle orientation change on mobile
    $(window).on('orientationchange', function() {
        setTimeout(function() {
            updateProgressBar();
            if (zoomLevel !== 1) {
                applyZoom();
            }
        }, 500);
    });
    
    // ===== START INITIALIZATION =====
    if ($('.manga-reader').length > 0) {
        initReader();
    }
    
    // ===== CLEANUP ON PAGE UNLOAD =====
    $(window).on('beforeunload', function() {
        stopAutoScroll();
        
        // Save reading progress one last time
        const mangaId = $('.manga-reader').data('manga-id');
        const chapterNumber = $('.manga-reader').data('chapter-number');
        
        if (mangaId && chapterNumber) {
            navigator.sendBeacon(mangastream_ajax.ajax_url, new URLSearchParams({
                action: 'update_reading_progress',
                manga_id: mangaId,
                chapter_number: chapterNumber,
                nonce: mangastream_ajax.nonce
            }));
        }
    });
});
