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
    
    //找回密码验证码
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
        
        }elseif($type=='tel'){
            if(!$user->tel){
                return json_encode(['stat'=>'fail','message'=>'未设置手机号'],256);
            }
            $result_json= \app\components\CommonHelper::sendSms($user->tel, 'sms_captcha', ['code'=>$num]);
            $result_arr= json_decode($result_json,TRUE);
            $result=$result_arr['stat']=='success';
        }else{
            return json_encode(['stat'=>'fail','message'=>'类型错误'],256);
        }
        
        if($result){
            Yii::$app->session->set('verifyCode',['type'=>$type,'code'=>$num]);
            return json_encode(['stat'=>'success','message'=>'验证码发送成功'],256);
        }else{
            return json_encode(['stat'=>'fail','message'=>'验证码发送失败'],256);
        }
        
    }

    //认证验证码
    public function actionSendCaptcha($to,$type=null) {
        if(!$to){
            return json_encode(['stat'=>'fail','message'=>'未设置发送对象'],256);
        }

        $length= System::getValue('captcha_length');
        $num=rand(pow(10,($length-1)), pow(10,$length)-1);
        if($type=='email'){      
            $result =Yii::$app->mailer
                        ->compose(['html' => 'passwordFind-html', 'text' => 'passwordFind-text'], ['num' => $num])
                        //->setFrom([Yii::$app->params['supportEmail']['transport']['username'] => Yii::$app->name])
                        ->setTo($to)
                        ->setSubject(Yii::$app->name . '邮箱验证码邮件')
                        ->send();
        
        }elseif($type=='tel'){
            $result_json= \app\components\CommonHelper::sendSms($to, 'sms_captcha', ['code'=>$num]);
            $result_arr= json_decode($result_json,TRUE);
            $result=$result_arr['stat']=='success';
        }else{
            return json_encode(['stat'=>'fail','message'=>'类型错误'],256);
        }
        
        if($result){
            Yii::$app->session->set('auth_verifyCode',['type'=>$type,'to'=>$to,'code'=>$num]);
            return json_encode(['stat'=>'success','message'=>'验证码发送成功'],256);
        }else{
            return json_encode(['stat'=>'fail','message'=>'验证码发送失败'],256);
        }
        
    }
    
    /**.
     *
     * @return string
     */
    public function actionGetfav() {
        $url = Yii::$app->request->get('url'); //parse_url(Yii::$app->request->get('url'), PHP_URL_HOST);
        $cache = Yii::$app->cache;
        $file = $cache->get('fav_' . $url);
        if ($file === false) {
            session_write_close();
            $flag = 0;
            $dir = 'data/icon'; //图标存放文件夹
            if (!is_dir($dir)) {
                mkdir($dir, 0777, TRUE);
            }
            $fav = $dir . "/" . $url . ".png"; //图标存放路径
            if (file_exists($fav)) {
                $file = @file_get_contents($fav);
                $flag = 1;
            } else {
                $file = @file_get_contents("https://api.byi.pw/favicon/?url=$url");
                @file_put_contents($fav, $file);
                $flag = 1;

//        $img_info_1 = md5_file("https://api.byi.pw/favicon/?url=$url");
//        $img_info_2 = md5_file("https://api.byi.pw/favicon/?url=error"); //别人接口默认的值

                $size = filesize($fav);
                if ($size == 492 || $size == 0 || $size == 726) {
                    @unlink($fav);
                    $file = @file_get_contents('image/default_e.png');
                    $flag = 0;
                }
            }
            if ($flag) {
                $cache->set('url_' . $url, $file);
            }
        }
        //header('Content-type: image/png');
        return $file;
    }
}
