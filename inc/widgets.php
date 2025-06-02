<?php
// Popular Manga Widget
class Popular_Manga_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'popular_manga_widget',
            'Popular Manga',
            array('description' => 'Menampilkan manga populer')
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : 'Manga Populer';
        $number = !empty($instance['number']) ? $instance['number'] : 10;
        
        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        
        $popular_query = new WP_Query(array(
            'post_type' => 'manga',
            'posts_per_page' => $number,
            'meta_key' => 'total_views',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        ));
        
        if ($popular_query->have_posts()):
            echo '<div class="popular-manga-list">';
            $counter = 1;
            while ($popular_query->have_posts()): $popular_query->the_post();
        ?>
            <div class="popular-manga-item">
                <span class="popular-rank"><?php echo $counter; ?></span>
                <div class="popular-thumbnail">
                    <?php if (has_post_thumbnail()): ?>
                        <img src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php the_title(); ?>">
                    <?php endif; ?>
                </div>
                <div class="popular-info">
                    <a href="<?php the_permalink(); ?>" class="popular-title"><?php the_title(); ?></a>
                    <div class="popular-meta">
                        <span class="popular-views"><?php echo number_format(get_post_meta(get_the_ID(), 'total_views', true)); ?> views</span>
                    </div>
                </div>
            </div>
        <?php
            $counter++;
            endwhile;
            echo '</div>';
            wp_reset_postdata();
        endif;
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Manga Populer';
        $number = !empty($instance['number']) ? $instance['number'] : 10;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">Number of manga to show:</label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>" 
                   name="<?php echo $this->get_field_name('number'); ?>" type="number" 
                   step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 10;
        return $instance;
    }
}

// Latest Chapters Widget
class Latest_Chapters_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'latest_chapters_widget',
            'Latest Chapters',
            array('description' => 'Menampilkan chapter terbaru')
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : 'Chapter Terbaru';
        $number = !empty($instance['number']) ? $instance['number'] : 15;
        
        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        
        $chapters_query = new WP_Query(array(
            'post_type' => 'chapter',
            'posts_per_page' => $number,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if ($chapters_query->have_posts()):
            echo '<ul class="latest-chapters-list">';
            while ($chapters_query->have_posts()): $chapters_query->the_post();
                $manga_id = get_post_meta(get_the_ID(), 'manga_id', true);
                $manga = get_post($manga_id);
                $chapter_number = get_post_meta(get_the_ID(), 'chapter_number', true);
        ?>
            <li class="chapter-item">
                <a href="<?php echo get_chapter_url($manga->post_name, get_post_field('post_name')); ?>">
                    <span class="manga-title"><?php echo $manga->post_title; ?></span>
                    <span class="chapter-number">Ch. <?php echo $chapter_number; ?></span>
                </a>
                <span class="chapter-time"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> lalu</span>
            </li>
        <?php
            endwhile;
            echo '</ul>';
            wp_reset_postdata();
        endif;
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Chapter Terbaru';
        $number = !empty($instance['number']) ? $instance['number'] : 15;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">Number of chapters to show:</label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>" 
                   name="<?php echo $this->get_field_name('number'); ?>" type="number" 
                   step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 15;
        return $instance;
    }
}

// Register widgets
function register_mangastream_widgets() {
    register_widget('Popular_Manga_Widget');
    register_widget('Latest_Chapters_Widget');
}
add_action('widgets_init', 'register_mangastream_widgets');
?>
