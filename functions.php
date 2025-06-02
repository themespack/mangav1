<?php
// Debug rewrite rules - hapus setelah testing
function debug_rewrite_rules() {
    if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['debug_rewrite'])) {
        global $wp_rewrite;
        echo '<pre>';
        print_r($wp_rewrite->rules);
        echo '</pre>';
        exit;
    }
}
add_action('init', 'debug_rewrite_rules');

// Safe Selected Functions
function safe_selected($current, $value) {
    if (is_array($current)) {
        return in_array($value, $current) ? 'selected="selected"' : '';
    }
    return selected($current, $value, false);
}

function safe_checked($current, $value) {
    if (is_array($current)) {
        return in_array($value, $current) ? 'checked="checked"' : '';
    }
    return checked($current, $value, false);
}

function mangastream_theme_setup() {
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('post-formats', array('aside', 'gallery', 'quote', 'image', 'video'));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => 'Primary Menu',
        'footer' => 'Footer Menu'
    ));

    // Add image sizes
    add_image_size('manga-thumbnail', 200, 280, true);
    add_image_size('manga-large', 800, 1200, true);
    add_image_size('manga-medium', 400, 600, true);
    add_image_size('manga-small', 150, 200, true);
}
add_action('after_setup_theme', 'mangastream_theme_setup');

// Function untuk force refresh cache semua assets
function mangastream_force_refresh_assets() {
    if (current_user_can('manage_options') && isset($_GET['refresh_assets'])) {
        // Update theme version untuk force refresh
        $current_version = wp_get_theme()->get('Version');
        $new_version = $current_version . '.' . time();
        
        // Set transient untuk version baru
        set_transient('mangastream_force_version', $new_version, HOUR_IN_SECONDS);
        
        // Redirect tanpa query parameter
        wp_redirect(remove_query_arg('refresh_assets'));
        exit;
    }
}
add_action('init', 'mangastream_force_refresh_assets');

// Function untuk get version dengan force refresh support
function get_mangastream_version() {
    $force_version = get_transient('mangastream_force_version');
    
    if ($force_version) {
        return $force_version;
    }
    
    return wp_get_theme()->get('Version') ?: '1.0.0';
}

