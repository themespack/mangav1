    </main>
    
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Tentang <?php bloginfo('name'); ?></h3>
                    <p>Website terbaik untuk membaca manga online gratis dengan update terbaru setiap hari. Nikmati ribuan judul manga dari berbagai genre.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-discord"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Genre Populer</h3>
                    <ul class="footer-links">
                        <?php
                        $popular_genres = get_terms(array(
                            'taxonomy' => 'manga_genre',
                            'number' => 8,
                            'orderby' => 'count',
                            'order' => 'DESC'
                        ));
                        
                        foreach ($popular_genres as $genre):
                        ?>
                            <li><a href="<?php echo get_term_link($genre); ?>"><?php echo $genre->name; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Link Cepat</h3>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'container' => false,
                        'menu_class' => 'footer-links',
                        'fallback_cb' => false
                    ));
                    ?>
                </div>
                
                <div class="footer-section">
                    <h3>Update Terbaru</h3>
                    <?php
                    $recent_manga = new WP_Query(array(
                        'post_type' => 'manga',
                        'posts_per_page' => 5,
                        'orderby' => 'modified',
                        'order' => 'DESC'
                    ));
                    
                    if ($recent_manga->have_posts()):
                    ?>
                        <ul class="footer-links">
                            <?php while ($recent_manga->have_posts()): $recent_manga->the_post(); ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                        <span class="update-time"><?php echo human_time_diff(get_the_modified_time('U'), current_time('timestamp')); ?> lalu</span>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Semua hak dilindungi.</p>
                    <p>Disclaimer: Semua manga di website ini hanya untuk tujuan promosi. Silakan dukung penulis dengan membeli manga asli.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <?php wp_footer(); ?>
</body>
</html>
