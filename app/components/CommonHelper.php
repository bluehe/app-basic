<?php

namespace app\components;

use Yii;

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

}
