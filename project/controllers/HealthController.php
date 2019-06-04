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
use project\models\CodehubExec;


class HealthController extends Controller { 
    
    public function actions()
    {
        return [
            'index' => [     
                'class' => IndexAction::className(),
                'data' => function(){
            
//            $repository_uuid='8ed8d2b46313434c98f8c6a617ffe897';
//            $token=CorporationAccount::get_token(4);
//                    $auth=CurlHelper::getCodehub($repository_uuid, $token);
//                    var_dump($auth);
//                    exit;
                    
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
                    $url= Yii::$app->request->referrer;
                    if(substr($url, strrpos($url, '/')+1,5)=='index'){
                        Yii::$app->session->set('memberlist_url',Yii::$app->request->referrer);
                    }
                                  
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
            'codehub-list' => [     
                'class' => IndexAction::className(),
                'ajax'=>true,
                'data' => function(){ 
                
                    $url= Yii::$app->request->referrer;
                    if(substr($url, strrpos($url, '/')+1,5)=='index'){
                        Yii::$app->session->set('memberlist_url',Yii::$app->request->referrer);
                    }
                    
                    $corporation_id=Yii::$app->request->get('id'); 
                    CorporationCodehub::set_corporation_codehub_list($corporation_id);
               
                    $dataProvider = new ActiveDataProvider([
                        'query' => CorporationCodehub::find()->andWhere(['corporation_id'=> $corporation_id]),
                        'sort'=>['defaultOrder' => [
                            'id' => SORT_ASC,
                        ]]
                    ]);
                    return [
                        'dataProvider' => $dataProvider,
                        'corporation_id'=>$corporation_id
                    ];
                              
                }
            ],
                    
        ];
    }
    
    //添加用户
    public function actionAccountAdd($corporation_id) {
        $corporation = Corporation::findOne($corporation_id);
        
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

                    CorporationAccount::set_corporation_account_list($corporation_id);
                    
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
    
    //创建用户
    public function actionAccountCreate($corporation_id) {
        $corporation = Corporation::findOne($corporation_id);
        
        $model = new CorporationAccount();
        $model->scenario='create';
        $model->corporation_id=$corporation_id;
        $model->account_name=$corporation->huawei_account;
        $model->is_admin= CorporationAccount::ADMIN_NO;         
        $model->add_type= CorporationAccount::TYPE_SYSTEM;
              
        $model->user_name= CorporationAccount::get_last_username($corporation_id);
        $model->password=substr(md5($model->account_name.$model->user_name),0,8).'a1';
        $token = CorporationAccount::get_token($corporation_id, CorporationAccount::ADMIN_YES);
        
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
    
    public function actionAccountDelete($id){
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
    
    public function actionProjectCreate($corporation_id) {
        
        $model = new CorporationProject();       
        $model->corporation_id=$corporation_id;
        $model->name='demo2019';
        $model->description= '';
        $model->add_type= CorporationProject::TYPE_ADD;
        
        $token = CorporationAccount::get_token($corporation_id);
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
    
    public function actionMemberList($corporation_id) {
       
        
        $model = CorporationProject::findOne(['corporation_id'=>$corporation_id]);
        
        $members=[];
        $token = CorporationAccount::get_token($corporation_id);
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
            return $this->redirect(Yii::$app->session->get('memberlist_url'));
            
        }else{                        
                      
            return $this->renderAjax('member-list', [
                        'model' => $model,
        ]);
            
        }
        
    }
    
    public function actionCodehubCreate($corporation_id) {
       
        $project = CorporationProject::findOne(['corporation_id'=>$corporation_id]);
        
        $model = new CorporationCodehub(); 
        $model->loadDefaultValues();
        $model->corporation_id=$corporation_id;
        $model->project_id=$project->id;
        $model->repository_name= CorporationCodehub::get_last_codehubname($corporation_id);
        $model->project_uuid=$project->project_uuid;
                   
        $token = CorporationAccount::get_token($corporation_id);
        $auth = CurlHelper::addCodehub($project->project_uuid,$model->repository_name,$token);
        if($auth['code']=='200'&&$auth['content']['status']=='success'){
            $model->repository_uuid=$auth['content']['result']['repository_uuid'];
            sleep(2);
            $auth1 = CurlHelper::getCodehub($model->repository_uuid, $token);
            if($auth1['code']=='200'&&$auth1['content']['status']=='success'){

                $model->https_url=$auth1['content']['result']['https_url'];
                $model->status=0;

                $model->add_type= CorporationCodehub::TYPE_SYSTEM;
                $model->created_at= strtotime($auth1['content']['result']['created_at']);
                $model->updated_at=0;
                $model->ci= CorporationCodehub::CI_NO;
            }else{
                CurlHelper::deleteCodehub($model->repository_uuid,$token);
            }
        }
        
        if($model->https_url){
            if($model->save()){
                return json_encode(['stat'=>'success']);
            }else{
                Yii::$app->session->setFlash('error', '操作失败。'. json_encode($model->getErrors()));
            }
        }else{
            Yii::$app->session->setFlash('error', '请求失败。'. json_encode($auth1));
        }
           
        return $this->redirect(Yii::$app->request->referrer);
        
    }
    
    public function actionCodehubUpdate($id) {
        $model = $codehub=CorporationCodehub::findOne($id);
        $model->scenario='update';
        
        $corporation_account = CorporationAccount::find()->where(['corporation_id'=>$model->corporation_id,'add_type'=>[CorporationAccount::TYPE_ADD, CorporationAccount::TYPE_SYSTEM]])->orderBy(['is_admin'=>SORT_ASC,'add_type'=>SORT_ASC,'id'=>SORT_ASC])->one();  
        $model->username=$corporation_account->account_name.'/'.$corporation_account->user_name;
        $model->password=$corporation_account->password;
        
        if ($model->load(Yii::$app->request->post())) {
            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
            

            $model->left_num=$model->total_num;
            
            $targetFolder = '/data/git';
            $targetPath = Yii::getAlias('@webroot') . $targetFolder;

            if (!file_exists($targetPath)) {
                @mkdir($targetPath, 0777, true);
            }
                        
            if(!file_exists($targetPath.'/'.$model->id)||$model->username!=$codehub->username||$model->password!=$codehub->password){        
                
                if (file_exists($targetPath.'/'.$model->id)) {
                    if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
                        $comm='cd '.$targetPath.' && rd/s/q '.$model->id;
                    }else{
                        $comm='cd '.$targetPath.' && sudo rm -rf '.$model->id;
                    } 
                    exec($comm.' >>demo.log');
                }


                $command='cd '.$targetPath.' && git clone https://'. urlencode(trim($model->username)).':'.urlencode(trim($model->password)).'@'. substr($model->https_url, 8).' '.$model->id;

                exec($command.' >>demo.log 2>&1',$output,$status);
            }         
            
            if(file_exists($targetPath.'/'.$model->id)&&$model->save()){
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
    
    public function actionCodehubDelete($id)
    {
        $model = CorporationCodehub::findOne($id);
        $stat='error';
        if ($model !== null) {
                       
            $status=0;
            if($model->username){
                $targetFolder = '/data/git';
                $targetPath = Yii::getAlias('@webroot') . $targetFolder;

                if (!file_exists($targetPath)) {
                    @mkdir($targetPath, 0777, true);
                }
                if (file_exists($targetPath.'/'.$model->id)) {
                    if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
                        $command='cd '.$targetPath.' && rd/s/q '.$model->id;
                    }else{
                        $command='cd '.$targetPath.' && sudo rm -rf '.$model->id;
                    } 
                    exec($command.' >>demo.log 2>&1',$output,$status);
                }
            }
            $auth['code']='200';
            if($model->add_type== CorporationCodehub::TYPE_SYSTEM){
                $auth=CurlHelper::deleteCodehub($model->repository_uuid,CorporationAccount::get_token($model->corporation_id));
            }
            if($status==0&&$auth['code']==200&&$model->delete()){
                $stat='success';
            }else{
                $stat='fail';
            }
        }

        return json_encode(['stat' => $stat]);
    }
    
    public function actionCodehubExec($id) {   
            
        $stat = CorporationCodehub::codehub_exec($id)?'success':'error';
        
        $exec = new CodehubExec();
        $exec->codehub_id=$id;
        $exec->user_id=Yii::$app->user->identity->id;
        $exec->updated_at=time();
        $exec->type= CodehubExec::TYPE_ADD;
        $exec->stat = $stat=='success'?CodehubExec::STAT_YES:CodehubExec::STAT_NO;
        $exec->save();
       
        return json_encode(['stat'=>$stat,'message'=>$stat=='success'?'执行成功':'执行失败']);

    }
    
}
