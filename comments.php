<?php
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">
    <?php if (have_comments()): ?>
        <h3 class="comments-title">
            <?php
            $comment_count = get_comments_number();
            if ($comment_count == 1) {
                echo '1 Komentar';
            } else {
                echo $comment_count . ' Komentar';
            }
            ?>
        </h3>

        <ol class="comment-list">
            <?php
            wp_list_comments(array(
                'style' => 'ol',
                'short_ping' => true,
                'callback' => 'mangastream_comment_callback'
            ));
            ?>
        </ol>

        <?php if (get_comment_pages_count() > 1 && get_option('page_comments')): ?>
            <nav class="comment-navigation">
                <div class="nav-previous"><?php previous_comments_link('&laquo; Komentar Sebelumnya'); ?></div>
                <div class="nav-next"><?php next_comments_link('Komentar Selanjutnya &raquo;'); ?></div>
            </nav>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')): ?>
        <p class="no-comments">Komentar ditutup.</p>
    <?php endif; ?>

    <?php
    comment_form(array(
        'title_reply' => 'Tinggalkan Komentar',
        'title_reply_to' => 'Balas ke %s',
        'cancel_reply_link' => 'Batal Balas',
        'label_submit' => 'Kirim Komentar',
        'comment_field' => '<p class="comment-form-comment"><label for="comment">Komentar *</label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>',
        'fields' => array(
            'author' => '<p class="comment-form-author"><label for="author">Nama *</label><input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" size="30" required /></p>',
            'email' => '<p class="comment-form-email"><label for="email">Email *</label><input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email']) . '" size="30" required /></p>',
            'url' => '<p class="comment-form-url"><label for="url">Website</label><input id="url" name="url" type="url" value="' . esc_attr($commenter['comment_author_url']) . '" size="30" /></p>'
        )
    ));
    ?>
</div>
