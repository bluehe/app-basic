<?php

use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\components\CommonHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '注册信息';
$this->params['breadcrumbs'][] = ['label' => '账号信息', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-body">
                <dl class="dl-horizontal">
                    
                    <dt>用户名</dt><dd><span class="dd-c"><?= $model->username ?></span></dd>
                    <dt>登录密码</dt><dd><span class="dd-c">********</span><span class="dd-a"><a class="btn btn-primary btn-xs" href="<?php echo Yii::$app->urlManager->createUrl(['account/change-password']); ?>">修改密码</a></span></dd>                  
                    <dt>用户昵称</dt><dd><span class="nickname dd-c"><?= $model->nickname ?></span><span class="dd-a"><a class="btn btn-primary btn-xs change-nickname" href="javascript:void(0);">修改昵称</a></span></dd>
                    
                    <dt>电子邮箱</dt><dd><span class="dd-c"><?= $model->email?CommonHelper::hideName($model->email):'未设置邮箱'; ?></span><span class="dd-a"><a class="btn btn-primary btn-xs change-email" href="javascript:void(0);">修改邮箱</a></span></dd>
                    <dt>联系电话</dt><dd><span class="dd-c"><?= $model->tel?CommonHelper::hideName($model->tel):'未设置手机号' ?></span><span class="dd-a"><a class="btn btn-primary btn-xs change-tel" href="javascript:void(0);">修改手机</a></span></dd>
                    <dt>注册时间</dt><dd><span class="dd-c"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></span></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'account-modal',
    'header' => '<h4 class="modal-title"></h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('user') ?>
    $('.change-nickname').on('click', function () {
        $.get('<?= Url::toRoute('account/change-nickname') ?>',
                function (data) {
                    $('#account-modal .modal-title').html('修改昵称');
                    $('#account-modal .modal-body').html(data);
                    $('#account-modal').modal('show');
                }
        );
    });
    
    $('.change-email').on('click', function () {
        $.get('<?= Url::toRoute('account/change-auth?type=email') ?>',
                function (data) {
                    $('#account-modal .modal-title').html('修改邮箱');
                    $('#account-modal .modal-body').html(data);
                    $('#account-modal').modal('show');
                }
        );
    });
    
    $('.change-tel').on('click', function () {
        $.get('<?= Url::toRoute('account/change-auth?type=tel') ?>',
                function (data) {
                    $('#account-modal .modal-title').html('修改手机');
                    $('#account-modal .modal-body').html(data);
                    $('#account-modal').modal('show');
                }
        );
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['user'], \yii\web\View::POS_END); ?>
