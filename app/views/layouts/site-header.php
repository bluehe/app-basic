<?php

use yii\bootstrap\Nav;
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\Alert;
use app\models\Recommend;

/* @var $this \yii\web\View */
/* @var $content string */
$menuItems = [
    ['label' => '共享网址', 'url' => ['page/all']],
    ['label' => '我的网址', 'url' => ['page/user']],
];
$recommend = Recommend::get_recommend(Recommend::STAT_OPEN, 12);
?>

<header>
    <?= \dmstr\widgets\Alert::widget()?>
    <div class="main-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-xs-12 pull-left">
                    <a href="javascript:void(0);" class="change-skin" data-skin="<?= Yii::$app->params['skin'] ?>"><i class="fa fa-exchange"></i> 更换皮肤</a>
                    <a href="javascript:void(0);" class="change-plate" data-plate="<?= Yii::$app->params['plate'] ?>"><i class="fa fa-refresh"></i> 切换板式</a>
                    <?=
                    Html::a('<i class="fa fa-info-circle"></i> 新手帮助', ['/page/help'])
                    ?>
                </div>
                <!--右侧功能块-->
                <div class="col-lg-6 col-xs-12">

                    <?php if (Yii::$app->user->isGuest) { ?>
                        <div class="pull-right">
                            <?=
                            Html::a('<i class="fa fa-sign-in"></i> 登录', ['/site/login'])
                            ?>
                            <?=
                            Html::a('<i class="fa fa-pencil"></i> 注册', ['/site/signup'])
                            ?>
                        </div>
                    <?php } else { ?>

                        <div class="user-menu pull-right">
                            <a href="javascript:void(0);">
                                <img src="<?= Yii::$app->user->identity->avatar ? Yii::$app->user->identity->avatar : $directoryAsset . '/image/user.png' ?>" class="user-image" />
                                <span class="name"><?= Yii::$app->user->identity->nickname ? Yii::$app->user->identity->nickname : Yii::$app->user->identity->username ?></span>

                            </a>
                            <div class="myhome">
                                <?= Html::a('<i class="fa fa-home"></i><span>个人中心</span>', ['person/index']) ?>
                                <?= Html::a('<i class="fa fa-sign-out"></i><span>退出</span>', ['site/logout'], ['data-method' => 'post']) ?>
                            </div>
                        </div>

                    <?php } ?>


                </div>
            </div>
            <nav class="navbar navbar-inverse visible-xs navbar-fixed-top">
                <div class="container-fluid">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                            <span class="sr-only">导航切换</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>

                    </div>

                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <?php
                        echo Nav::widget([
                            'options' => ['class' => 'navbar-nav nav'],
                            'items' => $menuItems,
                        ]);
                        ?>


                    </div><!-- /.navbar-collapse -->
                </div>
            </nav>
        </div>
    </div>
    <div class="nav-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-xs-12 logo">
                    <a href="<?= Yii::$app->homeUrl ?>">
                        <img src="<?= $directoryAsset ?>/image/logo.png" alt="<?= Yii::$app->name ?>" class="img-responsive center-block"/>
                    </a>
                </div>

                <div class="col-lg-4 col-lg-push-6 col-xs-12">
                    <form class="search" action="https://www.baidu.com/s" method="get" target="_blank" accept-charset="utf-8" id="searchform">
                        <span><img src="<?= $directoryAsset ?>/image/baidu.png"></span>
                        <input type="text" name="wd" placeholder="请输入您要搜索的内容..." autocomplete="off">
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
                <?php
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav col-lg-6 col-lg-pull-4 hidden-xs'],
                    'items' => $menuItems,
                ]);
                ?>


            </div>
        </div>
    </div>
    <div class="content-wrapper">
        <?= Alert::widget() ?>

        <?php
        if (count($recommend) > 0) {
            ?>

            <div class="container recommend">
                <?php foreach ($recommend as $r) { ?>
                    <div class="col-lg-1 col-xs-2 text-center">
                        <?= Html::a(Html::img($r['img'], ['class' => 'img-rounded img-responsive center-block', 'width' => 65, 'height' => 65]) . '<span class="hidden-xs">' . $r['name'] . '</span>', $r['url'], ['data-id' => $r['id'], 'class' => 'clickurl', 'target' => '_blank']) ?>

                    </div>
                    <?php
                }
                ?>
            </div>

        <?php } ?>
    </div>
</header>
<script>
<?php $this->beginBlock('header') ?>
    $('.change-skin').on('click', function () {
        var skin = $(this).data('skin');
        $.getJSON({
            url: '<?= Url::toRoute('ajax/change-skin') ?>',
            data: {'id': skin},
            success: function (data) {

                if (data.stat === 'success') {
                    $('body').removeClass('skin-' + skin).addClass('skin-' + data.skin);
                    $('.change-skin').data('skin', data.skin);
                }

            }
        });
    });
    $('.change-plate').on('click', function () {
        var plate = $(this).data('plate');
        $.getJSON({
            url: '<?= Url::toRoute('ajax/change-plate') ?>',
            data: {'id': plate},
            success: function (data) {

                if (data.stat === 'success') {
                    $(".website").removeClass('plate-' + plate).addClass('plate-' + data.plate);
                    $('.change-plate').data('plate', data.plate);
                }

            }
        });
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['header'], \yii\web\View::POS_END); ?>