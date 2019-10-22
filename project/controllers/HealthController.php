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
use project\models\HealthLog;
use project\models\HealthText;
use project\models\HealthData;
use project\components\ExcelHelper;
use project\models\UserGroup;
use project\models\Group;
use project\models\CorporationBd;
use project\models\ColumnSetting;


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

                    $column= ColumnSetting::get_column_content(Yii::$app->user->identity->id,'health');
                    return [  
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'start' => $start,
                        'end' => $end,
                        'annual'=>$annual,
                        'sum'=>$sum,
                        'column'=>$column,
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
            'import-list' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $dataProvider = new ActiveDataProvider([
                        'query' => HealthLog::find()->andWhere(['or',['group_id'=> UserGroup::get_user_groupid(Yii::$app->user->identity->id)],['group_id'=>NULL]]),
                        'sort' => ['defaultOrder' => [                            
                            'id' => SORT_DESC,
                        ]],
                    ]);
                    return [
                        'dataProvider' => $dataProvider,
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
        $auth = CurlHelper::createUser($model,$token);
        
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
        $auth = CurlHelper::createProject($model,$token);
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
    
    public function actionCodehubDelete($id){
        $model = CorporationCodehub::findOne($id);
        $stat='error';
        if ($model !== null) {                      
            $stat = CorporationCodehub::codehub_delete($id)?'success':'error';
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
    
    public function actionImport() {       
        //判断是否Ajax
        if (Yii::$app->request->isAjax) {

            if (empty($_FILES['files'])) {
                $postMaxSize = ini_get('post_max_size');
                $fileMaxSize = ini_get('upload_max_filesize');
                $displayMaxSize = $postMaxSize < $fileMaxSize ? $postMaxSize : $fileMaxSize;

                return json_encode(['error' => '没有文件上传,文件最大为' . $displayMaxSize], JSON_UNESCAPED_UNICODE);
                // or you can throw an exception
            }


            //目标文件夹，不存在则创建
            $targetFolder = '/data/health_data';
            $targetPath = Yii::getAlias('@webroot') . $targetFolder;

            if (!file_exists($targetPath)) {
                @mkdir($targetPath, 0777, true);
            }

            $files = $_FILES['files'];
            $filenames = $files['name'];

            $ext = explode('.', basename($filenames[0]));
            $f_name = md5(uniqid()) . "." . strtolower(array_pop($ext));
            $filename = $targetPath . DIRECTORY_SEPARATOR . $f_name;
            //文件存在则删除
            if (file_exists($filename)) {
                @unlink($filename);
            }
            
            $model= new HealthLog();
            $model->stat= HealthLog::STAT_UPLOAD;
            $model->uid=Yii::$app->user->identity->id;
            $model->created_at=time();
            $model->name=$filenames[0];
            $model->patch=$f_name;
            $group = Group::get_user_group(Yii::$app->user->identity->id);
            if(count($group)==1){
                $model->group_id= key($group);   
            }
            
            if (@move_uploaded_file($files['tmp_name'][0], $filename)&&$model->save()) {                

                $format = \PHPExcel_IOFactory::identify($filename);
                $objectreader = \PHPExcel_IOFactory::createReader($format);
                $objectPhpExcel = $objectreader->load($filename);

                $dataArray = $objectPhpExcel->getActiveSheet()->toArray(null, true, true, true);

                $datas = ExcelHelper::execute_array_label($dataArray);

                //项目处理
                if(isset($datas[0])){                                           
                    $health_text=[];
                    foreach ($datas as $key=>$data) {
                        $health_text[]=['log_id'=>$model->id,'data'=> json_encode($data)];
                    }
                    
                    if(!empty($health_text)){
                        if(Yii::$app->db->createCommand()->batchInsert(HealthText::tableName(), ['log_id', 'data'], $health_text)->execute()){
                            Yii::$app->session->setFlash('success', '导入成功。');
                        }else{
                            $model->delete();
                            Yii::$app->session->setFlash('error', '导入失败。');
                        }
                    }                    
                    return $this->redirect(Yii::$app->request->referrer);
                    
                }else{
                    Yii::$app->session->setFlash('error', '没有有效数据');
                    //throw new \Exception('没有有效数据');
                    $model->delete();
                    return $this->redirect(Yii::$app->request->referrer);
                }
              
            }else{
                Yii::$app->session->setFlash('error', '导入失败。');
            }           
        } else {
            Yii::$app->session->setFlash('error', '导入失败。');
        }
        return true;
        
    }
    
    public function actionInduce($id) {       
        $model = HealthLog::findOne($id);
        if ($model !== null) {
            if($model->statistics_at&&$model->group_id){
                          
            $transaction = Yii::$app->db->beginTransaction();
            try {               
                
                HealthData::deleteAll(['log_id'=>$model->id]);

                $datas = HealthText::find()->where(['log_id'=>$model->id])->select(['data'])->column();

                //项目处理
                if(isset($datas[0])){
                                      
                    $data_v= json_decode($datas[0],true);//去除0值和空值
                    $keys= array_filter(array_keys($data_v));                                               
                    
                   
                    $field_huawei_account= in_array('用户名', $keys)?'用户名':null;
                    $field_corporation_name=in_array('客户名称', $keys)?'客户名称':null;
                    if(!$field_huawei_account||!preg_match("/[\w|-]{6,32}$/",$data_v[$field_huawei_account])){
                        Yii::$app->session->setFlash('error', '文件首行不存在、还未设置或设置错误<<用户名>>字段');
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                    if(!$field_corporation_name){
                        Yii::$app->session->setFlash('error', '文件首行不存在或还未设置<<客户名称>>字段');
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                }else{
                    Yii::$app->session->setFlash('error', '没有有效数据');
                    return $this->redirect(Yii::$app->request->referrer);
                }

                $model_import_data=[];
                $corporations = Corporation::find()->where(['not',['huawei_account'=>NULL]])->select(['id','huawei_account'])->indexBy('huawei_account')->column();
                $corporation_bd= CorporationBd::get_bd_by_time($model->statistics_at);
                foreach ($datas as $data) {
//                    Yii::$app->session->setFlash('success', json_encode($datas,256));
//                    return true;

                    //数据处理
                    $data= array_filter(json_decode($data,true));//去除0值和空值


                    //$corporation= Corporation::findOne(['huawei_account'=>trim($data[$field_huawei_account])]);
                    if(!array_key_exists(trim($data[$field_huawei_account]), $corporations)){
                        //不存在
                        $corporation=new Corporation();
                        $corporation->loadDefaultValues();
                        $corporation->group_id=$model->group_id;
                        $corporation->huawei_account=trim($data[$field_huawei_account]);
                        $corporation->base_company_name=$field_corporation_name&&isset($data[$field_corporation_name])?trim($data[$field_corporation_name]):trim($data[$field_huawei_account]);
                        $corporation->save(false); 
                        $corporation_id=$corporation->id;
                    }else{
                        $corporation_id=$corporations[trim($data[$field_huawei_account])];
                    }


                    if(isset($data['最近一天是否活跃'])){
                        $activity_day=$data['最近一天是否活跃']=='活跃'?HealthData::ACT_Y:HealthData::ACT_N;
                    }else{
                        $activity_day= HealthData::ACT_D;
                    }
                    if(isset($data['最近一周是否活跃'])){
                        $activity_week=$data['最近一周是否活跃']=='活跃'?HealthData::ACT_Y:HealthData::ACT_N;
                    }else{
                        $activity_week= HealthData::ACT_D;
                    }
                    if(isset($data['最近一月是否活跃'])){
                        $activity_month=$data['最近一月是否活跃']=='活跃'?HealthData::ACT_Y:HealthData::ACT_N;
                    }else{
                        $activity_month= HealthData::ACT_D;
                    }
                    
                    if(isset($data['日活跃与否'])){
                        $activity_day=$data['日活跃与否']=='✔'?HealthData::ACT_Y:HealthData::ACT_N;
                    }else{
                        $activity_day= HealthData::ACT_D;
                    }
                    if(isset($data['周活跃与否'])){
                        $activity_week=$data['周活跃与否']=='✔'?HealthData::ACT_Y:HealthData::ACT_N;
                    }else{
                        $activity_week= HealthData::ACT_D;
                    }
                    if(isset($data['月活跃与否'])){
                        $activity_month=$data['月活跃与否']=='✔'?HealthData::ACT_Y:HealthData::ACT_N;
                    }else{
                        $activity_month= HealthData::ACT_D;
                    }
                    
                    
                    if(isset($data['成长等级H'])){
                        switch (substr($data['成长等级H'], 0, 2)){
                            case 'H1':$level= HealthData::HEALTH_H1;break;
                            case 'H2':$level= HealthData::HEALTH_H2;break;
                            case 'H3':$level= HealthData::HEALTH_H3;break;
                            case 'H4':$level= HealthData::HEALTH_H4;break;
                            case 'H5':$level= HealthData::HEALTH_H5;break;
                            default:$level= HealthData::HEALTH_WA;
                            
                        }
                    }else{
                        $level= HealthData::HEALTH_WA;
                    }
                    $H=isset($data['健康度H'])?$data['健康度H']:0;
                    $V=isset($data['V'])?$data['V']:0;
                    $D=isset($data['D'])?$data['D']:0;
                    $C=isset($data['C'])?$data['C']:0;
                    $I=isset($data['I'])?$data['I']:0;
                    $A=isset($data['A'])?$data['A']:0;
                    $R=isset($data['R'])?$data['R']:0;
                       
                    $model_import_data[]=['log_id'=>$model->id,'group_id'=>$model->group_id,'corporation_id'=>$corporation_id,'bd_id'=>isset($corporation_bd[$corporation_id])?$corporation_bd[$corporation_id]:null,'statistics_time'=>$model->statistics_at,'activity_day'=>$activity_day,'activity_week'=>$activity_week,'activity_month'=>$activity_month,'level'=>$level,'H'=>$H,'V'=>$V,'D'=>$D,'C'=>$C,'I'=>$I,'A'=>$A,'R'=>$R];

                }
                if(!empty($model_import_data)){
                    Yii::$app->db->createCommand()->batchInsert(HealthData::tableName(), ['log_id','group_id', 'corporation_id','bd_id','statistics_time','activity_day','activity_week','activity_month','level','H','V','D','C','I','A','R'], $model_import_data)->execute();
                }
                
                //状态变化
                HealthLog::updateAll(['stat'=> HealthLog::STAT_COVER], ['and',['statistics_at'=>$model->statistics_at,'group_id'=>$model->group_id],['not',['id'=>$model->id]]]);
                $model->stat= HealthLog::STAT_INDUCE;
                $model->save();
                
                //生成区间活跃数据
                
                //后部分区间
                $next_time= HealthData::get_next_time($model->statistics_at,$model->group_id);
                if($next_time){                   
                    HealthData::updateAll(['act_trend'=> HealthData::TREND_WA,'health_trend'=> HealthData::TREND_WA],['statistics_time'=> $next_time,'group_id'=>$model->group_id]);                       
                }

                //设置下拨
                HealthData::set_allocate();

                //设置活跃趋势
                HealthData::set_activity_trend();

                //设置健康趋势
                HealthData::set_health_trend();

                //清除缓存
                Yii::$app->cache->delete('health');
                    
                $transaction->commit();
                Yii::$app->session->setFlash('success', '生成数据成功。');
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
                Yii::$app->session->setFlash('error', '生成数据失败。');
            }
                  
                  
            }else{
                Yii::$app->session->setFlash('error', '请先设置统计日期或项目');     
            }
        } else {
            Yii::$app->session->setFlash('error', '项目不存在。');
        }
       return $this->redirect(Yii::$app->request->referrer);
        
    }
    
    public function actionClean($id) {
        $model = HealthLog::findOne($id);
        if ($model !== null) {
            HealthData::deleteAll(['statistics_time'=>$model->statistics_at,'group_id'=>$model->group_id]);
 
            
            $next_time= HealthData::get_next_time($model->statistics_at,$model->group_id);
            if($next_time){
                HealthData::updateAll(['act_trend'=> HealthData::TREND_WA,'health_trend'=> HealthData::TREND_WA],['statistics_time'=> $next_time,'group_id'=>$model->group_id]);
            }
            $model->stat= HealthLog::STAT_UPLOAD;
            $model->save();
            
            //设置下拨
            HealthData::set_allocate();

            //设置活跃趋势
            HealthData::set_activity_trend();

            //设置健康趋势
            HealthData::set_health_trend();

            //清除缓存
            Yii::$app->cache->delete('health');
//            Yii::$app->cache->flush();
                   
            
        } else {
            Yii::$app->session->setFlash('error', '项目不存在。');           
        }
        return $this->redirect(Yii::$app->request->referrer);
        
    }
    
    public function actionBind($id) {
        $model = HealthLog::findOne($id);
        
        if (Yii::$app->request->isPost&&$model->load(Yii::$app->request->post())) {
       
            $model->statistics_at= strtotime($model->statistics_at);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', '操作成功。');
            } else {
                Yii::$app->session->setFlash('error', '操作失败。');
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            if ($model !== null) {
                $model->statistics_at= $model->statistics_at>0?date('Y-m-d',$model->statistics_at):'';
                return $this->renderAjax('bind', [
                            'model' => $model,
                ]);
            } else {
                Yii::$app->session->setFlash('error', '项目不存在。');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
    }
    
    public function actionBindGroup($id) {
        $model = HealthLog::findOne($id);
        
        if (Yii::$app->request->isPost&&$model->load(Yii::$app->request->post())) {
       
            if ($model->save()) {
                Yii::$app->session->setFlash('success', '操作成功。');
            } else {
                Yii::$app->session->setFlash('error', '操作失败。');
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            if ($model !== null) {
                $model->scenario='group';
                return $this->renderAjax('bind-group', [
                            'model' => $model,
                ]);
            } else {
                Yii::$app->session->setFlash('error', '内容不存在。');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
    }
    
    public function actionColumn() {

        $model=ColumnSetting::get_column(Yii::$app->user->identity->id,'health');
        if($model==null){
            $model=new ColumnSetting();
            $model->uid=Yii::$app->user->identity->id;
            $model->type='health';
            
        }
        if ($model->load(Yii::$app->request->post())) {
            
            $column = Yii::$app->request->post('ColumnSetting');
            $model->content= json_encode($column['content']);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', '操作成功。');
            } else {
                Yii::$app->session->setFlash('error', '操作失败。');
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {
          
            $model->content= json_decode($model->content);
                
            return $this->renderAjax('column', [
                'model' => $model,
            ]);
      
        }
    }
    
   
    
}
