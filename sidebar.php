<aside class="sidebar">
    <?php if (is_active_sidebar('main-sidebar')): ?>
        <?php dynamic_sidebar('main-sidebar'); ?>
    <?php else: ?>
        <!-- Default widgets if no widgets are assigned -->
        
        <!-- Popular Today Widget -->
        <div class="widget">
            <h3 class="widget-title">Populer Hari Ini</h3>
            <div class="popular-list">
                <?php
                $popular_today = new WP_Query(array(
                    'post_type' => 'manga',
                    'posts_per_page' => 10,
                    'meta_key' => 'daily_views',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                    'date_query' => array(
                        array(
                            'after' => '1 day ago'
                        )
                    )
                ));
                
                if ($popular_today->have_posts()):
                    $counter = 1;
                    while ($popular_today->have_posts()): $popular_today->the_post();
                ?>
                    <div class="popular-item">
                        <span class="popular-rank"><?php echo $counter; ?></span>
                        <div class="popular-thumbnail">
                            <?php if (has_post_thumbnail()): ?>
                                <img src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php the_title(); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="popular-info">
                            <a href="<?php the_permalink(); ?>" class="popular-title"><?php the_title(); ?></a>
                            <div class="popular-meta">
                                <?php
                                $latest_chapter = get_latest_chapter(get_the_ID());
                                if ($latest_chapter):
                                ?>
                                    <span class="popular-chapter">Ch. <?php echo $latest_chapter->chapter_number; ?></span>
                                <?php endif; ?>
                                <span class="popular-views"><?php echo get_post_meta(get_the_ID(), 'daily_views', true); ?> views</span>
                            </div>
                        </div>
                    </div>
                <?php
                    $counter++;
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
        
        <!-- Latest Updates Widget -->
        <div class="widget">
            <h3 class="widget-title">Update Terbaru</h3>
            <ul class="latest-updates-list">
                <?php
                $latest_updates = new WP_Query(array(
                    'post_type' => 'chapter',
                    'posts_per_page' => 15,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ));
                
                if ($latest_updates->have_posts()):
                    while ($latest_updates->have_posts()): $latest_updates->the_post();
                        $manga_id = get_post_meta(get_the_ID(), 'manga_id', true);
                        $manga = get_post($manga_id);
                        $chapter_number = get_post_meta(get_the_ID(), 'chapter_number', true);
                ?>
                    <li class="update-item">
                        <a href="<?php echo get_chapter_url($manga->post_name, get_post_field('post_name')); ?>">
                            <span class="manga-title"><?php echo $manga->post_title; ?></span>
                            <span class="chapter-number">Ch. <?php echo $chapter_number; ?></span>
                        </a>
                        <span class="update-time"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> lalu</span>
                    </li>
                <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </ul>
        </div>
        
        <!-- Genre Filter Widget -->
        <div class="widget">
            <h3 class="widget-title">Genre</h3>
            <div class="genre-filter">
                <?php
                $genres = get_terms(array(
                    'taxonomy' => 'manga_genre',
                    'hide_empty' => true,
                    'orderby' => 'name',
                    'order' => 'ASC'
                ));
                
                foreach ($genres as $genre):
                ?>
                    <a href="<?php echo get_term_link($genre); ?>" class="genre-link">
                        <?php echo $genre->name; ?>
                        <span class="genre-count">(<?php echo $genre->count; ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Random Manga Widget -->
        <div class="widget">
            <h3 class="widget-title">Manga Random</h3>
            <div class="random-manga">
                <?php
                $random_manga = new WP_Query(array(
                    'post_type' => 'manga',
                    'posts_per_page' => 1,
                    'orderby' => 'rand'
                ));
                
                if ($random_manga->have_posts()):
                    while ($random_manga->have_posts()): $random_manga->the_post();
                ?>
                    <div class="random-manga-card">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()): ?>
                                <img src="<?php the_post_thumbnail_url('manga-thumbnail'); ?>" alt="<?php the_title(); ?>">
                            <?php endif; ?>
                        </a>
                        <div class="random-manga-info">
                            <a href="<?php the_permalink(); ?>" class="random-manga-title"><?php the_title(); ?></a>
                            <div class="random-manga-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="read-now-btn">Baca Sekarang</a>
                        </div>
                    </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                <button id="random-manga-btn" class="random-btn">Manga Lain</button>
            </div>
        </div>
        
    <?php endif; ?>
</aside>
