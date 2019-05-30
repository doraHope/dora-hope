$(function(){
    const STYLE_URL = 'http://zc.weibo.com/static/';

    let expand = false;

    function change2img(pos) {
        $('.news-photo__link li').removeClass('news-photo__link--active');
        $('.news-photo__link li').eq(pos).addClass('news-photo__link--active');
    }

    function change_nav_position(type = 0) {
        if(type == 0) {
            $('.mid-content').css('marginLeft', '0');
            $('.left-nav').removeClass('left-nav__fixed');
        } else {
            $('.mid-content').css('marginLeft', '110px');
            $('.left-nav').addClass('left-nav__fixed');
        }
    }



    /*----------------- 事件*/
    //轮播图导航按钮
    $('.news-nav__bar').on('click', '.news-nav__bar--li', function () {
        $('.news-nav__bar .news-nav__bar--li').removeClass('news-nav__bar--active_li');
        $(this).addClass('news-nav__bar--active_li');
        change2img($(this).index());
    })

    //左侧导航栏
    $('.nav-list').on('mouseenter', '.nav-item span', function () {
        $(this).addClass('active-nav');
    })

    $('.nav-list').on('mouseleave', '.nav-item span', function () {
        $('.nav-list .nav-item span').removeClass('active-nav');
    })

    //左侧导航栏fixed变化
    $(window).scroll(function (e) {
        e.preventDefault();
        let window_scroll_top = $(window).scrollTop();
        console.log(window_scroll_top);
        if(window_scroll_top > 50) {
            change_nav_position(1);
        } else {
            change_nav_position(0);
        }
    })

    //按钮悬浮事件
    
    $('.options-list').on('mouseenter', '.option-item', function () {
        let img = $(this)[0].dataset['url'];
        $(this).attr('data-url', $(this).find('.options-item_img').attr('src'));
        $(this).find('.options-item_img').attr('src', img);
        $(this).find('.options-item_link').addClass('options-item_link--active');
    });

    $('.options-list').on('mouseleave', '.option-item', function () {
        let img = $(this)[0].dataset['url'];
        $(this).attr('data-url', $(this).find('.options-item_img').attr('src'));
        $(this).find('.options-item_img').attr('src', img);
        $(this).find('.options-item_link').removeClass('options-item_link--active');
    });

    //消息发送框动态变化
    $('.wb-editor__input').keyup(function () {
        let length = $(this).val().length;
        console.log(length);
        let lines = $(this).val().split('\n').length;
        if(
            lines > 3
            || (lines == 1 && length > 47*3)
            || (lines == 2 && length > 47*2.5)
            || (lines == 3 && length > 47*1.5)
        ) {
            if(expand) {
                return;
            }
            expand = true;
            $(this).css({'height':$(this).height()*3+'px', 'overflowY':'scroll'});
        } else {
            $(this).css({'height':'68px', 'overflowY':'hidden'});
            expand = false;
        }
    })

})