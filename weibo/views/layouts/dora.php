<! DOCTYPE HTML>
<?php
    $this->params['common'] = '@app/views/common/com_';
    $actions = WeiBoConfig::$CONTROL_ACTION;
?>
<?php $this->beginPage() ?>
<html>
    <head>
        <title><?=$this->params['title'] ?></title>
        <link rel="icon" href="<?=WB_URL.'/static/icon.png' ?>" type="image/png">
        <?php $this->beginContent($this->params['common'].'header.php') ?>
        <?php $this->endContent() ?>
        <?php foreach ($this->params['css'] as $item) : ?>
            <link rel="stylesheet" href="<?=WB_URL.'/static/css/'.$item ?>">
        <?php endforeach; ?>
    </head>
    <?php $this->beginBody() ?>
    <body>
    <div id="meng">
        <div class="tip">

        </div>
    </div>
    <div id="main">
        <div class="header com-box_shadow">
            
        </div>
        <div class="body">
            <div class="left_nav">
                <header class="nav_title com-box_shadow">
                    任务管理
                </header>
                <ul class="nav_options">
                    <?php foreach ($actions as $nav_name => $options): ?>
                    <li class="nav_options--li <?=$this->params['default_nav'] === $options['default'] ? 'active_nav' : ''?>" data-url="<?=$options['default'] ?>">
                        <header class="nav_title__sub flex-row__h--center cursor-pointer <?=$this->params['default_nav'] === $options['default'] ? 'active_nav__title' : ''?>">
                            <?=$nav_name ?>
                        </header>
                        <ul class="nav_sub_options <?=$this->params['default_nav'] === $options['default'] ? 'active_options' : '' ?>">
                            <?php foreach ($options['ca'] as $sub_nav_name => $action): ?>
                            <?php if($this->params['default_nav'] !== $options['default']): ?>
                                <li class="cursor-pointer <?=$action === $options['default'] ? 'default-clicked' : '' ?>" data-url="<?=$action ?>"><?=$sub_nav_name ?></li>
                            <?php else: ?>
                                <li class="cursor-pointer <?=$action === $this->params['select_nav'] ? 'default-clicked' : '' ?>" data-url="<?=$action ?>"><?=$sub_nav_name ?></li>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endforeach; ?>
<!--                    <li class="nav_options--li">-->
<!--                        <header class="nav_title__sub flex-row__h--center cursor-pointer">用户管理</header>-->
<!--                        <ul class="nav_sub_options">-->
<!--                            <li class="cursor-pointer default-clicked">用户流量分析</li>-->
<!--                            <li class="cursor-pointer">用户身份管理</li>-->
<!--                        </ul>-->
<!--                    </li>-->
<!--                    <li class="nav_options--li">-->
<!--                        <header class="nav_title__sub flex-row__h--center cursor-pointer">微博管理</header>-->
<!--                        <ul class="nav_sub_options">-->
<!--                            <li class="cursor-pointer default-clicked">消息管理</li>-->
<!--                        </ul>-->
<!--                    </li>-->
                </ul>
            </div>
            <div class="page">
                <?=$content ?>
            </div>
        </div>
        <div class="footer">
            <header>&copy; dora_m hope for you!</header>
        </div>
    </div>
    </body>
    <?php $this->beginContent($this->params['common'].'footer.php') ?>
    <?php $this->endContent() ?>
    <?php $this->endBody() ?>
</html>
<?php $this->endPage() ?>