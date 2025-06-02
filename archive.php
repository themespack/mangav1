<?php get_header(); ?>

<div class="container">
    <div class="content-area">
        <?php if (is_tax('manga_genre') || is_tax('manga_status')): ?>
            <!-- Manga Taxonomy Archive -->
            <header class="archive-header">
                <h1 class="archive-title">
                    <?php if (is_tax('manga_genre')): ?>
                        Genre: <?php single_term_title(); ?>
                    <?php elseif (is_tax('manga_status')): ?>
                        Status: <?php single_term_title(); ?>
                    <?php endif; ?>
                </h1>
                
                <?php if (term_description()): ?>
                    <div class="archive-description">
                        <?php echo term_description(); ?>
                    </div>
                <?php endif; ?>
            </header>
            
        <?php elseif (is_post_type_archive('manga')): ?>
            <!-- Manga Archive -->
            <header class="archive-header">
                <h1 class="archive-title">Semua Manga</h1>
                <p class="archive-description">Jelajahi koleksi lengkap manga kami</p>
            </header>
            
        <?php else: ?>
            <!-- Default Archive -->
            <header class="archive-header">
                <h1 class="archive-title"><?php the_archive_title(); ?></h1>
                <?php if (the_archive_description()): ?>
                    <div class="archive-description">
                        <?php the_archive_description(); ?>
                    </div>
                <?php endif; ?>
            </header>
        <?php endif; ?>
        
        <!-- Manga Filters (only for manga archives) -->
        <?php if (is_post_type_archive('manga') || is_tax('manga_genre') || is_tax('manga_status')): ?>
            <div class="manga-filters">
                <div class="filter-row">
                    <div class="filter-group">
                        <input type="text" id="manga-search" placeholder="Cari manga..." class="filter-input">
                    </div>
                    
                    <div class="filter-group">
                        <select id="sort-by" class="filter-select">
                            <option value="latest">Terbaru</option>
                            <option value="popular">Populer</option>
                            <option value="title">Judul A-Z</option>
                            <option value="rating">Rating</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select id="status-filter" class="filter-select">
                            <option value="">Semua Status</option>
                            <?php
                            $statuses = get_terms(array(
                                'taxonomy' => 'manga_status',
                                'hide_empty' => true
                            ));
                            foreach ($statuses as $status):
                            ?>
                                <option value="<?php echo $status->slug; ?>" 
                                        <?php selected(is_tax('manga_status') ? get_queried_object()->slug : '', $status->slug); ?>>
                                    <?php echo $status->name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select id="genre-filter" class="filter-select">
                            <option value="">Semua Genre</option>
                            <?php
                            $genres = get_terms(array(
                                'taxonomy' => 'manga_genre',
                                'hide_empty' => true,
                                'orderby' => 'name'
                            ));
                            foreach ($genres as $genre):
                            ?>
                                <option value="<?php echo $genre->slug; ?>"
                                        <?php selected(is_tax('manga_genre') ? get_queried_object()->slug : '', $genre->slug); ?>>
                                    <?php echo $genre->name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="view-toggle">
                        <button class="view-btn active" data-view="grid">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Content -->
        <?php if (have_posts()): ?>
            <?php if (is_post_type_archive('manga') || is_tax('manga_genre') || is_tax('manga_status')): ?>
                <!-- Manga Grid -->
                <div class="manga-grid" id="manga-results">
                    <?php while (have_posts()): the_post(); ?>
                        <?php get_template_part('template-parts/manga-card'); ?>
                    <?php endwhile; ?>
                </div>
                
                <!-- Load More Button -->
                <div class="load-more-container">
                    <button id="load-more-manga" class="btn btn-primary" data-page="2">Muat Lebih Banyak</button>
                </div>
                
            <?php else: ?>
                <!-- Default Archive Layout -->
                <div class="archive-posts">
                    <?php while (have_posts()): the_post(); ?>
                        <article class="archive-post">
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <div class="post-meta">
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                                <span class="post-author">oleh <?php the_author(); ?></span>
                            </div>
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="read-more">Baca Selengkapnya</a>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <?php
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => '&laquo; Sebelumnya',
                    'next_text' => 'Selanjutnya &raquo;'
                ));
                ?>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-results">
                <h2>Tidak ada konten ditemukan</h2>
                <p>Maaf, tidak ada konten yang sesuai dengan kriteria pencarian Anda.</p>
                <a href="<?php echo home_url('/manga/'); ?>" class="btn btn-primary">Lihat Semua Manga</a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
