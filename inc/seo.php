<?php
// SEO enhancements for manga theme

function mangastream_seo_meta() {
    if (is_singular('manga')) {
        global $post;
        $manga_id = $post->ID;
        
        // Open Graph tags
        echo '<meta property="og:type" content="book">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr(wp_trim_words(get_the_excerpt(), 20)) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">' . "\n";
        
        if (has_post_thumbnail()) {
            echo '<meta property="og:image" content="' . esc_url(get_the_post_thumbnail_url($manga_id, 'manga-large')) . '">' . "\n";
        }
        
        // Twitter Card
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr(wp_trim_words(get_the_excerpt(), 20)) . '">' . "\n";
        
        // Schema.org markup
        $author = get_post_meta($manga_id, 'manga_author', true);
        $rating = get_post_meta($manga_id, 'manga_rating', true);
        $genres = get_the_terms($manga_id, 'manga_genre');
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Book',
            'name' => get_the_title(),
            'description' => wp_trim_words(get_the_excerpt(), 50),
            'url' => get_permalink(),
            'image' => get_the_post_thumbnail_url($manga_id, 'manga-large')
        );
        
        if ($author) {
            $schema['author'] = array(
                '@type' => 'Person',
                'name' => $author
            );
        }
        
        if ($rating) {
            $schema['aggregateRating'] = array(
                '@type' => 'AggregateRating',
                'ratingValue' => $rating,
                'bestRating' => '5'
            );
        }
        
        if ($genres) {
            $schema['genre'] = wp_list_pluck($genres, 'name');
        }
        
        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>' . "\n";
    }
}
add_action('wp_head', 'mangastream_seo_meta');

// Add canonical URL
function mangastream_canonical_url() {
    if (is_singular()) {
        echo '<link rel="canonical" href="' . esc_url(get_permalink()) . '">' . "\n";
    }
}
add_action('wp_head', 'mangastream_canonical_url');

// Optimize title for SEO
function mangastream_document_title_parts($title) {
    if (is_singular('manga')) {
        $title['title'] = get_the_title() . ' - Baca Manga Online';
    } elseif (is_tax('manga_genre')) {
        $title['title'] = 'Manga Genre ' . single_term_title('', false) . ' - Baca Online';
    }
    
    return $title;
}
add_filter('document_title_parts', 'mangastream_document_title_parts');
?>
