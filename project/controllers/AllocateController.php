<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\actions\IndexAction;
use project\models\CorporationMeal;
use project\models\CorporationMealSearch;
use project\actions\DeleteAction;
use project\components\ExcelHelper;
use project\models\User;
use project\models\Corporation;
use project\models\Meal;
USE project\models\Parameter;
use project\models\ColumnSetting;
use project\models\Group;
use project\models\ActivityChange;
use project\models\UserGroup;


class AllocateController extends Controller { 
    
    public function actions()
    {
        return [
            'allocate-list' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $searchModel = new CorporationMealSearch();
                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                    $column= ColumnSetting::get_column_content(Yii::$app->user->identity->id,'allocate');
                    return [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                        'column'=>$column,
                    ];               
                }
            ],
            'allocate-delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => CorporationMeal::className(),
            ],

        ];
    }
    
    public function actionAllocateUpdate($id) {
        $model = CorporationMeal::findOne($id);                 
        if ($model->load(Yii::$app->request->post())) {
            
            $model->start_time= strtotime($model->start_time);
            $model->end_time = $model->end_time?strtotime($model->end_time)+86399:strtotime('+1 year', $model->start_time)-1;
            
            if (Yii::$app->request->isAjax) {                
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }            
     
            if($model->save()){
                ActivityChange::updateAll(['is_allocate'=> ActivityChange::ALLOCATE_D], ['corporation_id'=>$model->corporation_id]);
                ActivityChange::set_allocate();
                Yii::$app->session->setFlash('success', '更新成功');
            }else{
                Yii::$app->session->setFlash('error', '更新失败');
            }        
            return $this->redirect(Yii::$app->request->referrer);
        }else{
                              
            $model->start_time= date('Y-m-d',$model->start_time);
            $model->end_time= date('Y-m-d',$model->end_time);
        }
        return $this->renderAjax('allocate-update', [
                    'model' => $model,
        ]);
    }
    
    public function actionAllocateExport() {
        $start_time= microtime(true);
        $searchModel = new CorporationMealSearch();
        $models = $searchModel->search(Yii::$app->request->queryParams,1000)->getModels();
       
        $fileName= Yii::getAlias('@webroot').'/excel/allocate_temple.xlsx';
        $format = \PHPExcel_IOFactory::identify($fileName);
        $objectreader = \PHPExcel_IOFactory::createReader($format);
        $objectPhpExcel = $objectreader->load($fileName);
        $objSheet = $objectPhpExcel->getActiveSheet();
        
        $objSheet->setCellValue( 'A1', '序号')
                ->setCellValue( 'B1', $searchModel->getAttributeLabel('corporation_id'))
                ->setCellValue( 'C1', $searchModel->getAttributeLabel('huawei_account'))
                ->setCellValue( 'D1', $searchModel->getAttributeLabel('bd'))
                ->setCellValue( 'E1', $searchModel->getAttributeLabel('annual'))
                ->setCellValue( 'F1', $searchModel->getAttributeLabel('meal_id'))
                ->setCellValue( 'G1', $searchModel->getAttributeLabel('number'))
                ->setCellValue( 'H1', $searchModel->getAttributeLabel('amount'))
                ->setCellValue( 'I1', $searchModel->getAttributeLabel('start_time'))
                ->setCellValue( 'J1', $searchModel->getAttributeLabel('end_time'))
                ->setCellValue( 'K1', $searchModel->getAttributeLabel('devcloud_count'))
                ->setCellValue( 'L1', $searchModel->getAttributeLabel('devcloud_amount'))
                ->setCellValue( 'M1', $searchModel->getAttributeLabel('cloud_amount'))
                ->setCellValue( 'N1', $searchModel->getAttributeLabel('stat'));
        
        $group_count=count(Group::get_user_group(Yii::$app->user->identity->id));
        
        if($group_count>1){
            $objSheet->setCellValue( 'O1', $searchModel->getAttributeLabel('group_id'));
        }

        foreach($models as $key=>$model){
            $k=$key+2;
            $objSheet->setCellValue( 'A'.$k, $key+1)
                    ->setCellValue( 'B'.$k, $model->corporation->base_company_name)
                    ->setCellValue( 'C'.$k, $model->huawei_account)
                    ->setCellValue( 'D'.$k, $model->bd?($model->bd0->nickname?$model->bd0->nickname:$model->bd0->username):'')
                    ->setCellValue( 'E'.$k, implode(',', Parameter::get_para_value('allocate_annual',$model->annual)))
                    ->setCellValue( 'F'.$k, $model->meal_id?$model->meal->name:'其他')
                    ->setCellValue( 'G'.$k, $model->number)
                    ->setCellValue( 'H'.$k, $model->amount)
                    ->setCellValue( 'I'.$k, $model->start_time>0?date('Y-m-d',$model->start_time):'')
                    ->setCellValue( 'J'.$k, $model->end_time>0?date('Y-m-d',$model->end_time):'')
                    ->setCellValue( 'K'.$k, $model->devcloud_count)
                    ->setCellValue( 'L'.$k, $model->devcloud_amount)
                    ->setCellValue( 'M'.$k, $model->cloud_amount)
                    ->setCellValue( 'N'.$k, $model->Stat);
            if($group_count>1){
                $objSheet->setCellValue( 'O'.$k, $model->group_id?$model->group->title:$model->group_id);
            }
                    
        }
        
        
        $end_time= microtime(true);
        if($end_time-$start_time<1){
            sleep(1);
        }

        ExcelHelper::excel_set_headers($format,'下拨信息(' . date('Y-m-d', time()) . ')');
        
        $objectwriter = \PHPExcel_IOFactory::createWriter($objectPhpExcel, $format);
        $path = 'php://output';
        $objectwriter->save($path);        
        exit();
    }
    
     public function actionAllocateTemple() {
        
        $start_time= microtime(true);
        
        $fileName= Yii::getAlias('@webroot').'/excel/allocate_temple.xlsx';
        $format = \PHPExcel_IOFactory::identify($fileName);
        $objectreader = \PHPExcel_IOFactory::createReader($format);
        $objectPhpExcel = $objectreader->load($fileName);
        $searchModel = new CorporationMealSearch();
        $objectPhpExcel->getActiveSheet()->setCellValue( 'A1', '序号')
                ->setCellValue( 'B1', $searchModel->getAttributeLabel('corporation_id'))
                ->setCellValue( 'C1', $searchModel->getAttributeLabel('huawei_account'))
                ->setCellValue( 'D1', $searchModel->getAttributeLabel('bd'))
                ->setCellValue( 'E1', $searchModel->getAttributeLabel('annual'))
                ->setCellValue( 'F1', $searchModel->getAttributeLabel('meal_id'))
                ->setCellValue( 'G1', $searchModel->getAttributeLabel('number'))
                ->setCellValue( 'H1', $searchModel->getAttributeLabel('amount'))
                ->setCellValue( 'I1', $searchModel->getAttributeLabel('start_time'))
                ->setCellValue( 'J1', $searchModel->getAttributeLabel('end_time'))
                ->setCellValue( 'K1', $searchModel->getAttributeLabel('devcloud_count'))
                ->setCellValue( 'L1', $searchModel->getAttributeLabel('devcloud_amount'))
                ->setCellValue( 'M1', $searchModel->getAttributeLabel('cloud_amount'));
        
        if(count(Group::get_user_group(Yii::$app->user->identity->id))>1){
            $objectPhpExcel->getActiveSheet()->setCellValue( 'N1', $searchModel->getAttributeLabel('group_id'));
        }
        
        $end_time= microtime(true);
        if($end_time-$start_time<1){
            sleep(1);
        }
        
        ExcelHelper::excel_set_headers($format,'下拨信息模板');
        
        $objectwriter = \PHPExcel_IOFactory::createWriter($objectPhpExcel, $format);
        $path = 'php://output';
        $objectwriter->save($path);        
        exit();
        
    }
    
    public function actionAllocateImport() {
        //判断是否Ajax
        if (Yii::$app->request->isAjax) {

            if (empty($_FILES['files'])) {
                $postMaxSize = ini_get('post_max_size');
                $fileMaxSize = ini_get('upload_max_filesize');
                $displayMaxSize = $postMaxSize < $fileMaxSize ? $postMaxSize : $fileMaxSize;

                return json_encode(['error' => '没有文件上传,文件最大为' . $displayMaxSize], JSON_UNESCAPED_UNICODE);
                // or you can throw an exception
            }


//            $start_time= microtime(true);

            $files = $_FILES['files'];  
            
            $fileName= $files['tmp_name'][0];
            //$fileName= Yii::getAlias('@webroot').'/excel/1.xlsx';
            $format = \PHPExcel_IOFactory::identify($fileName);
            $objectreader = \PHPExcel_IOFactory::createReader($format);
            $objectPhpExcel = $objectreader->load($fileName);
            
            $dataArray = $objectPhpExcel->getActiveSheet()->toArray(null, true, true, true);
            
            $datas = ExcelHelper::execute_array_label($dataArray);
                
            $group = Group::get_user_group(Yii::$app->user->identity->id);
            $bd=User::get_bd(User::STATUS_ACTIVE, UserGroup::get_group_userid(array_keys($group)));
            $intent_set= Meal::get_meal(Meal::STAT_ACTIVE,array_keys($group));
           
            $searchModel = new CorporationMealSearch();
            $index=[
                'corporation_id'=>$searchModel->getAttributeLabel('corporation_id'),
                'huawei_account'=>$searchModel->getAttributeLabel('huawei_account'),
                'bd'=>$searchModel->getAttributeLabel('bd'),
                'annual'=>$searchModel->getAttributeLabel('annual'),
                'meal_id'=>$searchModel->getAttributeLabel('meal_id'),
                'number'=>$searchModel->getAttributeLabel('number'),
                'amount'=>$searchModel->getAttributeLabel('amount'),
                'start_time'=>$searchModel->getAttributeLabel('start_time'),
                'end_time'=>$searchModel->getAttributeLabel('end_time'),    
                'devcloud_count'=>$searchModel->getAttributeLabel('devcloud_count'),
                'devcloud_amount'=>$searchModel->getAttributeLabel('devcloud_amount'),
                'cloud_amount'=>$searchModel->getAttributeLabel('cloud_amount'),
                'group_id'=>$searchModel->getAttributeLabel('group_id'),
                ];
            
            //项目处理
            if(isset($datas[0])){
                $keys= array_filter(array_keys($datas[0]));
                if(!in_array($index['huawei_account'], $keys)){
                    Yii::$app->session->setFlash('error', '文件首行不存在<<'.$index['huawei_account'].'>>字段');
                    return false;
                }
                if(!in_array($index['start_time'], $keys)){
                    Yii::$app->session->setFlash('error', '文件首行不存在<<'.$index['start_time'].'>>字段');
                    return false;
                }
            }else{
                Yii::$app->session->setFlash('error', '没有有效数据');
                return false;
            }
            
            $annual=Parameter::get_type('allocate_annual');
            
            $num=['add'=>0,'update'=>0,'fail'=>0];
            $notice_error=[];
            
            $allocates =[];
            foreach ($datas as $key=>$data) {
               
                $data= array_filter($data);//去除0值和空值
                if(isset($data[$index['corporation_id']])&&isset($data[$index['start_time']])&&strtotime($data[$index['start_time']])){
                    $allocates[trim($data[$index['corporation_id']])][strtotime($data[$index['start_time']])]=$data;
                    
                }
            }
                        
            foreach ($allocates as $company_name=>$company) {

                $corporation = Corporation::findOne(['base_company_name'=>$company_name]);
                if($corporation==null){
                    //企业不存在
                    $corporation=new Corporation();
                    $corporation->loadDefaultValues();
                    $corporation->base_company_name=$company_name;
                    if(count($group)>1){
                        if(isset($data[$index['group_id']])&&array_search(trim($data[$index['group_id']]), $group)){
                            $corporation->group_id= array_search(trim($data[$index['group_id']]), $group);
                        }else{
                            $notice_error[]=$company_name. ' {未指明项目组或项目组不存在}';
                            $num['fail']++;
                            continue;
                        }
                    }else{
                        if(!isset($data[$index['group_id']])||(isset($data[$index['group_id']])&&array_search(trim($data[$index['group_id']]), $group))){
                            $corporation->group_id= key($group);          
                        }else{
                            $notice_error[]=$company_name. ' {未指明项目组或项目组不存在}';
                            $num['fail']++;
                            continue;
                        }
                    }
                    $corporation->save();
                    
                }elseif(!Yii::$app->user->can('企业修改',['id'=>$corporation->id])){
                    $notice_error[]=$company_name. ' {无操作权限}';
                    $num['fail']++;
                    continue;
                }
                ksort($company);//下拨时间排序

                foreach($company as $key=>$data){

                    $stat =0;

                    $allocate = CorporationMeal::findOne(['corporation_id'=>$corporation->id,'start_time'=>$key]);
                    if($allocate===null){
                        //不存在
                        if($key>CorporationMeal::get_last_start_time($corporation->id)){
                            $num_key='add';
                            $allocate=new CorporationMeal();
                            $allocate->loadDefaultValues();
                            $allocate->corporation_id=$corporation->id;
                            $allocate->group_id=$corporation->group_id;
                            $allocate->start_time=$key;                                                              
                            $allocate->created_at = time();
                            $allocate->stat=CorporationMeal::get_allocate($corporation->id)?CorporationMeal::STAT_AGAIN:CorporationMeal::STAT_ALLOCATE;


                            if($corporation->stat==Corporation::STAT_AGAIN){
                                $stat=1;
                            }
                            $corporation->stat= CorporationMeal::get_allocate($corporation->id)?Corporation::STAT_AGAIN:Corporation::STAT_ALLOCATE;
                        }else{
                            $notice_error[]=$data[$corporation->id]. ' {下拨时间出错}';
                            $num['fail']++;
                            continue;
                        }
                    }else{
                        $num_key='update';
                    }

                    $allocate->user_id = Yii::$app->user->identity->id;

                    $allocate->end_time = isset($data[$index['end_time']])?strtotime($data[$index['end_time']])+86399:strtotime('+1 year', $allocate->start_time)-1;                        

                    if(isset($data[$index['huawei_account']])){
                        $allocate->huawei_account= trim($data[$index['huawei_account']]);
                    }
                    if(isset($data[$index['bd']])&&array_search(trim($data[$index['bd']]), $bd)){
                        $allocate->bd= array_search(trim($data[$index['bd']]), $bd);
                        $corporation->base_bd=$corporation->base_bd?$corporation->base_bd:$allocate->bd;
                    }

                    if(isset($data[$index['annual']])&&array_search(trim($data[$index['annual']]), $annual)){
                        $allocate->annual= (string)array_search(trim($data[$index['annual']]), $annual);
                    }

                    if(isset($data[$index['meal_id']])&&array_search(trim($data[$index['meal_id']]), $intent_set)){
                        $allocate->meal_id= array_search(trim($data[$index['meal_id']]), $intent_set);
                    }
                    if(isset($data[$index['number']])){
                        $allocate->number= trim($data[$index['number']]);
                    }

                    //是否存在套餐，否则需要提取后续数据
//                        if($allocate->meal_id&&$allocate->number){
//                            $allocate->amount=$allocate->number*Meal::get_meal_amount($allocate->meal_id);
//                        }elseif(isset($data[$index['devcloud_amount']])&&isset($data[$index['cloud_amount']])&&isset($data[$index['devcloud_count']])){
//                            $allocate->devcloud_count=trim($data[$index['devcloud_count']]);
//                            $allocate->devcloud_amount=trim($data[$index['devcloud_amount']]);
//                            $allocate->cloud_amount=trim($data[$index['cloud_amount']]);
//                            $allocate->amount=$allocate->devcloud_amount+$allocate->cloud_amount;
//                        }
                    if(isset($data[$index['devcloud_amount']])&&isset($data[$index['cloud_amount']])&&isset($data[$index['devcloud_count']])){
                        $allocate->devcloud_count=trim($data[$index['devcloud_count']]);
                        $allocate->devcloud_amount=str_replace([',','¥'], '',trim($data[$index['devcloud_amount']]));
                        $allocate->cloud_amount=str_replace([',','¥'], '',trim($data[$index['cloud_amount']]));                          
                    }

                    $corporation->huawei_account=$allocate->huawei_account;

                    if($stat){
                        //续拨继续下拨需要手动添加状态
                        $statModel=new CorporationStat();
                        $statModel->corporation_id=$corporation->id;
                        $statModel->stat=$corporation->stat;
                        $statModel->user_id=Yii::$app->user->identity->id;
                        $statModel->created_at=$allocate->created_at;
                        $statModel->save();         
                    }


                    if($allocate->save()&&$corporation->save(false)){
                        ActivityChange::updateAll(['is_allocate'=> ActivityChange::ALLOCATE_D], ['corporation_id'=>$allocate->corporation_id]);                        
                        $num[$num_key]++;
                    }else{
                        $errors=$allocate->getErrors();

                        if($errors){
                            $error=[];
                            foreach($errors as $e){
                                $error[]=$e[0];
                            }
                            $notice_error[]=$data[$index['corporation_id']]. ' {'. implode(' ', $error).'}';
                        }
                        $num['fail']++;
                    }

                }
                
            }
            ActivityChange::set_allocate();
            if($notice_error){
                Yii::$app->session->setFlash('error', $notice_error);
            }
            Yii::$app->session->setFlash('warning', '新增'.$num['add'].'，更新'.$num['update'].'，失败'.$num['fail'].'。');
                  
         
        } else {
            Yii::$app->session->setFlash('error', '上传失败。');
        }
        return true;
        
    }
    
    public function actionAllocateColumn() {

        $model=ColumnSetting::get_column(Yii::$app->user->identity->id,'allocate');
        if($model==null){
            $model=new ColumnSetting();
            $model->uid=Yii::$app->user->identity->id;
            $model->type='allocate';
            
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
                
            return $this->renderAjax('allocate-column', [
                'model' => $model,
            ]);
      
        }
    }
    
}
