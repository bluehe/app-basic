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
    
}
