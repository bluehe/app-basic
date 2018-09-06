<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use app\models\System;
use yii\bootstrap\Modal;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = '完善信息';

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-user form-control-feedback'></span>"
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>"
];
$fieldOptions3 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];
$fieldOptions4 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-phone form-control-feedback'></span>"
];
?>

<div class="login-box">
    <div class="login-logo">
        <?=
        Html::a('<b>' . Yii::$app->name . '</b>', Yii::$app->homeUrl)
        ?>

    </div>
    <!-- /.login-logo -->
    <div class="nav-tabs-custom complete">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">绑定已有帐号</a></li>
            <li><a href="#tab_2" data-toggle="tab">创建帐号并绑定</a></li>


        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
                <div class="login-box-body">


                    <?php $form1 = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => true]); ?>

                    <?=
                            $form1
                            ->field($model_l, 'username', $fieldOptions1)
                            ->label(false)
                            ->textInput(['placeholder' => '用户名/手机号/电子邮件'])
                    ?>

                    <?=
                            $form1
                            ->field($model_l, 'password', $fieldOptions3)
                            ->label(false)
                            ->passwordInput(['placeholder' => $model_l->getAttributeLabel('password')])
                    ?>
                    <?php if ($model_l->scenario == 'captchaRequired'): ?>
                        <?=
                        $form1->field($model_l, 'verifyCode')->widget(Captcha::className(), [
                            'template' => '<div class="row"><div class="col-xs-8">{input}</div><div class="col-xs-4">{image}</div></div>',
                            'options' => ['placeholder' => $model_l->getAttributeLabel('verifyCode'), 'class' => 'form-control', 'autoCompete' => false],
                            'imageOptions' => ['alt' => '点击换图', 'title' => '点击换图', 'style' => 'cursor:pointer', 'height' => 34]])->label(false)
                        ?>
                    <?php endif; ?>
                    <div class="row">

                        <!-- /.col -->
                        <div class="col-xs-12">
                            <?= Html::submitButton('绑 定', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'type', 'value' => 'bind']) ?>
                        </div>
                        <!-- /.col -->
                    </div>


                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <!-- /.tab-pane -->
            <div class="tab-pane" id="tab_2">
                <div class="login-box-body">


                    <?php $form = ActiveForm::begin(['id' => 'signup-form', 'enableAjaxValidation' => true, 'enableClientValidation' => true]); ?>

                    <?=
                            $form
                            ->field($model_s, 'username', $fieldOptions1)
                            ->label(false)
                            ->textInput(['placeholder' => $model_s->getAttributeLabel('username')])
                    ?>


                    <?=
                            $form
                            ->field($model_s, 'password', $fieldOptions3)
                            ->label(false)
                            ->passwordInput(['placeholder' => $model_s->getAttributeLabel('password')])
                    ?>
                    <?=
                            $form
                            ->field($model_s, 'password1', $fieldOptions3)
                            ->label(false)
                            ->passwordInput(['placeholder' => $model_s->getAttributeLabel('password1')])
                    ?>

                    
                    <?php if ($model_s->scenario == 'captchaRequired'): ?>
                        <?=
                        $form->field($model_s, 'verifyCode')->widget(Captcha::className(), [
                            'template' => '<div class="row"><div class="col-xs-8">{input}</div><div class="col-xs-4">{image}</div></div>',
                            'options' => ['placeholder' => $model_s->getAttributeLabel('verifyCode'), 'class' => 'form-control','autocomplete'=>'off'],
                            'imageOptions' => ['alt' => '点击换图', 'title' => '点击换图', 'style' => 'cursor:pointer', 'height' => 34]])->label(false)
                        ?>
                    <?php endif; ?>
                    <div class="text-center sign-icon">
                        <p>登录或找回密码使用(非必填)</p>
                    </div>
                    <?=
                            $form
                            ->field($model_s, 'email', $fieldOptions2)
                            ->label(false)
                            ->textInput(['placeholder' => $model_s->getAttributeLabel('email')])
                    ?>
                    <?php if (System::getValue('sms_service')): ?>
                    <?=
                            $form
                            ->field($model_s, 'tel', $fieldOptions4)
                            ->label(false)
                            ->textInput(['placeholder' => $model_s->getAttributeLabel('tel')])
                    ?>
                    <?php endif; ?>
                    <?php if (System::getValue('agreement_open')): ?>
                    <?= $form->field($model_s, 'agreement')->checkbox()->label($model_s->getAttributeLabel('agreement')) ?>
                    <?php
Modal::begin([
    'id' => 'agreement-modal',
    'header' => '<h4 class="modal-title"></h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('agreement') ?>
    
    $('.agreement').on('click', function () {
        var code =$(this).data('code');
        var title=$(this).html();
        $.get('<?= Url::toRoute(['site/agreement']) ?>?code='+code,
                function (data) {
                    $('#agreement-modal .modal-title').html(title);
                    $('#agreement-modal .modal-body').html(data);
                    $('#agreement-modal').modal('show');
                }
        );
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['agreement'], \yii\web\View::POS_END); ?>
                    <?php endif; ?>
                    <div class="row">

                        <div class="col-xs-12">
                            <?= Html::submitButton('创建帐号并绑定', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'type', 'value' => 'new']) ?>
                        </div>
                        <!-- /.col -->
                    </div>


                    <?php ActiveForm::end(); ?>
                    <!--
                            <div class="social-auth-links text-center">
                                <p>- OR -</p>
                                <a href="#" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> Sign in
                                    using Facebook</a>
                                <a href="#" class="btn btn-block btn-social btn-google-plus btn-flat"><i class="fa fa-google-plus"></i> Sign
                                    in using Google+</a>
                            </div>-->
                    <!-- /.social-auth-links -->



                </div>
            </div>
            <!-- /.tab-pane -->

        </div>
        <!-- /.tab-content -->
    </div>
    <!-- /.login-box-body -->
</div><!-- /.login-box -->
<script>
<?php $this->beginBlock('captcha') ?>
    $(document).ready(function () {
        changeVerifyCode();
    });
//更改或者重新加载验证码
    function changeVerifyCode() {
        $.ajax({
            url: "/site/captcha?refresh",
            dataType: "json",
            cache: false,
            success: function (data) {
//                $("#imgVerifyCode").attr("src", data["url"]);
            }
        });
    }
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['captcha'], \yii\web\View::POS_END); ?>