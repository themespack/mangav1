jQuery(document).ready(function($) {
    // Advanced filtering for manga archive
    
    function filterManga() {
        const sortBy = $('#sort-by').val();
        const statusFilter = $('#status-filter').val();
        const genreFilter = $('#genre-filter').val();
        const searchTerm = $('#manga-search').val();
        
        const data = {
            action: 'filter_manga',
            sort_by: sortBy,
            status: statusFilter,
            genre: genreFilter,
            search: searchTerm,
            nonce: mangastream_ajax.nonce
        };
        
        $('#manga-results').addClass('loading');
        
        $.ajax({
            url: mangastream_ajax.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                $('#manga-results').removeClass('loading').html(response);
                
                // Update URL without reload
                const url = new URL(window.location);
                if (sortBy) url.searchParams.set('sort', sortBy);
                if (statusFilter) url.searchParams.set('status', statusFilter);
                if (genreFilter) url.searchParams.set('genre', genreFilter);
                if (searchTerm) url.searchParams.set('search', searchTerm);
                
                window.history.pushState({}, '', url);
            },
            error: function() {
                $('#manga-results').removeClass('loading');
                alert('Terjadi kesalahan saat memfilter manga.');
            }
        });
    }
    
    // Filter events
    $('#sort-by, #status-filter, #genre-filter').change(filterManga);
    
    let searchTimeout;
    $('#manga-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterManga, 500);
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
    
    // Infinite scroll
    let loading = false;
    let page = 2;
    
    $(window).scroll(function() {
        if (loading) return;
        
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 1000) {
            loading = true;
            
            $.ajax({
                url: mangastream_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'load_more_manga',
                    page: page,
                    nonce: mangastream_ajax.nonce
                },
                success: function(response) {
                    if (response.trim()) {
                        $('#manga-results').append(response);
                        page++;
                    }
                    loading = false;
                },
                error: function() {
                    loading = false;
                }
            });
        }
    });
});
