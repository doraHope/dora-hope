$(function () {

    function nav_click(p, s, pcn = 'active_nav', scn = 'active_nav__title') {
        p.addClass(pcn);
        s.addClass(scn);
    }

    function nav_clear(e, pcn = 'active_nav', scn = 'active_nav__title', sub_scn = 'active_nav_title--sub') {
        console.log(e);
        e.removeClass(pcn);
        e.find('.nav_title__sub').removeClass(scn);
        e.find('.cursor-pointer').removeClass(sub_scn);
        e.find('.nav_sub_options').slideUp();
    }

    /*----------------- 公共动态样式设计*/

    /*-------------- 导航栏样式设计*/
    $('.nav_sub_options').on('mouseenter', '.cursor-pointer', function () {
        $(this).parent().find('.cursor-pointer').addClass('option-null');
        $(this).parent().find('.cursor-pointer').removeClass('option-click');
        $(this).addClass('option-click');
    })

    $('.nav_sub_options').on('mouseleave', '.cursor-pointer', function () {
        $(this).parent().find('.cursor-pointer').removeClass('option-null');
        $(this).parent().find('.cursor-pointer').removeClass('option-click');
    })

    $('.nav_options').on('click', '.nav_title__sub', function () {
        nav_clear($('.nav_options--li'));
        nav_click($(this).parent(), $(this));
        $(this).next().slideDown();
    })




})