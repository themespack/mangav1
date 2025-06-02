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
            <span class="update-time">
                <?php echo human_time_diff(get_the_modified_time('U'), current_time('timestamp')) . ' yang lalu'; ?>
            </span>
        </div>
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
        <div class="genre-tags">
            <?php
            $genres = get_the_terms(get_the_ID(), 'manga_genre');
            if ($genres):
                foreach (array_slice($genres, 0, 3) as $genre):
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
