<?php
/*
Template for single manga posts
*/

get_header();

// Check if this is a chapter request
$manga_slug = get_post_field('post_name');
$chapter_slug = get_query_var('chapter');

if ($chapter_slug) {
    // Display manga reader
    $chapter = get_posts(array(
        'post_type' => 'chapter',
        'name' => $chapter_slug,
        'meta_query' => array(
            array(
                'key' => 'manga_id',
                'value' => get_the_ID()
            )
        ),
        'posts_per_page' => 1
    ));
    
    if ($chapter) {
        setup_postdata($chapter[0]);
        get_template_part('template-parts/manga-reader');
        wp_reset_postdata();
    } else {
        get_template_part('404');
    }
} else {
    // Display manga info page
    get_template_part('template-parts/manga-single');
}

get_footer();
?>
