<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use project\actions\IndexAction;
use project\models\Corporation;
use project\models\CorporationSearch;
use project\models\CorporationIndustry;
use project\actions\DeleteAction;
use project\actions\ViewAction;
use project\models\CorporationBd;
use project\models\CorporationStat;
use project\models\CorporationMeal;
use project\models\ColumnSetting;
use project\components\ExcelHelper;
use project\models\Parameter;
use project\models\User;
use project\models\Industry;
use project\models\Meal;
use project\models\System;


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
                    $column= ColumnSetting::get_column_content(Yii::$app->user->identity->id,'corporation');
                    return [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                        'column'=>$column,
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
           
            if ($model->load(Yii::$app->request->post())) {
                
                if($allocate){
                    $allocate->load(Yii::$app->request->post());
                }
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    if($allocate){         
                        $re = \yii\widgets\ActiveForm::validate($allocate);
                        if($re){
                            return $re;
                        }
                    }                     
                    
                    return \yii\widgets\ActiveForm::validate($model);
                    
                }
            
                
                $rw = Yii::$app->request->post('Corporation');
                $industrys = $rw['base_industry']&&!is_array($rw['base_industry']) ? explode(',',$rw['base_industry']) : array();
                $model->develop_language = $rw['develop_language'] ? implode(',',$rw['develop_language']) : '';
           
            
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if($allocate){
                        $allocate->start_time=strtotime($allocate->start_time);
                        $allocate->end_time = strtotime('+1 year', $allocate->start_time)-1;                       
                        $allocate->user_id = Yii::$app->user->identity->id;
                        $allocate->save(false);
                    }
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
            $stat =0;
            if($corporation->stat==Corporation::STAT_AGAIN){
                $stat=1;
            }
            $corporation->stat= CorporationMeal::get_allocate($corporation->id)?Corporation::STAT_AGAIN:Corporation::STAT_ALLOCATE;
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
                
      
                if($stat){
                    //续拨继续下拨需要手动添加状态
                    $statModel=new CorporationStat();
                    $statModel->corporation_id=$corporation->id;
                    $statModel->stat=$corporation->stat;
                    $statModel->user_id=Yii::$app->user->identity->id;
                    $statModel->created_at=$model->created_at;
                    $statModel->save();         
                }
                
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
    
    public function actionCorporationColumn() {

        $model=ColumnSetting::get_column(Yii::$app->user->identity->id,'corporation');
        if($model==null){
            $model=new ColumnSetting();
            $model->uid=Yii::$app->user->identity->id;
            $model->type='corporation';
            
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
                
            return $this->renderAjax('corporation-column', [
                'model' => $model,
            ]);
      
        }
    }

    public function actionCorporationTemple() {
        
        $start_time= microtime(true);
        
        $fileName= Yii::getAlias('@webroot').'/excel/corporation_temple.xlsx';
        $format = \PHPExcel_IOFactory::identify($fileName);
        $objectreader = \PHPExcel_IOFactory::createReader($format);
        $objectPhpExcel = $objectreader->load($fileName);
  
        $objSheet = $objectPhpExcel->getSheetByName('企业信息'); //这一句为要设置数据有效性的单元格  
        ExcelHelper::set_corporation_excel($objSheet);
        $searchModel = new CorporationSearch();
        $objSheet->setCellValue( 'A1', '序号')
                ->setCellValue( 'B1', $searchModel->getAttributeLabel('base_company_name'))
                ->setCellValue( 'C1', $searchModel->getAttributeLabel('stat'))
                ->setCellValue( 'D1', $searchModel->getAttributeLabel('base_bd'))
                ->setCellValue( 'E1', $searchModel->getAttributeLabel('huawei_account'))
                ->setCellValue( 'F1', $searchModel->getAttributeLabel('base_industry'))
                ->setCellValue( 'G1', $searchModel->getAttributeLabel('contact_park'))
                ->setCellValue( 'H1', $searchModel->getAttributeLabel('contact_address'))
                ->setCellValue( 'I1', $searchModel->getAttributeLabel('intent_set'))
                ->setCellValue( 'J1', $searchModel->getAttributeLabel('intent_number'))
                ->setCellValue( 'K1', $searchModel->getAttributeLabel('intent_amount'))
                ->setCellValue( 'L1', $searchModel->getAttributeLabel('base_company_scale'))
                ->setCellValue( 'M1', $searchModel->getAttributeLabel('base_registered_capital'))
                ->setCellValue( 'N1', $searchModel->getAttributeLabel('base_registered_time'))
                ->setCellValue( 'O1', $searchModel->getAttributeLabel('base_main_business'))
                ->setCellValue( 'P1', $searchModel->getAttributeLabel('base_last_income'))
                ->setCellValue( 'Q1', $searchModel->getAttributeLabel('contact_business_name'))
                ->setCellValue( 'R1', $searchModel->getAttributeLabel('contact_business_job'))
                ->setCellValue( 'S1', $searchModel->getAttributeLabel('contact_business_tel'))
                ->setCellValue( 'T1', $searchModel->getAttributeLabel('contact_technology_name'))
                ->setCellValue( 'U1', $searchModel->getAttributeLabel('contact_technology_job'))
                ->setCellValue( 'V1', $searchModel->getAttributeLabel('contact_technology_tel'))
                ->setCellValue( 'W1', $searchModel->getAttributeLabel('develop_scale'))
                ->setCellValue( 'X1', $searchModel->getAttributeLabel('develop_pattern'))
                ->setCellValue( 'Y1', $searchModel->getAttributeLabel('develop_scenario'))
                ->setCellValue( 'Z1', $searchModel->getAttributeLabel('develop_science'))
                ->setCellValue( 'AA1', $searchModel->getAttributeLabel('develop_language'))
                ->setCellValue( 'AB1', $searchModel->getAttributeLabel('develop_IDE'))
                ->setCellValue( 'AC1', $searchModel->getAttributeLabel('develop_current_situation'))
                ->setCellValue( 'AD1', $searchModel->getAttributeLabel('develop_weakness'));
        $end_time= microtime(true);
        if($end_time-$start_time<1){
            sleep(1);
        }
        
        ExcelHelper::excel_set_headers($format,'企业信息模板');
        
        $objectwriter = \PHPExcel_IOFactory::createWriter($objectPhpExcel, $format);
        $path = 'php://output';
        $objectwriter->save($path);        
        exit();
        
    }

    public function actionCorporationExport() {
        $start_time= microtime(true);
        $searchModel = new CorporationSearch();
        $models = $searchModel->search(Yii::$app->request->queryParams,1000)->getModels();
//        $model = RepairOrder::find()->joinWith('type')->joinWith('area')->joinWith('worker')->all();
//        var_dump(Yii::$app->request->queryParams);
//        exit;
        $fileName= Yii::getAlias('@webroot').'/excel/corporation_temple.xlsx';
        $format = \PHPExcel_IOFactory::identify($fileName);
        $objectreader = \PHPExcel_IOFactory::createReader($format);
        $objectPhpExcel = $objectreader->load($fileName);
  
        $objSheet = $objectPhpExcel->getSheetByName('企业信息'); //这一句为要设置数据有效性的单元格  
        ExcelHelper::set_corporation_excel($objSheet);
        
        $objSheet->setCellValue( 'A1', '序号')
                ->setCellValue( 'B1', $searchModel->getAttributeLabel('base_company_name'))
                ->setCellValue( 'C1', $searchModel->getAttributeLabel('stat'))
                ->setCellValue( 'D1', $searchModel->getAttributeLabel('base_bd'))
                ->setCellValue( 'E1', $searchModel->getAttributeLabel('huawei_account'))
                ->setCellValue( 'F1', $searchModel->getAttributeLabel('base_industry'))
                ->setCellValue( 'G1', $searchModel->getAttributeLabel('contact_park'))
                ->setCellValue( 'H1', $searchModel->getAttributeLabel('contact_address'))
                ->setCellValue( 'I1', $searchModel->getAttributeLabel('intent_set'))
                ->setCellValue( 'J1', $searchModel->getAttributeLabel('intent_number'))
                ->setCellValue( 'K1', $searchModel->getAttributeLabel('intent_amount'))
                ->setCellValue( 'L1', $searchModel->getAttributeLabel('base_company_scale'))
                ->setCellValue( 'M1', $searchModel->getAttributeLabel('base_registered_capital'))
                ->setCellValue( 'N1', $searchModel->getAttributeLabel('base_registered_time'))
                ->setCellValue( 'O1', $searchModel->getAttributeLabel('base_main_business'))
                ->setCellValue( 'P1', $searchModel->getAttributeLabel('base_last_income'))
                ->setCellValue( 'Q1', $searchModel->getAttributeLabel('contact_business_name'))
                ->setCellValue( 'R1', $searchModel->getAttributeLabel('contact_business_job'))
                ->setCellValue( 'S1', $searchModel->getAttributeLabel('contact_business_tel'))
                ->setCellValue( 'T1', $searchModel->getAttributeLabel('contact_technology_name'))
                ->setCellValue( 'U1', $searchModel->getAttributeLabel('contact_technology_job'))
                ->setCellValue( 'V1', $searchModel->getAttributeLabel('contact_technology_tel'))
                ->setCellValue( 'W1', $searchModel->getAttributeLabel('develop_scale'))
                ->setCellValue( 'X1', $searchModel->getAttributeLabel('develop_pattern'))
                ->setCellValue( 'Y1', $searchModel->getAttributeLabel('develop_scenario'))
                ->setCellValue( 'Z1', $searchModel->getAttributeLabel('develop_science'))
                ->setCellValue( 'AA1', $searchModel->getAttributeLabel('develop_language'))
                ->setCellValue( 'AB1', $searchModel->getAttributeLabel('develop_IDE'))
                ->setCellValue( 'AC1', $searchModel->getAttributeLabel('develop_current_situation'))
                ->setCellValue( 'AD1', $searchModel->getAttributeLabel('develop_weakness'));
        
        foreach($models as $key=>$model){
            $k=$key+2;
            $objSheet->setCellValue( 'A'.$k, $key+1)
                    ->setCellValue( 'B'.$k, $model->base_company_name)
                    ->setCellValue( 'C'.$k, $model->Stat)                    
                    ->setCellValue( 'D'.$k, $model->base_bd?($model->baseBd->nickname?$model->baseBd->nickname:$model->baseBd->username):'')
                    ->setCellValue( 'E'.$k, $model->huawei_account)
                    ->setCellValue( 'F'.$k, $model->get_industry($model->id))
                    ->setCellValue( 'G'.$k, implode(',', Parameter::get_para_value('contact_park',$model->contact_park)))
                    ->setCellValue( 'H'.$k, $model->contact_address)
                    ->setCellValue( 'I'.$k, $model->intent_set?$model->intentSet->name:$model->intent_set)                   
                    ->setCellValue( 'J'.$k, $model->intent_number)
                    ->setCellValue( 'K'.$k, $model->intent_amount)
                    ->setCellValue( 'L'.$k, $model->base_company_scale)
                    ->setCellValue( 'M'.$k, $model->base_registered_capital)
                    ->setCellValue( 'N'.$k, $model->base_registered_time>0?date('Y-m-d',$model->base_registered_time):'')                
                    ->setCellValue( 'O'.$k, $model->base_main_business)                   
                    ->setCellValue( 'P'.$k, $model->base_last_income)                  
                    ->setCellValue( 'Q'.$k, $model->contact_business_name)
                    ->setCellValue( 'R'.$k, $model->contact_business_job)
                    ->setCellValue( 'S'.$k, $model->contact_business_tel)
                    ->setCellValue( 'T'.$k, $model->contact_technology_name)
                    ->setCellValue( 'U'.$k, $model->contact_technology_job)
                    ->setCellValue( 'V'.$k, $model->contact_technology_tel)                  
                    ->setCellValue( 'W'.$k, $model->develop_scale)
                    ->setCellValue( 'X'.$k, implode(',', Parameter::get_para_value('develop_pattern',$model->develop_pattern)))
                    ->setCellValue( 'Y'.$k, implode(',', Parameter::get_para_value('develop_scenario',$model->develop_scenario)))
                    ->setCellValue( 'Z'.$k, implode(',', Parameter::get_para_value('develop_science',$model->develop_science)))
                    ->setCellValue( 'AA'.$k, implode(',', Parameter::get_para_value('develop_language',explode(',',$model->develop_language))))
                    ->setCellValue( 'AB'.$k, implode(',', Parameter::get_para_value('develop_IDE',$model->develop_IDE)))
                    ->setCellValue( 'AC'.$k, $model->develop_current_situation)
                    ->setCellValue( 'AD'.$k, $model->develop_weakness);
            $line_stat= implode(',', Corporation::get_stat_list($model->stat));
            //状态选择
            $objSheet->getCell('C'.$k)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(false)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('状态')  
                -> setFormula1('"'.$line_stat.'"'); 
                       
        }
        
        
        $end_time= microtime(true);
        if($end_time-$start_time<1){
            sleep(1);
        }

        ExcelHelper::excel_set_headers($format,'企业信息(' . date('Y-m-d', time()) . ')');
        
        $objectwriter = \PHPExcel_IOFactory::createWriter($objectPhpExcel, $format);
        $path = 'php://output';
        $objectwriter->save($path);        
        exit();
    }
    
    public function actionCorporationImport() {
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
            $base_industry= Industry::getIndustryName();
            $intent_set= Meal::get_meal();
            $contact_park=Parameter::get_type('contact_park');
            $develop_pattern=Parameter::get_type('develop_pattern');
            $develop_scenario=Parameter::get_type('develop_scenario');
            $develop_science=Parameter::get_type('develop_science');
            $develop_language=Parameter::get_type('develop_language');
            $develop_IDE=Parameter::get_type('develop_IDE');
                
            $num=['add'=>0,'update'=>0,'fail'=>0];
               
            $notice_error=[];
            $parameter_add=System::getValue('business_parameter');
            $searchModel = new CorporationSearch();
            $index=[
                'base_company_name'=>$searchModel->getAttributeLabel('base_company_name'),
                'stat'=>$searchModel->getAttributeLabel('stat'),
                'base_bd'=>$searchModel->getAttributeLabel('base_bd'),
                'huawei_account'=>$searchModel->getAttributeLabel('huawei_account'),
                'base_industry'=>$searchModel->getAttributeLabel('base_industry'),
                'contact_park'=>$searchModel->getAttributeLabel('contact_park'),
                'contact_address'=>$searchModel->getAttributeLabel('contact_address'),
                'intent_set'=>$searchModel->getAttributeLabel('intent_set'),
                'intent_number'=>$searchModel->getAttributeLabel('intent_number'),
                'intent_amount'=>$searchModel->getAttributeLabel('intent_amount'),
                'base_company_scale'=>$searchModel->getAttributeLabel('base_company_scale'),
                'base_registered_capital'=>$searchModel->getAttributeLabel('base_registered_capital'),
                'base_registered_time'=>$searchModel->getAttributeLabel('base_registered_time'),
                'base_main_business'=>$searchModel->getAttributeLabel('base_main_business'),
                'base_last_income'=>$searchModel->getAttributeLabel('base_last_income'),
                'contact_business_name'=>$searchModel->getAttributeLabel('contact_business_name'),
                'contact_business_job'=>$searchModel->getAttributeLabel('contact_business_job'),
                'contact_business_tel'=>$searchModel->getAttributeLabel('contact_business_tel'),
                'contact_technology_name'=>$searchModel->getAttributeLabel('contact_technology_name'),
                'contact_technology_job'=>$searchModel->getAttributeLabel('contact_technology_job'),
                'contact_technology_tel'=>$searchModel->getAttributeLabel('contact_technology_tel'),
                'develop_scale'=>$searchModel->getAttributeLabel('develop_scale'),
                'develop_pattern'=>$searchModel->getAttributeLabel('develop_pattern'),
                'develop_scenario'=>$searchModel->getAttributeLabel('develop_scenario'),
                'develop_science'=>$searchModel->getAttributeLabel('develop_science'),
                'develop_language'=>$searchModel->getAttributeLabel('develop_language'),
                'develop_IDE'=>$searchModel->getAttributeLabel('develop_IDE'),
                'develop_current_situation'=>$searchModel->getAttributeLabel('develop_current_situation'),
                'develop_weakness'=>$searchModel->getAttributeLabel('develop_weakness'),
                ];
            
            //项目处理
            if(isset($datas[0])){
                $keys= array_filter(array_keys($datas[0]));
                if(!in_array($index['base_company_name'], $keys)){
                    Yii::$app->session->setFlash('error', '文件首行不存在<<'.$index['base_company_name'].'>>字段');
                    return false;
                }
               
            }else{
                Yii::$app->session->setFlash('error', '没有有效数据');
                return false;
            }
            
            foreach ($datas as $key=>$data) {
//                    Yii::$app->session->setFlash('success', json_encode($datas,256));
//                    return true;                          
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
            Yii::$app->session->setFlash('warning', '新增'.$num['add'].'家，更新'.$num['update'].'家，失败'.$num['fail'].'家。');
                  
         
        } else {
            Yii::$app->session->setFlash('error', '上传失败。');
        }
        return true;
        
    }
    
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
