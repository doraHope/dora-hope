<?php
$this->params['common'] = '@app/views/common/com_';
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>hope--我愿将我的所有都给你</title>
    <link rel="stylesheet" href="<?=WB_URL.'/static/css/hope.css' ?>">
    <?php foreach ($this->params['css'] as $css): ?>
        <link rel="stylesheet" href="<?=WB_URL.'/static/css/'.$css?>">
    <?php endforeach; ?>
</head>
<body>
<div id="main" class="flex-column__v--center">
    <div class="top flex-row__hv-center">
        <div class="top-content flex-row__hv-center">
            <div class="hope-icon">
                <img src="<?=WB_URL.'/static/imgs/hope-icon.png' ?>">
            </div>
            <div class="search-bar">
                <input type="text" id="search-bar" name="search_content" placeholder="hope for you">
                <img src="<?=WB_URL.'/static/imgs/search_icon.png' ?>" class="search_bar--icon">
            </div>
            <div class="options-bar">
                <div class="options-list">
                    <div class="option-item" data-url="<?=WB_URL.'/static/imgs/' ?>home_light.png" data-href="home/index">
                        <img class="options-item_img" src="<?=WB_URL.'/static/imgs/home.png' ?>">
                        <a class="options-item_link">首页</a>
                    </div>
                    <div class="option-item" data-url="<?=WB_URL.'/static/imgs/' ?>video_light.png" data-href="home/video">
                        <img class="options-item_img" src="<?=WB_URL.'/static/imgs/video.png' ?>">
                        <a class="options-item_link">视频</a>
                    </div>
                    <div class="option-item" data-url="<?=WB_URL.'/static/imgs/' ?>scan_light.png" data-href="home/scan">
                        <img class="options-item_img" src="<?=WB_URL.'/static/imgs/scan.png' ?>">
                        <a class="options-item_link">发现</a>
                    </div>
                    <div class="option-item" data-url="<?=WB_URL.'/static/imgs/' ?>game_light.png" data-href="home/game">
                        <img class="options-item_img" src="<?=WB_URL.'/static/imgs/game.png' ?>">
                        <a class="options-item_link">游戏</a>
                    </div>
                </div>
                <div class="login-bar">
                    <a class="register">注册</a>
                    <a class="login">登陆</a>
                </div>

            </div>
        </div>
    </div>
    <div class="body">
        <?= $content?>
    </div>
    <div class="footer">
        <header>&copy; dora_m hope for you!</header>
    </div>
</div>
<script src="<?=WB_URL.'/static/js/jquery.min.js' ?>"></script>
<script src="<?=WB_URL.'/static/js/hope.js' ?>"></script>
</body>
</html>