// Enqueue scripts and styles dengan file terpisah dan conditional loading
function mangastream_scripts() {
    // Detect environment
    $is_development = (defined('WP_DEBUG') && WP_DEBUG) || (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG);
    
    // Get theme version
    $theme_version = get_mangastream_version();
    
    if ($is_development) {
        // Development: gunakan timestamp untuk force reload setiap kali
        $version_suffix = time();
    } else {
        // Production: gunakan file modification time untuk cache yang efisien
        $version_suffix = '';
    }
    
    // CSS Files
    $css_files = array(
        'mangastream-style' => array(
            'url' => get_stylesheet_uri(),
            'path' => get_stylesheet_directory() . '/style.css',
            'deps' => array(),
            'condition' => true
        ),
        'mangastream-dark' => array(
            'url' => get_template_directory_uri() . '/css/dark-mode.css',
            'path' => get_template_directory() . '/css/dark-mode.css',
            'deps' => array('mangastream-style'),
            'condition' => true
        ),
        'mangastream-responsive' => array(
            'url' => get_template_directory_uri() . '/css/responsive.css',
            'path' => get_template_directory() . '/css/responsive.css',
            'deps' => array('mangastream-style'),
            'condition' => true
        ),
        'mangastream-reader' => array(
            'url' => get_template_directory_uri() . '/css/reader.css',
            'path' => get_template_directory() . '/css/reader.css',
            'deps' => array('mangastream-style'),
            'condition' => is_singular('manga') && get_query_var('chapter')
        ),
        'mangastream-archive' => array(
            'url' => get_template_directory_uri() . '/css/archive.css',
            'path' => get_template_directory() . '/css/archive.css',
            'deps' => array('mangastream-style'),
            'condition' => is_post_type_archive('manga') || is_tax('manga_genre') || is_tax('manga_status')
        )
    );
    
    foreach ($css_files as $handle => $file) {
        if ($file['condition']) {
            if ($is_development) {
                $version = $theme_version . '.' . $version_suffix;
            } else {
                $version = $theme_version . '.' . (file_exists($file['path']) ? filemtime($file['path']) : time());
            }
            
            wp_enqueue_style($handle, $file['url'], $file['deps'], $version);
        }
    }
    
    // External CSS
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
    
    // JavaScript Files dengan conditional loading
    $js_files = array(
        'mangastream-main' => array(
            'url' => get_template_directory_uri() . '/js/main.js',
            'path' => get_template_directory() . '/js/main.js',
            'deps' => array('jquery'),
            'condition' => true, // Always load
            'in_footer' => true
        ),
        'mangastream-navigation' => array(
            'url' => get_template_directory_uri() . '/js/navigation.js',
            'path' => get_template_directory() . '/js/navigation.js',
            'deps' => array('jquery'),
            'condition' => true, // Always load
            'in_footer' => true
        ),
        'mangastream-search' => array(
            'url' => get_template_directory_uri() . '/js/search.js',
            'path' => get_template_directory() . '/js/search.js',
            'deps' => array('jquery'),
            'condition' => true, // Always load
            'in_footer' => true
        ),
        'mangastream-dark-mode' => array(
            'url' => get_template_directory_uri() . '/js/dark-mode.js',
            'path' => get_template_directory() . '/js/dark-mode.js',
            'deps' => array('jquery'),
            'condition' => true, // Always load
            'in_footer' => true
        ),
        'mangastream-reader' => array(
            'url' => get_template_directory_uri() . '/js/reader.js',
            'path' => get_template_directory() . '/js/reader.js',
            'deps' => array('jquery'),
            'condition' => is_singular('manga') && get_query_var('chapter'), // Only on chapter pages
            'in_footer' => true
        ),
        'mangastream-filters' => array(
            'url' => get_template_directory_uri() . '/js/filters.js',
            'path' => get_template_directory() . '/js/filters.js',
            'deps' => array('jquery'),
            'condition' => is_post_type_archive('manga') || is_tax('manga_genre') || is_tax('manga_status'), // Only on archive pages
            'in_footer' => true
        ),
        'mangastream-single' => array(
            'url' => get_template_directory_uri() . '/js/single.js',
            'path' => get_template_directory() . '/js/single.js',
            'deps' => array('jquery'),
            'condition' => is_singular('manga') && !get_query_var('chapter'), // Only on manga info pages
            'in_footer' => true
        ),
        'mangastream-comments' => array(
            'url' => get_template_directory_uri() . '/js/comments.js',
            'path' => get_template_directory() . '/js/comments.js',
            'deps' => array('jquery'),
            'condition' => is_singular() && (comments_open() || get_comments_number()), // Only when comments are enabled
            'in_footer' => true
        )
    );
    
    foreach ($js_files as $handle => $file) {
        if ($file['condition']) {
            if ($is_development) {
                $version = $theme_version . '.' . $version_suffix;
            } else {
                $version = $theme_version . '.' . (file_exists($file['path']) ? filemtime($file['path']) : time());
            }
            
            wp_enqueue_script($handle, $file['url'], $file['deps'], $version, $file['in_footer']);
        }
    }
    
    // Admin JS
    if (is_admin()) {
        $admin_js_path = get_template_directory() . '/js/admin.js';
        $admin_version = $is_development ? 
            $theme_version . '.' . $version_suffix : 
            $theme_version . '.' . (file_exists($admin_js_path) ? filemtime($admin_js_path) : time());
            
        wp_enqueue_script('mangastream-admin', get_template_directory_uri() . '/js/admin.js', array('jquery', 'wp-media'), $admin_version, true);
        
        // Localize admin script
        wp_localize_script('mangastream-admin', 'manga_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('manga_admin_nonce'),
            'theme_version' => $theme_version
        ));
    }
    
    // Localize script untuk semua JS files yang membutuhkan AJAX
    $localize_data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mangastream_nonce'),
        'theme_version' => $theme_version,
        'is_development' => $is_development,
        'is_user_logged_in' => is_user_logged_in(),
        'current_user_id' => get_current_user_id(),
        'home_url' => home_url(),
        'theme_url' => get_template_directory_uri(),
        'site_name' => get_bloginfo('name'),
        'current_page' => array(
            'is_home' => is_home(),
            'is_single' => is_singular('manga'),
            'is_chapter' => is_singular('manga') && get_query_var('chapter'),
            'is_archive' => is_post_type_archive('manga'),
            'is_search' => is_search()
        )
    );
    
    // Localize ke main.js (yang selalu dimuat)
    wp_localize_script('mangastream-main', 'mangastream_ajax', $localize_data);
}
add_action('wp_enqueue_scripts', 'mangastream_scripts');

