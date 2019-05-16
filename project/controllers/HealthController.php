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
        $model->corporation_id=$id;
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
    
}
