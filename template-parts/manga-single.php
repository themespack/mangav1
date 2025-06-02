<?php
$manga_id = get_the_ID();
$genres = get_the_terms($manga_id, 'manga_genre');
$status = get_the_terms($manga_id, 'manga_status');
$author = get_post_meta($manga_id, 'manga_author', true);
$artist = get_post_meta($manga_id, 'manga_artist', true);
$year = get_post_meta($manga_id, 'manga_year', true);
$rating = get_post_meta($manga_id, 'manga_rating', true);
$stats = get_manga_stats($manga_id);
?>

<article class="manga-single" data-manga-id="<?php echo $manga_id; ?>">
    <?php mangastream_breadcrumb(); ?>
    
    <div class="manga-header">
        <div class="manga-poster">
            <?php if (has_post_thumbnail()): ?>
                <img src="<?php the_post_thumbnail_url('manga-large'); ?>" 
                     alt="<?php the_title(); ?>" class="manga-cover">
            <?php endif; ?>
            
            <div class="manga-actions">
                <?php if (is_user_logged_in()): ?>
                    <button class="btn btn-primary bookmark-btn <?php echo is_manga_bookmarked($manga_id) ? 'bookmarked' : ''; ?>" 
                            data-manga-id="<?php echo $manga_id; ?>">
                        <i class="<?php echo is_manga_bookmarked($manga_id) ? 'fas' : 'far'; ?> fa-bookmark"></i> 
                        Bookmark
                    </button>
                <?php endif; ?>
                <button class="btn btn-secondary share-btn">
                    <i class="fas fa-share"></i> Bagikan
                </button>
            </div>
        </div>
        
        <div class="manga-info">
            <h1 class="manga-title"><?php the_title(); ?></h1>
            
            <div class="manga-meta">
                <?php if ($author): ?>
                    <div class="meta-item">
                        <strong>Pengarang:</strong> <?php echo esc_html($author); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($artist): ?>
                    <div class="meta-item">
                        <strong>Artist:</strong> <?php echo esc_html($artist); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($year): ?>
                    <div class="meta-item">
                        <strong>Tahun:</strong> <?php echo esc_html($year); ?>
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
                        <div class="rating-stars">
                            <?php
                            $full_stars = floor($rating);
                            $half_star = ($rating - $full_stars) >= 0.5;
                            
                            for ($i = 1; $i <= 5; $i++):
                                if ($i <= $full_stars):
                            ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i == $full_stars + 1 && $half_star): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php
                                endif;
                            endfor;
                            ?>
                            <span class="rating-number">(<?php echo $rating; ?>/5)</span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="meta-item">
                    <strong>Total Views:</strong> <?php echo format_view_count($stats['total_views']); ?>
                </div>
                
                <div class="meta-item">
                    <strong>Total Chapter:</strong> <?php echo $stats['total_chapters']; ?>
                </div>
                
                <div class="meta-item">
                    <strong>Terakhir Update:</strong> 
                    <?php echo human_time_diff(get_the_modified_time('U'), current_time('timestamp')); ?> lalu
                </div>
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
    
    <div class="manga-content">
        <div class="manga-description">
            <h3>Sinopsis</h3>
            <div class="description-text">
                <?php the_content(); ?>
            </div>
        </div>
        
        <!-- Chapter List -->
        <div class="chapter-list">
            <div class="chapter-list-header">
                <h3>Daftar Chapter</h3>
                <div class="chapter-controls">
                    <button class="sort-btn active" data-sort="asc">
                        <i class="fas fa-sort-numeric-down"></i> Terlama
                    </button>
                    <button class="sort-btn" data-sort="desc">
                        <i class="fas fa-sort-numeric-up"></i> Terbaru
                    </button>
                </div>
            </div>
            
            <div class="chapters" id="chapters-list">
                <?php
                $chapters = get_manga_chapters($manga_id);
                if ($chapters):
                    foreach ($chapters as $chapter):
                        $chapter_number = get_post_meta($chapter->ID, 'chapter_number', true);
                        $chapter_title = $chapter->post_title;
                        $chapter_date = get_the_date('', $chapter->ID);
                        $manga_slug = get_post_field('post_name', $manga_id);
                ?>
                    <div class="chapter-item" data-chapter="<?php echo $chapter_number; ?>">
                        <a href="<?php echo get_chapter_url($manga_slug, $chapter->post_name); ?>" class="chapter-link">
                            <span class="chapter-number">Chapter <?php echo $chapter_number; ?></span>
                            <?php if ($chapter_title && $chapter_title != 'Chapter ' . $chapter_number): ?>
                                <span class="chapter-title"><?php echo esc_html($chapter_title); ?></span>
                            <?php endif; ?>
                            <span class="chapter-date"><?php echo $chapter_date; ?></span>
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
    
    <!-- Related Manga -->
    <?php
    $related_manga = get_related_manga($manga_id, 6);
    if ($related_manga):
    ?>
        <div class="related-manga">
            <h3>Manga Serupa</h3>
            <div class="manga-grid">
                <?php
                foreach ($related_manga as $manga):
                    setup_postdata($manga);
                    get_template_part('template-parts/manga-card');
                endforeach;
                wp_reset_postdata();
                ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Comments Section -->
    <?php if (comments_open() || get_comments_number()): ?>
        <div class="comments-section">
            <h3>Komentar</h3>
            <?php comments_template(); ?>
        </div>
    <?php endif; ?>
</article>
