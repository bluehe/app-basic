<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\UserLevel;
?>

<?php
if (Yii::$app->controller->action->id == 'people' || Yii::$app->controller->action->id == 'follow' || !Yii::$app->user->isGuest) {
    if (Yii::$app->controller->action->id == 'people' || Yii::$app->controller->action->id == 'follow') {
        $user_id = Yii::$app->request->get('id');
    } else {
        $user_id = Yii::$app->user->identity->id;
    }
    $level = UserLevel::get_user_level($user_id);
    ?>
    <div class="user-head">
        <div class="person_info">
                <div class="cover" style="background-image:url(<?= Url::to('@web/image/user_cover_m.jpg') ?>)"></div>
                <div class="innerwrap">
                <div class="profile">
                    <div class="headpic">
                        <?= Html::img(User::get_avatar($user_id), ['class' => 'img-thumbnail', 'width' => 70, 'height' => 70]) ?>
                        <?= Yii::$app->controller->action->id == 'people' ? '' : Html::a('<div class="mask"><div class="Mask-mask Mask-mask--black UserAvatarEditor-maskInner"></div><div class="Mask-content"><i class="glyphicon glyphicon-camera"></i><div class="UserAvatarEditor-maskInnerText">修改头像</div></div></div>', ['account/thumb']) ?>

                    </div>
                    <div class="nameBox">
                        <div class="title">
                            <span class="name txt1"><?= User::get_nickname($user_id) ?></span>
                        </div>
                        <span>
                            <a title="等级" href="#"><span class="badge icon_level_c<?= ceil($level / Yii::$app->params['level_c']) ?>">Lv.<?= $level ?></span></a>
    <!--                            <a title="会员" href="#"><i class="W_icon icon_member"></i></a>-->
                        </span>
                        <div class="profile-contentFooter">
                            <div class="profileHeader-buttons">
                                <?php
                                if ((Yii::$app->controller->action->id == 'people' || Yii::$app->controller->action->id == 'follow') && (Yii::$app->user->isGuest || Yii::$app->user->identity->id != $user_id)) {
                                        echo Yii::$app->user->isGuest || !UserAtten::is_atten(Yii::$app->user->identity->id, $user_id) ? Html::tag('button', '<i class="fa fa-plus"></i> 关注', ['class' => 'btn btn-xs btn-primary user-follow', 'data-id' => $user_id]) : Html::tag('button', '已关注', ['class' => 'btn btn-xs btn-default user-unfollow', 'data-id' => $user_id]);
                                    // echo Html::a('<i class="fa fa-commenting-o"></i> 私信', ['#'], ['class' => 'btn btn-xs btn-default']);
                                } else {
                                    echo UserSign::exist_sign(Yii::$app->user->identity->id) ? Html::tag('button', '已签到', ['class' => 'btn btn-xs btn-default', 'disabled' => 'disabled']) : Html::tag('button', '<i class="fa fa-edit"></i>签到', ['class' => 'btn btn-xs btn-primary user-sign']);
                                    //echo Html::a('编辑', ['account/index'], ['class' => 'btn btn-xs btn-default btn-edit']);
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <ul class="user_atten">
                        <li class="line1 col-lg-4 col-xs-4"><?= Html::a('<strong data-type="follow">' . UserAtten::get_num($user_id) . '</strong><span class="txt2">关注了</span>', ['site/follow', 'id' => $user_id], ['class' => 'txt1']) ?></li>
                            <li class="line1 col-lg-4 col-xs-4"><?= Html::a('<strong data-type="fans">' . UserAtten::get_num($user_id, 'fans') . '</strong><span class="txt2">关注者</span>', ['site/follow', 'id' => $user_id, 'type' => 'fans'], ['class' => 'txt1']) ?></li>
                            <li class="line1 col-lg-4 col-xs-4"><?= Html::a('<strong>' . Website::get_website_num($user_id, '', Website::STAT_OPEN) . '</strong><span class="txt2">网址</span>', ['site/people', 'id' => $user_id], ['class' => 'txt1']) ?></li>
                    </ul>
            </div>
        </div>
    </div>
    <?php
}
?>

<div class="mk hidden-xs<?= Yii::$app->controller->action->id == 'people' || Yii::$app->controller->action->id == 'follow' || !Yii::$app->user->isGuest ? ' user' : '' ?>">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="col-lg-4 col-md-4 active"><a href="#useradd" aria-controls="useradd" role="tab" data-toggle="tab">新用户</a></li>
        <li role="presentation"  class="col-lg-4 col-md-4"><a href="#userfans" aria-controls="userfans" role="tab" data-toggle="tab">关注排行</a></li>
        <li role="presentation"  class="col-lg-4 col-md-4"><a href="#userlevel" aria-controls="userclick" role="tab" data-toggle="tab">等级排行</a></li>

    </ul>

    <!-- Tab panes -->
    <div class="tab-content" id="userscroll">
        <div role="tabpanel" class="tab-pane box active" id="useradd">
            <div class="list-group list">
                <?=
                Tab::widget(['items' => User::get_tab_useradd(20)]
                )
                ?>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="userfans">
            <div class="list-group">
                <?=
                Tab::widget(['items' => UserAtten::get_tab_userfans(10)]
                )
                ?>

            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="userlevel">
            <div class="list-group">
                <?=
                Tab::widget(['items' => UserLevel::get_tab_userlevel(10)]
                )
                ?>

            </div>
        </div>

    </div>

</div>

<div class="mk hidden-xs">

    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="col-lg-4 col-md-4 active"><a href="#addlist" aria-controls="addlist" role="tab" data-toggle="tab">网址动态</a></li>
        <li role="presentation"  class="col-lg-4 col-md-4"><a href="#addorder" aria-controls="addorder" role="tab" data-toggle="tab">添加排行</a></li>
        <li role="presentation"  class="col-lg-4 col-md-4"><a href="#clickorder" aria-controls="clickorder" role="tab" data-toggle="tab">点击排行</a></li>

    </ul>

    <!-- Tab panes -->
    <div class="tab-content" id="cxscroll">
        <div role="tabpanel" class="tab-pane box active" id="addlist">
            <div class="list-group list">
                <?=
                Tab::widget(['items' => Website::get_tab_addlist(20)]
                )
                ?>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="addorder">
            <div class="list-group">
                <?=
                Tab::widget(['items' => Website::get_tab_addorder(10)]
                )
                ?>

            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="clickorder">
            <div class="list-group">
                <?=
                Tab::widget(['items' => Website::get_tab_clickorder(10)]
                )
                ?>

            </div>
        </div>

    </div>

</div>
<?php dh\assets\AppAsset::addScript($this,'/js/jquery.cxscroll.min.js') ?>
<script>
<?php $this->beginBlock('left') ?>
    $(function () {
        $("#userscroll").cxScroll({direction: "bottom", speed: 1000, time: 2000});
        $("#cxscroll").cxScroll({direction: "bottom", speed: 1000, time: 2000});
    });
    //用户签到
    $('.user-sign').on('click', function () {
        $.getJSON("<?= Url::toRoute('ajax/user-sign') ?>", function (data) {
            if (data.stat === 'success') {
                $('.user-sign').addClass('btn-default').attr('disabled', 'disabled').html('已签到').removeClass('btn-primary user-sign');
                my_alert('success', data.msg, 3000);
            } else if (data.stat === 'fail') {
                my_alert('danger', data.msg, 3000);
            }
        });

    });
    //用户取消关注
    $('.profileHeader-buttons').on("mouseover", '.user-unfollow', function () {
        $(this).html('取消关注');
    }).on("mouseout", '.user-unfollow', function () {
        $(this).html('已关注');
    });
    $('.profileHeader-buttons').on('click', '.user-unfollow', function () {
        var _this=$(this);
        $.getJSON("<?= Url::toRoute('ajax/user-unfollow') ?>", {user_id: _this.data('id')}, function (data) {
            if (data.stat === 'success') {
                _this.addClass('btn-primary user-follow').html('<i class="fa fa-plus"></i> 关注').removeClass('btn-default user-unfollow');
                $('.user_atten li strong[data-type=fans]').html(parseInt($('.user_atten li strong[data-type=fans]').html()) - 1);
                my_alert('success', data.msg, 3000);
            } else if (data.stat === 'fail') {
                my_alert('danger', data.msg, 3000);
            }
        });

    });
    $('.profileHeader-buttons').on('click', '.user-follow', function () {
    var _this=$(this);
        $.getJSON("<?= Url::toRoute('ajax/user-follow') ?>", {user_id: _this.data('id')}, function (data) {
            if (data.stat === 'success') {
                _this.addClass('btn-default user-unfollow').html('已关注').removeClass('btn-primary user-follow');
                $('.user_atten li strong[data-type=fans]').html(parseInt($('.user_atten li strong[data-type=fans]').html()) + 1);
                my_alert('success', data.msg, 3000);
            } else if (data.stat === 'fail') {
                my_alert('danger', data.msg, 3000);
            }
        });

    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['left'], \yii\web\View::POS_END); ?>
