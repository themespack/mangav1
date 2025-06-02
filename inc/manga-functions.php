<?php
// Helper functions for manga functionality

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function get_manga_by_slug($slug) {
    if (empty($slug)) {
        return null;
    }
    
    $manga = get_posts(array(
        'post_type' => 'manga',
        'name' => $slug,
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));
    
    return $manga ? $manga[0] : null;
}

function get_chapter_by_slug($chapter_slug, $manga_id) {
    if (empty($chapter_slug) || empty($manga_id)) {
        return null;
    }
    
    $chapter = get_posts(array(
        'post_type' => 'chapter',
        'name' => $chapter_slug,
        'meta_query' => array(
            array(
                'key' => 'manga_id',
                'value' => intval($manga_id),
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));
    
    return $chapter ? $chapter[0] : null;
}

function get_chapter_pages($chapter_id) {
    if (empty($chapter_id)) {
        return array();
    }
    
    $pages = get_post_meta($chapter_id, 'chapter_pages', true);
    
    if (!$pages || !is_array($pages)) {
        return array();
    }
    
    $formatted_pages = array();
    foreach ($pages as $page_data) {
        // Support both old format (attachment IDs) and new format (mixed data)
        if (is_numeric($page_data)) {
            // Old format: attachment ID
            $page_id = intval($page_data);
            $attachment = wp_get_attachment_image_src($page_id, 'full');
            
            if ($attachment && isset($attachment[0])) {
                $image_url = $attachment[0];
                
                // Fix URL domain issues
                $site_url = site_url();
                $parsed_site = parse_url($site_url);
                
                if (strpos($image_url, 'localhost') !== false) {
                    $image_url = str_replace('localhost', $parsed_site['host'], $image_url);
                }
                
                if (is_ssl()) {
                    $image_url = str_replace('http://', 'https://', $image_url);
                }
                
                // Validasi file exists
                $upload_dir = wp_upload_dir();
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);
                $file_exists = file_exists($file_path);
                
                $formatted_pages[] = array(
                    'id' => $page_id,
                    'url' => $image_url,
                    'width' => isset($attachment[1]) ? intval($attachment[1]) : 0,
                    'height' => isset($attachment[2]) ? intval($attachment[2]) : 0,
                    'alt' => get_post_meta($page_id, '_wp_attachment_image_alt', true) ?: 'Manga Page ' . (count($formatted_pages) + 1),
                    'type' => 'upload',
                    'file_exists' => $file_exists
                );
            }
        } elseif (is_array($page_data)) {
            // New format: array with type and data
            if ($page_data['type'] === 'external' && !empty($page_data['url'])) {
                // External image URL - tidak perlu cek file_exists
                $formatted_pages[] = array(
                    'id' => 0,
                    'url' => esc_url($page_data['url']),
                    'width' => isset($page_data['width']) ? intval($page_data['width']) : 0,
                    'height' => isset($page_data['height']) ? intval($page_data['height']) : 0,
                    'alt' => isset($page_data['alt']) ? $page_data['alt'] : 'Manga Page ' . (count($formatted_pages) + 1),
                    'type' => 'external',
                    'file_exists' => true // External URL dianggap selalu ada
                );
            } elseif ($page_data['type'] === 'upload' && !empty($page_data['id'])) {
                // Uploaded attachment
                $page_id = intval($page_data['id']);
                $attachment = wp_get_attachment_image_src($page_id, 'full');
                
                if ($attachment && isset($attachment[0])) {
                    // Validasi file exists
                    $upload_dir = wp_upload_dir();
                    $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $attachment[0]);
                    $file_exists = file_exists($file_path);
                    
                    $formatted_pages[] = array(
                        'id' => $page_id,
                        'url' => $attachment[0],
                        'width' => isset($attachment[1]) ? intval($attachment[1]) : 0,
                        'height' => isset($attachment[2]) ? intval($attachment[2]) : 0,
                        'alt' => get_post_meta($page_id, '_wp_attachment_image_alt', true) ?: 'Manga Page ' . (count($formatted_pages) + 1),
                        'type' => 'upload',
                        'file_exists' => $file_exists
                    );
                }
            }
        } elseif (is_string($page_data) && filter_var($page_data, FILTER_VALIDATE_URL)) {
            // Direct URL string (for backward compatibility)
            $formatted_pages[] = array(
                'id' => 0,
                'url' => esc_url($page_data),
                'width' => 0,
                'height' => 0,
                'alt' => 'Manga Page ' . (count($formatted_pages) + 1),
                'type' => 'external',
                'file_exists' => true // URL dianggap selalu ada
            );
        }
    }
    
    return $formatted_pages;
}

