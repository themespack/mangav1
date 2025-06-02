<?php
// AJAX handlers for various functions

// Load more manga dengan filter
function ajax_load_more_manga_filtered() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    $page = intval($_POST['page']);
    $sort_by = sanitize_text_field($_POST['sort_by'] ?? '');
    $status = sanitize_text_field($_POST['status'] ?? '');
    $genre = sanitize_text_field($_POST['genre'] ?? '');
    $search = sanitize_text_field($_POST['search'] ?? '');
    
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => get_theme_mod('mangastream_manga_per_page', 24),
        'paged' => $page,
        'post_status' => 'publish'
    );
    
    // Apply same filters as ajax_filter_manga
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    $tax_query = array();
    if (!empty($status)) {
        $tax_query[] = array(
            'taxonomy' => 'manga_status',
            'field' => 'slug',
            'terms' => $status
        );
    }
    
    if (!empty($genre)) {
        $tax_query[] = array(
            'taxonomy' => 'manga_genre',
            'field' => 'slug',
            'terms' => $genre
        );
    }
    
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }
    
    // Apply sorting
    switch ($sort_by) {
        case 'popular':
            $args['meta_key'] = 'total_views';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'title':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'rating':
            $args['meta_key'] = 'manga_rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        default:
            $args['orderby'] = 'modified';
            $args['order'] = 'DESC';
    }
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/manga-card');
        }
        wp_send_json_success(array('has_more' => $query->max_num_pages > $page));
    } else {
        wp_send_json_success(array('has_more' => false));
    }
    
    wp_reset_postdata();
}
add_action('wp_ajax_load_more_manga_filtered', 'ajax_load_more_manga_filtered');
add_action('wp_ajax_nopriv_load_more_manga_filtered', 'ajax_load_more_manga_filtered');

// Filter manga
function ajax_filter_manga() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    $sort_by = sanitize_text_field($_POST['sort_by']);
    $status = sanitize_text_field($_POST['status']);
    $genre = sanitize_text_field($_POST['genre']);
    $search = sanitize_text_field($_POST['search']);
    
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => get_theme_mod('mangastream_manga_per_page', 24),
        'post_status' => 'publish'
    );
    
    // Add search
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    // Add taxonomy filters
    $tax_query = array();
    
    if (!empty($status)) {
        $tax_query[] = array(
            'taxonomy' => 'manga_status',
            'field' => 'slug',
            'terms' => $status
        );
    }
    
    if (!empty($genre)) {
        $tax_query[] = array(
            'taxonomy' => 'manga_genre',
            'field' => 'slug',
            'terms' => $genre
        );
    }
    
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }
    
    // Add sorting
    switch ($sort_by) {
        case 'popular':
            $args['meta_key'] = 'total_views';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'title':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'rating':
            $args['meta_key'] = 'manga_rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'completed':
            $args['tax_query'][] = array(
                'taxonomy' => 'manga_status',
                'field' => 'slug',
                'terms' => 'completed'
            );
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        default: // latest
            $args['orderby'] = 'modified';
            $args['order'] = 'DESC';
    }
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/manga-card');
        }
    } else {
        echo '<div class="no-results">Tidak ada manga yang ditemukan.</div>';
    }
    
    wp_reset_postdata();
    wp_die();
}
add_action('wp_ajax_filter_manga', 'ajax_filter_manga');
add_action('wp_ajax_nopriv_filter_manga', 'ajax_filter_manga');

// Load more manga for infinite scroll
function ajax_load_more_manga() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    $page = intval($_POST['page']);
    
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => get_theme_mod('mangastream_manga_per_page', 24),
        'paged' => $page,
        'post_status' => 'publish',
        'orderby' => 'modified',
        'order' => 'DESC'
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/manga-card');
        }
    }
    
    wp_reset_postdata();
    wp_die();
}
add_action('wp_ajax_load_more_manga', 'ajax_load_more_manga');
add_action('wp_ajax_nopriv_load_more_manga', 'ajax_load_more_manga');

// Bookmark manga
function ajax_bookmark_manga() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to bookmark manga.');
    }
    
    $manga_id = intval($_POST['manga_id']);
    
    if (!$manga_id || get_post_type($manga_id) !== 'manga') {
        wp_send_json_error('Invalid manga ID.');
    }
    
    $result = toggle_manga_bookmark($manga_id);
    
    if ($result) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error('Failed to update bookmark.');
    }
}
add_action('wp_ajax_bookmark_manga', 'ajax_bookmark_manga');

// Get random manga
function ajax_random_manga() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'post_status' => 'publish'
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            $data = array(
                'title' => get_the_title(),
                'url' => get_permalink(),
                'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'manga-thumbnail') ?: '',
                'excerpt' => wp_trim_words(get_the_excerpt(), 15)
            );
            
            wp_send_json_success($data);
        }
    }
    
    wp_reset_postdata();
    wp_send_json_error('No manga found.');
}
add_action('wp_ajax_random_manga', 'ajax_random_manga');
add_action('wp_ajax_nopriv_random_manga', 'ajax_random_manga');

