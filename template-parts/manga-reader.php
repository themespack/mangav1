<?php
global $post;

// Error handling untuk debug
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Get current manga and chapter data
$manga_id = get_the_ID();
$manga_slug = get_post_field('post_name', $manga_id);
$chapter_slug = get_query_var('chapter');
$page_num = get_query_var('page_num') ? intval(get_query_var('page_num')) : 1;

// Debug output
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo "<!-- DEBUG START -->";
    echo "<!-- Manga ID: " . $manga_id . " -->";
    echo "<!-- Manga Slug: " . $manga_slug . " -->";
    echo "<!-- Chapter Slug: " . $chapter_slug . " -->";
    echo "<!-- Page Num: " . $page_num . " -->";
}

if (!$chapter_slug) {
    echo '<div class="error-message">
        <h3>Chapter tidak ditemukan</h3>
        <p>URL chapter tidak valid atau chapter slug tidak ditemukan.</p>
        <a href="' . get_manga_url($manga_slug) . '" class="btn btn-primary">Kembali ke Info Manga</a>
    </div>';
    return;
}

$chapter = get_chapter_by_slug($chapter_slug, $manga_id);

if (!$chapter) {
    echo '<div class="error-message">
        <h3>Chapter tidak ditemukan</h3>
        <p>Chapter "' . esc_html($chapter_slug) . '" tidak ditemukan untuk manga ini.</p>
        <p><strong>Debug Info:</strong></p>
        <ul>
            <li>Manga ID: ' . $manga_id . '</li>
            <li>Chapter Slug: ' . esc_html($chapter_slug) . '</li>
        </ul>
        <a href="' . get_manga_url($manga_slug) . '" class="btn btn-primary">Kembali ke Info Manga</a>
    </div>';
    return;
}

if (!validate_chapter_access($chapter->ID, $manga_id)) {
    echo '<div class="error-message">
        <h3>Akses Ditolak</h3>
        <p>Anda tidak memiliki akses untuk membaca chapter ini.</p>
        <a href="' . get_manga_url($manga_slug) . '" class="btn btn-primary">Kembali ke Info Manga</a>
    </div>';
    return;
}

// Get chapter data
$chapter_number = get_post_meta($chapter->ID, 'chapter_number', true);
$pages = get_chapter_pages($chapter->ID);
$total_pages = count($pages);
$navigation = get_chapter_navigation($chapter->ID);

// Debug chapter data dengan safe access
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo "<!-- Chapter Found: " . $chapter->ID . " -->";
    echo "<!-- Chapter Title: " . $chapter->post_title . " -->";
    echo "<!-- Chapter Number: " . $chapter_number . " -->";
    echo "<!-- Pages Count: " . $total_pages . " -->";
    
    if (empty($pages)) {
        $chapter_pages_meta = get_post_meta($chapter->ID, 'chapter_pages', true);
        echo "<!-- Chapter Pages Meta: " . print_r($chapter_pages_meta, true) . " -->";
        
        // Check if meta exists but empty
        if ($chapter_pages_meta === false) {
            echo "<!-- NO CHAPTER_PAGES META FOUND -->";
        } elseif (empty($chapter_pages_meta)) {
            echo "<!-- CHAPTER_PAGES META IS EMPTY -->";
        } else {
            echo "<!-- CHAPTER_PAGES META EXISTS BUT get_chapter_pages RETURNED EMPTY -->";
        }
    } else {
        foreach ($pages as $index => $page) {
            $file_exists = get_safe_page_data($page, 'file_exists', 'unknown');
            $file_exists_text = is_bool($file_exists) ? ($file_exists ? 'yes' : 'no') : $file_exists;
            $type = get_safe_page_data($page, 'type', 'unknown');
            echo "<!-- Page " . ($index + 1) . ": " . $page['url'] . " (type: " . $type . ", exists: " . $file_exists_text . ") -->";
        }
    }
    echo "<!-- DEBUG END -->";
}

// Update reading progress
if (is_user_logged_in()) {
    update_manga_reading_progress($manga_id, $chapter_number);
}

// Update view count
increment_manga_views($manga_id);
?>

<!-- Progress Bar Container yang Diperbaiki -->
<div class="progress-container">
    <div class="progress-bar"></div>
</div>