function get_manga_chapters($manga_id) {
    if (empty($manga_id)) {
        return array();
    }
    
    return get_posts(array(
        'post_type' => 'chapter',
        'meta_query' => array(
            array(
                'key' => 'manga_id',
                'value' => intval($manga_id),
                'compare' => '='
            )
        ),
        'meta_key' => 'chapter_number',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));
}

function get_latest_chapter($manga_id) {
    if (empty($manga_id)) {
        return null;
    }
    
    $chapters = get_posts(array(
        'post_type' => 'chapter',
        'meta_query' => array(
            array(
                'key' => 'manga_id',
                'value' => intval($manga_id),
                'compare' => '='
            )
        ),
        'meta_key' => 'chapter_number',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));
    
    if ($chapters) {
        $chapter = $chapters[0];
        $chapter_number = get_post_meta($chapter->ID, 'chapter_number', true);
        return (object) array(
            'ID' => $chapter->ID,
            'post_title' => $chapter->post_title,
            'post_name' => $chapter->post_name,
            'chapter_number' => floatval($chapter_number),
            'date' => get_the_date('', $chapter->ID),
            'url' => get_permalink($chapter->ID)
        );
    }
    
    return null;
}

function get_previous_chapter($chapter_id) {
    if (empty($chapter_id)) {
        return null;
    }
    
    $current_chapter = get_post($chapter_id);
    if (!$current_chapter || $current_chapter->post_type !== 'chapter') {
        return null;
    }
    
    $manga_id = get_post_meta($chapter_id, 'manga_id', true);
    $chapter_number = get_post_meta($chapter_id, 'chapter_number', true);
    
    if (!$manga_id || !$chapter_number) {
        return null;
    }
    
    $prev_chapter = get_posts(array(
        'post_type' => 'chapter',
        'meta_query' => array(
            array(
                'key' => 'manga_id',
                'value' => intval($manga_id),
                'compare' => '='
            ),
            array(
                'key' => 'chapter_number',
                'value' => floatval($chapter_number),
                'compare' => '<',
                'type' => 'NUMERIC'
            )
        ),
        'meta_key' => 'chapter_number',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));
    
    return $prev_chapter ? $prev_chapter[0] : null;
}

function get_next_chapter($chapter_id) {
    if (empty($chapter_id)) {
        return null;
    }
    
    $current_chapter = get_post($chapter_id);
    if (!$current_chapter || $current_chapter->post_type !== 'chapter') {
        return null;
    }
    
    $manga_id = get_post_meta($chapter_id, 'manga_id', true);
    $chapter_number = get_post_meta($chapter_id, 'chapter_number', true);
    
    if (!$manga_id || !$chapter_number) {
        return null;
    }
    
    $next_chapter = get_posts(array(
        'post_type' => 'chapter',
        'meta_query' => array(
            array(
                'key' => 'manga_id',
                'value' => intval($manga_id),
                'compare' => '='
            ),
            array(
                'key' => 'chapter_number',
                'value' => floatval($chapter_number),
                'compare' => '>',
                'type' => 'NUMERIC'
            )
        ),
        'meta_key' => 'chapter_number',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));
    
    return $next_chapter ? $next_chapter[0] : null;
}

function get_chapter_url($manga_slug, $chapter_slug) {
    if (empty($manga_slug) || empty($chapter_slug)) {
        return home_url();
    }
    
    return home_url("/manga/" . sanitize_title($manga_slug) . "/chapter/" . sanitize_title($chapter_slug) . "/");
}

function get_manga_url($manga_slug) {
    if (empty($manga_slug)) {
        return home_url();
    }
    
    return home_url("/manga/" . sanitize_title($manga_slug) . "/");
}

function get_chapter_page_url($manga_slug, $chapter_slug, $page_number) {
    if (empty($manga_slug) || empty($chapter_slug) || empty($page_number)) {
        return home_url();
    }
    
    return home_url("/manga/" . sanitize_title($manga_slug) . "/chapter/" . sanitize_title($chapter_slug) . "/page/" . intval($page_number) . "/");
}

// Breadcrumb function
function mangastream_breadcrumb() {
    echo '<nav class="breadcrumb">';
    echo '<a href="' . esc_url(home_url()) . '">Beranda</a> &raquo; ';
    
    if (is_singular('manga')) {
        echo '<a href="' . esc_url(home_url('/manga/')) . '">Manga</a> &raquo; ';
        
        $chapter_slug = get_query_var('chapter');
        if ($chapter_slug) {
            $manga_slug = get_post_field('post_name');
            echo '<a href="' . esc_url(get_manga_url($manga_slug)) . '">' . esc_html(get_the_title()) . '</a> &raquo; ';
            
            $chapter = get_chapter_by_slug($chapter_slug, get_the_ID());
            if ($chapter) {
                $chapter_number = get_post_meta($chapter->ID, 'chapter_number', true);
                echo 'Chapter ' . esc_html($chapter_number);
            }
        } else {
            echo esc_html(get_the_title());
        }
    } elseif (is_post_type_archive('manga')) {
        echo 'Manga';
    } elseif (is_tax('manga_genre')) {
        echo '<a href="' . esc_url(home_url('/manga/')) . '">Manga</a> &raquo; ';
        echo 'Genre: ' . esc_html(single_term_title('', false));
    } elseif (is_tax('manga_status')) {
        echo '<a href="' . esc_url(home_url('/manga/')) . '">Manga</a> &raquo; ';
        echo 'Status: ' . esc_html(single_term_title('', false));
    }
    
    echo '</nav>';
}

