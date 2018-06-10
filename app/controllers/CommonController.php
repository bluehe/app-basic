<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use Da\QrCode\QrCode;
use app\models\User;
use app\models\System;

/**
 * Common controller
 */
class CommonController extends Controller {
    
    //二维码
    public function actionQrcode() {
        $url= Yii::$app->request->referrer;
        $qrCode = (new QrCode($url))->setSize(150)->setMargin(10)->useLogo(Yii::getAlias('@webroot') .'/image/logo.jpg')->setLogoWidth(30);
        header('Content-Type: '.$qrCode->getContentType());
        return  $qrCode->writeString();
    }
    
    //邮件验证码
    public function actionSendCaptchaByToken($type) {
        
        $token=Yii::$app->session->get('find_password_token');
        $user=User::findByPasswordResetToken($token);
        if(!$user){
            return json_encode(['stat'=>'fail','message'=>'用户不存在'],256);
        }
        $length= System::getValue('captcha_length');
        $num=rand(pow(10,($length-1)), pow(10,$length)-1);
        if($type=='email'){
            if(!$user->email){
                return json_encode(['stat'=>'fail','message'=>'未设置邮箱'],256);
            }
       
            $result =Yii::$app->mailer
                        ->compose(['html' => 'passwordFind-html', 'text' => 'passwordFind-text'], ['num' => $num])
                        //->setFrom([Yii::$app->params['supportEmail']['transport']['username'] => Yii::$app->name])
                        ->setTo($user->email)
                        ->setSubject(Yii::$app->name . '邮箱验证码邮件')
                        ->send();
        
        }else{
            if(!$user->tel){
                return json_encode(['stat'=>'fail','message'=>'未设置手机号'],256);
            }
            $result=false;
        }
        
        if($result){
            Yii::$app->session->set('verifyCode',['type'=>$type,'code'=>$num]);
            return json_encode(['stat'=>'success','message'=>'验证码发送成功'],256);
        }else{
            return json_encode(['stat'=>'fail','message'=>'验证码发送失败'],256);
        }
        
    }    
}
