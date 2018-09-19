<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\actions\IndexAction;
use project\models\CorporationMeal;
use project\models\CorporationMealSearch;
use project\actions\UpdateAction;
use project\actions\DeleteAction;
use project\components\ExcelHelper;
use project\models\User;
use project\models\Corporation;
use project\models\Meal;


class AllocateController extends Controller { 
    
    public function actions()
    {
        return [
            'allocate-list' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $searchModel = new CorporationMealSearch();
                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                    return [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                    ];               
                }
            ],
            'allocate-update' => [
                'class' => UpdateAction::className(),
                'modelClass' => CorporationMeal::className(),
                'successRedirect'=>Yii::$app->request->referrer,
                'ajax'=>true,
            ],
            'allocate-delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => CorporationMeal::className(),
            ],

        ];
    }
   
    
    public function actionAllocateExport() {
        $start_time= microtime(true);
        $searchModel = new CorporationMealSearch();
        $models = $searchModel->search(Yii::$app->request->queryParams)->getModels();
       
        $fileName= Yii::getAlias('@webroot').'/excel/allocate_temple.xlsx';
        $format = \PHPExcel_IOFactory::identify($fileName);
        $objectreader = \PHPExcel_IOFactory::createReader($format);
        $objectPhpExcel = $objectreader->load($fileName);
        
        $objectPhpExcel->getActiveSheet()->setCellValue( 'A1', '序号')
                ->setCellValue( 'B1', $searchModel->getAttributeLabel('corporation_id'))
                ->setCellValue( 'C1', $searchModel->getAttributeLabel('huawei_account'))
                ->setCellValue( 'D1', $searchModel->getAttributeLabel('bd'))
                ->setCellValue( 'E1', $searchModel->getAttributeLabel('meal_id'))
                ->setCellValue( 'F1', $searchModel->getAttributeLabel('number'))
                ->setCellValue( 'G1', $searchModel->getAttributeLabel('amount'))
                ->setCellValue( 'H1', $searchModel->getAttributeLabel('start_time'))
                ->setCellValue( 'I1', $searchModel->getAttributeLabel('end_time'));

        foreach($models as $key=>$model){
            $k=$key+2;
            $objectPhpExcel->getActiveSheet()->setCellValue( 'A'.$k, $key+1)
                    ->setCellValue( 'B'.$k, $model->corporation->base_company_name)
                    ->setCellValue( 'C'.$k, $model->huawei_account)
                    ->setCellValue( 'D'.$k, $model->bd?($model->bd0->nickname?$model->bd0->nickname:$model->bd0->username):'')
                    ->setCellValue( 'E'.$k, $model->meal_id?$model->meal->name:'其他')
                    ->setCellValue( 'F'.$k, $model->number)
                    ->setCellValue( 'G'.$k, $model->amount)
                    ->setCellValue( 'H'.$k, $model->start_time>0?date('Y-m-d',$model->start_time):'')
                    ->setCellValue( 'I'.$k, $model->end_time>0?date('Y-m-d',$model->end_time):'');
                    
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
                ->setCellValue( 'E1', $searchModel->getAttributeLabel('meal_id'))
                ->setCellValue( 'F1', $searchModel->getAttributeLabel('number'))
                ->setCellValue( 'G1', $searchModel->getAttributeLabel('amount'))
                ->setCellValue( 'H1', $searchModel->getAttributeLabel('start_time'))
                ->setCellValue( 'I1', $searchModel->getAttributeLabel('end_time'));
        
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
                
            $bd=User::get_bd();
            $intent_set= Meal::get_meal();

                
            
            $searchModel = new CorporationMealSearch();
            $index=[
                'corporation_id'=>$searchModel->getAttributeLabel('corporation_id'),
                'huawei_account'=>$searchModel->getAttributeLabel('huawei_account'),
                'bd'=>$searchModel->getAttributeLabel('bd'),
                'meal_id'=>$searchModel->getAttributeLabel('meal_id'),
                'number'=>$searchModel->getAttributeLabel('number'),
                'amount'=>$searchModel->getAttributeLabel('amount'),
                'start_time'=>$searchModel->getAttributeLabel('start_time'),
                'end_time'=>$searchModel->getAttributeLabel('end_time'),               
                ];
            
            //项目处理
            $keys= array_filter(array_keys($datas[0]));
            if(!in_array($index['corporation_id'], $keys)){
                Yii::$app->session->setFlash('error', '文件首行不存在<<'.$index['corporation_id'].'>>字段');
                return false;
            }
            if(!in_array($index['start_time'], $keys)){
                Yii::$app->session->setFlash('error', '文件首行不存在<<'.$index['start_time'].'>>字段');
                return false;
            }
            
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
                if($corporation!==null&&Yii::$app->user->can('企业修改',['id'=>$corporation->id])){
                    //企业存在
                    ksort($company);
                    
                    foreach($company as $key=>$data){
                        
                        $stat =0;
                        
                        $allocate = CorporationMeal::findOne(['corporation_id'=>$corporation->id,'start_time'=>$key]);
                        if($allocate===null){
                            //不存在
                            if($key>CorporationMeal::get_end_time($corporation->id)){
                                $num_key='add';
                                $allocate=new CorporationMeal();
                                $allocate->loadDefaultValues();
                                $allocate->corporation_id=$corporation->id;
                                $allocate->start_time=$key;
                                $allocate->end_time = strtotime('+1 year', $allocate->start_time)-1;                                
                                $allocate->created_at = time();
                                
                                
                                if($corporation->stat==Corporation::STAT_AGAIN){
                                    $stat=1;
                                }
                                $corporation->stat= CorporationMeal::get_allocate($corporation->id)?Corporation::STAT_AGAIN:Corporation::STAT_ALLOCATE;
                            }else{
                                continue;;
                            }
                        }else{
                            $num_key='update';
                        }

                        $allocate->user_id = Yii::$app->user->identity->id;
                        
                        if(isset($data[$index['huawei_account']])){
                            $allocate->huawei_account= trim($data[$index['huawei_account']]);
                        }
                        if(isset($data[$index['bd']])&&array_search(trim($data[$index['bd']]), $bd)){
                            $allocate->bd= array_search(trim($data[$index['bd']]), $bd);
                            $corporation->base_bd=$corporation->base_bd?$corporation->base_bd:$allocate->bd;
                        }

                        if(isset($data[$index['meal_id']])&&array_search(trim($data[$index['meal_id']]), $intent_set)){
                            $allocate->meal_id= array_search(trim($data[$index['meal_id']]), $intent_set);
                        }
                        if(isset($data[$index['number']])){
                            $allocate->number= trim($data[$index['number']]);
                        }                   
                        if($allocate->meal_id&&$allocate->number){
                            $allocate->amount=$allocate->number*Meal::get_meal_amount($allocate->meal_id);
                        }elseif(isset($data[$index['amount']])){
                            $allocate->amount=trim($data[$index['amount']]);
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
                                       
                }else{
                    $num['fail']++;
                }
                
            }
           
            if($notice_error){
                Yii::$app->session->setFlash('error', $notice_error);
            }
            Yii::$app->session->setFlash('warning', '新增'.$num['add'].'，更新'.$num['update'].'，失败'.$num['fail'].'。');
                  
         
        } else {
            Yii::$app->session->setFlash('error', '上传失败。');
        }
        return true;
        
    }
    
}
