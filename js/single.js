jQuery(document).ready(function($) {
    'use strict';
    
    // Chapter list sorting
    $('.sort-btn').on('click', function() {
        const sortType = $(this).data('sort');
        const $chapters = $('.chapter-item');
        
        $('.sort-btn').removeClass('active');
        $(this).addClass('active');
        
        let sortedChapters;
        
        if (sortType === 'asc') {
            sortedChapters = $chapters.sort((a, b) => {
                return parseFloat($(a).data('chapter')) - parseFloat($(b).data('chapter'));
            });
        } else {
            sortedChapters = $chapters.sort((a, b) => {
                return parseFloat($(b).data('chapter')) - parseFloat($(a).data('chapter'));
            });
        }
        
        $('.chapters').html(sortedChapters);
    });
    
    // Bookmark functionality
    $('.bookmark-btn').on('click', function() {
        const mangaId = $(this).data('manga-id');
        
        $.ajax({
            url: mangastream_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'toggle_manga_bookmark',
                manga_id: mangaId,
                nonce: mangastream_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const btn = $('.bookmark-btn');
                    const icon = btn.find('i');
                    
                    if (response.data.action === 'added') {
                        icon.removeClass('far').addClass('fas');
                        btn.text('Bookmarked');
                        showNotification('Manga ditambahkan ke bookmark', 'success');
                    } else {
                        icon.removeClass('fas').addClass('far');
                        btn.text('Bookmark');
                        showNotification('Manga dihapus dari bookmark', 'info');
                    }
                }
            }
        });
    });
    
    // Rating system
    $('.rating-star').on('click', function() {
        const rating = $(this).data('rating');
        const mangaId = $('.manga-single').data('manga-id');
        
        $.ajax({
            url: mangastream_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'rate_manga',
                manga_id: mangaId,
                rating: rating,
                nonce: mangastream_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateRatingDisplay(response.data.new_rating);
                    showNotification('Rating berhasil disimpan', 'success');
                }
            }
        });
    });
    
    function updateRatingDisplay(newRating) {
        $('.rating-stars i').removeClass('fas').addClass('far');
        for (let i = 1; i <= Math.floor(newRating); i++) {
            $(`.rating-star[data-rating="${i}"] i`).removeClass('far').addClass('fas');
        }
        $('.rating-number').text(newRating.toFixed(1));
    }
});
