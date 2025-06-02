<?php
function mangastream_customize_register($wp_customize) {
    // Theme Options Panel
    $wp_customize->add_panel('mangastream_options', array(
        'title' => 'MangaStream Options',
        'priority' => 30
    ));
    
    // General Settings Section
    $wp_customize->add_section('mangastream_general', array(
        'title' => 'General Settings',
        'panel' => 'mangastream_options'
    ));
    
    // Site Logo
    $wp_customize->add_setting('mangastream_logo', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw'
    ));
    
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'mangastream_logo', array(
        'label' => 'Site Logo',
        'section' => 'mangastream_general',
        'settings' => 'mangastream_logo'
    )));
    
    // Enable Dark Mode
    $wp_customize->add_setting('mangastream_dark_mode', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean'
    ));
    
    $wp_customize->add_control('mangastream_dark_mode', array(
        'label' => 'Enable Dark Mode Toggle',
        'section' => 'mangastream_general',
        'type' => 'checkbox'
    ));
    
    // Layout Settings Section
    $wp_customize->add_section('mangastream_layout', array(
        'title' => 'Layout Settings',
        'panel' => 'mangastream_options'
    ));
    
    // Sidebar Position
    $wp_customize->add_setting('mangastream_sidebar_position', array(
        'default' => 'right',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    
    $wp_customize->add_control('mangastream_sidebar_position', array(
        'label' => 'Sidebar Position',
        'section' => 'mangastream_layout',
        'type' => 'select',
        'choices' => array(
            'left' => 'Left',
            'right' => 'Right',
            'none' => 'No Sidebar'
        )
    ));
    
    // Manga per page
    $wp_customize->add_setting('mangastream_manga_per_page', array(
        'default' => 24,
        'sanitize_callback' => 'absint'
    ));
    
    $wp_customize->add_control('mangastream_manga_per_page', array(
        'label' => 'Manga per Page',
        'section' => 'mangastream_layout',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 6,
            'max' => 48,
            'step' => 6
        )
    ));
    
    // Colors Section
    $wp_customize->add_section('mangastream_colors', array(
        'title' => 'Colors',
        'panel' => 'mangastream_options'
    ));
    
    // Primary Color
    $wp_customize->add_setting('mangastream_primary_color', array(
        'default' => '#007cba',
        'sanitize_callback' => 'sanitize_hex_color'
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'mangastream_primary_color', array(
        'label' => 'Primary Color',
        'section' => 'mangastream_colors'
    )));
    
    // Secondary Color
    $wp_customize->add_setting('mangastream_secondary_color', array(
        'default' => '#28a745',
        'sanitize_callback' => 'sanitize_hex_color'
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'mangastream_secondary_color', array(
        'label' => 'Secondary Color',
        'section' => 'mangastream_colors'
    )));
    
    // Social Media Section
    $wp_customize->add_section('mangastream_social', array(
        'title' => 'Social Media',
        'panel' => 'mangastream_options'
    ));
    
    $social_networks = array(
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'instagram' => 'Instagram',
        'discord' => 'Discord',
        'telegram' => 'Telegram'
    );
    
    foreach ($social_networks as $network => $label) {
        $wp_customize->add_setting('mangastream_' . $network, array(
            'default' => '',
            'sanitize_callback' => 'esc_url_raw'
        ));
        
         $wp_customize->add_control('mangastream_' . $network, array(
            'label' => $label . ' URL',
            'section' => 'mangastream_social',
            'type' => 'url'
        ));
    }
    
    // Reader Settings Section
    $wp_customize->add_section('mangastream_reader', array(
        'title' => 'Reader Settings',
        'panel' => 'mangastream_options'
    ));
    
    // Default Reading Mode
    $wp_customize->add_setting('mangastream_reading_mode', array(
        'default' => 'all_pages',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    
    $wp_customize->add_control('mangastream_reading_mode', array(
        'label' => 'Default Reading Mode',
        'section' => 'mangastream_reader',
        'type' => 'select',
        'choices' => array(
            'all_pages' => 'All Pages',
            'single_page' => 'Single Page'
        )
    ));
    
    // Enable Keyboard Navigation
    $wp_customize->add_setting('mangastream_keyboard_nav', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean'
    ));
    
    $wp_customize->add_control('mangastream_keyboard_nav', array(
        'label' => 'Enable Keyboard Navigation',
        'section' => 'mangastream_reader',
        'type' => 'checkbox'
    ));
    
    // Image Quality
    $wp_customize->add_setting('mangastream_image_quality', array(
        'default' => 'high',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    
    $wp_customize->add_control('mangastream_image_quality', array(
        'label' => 'Image Quality',
        'section' => 'mangastream_reader',
        'type' => 'select',
        'choices' => array(
            'low' => 'Low (Fast Loading)',
            'medium' => 'Medium',
            'high' => 'High (Best Quality)'
        )
    ));
}
add_action('customize_register', 'mangastream_customize_register');

// Output custom CSS
function mangastream_customizer_css() {
    $primary_color = get_theme_mod('mangastream_primary_color', '#007cba');
    $secondary_color = get_theme_mod('mangastream_secondary_color', '#28a745');
    ?>
    <style type="text/css">
        :root {
            --primary-color: <?php echo esc_attr($primary_color); ?>;
            --secondary-color: <?php echo esc_attr($secondary_color); ?>;
        }
        
        .site-logo,
        .main-navigation a:hover,
        .manga-title:hover,
        .widget a:hover,
        .breadcrumb a,
        .genre-tag:hover {
            color: var(--primary-color);
        }
        
        .nav-btn,
        .pagination a:hover,
        .pagination .current,
        .search-btn,
        .btn-primary {
            background-color: var(--primary-color);
        }
        
        .manga-status,
        .btn-secondary {
            background-color: var(--secondary-color);
        }
        
        .widget-title {
            border-bottom-color: var(--primary-color);
        }
        
        .progress-bar {
            background-color: var(--primary-color);
        }
    </style>
    <?php
}
add_action('wp_head', 'mangastream_customizer_css');
?>
