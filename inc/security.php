<?php
// Security functions

function mangastream_sanitize_chapter_data($data) {
    $sanitized = array();
    
    if (isset($data['chapter_number'])) {
        $sanitized['chapter_number'] = floatval($data['chapter_number']);
    }
    
    if (isset($data['chapter_title'])) {
        $sanitized['chapter_title'] = sanitize_text_field($data['chapter_title']);
    }
    
    if (isset($data['chapter_pages']) && is_array($data['chapter_pages'])) {
        $sanitized['chapter_pages'] = array_map('absint', $data['chapter_pages']);
    }
    
    return $sanitized;
}

function verify_manga_permissions($manga_id, $action = 'read') {
    $manga = get_post($manga_id);
    
    if (!$manga || $manga->post_type !== 'manga') {
        return false;
    }
    
    switch ($action) {
        case 'read':
            return $manga->post_status === 'publish' || current_user_can('read_private_posts');
        case 'edit':
            return current_user_can('edit_post', $manga_id);
        case 'delete':
            return current_user_can('delete_post', $manga_id);
        default:
            return false;
    }
}

function rate_limit_check($action, $limit = 10, $window = 3600) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = 'rate_limit_' . $action . '_' . md5($ip);
    
    $count = get_transient($key) ?: 0;
    
    if ($count >= $limit) {
        return false;
    }
    
    set_transient($key, $count + 1, $window);
    return true;
}

// Add security headers
function add_security_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
add_action('send_headers', 'add_security_headers');

// Prevent direct access to PHP files
function prevent_direct_access() {
    if (!defined('ABSPATH')) {
        exit('Direct access not allowed.');
    }
}

// Clean up on theme switch
function mangastream_cleanup() {
    // Clear scheduled events
    wp_clear_scheduled_hook('reset_daily_views_hook');
    wp_clear_scheduled_hook('reset_weekly_views_hook');
    wp_clear_scheduled_hook('reset_monthly_views_hook');
    
    // Clear transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_manga_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_manga_%'");
}
add_action('switch_theme', 'mangastream_cleanup');
?>
