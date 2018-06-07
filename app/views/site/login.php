<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */
$this->title = '登录';
$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-user form-control-feedback'></span>"
];
$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];
?>
<div id="particles" style="width: 100%;height: 100%;position: absolute;left: 0;top: 0;z-index:-1"></div>
<div class="login-box">
    <div class="login-logo">
        <?=
        Html::a('<b>' . Yii::$app->name . '</b>', Yii::$app->homeUrl)
        ?>

    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">欢迎登录</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => true]); ?>

        <?=
                $form
                ->field($model, 'username', $fieldOptions1)
                ->label(false)
                ->textInput(['placeholder' => '用户名/手机号/电子邮件'])
        ?>

        <?=
                $form
                ->field($model, 'password', $fieldOptions2)
                ->label(false)
                ->passwordInput(['placeholder' => $model->getAttributeLabel('password')])
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
                <?= $form->field($model, 'rememberMe')->checkbox()->label($model->getAttributeLabel('rememberMe')) ?>
            </div>
            <!-- /.col -->
            <div class="col-xs-4">
                <?= Html::submitButton('登 录', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
            </div>
            <!-- /.col -->
        </div>


        <?php ActiveForm::end(); ?>
        <?=
        Html::a('忘记密码', ['/site/request-password-reset'])
        ?>
        <?=
        Html::a('注册新账号', ['/site/signup'], ['class' => 'pull-right'])
        ?>
        <div class="social-auth-links text-center social-icon">
            <p>第三方账号登录</p>
            <?=
            yii\authclient\widgets\AuthChoice::widget(['baseAuthUrl' => ['site/auth'], 'popupMode' => false,])
            ?>
        </div>
    </div>
    <!-- /.login-box-body -->
</div><!-- /.login-box -->
<script>
<?php $this->beginBlock('login') ?>
 $('#particles').particleground({
    dotColor: 'rgba(20,140,230,0.15)',
    lineColor: 'rgba(85,175,230,0.15)'
  });
<?php $this->endBlock() ?>
</script>
<?php app\assets\AppAsset::addScript($this, '/js/jquery.particleground.min.js'); ?>
<?php $this->registerJs($this->blocks['login'], \yii\web\View::POS_END); ?>