// Debug version information (hanya untuk admin)
function debug_asset_versions() {
    if (current_user_can('manage_options') && isset($_GET['debug_versions'])) {
        echo '<div style="background: #fff; padding: 20px; margin: 20px; border: 1px solid #ccc; position: fixed; top: 50px; right: 20px; z-index: 99999; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); max-height: 80vh; overflow-y: auto;">';
        echo '<h3>Asset Versions Debug</h3>';
        
        $theme_version = get_mangastream_version();
        echo '<p><strong>Theme Version:</strong> ' . $theme_version . '</p>';
        
        $files = array(
            'CSS Files' => array(
                'style.css' => get_stylesheet_directory() . '/style.css',
                'dark-mode.css' => get_template_directory() . '/css/dark-mode.css',
                'responsive.css' => get_template_directory() . '/css/responsive.css',
                'reader.css' => get_template_directory() . '/css/reader.css',
                'archive.css' => get_template_directory() . '/css/archive.css'
            ),
            'JS Files' => array(
                'main.js' => get_template_directory() . '/js/main.js',
                'navigation.js' => get_template_directory() . '/js/navigation.js',
                'search.js' => get_template_directory() . '/js/search.js',
                'dark-mode.js' => get_template_directory() . '/js/dark-mode.js',
                'reader.js' => get_template_directory() . '/js/reader.js',
                'filters.js' => get_template_directory() . '/js/filters.js',
                'single.js' => get_template_directory() . '/js/single.js',
                'comments.js' => get_template_directory() . '/js/comments.js',
                'admin.js' => get_template_directory() . '/js/admin.js'
            )
        );
        
        foreach ($files as $category => $file_list) {
            echo '<h4>' . $category . '</h4>';
            foreach ($file_list as $name => $path) {
                if (file_exists($path)) {
                    $mtime = filemtime($path);
                    $version = $theme_version . '.' . $mtime;
                    $size = round(filesize($path) / 1024, 2);
                    echo '<p><strong>' . $name . ':</strong><br>';
                    echo 'Version: ' . $version . '<br>';
                    echo 'Size: ' . $size . ' KB<br>';
                    echo '<small>Modified: ' . date('Y-m-d H:i:s', $mtime) . '</small></p>';
                } else {
                    echo '<p><strong>' . $name . ':</strong> <span style="color: red;">File not found</span></p>';
                }
            }
        }
        
        echo '<hr>';
        echo '<p><strong>Current Page:</strong></p>';
        echo '<ul>';
        echo '<li>Is Home: ' . (is_home() ? 'Yes' : 'No') . '</li>';
        echo '<li>Is Single Manga: ' . (is_singular('manga') ? 'Yes' : 'No') . '</li>';
        echo '<li>Is Chapter: ' . (get_query_var('chapter') ? 'Yes' : 'No') . '</li>';
        echo '<li>Is Archive: ' . (is_post_type_archive('manga') ? 'Yes' : 'No') . '</li>';
        echo '<li>Is Search: ' . (is_search() ? 'Yes' : 'No') . '</li>';
        echo '</ul>';
        
        echo '<hr>';
        echo '<p><a href="?refresh_assets=1" style="background: #007cba; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px;">Force Refresh All Assets</a>';
        echo '<a href="' . remove_query_arg('debug_versions') . '" style="color: #666; text-decoration: none;">Close Debug</a></p>';
        echo '</div>';
    }
}
add_action('wp_footer', 'debug_asset_versions');

