<?php

namespace project\controllers;

use Yii;
use project\models\CloudSubsidy;
use project\models\CloudSubsidySearch;
use yii\web\Controller;
use project\actions\IndexAction;
use project\actions\UpdateAction;
use project\actions\DeleteAction;
use project\actions\CreateAction;
use project\components\ExcelHelper;
use project\models\Group;
use project\models\Parameter;

/**
 * SubsidyController implements the CRUD actions for CloudSubsidy model.
 */
class SubsidyController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'subsidy-list' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $searchModel = new CloudSubsidySearch();
                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                    return [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                    ];               
                }
            ],
            'subsidy-create' => [
                'class' => CreateAction::className(),
                'modelClass' => CloudSubsidy::className(),
                'successRedirect'=>Yii::$app->request->referrer,
                'ajax'=>true,
                'default_group'=>true,
            ],
            'subsidy-update' => [
                'class' => UpdateAction::className(),
                'modelClass' => CloudSubsidy::className(),
                'successRedirect'=>'subsidy-list',
                'ajax'=>true,
            ],
            'subsidy-delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => CloudSubsidy::className(),
            ],

        ];
    }
    
    public function actionSubsidyExport() {
        $start_time= microtime(true);
        $searchModel = new CloudSubsidySearch();
        $models = $searchModel->search(Yii::$app->request->queryParams,1000)->getModels();
       
        $fileName= Yii::getAlias('@webroot').'/excel/subsidy_temple.xlsx';
        $format = \PHPExcel_IOFactory::identify($fileName);
        $objectreader = \PHPExcel_IOFactory::createReader($format);
        $objectPhpExcel = $objectreader->load($fileName);
        
        $objectPhpExcel->getActiveSheet()->setCellValue( 'A1', '序号')
                ->setCellValue( 'B1', $searchModel->getAttributeLabel('corporation_name'))
                ->setCellValue( 'C1', $searchModel->getAttributeLabel('subsidy_bd'))
                ->setCellValue( 'D1', $searchModel->getAttributeLabel('annual'))
                ->setCellValue( 'E1', $searchModel->getAttributeLabel('subsidy_time'))
                ->setCellValue( 'F1', $searchModel->getAttributeLabel('subsidy_amount'))
                ->setCellValue( 'G1', $searchModel->getAttributeLabel('subsidy_note'));
        
        $group_count=count(Group::get_user_group(Yii::$app->user->identity->id));
        
        if($group_count>1){
            $objectPhpExcel->getActiveSheet()->setCellValue( 'H1', $searchModel->getAttributeLabel('group_id'));
        }

        foreach($models as $key=>$model){
            $k=$key+2;
            $objectPhpExcel->getActiveSheet()->setCellValue( 'A'.$k, $key+1)
                    ->setCellValue( 'B'.$k, $model->corporation_name)
                    ->setCellValue( 'C'.$k, $model->subsidy_bd?($model->subsidyBd->nickname?$model->subsidyBd->nickname:$model->subsidyBd->username):'')
                    ->setCellValue( 'D'.$k, implode(',', Parameter::get_para_value('allocate_annual',$model->annual)))
                    ->setCellValue( 'E'.$k, $model->subsidy_time)
                    ->setCellValue( 'F'.$k, $model->subsidy_amount)
                    ->setCellValue( 'G'.$k, $model->subsidy_note);
            
            if($group_count>1){
                $objectPhpExcel->getActiveSheet()->setCellValue( 'H'.$k, $model->group_id?$model->group->title:$model->group_id);
            }
                    
        }
        
        
        $end_time= microtime(true);
        if($end_time-$start_time<1){
            sleep(1);
        }

        ExcelHelper::excel_set_headers($format,'补贴信息(' . date('Y-m-d', time()) . ')');
        
        $objectwriter = \PHPExcel_IOFactory::createWriter($objectPhpExcel, $format);
        $path = 'php://output';
        $objectwriter->save($path);        
        exit();
    }

}
