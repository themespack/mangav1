<div class="manga-list-page">
    <header class="page-header">
        <h1 class="page-title">Semua Manga</h1>
        <p class="page-description">Jelajahi koleksi lengkap manga kami</p>
    </header>

    <!-- Filter dan Search -->
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
                        <option value="<?php echo $status->slug; ?>"><?php echo $status->name; ?></option>
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
                        <option value="<?php echo $genre->slug; ?>"><?php echo $genre->name; ?></option>
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

    <!-- Manga Grid -->
    <div class="manga-grid" id="manga-results">
        <?php
        $manga_query = new WP_Query(array(
            'post_type' => 'manga',
            'posts_per_page' => get_theme_mod('mangastream_manga_per_page', 24),
            'orderby' => 'modified',
            'order' => 'DESC'
        ));

        if ($manga_query->have_posts()):
            while ($manga_query->have_posts()): $manga_query->the_post();
                get_template_part('template-parts/manga-card');
            endwhile;
            wp_reset_postdata();
        else:
        ?>
            <div class="no-manga">
                <h3>Belum ada manga</h3>
                <p>Belum ada manga yang tersedia saat ini.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Load More Button -->
    <div class="load-more-container">
        <button id="load-more-manga" class="btn btn-primary" data-page="2">Muat Lebih Banyak</button>
    </div>
</div>