// Get manga reading progress
function get_manga_reading_progress($manga_id, $user_id = null) {
    if (empty($manga_id)) {
        return null;
    }
    
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return null;
    }
    
    $progress = get_user_meta($user_id, 'manga_progress_' . intval($manga_id), true);
    $total_chapters = count(get_manga_chapters($manga_id));
    
    return $progress ?: array(
        'last_chapter' => 0,
        'last_read' => '',
        'total_chapters' => $total_chapters,
        'progress_percentage' => 0
    );
}

// Update manga reading progress
function update_manga_reading_progress($manga_id, $chapter_number, $user_id = null) {
    if (empty($manga_id) || empty($chapter_number)) {
        return false;
    }
    
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $total_chapters = count(get_manga_chapters($manga_id));
    $progress_percentage = $total_chapters > 0 ? round(($chapter_number / $total_chapters) * 100, 2) : 0;
    
    $progress = array(
        'last_chapter' => floatval($chapter_number),
        'last_read' => current_time('mysql'),
        'total_chapters' => $total_chapters,
        'progress_percentage' => $progress_percentage
    );
    
    return update_user_meta($user_id, 'manga_progress_' . intval($manga_id), $progress);
}

// Get manga statistics
function get_manga_stats($manga_id) {
    if (empty($manga_id)) {
        return array();
    }
    
    $total_views = get_post_meta($manga_id, 'total_views', true) ?: 0;
    $daily_views = get_post_meta($manga_id, 'daily_views', true) ?: 0;
    $weekly_views = get_post_meta($manga_id, 'weekly_views', true) ?: 0;
    $monthly_views = get_post_meta($manga_id, 'monthly_views', true) ?: 0;
    $total_chapters = count(get_manga_chapters($manga_id));
    $rating = get_post_meta($manga_id, 'manga_rating', true) ?: 0;
    $rating_count = get_post_meta($manga_id, 'rating_count', true) ?: 0;
    
    return array(
        'total_views' => intval($total_views),
        'daily_views' => intval($daily_views),
        'weekly_views' => intval($weekly_views),
        'monthly_views' => intval($monthly_views),
        'total_chapters' => $total_chapters,
        'rating' => floatval($rating),
        'rating_count' => intval($rating_count),
        'last_updated' => get_the_modified_date('Y-m-d H:i:s', $manga_id)
    );
}

// Validate chapter access
function validate_chapter_access($chapter_id, $manga_id) {
    if (empty($chapter_id) || empty($manga_id)) {
        return false;
    }
    
    $chapter = get_post($chapter_id);
    
    if (!$chapter || $chapter->post_type !== 'chapter') {
        return false;
    }
    
    $chapter_manga_id = get_post_meta($chapter_id, 'manga_id', true);
    
    if (intval($chapter_manga_id) !== intval($manga_id)) {
        return false;
    }
    
    return $chapter->post_status === 'publish';
}

// Get related manga based on genres
function get_related_manga($manga_id, $limit = 6) {
    if (empty($manga_id)) {
        return array();
    }
    
    $genres = get_the_terms($manga_id, 'manga_genre');
    
    if (!$genres || is_wp_error($genres)) {
        return array();
    }
    
    $genre_ids = wp_list_pluck($genres, 'term_id');
    
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => intval($limit),
        'post__not_in' => array(intval($manga_id)),
        'tax_query' => array(
            array(
                'taxonomy' => 'manga_genre',
                'field' => 'term_id',
                'terms' => $genre_ids,
                'operator' => 'IN'
            )
        ),
        'orderby' => 'rand',
        'post_status' => 'publish'
    );
    
    return get_posts($args);
}

// Format view count for display
function format_view_count($count) {
    $count = intval($count);
    
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M';
    } elseif ($count >= 1000) {
        return round($count / 1000, 1) . 'K';
    }
    
    return number_format($count);
}

// Get chapter navigation data
function get_chapter_navigation($chapter_id) {
    if (empty($chapter_id)) {
        return array();
    }
    
    $prev_chapter = get_previous_chapter($chapter_id);
    $next_chapter = get_next_chapter($chapter_id);
    $manga_id = get_post_meta($chapter_id, 'manga_id', true);
    $all_chapters = get_manga_chapters($manga_id);
    
    return array(
        'prev' => $prev_chapter,
        'next' => $next_chapter,
        'all' => $all_chapters,
        'current' => get_post($chapter_id)
    );
}

