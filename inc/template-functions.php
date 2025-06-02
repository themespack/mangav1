<?php
// Template helper functions

// Safe array access helper
function safe_array_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Safe page data access
function get_safe_page_data($page, $key, $default = null) {
    if (!is_array($page)) {
        return $default;
    }
    
    return isset($page[$key]) ? $page[$key] : $default;
}

function get_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Average reading speed
    return $reading_time . ' min read';
}

function get_manga_status_color($status_slug) {
    $colors = array(
        'ongoing' => '#28a745',
        'completed' => '#dc3545',
        'hiatus' => '#ffc107',
        'dropped' => '#6c757d'
    );
    
    return isset($colors[$status_slug]) ? $colors[$status_slug] : '#007cba';
}
?>
