<?php
// Manga meta boxes for admin

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add meta boxes for manga
function add_manga_meta_boxes() {
    add_meta_box(
        'manga-details',
        'Manga Details',
        'manga_details_callback',
        'manga',
        'normal',
        'high'
    );
    
    add_meta_box(
        'manga-chapters',
        'Chapters',
        'manga_chapters_callback',
        'manga',
        'normal',
        'high'
    );
    
    add_meta_box(
        'manga-stats',
        'Statistics',
        'manga_stats_callback',
        'manga',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_manga_meta_boxes');

function manga_details_callback($post) {
    wp_nonce_field('manga_details_nonce', 'manga_details_nonce_field');
    
    $author = get_post_meta($post->ID, 'manga_author', true);
    $artist = get_post_meta($post->ID, 'manga_artist', true);
    $year = get_post_meta($post->ID, 'manga_year', true);
    $rating = get_post_meta($post->ID, 'manga_rating', true);
    $featured = get_post_meta($post->ID, 'featured', true);
    ?>
    
    <table class="form-table">
        <tr>
            <th><label for="manga_author">Author</label></th>
            <td>
                <input type="text" id="manga_author" name="manga_author" 
                       value="<?php echo esc_attr($author); ?>" class="regular-text">
                <p class="description">Nama pengarang manga</p>
            </td>
        </tr>
        <tr>
            <th><label for="manga_artist">Artist</label></th>
            <td>
                <input type="text" id="manga_artist" name="manga_artist" 
                       value="<?php echo esc_attr($artist); ?>" class="regular-text">
                <p class="description">Nama artist/illustrator manga</p>
            </td>
        </tr>
        <tr>
            <th><label for="manga_year">Year</label></th>
            <td>
                <input type="number" id="manga_year" name="manga_year" 
                       value="<?php echo esc_attr($year); ?>" min="1900" max="<?php echo date('Y'); ?>">
                <p class="description">Tahun rilis manga</p>
            </td>
        </tr>
        <tr>
            <th><label for="manga_rating">Rating</label></th>
            <td>
                <input type="number" id="manga_rating" name="manga_rating" 
                       value="<?php echo esc_attr($rating); ?>" min="0" max="5" step="0.1">
                <p class="description">Rating dari 0 sampai 5</p>
            </td>
        </tr>
        <tr>
            <th><label for="featured">Featured</label></th>
            <td>
                <input type="checkbox" id="featured" name="featured" value="yes" <?php checked($featured, 'yes'); ?>>
                <label for="featured">Tampilkan di manga featured</label>
            </td>
        </tr>
    </table>
    
    <style>
    .form-table th {
        width: 150px;
    }
    .form-table .description {
        font-style: italic;
        color: #666;
    }
    </style>
    
    <?php
}

function manga_chapters_callback($post) {
    wp_nonce_field('manga_chapters_nonce', 'manga_chapters_nonce_field');
    
    $chapters = get_manga_chapters($post->ID);
    ?>
    
    <div id="chapters-container">
        <div class="chapters-header">
            <h4>Daftar Chapter</h4>
            <button type="button" id="add-chapter-btn" class="button button-primary">Add New Chapter</button>
        </div>
        
        <div class="chapters-list">
            <?php if ($chapters): ?>
                <?php foreach ($chapters as $index => $chapter): ?>
                    <div class="chapter-row" data-chapter-id="<?php echo $chapter->ID; ?>">
                        <span class="chapter-handle">⋮⋮</span>
                        <div class="chapter-fields">
                            <input type="hidden" name="chapter_ids[]" value="<?php echo $chapter->ID; ?>">
                            <div class="field-group">
                                <label>Chapter Number:</label>
                                <input type="number" name="chapter_numbers[]" 
                                       value="<?php echo esc_attr(get_post_meta($chapter->ID, 'chapter_number', true)); ?>" 
                                       placeholder="1" class="chapter-number" step="0.1" min="0">
                            </div>
                            <div class="field-group">
                                <label>Chapter Title:</label>
                                <input type="text" name="chapter_titles[]" 
                                       value="<?php echo esc_attr($chapter->post_title); ?>" 
                                       placeholder="Chapter Title" class="chapter-title">
                            </div>
                            <div class="field-group">
                                <label>Status:</label>
                                <select name="chapter_status[]" class="chapter-status">
                                    <option value="publish" <?php selected($chapter->post_status, 'publish'); ?>>Published</option>
                                    <option value="draft" <?php selected($chapter->post_status, 'draft'); ?>>Draft</option>
                                    <option value="private" <?php selected($chapter->post_status, 'private'); ?>>Private</option>
                                </select>
                            </div>
                        </div>
                        <div class="chapter-actions">
                            <a href="<?php echo get_edit_post_link($chapter->ID); ?>" class="button" target="_blank">Edit Pages</a>
                            <button type="button" class="remove-chapter button">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-chapters">
                    <p>Belum ada chapter. Klik "Add New Chapter" untuk menambah chapter baru.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="chapter-template" style="display: none;">
            <div class="chapter-row">
                <span class="chapter-handle">⋮⋮</span>
                <div class="chapter-fields">
                    <input type="hidden" name="chapter_ids[]" value="">
                    <div class="field-group">
                        <label>Chapter Number:</label>
                        <input type="number" name="chapter_numbers[]" placeholder="1" class="chapter-number" step="0.1" min="0">
                    </div>
                    <div class="field-group">
                        <label>Chapter Title:</label>
                        <input type="text" name="chapter_titles[]" placeholder="Chapter Title" class="chapter-title">
                    </div>
                    <div class="field-group">
                        <label>Status:</label>
                        <select name="chapter_status[]" class="chapter-status">
                            <option value="draft">Draft</option>
                            <option value="publish">Published</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                </div>
                <div class="chapter-actions">
                    <button type="button" class="remove-chapter button">Remove</button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .chapters-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
    }
    
    .chapter-row {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #f9f9f9;
    }
    
    .chapter-handle {
        cursor: move;
        color: #666;
        font-size: 18px;
    }
    
    .chapter-fields {
        flex: 1;
        display: grid;
        grid-template-columns: 120px 1fr 120px;
        gap: 15px;
        align-items: end;
    }
    
    .field-group {
        display: flex;
        flex-direction: column;
    }
    
    .field-group label {
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }
    
    .chapter-number {
        width: 100%;
    }
    
    .chapter-title {
        width: 100%;
    }
    
    .chapter-status {
        width: 100%;
    }
    
    .chapter-actions {
        display: flex;
        gap: 10px;
    }
    
    .chapter-placeholder {
        height: 60px;
        background: #f0f0f0;
        border: 2px dashed #ddd;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    
    .no-chapters {
        text-align: center;
        padding: 40px 20px;
        color: #666;
        font-style: italic;
    }
    
    @media (max-width: 768px) {
        .chapter-fields {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        
        .chapter-row {
            flex-direction: column;
            align-items: stretch;
        }
        
        .chapter-actions {
            justify-content: center;
        }
    }
    </style>
    
    <?php
}

function manga_stats_callback($post) {
    $stats = get_manga_stats($post->ID);
    $latest_chapter = get_latest_chapter($post->ID);
    ?>
    
    <table class="form-table">
        <tr>
            <th>Total Views</th>
            <td><?php echo number_format($stats['total_views']); ?></td>
        </tr>
        <tr>
            <th>Daily Views</th>
            <td><?php echo number_format($stats['daily_views']); ?></td>
        </tr>
        <tr>
            <th>Weekly Views</th>
            <td><?php echo number_format($stats['weekly_views']); ?></td>
        </tr>
        <tr>
            <th>Monthly Views</th>
            <td><?php echo number_format($stats['monthly_views']); ?></td>
        </tr>
        <tr>
            <th>Total Chapters</th>
            <td><?php echo $stats['total_chapters']; ?></td>
        </tr>
        <tr>
            <th>Latest Chapter</th>
            <td>
                <?php if ($latest_chapter): ?>
                    Chapter <?php echo $latest_chapter->chapter_number; ?>
                    <br><small><?php echo $latest_chapter->date; ?></small>
                <?php else: ?>
                    No chapters
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Rating</th>
            <td>
                <?php if ($stats['rating'] > 0): ?>
                    <?php echo $stats['rating']; ?>/5 
                    <small>(<?php echo $stats['rating_count']; ?> votes)</small>
                <?php else: ?>
                    Not rated yet
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Last Updated</th>
            <td><?php echo get_the_modified_date('F j, Y g:i a', $post); ?></td>
        </tr>
    </table>
    
    <div class="stats-actions">
        <button type="button" id="reset-views" class="button">Reset Views</button>
        <button type="button" id="refresh-stats" class="button">Refresh Stats</button>
    </div>
    
    <style>
    .stats-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #ddd;
    }
    
    .stats-actions .button {
        margin-right: 10px;
    }
    </style>
    
    <?php
}

// Add meta boxes for chapter
function add_chapter_meta_boxes() {
    add_meta_box(
        'chapter-details',
        'Chapter Details',
        'chapter_details_callback',
        'chapter',
        'normal',
        'high'
    );
    
    add_meta_box(
        'chapter-pages',
        'Chapter Pages',
        'chapter_pages_callback',
        'chapter',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_chapter_meta_boxes');

function chapter_details_callback($post) {
    wp_nonce_field('chapter_details_nonce', 'chapter_details_nonce_field');
    
    $manga_id = get_post_meta($post->ID, 'manga_id', true);
    $chapter_number = get_post_meta($post->ID, 'chapter_number', true);
    
    // Get all manga for dropdown
    $all_manga = get_posts(array(
        'post_type' => 'manga',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    ?>
    
    <table class="form-table">
        <tr>
            <th><label for="manga_id">Manga</label></th>
            <td>
                <select id="manga_id" name="manga_id" class="regular-text" required>
                    <option value="">Select Manga</option>
                    <?php foreach ($all_manga as $manga): ?>
                        <option value="<?php echo $manga->ID; ?>" <?php selected($manga_id, $manga->ID); ?>>
                            <?php echo esc_html($manga->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Pilih manga untuk chapter ini</p>
            </td>
        </tr>
        <tr>
            <th><label for="chapter_number">Chapter Number</label></th>
            <td>
                <input type="number" id="chapter_number" name="chapter_number" 
                       value="<?php echo esc_attr($chapter_number); ?>" 
                       step="0.1" min="0" class="regular-text" required>
                <p class="description">Nomor chapter (bisa desimal, contoh: 1.5)</p>
            </td>
        </tr>
    </table>
    
    <?php
}

function chapter_pages_callback($post) {
    wp_nonce_field('chapter_pages_nonce', 'chapter_pages_nonce_field');
    
    $pages = get_post_meta($post->ID, 'chapter_pages', true) ?: array();
    ?>
    
    <div class="chapter-pages-container">
        <div class="pages-header">
            <h4>Manga Pages</h4>
            <div class="page-controls">
                <button type="button" class="upload-manga-pages button button-primary">Upload Pages</button>
                <button type="button" class="add-external-url button">Add External URL</button>
                <button type="button" class="clear-all-pages button">Clear All</button>
            </div>
        </div>
        
        <div id="manga-pages-container" class="pages-grid">
            <?php if ($pages): ?>
                <?php foreach ($pages as $index => $page_data): ?>
                    <?php
                    // Handle different data formats
                    if (is_numeric($page_data)) {
                        // Old format: attachment ID
                        $page_id = $page_data;
                        $page_url = wp_get_attachment_image_url($page_id, 'medium');
                        $page_type = 'upload';
                        $page_alt = get_post_meta($page_id, '_wp_attachment_image_alt', true) ?: '';
                    } elseif (is_array($page_data)) {
                        // New format
                        $page_type = $page_data['type'];
                        if ($page_type === 'upload') {
                            $page_id = $page_data['id'];
                            $page_url = wp_get_attachment_image_url($page_id, 'medium');
                            $page_alt = get_post_meta($page_id, '_wp_attachment_image_alt', true) ?: '';
                        } else {
                            $page_id = 0;
                            $page_url = $page_data['url'];
                            $page_alt = isset($page_data['alt']) ? $page_data['alt'] : '';
                        }
                    } elseif (is_string($page_data)) {
                        // Direct URL
                        $page_id = 0;
                        $page_url = $page_data;
                        $page_type = 'external';
                        $page_alt = '';
                    }
                    ?>
                    
                    <div class="manga-page-item" data-page-type="<?php echo $page_type; ?>" data-page-index="<?php echo $index; ?>">
                        <div class="page-thumbnail">
                            <img src="<?php echo esc_url($page_url); ?>" alt="Page" loading="lazy">
                            <div class="page-type-badge <?php echo $page_type; ?>">
                                <?php echo $page_type === 'upload' ? 'Upload' : 'External'; ?>
                            </div>
                        </div>
                        
                        <?php if ($page_type === 'upload'): ?>
                            <input type="hidden" name="manga_pages[<?php echo $index; ?>][type]" value="upload">
                            <input type="hidden" name="manga_pages[<?php echo $index; ?>][id]" value="<?php echo $page_id; ?>">
                        <?php else: ?>
                            <input type="hidden" name="manga_pages[<?php echo $index; ?>][type]" value="external">
                            <input type="text" name="manga_pages[<?php echo $index; ?>][url]" value="<?php echo esc_attr($page_url); ?>" class="external-url-input" placeholder="Image URL">
                            <input type="hidden" name="manga_pages[<?php echo $index; ?>][alt]" value="<?php echo esc_attr($page_alt); ?>">
                        <?php endif; ?>
                        
                        <div class="page-actions">
                            <button type="button" class="move-up button-small">↑</button>
                            <button type="button" class="move-down button-small">↓</button>
                            <button type="button" class="edit-page button-small">✎</button>
                            <button type="button" class="remove-page button-small">×</button>
                        </div>
                        <div class="page-number"><?php echo $index + 1; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="pages-info">
            <p><strong>Total Pages:</strong> <span id="total-pages"><?php echo count($pages); ?></span></p>
            <p class="description">
                Anda dapat mengupload gambar atau menggunakan URL external. Drag and drop untuk mengurutkan halaman.
            </p>
        </div>
    </div>
    
    <!-- External URL Modal -->
    <div id="external-url-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Add External Image URL</h3>
            <div class="modal-body">
                <label for="external-image-url">Image URL:</label>
                <input type="url" id="external-image-url" placeholder="https://example.com/image.jpg" class="widefat">
                
                <label for="external-image-alt">Alt Text (Optional):</label>
                <input type="text" id="external-image-alt" placeholder="Page description" class="widefat">
                
                <div class="image-preview" id="external-image-preview" style="display: none;">
                    <img src="" alt="Preview" style="max-width: 200px; max-height: 200px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-primary" id="add-external-image">Add Image</button>
                <button type="button" class="button" id="cancel-external-image">Cancel</button>
            </div>
        </div>
    </div>
    
    <style>
    .pages-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
    }
    
    .page-controls {
        display: flex;
        gap: 10px;
    }
    
    .pages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
        min-height: 100px;
        border: 2px dashed #ddd;
        border-radius: 4px;
        padding: 15px;
    }
    
    .manga-page-item {
        position: relative;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: hidden;
        background: #fff;
        cursor: move;
    }
    
    .page-thumbnail {
        aspect-ratio: 3/4;
        overflow: hidden;
        position: relative;
    }
    
    .page-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .page-type-badge {
        position: absolute;
        top: 5px;
        left: 5px;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: bold;
        color: white;
    }
    
    .page-type-badge.upload {
        background: #28a745;
    }
    
    .page-type-badge.external {
        background: #007cba;
    }
    
    .external-url-input {
        width: 100%;
        padding: 5px;
        margin-top: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .page-actions {
        position: absolute;
        top: 5px;
        right: 5px;
        display: flex;
        gap: 2px;
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .manga-page-item:hover .page-actions {
        opacity: 1;
    }
    
    .button-small {
        padding: 2px 6px;
        font-size: 12px;
        line-height: 1;
        background: rgba(0,0,0,0.7);
        color: white;
        border: none;
        border-radius: 2px;
        cursor: pointer;
    }
    
    .button-small:hover {
        background: rgba(0,0,0,0.9);
    }
    
    .page-number {
        position: absolute;
        bottom: 5px;
        left: 5px;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 2px 6px;
        border-radius: 2px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
    }
    
    .modal-body {
        margin: 20px 0;
    }
    
    .modal-body label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .modal-body input {
        margin-bottom: 15px;
    }
    
    .modal-footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    
    .image-preview {
        margin-top: 10px;
        text-align: center;
    }
    </style>
    
    <?php
}

// Save meta box data
function save_manga_meta_boxes($post_id) {
    // Verify nonce
    if (!isset($_POST['manga_details_nonce_field']) || 
        !wp_verify_nonce($_POST['manga_details_nonce_field'], 'manga_details_nonce')) {
        return;
    }
    
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Check post type
    if (get_post_type($post_id) !== 'manga') {
        return;
    }
    
    // Save fields with proper sanitization
    $fields = array(
        'manga_author' => 'sanitize_text_field',
        'manga_artist' => 'sanitize_text_field', 
        'manga_year' => 'absint',
        'manga_rating' => 'floatval'
    );
    
    foreach ($fields as $field => $sanitizer) {
        if (isset($_POST[$field])) {
            $value = call_user_func($sanitizer, $_POST[$field]);
            update_post_meta($post_id, $field, $value);
        }
    }
    
    // Save featured status
    $featured = isset($_POST['featured']) ? 'yes' : 'no';
    update_post_meta($post_id, 'featured', $featured);
    
    // Save chapters
    if (isset($_POST['chapter_numbers']) && is_array($_POST['chapter_numbers'])) {
        $chapter_numbers = $_POST['chapter_numbers'];
        $chapter_titles = $_POST['chapter_titles'] ?? array();
        $chapter_status = $_POST['chapter_status'] ?? array();
        $chapter_ids = $_POST['chapter_ids'] ?? array();
        
        foreach ($chapter_numbers as $index => $number) {
            if (empty($number)) continue;
            
            $title = !empty($chapter_titles[$index]) ? $chapter_titles[$index] : 'Chapter ' . $number;
            $status = !empty($chapter_status[$index]) ? $chapter_status[$index] : 'draft';
            
            $chapter_data = array(
                'post_title' => sanitize_text_field($title),
                'post_type' => 'chapter',
                'post_status' => sanitize_text_field($status)
            );
            
            if (!empty($chapter_ids[$index])) {
                // Update existing chapter
                $chapter_data['ID'] = intval($chapter_ids[$index]);
                wp_update_post($chapter_data);
                $chapter_id = $chapter_ids[$index];
            } else {
                // Create new chapter
                $chapter_id = wp_insert_post($chapter_data);
            }
            
            if ($chapter_id && !is_wp_error($chapter_id)) {
                update_post_meta($chapter_id, 'manga_id', $post_id);
                update_post_meta($chapter_id, 'chapter_number', floatval($number));
            }
        }
    }
}
add_action('save_post', 'save_manga_meta_boxes');

// Save chapter meta boxes
function save_chapter_meta_boxes($post_id) {
    // Check nonces
    $details_nonce = isset($_POST['chapter_details_nonce_field']) && 
                    wp_verify_nonce($_POST['chapter_details_nonce_field'], 'chapter_details_nonce');
    $pages_nonce = isset($_POST['chapter_pages_nonce_field']) && 
                  wp_verify_nonce($_POST['chapter_pages_nonce_field'], 'chapter_pages_nonce');
    
    if (!$details_nonce && !$pages_nonce) {
        return;
    }
    
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Check post type
    if (get_post_type($post_id) !== 'chapter') {
        return;
    }
    
    // Save chapter details
    if ($details_nonce) {
        if (isset($_POST['manga_id'])) {
            update_post_meta($post_id, 'manga_id', absint($_POST['manga_id']));
        }
        
        if (isset($_POST['chapter_number'])) {
            update_post_meta($post_id, 'chapter_number', floatval($_POST['chapter_number']));
        }
    }
    
    // Save chapter pages with new format
    if ($pages_nonce) {
        if (isset($_POST['manga_pages']) && is_array($_POST['manga_pages'])) {
            $pages = array();
            
            foreach ($_POST['manga_pages'] as $page_data) {
                if (isset($page_data['type'])) {
                    if ($page_data['type'] === 'upload' && !empty($page_data['id'])) {
                        $pages[] = array(
                            'type' => 'upload',
                            'id' => absint($page_data['id'])
                        );
                    } elseif ($page_data['type'] === 'external' && !empty($page_data['url'])) {
                        $pages[] = array(
                            'type' => 'external',
                            'url' => esc_url_raw($page_data['url']),
                            'alt' => isset($page_data['alt']) ? sanitize_text_field($page_data['alt']) : ''
                        );
                    }
                }
            }
            
            update_post_meta($post_id, 'chapter_pages', $pages);
        } else {
            delete_post_meta($post_id, 'chapter_pages');
        }
    }
}
add_action('save_post', 'save_chapter_meta_boxes');

// Enqueue admin scripts
function manga_admin_scripts($hook) {
    global $post_type;
    
    if (($hook == 'post-new.php' || $hook == 'post.php') && ($post_type == 'manga' || $post_type == 'chapter')) {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('manga-admin', get_template_directory_uri() . '/js/admin.js', array('jquery'), '1.0', true);
        
        wp_localize_script('manga-admin', 'manga_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('manga_admin_nonce')
        ));
    }
}
add_action('admin_enqueue_scripts', 'manga_admin_scripts');

// AJAX handlers for admin
function ajax_reset_manga_views() {
    check_ajax_referer('manga_admin_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
    }
    
    $manga_id = intval($_POST['manga_id']);
    
    update_post_meta($manga_id, 'total_views', 0);
    update_post_meta($manga_id, 'daily_views', 0);
    update_post_meta($manga_id, 'weekly_views', 0);
    update_post_meta($manga_id, 'monthly_views', 0);
    
    wp_send_json_success('Views reset successfully');
}
add_action('wp_ajax_reset_manga_views', 'ajax_reset_manga_views');
?>
