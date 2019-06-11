$(function () {
    const BASE_URL = 'http://zc.weibo.com/index.php';
    function clear_error(element) {
        element.removeClass('input_error');
        element.val('');
        element.css('border', '1px solid #ccc');
    }

    let re_register_lock = false;
    function ajax_register(passwd) {
        if(re_register_lock) {
            return;
        }
        re_register_lock = true;
        $.post(BASE_URL+'/action/register-login', {password: passwd}, function (data, status) {

        })
    }
    
    function input_error(element, msg) {
        element.addClass('input_error');
        element.val(msg);
        element.css('border', '1px solid rgba(244,89,72,0.75)');
    }

    function password_strong(password) {
        if(password.length < 6 || password.length > 16) {
            alert('请输入密码长度在 6~16 之间');
            return false;
        }
        grep_char = /[a-zA-Z]+/;
        grep_number = /[0-9]+/;
        if(!grep_number.test(password) || !grep_char.test(password)) {
            alert('请输入字母+数字且长度在6~16之间的字符串!');
            return false;
        }
        return true;
    }

    $('.login-window').on('click', '.register-btn', function () {
        let pass = $.trim($('.wb-pass_input').val());
        let re_pass = $.trim($('.wb-pass_input--confirm').val());
        if(pass == '' || pass == undefined) {
            input_error($('.wb-pass_input'), '');
        } else if(re_pass == '' || re_pass == undefined) {
            input_error($('.wb-pass_input--confirm'), '');
        }
        if(!password_strong(pass)) {
            return;
        }
        if(pass != re_pass) {
            alert('两次密码输入不一样');
            return;
        }
        ajax_register(pass);
    })

    $('.login-window').on('click', '.input_error', function () {
        clear_error($(this));
    })

})