<div class="manga-reader" 
     data-manga-id="<?php echo $manga_id; ?>" 
     data-chapter-id="<?php echo $chapter->ID; ?>" 
     data-chapter-number="<?php echo $chapter_number; ?>"
     data-manga-slug="<?php echo $manga_slug; ?>"
     data-chapter-slug="<?php echo $chapter_slug; ?>">
     
    <div class="reader-container">
        <!-- Breadcrumb Navigation -->
        <?php mangastream_breadcrumb(); ?>
        
        <!-- Reader Navigation Top -->
        <div class="reader-navigation">
            <div class="nav-left">
                <?php if ($navigation['prev']): ?>
                    <a href="<?php echo get_chapter_url($manga_slug, $navigation['prev']->post_name); ?>" 
                       class="nav-btn prev-chapter">
                        <i class="fas fa-chevron-left"></i> Chapter Sebelumnya
                    </a>
                <?php else: ?>
                    <button class="nav-btn" disabled>
                        <i class="fas fa-chevron-left"></i> Chapter Sebelumnya
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="nav-center">
                <select class="chapter-selector">
                    <?php foreach ($navigation['all'] as $ch): ?>
                        <?php $ch_number = get_post_meta($ch->ID, 'chapter_number', true); ?>
                        <option value="<?php echo get_chapter_url($manga_slug, $ch->post_name); ?>" 
                                <?php selected($ch->ID, $chapter->ID); ?>>
                            Chapter <?php echo $ch_number; ?>
                            <?php if ($ch->post_title && $ch->post_title !== 'Chapter ' . $ch_number): ?>
                                - <?php echo esc_html($ch->post_title); ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <span class="page-info">
                    Halaman <span class="current-page" data-page="<?php echo $page_num; ?>"><?php echo $page_num; ?></span> 
                    dari <span class="total-pages"><?php echo $total_pages; ?></span>
                </span>
                
                <button id="reading-mode-toggle" class="nav-btn">
                    <i class="fas fa-th-large"></i> Mode Halaman Tunggal
                </button>
                
                <button id="reading-settings-btn" class="nav-btn">
                    <i class="fas fa-cog"></i> Pengaturan
                </button>
            </div>
            
            <div class="nav-right">
                <?php if ($navigation['next']): ?>
                    <a href="<?php echo get_chapter_url($manga_slug, $navigation['next']->post_name); ?>" 
                       class="nav-btn next-chapter">
                        Chapter Selanjutnya <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <button class="nav-btn" disabled>
                        Chapter Selanjutnya <i class="fas fa-chevron-right"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Chapter Info -->
        <div class="chapter-info">
            <h1 class="chapter-title">
                <a href="<?php echo get_manga_url($manga_slug); ?>">
                    <?php echo get_the_title($manga_id); ?>
                </a>
                - Chapter <?php echo $chapter_number; ?>
            </h1>
            
            <?php if ($chapter->post_title && $chapter->post_title !== 'Chapter ' . $chapter_number): ?>
                <h2 class="chapter-subtitle"><?php echo esc_html($chapter->post_title); ?></h2>
            <?php endif; ?>
            
            <div class="chapter-meta">
                <span class="chapter-date">
                    <i class="fas fa-calendar"></i> 
                    <?php echo get_the_date('', $chapter->ID); ?>
                </span>
                
                <span class="chapter-views">
                    <i class="fas fa-eye"></i> 
                    <?php echo format_view_count(get_post_meta($manga_id, 'total_views', true) ?: 0); ?> views
                </span>
                
                <span class="chapter-pages">
                    <i class="fas fa-images"></i> 
                    <?php echo $total_pages; ?> halaman
                </span>
            </div>
        </div>
        
        <!-- Manga Pages dengan Safe Array Access -->
        <?php if ($pages && $total_pages > 0): ?>
            <div class="manga-pages">
                <?php foreach ($pages as $index => $page): ?>
                    <img src="<?php echo esc_url($page['url']); ?>" 
                         alt="<?php echo esc_attr(get_safe_page_data($page, 'alt', 'Manga Page')); ?>" 
                         class="manga-page"
                         data-page="<?php echo $index + 1; ?>"
                         data-width="<?php echo get_safe_page_data($page, 'width', 0); ?>"
                         data-height="<?php echo get_safe_page_data($page, 'height', 0); ?>"
                         data-type="<?php echo get_safe_page_data($page, 'type', 'unknown'); ?>"
                         data-file-exists="<?php echo get_safe_page_data($page, 'file_exists', true) ? 'true' : 'false'; ?>"
                         loading="<?php echo $index < 3 ? 'eager' : 'lazy'; ?>"
                         onerror="console.log('Image failed to load:', this.src);">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-pages">
                <div class="no-pages-content">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>Tidak ada halaman tersedia</h3>
                    <p>Chapter ini belum memiliki halaman yang dapat dibaca.</p>
                    
                    <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
                        <div class="debug-info">
                            <h4>Debug Information:</h4>
                            <ul>
                                <li><strong>Chapter ID:</strong> <?php echo $chapter->ID; ?></li>
                                <li><strong>Chapter Slug:</strong> <?php echo $chapter_slug; ?></li>
                                <li><strong>Manga ID:</strong> <?php echo $manga_id; ?></li>
                                <li><strong>Chapter Meta:</strong> 
                                    <pre><?php print_r(get_post_meta($chapter->ID, 'chapter_pages', true)); ?></pre>
                                </li>
                                <li><strong>Processed Pages:</strong>
                                    <pre><?php print_r($pages); ?></pre>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="no-pages-actions">
                        <a href="<?php echo get_manga_url($manga_slug); ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Info Manga
                        </a>
                        <?php if (current_user_can('edit_posts')): ?>
                            <a href="<?php echo get_edit_post_link($chapter->ID); ?>" class="btn btn-secondary" target="_blank">
                                <i class="fas fa-edit"></i> Edit Chapter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Reader Navigation Bottom -->
        <div class="reader-navigation">
            <div class="nav-left">
                <?php if ($navigation['prev']): ?>
                    <a href="<?php echo get_chapter_url($manga_slug, $navigation['prev']->post_name); ?>" 
                       class="nav-btn prev-chapter">
                        <i class="fas fa-chevron-left"></i> Chapter Sebelumnya
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="nav-center">
                <a href="<?php echo get_manga_url($manga_slug); ?>" class="nav-btn">
                    <i class="fas fa-info-circle"></i> Info Manga
                </a>
                
                <?php if (is_user_logged_in()): ?>
                    <button id="bookmark-chapter-btn" class="nav-btn <?php echo is_manga_bookmarked($manga_id) ? 'bookmarked' : ''; ?>">
                        <i class="<?php echo is_manga_bookmarked($manga_id) ? 'fas' : 'far'; ?> fa-bookmark"></i> 
                        Bookmark
                    </button>
                <?php endif; ?>
                
                <button class="nav-btn share-btn" data-url="<?php echo get_chapter_url($manga_slug, $chapter_slug); ?>" data-title="<?php echo get_the_title($manga_id); ?> Chapter <?php echo $chapter_number; ?>">
                    <i class="fas fa-share"></i> Bagikan
                </button>
                
                <button id="toggle-comments-btn" class="nav-btn">
                    <i class="fas fa-comments"></i> Komentar
                </button>
            </div>
            
            <div class="nav-right">
                <?php if ($navigation['next']): ?>
                    <a href="<?php echo get_chapter_url($manga_slug, $navigation['next']->post_name); ?>" 
                       class="nav-btn next-chapter">
                        Chapter Selanjutnya <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Comments Section (Initially Hidden) -->
        <div id="chapter-comments-container" style="display: none;">
            <!-- Comments will be loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Debug Progress Bar (hanya jika debug aktif) -->
