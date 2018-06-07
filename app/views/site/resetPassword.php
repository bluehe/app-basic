<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = '重置密码';

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
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

        <?php $form = ActiveForm::begin(['id' => 'passwordreset-form', 'enableClientValidation' => true]); ?>
        <?=
                $form
                ->field($model, 'password', $fieldOptions2)
                ->label(false)
                ->passwordInput(['placeholder' => $model->getAttributeLabel('新密码')])
        ?>

        <div class="row">
            <div class="col-xs-8">
                <?=
                Html::a('立即登录', ['/site/login'])
                ?>
            </div>
            <div class="col-xs-4">
                <?= Html::submitButton('保 存', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
            </div>
            <!-- /.col -->
        </div>


        <?php ActiveForm::end(); ?>




    </div>
    <!-- /.login-box-body -->
</div><!-- /.login-box -->