// Debug progress bar issues
function debug_progress_bar_styles() {
    if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['debug_progress'])) {
        ?>
        <style>
        /* Debug styles untuk progress bar */
        .progress-bar {
            background: red !important;
            opacity: 0.8 !important;
            height: 10px !important;
            z-index: 99999 !important;
        }
        
        .progress-bar::after {
            content: 'DEBUG: Progress Bar - Width: ' attr(style);
            position: fixed;
            top: 15px;
            left: 10px;
            background: black;
            color: white;
            padding: 5px;
            font-size: 12px;
            z-index: 100000;
        }
        
        .progress-container {
            border: 2px solid blue !important;
        }
        </style>
        <script>
        console.log('Progress Bar Debug Mode Active');
        </script>
        <?php
    }
}
add_action('wp_head', 'debug_progress_bar_styles');

// Register sidebar
function mangastream_widgets_init() {
    register_sidebar(array(
        'name' => 'Main Sidebar',
        'id' => 'main-sidebar',
        'description' => 'Appears on the right side of the site',
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>'
    ));

    register_sidebar(array(
        'name' => 'Footer Widgets',
        'id' => 'footer-widgets',
        'description' => 'Appears in the footer area',
        'before_widget' => '<div class="footer-widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="footer-widget-title">',
        'after_title' => '</h4>'
    ));
}
add_action('widgets_init', 'mangastream_widgets_init');

// Custom post type for Manga
function create_manga_post_type() {
    register_post_type('manga', array(
        'labels' => array(
            'name' => 'Manga',
            'singular_name' => 'Manga',
            'add_new' => 'Add New Manga',
            'add_new_item' => 'Add New Manga',
            'edit_item' => 'Edit Manga',
            'new_item' => 'New Manga',
            'view_item' => 'View Manga',
            'search_items' => 'Search Manga',
            'not_found' => 'No manga found',
            'not_found_in_trash' => 'No manga found in trash'
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'comments'),
        'menu_icon' => 'dashicons-book-alt',
        'rewrite' => array(
            'slug' => 'manga',
            'with_front' => false
        ),
        'show_in_rest' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'exclude_from_search' => false
    ));

    // Register Chapter post type
    register_post_type('chapter', array(
        'labels' => array(
            'name' => 'Chapters',
            'singular_name' => 'Chapter',
            'add_new' => 'Add New Chapter',
            'add_new_item' => 'Add New Chapter',
            'edit_item' => 'Edit Chapter',
            'new_item' => 'New Chapter',
            'view_item' => 'View Chapter',
            'search_items' => 'Search Chapters',
            'not_found' => 'No chapters found',
            'not_found_in_trash' => 'No chapters found in trash'
        ),
        'public' => false,
        'show_ui' => true,
        'supports' => array('title', 'editor', 'custom-fields', 'comments'),
        'menu_icon' => 'dashicons-media-document',
        'show_in_rest' => true,
        'capability_type' => 'post',
        'hierarchical' => false
    ));
}
add_action('init', 'create_manga_post_type');

// Custom taxonomies dengan rewrite yang diperbaiki
function create_manga_taxonomies() {
    // Genre taxonomy
    register_taxonomy('manga_genre', 'manga', array(
        'labels' => array(
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'search_items' => 'Search Genres',
            'all_items' => 'All Genres',
            'parent_item' => 'Parent Genre',
            'parent_item_colon' => 'Parent Genre:',
            'edit_item' => 'Edit Genre',
            'update_item' => 'Update Genre',
            'add_new_item' => 'Add New Genre',
            'new_item_name' => 'New Genre Name',
            'menu_name' => 'Genres'
        ),
        'hierarchical' => true,
        'public' => true,
        'rewrite' => array(
            'slug' => 'manga/genre',
            'with_front' => false,
            'hierarchical' => true
        ),
        'show_in_rest' => true,
        'show_admin_column' => true
    ));

    // Status taxonomy
    register_taxonomy('manga_status', 'manga', array(
        'labels' => array(
            'name' => 'Status',
            'singular_name' => 'Status',
            'search_items' => 'Search Status',
            'all_items' => 'All Status',
            'edit_item' => 'Edit Status',
            'update_item' => 'Update Status',
            'add_new_item' => 'Add New Status',
            'new_item_name' => 'New Status Name',
            'menu_name' => 'Status'
        ),
        'hierarchical' => true,
        'public' => true,
        'rewrite' => array(
            'slug' => 'manga/status',
            'with_front' => false,
            'hierarchical' => true
        ),
        'show_in_rest' => true,
        'show_admin_column' => true
    ));
}
add_action('init', 'create_manga_taxonomies');

