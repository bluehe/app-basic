<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use project\actions\IndexAction;
use project\models\HealthSearch;
use project\models\CorporationAccount;
use project\models\Corporation;
use project\components\CurlHelper;
use project\models\CorporationProject;
use project\models\CorporationCodehub;


class HealthController extends Controller { 
    
    public function actions()
    {
        return [
            'index' => [     
                'class' => IndexAction::className(),
                'data' => function(){
                    $end = strtotime('today');
                    $start = strtotime('-1 months +1 days',$end);
                    $annual=Yii::$app->request->get('annual');
                    $sum=Yii::$app->request->get('sum',1);
                    
                    if (Yii::$app->request->get('range')) {
                        $range = explode('~', Yii::$app->request->get('range'));
                        $start = isset($range[0])? strtotime($range[0]) : $start;
                        $end = isset($range[1])&& (strtotime($range[1]) < $end)? strtotime($range[1]): $end;
                    }

                    $searchModel = new HealthSearch();
                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$start-86400,$end,$sum,$annual);

                    return [  
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'start' => $start,
                        'end' => $end,
                        'annual'=>$annual,
                        'sum'=>$sum,
                    ];
                              
                }
            ],
            'corporation-user' => [     
                'class' => IndexAction::className(),
                'ajax'=>true,
                'data' => function(){                  
                    $id=Yii::$app->request->get('id');                                      

                    CorporationAccount::set_corporation_account_list($id);
                    $dataProvider = new ActiveDataProvider([
                        'query' => CorporationAccount::find()->andWhere(['corporation_id'=> $id]),
                        'sort'=>['defaultOrder' => [
                            'add_type' => SORT_ASC,
                            'id' => SORT_ASC,
                        ]]
                    ]);
                    return [
                        'dataProvider' => $dataProvider,
                        'corporation_id'=>$id
                    ];
                              
                }
            ],
        ];
    }
    
    public function actionAccountCreate($id) {
        $corporation = Corporation::findOne($id);
        
        $model = new CorporationAccount();
        $model->scenario='create';
        $model->corporation_id=$corporation->id;
        $model->account_name=$corporation->huawei_account;
        $model->is_admin= CorporationAccount::ADMIN_NO;
       
        if ($model->load(Yii::$app->request->post())) {
            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
                 
            $model->add_type= CorporationAccount::TYPE_ADD;
            if(!$model->user_name){
                $model->user_name=$model->account_name;
            }
            
            $auth = CurlHelper::authToken($model);
            if($auth['code']=='201'){
                $token=$auth['content']['token'];
                $model->domain_id=$token['user']['domain']['id'];
                $model->user_id=$token['user']['id'];
                foreach($token['roles'] as $role){
                    if($role['name']=="secu_admin"){
                        $model->is_admin= CorporationAccount::ADMIN_YES;
                        break;
                    }
                }
                if($model->save()){
                    $cache=Yii::$app->cache;
                    $cache->set('accountToken_'.$model->id,$auth['token'], strtotime($token['expires_at'])-time());

                    CorporationAccount::set_corporation_account_list($id);
                    
                    Yii::$app->session->setFlash('success', '操作成功。');
                }else{
                    Yii::$app->session->setFlash('error', '操作失败。');
                }
            }else{
                Yii::$app->session->setFlash('error', '请求失败。');
            }
           
            return $this->redirect(Yii::$app->request->referrer);
            
        }else{                        
                      
            return $this->renderAjax('account-create', [
                        'model' => $model,
        ]);
            
        }
        
    }
    
    public function actionAccountUpdate($id) {
       
        
        $model = CorporationAccount::findOne($id);
        $model->scenario='create';
       
        if ($model->load(Yii::$app->request->post())) {
            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
                 
            $model->add_type= CorporationAccount::TYPE_ADD;
            if(!$model->user_name){
                $model->user_name=$model->account_name;
            }
            
            $auth = CurlHelper::authToken($model);
            if($auth['code']=='201'){
                $token=$auth['content']['token'];
                $model->domain_id=$token['user']['domain']['id'];
                $model->user_id=$token['user']['id'];
                foreach($token['roles'] as $role){
                    if($role['name']=="secu_admin"){
                        $model->is_admin= CorporationAccount::ADMIN_YES;
                        break;
                    }
                }
                if($model->save()){
                    $cache=Yii::$app->cache;
                    $cache->set('accountToken_'.$model->id,$auth['token'], strtotime($token['expires_at'])-time());

                    CorporationAccount::set_corporation_account_list($id);
                    
                    Yii::$app->session->setFlash('success', '操作成功。');
                }else{
                    Yii::$app->session->setFlash('error', '操作失败。');
                }
            }else{
                Yii::$app->session->setFlash('error', '请求失败。');
            }
           
            return $this->redirect(Yii::$app->request->referrer);
            
        }else{                        
                      
            return $this->renderAjax('account-update', [
                        'model' => $model,
        ]);
            
        }
        
    }
    
    public function actionAccountAdd($id) {
        $corporation = Corporation::findOne($id);
        
        $model = new CorporationAccount();
        $model->scenario='create';
        $model->corporation_id=$id;
        $model->account_name=$corporation->huawei_account;
        $model->is_admin= CorporationAccount::ADMIN_NO;         
        $model->add_type= CorporationAccount::TYPE_SYSTEM;
              
        $model->user_name= CorporationAccount::get_last_username($id);
        $model->password=substr(md5($model->account_name.$model->user_name),0,8).'a1';
        $token = CorporationAccount::get_token($id, CorporationAccount::ADMIN_YES);
        
        if(!$token){
            return false;
        }
        
//        CorporationAccount::set_corporation_account_list($id);
        $auth = CurlHelper::addUser($model,$token);
        
        if($auth['code']=='201'){
           
            $model->domain_id=$auth['content']['user']['domain_id'];
            $model->user_id=$auth['content']['user']['id'];
            
            if($model->save()){
                return json_encode(['stat'=>'success']);
            }else{
                CurlHelper::deleteUser($model->user_id,$token);
                Yii::$app->session->setFlash('error', '操作失败。');
            }
        }else{
            Yii::$app->session->setFlash('error', '请求失败。');
        }

        return $this->redirect(Yii::$app->request->referrer); 
        
    }
    
    public function actionAccountDelete($id)
    {
        $model = CorporationAccount::findOne($id);
        $stat='error';
        if ($model !== null) {
            $auth['code']='204';
            if($model->add_type== CorporationAccount::TYPE_SYSTEM){
                $auth=CurlHelper::deleteUser($model->user_id,CorporationAccount::get_token($model->corporation_id, CorporationAccount::ADMIN_YES));
            }
            if($auth['code']=='204'&&$model->delete()){
                $stat='success';
            }else{
                $stat='fail';
            }
        }        
        return json_encode(['stat' => $stat]);
    }
    
    public function actionProjectCreate($id) {
        
        $model = new CorporationProject();       
        $model->corporation_id=$id;
        $model->name='demo2019';
        $model->description= '';
        $model->add_type= CorporationProject::TYPE_ADD;
                   
        $token = CorporationAccount::get_token($id);
        $auth = CurlHelper::addProject($model,$token);
        if($auth['code']=='200'&&$auth['content']['status']=='success'){
            $model->project_uuid=$auth['content']['result']['project']['project_uuid'];          
        }elseif($auth['code']=='200'&&$auth['content']['status']=='failed'){
            $auth1 = CurlHelper::listProject($token);
            if($auth1['code']=='200'){
                foreach ($auth1['content']['result']['projects'] as $project){
                    if($project['name']=='demo2019'){
                        $model->project_uuid=$project['project_uuid'];
                    }
                }               
            }
        }
        
        if($model->project_uuid){
            if($model->save()){
                $cache=Yii::$app->cache;
                $cache->set('corporationProject_'.$model->corporation_id,$model->project_uuid);
                Yii::$app->session->setFlash('success', '操作成功。');
            }else{
                Yii::$app->session->setFlash('error', '操作失败。');
            }
        }else{
            Yii::$app->session->setFlash('error', '请求失败。');
        }
           
        return $this->redirect(Yii::$app->request->referrer);
   
    }
    
    public function actionMemberList($id) {
       
        
        $model = CorporationProject::findOne(['corporation_id'=>$id]);
        
        $members=[];
        $token = CorporationAccount::get_token($id);
        $auth_member= CurlHelper::listMember($model->project_uuid, $token);
        if($auth_member['code']=='200'&&$auth_member['content']['status']=='success'){
            
            foreach ($auth_member['content']['result']['members'] as $member){
                $members[$member['user_id']]=$member['role_id'];
            }
            $model->member= array_keys($members);
        }
       
        if ($model->load(Yii::$app->request->post())) {
            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
            
            $add_members= array_diff($model->member, array_keys($members));
            $delete_members= array_diff(array_keys($members), $model->member);
                
            $num_success=$num_fail=0;
            if(count($add_members)>0){
                foreach ($add_members as $add){                 
                    $account = CorporationAccount::findOne(['user_id'=>$add]);
                    $auth=CurlHelper::addMember($model->project_uuid, $account, $token);
                    if($auth['code']=='200'&&$auth['content']['status']=='success'){
                        ++$num_success;
                    }else{
                        ++$num_fail;
                    }
                }
                
            }
            
            if(count($delete_members)>0){
                foreach ($delete_members as $delete){
                    if($members[$delete]!=3){
                        $auth=CurlHelper::deleteMember($model->project_uuid, $delete, $token);
                        if($auth['code']=='200'&&$auth['content']['status']=='success'){
                            ++$num_success;
                        }else{
                            ++$num_fail;
                        }
                    }else{
                        ++$num_fail;
                    }
                }
                
            }
            Yii::$app->session->setFlash('warning', '操作成功'.$num_success.'个，失败'.$num_fail.'个。');
            return $this->redirect(Yii::$app->request->referrer);
            
        }else{                        
                      
            return $this->renderAjax('member-list', [
                        'model' => $model,
        ]);
            
        }
        
    }
    
    public function actionCodehubCreate($id) {
        $corporation = Corporation::findOne($id);
        
        $model = new CorporationCodehub();
        $model->corporation_id=$corporation->id;
        
        if ($model->load(Yii::$app->request->post())) {
            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
            
            $targetFolder = '/data/git';
            $targetPath = Yii::getAlias('@webroot') . $targetFolder;

            if (!file_exists($targetPath)) {
                @mkdir($targetPath, 0777, true);
            }
                 
            if (file_exists($targetPath.'/'.$model->corporation_id)) {
                if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
                    $comm='cd '.$targetPath.' && rd/s/q '.$model->corporation_id;
                }else{
                    $comm='cd '.$targetPath.' && rm -rf '.$model->corporation_id;
                } 
                exec($comm);
            }


            $command='cd '.$targetPath.' && git clone https://'. urlencode(trim($model->username)).':'.urlencode(trim($model->password)).'@'. substr($model->https_url, 8).' '.$model->corporation_id;
            
            exec($command.' 2>&1',$output,$status);
                       
            if(file_exists($targetPath.'/'.$model->corporation_id)&&$model->save()){                   
                Yii::$app->session->setFlash('success', '操作成功。');
            }else{
                Yii::$app->session->setFlash('error', '操作失败。'.$status.$command. json_encode($output));
            }
                      
            return $this->redirect(Yii::$app->request->referrer);
            
        }else{                        
                      
            return $this->renderAjax('codehub-create', [
                        'model' => $model,
            ]);
            
        }

    }
    
    public function actionCodehubExec() {    
        
        //$model=CorporationCodehub::findOne(['corporation_id'=>$id]);
       
        $id=4;
        
        $targetFolder = '/data/git';
        $targetPath = Yii::getAlias('@webroot') . $targetFolder.'/'.$id;

        if (!file_exists($targetPath)) {
            return false;
        }               
//        echo $command='cd '.$targetPath.' && echo '.time().' > README.md && git add . && git commit -m "'.time().'" && git push';
//        exec($command.' 2>&1',$output,$status);
//        var_dump($output);
//        echo $status;
        
        if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
            $command="call ".Yii::getAlias('@webroot') ."/data/git.sh {$id}";
        }else{
            $command=Yii::getAlias('@webroot') ."/data/git.sh {$targetPath} ".time();
        } 
        exec($command.' 2>&1',$output,$status);
        var_dump($output);
        echo $status;
   
    }
    
}
