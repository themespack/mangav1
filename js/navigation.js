jQuery(document).ready(function($) {
    'use strict';
    
    // ===== MOBILE NAVIGATION =====
    
    function initMobileNavigation() {
        // Mobile menu toggle
        if (!$('.mobile-menu-toggle').length) {
            $('.header-container').append('<button class="mobile-menu-toggle"><i class="fas fa-bars"></i></button>');
        }
        
        $('.mobile-menu-toggle').on('click', function() {
            $(this).toggleClass('active');
            $('.main-navigation').toggleClass('active');
            $('body').toggleClass('menu-open');
            
            // Change icon
            const icon = $(this).find('i');
            if ($(this).hasClass('active')) {
                icon.removeClass('fa-bars').addClass('fa-times');
            } else {
                icon.removeClass('fa-times').addClass('fa-bars');
            }
        });
        
        // Close menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.main-navigation, .mobile-menu-toggle').length) {
                $('.mobile-menu-toggle').removeClass('active');
                $('.main-navigation').removeClass('active');
                $('body').removeClass('menu-open');
                $('.mobile-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
            }
        });
        
        // Close menu on window resize
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                $('.mobile-menu-toggle').removeClass('active');
                $('.main-navigation').removeClass('active');
                $('body').removeClass('menu-open');
                $('.mobile-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
            }
        });
    }
    
    // ===== SMOOTH SCROLLING =====
    
    function initSmoothScrolling() {
        $('a[href*="#"]:not([href="#"])').click(function() {
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                let target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                    return false;
                }
            }
        });
    }
    
    // ===== BACK TO TOP BUTTON =====
    
    function initBackToTop() {
        // Add back to top button
        if (!$('.back-to-top').length) {
            $('body').append('<button class="back-to-top"><i class="fas fa-arrow-up"></i></button>');
        }
        
        // Show/hide on scroll
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('.back-to-top').addClass('show');
            } else {
                $('.back-to-top').removeClass('show');
            }
        });
        
        // Click event
        $('.back-to-top').click(function() {
            $('html, body').animate({scrollTop: 0}, 800);
            return false;
        });
    }
    
    // ===== INITIALIZATION =====
    
    initMobileNavigation();
    initSmoothScrolling();
    initBackToTop();
    
    console.log('Navigation functionality initialized');
});
