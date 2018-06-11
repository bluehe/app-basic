<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '短信设置';
$this->params['breadcrumbs'][] = ['label' => '系统设置', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <?php
            $form = ActiveForm::begin(['id' => 'sms-form',
                        'options' => ['class' => 'form-horizontal'],
            ]);
            ?>
            <div class="box-body">
                <?php
                foreach ($model as $one) {
                    if ($one['type'] == 'text' || $one['type'] == 'password') {
                        ?>
                        <div class="form-group field-sms field-<?= $one['code'] ?>"><label class="col-md-2 control-label" for="<?= $one['code'] ?>"><?= $one['tag'] ?></label><div class="col-md-4"><input type="<?= $one['type'] ?>" id="<?= $one['code'] ?>" class="form-control" name="System[<?= $one['code'] ?>]" value="<?= $one['value'] ?>"></div><div class="col-md-6"><div class="help-block"></div></div></div>
                        <?php
                    } elseif ($one['type'] == 'radio' && $ranges = json_decode($one['store_range'])) {
                        ?>
                        <div class="form-group field-sms field-<?= $one['code'] ?>"><label class="col-md-2 control-label"><?= $one['tag'] ?></label>
                            <div class="col-md-4">
                                <div id="<?= $one['code'] ?>">
                                    <?php
                                    foreach ($ranges as $key => $range) {
                                        ?>
                                        <label class="radio-inline"><input type="radio" class="radio-<?= $one['code'] ?>" name="System[<?= $one['code'] ?>]" value="<?= $key ?>" <?= "$key" == $one['value'] ? 'checked="checked"' : '' ?>> <?= $range ?></label>
                                        <?php
                                    }
                                    ?>

                                </div>
                            </div>
                            <div class="col-md-6"><div class="help-block"></div></div>
                        </div>
                        <?php
                    }
                }
                ?>
                <div class="form-group field-sms"><label class="col-md-2 col-xs-12 control-label" for="smtp-test">测试号码</label><div class="col-md-4 col-xs-7"><input type="text" id="sms-test" class="form-control" name="tel" value=""></div><div class="col-md-6 col-xs-5"><input id="second" type="button" value="发送测试短信" class="btn btn-success"></div></div>
            </div>
            <div class="box-footer">
                <div class="col-md-1 col-md-offset-2 col-xs-6 text-right">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary', 'name' => 'update-button']) ?>

                </div>
                <div class="col-md-1 col-xs-6 text-left">
                    <?= Html::resetButton('重置', ['class' => 'btn btn-default', 'name' => 'update-button']) ?>
                </div>

            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<script>
<?php $this->beginBlock('sendsms') ?>
    
    $(document).ready(function () {
        changeshow();
        $('.radio-sms_service,.radio-sms_platform').on('change', function () {
            changeshow();
        });

    });
    function changeshow() {
        var service = $('.radio-sms_service:checked').val();
        if (service === '1') {
            $('.field-sms:not(.field-smtp_service)').show();
            
            var platform = $('.radio-sms_platform:checked').val();
            if (platform === 'cloudsmser'||platform === 'submail') {
                $('.field-sms_sign').hide();
            } else {
                $('.field-sms_sign').show();           
            }
        } else {
            $('.field-sms:not(.field-sms_service)').hide();                      
        }
        
        
    }
    
    
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
var result = isPhone();   
    if(result){
    $.ajax({
        async : false,  
        cache : false,  
        type : 'POST',  
        url: '/system/send-sms',
        dataType: "json",
        data: $('form').serialize(),
        success:function(data){  
            if(data.stat=='success'){ 
                addCookie("secondsremained_login",60,60);//添加cookie记录,有效时间60s  
                settime(obj);//开始倒计时  
                $('.content').prepend('<div id="w0-success" class="alert-success alert fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-check"></i>'+data.message+'</div>');
            }else{  
                $('.content').prepend('<div id="w0-error" class="alert-error alert fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-close"></i>'+data.message+'</div>');
                return false;  
            }  
        },
        error: function () {
                    $('.content').prepend('<div id="w0-error" class="alert-error alert fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-close"></i>测试短信发送失败。</div>');
        },
    });  
    }
}
//开始倒计时  
var countdown;  
function settime(obj) {  
    countdown=getCookieValue("secondsremained_login") ? getCookieValue("secondsremained_login") : 0;  
    if (countdown ==0) {  
        obj.removeAttr("disabled");  
        obj.val("发送测试短信");  
        return;  
    } else {  
        obj.attr("disabled", true);  
        obj.val(countdown + "秒后重新发送");  
        countdown--;  
        editCookie("secondsremained_login",countdown,countdown+1);  
    }  
    setTimeout(function() { settime(obj) },1000) //每1000毫秒执行一次
}  

//校验手机是否合法  
function isPhone(){  
    var email = $("#sms-test").val();  
    var myreg = /^(1[34578]{1}\d{9})$/;  
    if(!myreg.test(email.toLowerCase())){  
        $('.content').prepend('<div id="w0-error" class="alert-error alert fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-close"></i>请输入有效的测试号码！</div>');
        $("#sms-test").focus();  
           return false;  
    }else{  
        return true;  
    }  
}  
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['sendsms'], \yii\web\View::POS_END); ?>
