<?php

namespace project\components;

use Yii;
use project\models\System;

class CommonHelper {

    public static function hideName($name) {
        
        if(strpos($name,'@')){
            //电子邮箱
            $a_pos=strpos($name,'@');//@位置
            $n=mb_substr($name,0,$a_pos);//@前面部分
            $l= mb_strlen($n);
            if($l>=3){
                $n=substr_replace($n, '****', 3);
            }else{
                $n=substr_replace($n, '****', 1);
            }
            $name=$n.mb_substr($name,$a_pos);
        }elseif(preg_match("/^1[34578]{1}\d{9}$/",$name)){
            //手机
            $name=substr_replace($name, '****', 3, 4);
        }elseif(preg_match("/^[0-9a-zA-Z]{4,}$/",$name)){
            //用户名
            
            $name=substr_replace($name, '****', 2);
        }else{
            //其他
            $name=mb_substr($name, 0, 2).'****';
        }
        return $name;
    }
    
    public static function sendSms($smsto,$type,$content) {
       
        if (!preg_match("/^1[34578]{1}\d{9}$/",$smsto)) {
            return json_encode(['stat'=>'fail','message'=>'请输入有效手机号']);
        }
        
        $cache = Yii::$app->cache;
        $sms = $cache->get('system_sms');
        if ($sms === false) {
            $sms = System::getChildrenValue('sms');
            $cache->set('system_sms', $sms);
        }
        
        if($sms['sms_service']==1){
            if($sms['sms_platform']=='aliyun'){
                Yii::$app->set('aliyun', [
                    'class' => 'saviorlv\aliyun\Sms',
                    'accessKeyId' => $sms['sms_key'],
                    'accessKeySecret' => $sms['sms_secret'],                
                ]);
                $response = Yii::$app->aliyun->sendSms(
                            $sms['sms_sign'], // 短信签名
                            $sms[$type], //模板编号
                            $smsto, // 短信接收者
                            $content
                        );
                $sms_response= json_decode($response,TRUE);
                $result =$sms_response['code']==200?'success':$sms_response['message'];
            }elseif($sms['sms_platform']=='cloudsmser'){
                require_once(Yii::getAlias('@common').'/vendor/smser/cloudsmser/smsapi.fun.php');
                $response=sendSMS($sms['sms_key'],$sms['sms_secret'],$smsto,array_to_json($content),$sms[$type]);
                $result =$response['stat']==100?'success':$response['message'];
            }elseif($sms['sms_platform']=='submail'){
                $server='https://api.mysubmail.com/';
                $message_configs['appid']=$sms['sms_key'];
                $message_configs['appkey']=$sms['sms_secret'];
                $message_configs['sign_type']='normal';
                $message_configs['server']=$server;
                
                require_once(Yii::getAlias('@common').'/vendor/smser/submail/SUBMAILAutoload.php');
                $submail=new \MESSAGEXsend($message_configs);
                $submail->setTo($smsto);
                $submail->SetProject($sms[$type]);
                foreach($content as $k=>$v){
                    $submail->AddVar($k,$v);
                }
                $xsend=$submail->xsend();
                $result =$xsend['status']=='success'?'success':$xsend['msg'];               
            }
             return $result=='success'?json_encode(['stat'=>'success','message'=>'短信发送成功']):json_encode(['stat'=>'fail','message'=>$result]);
        }else{
            return json_encode(['stat'=>'fail','message'=>'未开启短信功能']);
        }
    }
    
    public static function maxSize() {
        $postMaxSize = ini_get('post_max_size');
        $fileMaxSize = ini_get('upload_max_filesize');
        $displayMaxSize = $postMaxSize < $fileMaxSize ? $postMaxSize : $fileMaxSize;
        switch (substr($displayMaxSize, -1)) {
            case 'G':
                $displayMaxSize = $displayMaxSize * 1024 * 1024;
                break;
            case 'M':
                $displayMaxSize = $displayMaxSize * 1024;
                break;
            default:;
        }
        return $displayMaxSize;
    }

}
