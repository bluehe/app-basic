<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use project\actions\IndexAction;
//use bluehe\phpexcel\Excel;
use project\models\Corporation;
use project\models\CorporationSearch;
use project\models\CorporationIndustry;
use project\actions\DeleteAction;
use project\actions\ViewAction;
use project\models\CorporationBd;
use project\models\CorporationStat;
use project\models\CorporationMeal;
//use rky\models\Parameter;
//use rky\models\User;
//use rky\models\Industry;
//use rky\components\ExcelHelper;

/**
 * CorporationController implements the CRUD actions for Corporation model.
 */
class CorporationController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    
     public function actions()
    {
        return [
            'corporation-list' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $searchModel = new CorporationSearch();
                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                    return [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                    ];
                }
            ],
            'corporation-delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => Corporation::className(),
            ],
            'corporation-view' => [
                'class' => ViewAction::className(),
                'modelClass' => Corporation::className(),
                'viewFile'=>'corporation-view',
                'ajax'=>true,
            ],
        ];
    }
    
    public function actionCorporationCreate() {
        $model = new Corporation();
        //$model->scenario='industry';
        $model->loadDefaultValues();
        if ($model->load(Yii::$app->request->post())) {
            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
                                               
            $rw = Yii::$app->request->post('Corporation');
            $industrys = $rw['base_industry']&&!is_array($rw['base_industry']) ? explode(',',$rw['base_industry']) : array();
            $model->develop_language = $rw['develop_language'] ? implode(',',$rw['develop_language']) : '';
            
                       
            $transaction = Yii::$app->db->beginTransaction();
            try {
                
                $model->base_registered_time= strtotime($model->base_registered_time);
//                $model->allocate_time= $model->stat== Corporation::STAT_ALLOCATE?strtotime($model->allocate_time):null;               
                $model->save(false);
                                               
                if (count($industrys) > 0) {
                    $industry = new CorporationIndustry();
                    $industry->corporation_id = $model->id;
                    foreach ($industrys as $t) {
                        $_industry = clone $industry;                 
                        $_industry->industry_id = $t;
                        if (!$_industry->save()) {
                            throw new \Exception("操作失败");
                        }
                    }
                }
                $transaction->commit();
                Yii::$app->session->setFlash('success', '操作成功。');
               
             } catch (\Exception $e) {

                $transaction->rollBack();
//                throw $e;
                Yii::$app->session->setFlash('error', '操作失败。');
            }
            return $this->redirect(Yii::$app->request->referrer);
            
        }else{
            $model->base_bd=Yii::$app->user->identity->id;
            return $this->renderAjax('corporation-create', [
                        'model' => $model,
                'allocate'=>null,
        ]);
            
        }
        
    }
    
    public function actionCorporationUpdate($id) {
        $model = Corporation::findOne($id);
        //$model->scenario='industry';
        if($model !== null&&Yii::$app->user->can('企业修改',['id'=>$id])){
            $model->base_industry =$old_industry= CorporationIndustry::get_corporation_industryid($model->id);
            $allocate =in_array($model->stat, [Corporation::STAT_ALLOCATE, Corporation::STAT_AGAIN])?CorporationMeal::get_allocate($model->id):null;
            if($allocate){
                $allocate->start_time=$allocate->start_time>0?date('Y-m-d',$allocate->start_time):'';
            }
           
            if ($model->load(Yii::$app->request->post())&&$allocate->load(Yii::$app->request->post())) {
            
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return \yii\bootstrap\ActiveForm::validate($model)&&\yii\bootstrap\ActiveForm::validate($allocate);
                }
            
                $rw = Yii::$app->request->post('Corporation');
                $industrys = $rw['base_industry']&&!is_array($rw['base_industry']) ? explode(',',$rw['base_industry']) : array();
                $model->develop_language = $rw['develop_language'] ? implode(',',$rw['develop_language']) : '';
           
            
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->base_registered_time= strtotime($model->base_registered_time);
                    //$model->allocate_time= $model->stat== Corporation::STAT_ALLOCATE?strtotime($model->allocate_time):null;               
                    $model->save(false);
                
                    $t1 = array_diff($industrys, $old_industry); //新增
                    $t2 = array_diff($old_industry, $industrys); //删除
                
                    if (count($t1) > 0) {
                        $industry = new CorporationIndustry();
                        $industry->corporation_id = $model->id;
                        foreach ($t1 as $t) {
                            $_industry = clone $industry;
                            $_industry->industry_id = $t;
                            if (!$_industry->save()) {
                                throw new \Exception("修改失败");
                            }
                        }
                    }
                    if (count($t2) > 0) {
                        CorporationIndustry::deleteAll(['corporation_id' => $model->id, 'industry_id' => $t2]);
                    }
                
                    $transaction->commit();                   
                    Yii::$app->session->setFlash('success', '修改成功。');
                } catch (\Exception $e) {

                    $transaction->rollBack();
//                throw $e;
                    Yii::$app->session->setFlash('error', '修改失败。');
                }
                return $this->redirect(Yii::$app->request->referrer);
            }else{
                $model->base_registered_time= $model->base_registered_time>0?date('Y-m-d',$model->base_registered_time):'';
                //$model->allocate_time= $model->allocate_time>0?date('Y-m-d',$model->allocate_time):'';
                $model->develop_language = explode(',', $model->develop_language);
            }
            return $this->renderAjax('corporation-update', [
                    'model' => $model,
                    'allocate'=>$allocate,
            ]);
        
        }else{
            Yii::$app->session->setFlash('error', '企业不存在或权限不足。');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    
    public function actionCorporationApply($id) {
        $model = Corporation::findOne($id);
        if($model !== null&&Yii::$app->user->can('企业修改',['id'=>$id])){
            $model->stat= Corporation::STAT_APPLY;
           
            if ($model->load(Yii::$app->request->post())) {
                                     
                if (Yii::$app->request->isAjax) {                
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return \yii\bootstrap\ActiveForm::validate($model);
                }          
          
                if($model->save(false)){
                    Yii::$app->session->setFlash('success', '申请成功。');
                }else{
                    Yii::$app->session->setFlash('error', '申请失败。');
                }
                return $this->redirect(Yii::$app->request->referrer);
            }
            return $this->renderAjax('corporation-apply', [
                    'model' => $model,
            ]);
        
        }else{
            Yii::$app->session->setFlash('error', '企业不存在或权限不足。');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    
    public function actionCorporationAllocate($id) {
        $corporation = Corporation::findOne($id);
        if($corporation !== null&&in_array($corporation->stat,[Corporation::STAT_CHECK,Corporation::STAT_ALLOCATE,Corporation::STAT_AGAIN,Corporation::STAT_OVERDUE])&&Yii::$app->user->can('企业修改',['id'=>$id])){
            $corporation->stat= $corporation->stat==Corporation::STAT_CHECK?Corporation::STAT_ALLOCATE:Corporation::STAT_AGAIN;
            $model = new CorporationMeal();
            $model->loadDefaultValues();
            $model->corporation_id=$id;
            $model->meal_id=$corporation->intent_set;
            $model->number=$corporation->intent_number;
            $model->huawei_account=$corporation->huawei_account;
           
            if ($model->load(Yii::$app->request->post())) {
                                     
                if (Yii::$app->request->isAjax) {                
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return \yii\bootstrap\ActiveForm::validate($model);
                }          
          
                $model->start_time= strtotime($model->start_time);
                $model->end_time = strtotime('+1 year', $model->start_time)-1;
                $model->bd = $corporation->base_bd;
                $model->user_id = Yii::$app->user->identity->id;
                $model->created_at = time();
                
                $corporation->huawei_account=$model->huawei_account;
                
                if($model->save()&&$corporation->save()){
                    Yii::$app->session->setFlash('success', '下拨成功。');
                }else{
                    Yii::$app->session->setFlash('error', '下拨失败。');
                }
                return $this->redirect(Yii::$app->request->referrer);
            }
            return $this->renderAjax('corporation-allocate', [
                    'model'=>$model
            ]);
        
        }else{
            Yii::$app->session->setFlash('error', '企业不存在或权限不足。');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    
//    public function actionCorporationDelete($id) {
//        $model = Corporation::findOne($id);
//        if ($model !== null&&Yii::$app->user->can('公司删除',['id'=>$id])) {
//            $model->delete();           
//        }
//
//        return $this->redirect(Yii::$app->request->referrer);
//    }
    
//    public function actionCorporationCheck($id) {
//        $model = Corporation::findOne($id);
//        if ($model !== null&&$model->stat== Corporation::STAT_APPLY&&Yii::$app->user->identity->role=='pm') {           
//            $model->stat = Corporation::STAT_CHECK;
//            $model->save(false);             
//        }
//
//        return $this->redirect(Yii::$app->request->referrer);
//    }
//    
    public function actionCorporationBd($id) {

        $model = CorporationBd::find()->where(['corporation_id'=>$id])->orderBy(['start_time'=>SORT_ASC])->all();

        return $this->renderAjax('corporation-bd', [
                    'model' => $model,
        ]);     
        
    }
    
    public function actionCorporationStat($id) {

        $model = CorporationStat::find()->where(['corporation_id'=>$id])->orderBy(['created_at'=>SORT_ASC])->all();

        return $this->renderAjax('corporation-stat', [
                    'model' => $model,
        ]);     
        
    }
    
    public function actionCorporationUpdateStat($id,$stat) {
        

        $model = Corporation::findOne($id);
        if(Yii::$app->user->can('企业修改',['id'=>$id])&&$model!=null){
            $model->stat=$stat;
            if($model->save()){
                Yii::$app->session->setFlash('success', '状态更改成功。');
            }else{
                Yii::$app->session->setFlash('error', '状态更改失败，'. current($model->getFirstErrors()));
            }
        }else{
            Yii::$app->session->setFlash('error', '企业不存在或权限不足。');
        }

        return $this->redirect(Yii::$app->getRequest()->headers['referer']); 
        
    }
    
//    public function actionCorporationImport1() {
//        //判断是否Ajax
//          if (Yii::$app->request->isAjax) {
//
//            if (empty($_FILES['files'])) {
//                $postMaxSize = ini_get('post_max_size');
//                $fileMaxSize = ini_get('upload_max_filesize');
//                $displayMaxSize = $postMaxSize < $fileMaxSize ? $postMaxSize : $fileMaxSize;
//
//                return json_encode(['error' => '没有文件上传,文件最大为' . $displayMaxSize], JSON_UNESCAPED_UNICODE);
//                // or you can throw an exception
//            }
//
//
//            //目标文件夹，不存在则创建
//            $targetFolder = '/data/corporation';
//            $targetPath = Yii::getAlias('@webroot') . $targetFolder;
//
//            if (!file_exists($targetPath)) {
//                @mkdir($targetPath, 0777, true);
//            }
//
//            $files = $_FILES['files'];
//            $filenames = $files['name'];
//
//            $ext = explode('.', basename($filenames[0]));
//            $f_name = md5(uniqid()) . "." . strtolower(array_pop($ext));
//            $filename = $targetPath . DIRECTORY_SEPARATOR . $f_name;
//            //文件存在则删除
//            if (file_exists($filename)) {
//                @unlink($filename);
//            }
//            
//            if (@move_uploaded_file($files['tmp_name'][0], $filename)) {
//               
//                $datas = Excel::import($filename, ['headerTitle' => true, 'setFirstRecordAsKeys' => true,]);
//                
//                $bd=User::get_bd();
//                $base_industry= Industry::get_industry_children();
//                $stat= Corporation::$List['stat'];
//                $allocate_set= Corporation::$List['allocate_set'];
//                $amount=Corporation::$List['allocate_amount'];
//                $contact_park=Parameter::get_type('contact_park');
//                $develop_pattern=Parameter::get_type('develop_pattern');
//                $develop_scenario=Parameter::get_type('develop_scenario');
//                $develop_science=Parameter::get_type('develop_science');
//                $develop_language=Parameter::get_type('develop_language');
//                $develop_IDE=Parameter::get_type('develop_IDE');
//               
//                
//                foreach ($datas as $key=>$data) {
////                    Yii::$app->session->setFlash('success', json_encode($datas,256));
////                    return true;
//                    if($key==0){
//                        //项目处理
//                        $keys= array_filter(array_keys($data));
//                        if(!in_array('公司名称', $keys)){
//                            Yii::$app->session->setFlash('error', '文件首行不存在<<公司名称>>字段或文件存在多个sheet');
//                            break;
//                        }
//                    }                            
//                    //数据处理
//                    $data= array_filter($data);//去除0值和空值
//                   
//                    if(isset($data['公司名称'])){
//                        $company = Corporation::findOne(['base_company_name'=>$data['公司名称']]);
//
//                        if($company===null){
//                            //不存在
//                            $company=new Corporation();
//                            $company->base_company_name=$data['公司名称'];
//                        }
//                            
//                            if(isset($data['客户经理'])&&array_search($data['客户经理'], $bd)){
//                                $company->base_bd= array_search($data['客户经理'], $bd);
//                            }
//                            if(isset($data['状态'])&&array_search($data['状态'], $stat)){
//                                $company->stat= array_search($data['状态'], $stat);
//                            }
//                            if(isset($data['意向套餐'])&&array_search($data['意向套餐'], $allocate_set)){
//                                $company->intent_set= array_search($data['意向套餐'], $allocate_set);
//                            }
//                            if(isset($data['华为云账号'])){
//                                $company->huawei_account= $data['华为云账号'];
//                            }
//                            if(isset($data['下拨金额(万元)'])){
//                                $company->allocate_set= array_search($data['下拨金额(万元)'], $amount)?array_search($data['下拨金额(万元)'], $amount):null;
//                                $company->allocate_amount=$data['下拨金额(万元)'];
//                            }
//                            if(isset($data['下拨日期'])&&strtotime($data['下拨日期'])){
//                                $company->allocate_time= strtotime($data['下拨日期']);
//                            }
//                            if(isset($data['主营业务'])){
//                                $company->base_main_business= $data['主营业务'];
//                            }
//                            if(isset($data['注册日期'])&&strtotime($data['注册日期'])){
//                                $company->base_registered_time= strtotime($data['注册日期']);
//                            }
//                            if(isset($data['近一年营业收入'])){
//                                $company->base_last_income= $data['近一年营业收入'];
//                            }
//                            if(isset($data['注册资金(万元)'])){
//                                $company->base_registered_capital= $data['注册资金(万元)'];
//                            }
//                           
//                            if(isset($data['企业规模'])){
//                                $company->base_company_scale= $data['企业规模'];
//                            }
//                            if(isset($data['所属园区'])&&array_search($data['所属园区'], $contact_park)){
//                                $company->contact_park= array_search($data['所属园区'], $contact_park);
//                            }
//                            if(isset($data['实际地址'])){
//                                $company->contact_address= $data['实际地址'];
//                            }
//                            if(isset($data['商业联系人'])){
//                                $company->contact_business_name= $data['商业联系人'];
//                            }
//                            if(isset($data['商业联系人职务'])){
//                                $company->contact_business_job= $data['商业联系人职务'];
//                            }
//                            if(isset($data['商业联系人电话'])){
//                                $company->contact_business_tel= (string)$data['商业联系人电话'];
//                            }
//                            if(isset($data['技术联系人'])){
//                                $company->contact_technology_name= $data['技术联系人'];
//                            }
//                            if(isset($data['技术联系人职务'])){
//                                $company->contact_technology_job= $data['技术联系人职务'];
//                            }
//                            if(isset($data['技术联系人电话'])){
//                                $company->contact_technology_tel= (string)$data['技术联系人电话'];
//                            }
//                            if(isset($data['研发规模'])){
//                                $company->develop_scale= $data['研发规模'];
//                            }
//                            if(isset($data['开发模式'])&&array_search($data['开发模式'], $develop_pattern)){
//                                $company->develop_pattern= array_search($data['开发模式'], $develop_pattern);
//                            }
//                            if(isset($data['开发场景'])&&array_search($data['开发场景'], $develop_scenario)){
//                                $company->develop_scenario= array_search($data['开发场景'], $develop_scenario);
//                            }
//                            if(isset($data['开发环境'])&&array_search($data['开发环境'], $develop_science)){
//                                $company->develop_science= array_search($data['开发环境'], $develop_science);
//                            }
//                            if(isset($data['开发语言'])){
//                                $ls= explode(',', $data['开发语言']);
//                                $dl=[];
//                                foreach($ls as $l){
//                                    if(array_search($l, $develop_language)){
//                                        $dl[]=array_search($l, $develop_language);
//                                    }
//                                }
//                                $company->develop_language= implode(',', $dl);
//                            }
//                            if(isset($data['开发IDE'])&&array_search($data['开发IDE'], $develop_IDE)){
//                                $company->develop_IDE= array_search($data['开发IDE'], $develop_IDE);
//                            }
//                            if(isset($data['研发工具现状'])){
//                                $company->develop_current_situation= $data['研发工具现状'];
//                            }
//                            if(isset($data['研发痛点'])){
//                                $company->develop_weakness= $data['研发痛点'];
//                            }
//                            $company->save(); 
////                            if($company->getErrors()){
////                                return json_encode($company->getErrors(),256);
////                            }
//                            if(isset($data['行业'])){
//                                CorporationIndustry::deleteAll(['corporation_id'=>$company->id]);
//                                $industry= new CorporationIndustry();
//                                $industry->corporation_id=$company->id;
//                                $industrys= explode(',', $data['行业']);
//                               
//                                foreach($industrys as $i){
//                                    if(array_search($i, $base_industry)){
//                                        $_industry =clone $industry;
//                                        $_industry->industry_id=array_search($i, $base_industry);
//                                        $_industry->save();
//                                    }
//                                }
//                               
//                            }
//                       
//
//                    }
//                                    
//            }
//             Yii::$app->session->setFlash('success', '上传成功。');
//                  
//            }else{
//                Yii::$app->session->setFlash('error', '上传失败。');
//            }           
//        } else {
//            Yii::$app->session->setFlash('error', '上传失败。');
//        }
//        return true;
//        
//    }
    
//    public function actionCorporationExport1() {
//        $searchModel = new CorporationSearch();
//        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,1000);
////        $model = RepairOrder::find()->joinWith('type')->joinWith('area')->joinWith('worker')->all();
////        var_dump(Yii::$app->request->queryParams);
////        exit;
//        sleep(1);
//        Excel::export([
//            'models' => $dataProvider->getModels(),
//            'fileName' => '企业信息(' . date('Y-m-d', time()) . ')',
//            'format' => 'Excel2007',
//            'style' => ['row_height' => 15,'font_name' => '微软雅黑', 'font_size' => 11, 'alignment_horizontal' => 'center', 'alignment_vertical' => 'center'],
//            'headerTitle' => false,
//            'firstTitle' => ['font_bold' => true, 'row_height' => 20,'wrap_text'=>true,'fill_color'=>'FF00A65A', 'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//            'columns' => [
//                [
//                    'attribute' => 'base_company_name',
//                    'style' => ['column_width' => 32,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                        'attribute' => 'base_bd',
//                        'value' =>
//                        function($model) {
//                            return $model->base_bd?($model->baseBd->nickname?$model->baseBd->nickname:$model->baseBd->username):'';   //主要通过此种方式实现
//                        },
//                        'style' => ['column_width' => 8,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'base_industry',
//                    'value' =>
//                        function($model) {
//                            return $model->get_industry($model->id);   //主要通过此种方式实现
//                        },
//                    'style' => ['column_width' => 20,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                        'attribute' => 'stat',
//                        'value' =>
//                        function($model) {
//                            return $model->Stat;   //主要通过此种方式实现
//                        },
//                        'style' => ['column_width' => 8,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'intent_set',
//                    'value' =>
//                        function($model) {
//                            return $model->IntentSet;   //主要通过此种方式实现
//                        },
//                    'style' => ['column_width' => 8,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'huawei_account',
//                    'style' => ['column_width' => 16,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                
//                [
//                    'attribute' => 'allocate_amount',
//                    'style' => ['column_width' => 14,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'allocate_time', 
//                    'value' =>
//                        function($model) {
//                            return $model->allocate_time>0?date('Y-m-d',$model->allocate_time):'';   //主要通过此种方式实现
//                        },
//                    'style' => ['column_width' => 12, 'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]]
//                ],
//                
//                [
//                    'attribute' => 'base_main_business',
//                    'style' => ['column_width' => 30,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'base_registered_time', 
//                    'value' =>
//                        function($model) {
//                            return $model->base_registered_time>0?date('Y-m-d',$model->base_registered_time):'';   //主要通过此种方式实现
//                        },
//                    'style' => ['column_width' => 12, 'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]]
//                ],
//                [
//                    'attribute' => 'base_last_income',
//                    'style' => ['column_width' => 13,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'base_registered_capital',
//                    'style' => ['column_width' => 14,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                
//                [
//                    'attribute' => 'base_company_scale',
//                    'style' => ['column_width' => 8,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'contact_park',
//                    'value' =>
//                        function($model) {
//                            return implode(',', Parameter::get_para_value('contact_park',$model->contact_park));   //主要通过此种方式实现
//                        },
//                    'style' => ['column_width' => 10,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'contact_address',
//                    'style' => ['alignment_horizontal' => 'left','column_width' => 30,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'contact_business_name',
//                    'style' => ['column_width' => 12,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'contact_business_job',
//                    'style' => ['column_width' => 14,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'contact_business_tel',
//                    'style' => ['column_width' => 14,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'contact_technology_name',
//                    'style' => ['column_width' => 12,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'contact_technology_job',
//                    'style' => ['column_width' => 14,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'contact_technology_tel',
//                    'style' => ['column_width' => 14,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'develop_scale',
//                    'style' => ['column_width' => 8,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'develop_pattern',
//                    'value' =>
//                        function($model) {
//                            return implode(',', Parameter::get_para_value('develop_pattern',$model->develop_pattern));   //主要通过此种方式实现
//                        },
//                    'style' => ['column_width' => 10,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'develop_scenario',
//                    'value' =>
//                        function($model) {
//                            return implode(',', Parameter::get_para_value('develop_scenario',$model->develop_scenario));   //主要通过此种方式实现
//                        },
//                    'style' => ['column_width' => 14,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'develop_science',
//                    'value' =>
//                        function($model) {
//                            return implode(',', Parameter::get_para_value('develop_science',$model->develop_science));   //主要通过此种方式实现
//                        },
//                    'style' => ['column_width' => 10,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'develop_language',
//                    'value' =>
//                        function($model) {
//                            return implode(',', Parameter::get_para_value('develop_language',explode(',',$model->develop_language)));   //主要通过此种方式实现
//                        },
//                    'style' => ['column_width' => 16,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'develop_IDE',
//                    'value' =>
//                        function($model) {
//                            return implode(',', Parameter::get_para_value('develop_IDE',$model->develop_IDE));   //主要通过此种方式实现
//                        },
//                    'style' => ['column_width' => 10,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'develop_current_situation',
//                    'style' => ['alignment_horizontal' => 'left','column_width' => 20,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//                [
//                    'attribute' => 'develop_weakness',
//                    'style' => ['alignment_horizontal' => 'left','column_width' => 20,'from_array' => ['borders' => ['outline' => ['style' => 'thin', 'color' => ['argb' => 'FF000000']]]]],
//                ],
//               
//            ],
//            'headers' => [
//            'base_company_name' => '公司名称',
//            'base_bd' => '客户经理',
//            'base_industry'=>'行业',
//            'base_company_scale' => '企业规模',
//            'base_registered_capital' => '注册资金(万元)',
//            'base_registered_time' => '注册日期',
//            'base_main_business' => '主营业务',
//            'base_last_income' => '近一年营业收入',
//            'stat' => '状态',
//            'intent_set' => '意向套餐',
//            'huawei_account' => '华为云账号',
//            'allocate_set' => '下拨套餐',
//            'allocate_amount' => '下拨金额(万元)',
//            'allocate_time' => '下拨日期',
//            'contact_park' => '所属园区',
//            'contact_address' => '实际地址',
//            'contact_business_name' => '商业联系人',
//            'contact_business_job' => '商业联系人职务',
//            'contact_business_tel' => '商业联系人电话',
//            'contact_technology_name' => '技术联系人',
//            'contact_technology_job' => '技术联系人职务',
//            'contact_technology_tel' => '技术联系人电话',
//            'develop_scale' => '研发规模',
//            'develop_pattern' => '开发模式',
//            'develop_scenario' => '开发场景',
//            'develop_science' => '开发环境',
//            'develop_language' => '开发语言',
//            'develop_IDE' => '开发IDE',
//            'develop_current_situation' => '研发工具现状',
//            'develop_weakness' => '研发痛点',
//            ],
//        ]);
//
//        return $this->redirect(Yii::$app->request->referrer);
//    }
//    
//    public function actionCorporationTemple() {
//        
//        $start_time= microtime(true);
//        
//        $fileName= Yii::getAlias('@webroot').'/excel/corporation_temple.xlsx';
//        $format = \PHPExcel_IOFactory::identify($fileName);
//        $objectreader = \PHPExcel_IOFactory::createReader($format);
//        $objectPhpExcel = $objectreader->load($fileName);
//  
//        $objSheet = $objectPhpExcel->getSheetByName('企业信息'); //这一句为要设置数据有效性的单元格  
//        ExcelHelper::set_corporation_excel($objSheet);
//        $end_time= microtime(true);
//        if($end_time-$start_time<1){
//            sleep(1);
//        }
//        
//        ExcelHelper::excel_set_headers($format,'企业信息');
//        
//        $objectwriter = \PHPExcel_IOFactory::createWriter($objectPhpExcel, $format);
//        $path = 'php://output';
//        $objectwriter->save($path);        
//        exit();
//        
//    }
//
//    public function actionCorporationExport() {
//        $start_time= microtime(true);
//        $searchModel = new CorporationSearch();
//        $models = $searchModel->search(Yii::$app->request->queryParams,1000)->getModels();
////        $model = RepairOrder::find()->joinWith('type')->joinWith('area')->joinWith('worker')->all();
////        var_dump(Yii::$app->request->queryParams);
////        exit;
//        $fileName= Yii::getAlias('@webroot').'/excel/corporation_temple.xlsx';
//        $format = \PHPExcel_IOFactory::identify($fileName);
//        $objectreader = \PHPExcel_IOFactory::createReader($format);
//        $objectPhpExcel = $objectreader->load($fileName);
//  
//        $objSheet = $objectPhpExcel->getSheetByName('企业信息'); //这一句为要设置数据有效性的单元格  
//        ExcelHelper::set_corporation_excel($objSheet);
//        
//        foreach($models as $key=>$model){
//            $k=$key+2;
//            $objSheet->setCellValue( 'A'.$k, $model->base_company_name)
//                    ->setCellValue( 'B'.$k, $model->base_bd?($model->baseBd->nickname?$model->baseBd->nickname:$model->baseBd->username):'')
//                    ->setCellValue( 'C'.$k, $model->get_industry($model->id))
//                    ->setCellValue( 'D'.$k, $model->Stat)
//                    ->setCellValue( 'E'.$k, $model->IntentSet)
//                    ->setCellValue( 'F'.$k, $model->huawei_account)
//                    ->setCellValue( 'G'.$k, $model->allocate_amount)
//                    ->setCellValue( 'H'.$k, $model->allocate_time>0?date('Y-m-d',$model->allocate_time):'')
//                    ->setCellValue( 'I'.$k, $model->base_main_business)
//                    ->setCellValue( 'J'.$k, $model->base_registered_time>0?date('Y-m-d',$model->base_registered_time):'')
//                    ->setCellValue( 'K'.$k, $model->base_last_income)
//                    ->setCellValue( 'L'.$k, $model->base_registered_capital)
//                    ->setCellValue( 'M'.$k, $model->base_company_scale)
//                    ->setCellValue( 'N'.$k, implode(',', Parameter::get_para_value('contact_park',$model->contact_park)))
//                    ->setCellValue( 'O'.$k, $model->contact_address)
//                    ->setCellValue( 'P'.$k, $model->contact_business_name)
//                    ->setCellValue( 'Q'.$k, $model->contact_business_job)
//                    ->setCellValue( 'R'.$k, $model->contact_business_tel)
//                    ->setCellValue( 'S'.$k, $model->contact_technology_name)
//                    ->setCellValue( 'T'.$k, $model->contact_technology_job)
//                    ->setCellValue( 'U'.$k, $model->contact_technology_tel)
//                    ->setCellValue( 'V'.$k, $model->develop_scale)
//                    ->setCellValue( 'W'.$k, implode(',', Parameter::get_para_value('develop_pattern',$model->develop_pattern)))
//                    ->setCellValue( 'X'.$k, implode(',', Parameter::get_para_value('develop_scenario',$model->develop_scenario)))
//                    ->setCellValue( 'Y'.$k, implode(',', Parameter::get_para_value('develop_science',$model->develop_science)))
//                    ->setCellValue( 'Z'.$k, implode(',', Parameter::get_para_value('develop_language',explode(',',$model->develop_language))))
//                    ->setCellValue( 'AA'.$k, implode(',', Parameter::get_para_value('develop_IDE',$model->develop_IDE)))
//                    ->setCellValue( 'AB'.$k, $model->develop_current_situation)
//                    ->setCellValue( 'AC'.$k, $model->develop_weakness);
//            $line_stat= implode(',', Corporation::get_stat_list($model->stat));
//            //状态选择
//            $objSheet->getCell('D'.$k)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
//                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
//                -> setAllowBlank(false)  
//                -> setShowInputMessage(true)  
//                -> setShowErrorMessage(true)  
//                -> setShowDropDown(true)  
//                -> setErrorTitle('输入的值有误')  
//                -> setError('您输入的值不在下拉框列表内.')  
//                -> setPromptTitle('状态')  
//                -> setFormula1('"'.$line_stat.'"'); 
//                       
//        }
//        
//        
//        $end_time= microtime(true);
//        if($end_time-$start_time<1){
//            sleep(1);
//        }
//
//        ExcelHelper::excel_set_headers($format,'企业信息(' . date('Y-m-d', time()) . ')');
//        
//        $objectwriter = \PHPExcel_IOFactory::createWriter($objectPhpExcel, $format);
//        $path = 'php://output';
//        $objectwriter->save($path);        
//        exit();
//    }
//    
//    public function actionCorporationImport() {
//        //判断是否Ajax
//        if (Yii::$app->request->isAjax) {
//
//            if (empty($_FILES['files'])) {
//                $postMaxSize = ini_get('post_max_size');
//                $fileMaxSize = ini_get('upload_max_filesize');
//                $displayMaxSize = $postMaxSize < $fileMaxSize ? $postMaxSize : $fileMaxSize;
//
//                return json_encode(['error' => '没有文件上传,文件最大为' . $displayMaxSize], JSON_UNESCAPED_UNICODE);
//                // or you can throw an exception
//            }
//
//
////            $start_time= microtime(true);
//
//            $files = $_FILES['files'];  
//            
//            $fileName= $files['tmp_name'][0];
//            //$fileName= Yii::getAlias('@webroot').'/excel/1.xlsx';
//            $format = \PHPExcel_IOFactory::identify($fileName);
//            $objectreader = \PHPExcel_IOFactory::createReader($format);
//            $objectPhpExcel = $objectreader->load($fileName);
//            
//            $dataArray = $objectPhpExcel->getActiveSheet()->toArray(null, true, true, true);
//            
//            $datas = ExcelHelper::execute_array_label($dataArray);
//                
//            $bd=User::get_bd();
//            $base_industry= Industry::get_industry_children();
//            $stat= Corporation::$List['stat'];
//            $allocate_set= Corporation::$List['allocate_set'];
//            $amount=Corporation::$List['allocate_amount'];
//            $contact_park=Parameter::get_type('contact_park');
//            $develop_pattern=Parameter::get_type('develop_pattern');
//            $develop_scenario=Parameter::get_type('develop_scenario');
//            $develop_science=Parameter::get_type('develop_science');
//            $develop_language=Parameter::get_type('develop_language');
//            $develop_IDE=Parameter::get_type('develop_IDE');
//                
//            $num=['add'=>0,'update'=>0,'fail'=>0];
//               
//            $notice_error=[];    
//            foreach ($datas as $key=>$data) {
////                    Yii::$app->session->setFlash('success', json_encode($datas,256));
////                    return true;
//                if($key==0){
//                    //项目处理
//                    $keys= array_filter(array_keys($data));
//                    if(!in_array('公司名称', $keys)){
//                        Yii::$app->session->setFlash('error', '文件首行不存在<<公司名称>>字段');
//                        break;
//                    }
//                }                            
//                //数据处理
//                
//                $data= array_filter($data);//去除0值和空值
//                   
//                if(isset($data['公司名称'])){
//                    $company = Corporation::findOne(['base_company_name'=>trim($data['公司名称'])]);
//
//                    if($company===null){
//                        //不存在
//                        $num_key='add';
//                        $company=new Corporation();
//                        $company->base_company_name=trim($data['公司名称']);
//                    }else{
//                        $num_key='update';
//                    }
//                            
//                    if(isset($data['客户经理'])&&array_search(trim($data['客户经理']), $bd)){
//                        $company->base_bd= array_search(trim($data['客户经理']), $bd);
//                    }
//                    if(isset($data['状态'])&&array_search(trim($data['状态']), $stat)){
//                        $company->stat= array_search(trim($data['状态']), $stat);
//                    }
//                    if(isset($data['意向套餐'])&&array_search(trim($data['意向套餐']), $allocate_set)){
//                        $company->intent_set= array_search(trim($data['意向套餐']), $allocate_set);
//                    }
//                    if(isset($data['华为云账号'])){
//                        $company->huawei_account= trim($data['华为云账号']);
//                    }
//                    if(isset($data['下拨金额(万元)'])){
//                        $data['下拨金额(万元)']=trim($data['下拨金额(万元)']);
//                        $company->allocate_set= array_search($data['下拨金额(万元)'], $amount)?array_search($data['下拨金额(万元)'], $amount):null;
//                        $company->allocate_amount=$data['下拨金额(万元)'];
//                    }
//                    if(isset($data['下拨日期'])&&strtotime($data['下拨日期'])){
//                        $company->allocate_time= strtotime($data['下拨日期']);
//                    }
//                    if(isset($data['主营业务'])){
//                        $company->base_main_business= trim($data['主营业务']);
//                    }
//                    if(isset($data['注册日期'])&&strtotime($data['注册日期'])){
//                        $company->base_registered_time= strtotime($data['注册日期']);
//                    }
//                    if(isset($data['近一年营业收入'])){
//                        $company->base_last_income= trim($data['近一年营业收入']);
//                    }
//                    if(isset($data['注册资金(万元)'])){
//                        $company->base_registered_capital= trim($data['注册资金(万元)']);
//                    }
//                           
//                    if(isset($data['企业规模'])){
//                        $company->base_company_scale= trim($data['企业规模']);
//                    }
//                    if(isset($data['所属园区'])){
//                        $data['所属园区']=trim($data['所属园区']);
//                        if(array_search($data['所属园区'], $contact_park)){
//                            $company->contact_park= array_search($data['所属园区'], $contact_park);
//                        }else{
//                            $company->contact_park= Parameter::add_type('contact_park', $data['所属园区']);
//                            $contact_park[$company->contact_park]=$data['所属园区'];
//                        }
//                    }
//                    if(isset($data['实际地址'])){
//                        $company->contact_address= trim($data['实际地址']);
//                    }
//                    if(isset($data['商业联系人'])){
//                        $company->contact_business_name= trim($data['商业联系人']);
//                    }
//                    if(isset($data['商业联系人职务'])){
//                        $company->contact_business_job= trim($data['商业联系人职务']);
//                    }
//                    if(isset($data['商业联系人电话'])){
//                        $company->contact_business_tel= trim((string)$data['商业联系人电话']);
//                    }
//                    if(isset($data['技术联系人'])){
//                        $company->contact_technology_name= trim($data['技术联系人']);
//                    }
//                    if(isset($data['技术联系人职务'])){
//                        $company->contact_technology_job= trim($data['技术联系人职务']);
//                    }
//                    if(isset($data['技术联系人电话'])){
//                        $company->contact_technology_tel= trim((string)$data['技术联系人电话']);
//                    }
//                    if(isset($data['研发规模'])){
//                        $company->develop_scale= trim($data['研发规模']);
//                    }
//                    if(isset($data['开发模式'])){
//                        $data['开发模式']=trim($data['开发模式']);
//                        if(array_search($data['开发模式'], $develop_pattern)){
//                            $company->develop_pattern= array_search($data['开发模式'], $develop_pattern);
//                        }else{
//                            $company->develop_pattern= Parameter::add_type('develop_pattern', $data['开发模式']);
//                            $develop_pattern[$company->develop_pattern]=$data['开发模式'];
//                        }
//                    }
//                    if(isset($data['开发场景'])){
//                        $data['开发场景']=trim($data['开发场景']);
//                        if(array_search($data['开发场景'], $develop_scenario)){
//                            $company->develop_scenario= array_search($data['开发场景'], $develop_scenario);
//                        }else{
//                            $company->develop_scenario= Parameter::add_type('develop_scenario', $data['开发场景']);
//                            $develop_scenario[$company->develop_scenario]=$data['开发场景'];
//                        }
//                    }
//                    if(isset($data['开发环境'])){
//                        $data['开发环境']=trim($data['开发环境']);
//                        if(array_search($data['开发环境'], $develop_science)){
//                            $company->develop_science= array_search($data['开发环境'], $develop_science);
//                        }else{
//                            $company->develop_science= Parameter::add_type('develop_science', $data['开发环境']);
//                            $develop_science[$company->develop_science]=$data['开发环境'];
//                        }
//                    }
//                    if(isset($data['开发语言'])){
//                        $ls= explode(',', str_replace('、',',',str_replace('，',',',$data['开发语言'])));
//                        $dl=[];
//                        foreach($ls as $l){
//                            $l=trim($l);
//                            if(array_search($l, $develop_language)){
//                                $dl[]=array_search($l, $develop_language);
//                            }else{
//                                $lid=Parameter::add_type('develop_language', $l);
//                                $dl[]=$lid;
//                                $develop_language[$lid]=$l;
//                            }
//                        }
//                        $company->develop_language= implode(',', $dl);
//                    }
//                    if(isset($data['开发IDE'])){
//                        $data['开发IDE']=trim($data['开发IDE']);
//                        if(array_search($data['开发IDE'], $develop_IDE)){
//                            $company->develop_IDE= array_search($data['开发IDE'], $develop_IDE);
//                        }else{
//                            $company->develop_IDE= Parameter::add_type('develop_IDE', $data['开发IDE']);
//                            $develop_IDE[$company->develop_IDE]=$data['开发IDE'];
//                        }
//                    }
//                    if(isset($data['研发工具现状'])){
//                        $company->develop_current_situation= trim($data['研发工具现状']);
//                    }
//                    if(isset($data['研发痛点'])){
//                        $company->develop_weakness= trim($data['研发痛点']);
//                    }
//                    if($company->save()){
//                       
//                        if(isset($data['行业'])){
//                            CorporationIndustry::deleteAll(['corporation_id'=>$company->id]);
//                            $industry= new CorporationIndustry();
//                            $industry->corporation_id=$company->id;
//                            $industrys= explode(',', str_replace('、',',',str_replace('，',',',$data['行业'])));
//                               
//                            foreach($industrys as $i){
//                                $i=trim($i);
//                                if(array_search($i, $base_industry)){
//                                    $_industry =clone $industry;
//                                    $_industry->industry_id=array_search($i, $base_industry);
//                                    $_industry->save();
//                                }
//                            }                              
//                        }
//                        $num[$num_key]++;
//                    }else{
//                        $errors=$company->getErrors();
//                   
//                        if($errors){
//                            $error=[];
//                            foreach($errors as $e){
//                                $error[]=$e[0];
//                            }
//                            $notice_error[]=$data['公司名称']. ' {'. implode(' ', $error).'}';
//                        }
//                        $num['fail']++;
//                    }
//                }else{
//                    $num['fail']++;
//                }
//                                    
//            }
//            if($notice_error){
//                Yii::$app->session->setFlash('error', $notice_error);
//            }
//            Yii::$app->session->setFlash('warning', '新增'.$num['add'].'家，更新'.$num['update'].'家，失败'.$num['fail'].'家。');
//                  
//         
//        } else {
//            Yii::$app->session->setFlash('error', '上传失败。');
//        }
//        return true;
//        
//    }
//    
//    public function actionCorporationImportTest() {
//        //判断是否Ajax
//        if (Yii::$app->request->isAjax) {
//
//            if (empty($_FILES['files'])) {
//                $postMaxSize = ini_get('post_max_size');
//                $fileMaxSize = ini_get('upload_max_filesize');
//                $displayMaxSize = $postMaxSize < $fileMaxSize ? $postMaxSize : $fileMaxSize;
//
//                return json_encode(['error' => '没有文件上传,文件最大为' . $displayMaxSize], JSON_UNESCAPED_UNICODE);
//                // or you can throw an exception
//            }
//
//
////            $start_time= microtime(true);
//
//            $files = $_FILES['files'];  
//            
//            $fileName= $files['tmp_name'][0];
//            //$fileName= Yii::getAlias('@webroot').'/excel/1.xlsx';
//            $format = \PHPExcel_IOFactory::identify($fileName);
//            $objectreader = \PHPExcel_IOFactory::createReader($format);
//            $objectPhpExcel = $objectreader->load($fileName);
//            
//            $dataArray = $objectPhpExcel->getActiveSheet()->toArray(null, true, true, true);
//            
//            $datas = ExcelHelper::execute_array_label($dataArray);
//                
//            $bd=User::get_bd();
//           
//            $stat= Corporation::$List['stat'];          
//                
//            $num=['add'=>0,'update'=>0,'fail'=>0];
//               
//            $notice_error=[];    
//            foreach ($datas as $key=>$data) {
////                    Yii::$app->session->setFlash('success', json_encode($datas,256));
////                    return true;
//                if($key==0){
//                    //项目处理
//                    $keys= array_filter(array_keys($data));
//                    if(!in_array('标题', $keys)){
//                        Yii::$app->session->setFlash('error', '文件首行不存在<<标题>>字段');
//                        break;
//                    }
//                }                            
//                //数据处理
//                
//                $data= array_filter($data);//去除0值和空值
//                   
//                if(isset($data['标题'])){
//                    $company = Corporation::findOne(['base_company_name'=>trim($data['标题'])]);
//
//                    if($company===null){
//                        //不存在
//                        $num_key='add';
//                        $company=new Corporation();
//                        $company->base_company_name=trim($data['标题']);
//                    }else{
//                        if($company->stat== Corporation::STAT_ALLOCATE){
//                            continue;
//                        }
//                        $num_key='update';
//                    }
//                            
//                    if(isset($data['处理人'])&&array_search(trim($data['处理人']), $bd)){
//                        $company->base_bd= array_search(trim($data['处理人']), $bd);
//                    }
//                    if(isset($data['状态'])&&array_search(trim($data['状态']), $stat)){
//                        $company->stat= array_search(trim($data['状态']), $stat);
//                    }
//                    
//                    if($company->save(false)){
//                                             
//                        $num[$num_key]++;
//                    }else{
//                        $errors=$company->getErrors();
//                   
//                        if($errors){
//                            $error=[];
//                            foreach($errors as $e){
//                                $error[]=$e[0];
//                            }
//                            $notice_error[]=$data['标题']. ' {'. implode(' ', $error).'}';
//                        }
//                        $num['fail']++;
//                    }
//                }else{
//                    $num['fail']++;
//                }
//                                    
//            }
//            if($notice_error){
//                Yii::$app->session->setFlash('error', $notice_error);
//            }
//            Yii::$app->session->setFlash('warning', '新增'.$num['add'].'家，更新'.$num['update'].'家，失败'.$num['fail'].'家。');
//                  
//         
//        } else {
//            Yii::$app->session->setFlash('error', '上传失败。');
//        }
//        return true;
//        
//    }

}