// Custom rewrite rules yang diperbaiki
function manga_rewrite_rules() {
    // Manga taxonomy archives - HARUS DI ATAS
    add_rewrite_rule(
        '^manga/genre/([^/]+)/?$',
        'index.php?manga_genre=$matches[1]',
        'top'
    );
    
    add_rewrite_rule(
        '^manga/status/([^/]+)/?$',
        'index.php?manga_status=$matches[1]',
        'top'
    );
    
    // Manga chapter reader dengan page number
    add_rewrite_rule(
        '^manga/([^/]+)/chapter/([^/]+)/page/([0-9]+)/?$',
        'index.php?post_type=manga&name=$matches[1]&chapter=$matches[2]&page_num=$matches[3]',
        'top'
    );

    // Manga chapter reader
    add_rewrite_rule(
        '^manga/([^/]+)/chapter/([^/]+)/?$',
        'index.php?post_type=manga&name=$matches[1]&chapter=$matches[2]',
        'top'
    );

    // Manga info page (default single manga)
    add_rewrite_rule(
        '^manga/([^/]+)/?$',
        'index.php?post_type=manga&name=$matches[1]',
        'top'
    );
    
    // Manga archive
    add_rewrite_rule(
        '^manga/?$',
        'index.php?post_type=manga',
        'top'
    );
}
add_action('init', 'manga_rewrite_rules');

// Add custom query vars
function manga_query_vars($vars) {
    $vars[] = 'chapter';
    $vars[] = 'page_num';
    return $vars;
}
add_filter('query_vars', 'manga_query_vars');

// Template redirect untuk handling custom URLs
function manga_template_redirect() {
    global $wp_query;

    if (get_query_var('chapter') && is_singular('manga')) {
        // Set proper template for chapter reading
        $wp_query->is_singular = true;
        $wp_query->is_single = true;

        // Load single-manga.php template
        include(get_template_directory() . '/single-manga.php');
        exit;
    }
}
add_action('template_redirect', 'manga_template_redirect');

// Fix permalink untuk chapter posts
function fix_chapter_permalink($permalink, $post) {
    if ($post->post_type === 'chapter') {
        $manga_id = get_post_meta($post->ID, 'manga_id', true);
        if ($manga_id) {
            $manga = get_post($manga_id);
            if ($manga) {
                return home_url("/manga/{$manga->post_name}/chapter/{$post->post_name}/");
            }
        }
    }
    return $permalink;
}
add_filter('post_link', 'fix_chapter_permalink', 10, 2);
add_filter('post_type_link', 'fix_chapter_permalink', 10, 2);

// Fix taxonomy links
function fix_manga_taxonomy_link($link, $term, $taxonomy) {
    if ($taxonomy === 'manga_genre') {
        return home_url("/manga/genre/{$term->slug}/");
    } elseif ($taxonomy === 'manga_status') {
        return home_url("/manga/status/{$term->slug}/");
    }
    return $link;
}
add_filter('term_link', 'fix_manga_taxonomy_link', 10, 3);

// Flush rewrite rules on theme activation
function manga_flush_rewrite_rules() {
    create_manga_post_type();
    create_manga_taxonomies();
    manga_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'manga_flush_rewrite_rules');

// Check dan update rewrite rules version
function manga_check_rewrite_rules() {
    $rules_version = get_option('manga_rewrite_rules_version', '1.0');
    $current_version = '1.3'; // Update saat ada perubahan rules
    
    if (version_compare($rules_version, $current_version, '<')) {
        manga_flush_rewrite_rules();
        update_option('manga_rewrite_rules_version', $current_version);
    }
}
add_action('init', 'manga_check_rewrite_rules');

