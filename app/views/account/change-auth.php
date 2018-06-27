<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use app\models\ChangeAuth;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">

        <?php
        $form = ActiveForm::begin(['id' => 'auth-form',
                    'enableAjaxValidation' => true,
                    'enableClientValidation' => true,
                    'options' => ['class' => 'form-horizontal'],
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                    ],
        ]);
        ?>
        <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>
        <?php if ($model->type == ChangeAuth::TYPE_EMAIL): ?>
            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        <?php elseif ($model->type == ChangeAuth::TYPE_TEL): ?>
            <?= $form->field($model, 'tel')->textInput(['maxlength' => true]) ?>
        <?php endif; ?>


        <?=
                $form->field($model, 'verifyCode', ['inputTemplate' => "<div class='row'><div class='col-md-6 col-xs-6'>{input}</div><div class='col-md-6 col-xs-6'>" . Html::buttonInput('免费获取验证码', ['class' => 'btn btn-success', 'name' => 'signup-button', 'id' => 'second', 'style' => 'width:100%']) . "</div></div>"
                ])
                ->textInput(['placeholder' => $model->getAttributeLabel('verifyCode'), 'autocomplete' => 'off'])
        ?>



        <div class="col-md-6 col-xs-6 text-right">

            <?= Html::submitButton('确定', ['class' => 'btn btn-primary']) ?>

        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
        </div>


        <?php ActiveForm::end(); ?>


    </div>
</div>
<?php app\assets\AppAsset::addScript($this, '/js/sendcookie.js'); ?> 
<script>
<?php $this->beginBlock('auth') ?>
   $(function () {
        $("#account-modal").off("click",'#second').on('click','#second', function () {
            sendCode($("#second"));
        });
        var v = getCookieValue("secondsremained_login") ? getCookieValue("secondsremained_login") : 0;//获取cookie值  
        if (v > 0) {
            settime($("#second"));//开始倒计时  
        }
    });
    function sendCode(obj) {
        var type = $('#changeauth-type').val();
        var to='';
        if(type==='email'){
            to=$('#changeauth-email').val();
        }else if(type==='tel'){
            to=$('#changeauth-tel').val();
        }
        var result=false;
        if(type==='email'){
            result = isEmail(to);
        }else if(type==='tel'){
            result = isPhone(to);
        }
        if(result){
        $.ajax({
            async: false,
            cache: false,
            type: 'POST',
            url: '<?= Url::toRoute(['common/send-captcha'])?>'+"?to=" + to+"&type="+type, // 请求的action路径
            dataType: "json",
            success: function (data) {
                if (data.stat === 'success') {
                    addCookie("secondsremained_login", 60);//添加cookie记录,有效时间60s  
                    settime(obj);//开始倒计时  
                    $('.field-changeauth-verifycode .help-block').html(data.message);
                } else {
                    $('.field-changeauth-verifycode .help-block').html(data.message);
                    return false;
                }
            }
        });
        }

    }
    
    //校验邮箱是否合法  
function isEmail(email){   
    var myreg = /^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/;  
    if(!myreg.test(email.toLowerCase())){  
//        $('.content').prepend('<div id="w0-error" class="alert-error alert fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-close"></i>请输入有效的电子邮箱！</div>');
        $('.field-changeauth-email').addClass('has-error').find('.help-block').html('请输入有效的电子邮箱！');
        $("#changeauth-email").focus();  
           return false;  
    }else{  
        return true;  
    }  
} 
//校验手机是否合法  
function isPhone(tel){  
    var myreg = /^(1[34578]{1}\d{9})$/;  
    if(!myreg.test(tel.toLowerCase())){  
//        $('.content').prepend('<div id="w0-error" class="alert-error alert fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-close"></i>请输入有效的手机号！</div>');
        $('.field-changeauth-tel').addClass('has-error').find('.help-block').html('请输入有效的手机号！');
        $("#changeauth-tel").focus();  
           return false;  
    }else{  
        return true;  
    }  
}  
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['auth'], \yii\web\View::POS_END); ?>