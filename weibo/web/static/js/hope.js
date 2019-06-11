$(function(){


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


})