// Update view count
function ajax_update_view_count() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    $post_id = intval($_POST['post_id']);
    $post_type = get_post_type($post_id);
    
    if ($post_type == 'manga') {
        $total_views = get_post_meta($post_id, 'total_views', true) ?: 0;
        $daily_views = get_post_meta($post_id, 'daily_views', true) ?: 0;
        
        update_post_meta($post_id, 'total_views', $total_views + 1);
        update_post_meta($post_id, 'daily_views', $daily_views + 1);
        update_post_meta($post_id, 'last_viewed', current_time('mysql'));
    }
    
    wp_send_json_success();
}
add_action('wp_ajax_update_view_count', 'ajax_update_view_count');
add_action('wp_ajax_nopriv_update_view_count', 'ajax_update_view_count');

// Update reading progress
function ajax_update_reading_progress() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $manga_id = intval($_POST['manga_id']);
    $chapter_number = floatval($_POST['chapter_number']);
    
    if (!$manga_id || !$chapter_number) {
        wp_send_json_error('Invalid data');
    }
    
    $result = update_manga_reading_progress($manga_id, $chapter_number);
    
    if ($result) {
        wp_send_json_success('Progress updated');
    } else {
        wp_send_json_error('Failed to update progress');
    }
}
add_action('wp_ajax_update_reading_progress', 'ajax_update_reading_progress');

// Bookmark chapter
function ajax_bookmark_chapter() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $manga_id = intval($_POST['manga_id']);
    $chapter_number = floatval($_POST['chapter_number']);
    
    if (!$manga_id) {
        wp_send_json_error('Invalid manga ID');
    }
    
    $result = toggle_manga_bookmark($manga_id);
    
    wp_send_json_success($result);
}
add_action('wp_ajax_bookmark_chapter', 'ajax_bookmark_chapter');

// Load chapter comments
function ajax_load_chapter_comments() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    $chapter_id = intval($_POST['chapter_id']);
    
    if (!$chapter_id || get_post_type($chapter_id) !== 'chapter') {
        wp_send_json_error('Invalid chapter ID');
    }
    
    // Get comments for chapter
    $comments = get_comments(array(
        'post_id' => $chapter_id,
        'status' => 'approve',
        'order' => 'ASC'
    ));
    
    ob_start();
    if ($comments) {
        echo '<ul class="comment-list">';
        wp_list_comments(array(
            'callback' => 'mangastream_comment_callback',
            'style' => 'ul'
        ), $comments);
        echo '</ul>';
    } else {
        echo '<p class="no-comments">Belum ada komentar untuk chapter ini.</p>';
    }
    $comments_html = ob_get_clean();
    
    // Get comment form
    ob_start();
    comment_form(array(
        'title_reply' => 'Tinggalkan Komentar',
        'title_reply_to' => 'Balas ke %s',
        'cancel_reply_link' => 'Batal Balas',
        'label_submit' => 'Kirim Komentar',
        'comment_field' => '<p class="comment-form-comment"><label for="comment">Komentar *</label><textarea id="comment" name="comment" cols="45" rows="6" required></textarea></p>',
        'fields' => array(
            'author' => '<p class="comment-form-author"><label for="author">Nama *</label><input id="author" name="author" type="text" size="30" required /></p>',
            'email' => '<p class="comment-form-email"><label for="email">Email *</label><input id="email" name="email" type="email" size="30" required /></p>',
            'url' => '<p class="comment-form-url"><label for="url">Website</label><input id="url" name="url" type="url" size="30" /></p>'
        )
    ), $chapter_id);
    $form_html = ob_get_clean();
    
    wp_send_json_success(array(
        'comments' => $comments_html,
        'form' => $form_html
    ));
}
add_action('wp_ajax_load_chapter_comments', 'ajax_load_chapter_comments');
add_action('wp_ajax_nopriv_load_chapter_comments', 'ajax_load_chapter_comments');

// Submit chapter comment
function ajax_submit_chapter_comment() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    $chapter_id = intval($_POST['chapter_id']);
    $comment_content = sanitize_textarea_field($_POST['comment']);
    $author_name = sanitize_text_field($_POST['author']);
    $author_email = sanitize_email($_POST['email']);
    $author_url = esc_url_raw($_POST['url']);
    
    if (!$chapter_id || !$comment_content || !$author_name || !$author_email) {
        wp_send_json_error('Required fields missing');
    }
    
    $comment_data = array(
        'comment_post_ID' => $chapter_id,
        'comment_content' => $comment_content,
        'comment_author' => $author_name,
        'comment_author_email' => $author_email,
        'comment_author_url' => $author_url,
        'comment_type' => '',
        'comment_approved' => 0, // Pending approval
        'user_id' => get_current_user_id()
    );
    
    $comment_id = wp_insert_comment($comment_data);
    
    if ($comment_id) {
        wp_send_json_success('Comment submitted successfully');
    } else {
        wp_send_json_error('Failed to submit comment');
    }
}
add_action('wp_ajax_submit_chapter_comment', 'ajax_submit_chapter_comment');
add_action('wp_ajax_nopriv_submit_chapter_comment', 'ajax_submit_chapter_comment');

