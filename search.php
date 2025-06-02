<?php get_header(); ?>

<div class="container">
    <div class="content-area">
        <?php
        // Get post type safely
        $current_post_type = get_query_var('post_type');
        if (is_array($current_post_type)) {
            $current_post_type = isset($current_post_type[0]) ? $current_post_type[0] : '';
        }
        ?>
        
        <header class="search-header">
            <h1 class="search-title">
                <?php if (get_search_query()): ?>
                    Hasil Pencarian untuk: "<?php echo get_search_query(); ?>"
                <?php else: ?>
                    Hasil Pencarian
                <?php endif; ?>
            </h1>
            <p class="search-results-count">
                <?php
                global $wp_query;
                $total = $wp_query->found_posts;
                if ($total > 0) {
                    echo "Ditemukan {$total} hasil";
                } else {
                    echo "Tidak ada hasil ditemukan";
                }
                ?>
            </p>
        </header>
        
        <!-- Advanced Search Form -->
        <div class="advanced-search">
            <form class="search-form-advanced" method="get" action="<?php echo home_url('/'); ?>">
                <div class="search-fields">
                    <input type="text" name="s" value="<?php echo get_search_query(); ?>" 
                           placeholder="Cari manga, chapter, atau kata kunci..." class="search-input-advanced">
                    
                    <select name="post_type" class="search-select">
                        <option value="">Semua Tipe</option>
                        <option value="manga" <?php 
                            if (is_array($current_post_type)) {
                                echo in_array('manga', $current_post_type) ? 'selected="selected"' : '';
                            } else {
                                echo selected($current_post_type, 'manga', false);
                            }
                        ?>>Manga</option>
                        <option value="post" <?php 
                            if (is_array($current_post_type)) {
                                echo in_array('post', $current_post_type) ? 'selected="selected"' : '';
                            } else {
                                echo selected($current_post_type, 'post', false);
                            }
                        ?>>Artikel</option>
                    </select>
                    
                    <?php if ($current_post_type === 'manga' || empty($current_post_type)): ?>
                    <select name="manga_genre" class="search-select">
                        <option value="">Semua Genre</option>
                        <?php
                        $genres = get_terms(array(
                            'taxonomy' => 'manga_genre',
                            'hide_empty' => true
                        ));
                        $current_genre = get_query_var('manga_genre');
                        if ($genres && !is_wp_error($genres)):
                            foreach ($genres as $genre):
                        ?>
                            <option value="<?php echo $genre->slug; ?>" 
                                    <?php 
                                    if (is_array($current_genre)) {
                                        echo in_array($genre->slug, $current_genre) ? 'selected="selected"' : '';
                                    } else {
                                        echo selected($current_genre, $genre->slug, false);
                                    }
                                    ?>>
                                <?php echo $genre->name; ?>
                            </option>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </select>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>
        </div>
        
        <?php if (have_posts()): ?>
            <div class="search-results-content">
                <?php if ($current_post_type === 'manga'): ?>
                    <div class="manga-grid">
                        <?php while (have_posts()): the_post(); ?>
                            <?php get_template_part('template-parts/manga-card'); ?>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="search-results-list">
                        <?php while (have_posts()): the_post(); ?>
                            <article class="search-result-item">
                                <h2 class="result-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <div class="result-meta">
                                    <span class="result-type"><?php echo get_post_type_object(get_post_type())->labels->singular_name; ?></span>
                                    <span class="result-date"><?php echo get_the_date(); ?></span>
                                </div>
                                <div class="result-excerpt">
                                    <?php
                                    $excerpt = get_the_excerpt();
                                    $search_term = get_search_query();
                                    if ($search_term) {
                                        $excerpt = preg_replace('/(' . preg_quote($search_term, '/') . ')/i', '<mark>$1</mark>', $excerpt);
                                    }
                                    echo $excerpt;
                                    ?>
                                </div>
                                <a href="<?php the_permalink(); ?>" class="btn btn-secondary btn-small">Baca Selengkapnya</a>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
                
                <?php
                // Pagination
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => '&laquo; Sebelumnya',
                    'next_text' => 'Selanjutnya &raquo;'
                ));
                ?>
            </div>
        <?php else: ?>
            <div class="no-search-results">
                <h2>Tidak ada hasil ditemukan</h2>
                <?php if (get_search_query()): ?>
                    <p>Maaf, pencarian untuk "<strong><?php echo get_search_query(); ?></strong>" tidak menghasilkan apapun.</p>
                <?php else: ?>
                    <p>Silakan masukkan kata kunci pencarian.</p>
                <?php endif; ?>
                
                <div class="search-suggestions">
                    <h3>Saran:</h3>
                    <ul>
                        <li>Periksa ejaan kata kunci</li>
                        <li>Gunakan kata kunci yang lebih umum</li>
                        <li>Coba kata kunci yang berbeda</li>
                        <li>Gunakan lebih sedikit kata kunci</li>
                    </ul>
                </div>
                
                <div class="popular-searches">
                    <h3>Manga Populer:</h3>
                    <div class="manga-grid">
                        <?php
                        $popular_manga = new WP_Query(array(
                            'post_type' => 'manga',
                            'posts_per_page' => 6,
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
            </div>
        <?php endif; ?>
    </div>
    
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
