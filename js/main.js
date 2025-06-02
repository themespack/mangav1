jQuery(document).ready(function($) {
    'use strict';
    
    // ===== DARK MODE FUNCTIONALITY =====
    function initDarkMode() {
        // Dark mode toggle
        $('#dark-mode-toggle').click(function() {
            $('body').toggleClass('dark-mode');
            const isDarkMode = $('body').hasClass('dark-mode');
            localStorage.setItem('darkMode', isDarkMode);
            
            // Update icon
            const icon = $(this).find('i');
            if (isDarkMode) {
                icon.removeClass('fa-moon').addClass('fa-sun');
            } else {
                icon.removeClass('fa-sun').addClass('fa-moon');
            }
        });
        
        // Load dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            $('body').addClass('dark-mode');
            $('#dark-mode-toggle i').removeClass('fa-moon').addClass('fa-sun');
        }
    }
    
    // ===== SEARCH FUNCTIONALITY =====
    function initSearch() {
        let searchTimeout;
        let searchResults = $('#search-results');
        
        // Create search results container if not exists
        if (searchResults.length === 0) {
            $('.search-container').append('<div id="search-results" class="search-results"></div>');
            searchResults = $('#search-results');
        }
        
        // AJAX search
        $('#search-input').on('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val().trim();
            
            if (searchTerm.length < 3) {
                searchResults.hide().empty();
                return;
            }
            
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: mangastream_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'mangastream_search',
                        search_term: searchTerm,
                        nonce: mangastream_ajax.nonce
                    },
                    beforeSend: function() {
                        searchResults.html('<div class="search-loading">Mencari...</div>').show();
                    },
                    success: function(response) {
                        let resultsHtml = '';
                        if (response && response.length > 0) {
                            resultsHtml = '<div class="search-results-list">';
                            response.forEach(function(item) {
                                resultsHtml += `
                                    <div class="search-result-item">
                                        <div class="search-result-thumb">
                                            ${item.thumbnail ? `<img src="${item.thumbnail}" alt="${item.title}" onerror="this.style.display='none'">` : ''}
                                        </div>
                                        <div class="search-result-info">
                                            <a href="${item.url}" class="search-result-title">${item.title}</a>
                                            <div class="search-result-meta">Manga</div>
                                        </div>
                                    </div>
                                `;
                            });
                            resultsHtml += '</div>';
                        } else {
                            resultsHtml = '<div class="no-search-results">Tidak ada hasil ditemukan</div>';
                        }
                        searchResults.html(resultsHtml).show();
                    },
                    error: function() {
                        searchResults.html('<div class="search-error">Terjadi kesalahan saat mencari</div>').show();
                    }
                });
            }, 300);
        });
        
        // Hide search results when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('.search-container').length) {
                searchResults.hide();
            }
        });
        
        // Show results when focusing on search input
        $('#search-input').focus(function() {
            if ($(this).val().length >= 3 && searchResults.children().length > 0) {
                searchResults.show();
            }
        });
    }
    
    // ===== MANGA FILTERING =====
    function initMangaFilters() {
        if ($('.manga-filters').length === 0) return;
        
        let currentPage = 1;
        let isFiltering = false;
        
        function filterManga(resetPage = true) {
            if (isFiltering) return;
            
            isFiltering = true;
            
            if (resetPage) {
                currentPage = 1;
            }
            
            const sortBy = $('#sort-by').val() || '';
            const statusFilter = $('#status-filter').val() || '';
            const genreFilter = $('#genre-filter').val() || '';
            const searchTerm = $('#manga-search').val() || '';
            
            const data = {
                action: 'filter_manga',
                sort_by: sortBy,
                status: statusFilter,
                genre: genreFilter,
                search: searchTerm,
                nonce: mangastream_ajax.nonce
            };
            
            const resultsContainer = $('#manga-results');
            resultsContainer.addClass('loading');
            
            // Hide load more button during filtering
            $('#load-more-manga').hide();
            
            $.ajax({
                url: mangastream_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    resultsContainer.removeClass('loading').html(response);
                    
                    // Update URL without reload
                    updateURL({
                        sort: sortBy,
                        status: statusFilter,
                        genre: genreFilter,
                        search: searchTerm
                    });
                    
                    // Reset load more functionality
                    resetLoadMore();
                    
                    // Reinitialize lazy loading for new content
                    initLazyLoading();
                    
                    isFiltering = false;
                },
                error: function() {
                    resultsContainer.removeClass('loading');
                    showNotification('Terjadi kesalahan saat memfilter manga.', 'error');
                    isFiltering = false;
                }
            });
        }
        
        function resetLoadMore() {
            currentPage = 2;
            const loadMoreBtn = $('#load-more-manga');
            loadMoreBtn.show().text('Muat Lebih Banyak').prop('disabled', false);
            loadMoreBtn.data('page', currentPage);
        }
        
        // Filter events
        $('#sort-by, #status-filter, #genre-filter').change(function() {
            filterManga(true);
        });
        
        let searchTimeout;
        $('#manga-search').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterManga(true);
            }, 500);
        });
        
        // View toggle
        $('.view-btn').click(function() {
            $('.view-btn').removeClass('active');
            $(this).addClass('active');
            
            const view = $(this).data('view');
            $('#manga-results').removeClass('grid-view list-view').addClass(view + '-view');
            
            localStorage.setItem('manga_view', view);
        });
        
        // Load saved view preference
        const savedView = localStorage.getItem('manga_view');
        if (savedView) {
            $(`.view-btn[data-view="${savedView}"]`).click();
        }
    }
    
    // ===== INFINITE SCROLL / LOAD MORE =====
    function initLoadMore() {
        let loading = false;
        let page = 2;
        const loadMoreBtn = $('#load-more-manga');
        
        if (loadMoreBtn.length === 0) return;
        
        // Load more button click
        loadMoreBtn.click(function() {
            if (loading) return;
            
            loading = true;
            const button = $(this);
            const originalText = button.text();
            
            button.text('Memuat...').prop('disabled', true);
            
            // Get current filter values
            const currentFilters = {
                sort_by: $('#sort-by').val() || '',
                status: $('#status-filter').val() || '',
                genre: $('#genre-filter').val() || '',
                search: $('#manga-search').val() || ''
            };
            
            $.ajax({
                url: mangastream_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'load_more_manga_filtered',
                    page: page,
                    ...currentFilters,
                    nonce: mangastream_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const newContent = response.data;
                        if (newContent && newContent.trim()) {
                            $('#manga-results').append(newContent);
                            page++;
                            button.text(originalText).prop('disabled', false);
                            
                            // Reinitialize lazy loading for new content
                            initLazyLoading();
                        } else {
                            button.text('Tidak ada lagi').prop('disabled', true);
                        }
                    } else {
                        button.text('Tidak ada lagi').prop('disabled', true);
                    }
                    loading = false;
                },
                error: function() {
                    button.text('Error - Coba Lagi').prop('disabled', false);
                    loading = false;
                }
            });
        });
        
        // Auto infinite scroll (optional)
        if (loadMoreBtn.data('auto-scroll') !== false) {
            $(window).scroll(function() {
                if (loading || loadMoreBtn.prop('disabled')) return;
                
                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 1000) {
                    loadMoreBtn.click();
                }
            });
        }
    }
    
    // ===== BOOKMARK FUNCTIONALITY =====
    function initBookmarks() {
        $(document).on('click', '.bookmark-btn', function() {
            const button = $(this);
            const mangaId = button.data('manga-id');
            
            if (!mangaId) return;
            
            $.ajax({
                url: mangastream_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'bookmark_manga',
                    manga_id: mangaId,
                    nonce: mangastream_ajax.nonce
                },
                beforeSend: function() {
                    button.prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        const action = response.data.action;
                        const icon = button.find('i');
                        
                        if (action === 'added') {
                            button.addClass('bookmarked');
                            icon.removeClass('far').addClass('fas');
                            showNotification('Manga ditambahkan ke bookmark', 'success');
                        } else {
                            button.removeClass('bookmarked');
                            icon.removeClass('fas').addClass('far');
                            showNotification('Manga dihapus dari bookmark', 'info');
                        }
                    } else {
                        showNotification(response.data || 'Gagal menambahkan bookmark', 'error');
                    }
                    button.prop('disabled', false);
                },
                error: function() {
                    showNotification('Terjadi kesalahan', 'error');
                    button.prop('disabled', false);
                }
            });
        });
    }
    
    // ===== RANDOM MANGA =====
    function initRandomManga() {
        $('#random-manga-btn').click(function() {
            const button = $(this);
            const originalText = button.text();
            
            button.text('Loading...').prop('disabled', true);
            
            $.ajax({
                url: mangastream_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'random_manga',
                    nonce: mangastream_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const manga = response.data;
                        const container = $('.random-manga-card');
                        
                        if (container.length) {
                            container.find('img').attr('src', manga.thumbnail).attr('alt', manga.title);
                            container.find('.random-manga-title').text(manga.title).attr('href', manga.url);
                            container.find('.random-manga-excerpt').text(manga.excerpt);
                            container.find('.read-now-btn').attr('href', manga.url);
                            
                            // Add fade effect
                            container.fadeOut(200).fadeIn(200);
                        }
                    }
                    button.text(originalText).prop('disabled', false);
                },
                error: function() {
                    button.text('Error').prop('disabled', false);
                    setTimeout(() => {
                        button.text(originalText).prop('disabled', false);
                    }, 2000);
                }
            });
        });
    }
    
    // ===== BACK TO TOP =====
    function initBackToTop() {
        let backToTop = $('.back-to-top');
        
        if (backToTop.length === 0) {
            $('body').append('<button id="back-to-top" class="back-to-top"><i class="fas fa-arrow-up"></i></button>');
            backToTop = $('.back-to-top');
        }
        
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                backToTop.addClass('show');
            } else {
                backToTop.removeClass('show');
            }
        });
        
        $(document).on('click', '.back-to-top', function() {
            $('html, body').animate({scrollTop: 0}, 600);
            return false;
        });
    }
    
    // ===== LAZY LOADING =====
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            img.classList.add('loaded');
                            imageObserver.unobserve(img);
                        }
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for older browsers
            $('img[data-src]').each(function() {
                $(this).attr('src', $(this).data('src')).removeClass('lazy').addClass('loaded');
            });
        }
    }
    
    // ===== VIEW COUNTER =====
    function initViewCounter() {
        if ($('body').hasClass('single-manga') || $('body').hasClass('manga-reader-page')) {
            const postId = $('body').data('post-id') || $('.manga-single').data('manga-id') || $('.manga-reader').data('manga-id');
            
            if (postId) {
                // Delay to ensure user actually views the content
                setTimeout(function() {
                    $.ajax({
                        url: mangastream_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'update_view_count',
                            post_id: postId,
                            nonce: mangastream_ajax.nonce
                        }
                    });
                }, 3000); // 3 second delay
            }
        }
    }
    
    // ===== SHARE FUNCTIONALITY =====
    function initShareButtons() {
        $('.share-btn').click(function() {
            const url = window.location.href;
            const title = document.title;
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: url
                }).catch(function(error) {
                    console.log('Error sharing:', error);
                    showShareModal(url, title);
                });
            } else {
                // Fallback: copy to clipboard
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(function() {
                        showNotification('Link disalin ke clipboard', 'success');
                    }).catch(function() {
                        showShareModal(url, title);
                    });
                } else {
                    showShareModal(url, title);
                }
            }
        });
    }
    
    // ===== MOBILE MENU =====
    function initMobileMenu() {
        // Create mobile menu toggle if not exists
        if ($('.mobile-menu-toggle').length === 0) {
            $('.header-container').prepend('<button class="mobile-menu-toggle"><i class="fas fa-bars"></i></button>');
        }
        
        $('.mobile-menu-toggle').click(function() {
            $('.main-navigation').toggleClass('active');
            $(this).find('i').toggleClass('fa-bars fa-times');
        });
        
        // Close mobile menu when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('.header-container').length) {
                $('.main-navigation').removeClass('active');
                $('.mobile-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
            }
        });
        
        // Close mobile menu when clicking on menu items
        $('.main-navigation a').click(function() {
            $('.main-navigation').removeClass('active');
            $('.mobile-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
        });
    }
    
    // ===== CHAPTER SORTING =====
    function initChapterSorting() {
        $('.sort-btn').click(function() {
            const button = $(this);
            const sortType = button.data('sort');
            
            // Update active state
            $('.sort-btn').removeClass('active');
            button.addClass('active');
            
            // Get all chapter items
            const chaptersContainer = $('#chapters-list');
            const chapters = chaptersContainer.find('.chapter-item').get();
            
            // Sort chapters
            chapters.sort(function(a, b) {
                const aNumber = parseFloat($(a).data('chapter'));
                const bNumber = parseFloat($(b).data('chapter'));
                
                if (sortType === 'desc') {
                    return bNumber - aNumber;
                } else {
                    return aNumber - bNumber;
                }
            });
            
            // Reorder DOM elements
            $.each(chapters, function(index, item) {
                chaptersContainer.append(item);
            });
            
            // Save preference
            localStorage.setItem('chapter_sort', sortType);
        });
        
        // Load saved sort preference
        const savedSort = localStorage.getItem('chapter_sort');
        if (savedSort) {
            $(`.sort-btn[data-sort="${savedSort}"]`).click();
        }
    }
    
    // ===== READING PROGRESS =====
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
    
    // ===== UTILITY FUNCTIONS =====
    function updateURL(params) {
        if (!window.history || !window.history.replaceState) return;
        
        const url = new URL(window.location);
        
        Object.keys(params).forEach(key => {
            if (params[key]) {
                url.searchParams.set(key, params[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        
        window.history.replaceState({}, '', url);
    }
    
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        $('.notification').remove();
        
        const notification = $(`
            <div class="notification notification-${type}">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `);
        
        $('body').append(notification);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            notification.fadeOut(() => notification.remove());
        }, 5000);
        
        // Manual close
        notification.find('.notification-close').click(() => {
            notification.fadeOut(() => notification.remove());
        });
    }
    
    function showShareModal(url, title) {
        const modal = $(`
            <div class="share-modal-overlay">
                <div class="share-modal">
                    <h3>Bagikan</h3>
                    <div class="share-options">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" target="_blank" class="share-option facebook">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}" target="_blank" class="share-option twitter">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}" target="_blank" class="share-option whatsapp">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <a href="https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}" target="_blank" class="share-option telegram">
                            <i class="fab fa-telegram"></i> Telegram
                        </a>
                    </div>
                    <div class="share-url">
                        <input type="text" value="${url}" readonly>
                        <button class="copy-url-btn">Copy</button>
                    </div>
                    <button class="close-modal">&times;</button>
                </div>
            </div>
        `);
        
        $('body').append(modal);
        
        // Copy URL functionality
        modal.find('.copy-url-btn').click(function() {
            const input = modal.find('input');
            input.select();
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(input.val()).then(function() {
                    $(this).text('Copied!');
                    setTimeout(() => $(this).text('Copy'), 2000);
                }.bind(this));
            } else {
                // Fallback for older browsers
                document.execCommand('copy');
                $(this).text('Copied!');
                setTimeout(() => $(this).text('Copy'), 2000);
            }
        });
        
        // Close modal
        modal.find('.close-modal, .share-modal-overlay').click(function(e) {
            if (e.target === this) {
                modal.fadeOut(() => modal.remove());
            }
        });
    }
    
    // ===== FORM ENHANCEMENTS =====
    function initFormEnhancements() {
        // Add loading state to forms
        $('form').submit(function() {
            const submitBtn = $(this).find('button[type="submit"], input[type="submit"]');
            const originalText = submitBtn.val() || submitBtn.text();
            
            submitBtn.prop('disabled', true);
            if (submitBtn.is('button')) {
                submitBtn.text('Loading...');
            } else {
                submitBtn.val('Loading...');
            }
            
            // Reset after 10 seconds (fallback)
            setTimeout(() => {
                submitBtn.prop('disabled', false);
                if (submitBtn.is('button')) {
                    submitBtn.text(originalText);
                } else {
                    submitBtn.val(originalText);
                }
            }, 10000);
        });
        
        // Auto-resize textareas
        $('textarea').each(function() {
            this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
        }).on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Enhanced search input
        $('.search-input').on('focus', function() {
            $(this).closest('.search-container').addClass('focused');
        }).on('blur', function() {
            setTimeout(() => {
                $(this).closest('.search-container').removeClass('focused');
            }, 200);
        });
    }
    
    // ===== KEYBOARD SHORTCUTS =====
    function initKeyboardShortcuts() {
        $(document).keydown(function(e) {
            // Don't trigger shortcuts when typing in inputs
            if ($(e.target).is('input, textarea, select')) {
                return;
            }
            
            switch(e.which) {
                case 191: // "/" key for search
                    e.preventDefault();
                    $('#search-input').focus();
                    break;
                case 27: // ESC key
                    // Close modals/dropdowns
                    $('.share-modal-overlay').fadeOut(() => $('.share-modal-overlay').remove());
                    $('#search-results').hide();
                    $('.main-navigation').removeClass('active');
                    $('.mobile-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
                    break;
                case 68: // "D" key for dark mode
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        $('#dark-mode-toggle').click();
                    }
                    break;
            }
        });
    }
    
    // ===== ERROR HANDLING =====
    function initErrorHandling() {
        // Global AJAX error handler
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            console.error('AJAX Error:', thrownError);
            
            // Don't show error for search requests (they're expected to fail sometimes)
            if (settings.data && settings.data.includes('mangastream_search')) {
                return;
            }
            
            showNotification('Terjadi kesalahan jaringan. Silakan coba lagi.', 'error');
        });
        
        // Image error handling
        $(document).on('error', 'img', function() {
            const img = $(this);
            if (!img.hasClass('error-handled')) {
                img.addClass('error-handled');
                
                // Try to load a placeholder image
                const placeholder = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjI4MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIG5vdCBmb3VuZDwvdGV4dD48L3N2Zz4=';
                img.attr('src', placeholder);
            }
        });
    }
    
    // ===== PERFORMANCE MONITORING =====
    function initPerformanceMonitoring() {
        // Monitor page load time
        window.addEventListener('load', function() {
            const loadTime = performance.now();
            if (loadTime > 3000) {
                console.warn('Slow page load detected:', loadTime + 'ms');
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
    function init() {
        // Core functionality
        initDarkMode();
        initSearch();
        initMangaFilters();
        initLoadMore();
        initBookmarks();
        initRandomManga();
        initBackToTop();
        initLazyLoading();
        initViewCounter();
        initShareButtons();
        initMobileMenu();
        initChapterSorting();
        initReadingProgress();
        
        // Enhancements
        initFormEnhancements();
        initKeyboardShortcuts();
        initErrorHandling();
        initPerformanceMonitoring();
        
        // Add loaded class to body
        $('body').addClass('js-loaded');
        
        console.log('MangaStream theme initialized successfully');
    }
    
    // ===== EVENT HANDLERS =====
    
    // Handle page visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Pause any ongoing operations when tab is not active
            console.log('Page hidden - pausing operations');
        } else {
            console.log('Page visible - resuming operations');
        }
    });
    
    // Handle window resize
    let resizeTimeout;
    $(window).resize(function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            // Trigger resize events for responsive components
            $(window).trigger('mangastream:resize');
            
            // Recalculate any fixed elements
            if ($('.manga-reader').length) {
                // Update reader layout if needed
            }
        }, 250);
    });
    
    // Handle orientation change on mobile
    $(window).on('orientationchange', function() {
        setTimeout(function() {
            $(window).trigger('resize');
        }, 500);
    });
    
    // ===== START INITIALIZATION =====
    init();
    
    // ===== REINITIALIZE ON AJAX CONTENT LOAD =====
    $(document).ajaxComplete(function() {
        initLazyLoading();
    });
    
    // ===== CLEANUP ON PAGE UNLOAD =====
    $(window).on('beforeunload', function() {
        // Clean up any ongoing operations
        console.log('Page unloading - cleaning up');
    });
    
    // ===== CUSTOM EVENTS =====
    
    // Custom event for when manga content is loaded
    $(document).on('mangastream:contentLoaded', function() {
        initLazyLoading();
        initBookmarks();
    });
    
    // Custom event for filter changes
    $(document).on('mangastream:filterChanged', function(e, filters) {
        console.log('Filters changed:', filters);
    });
    
    // ===== ACCESSIBILITY IMPROVEMENTS =====
    
    // Skip to content link
    if ($('.skip-to-content').length === 0) {
        $('body').prepend('<a href="#main-content" class="skip-to-content sr-only">Skip to main content</a>');
    }
    
    // Add main content ID if not exists
    if ($('#main-content').length === 0) {
        $('.main-content').attr('id', 'main-content');
    }
    
    // Improve focus management
    $('.skip-to-content').focus(function() {
        $(this).removeClass('sr-only');
    }).blur(function() {
        $(this).addClass('sr-only');
    });
    
    // ===== FINAL SETUP =====
    
    // Set initial page state
    $('body').attr('data-theme-version', '1.0');
    
    // Log successful initialization
    console.log('MangaStream Theme v1.0 - All systems ready!');
});
