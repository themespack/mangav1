<?php get_header(); ?>

<div class="container">
    <div class="content-area">
        <?php if (have_posts()): ?>
            <?php while (have_posts()): the_post(); ?>
                <article class="page-content">
                    <header class="page-header">
                        <h1 class="page-title"><?php the_title(); ?></h1>
                    </header>
                    
                    <div class="page-content-inner">
                        <?php the_content(); ?>
                    </div>
                    
                    <?php if (comments_open() || get_comments_number()): ?>
                        <div class="comments-section">
                            <?php comments_template(); ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
