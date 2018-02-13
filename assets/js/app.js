import $ from 'jquery';

require('bootstrap');

$(document).ready(function() {
    function shootingStar(that, dir) {
        setInterval(function() {
            var topPos = Math.floor(Math.random() * 80) + 1,
                topPos = topPos + '%',
                leftPos = Math.floor(Math.random() * 40) + 1,
                leftPos = leftPos + '%',
                trans = Math.floor(Math.random() * 300) + 1,
                trans = trans + 'deg';
            that.css({
                top: topPos,
                left: leftPos,
                dir: dir,
                transform: 'rotate(' + trans + ')',
            });
        }, 2000);
    }

    $('.shooting-star').each(function() {
        var el = $(this);
        shootingStar(el, 'left');
    });

    $('.shooting-star-right').each(function() {
        var el = $(this);
        shootingStar(el, 'right');
    });

    $(document).on('click', 'a[href^="#"]', function(event) {
        event.preventDefault();

        $('html, body').animate(
            {
                scrollTop: $($.attr(this, 'href')).offset().top,
            },
            500,
        );
    });
});
