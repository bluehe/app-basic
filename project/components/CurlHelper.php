<?php

namespace project\components;

use Yii;

class CurlHelper {
    
    /**
    * [http 调用接口函数]
    * @Date   2016-07-11
    * @Author GeorgeHao
    * @param  string       $url     [接口地址]
    * @param  array        $params  [数组]
    * @param  string       $method  [GET\POST\DELETE\PUT]
    * @param  array        $header  [HTTP头信息]
    * @param  integer      $timeout [超时时间]
    * @return [type]                [接口返回数据]
    */
    private static function http($url, $params, $method = 'GET', $header = array(), $timeout = 5)
    {
        // POST 提交方式的传入 $set_params 必须是字符串形式
        $opts = array(
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $header
        );

       /* 根据请求类型设置特定参数 */
       switch (strtoupper($method)) {
           case 'GET':
               $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
               break;
           case 'POST':
               $params = http_build_query($params);
               $opts[CURLOPT_URL] = $url;
               $opts[CURLOPT_POST] = 1;
               $opts[CURLOPT_POSTFIELDS] = $params;
               break;
           case 'DELETE':
               $opts[CURLOPT_URL] = $url;
               $opts[CURLOPT_HTTPHEADER] = array("X-HTTP-Method-Override: DELETE");
               $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
               $opts[CURLOPT_POSTFIELDS] = $params;
               break;
           case 'PUT':
               $opts[CURLOPT_URL] = $url;
               $opts[CURLOPT_POST] = 0;
               $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
               $opts[CURLOPT_POSTFIELDS] = $params;
               break;
           default:
               throw new Exception('不支持的请求方式！');
       }

       /* 初始化并执行curl请求 */
       $ch = curl_init();
       curl_setopt_array($ch, $opts);
       $data = curl_exec($ch);
       $error = curl_error($ch);
       return $data;
    }
    
    private static function geturl($url,$headerArray =[]){
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);//获取http返回值,最后一个收到的HTTP代码
        curl_close($curl);
        
        $info['code'] = $status;
        $info['content'] = json_decode($output,true);
        return $info;
    }


    private static function posturl($url,$params,$headerArray =[],$returnHeader=false){
        $params  = is_array($params)?json_encode($params):$params;          
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$headerArray);
        curl_setopt($curl, CURLOPT_HEADER, $returnHeader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);//获取http返回值,最后一个收到的HTTP代码
        curl_close($curl);
        
        $info['code'] = $status;
        if($returnHeader){
            list($header, $body) = explode("\r\n\r\n", $output, 2);           
            $info['header']=$header;
            $info['content'] = json_decode($body,true);
        }else{
            $info['content'] = json_decode($output,true);
        }
        return $info;
    }
    
    private static function delurl($url,$params,$headerArray=[]){  
        $params  = is_array($params)?json_encode($params):$params;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);        
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        $output = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);//获取http返回值,最后一个收到的HTTP代码
        curl_close($curl);
        
        $info['code'] = $status;
        $info['content'] = json_decode($output,true);       
        return $info;
    }


    function puturl($url,$data){
        $data = json_encode($data);
        $ch = curl_init(); //初始化CURL句柄 
        curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PUT"); //设置请求方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置提交的字符串
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output,true);
    }

    


    public static function authToken($account){
        $url='https://iam.myhuaweicloud.com/v3/auth/tokens';
        $params='{"auth": {"identity": {"methods": ["password"],"password": {"user": {"name": "'.$account->user_name.'","password": "'.$account->password.'","domain": {"name": "'.$account->account_name.'"}}}},"scope": {"domain": {"name": "'.$account->account_name.'"}}}}';
        $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $data= self::posturl($url, $params,$headerArray,true);
        if($data['code']=='201'){
            preg_match("/X-Subject-Token\:(.*?)\n/", $data['header'], $matches);
            $data['token']  = trim($matches[1]);
        }
        return $data;
    }
    
    public static function addUser($account,$token){
        $url='https://iam.myhuaweicloud.com/v3/users';
        $params='{"user": {"enabled": true,"name": "'.$account->user_name.'","password": "'.$account->password .'"}}';
        $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json","X-Auth-Token:".$token);
        $data= self::posturl($url, $params,$headerArray);      
        return $data;
    }
    
    public static function deleteUser($user_id,$token){
        $url='https://iam.myhuaweicloud.com/v3/users/'.$user_id;      
        $headerArray =array("Content-type:application/json;","X-Auth-Token:".$token);
        $data= self::delurl($url,'',$headerArray);       
        return $data;
    }
    
    public static function listUser($token){
        $url='https://iam.myhuaweicloud.com/v3/users';      
        $headerArray =array("Content-type:application/json;","X-Auth-Token:".$token);
        $data= self::geturl($url,$headerArray);       
        return $data;
    }
    
    public static function addProject($project,$token){
        $url='https://api.devcloud.huaweicloud.com/pcedge/v1/projects';
        $params='{"name":"'.$project->name.'", "description": "'.$project->description.'", "type": "normal", "homepage": ""}';
        $headerArray =array("Content-type:application/json;","X-Auth-Token:".$token);
        $data= self::posturl($url, $params,$headerArray);      
        return $data;
    }
    
    public static function listProject($token){
        $url='https://api.devcloud.huaweicloud.com/pcedge/v1/projects?1,100';      
        $headerArray =array("Content-type:application/json;","X-Auth-Token:".$token);
        $data= self::geturl($url,$headerArray);       
        return $data;

    }
    
    public static function addMember($project_uuid,$account,$token){
        $url='https://api.devcloud.huaweicloud.com/pcedge/v1/projects/'.$project_uuid.'/members';
        $params='{
            "user_id": "'.$account->user_id.'",
            "user_name": "'.$account->user_name.'",
            "domain_id": "'.$account->domain_id.'",
            "domain_name": "'.$account->account_name.'",
            "role": 4
        }';
        $headerArray =array("Content-type:application/json;","X-Auth-Token:".$token);
        $data= self::posturl($url, $params,$headerArray);       
        return $data;
    }
    
    public static function deleteMember($project_uuid,$user_id,$token){
        $url='https://api.devcloud.huaweicloud.com/pcedge/v1/projects/'.$project_uuid.'/members/'.$user_id;      
        $headerArray =array("Content-type:application/json;","X-Auth-Token:".$token);
        $data= self::delurl($url,'',$headerArray);       
        return $data;
    }
    
    public static function listMember($project_uuid,$token){
        $url='https://api.devcloud.huaweicloud.com/pcedge/v1/projects/'.$project_uuid.'/members';      
        $headerArray =array("Content-type:application/json;","X-Auth-Token:".$token);
        $data= self::geturl($url,$headerArray);       
        return $data;
    }
    
    public static function addCodehub($project_uuid,$codehub,$token){
        $url='https://api.devcloud.huaweicloud.com/codehub/v1/repositories';
        $params='{"name":"'.$codehub->name.'", "template_id": "", "project_uuid": "'.$project_uuid.'", "import_members": 1}';
        $headerArray =array("Content-type:application/json;","X-Auth-Token:".$token);
        $data= self::posturl($url, $params,$headerArray);      
        return $data;

    }
    
    public static function deleteCodehub($repository_uuid,$token){
        $url='https://api.devcloud.huaweicloud.com/codehub/v1/repositories/'.$repository_uuid;      
        $headerArray =array("Content-type:application/json;","X-Auth-Token:".$token);
        $data= self::delurl($url,'',$headerArray);       
        return $data;
    }
    
}
