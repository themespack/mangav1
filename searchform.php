<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="search-container">
        <input type="search" 
               class="search-input" 
               placeholder="Cari manga..." 
               value="<?php echo get_search_query(); ?>" 
               name="s" 
               id="search-input">
        <button type="submit" class="search-btn">
            <i class="fas fa-search"></i>
        </button>
        <div id="search-results" class="search-results"></div>
    </div>
</form>
