$(function () {
    const STYLE_URL = 'http://zc.weibo.com/static/';
    const BASE_URL = 'http://zc.weibo.com/index.php';
    const TAB_LIMIT = 2;
    let expand = false;
    let goto_lock = false;  //页面跳转lock

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


    /*--------------------------------- 消息框*/
    let tab_xl_content = [];           //存放请求的tab框内容

    function ajax_request_tab() {

    }

    //打开表情或者图像框
    function switch_option_box(tab)
    {
        $('.expand-box').css('display', 'block');
        $('.expand-content').find('.expand-tab').removeClass('expand-content_active');
        $('.expand-content').find('.expand-tab').eq(tab).addClass('expand-content_active');
    }

    //选择表情或者图像
    $('.wb-editor__options').on('click', '.option-item', function () {
        let tab = $(this).index();
        if(tab >= TAB_LIMIT) {
            return;         //除了表情和图片暂时不支持其它
        }
        switch_option_box(tab);
        let padding_left_length = tab*80;
        $('.expand-outer').css('paddingLeft', padding_left_length+'px');
    })

    //关闭扩展框
    $('.expand-box').on('click', '.expand-close', function () {
        $('.expand-box').css('display', 'none');
        $('.expand-content').find('.expand-tab').removeClass('expand-content_active');
    })

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

    /*---------------------------------------------- 登陆/注册窗口*/
    function User(username, password) {
        this.username = username;
        this.password = password;
    }
    function login_verify(user) {

    }

    function register(email) {
        $.post(BASE_URL+'action/register', {mail: email}, function (status, data) {
            if(status != 'success') {
                return;
            }
            console.log(data);
        })
    }

    $('.login-window').on('click', '.register-btn', function () {
        $('.login-register').find('a').text('已经注册完成~');
        $(this).text('返回登陆');
        $('.login-input_item').eq(0).addClass('input_hide');
        $('.login-input_item').eq(1).addClass('input_hide');
        $('.login-input_item').eq(2).removeClass('input_hide');
        $('.login-btn').text('注册');
        $('.login-btn').addClass('to-register');
        $('.login-btn').removeClass('login-btn');
    })

    $('.login-window').on('click', '.login-btn', function () {
        if(goto_lock) {
            return;
        }
        let username = $.trim($('.wb-user_input').val());
        let password = $.trim($('.wb-pass_input').val());
        if(username == '' || password == '' || username == undefined || password == undefined) {
            return;
        }
        login_verify(new User(username, password));
    })

    $('.login-window').on('click', '.to-register', function () {
        let mail = $.trim($('.wb-mail_input').val());
        if(mail == '' || mail == undefined) {
            return;
        }
        register(mail);
    })
})