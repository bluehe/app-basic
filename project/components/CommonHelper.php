<?php

namespace project\components;

use Yii;
use project\models\System;
use project\models\CorporationCodehub;

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
                    'accessKeyId' => $sms['aliyun_sms_key'],
                    'accessKeySecret' => $sms['aliyun_sms_secret'],                
                ]);
                $response = Yii::$app->aliyun->sendSms(
                            $sms['aliyun_sms_sign'], // 短信签名
                            $sms['aliyun_'.$type], //模板编号
                            $smsto, // 短信接收者
                            $content
                        );
                $sms_response= json_decode($response,TRUE);
                $result =$sms_response['code']==200?'success':$sms_response['message'];
            }elseif($sms['sms_platform']=='cloudsmser'){
                require_once(Yii::getAlias('@common').'/vendor/smser/cloudsmser/smsapi.fun.php');
                $response=sendSMS($sms['cloudsmser_sms_key'],$sms['cloudsmser_sms_secret'],$smsto,array_to_json($content),$sms['cloudsmser_'.$type]);
                $result =$response['stat']==100?'success':$response['message'];
            }elseif($sms['sms_platform']=='submail'){
                $server='https://api.mysubmail.com/';
                $message_configs['appid']=$sms['submail_sms_key'];
                $message_configs['appkey']=$sms['submail_sms_secret'];
                $message_configs['sign_type']='normal';
                $message_configs['server']=$server;
                
                require_once(Yii::getAlias('@common').'/vendor/smser/submail/SUBMAILAutoload.php');
                $submail=new \MESSAGEXsend($message_configs);
                $submail->setTo($smsto);
                $submail->SetProject($sms['submail_'.$type]);
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
    
    public static function getImage($url,$duration = 3600*24) {
        if (Yii::$app->siteConfig->cdn_service) {
            $cache = Yii::$app->cache;
            $imgurl = $cache->get(Yii::$app->siteConfig->cdn_platform.'_' . $url);
            if ($imgurl === false) {
                if(strpos($url, '/') === 0 ){
                    $path = substr($url, 1);
                }else{
                    $path=$url;
                }
                $flag = 0;                             
                
                if(Yii::$app->cdn->exists($path)){             
                    $flag = 1;
                }elseif (file_exists($path)){
                    if(Yii::$app->cdn->upload($path,$path)){
                        $flag = 1;                    
                    }else{
                        $flag = 0; 
                    }
                }else{
                     $flag = 0; 
                }
                
                if ($flag==1) {
                    $imgurl=Yii::$app->siteConfig->{Yii::$app->siteConfig->cdn_platform.'_cdn_host'}.'/'.$path;
                    $cache->set(Yii::$app->siteConfig->cdn_platform.'_' . $url, $imgurl,$duration);
                }else{
                    $imgurl=$url;
                }
                
                
            }
            return $imgurl;
        }else{
            return $url;
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
    
    public static function searchNumber($query,$attribute,$value,$group=false) {
       
        if(preg_match('/[~|-]{1}/',$value)){
            $v= explode('~', $value);
            if(count($v)<2){
                $v= explode('-', $value);
            }
            if($group){
                $query->andHaving(['between',$attribute,$v[0],$v[1]]);
            }else{
                $query->andFilterWhere(['between',$attribute,$v[0],$v[1]]);
            }
            
        }elseif (preg_match('/^(<>|>=|>|<=|<|=)/', $value, $matches)) {          
            $operator = $matches[1];
            $value = substr($value, strlen($operator));
            if($group){
                $query->andHaving([$operator, $attribute, $value]);
            }else{
                $query->andFilterWhere([$operator, $attribute, $value]);
            }
            
        } else {
            if($group){
                $query->andHaving(['=', $attribute, $value]);
            }else{
                $query->andFilterWhere(['=', $attribute, $value]);
            }
        }
        
        return $query;
    }
    
    public static function corporationRule($rule='update',$key=null){
        $cache = Yii::$app->cache;
        $corporation = $cache->get('corporation_'.$rule);
        if ($corporation === false||!isset($corporation[$key][Yii::$app->user->identity->id])) {
            if($rule=='update'){
                $corporation[$key][Yii::$app->user->identity->id]=Yii::$app->user->can('企业修改',['id'=>$key]);
            }elseif($rule=='delete'){
                $corporation[$key][Yii::$app->user->identity->id]=Yii::$app->user->can('企业删除',['id'=>$key]);
            }
                           
            $cache->set('corporation_'.$rule, $corporation);
        }
        return $corporation[$key][Yii::$app->user->identity->id];
        
    }
    
}