// Fix search query untuk custom post types
function fix_search_query($query) {
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        // Handle post type parameter
        $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
        
        if (!empty($post_type)) {
            $query->set('post_type', array($post_type));
        } else {
            $query->set('post_type', array('post', 'manga'));
        }
        
        // Handle manga genre filter
        if ($post_type === 'manga' && isset($_GET['manga_genre']) && !empty($_GET['manga_genre'])) {
            $genre = sanitize_text_field($_GET['manga_genre']);
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'manga_genre',
                    'field' => 'slug',
                    'terms' => $genre
                )
            ));
        }
        
        // Prevent empty search from showing all posts
        if (empty(get_search_query())) {
            $query->set('posts_per_page', 0);
        }
    }
}
add_action('pre_get_posts', 'fix_search_query');

// Add search query var for manga genre
function add_manga_search_vars($vars) {
    $vars[] = 'manga_genre';
    return $vars;
}
add_filter('query_vars', 'add_manga_search_vars');

// AJAX search functionality
function mangastream_ajax_search() {
    check_ajax_referer('mangastream_nonce', 'nonce');

    $search_term = sanitize_text_field($_POST['search_term']);

    $args = array(
        'post_type' => 'manga',
        's' => $search_term,
        'posts_per_page' => 10,
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);
    $results = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = array(
                'title' => get_the_title(),
                'url' => get_permalink(),
                'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'manga-thumbnail') ?: get_template_directory_uri() . '/images/no-image.jpg'
            );
        }
    }

    wp_reset_postdata();
    wp_send_json($results);
}
add_action('wp_ajax_mangastream_search', 'mangastream_ajax_search');
add_action('wp_ajax_nopriv_mangastream_search', 'mangastream_ajax_search');

// Comment callback function
function mangastream_comment_callback($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    ?>
    <li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
        <div class="comment-body">
            <div class="comment-author vcard">
                <?php echo get_avatar($comment, 60); ?>
                <cite class="fn"><?php comment_author_link(); ?></cite>
                <span class="comment-date"><?php comment_date(); ?> at <?php comment_time(); ?></span>
            </div>

            <div class="comment-content">
                <?php comment_text(); ?>
            </div>

            <div class="comment-reply">
                <?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
            </div>
        </div>
    <?php
}

// Custom excerpt length
function mangastream_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'mangastream_excerpt_length');

// Custom excerpt more
function mangastream_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'mangastream_excerpt_more');

// Add body classes for better styling
function mangastream_body_classes($classes) {
    if (is_singular('manga')) {
        $classes[] = 'single-manga';

        if (get_query_var('chapter')) {
            $classes[] = 'manga-reader-page';
        } else {
            $classes[] = 'manga-info-page';
        }
    }

    if (is_post_type_archive('manga')) {
        $classes[] = 'manga-archive';
    }
    
    if (is_tax('manga_genre')) {
        $classes[] = 'manga-genre-archive';
    }
    
    if (is_tax('manga_status')) {
        $classes[] = 'manga-status-archive';
    }

    return $classes;
}
add_filter('body_class', 'mangastream_body_classes');

// Performance optimizations
function mangastream_performance_optimizations() {
    // Remove unnecessary WordPress features
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    
    // Disable emojis
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    
    // Remove query strings from static resources
    if (!is_admin()) {
        add_filter('script_loader_src', 'remove_script_version', 15, 1);
        add_filter('style_loader_src', 'remove_script_version', 15, 1);
    }
}
add_action('init', 'mangastream_performance_optimizations');

function remove_script_version($src) {
    $parts = explode('?ver', $src);
    return $parts[0];
}

// Security enhancements
function mangastream_security_enhancements() {
    // Hide WordPress version
    remove_action('wp_head', 'wp_generator');
    
    // Remove version from RSS
    add_filter('the_generator', '__return_empty_string');
    
    // Disable file editing
    if (!defined('DISALLOW_FILE_EDIT')) {
        define('DISALLOW_FILE_EDIT', true);
    }
}
add_action('init', 'mangastream_security_enhancements');

// Include additional files
$include_files = array(
    '/inc/customizer.php',
    '/inc/widgets.php',
    '/inc/manga-functions.php',
    '/inc/template-functions.php',
    '/inc/security.php',
    '/inc/ajax-handlers.php',
    '/inc/view-counter.php',
    '/admin/manga-meta-boxes.php'
);

foreach ($include_files as $file) {
    $file_path = get_template_directory() . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}
?>
