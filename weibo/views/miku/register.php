<?php
    $this->params['css'] = ['miku_register.css'];
    $this->params['js'] = ['miku_register.js'];
    $this->params['title'] = '用户注册';
?>

<div class="login-window">
    <div class="login-box">
        <header class="login-top">账号</header>
        <div class="login-input_item">
            <img src="<?=WB_URL.'/static/imgs/login-user.png' ?>" alt="">
            <input type="text" class="wb-input wb-user_input wb-input_readonly" value="<?=$mail ?>" readonly>
        </div>
        <div class="login-input_item">
            <img src="<?=WB_URL.'/static/imgs/login-pass.png' ?>" alt="">
            <input type="text" class="wb-input wb-nickname" placeholder="输入昵称">
        </div>
        <div class="login-input_item">
            <img src="<?=WB_URL.'/static/imgs/login-pass.png' ?>" alt="">
            <input type="password" class="wb-input wb-pass_input" placeholder="输入密码">
        </div>
        <div class="login-input_item">
            <img src="<?=WB_URL.'/static/imgs/login-pass.png' ?>" alt="">
            <input type="password" class="wb-input wb-pass_input--confirm" placeholder="确认密码">
        </div>
        <span class="register-btn">注册</span>
    </div>
</div>
