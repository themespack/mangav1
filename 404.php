<?php get_header(); ?>

<div class="container">
    <div class="content-area">
        <div class="error-404">
            <div class="error-content">
                <div class="error-image">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/404-manga.png" alt="404 Error" class="error-img">
                </div>
                
                <div class="error-text">
                    <h1 class="error-title">404</h1>
                    <h2 class="error-subtitle">Halaman Tidak Ditemukan</h2>
                    <p class="error-description">
                        Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin halaman tersebut telah dipindahkan, 
                        dihapus, atau Anda salah mengetik URL.
                    </p>
                    
                    <div class="error-actions">
                        <a href="<?php echo home_url(); ?>" class="btn btn-primary">Kembali ke Beranda</a>
                        <a href="<?php echo home_url('/manga/'); ?>" class="btn btn-secondary">Lihat Semua Manga</a>
                    </div>
                    
                    <!-- Search Form -->
                    <div class="error-search">
                        <h3>Atau cari manga yang Anda inginkan:</h3>
                        <form role="search" method="get" action="<?php echo home_url('/'); ?>" class="search-form-404">
                            <input type="search" placeholder="Cari manga..." name="s" class="search-input-404">
                            <input type="hidden" name="post_type" value="manga">
                            <button type="submit" class="search-btn-404">Cari</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Suggested Content -->
            <div class="suggested-content">
                <h3>Manga Populer</h3>
                <div class="manga-grid">
                    <?php
                    $popular_manga = new WP_Query(array(
                        'post_type' => 'manga',
                        'posts_per_page' => 8,
                        'meta_key' => 'total_views',
                        'orderby' => 'meta_value_num',
                        'order' => 'DESC'
                    ));
                    
                    if ($popular_manga->have_posts()):
                        while ($popular_manga->have_posts()): $popular_manga->the_post();
                            get_template_part('template-parts/manga-card');
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
            </div>
            
            <!-- Recent Updates -->
            <div class="recent-updates-404">
                <h3>Update Terbaru</h3>
                <ul class="recent-list">
                    <?php
                    $recent_chapters = new WP_Query(array(
                        'post_type' => 'chapter',
                        'posts_per_page' => 10,
                        'orderby' => 'date',
                        'order' => 'DESC'
                    ));
                    
                    if ($recent_chapters->have_posts()):
                        while ($recent_chapters->have_posts()): $recent_chapters->the_post();
                            $manga_id = get_post_meta(get_the_ID(), 'manga_id', true);
                            $manga = get_post($manga_id);
                            $chapter_number = get_post_meta(get_the_ID(), 'chapter_number', true);
                    ?>
                        <li>
                            <a href="<?php echo get_chapter_url($manga->post_name, get_post_field('post_name')); ?>">
                                <span class="manga-title"><?php echo $manga->post_title; ?></span>
                                <span class="chapter-number">Chapter <?php echo $chapter_number; ?></span>
                                <span class="update-time"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> lalu</span>
                            </a>
                        </li>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