// Check if user has bookmarked manga
function is_manga_bookmarked($manga_id, $user_id = null) {
    if (empty($manga_id)) {
        return false;
    }
    
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $bookmarks = get_user_meta($user_id, 'manga_bookmarks', true) ?: array();
    return in_array(intval($manga_id), array_map('intval', $bookmarks));
}

// Add or remove manga bookmark
function toggle_manga_bookmark($manga_id, $user_id = null) {
    if (empty($manga_id)) {
        return false;
    }
    
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $bookmarks = get_user_meta($user_id, 'manga_bookmarks', true) ?: array();
    $manga_id = intval($manga_id);
    
    if (in_array($manga_id, $bookmarks)) {
        // Remove bookmark
        $bookmarks = array_diff($bookmarks, array($manga_id));
        $action = 'removed';
    } else {
        // Add bookmark
        $bookmarks[] = $manga_id;
        $action = 'added';
    }
    
    update_user_meta($user_id, 'manga_bookmarks', array_values(array_unique($bookmarks)));
    
    return array(
        'action' => $action,
        'count' => count($bookmarks)
    );
}

// Get trending manga
function get_trending_manga($days = 7, $limit = 10) {
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => intval($limit),
        'meta_key' => 'weekly_views',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'post_status' => 'publish',
        'date_query' => array(
            array(
                'after' => intval($days) . ' days ago'
            )
        )
    );
    
    return get_posts($args);
}

// Get popular manga by time period
function get_popular_manga($period = 'total', $limit = 10) {
    $meta_key = 'total_views';
    
    switch ($period) {
        case 'daily':
            $meta_key = 'daily_views';
            break;
        case 'weekly':
            $meta_key = 'weekly_views';
            break;
        case 'monthly':
            $meta_key = 'monthly_views';
            break;
        default:
            $meta_key = 'total_views';
    }
    
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => intval($limit),
        'meta_key' => $meta_key,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'post_status' => 'publish'
    );
    
    return get_posts($args);
}

// Sanitize manga data
function sanitize_manga_data($data) {
    if (!is_array($data)) {
        return array();
    }
    
    $sanitized = array();
    
    $fields = array(
        'manga_author' => 'sanitize_text_field',
        'manga_artist' => 'sanitize_text_field',
        'manga_year' => 'absint',
        'manga_rating' => 'floatval',
        'manga_status' => 'sanitize_text_field'
    );
    
    foreach ($fields as $field => $sanitizer) {
        if (isset($data[$field])) {
            $sanitized[$field] = call_user_func($sanitizer, $data[$field]);
        }
    }
    
    return $sanitized;
}

// Update manga view count
function increment_manga_views($manga_id) {
    if (empty($manga_id)) {
        return false;
    }
    
    // Check if already viewed today by this IP
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $viewed_key = 'manga_viewed_' . $manga_id . '_' . md5($ip);
    
    if (get_transient($viewed_key)) {
        return false; // Already viewed today
    }
    
    // Update view counts
    $total_views = get_post_meta($manga_id, 'total_views', true) ?: 0;
    $daily_views = get_post_meta($manga_id, 'daily_views', true) ?: 0;
    $weekly_views = get_post_meta($manga_id, 'weekly_views', true) ?: 0;
    $monthly_views = get_post_meta($manga_id, 'monthly_views', true) ?: 0;
    
    update_post_meta($manga_id, 'total_views', intval($total_views) + 1);
    update_post_meta($manga_id, 'daily_views', intval($daily_views) + 1);
    update_post_meta($manga_id, 'weekly_views', intval($weekly_views) + 1);
    update_post_meta($manga_id, 'monthly_views', intval($monthly_views) + 1);
    update_post_meta($manga_id, 'last_viewed', current_time('mysql'));
    
    // Set transient to prevent multiple views from same IP
    set_transient($viewed_key, true, DAY_IN_SECONDS);
    
    return true;
}

// Get user bookmarks
function get_user_bookmarks($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return array();
    }
    
    return get_user_meta($user_id, 'manga_bookmarks', true) ?: array();
}

// Get manga progress
function get_manga_progress($manga_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return null;
    }
    
    $progress = get_user_meta($user_id, 'manga_progress_' . $manga_id, true);
    return $progress ?: array('last_chapter' => 0, 'last_read' => '');
}

// Update manga progress
function update_manga_progress($manga_id, $chapter_number, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $progress = array(
        'last_chapter' => $chapter_number,
        'last_read' => current_time('mysql')
    );
    
    return update_user_meta($user_id, 'manga_progress_' . $manga_id, $progress);
}
?>
