$(function () {
    const STYLE_URL = 'http://www.caibird.top/static/';
    const BASE_URL = 'http://www.caibird.top/index.php';
    const TAB_LIMIT = 2;
    let expand = false;
    let goto_lock = false;  //页面跳转lock

    /*--- 消息*/
    let res_wb_information_list = $('.news-list');
    let wb_load_index = 0;
    let wb_per_load = 10;
    let wb_load_offset = 0;
    let wb_information_index = 0;
    let wb_information_count = 0;
    // let wb_information_limit = 100;  存储上限，暂时不考虑
    let load_wb_lock = false;
    let wb_objects = [];


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

    /*---------------- 初始化*/
    if($('.login_token').val() != 0) {
        init();
    }

    function init() {
        requestWb();
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
        $.post(BASE_URL+'/action/login-verify', {username: user.username, password: user.password}, function (data, status) {
            console.log(data);
            if(status != 'success') {
                return;
            }
            let ret;
            try{
                ret = JSON.parse(data);
                if(ret['code'] != null && ret['code'] == 0) {
                    window.location.reload();
                } else {
                    alert(ret['msg']);
                }
            } catch (exception) {
                alert('json解析失败');
            }
        })
    }

    function register(email) {
        $.post(BASE_URL+'/action/register', {mail: email}, function (status, data) {
            if(status != 'success') {
                return;
            }
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

    $('.user-bar').on('click', '.logout', function () {
        $.post(BASE_URL+'/action/logout', function (data, status) {
            if(status != 'success') {
                alert('请检查网络是否连接正常');
            }
            let ret;
            try{
                ret = JSON.parse(data);
                if(ret['code'] != null && ret['code'] == 0) {
                    window.location.reload();
                } else {
                    alert(ret['msg']);
                }
            }catch (exception) {

            }
        })
    })

    /*----------------- 微博消息加载 */
    //消息类
    function WbEvent(openid, headimg, nickname, content, liker, comments, trans, create_time, update_time) {
        this.openid = openid;
        this.headimg = headimg;
        this.nickname = nickname;
        this.content = content;
        this.liker = liker;
        this.comments = comments;
        this.trans = trans;
        this.create = create_time;
        this.update = update_time;
    }
    
    function item_weibo(wb_obj) {
        let event_weibo = $('<div class="wb-event_weibo"></div>');

        let wb_top = $('<div class="wb-event_weibo--top"></div>');
        let user_icon = $('<img class="wb-user_icon" src="http://zc.weibo.com/static/imgs/wb-default_icon.svg" alt="">');
        let nickname = $('<span class="wb-user_nickname"></span>');
        nickname.text(wb_obj.nickname);
        let user_labels = $('<div class="wb-user_labels"></div>');
        let create_time = $('<a class="wb-create_time"></a>');
        create_time.text(wb_obj.create);
        wb_top.append(user_icon);
        wb_top.append(nickname);
        wb_top.append(user_labels);
        wb_top.append(create_time);
        let wb_content = $('<div class="wb-event_weibo--content"></div>');
        let content_wz = $('<div class="wb-event_weibo--content_wz"></div>');
        let content = $('<p></p>');
        content.text(wb_obj.content);
        content_wz.append(content)
        wb_content.append(content_wz);
        let wb_bottom = $('<div class="wb-event_weibo--bottom"></div>');
        let btn_list = $('<div class="wb-event_weibo--btn_list"></div>');
        for (let i = 0; i <= 3; i++) {
            let span = $('<span class="btn_list-item"></span>');
            let img = '';
            let a = $('<a></a>');
            switch (i) {
                case 1:
                    img = $('<img src="http://zc.weibo.com/static/imgs/wb-dz.svg" alt="">');
                    a.text(wb_obj.liker);
                    break;
                case 2:
                    img = $('<img src="http://zc.weibo.com/static/imgs/wb-comment.svg" alt="">');
                    a.text(wb_obj.comments);
                    break;
                case 3:
                    img = $('<img src="http://zc.weibo.com/static/imgs/wb-trans.svg" alt="">');
                    a.text(wb_obj.trans);
                    break;
            }
            span.append(img);
            span.append(a);
            btn_list.append(span);
        }
        wb_bottom.append(btn_list);
        event_weibo.append(wb_top);
        event_weibo.append(wb_content);
        event_weibo.append(wb_bottom);
        return event_weibo;
    }

    function append_wbs() {
        let up = wb_load_index + wb_per_load;
        for (; wb_load_index < up && wb_load_index < wb_information_count; wb_load_index++) {
            res_wb_information_list.append(item_weibo(wb_objects[wb_load_index]));
        }
    }

    function load_weibo_objects(objects) {
        $.each(objects, function (i) {
            wb_objects[wb_information_index] = new WbEvent(
                objects[i]['openid'],
                '',
                objects[i]['nickname'],
                objects[i]['content'],
                objects[i]['liker_number'],
                objects[i]['comment_number'],
                objects[i]['trans_number'],
                objects[i]['create_time'],
                objects[i]['last_time'],
            );
            wb_information_index++;
            wb_information_count++;
        })
    }

    function requestWb() {
        if(load_wb_lock) {
            return;
        }
        $.post(BASE_URL+'/wb/query-wei-bo', {offset: wb_load_offset}, function (data, status) {
            if(status != 'success') {
                alert('请检查网络是否连接正常');
            }
            let ret;
            try{
                ret = JSON.parse(data);
                if(ret['code'] != null && ret['code'] == 0) {
                    if(ret['data'].length > 0) {
                        load_weibo_objects(ret['data']);
                        append_wbs();
                    }
                } else {
                    alert(ret['msg']);
                }
            }catch (e) {
                console.log('json 解析失败');
            }
        })
    }

    /*---------------------- 发送微博*/
    function SendWeiBo(content) {
        this.content = content;
    }
    
    function send_weibo(obj) {
        $.post(BASE_URL+'/wb/wei-bo', {obj_wei_bo: obj}, function (data, status) {
            if(status != 'success') {
                alert('请检查网络是否连接正常');
            }
            let ret;
            try{
                ret = JSON.parse(data);
                if(ret['code'] != null && ret['code'] == 0) {
                    window.location.reload();   //临时代替
                } else {
                    alert('发送失败');
                }
            }catch (e) {
                console.log('失败');
            }
        });
    }

    //暂时只做发送文字的
    $('.wb-editor').on('click', '.wb-send_btn', function () {
        if($.trim($('.wb-editor__input').val()) == '') {
            return;
        }
        send_weibo(new SendWeiBo($.trim($('.wb-editor__input').val())));
    })

    /*----------------------------- 生成弹窗*/
    let last_click_icon = null;
    let window_width = 0;
    let user_box_status = false;
    window_width = $(window).width();
    function createUserBox(offsetX, offsetY) {
        let window = $('<div></div>');
        window.addClass('wb-outer_uerBox');
        window.css({"position":"absolute", "left":offsetX+'px', "top":offsetY+'px', "padding":8+'px', "background":"#000"});
        $('body').append(window);
    }

    function clearUserBox() {
        $('.wb-outer_uerBox').remove();
    }

    function reSizeUserBox() {
        if(last_click_icon != null) {
            let left = last_click_icon.offset().left+48;
            let top = last_click_icon.offset().top+48;
            createUserBox(left, top);
        }

    }

    $('#main').on('click', '.wb-user_icon', function () {
        let left = $(this).offset().left+48;
        let top = $(this).offset().top+48;
        clearUserBox();
        createUserBox(left, top);
        last_click_icon = $(this);
        user_box_status = true;
    })

    $('body').on('click', '.wb-outer_uerBox', function () {
        clearUserBox();
        user_box_status = false;
    })

    window.onresize = function () {
        if(!user_box_status) {
            return;
        }
        let wWidth = $(window).width();
        console.log(wWidth, window_width);
        if(window_width != wWidth) {
            clearUserBox();
            reSizeUserBox();
            window_width = wWidth;
        }
    }


})