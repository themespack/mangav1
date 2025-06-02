<?php
// Performance optimizations

// Lazy load images
function add_lazy_loading($content) {
    if (is_admin() || is_feed()) {
        return $content;
    }
    
    $content = preg_replace('/<img(.*?)src=[\'"](.*?)[\'"]/', '<img$1src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E" data-src="$2"', $content);
    
    return $content;
}
add_filter('the_content', 'add_lazy_loading');

// Preload critical resources
function mangastream_preload_resources() {
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/js/main.js" as="script">' . "\n";
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/style.css" as="style">' . "\n";
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
}
add_action('wp_head', 'mangastream_preload_resources', 1);

// Remove unused WordPress features
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');

// Optimize database queries
function optimize_manga_queries() {
    if (is_admin()) return;
    
    // Remove unnecessary queries
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
    
    // Limit post revisions
    if (!defined('WP_POST_REVISIONS')) {
        define('WP_POST_REVISIONS', 3);
    }
}
add_action('init', 'optimize_manga_queries');

// Cache manga data
function cache_manga_data($manga_id) {
    $cache_key = 'manga_data_' . $manga_id;
    $cached_data = wp_cache_get($cache_key);
    
    if ($cached_data === false) {
        $manga_data = array(
            'chapters' => get_manga_chapters($manga_id),
            'genres' => get_the_terms($manga_id, 'manga_genre'),
            'status' => get_the_terms($manga_id, 'manga_status'),
            'meta' => get_post_meta($manga_id)
        );
        
        wp_cache_set($cache_key, $manga_data, '', 3600); // Cache for 1 hour
        return $manga_data;
    }
    
    return $cached_data;
}
?>
