// Sticky Navbar Scroll Effect
$(document).ready(function(){
    $(window).scroll(function(){
        if($(this).scrollTop() > 50){
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });
    
    // Smooth scrolling for anchor links (ignore bare "#" placeholders and missing targets)
    $('a[href*="#"]').not('[href="#"]').on('click', function(e) {
        let target = $($(this).attr('href'));
        if (!target.length) {
            return;
        }

        e.preventDefault();

        $('html, body').animate(
            {
                scrollTop: target.offset().top - 70,
            },
            500,
            'linear'
        );
    });
});