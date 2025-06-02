<?php
$manga_slug = get_query_var('manga');
$manga = get_manga_by_slug($manga_slug);

if (!$manga) {
    get_template_part('404');
    return;
}

$manga_id = $manga->ID;
setup_postdata($manga);
?>

<div class="manga-info-page">
    <?php mangastream_breadcrumb(); ?>
    
    <div class="manga-header">
        <div class="manga-poster">
            <?php if (has_post_thumbnail($manga_id)): ?>
                <img src="<?php echo get_the_post_thumbnail_url($manga_id, 'manga-large'); ?>" 
                     alt="<?php echo get_the_title($manga_id); ?>" class="manga-cover">
            <?php endif; ?>
            
            <div class="manga-actions">
                <button class="btn btn-primary bookmark-btn" data-manga-id="<?php echo $manga_id; ?>">
                    <i class="fas fa-bookmark"></i> Bookmark
                </button>
                <button class="btn btn-secondary share-btn">
                    <i class="fas fa-share"></i> Bagikan
                </button>
            </div>
        </div>
        
        <div class="manga-details">
            <h1 class="manga-title"><?php echo get_the_title($manga_id); ?></h1>
            
            <div class="manga-meta">
                <?php
                $author = get_post_meta($manga_id, 'manga_author', true);
                $artist = get_post_meta($manga_id, 'manga_artist', true);
                $year = get_post_meta($manga_id, 'manga_year', true);
                $rating = get_post_meta($manga_id, 'manga_rating', true);
                $status = get_the_terms($manga_id, 'manga_status');
                $genres = get_the_terms($manga_id, 'manga_genre');
                ?>
                
                <?php if ($author): ?>
                    <div class="meta-item">
                        <strong>Pengarang:</strong> <?php echo esc_html($author); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($status): ?>
                    <div class="meta-item">
                        <strong>Status:</strong>
                        <?php foreach ($status as $stat): ?>
                            <span class="status-badge <?php echo esc_attr($stat->slug); ?>">
                                <?php echo esc_html($stat->name); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($rating): ?>
                    <div class="meta-item">
                        <strong>Rating:</strong>
                        <div class="rating-display">
                            <?php echo $rating; ?>/5 ⭐
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($genres): ?>
                <div class="manga-genres">
                    <strong>Genre:</strong>
                    <div class="genre-tags">
                        <?php foreach ($genres as $genre): ?>
                            <a href="<?php echo get_term_link($genre); ?>" class="genre-tag">
                                <?php echo esc_html($genre->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="manga-description">
        <h3>Sinopsis</h3>
        <div class="description-content">
            <?php echo apply_filters('the_content', $manga->post_content); ?>
        </div>
    </div>
    
    <!-- Chapter List -->
    <div class="chapter-list">
        <h3>Daftar Chapter</h3>
        <div class="chapters">
            <?php
            $chapters = get_manga_chapters($manga_id);
            if ($chapters):
                foreach ($chapters as $chapter):
                    $chapter_number = get_post_meta($chapter->ID, 'chapter_number', true);
            ?>
                <div class="chapter-item">
                    <a href="<?php echo get_chapter_url($manga_slug, $chapter->post_name); ?>" class="chapter-link">
                        <span class="chapter-number">Chapter <?php echo $chapter_number; ?></span>
                        <span class="chapter-title"><?php echo $chapter->post_title; ?></span>
                        <span class="chapter-date"><?php echo get_the_date('', $chapter->ID); ?></span>
                    </a>
                </div>
            <?php
                endforeach;
            else:
            ?>
                <div class="no-chapters">
                    <p>Belum ada chapter yang tersedia.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php wp_reset_postdata(); ?>
