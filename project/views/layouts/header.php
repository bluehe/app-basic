<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini">' . Yii::$app->params['shortname'] . '</span><span class="logo-lg">' . Yii::$app->name . '</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">切换导航</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">
                <!-- User Account: style can be found in dropdown.less -->

                <li class="dropdown hidden-xs">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="padding:10px 15px ">
                        <i class="fa fa-mobile fa-2x"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header"><img src="<?= Url::to(['common/qrcode']) ?>"/></li>
                        <li class="user-footer text-center"><span style="font-size: 12px;">扫一扫，直接在手机上打开</span></li>
                    </ul>
                </li>
                
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= $directoryAsset ?><?= Yii::$app->user->identity->avatar ? Yii::$app->user->identity->avatar : '/image/user.png' ?>" class="user-image" alt="User Image"/>
                        <span class="hidden-xs"><?= Yii::$app->user->identity->username ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?= $directoryAsset ?><?= Yii::$app->user->identity->avatar ? Yii::$app->user->identity->avatar : '/image/user.png' ?>" class="img-circle" alt="User Image"/>

                            <p>
                                <?= Yii::$app->user->identity->username ?>
                                <small>注册时间 <?= date('Y-m-d', Yii::$app->user->identity->created_at) ?></small>
                            </p>
                        </li>

                        <li class="user-footer">
                            <!--                            <div class="pull-left">
                                                            <a href="#" class="btn btn-default btn-flat">Profile</a>
                                                        </div>-->
                            <div class="text-center">
                                <?=
                                Html::a(
                                        '退 出', ['/site/logout'], ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                )
                                ?>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
