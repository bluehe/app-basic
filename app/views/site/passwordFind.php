<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\models\System;

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
        <p class="login-box-msg text-red"><?= $model->getName($model->type) ?></p>

        <?php $form = ActiveForm::begin(['id' => 'passwordreset-form','enableAjaxValidation' => true, 'enableClientValidation' => true]); ?>
        
        <?=
                $form->field($model, 'type')->label(false)->hiddenInput();
        ?>
        <?=
                $form
                ->field($model, 'password', $fieldOptions2)
                ->label(false)
                ->passwordInput(['placeholder' => $model->getAttributeLabel('password')])
        ?>
        
        <?=
                $form
                ->field($model, 'password1', $fieldOptions2)
                ->label(false)
                ->passwordInput(['placeholder' => $model->getAttributeLabel('password1')])
        ?>
        <div class="row">
            <div class="col-xs-6">
        <?=
            $form->field($model, 'verifyCode', [
                'options' => ['class' => 'form-group has-feedback'],
                
                ])
                ->label(false)
                ->textInput(['placeholder' => $model->getAttributeLabel('verifyCode')])
            ?>
            </div>
            <div class="col-xs-6">
            <?= Html::buttonInput('免费获取验证码', ['class' => 'btn btn-success', 'name' => 'signup-button', 'id' => 'second','style'=>'width:100%']) ?> 
            </div>
        </div>
        <div class="row">
            <div class="col-xs-8">
                <?php
                if($model->type=='email'){
                    echo System::getValue('sms_service')?Html::a('通过手机找回', ['/site/password-find','type'=>'tel']):'';
                }elseif($model->type=='tel'){
                    echo Html::a('通过邮箱找回', ['/site/password-find','type'=>'email']);
                }
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
<script>
<?php $this->beginBlock('password') ?>
function addCookie(name,value,expiresHours){      
    var cookieString=name+"="+escape(value);      
    //判断是否设置过期时间,0代表关闭浏览器时失效  
    if(expiresHours>0){  
        var date=new Date();  
        date.setTime(date.getTime()+expiresHours*1000);  
        cookieString=cookieString+";expires=" + date.toUTCString();  
    }
    document.cookie=cookieString;  
}
function editCookie(name,value,expiresHours){      
    var cookieString=name+"="+escape(value);   
    if(expiresHours>0){  
    var date=new Date();  
    date.setTime(date.getTime()+expiresHours*1000); //单位毫秒  
        cookieString=cookieString+";expires=" + date.toGMTString();  
    }
    document.cookie=cookieString;  
}
function getCookieValue(name){  
    var strCookie=document.cookie;  
    var arrCookie=strCookie.split("; ");  
    for(var i=0;i<arrCookie.length;i++){  
        var arr=arrCookie[i].split("=");  
          if(arr[0]==name){   
             return unescape(arr[1]);  
           break;  
        }  
    }   
}
$(function(){  
    $("#second").on('click',function (){  
        sendCode($("#second"));  
    });  
    var v = getCookieValue("secondsremained_login") ? getCookieValue("secondsremained_login") : 0;//获取cookie值  
    if(v>0){  
        settime($("#second"));//开始倒计时  
    }  
})
function sendCode(obj){
    var type=$('#passwordfindform-type').val();
    $.ajax({
        async : false,  
        cache : false,  
        type : 'POST',  
        url : "/common/send-captcha?type="+type,// 请求的action路径
        dataType: "json",
        success:function(data){  
            if(data.stat=='success'){ 
                addCookie("secondsremained_login",60,60);//添加cookie记录,有效时间60s  
                settime(obj);//开始倒计时  
                $('.login-page').prepend('<div id="w0-success" class="alert-success alert fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-check"></i>'+data.message+'</div>');
            }else{  
                $('.login-page').prepend('<div id="w0-error" class="alert-error alert fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-check"></i>'+data.message+'</div>');
                return false;  
            }  
        }  
    });  
    
}
//开始倒计时  
var countdown;  
function settime(obj) {  
    countdown=getCookieValue("secondsremained_login") ? getCookieValue("secondsremained_login") : 0;  
    if (countdown ==0) {  
        obj.removeAttr("disabled");  
        obj.val("重新获取验证码");  
        return;  
    } else {  
        obj.attr("disabled", true);  
        obj.val(countdown + "秒后重新获取");  
        countdown--;  
        editCookie("secondsremained_login",countdown,countdown+1);  
    }  
    setTimeout(function() { settime(obj) },1000) //每1000毫秒执行一次
}  
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['password'], \yii\web\View::POS_END); ?>