// Rate manga
function ajax_rate_manga() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $manga_id = intval($_POST['manga_id']);
    $rating = floatval($_POST['rating']);
    
    if (!$manga_id || $rating < 1 || $rating > 5) {
        wp_send_json_error('Invalid data');
    }
    
    $user_id = get_current_user_id();
    
    // Save user rating
    update_user_meta($user_id, 'manga_rating_' . $manga_id, $rating);
    
    // Calculate average rating
    $ratings = array();
    $users = get_users();
    
    foreach ($users as $user) {
        $user_rating = get_user_meta($user->ID, 'manga_rating_' . $manga_id, true);
        if ($user_rating) {
            $ratings[] = floatval($user_rating);
        }
    }
    
    if (!empty($ratings)) {
        $average_rating = array_sum($ratings) / count($ratings);
        update_post_meta($manga_id, 'manga_rating', round($average_rating, 1));
        update_post_meta($manga_id, 'rating_count', count($ratings));
    }
    
    wp_send_json_success(array(
        'average_rating' => round($average_rating, 1),
        'rating_count' => count($ratings)
    ));
}
add_action('wp_ajax_rate_manga', 'ajax_rate_manga');

// Get user bookmarks
function ajax_get_user_bookmarks() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $user_id = get_current_user_id();
    $bookmarks = get_user_meta($user_id, 'manga_bookmarks', true) ?: array();
    
    $manga_data = array();
    
    foreach ($bookmarks as $manga_id) {
        $manga = get_post($manga_id);
        if ($manga && $manga->post_type === 'manga') {
            $manga_data[] = array(
                'id' => $manga_id,
                'title' => $manga->post_title,
                'url' => get_permalink($manga_id),
                'thumbnail' => get_the_post_thumbnail_url($manga_id, 'manga-thumbnail'),
                'last_chapter' => get_latest_chapter($manga_id)
            );
        }
    }
    
    wp_send_json_success($manga_data);
}
add_action('wp_ajax_get_user_bookmarks', 'ajax_get_user_bookmarks');

// Search suggestions
function ajax_search_suggestions() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    $term = sanitize_text_field($_POST['term']);
    
    if (strlen($term) < 2) {
        wp_send_json_success(array());
    }
    
    // Search in manga titles
    $manga_results = get_posts(array(
        'post_type' => 'manga',
        's' => $term,
        'posts_per_page' => 5,
        'post_status' => 'publish'
    ));
    
    // Search in genres
    $genre_results = get_terms(array(
        'taxonomy' => 'manga_genre',
        'name__like' => $term,
        'number' => 3
    ));
    
    $suggestions = array();
    
    // Add manga suggestions
    foreach ($manga_results as $manga) {
        $suggestions[] = array(
            'type' => 'manga',
            'title' => $manga->post_title,
            'url' => get_permalink($manga->ID)
        );
    }
    
    // Add genre suggestions
    foreach ($genre_results as $genre) {
        $suggestions[] = array(
            'type' => 'genre',
            'title' => 'Genre: ' . $genre->name,
            'url' => get_term_link($genre)
        );
    }
    
    wp_send_json_success($suggestions);
}
add_action('wp_ajax_search_suggestions', 'ajax_search_suggestions');
add_action('wp_ajax_nopriv_search_suggestions', 'ajax_search_suggestions');

// Report content
function ajax_report_content() {
    check_ajax_referer('mangastream_nonce', 'nonce');
    
    $post_id = intval($_POST['post_id']);
    $reason = sanitize_text_field($_POST['reason']);
    $description = sanitize_textarea_field($_POST['description']);
    
    if (!$post_id || !$reason) {
        wp_send_json_error('Required fields missing');
    }
    
    // Save report
    $report_data = array(
        'post_id' => $post_id,
        'reason' => $reason,
        'description' => $description,
        'reporter_ip' => $_SERVER['REMOTE_ADDR'],
        'date' => current_time('mysql')
    );
    
    $reports = get_option('manga_reports', array());
    $reports[] = $report_data;
    update_option('manga_reports', $reports);
    
    // Send email to admin (optional)
    $admin_email = get_option('admin_email');
    $subject = 'Content Report - ' . get_bloginfo('name');
    $message = "A content report has been submitted:\n\n";
    $message .= "Post ID: " . $post_id . "\n";
    $message .= "Reason: " . $reason . "\n";
    $message .= "Description: " . $description . "\n";
    $message .= "Date: " . current_time('mysql') . "\n";
    
    wp_mail($admin_email, $subject, $message);
    
    wp_send_json_success('Report submitted successfully');
}
add_action('wp_ajax_report_content', 'ajax_report_content');
add_action('wp_ajax_nopriv_report_content', 'ajax_report_content');
?>
