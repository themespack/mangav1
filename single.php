<?php get_header(); ?>

<div class="container">
    <div class="content-area">
        <?php if (have_posts()): ?>
            <?php while (have_posts()): the_post(); ?>
                <?php if (get_post_type() == 'manga'): ?>
                    <?php 
                    // Check if this is a chapter request
                    $chapter_slug = get_query_var('chapter');
                    if ($chapter_slug) {
                        // Display manga reader
                        get_template_part('template-parts/manga-reader');
                    } else {
                        // Display manga info page
                        get_template_part('template-parts/manga-single');
                    }
                    ?>
                <?php else: ?>
                    <article class="single-post">
                        <header class="post-header">
                            <h1 class="post-title"><?php the_title(); ?></h1>
                            <div class="post-meta">
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                                <span class="post-author">oleh <?php the_author(); ?></span>
                                <?php if (has_category()): ?>
                                    <span class="post-categories">
                                        dalam <?php the_category(', '); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </header>
                        
                        <div class="post-content">
                            <?php the_content(); ?>
                        </div>
                        
                        <?php if (has_tag()): ?>
                            <div class="post-tags">
                                <strong>Tags:</strong> <?php the_tags('', ', '); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-navigation">
                            <?php
                            $prev_post = get_previous_post();
                            $next_post = get_next_post();
                            ?>
                            
                            <?php if ($prev_post): ?>
                                <div class="nav-previous">
                                    <a href="<?php echo get_permalink($prev_post); ?>">
                                        &laquo; <?php echo get_the_title($prev_post); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($next_post): ?>
                                <div class="nav-next">
                                    <a href="<?php echo get_permalink($next_post); ?>">
                                        <?php echo get_the_title($next_post); ?> &raquo;
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (comments_open() || get_comments_number()): ?>
                            <div class="comments-section">
                                <?php comments_template(); ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