<?php if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['debug_progress'])): ?>
<script>
console.log('Progress Bar Debug Mode Active');
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        console.log('Progress Bar Element:', progressBar);
        console.log('Progress Bar Styles:', window.getComputedStyle(progressBar));
    }
});
</script>
<?php endif; ?>

<!-- Structured Data for SEO -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "<?php echo esc_js(get_the_title($manga_id) . ' Chapter ' . $chapter_number); ?>",
    "description": "Baca <?php echo esc_js(get_the_title($manga_id)); ?> Chapter <?php echo $chapter_number; ?> online gratis di <?php bloginfo('name'); ?>",
    "image": "<?php echo $pages ? esc_url($pages[0]['url']) : ''; ?>",
    "author": {
        "@type": "Organization",
        "name": "<?php bloginfo('name'); ?>"
    },
    "publisher": {
        "@type": "Organization",
        "name": "<?php bloginfo('name'); ?>",
        "logo": {
            "@type": "ImageObject",
            "url": "<?php echo get_site_icon_url(); ?>"
        }
    },
    "datePublished": "<?php echo get_the_date('c', $chapter->ID); ?>",
    "dateModified": "<?php echo get_the_modified_date('c', $chapter->ID); ?>",
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "<?php echo get_chapter_url($manga_slug, $chapter_slug); ?>"
    },
    "articleSection": "Manga",
    "keywords": "manga, <?php echo esc_js(get_the_title($manga_id)); ?>, chapter <?php echo $chapter_number; ?>, baca online",
    "wordCount": "<?php echo $total_pages; ?>"
}
</script>
