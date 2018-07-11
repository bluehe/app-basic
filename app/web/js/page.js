function my_alert(type, content, out = 0) {
    var html = '';
    html += '<div id="my_alert" class="alert-' + type + ' alert fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' + content + '</div>';
    $('header').prepend(html);
    if (out) {
        setTimeout(function () {
            $('header #my_alert').slideUp(500);
        }, out);
    }
}

$(document).ready(function () {

    $('body').on('click', '.list-group-item .clickurl', function () {
        var id = $(this).parents('.list-group-item').data('id');
        if (id) {
            $.get("/ajax/website-click", {id: id}, function (result) {});
        }
    });

    $('.recommend').on('click', '.clickurl', function () {
        var id = $(this).data('id');
        if (id) {
            $.get("/ajax/recommend-click", {id: id}, function (result) {});
        }
    });

    $("img.lazyload").lazyload({
        threshold: 500
    });

    $(function () {
        $(window).scroll(function () {
            if ($(window).scrollTop() > 500) {
                $("#to-top").fadeIn(500);
            } else {
                $("#to-top").fadeOut(500);
            }
        });
        $("#to-top").on('click', function () {
            $("html,body").animate({scrollTop: 0}, 500);
        });

    });
});
(function () {
    var bp = document.createElement('script');
    var curProtocol = window.location.protocol.split(':')[0];
    if (curProtocol === 'https') {
        bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
    } else {
        bp.src = 'http://push.zhanzhang.baidu.com/push.js';
    }
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(bp, s);
})();
