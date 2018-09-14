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
                'successRedirect'=>'allocate-list',
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
                
//            $bd=User::get_bd();
//            $base_industry= Industry::getIndustryName();
//            $stat= Corporation::$List['stat'];
//            $intent_set= Meal::get_meal();
//            $contact_park=Parameter::get_type('contact_park');
//            $develop_pattern=Parameter::get_type('develop_pattern');
//            $develop_scenario=Parameter::get_type('develop_scenario');
//            $develop_science=Parameter::get_type('develop_science');
//            $develop_language=Parameter::get_type('develop_language');
//            $develop_IDE=Parameter::get_type('develop_IDE');
                
            $num=['add'=>0,'update'=>0,'fail'=>0];
               
            $notice_error=[];
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
            foreach ($datas as $key=>$data) {
//                    Yii::$app->session->setFlash('success', json_encode($datas,256));
//                    return true;
                if($key==0){
                    //项目处理
                    $keys= array_filter(array_keys($data));
                    if(!in_array($index['base_company_name'], $keys)){
                        Yii::$app->session->setFlash('error', '文件首行不存在<<'.$index['base_company_name'].'>>字段');
                        break;
                    }
                }                            
                //数据处理
                
                $data= array_filter($data);//去除0值和空值
                
               
                   
                if(isset($data[$index['base_company_name']])){
                    $corporation = Corporation::findOne(['base_company_name'=>trim($data[$index['base_company_name']])]);

                    if($corporation===null){
                        //不存在
                        $num_key='add';
                        $corporation=new Corporation();
                        $corporation->loadDefaultValues();
                        $corporation->base_company_name=trim($data[$index['base_company_name']]);
                    }elseif(Yii::$app->user->can('企业修改',['id'=>$corporation->id])){
                        $num_key='update';
                    }else{
                        continue;
                    }
                    
//                    if(isset($data[$index['stat']])&&array_search(trim($data[$index['stat']]), $stat)){
//                        $corporation->stat= array_search(trim($data[$index['stat']]), $stat);
//                    }
                    if(isset($data[$index['base_bd']])&&array_search(trim($data[$index['base_bd']]), $bd)){
                        $corporation->base_bd= array_search(trim($data[$index['base_bd']]), $bd);
                    }
                    if(isset($data[$index['huawei_account']])){
                        $corporation->huawei_account= trim($data[$index['huawei_account']]);
                    }
                    if(isset($data[$index['contact_park']])){
                        $data[$index['contact_park']]=trim($data[$index['contact_park']]);
                        if(array_search($data[$index['contact_park']], $contact_park)){
                            $corporation->contact_park= array_search($data[$index['contact_park']], $contact_park);
                        }elseif($parameter_add){
                            $corporation->contact_park= Parameter::add_type('contact_park', $data[$index['contact_park']]);
                            $contact_park[$corporation->contact_park]=$data[$index['contact_park']];
                        }
                    }
                    if(isset($data[$index['contact_address']])){
                        $corporation->contact_address= trim($data[$index['contact_address']]);
                    }                   
                    if(isset($data[$index['intent_set']])&&array_search(trim($data[$index['intent_set']]), $intent_set)){
                        $corporation->intent_set= array_search(trim($data[$index['intent_set']]), $intent_set);
                    }
                    if(isset($data[$index['intent_number']])){
                        $corporation->intent_number= trim($data[$index['intent_number']]);
                    }                   
                    if($corporation->intent_set&&$corporation->intent_number){
                        $corporation->intent_amount=$corporation->intent_number*Meal::get_meal_amount($corporation->intent_set);
                    }elseif(isset($data[$index['intent_amount']])){
                        $corporation->intent_amount=trim($data[$index['intent_amount']]);
                    }
                    if(isset($data[$index['base_company_scale']])){
                        $corporation->base_company_scale= trim($data[$index['base_company_scale']]);
                    }
                    if(isset($data[$index['base_registered_capital']])){
                        $corporation->base_registered_capital= trim($data[$index['base_registered_capital']]);
                    }
                    if(isset($data[$index['base_registered_time']])&&strtotime($data[$index['base_registered_time']])){
                        $corporation->base_registered_time= strtotime($data[$index['base_registered_time']]);
                    }
                    if(isset($data[$index['base_main_business']])){
                        $corporation->base_main_business= trim($data[$index['base_main_business']]);
                    }                    
                    if(isset($data[$index['base_last_income']])){
                        $corporation->base_last_income= trim($data[$index['base_last_income']]);
                    }               
                    if(isset($data[$index['contact_business_name']])){
                        $corporation->contact_business_name= trim($data[$index['contact_business_name']]);
                    }
                    if(isset($data[$index['contact_business_job']])){
                        $corporation->contact_business_job= trim($data[$index['contact_business_job']]);
                    }
                    if(isset($data[$index['contact_business_tel']])){
                        $corporation->contact_business_tel= trim((string)$data[$index['contact_business_tel']]);
                    }
                    if(isset($data[$index['contact_technology_name']])){
                        $corporation->contact_technology_name= trim($data[$index['contact_technology_name']]);
                    }
                    if(isset($data[$index['contact_technology_job']])){
                        $corporation->contact_technology_job= trim($data[$index['contact_technology_job']]);
                    }
                    if(isset($data[$index['contact_technology_tel']])){
                        $corporation->contact_technology_tel= trim((string)$data[$index['contact_technology_tel']]);
                    }       
                    if(isset($data[$index['develop_scale']])){
                        $corporation->develop_scale= trim($data[$index['develop_scale']]);
                    }
                    
                    if(isset($data[$index['develop_pattern']])){
                        $data[$index['develop_pattern']]=trim($data[$index['develop_pattern']]);
                        if(array_search($data[$index['develop_pattern']], $develop_pattern)){
                            $corporation->develop_pattern= array_search($data[$index['develop_pattern']], $develop_pattern);
                        }elseif($parameter_add){
                            $corporation->develop_pattern= Parameter::add_type('develop_pattern', $data[$index['develop_pattern']]);
                            $develop_pattern[$corporation->develop_pattern]=$data[$index['develop_pattern']];
                        }
                    }
                    if(isset($data[$index['develop_scenario']])){
                        $data[$index['develop_scenario']]=trim($data[$index['develop_scenario']]);
                        if(array_search($data[$index['develop_scenario']], $develop_scenario)){
                            $corporation->develop_scenario= array_search($data[$index['develop_scenario']], $develop_scenario);
                        }elseif($parameter_add){
                            $corporation->develop_scenario= Parameter::add_type('develop_scenario', $data[$index['develop_scenario']]);
                            $develop_scenario[$corporation->develop_scenario]=$data[$index['develop_scenario']];
                        }
                    }
                    if(isset($data[$index['develop_science']])){
                        $data[$index['develop_science']]=trim($data[$index['develop_science']]);
                        if(array_search($data[$index['develop_science']], $develop_science)){
                            $corporation->develop_science= array_search($data[$index['develop_science']], $develop_science);
                        }elseif($parameter_add){
                            $corporation->develop_science= Parameter::add_type('develop_science', $data[$index['develop_science']]);
                            $develop_science[$corporation->develop_science]=$data[$index['develop_science']];
                        }
                    }
                    if(isset($data[$index['develop_language']])){
                        $ls= explode(',', str_replace('、',',',str_replace('，',',',$data[$index['develop_language']])));
                        $dl=[];
                        foreach($ls as $l){
                            $l=trim($l);
                            if(array_search($l, $develop_language)){
                                $dl[]=array_search($l, $develop_language);
                            }elseif($parameter_add){
                                $lid=Parameter::add_type('develop_language', $l);
                                $dl[]=$lid;
                                $develop_language[$lid]=$l;
                            }
                        }
                        $corporation->develop_language= implode(',', $dl);
                    }
                    if(isset($data[$index['develop_IDE']])){
                        $data[$index['develop_IDE']]=trim($data[$index['develop_IDE']]);
                        if(array_search($data[$index['develop_IDE']], $develop_IDE)){
                            $corporation->develop_IDE= array_search($data[$index['develop_IDE']], $develop_IDE);
                        }elseif($parameter_add){
                            $corporation->develop_IDE= Parameter::add_type('develop_IDE', $data[$index['develop_IDE']]);
                            $develop_IDE[$corporation->develop_IDE]=$data[$index['develop_IDE']];
                        }
                    }
                    if(isset($data[$index['develop_current_situation']])){
                        $corporation->develop_current_situation= trim($data[$index['develop_current_situation']]);
                    }
                    if(isset($data[$index['develop_weakness']])){
                        $corporation->develop_weakness= trim($data[$index['develop_weakness']]);
                    }
                    
                    if($corporation->save()){
                       
                        if(isset($data[$index['base_industry']])){
                            CorporationIndustry::deleteAll(['corporation_id'=>$corporation->id]);
                            $industry= new CorporationIndustry();
                            $industry->corporation_id=$corporation->id;
                            $industrys= explode(',', str_replace('、',',',str_replace('，',',',$data[$index['base_industry']])));
                               
                            foreach($industrys as $i){
                                $i=trim($i);
                                if(array_search($i, $base_industry)){
                                    $_industry =clone $industry;
                                    $_industry->industry_id=array_search($i, $base_industry);
                                    $_industry->save();
                                }
                            }                              
                        }
                        $num[$num_key]++;
                    }else{
                        $errors=$corporation->getErrors();
                   
                        if($errors){
                            $error=[];
                            foreach($errors as $e){
                                $error[]=$e[0];
                            }
                            $notice_error[]=$data[$index['base_company_name']]. ' {'. implode(' ', $error).'}';
                        }
                        $num['fail']++;
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
