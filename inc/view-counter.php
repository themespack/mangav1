<?php
// View counter functionality

function track_manga_views() {
    if (is_singular('manga') && !is_user_logged_in() || !current_user_can('edit_posts')) {
        global $post;
        
        // Check if already viewed today
        $viewed_today = get_transient('manga_viewed_' . $post->ID . '_' . get_client_ip());
        
        if (!$viewed_today) {
            // Update view counts
            $total_views = get_post_meta($post->ID, 'total_views', true) ?: 0;
            $daily_views = get_post_meta($post->ID, 'daily_views', true) ?: 0;
            $weekly_views = get_post_meta($post->ID, 'weekly_views', true) ?: 0;
            $monthly_views = get_post_meta($post->ID, 'monthly_views', true) ?: 0;
            
            update_post_meta($post->ID, 'total_views', $total_views + 1);
            update_post_meta($post->ID, 'daily_views', $daily_views + 1);
            update_post_meta($post->ID, 'weekly_views', $weekly_views + 1);
            update_post_meta($post->ID, 'monthly_views', $monthly_views + 1);
            update_post_meta($post->ID, 'last_viewed', current_time('mysql'));
            
            // Set transient to prevent multiple views from same IP
            set_transient('manga_viewed_' . $post->ID . '_' . get_client_ip(), true, DAY_IN_SECONDS);
        }
    }
}
add_action('wp_head', 'track_manga_views');

function get_client_ip() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return sanitize_text_field($ip);
}

// Reset daily views
function reset_daily_views() {
    global $wpdb;
    
    $wpdb->query("
        UPDATE {$wpdb->postmeta} 
        SET meta_value = '0' 
        WHERE meta_key = 'daily_views'
    ");
}

// Reset weekly views
function reset_weekly_views() {
    global $wpdb;
    
    $wpdb->query("
        UPDATE {$wpdb->postmeta} 
        SET meta_value = '0' 
        WHERE meta_key = 'weekly_views'
    ");
}

// Reset monthly views
function reset_monthly_views() {
    global $wpdb;
    
    $wpdb->query("
        UPDATE {$wpdb->postmeta} 
        SET meta_value = '0' 
        WHERE meta_key = 'monthly_views'
    ");
}

// Schedule view resets
if (!wp_next_scheduled('reset_daily_views_hook')) {
    wp_schedule_event(strtotime('tomorrow'), 'daily', 'reset_daily_views_hook');
}

if (!wp_next_scheduled('reset_weekly_views_hook')) {
    wp_schedule_event(strtotime('next monday'), 'weekly', 'reset_weekly_views_hook');
}

if (!wp_next_scheduled('reset_monthly_views_hook')) {
    wp_schedule_event(strtotime('first day of next month'), 'monthly', 'reset_monthly_views_hook');
}

add_action('reset_daily_views_hook', 'reset_daily_views');
add_action('reset_weekly_views_hook', 'reset_weekly_views');
add_action('reset_monthly_views_hook', 'reset_monthly_views');
?>
