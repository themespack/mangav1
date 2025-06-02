jQuery(document).ready(function($) {
    'use strict';
    
    // ===== DARK MODE FUNCTIONALITY =====
    
    function initDarkMode() {
        // Check saved preference
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            $('body').addClass('dark-mode');
            updateDarkModeIcon(true);
        }
        
        // Toggle functionality
        $('#dark-mode-toggle').on('click', function() {
            $('body').toggleClass('dark-mode');
            const isDark = $('body').hasClass('dark-mode');
            
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateDarkModeIcon(isDark);
            
            // Trigger custom event
            $(document).trigger('themeChanged', [isDark]);
        });
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (!localStorage.getItem('theme')) {
                if (e.matches) {
                    $('body').addClass('dark-mode');
                    updateDarkModeIcon(true);
                } else {
                    $('body').removeClass('dark-mode');
                    updateDarkModeIcon(false);
                }
            }
        });
    }
    
    function updateDarkModeIcon(isDark) {
        const icon = $('#dark-mode-toggle i');
        if (isDark) {
            icon.removeClass('fa-moon').addClass('fa-sun');
        } else {
            icon.removeClass('fa-sun').addClass('fa-moon');
        }
    }
    
    // ===== INITIALIZATION =====
    
    initDarkMode();
    
    console.log('Dark mode functionality initialized');
});
