<?php get_header(); ?>

<div class="container">
    <div class="content-area">
        <?php if (is_home() || is_front_page()): ?>
            <!-- Featured Slider -->
            <section class="featured-slider">
                <h2>Manga Populer</h2>
                <div class="manga-grid">
                    <?php
                    $featured_query = new WP_Query(array(
                        'post_type' => 'manga',
                        'posts_per_page' => 8,
                        'meta_key' => 'featured',
                        'meta_value' => 'yes'
                    ));
                    
                    if ($featured_query->have_posts()):
                        while ($featured_query->have_posts()): $featured_query->the_post();
                    ?>
                        <div class="manga-card">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()): ?>
                                    <img src="<?php the_post_thumbnail_url('manga-thumbnail'); ?>" 
                                         alt="<?php the_title(); ?>" class="manga-thumbnail">
                                <?php endif; ?>
                            </a>
                            <div class="manga-info">
                                <a href="<?php the_permalink(); ?>" class="manga-title">
                                    <?php the_title(); ?>
                                </a>
                                <div class="manga-meta">
                                    <?php
                                    $status = get_the_terms(get_the_ID(), 'manga_status');
                                    if ($status):
                                        foreach ($status as $stat):
                                    ?>
                                        <span class="manga-status <?php echo esc_attr($stat->slug); ?>">
                                            <?php echo esc_html($stat->name); ?>
                                        </span>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                                <div class="genre-tags">
                                    <?php
                                    $genres = get_the_terms(get_the_ID(), 'manga_genre');
                                    if ($genres):
                                        foreach ($genres as $genre):
                                    ?>
                                        <a href="<?php echo get_term_link($genre); ?>" class="genre-tag">
                                            <?php echo esc_html($genre->name); ?>
                                        </a>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
            </section>
            
            <!-- Latest Updates -->
            <section class="latest-updates">
                <h2>Update Terbaru</h2>
                <div class="manga-grid">
                    <?php
                    $latest_query = new WP_Query(array(
                        'post_type' => 'manga',
                        'posts_per_page' => 12,
                        'orderby' => 'modified',
                        'order' => 'DESC'
                    ));
                    
                    if ($latest_query->have_posts()):
                        while ($latest_query->have_posts()): $latest_query->the_post();
                            // Similar structure as above
                            get_template_part('template-parts/manga-card');
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
            </section>
        <?php else: ?>
            <?php if (have_posts()): ?>
                <div class="manga-grid">
                    <?php while (have_posts()): the_post(); ?>
                        <?php get_template_part('template-parts/manga-card'); ?>
                    <?php endwhile; ?>
                </div>
                
                <?php
                // Pagination
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => '&laquo; Sebelumnya',
                    'next_text' => 'Selanjutnya &raquo;'
                ));
                ?>
            <?php else: ?>
                <p>Tidak ada manga yang ditemukan.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
