<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

$this->title = '忘记密码';

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>"
];
?>

<div class="login-box">
    <div class="login-logo">
        <?=
        Html::a('<b>' . Yii::$app->name . '</b>', Yii::$app->homeUrl)
        ?>

    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">重置密码</p>

        <?php $form = ActiveForm::begin(['id' => 'passwordreset-form', 'enableAjaxValidation' => true, 'enableClientValidation' => true]); ?>
        <?=
                $form
                ->field($model, 'email', $fieldOptions1)
                ->label(false)
                ->textInput(['placeholder' => $model->getAttributeLabel('email')])
        ?>
        <?php if ($model->scenario == 'captchaRequired'): ?>
            <?=
            $form->field($model, 'verifyCode')->widget(Captcha::className(), [
                'template' => '<div class="row"><div class="col-xs-8">{input}</div><div class="col-xs-4">{image}</div></div>',
                'options' => ['placeholder' => $model->getAttributeLabel('verifyCode'), 'class' => 'form-control', 'autoCompete' => false],
                'imageOptions' => ['alt' => '点击换图', 'title' => '点击换图', 'style' => 'cursor:pointer', 'height' => 34]])->label(false)
            ?>
        <?php endif; ?>
        <div class="row">
            <div class="col-xs-8">
                <?=
                Html::a('立即登录', ['/site/login'])
                ?>
            </div>
            <div class="col-xs-4">
                <?= Html::submitButton('发送邮件', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
            </div>
            <!-- /.col -->
        </div>


        <?php ActiveForm::end(); ?>




    </div>
    <!-- /.login-box-body -->
</div><!-- /.login-box -->
