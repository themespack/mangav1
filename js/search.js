jQuery(document).ready(function($) {
    'use strict';
    
    // ===== AUTO HIDE SEARCH FUNCTIONALITY =====
    
    function initMobileSearch() {
        // Check if mobile
        function isMobile() {
            return $(window).width() <= 768;
        }
        
        // Toggle search visibility
        function toggleSearch() {
            $('.search-container').toggleClass('hide-search');
            
            if (!$('.search-container').hasClass('hide-search')) {
                // Focus on search input when shown
                setTimeout(function() {
                    $('.search-input').focus();
                }, 300);
            }
        }
        
        // Initialize search state
        function initSearchState() {
            if (isMobile()) {
                $('.search-container').addClass('hide-search');
                
                // Add toggle button if not exists
                if (!$('.search-toggle-btn').length) {
                    $('.search-container').append('<button class="search-toggle-btn" type="button"><i class="fas fa-search"></i></button>');
                }
            } else {
                $('.search-container').removeClass('hide-search');
                $('.search-toggle-btn').remove();
            }
        }
        
        // Event handlers
        $(document).on('click', '.search-btn, .search-toggle-btn', function(e) {
            if (isMobile()) {
                e.preventDefault();
                toggleSearch();
            }
        });
        
        // Handle form submission
        $(document).on('submit', '.search-form', function(e) {
            if (isMobile() && $('.search-container').hasClass('hide-search')) {
                e.preventDefault();
                toggleSearch();
            }
        });
        
        // Close search when clicking outside
        $(document).on('click', function(e) {
            if (isMobile() && !$(e.target).closest('.search-container').length) {
                if (!$('.search-container').hasClass('hide-search')) {
                    $('.search-container').addClass('hide-search');
                }
            }
        });
        
        // Handle window resize
        $(window).on('resize', function() {
            clearTimeout(window.resizeTimeout);
            window.resizeTimeout = setTimeout(initSearchState, 250);
        });
        
        // Initialize on load
        initSearchState();
    }
    
    // ===== SEARCH INPUT ENHANCEMENTS =====
    
    function initSearchEnhancements() {
        // Add search icon if not present
        $('.search-btn').html('<i class="fas fa-search"></i>');
        
        // Placeholder text
        $('.search-input').attr('placeholder', 'Cari manga...');
        
        // Clear search button
        $('.search-input').on('input', function() {
            const $input = $(this);
            const $container = $input.closest('.search-container');
            
            if ($input.val().length > 0) {
                if (!$container.find('.search-clear').length) {
                    $container.append('<button type="button" class="search-clear"><i class="fas fa-times"></i></button>');
                }
            } else {
                $container.find('.search-clear').remove();
            }
        });
        
        // Clear search functionality
        $(document).on('click', '.search-clear', function() {
            $(this).siblings('.search-input').val('').focus();
            $(this).remove();
        });
        
        // AJAX Search
        let searchTimeout;
        $('.search-input').on('keyup', function() {
            const query = $(this).val();
            clearTimeout(searchTimeout);
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(function() {
                    performAjaxSearch(query);
                }, 300);
            } else {
                $('.search-results').hide();
            }
        });
    }
    
    // AJAX Search Function
    function performAjaxSearch(query) {
        $.ajax({
            url: mangastream_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mangastream_search',
                search_term: query,
                nonce: mangastream_ajax.nonce
            },
            success: function(response) {
                displaySearchResults(response);
            },
            error: function() {
                console.log('Search error');
            }
        });
    }
    
    // Display search results
    function displaySearchResults(results) {
        let html = '';
        if (results.length > 0) {
            results.forEach(function(item) {
                html += `
                    <div class="search-result-item">
                        <div class="search-result-thumb">
                            <img src="${item.thumbnail}" alt="${item.title}" loading="lazy">
                        </div>
                        <div class="search-result-info">
                            <a href="${item.url}" class="search-result-title">${item.title}</a>
                        </div>
                    </div>
                `;
            });
        } else {
            html = '<div class="no-search-results">Tidak ada hasil ditemukan</div>';
        }
        
        $('.search-results').html(html).show();
    }
    
    // ===== KEYBOARD SHORTCUTS =====
    
    function initSearchKeyboard() {
        // Ctrl/Cmd + K to focus search
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                
                if ($(window).width() <= 768 && $('.search-container').hasClass('hide-search')) {
                    $('.search-container').removeClass('hide-search');
                }
                
                setTimeout(function() {
                    $('.search-input').focus();
                }, 100);
            }
            
            // Escape to close search on mobile
            if (e.key === 'Escape' && $(window).width() <= 768) {
                $('.search-container').addClass('hide-search');
                $('.search-results').hide();
            }
        });
    }
    
    // ===== INITIALIZATION =====
    
    // Initialize all search functionality
    initMobileSearch();
    initSearchEnhancements();
    initSearchKeyboard();
    
    console.log('Search functionality initialized');
});
