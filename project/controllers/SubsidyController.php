<?php

namespace project\controllers;

use Yii;
use project\models\ClouldSubsidy;
use project\models\ClouldSubsidySearch;
use yii\web\Controller;
use project\actions\IndexAction;
use project\actions\UpdateAction;
use project\actions\DeleteAction;
use project\actions\CreateAction;
use project\components\ExcelHelper;
use project\models\Corporation;
use yii\helpers\Html;
use project\models\UserGroup;
use project\models\User;
use project\models\Group;
use project\models\Parameter;

/**
 * SubsidyController implements the CRUD actions for ClouldSubsidy model.
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
                    $searchModel = new ClouldSubsidySearch();
                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                    return [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                    ];               
                }
            ],
            'subsidy-create' => [
                'class' => CreateAction::className(),
                'modelClass' => ClouldSubsidy::className(),
                'successRedirect'=>Yii::$app->request->referrer,
                'ajax'=>true,
                'default_group'=>true,
            ],
            'subsidy-update' => [
                'class' => UpdateAction::className(),
                'modelClass' => ClouldSubsidy::className(),
                'successRedirect'=>'subsidy-list',
                'ajax'=>true,
            ],
            'subsidy-delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => ClouldSubsidy::className(),
            ],

        ];
    }
    
    public function actionGroupCorporation($id) {

        $corporation= Corporation::get_corporation_id($id);

        $str_corporation = Html::tag('option', '其他', array('value' => ''));
        if ($corporation) {   
            foreach ($corporation as $value => $name) {
                $str_corporation .= Html::tag('option', Html::encode($name), array('value' => $value));
            }
        }
        
        
        $user = UserGroup::get_group_userid($id);
        
        $bds = User::get_bd(null, $user);

        $str = Html::tag('option', '', array('value' => ''));
        if ($user&&$bds) {   
            foreach ($bds as $value => $name) {
                $str .= Html::tag('option', Html::encode($name), array('value' => $value));
            }
        }
        return json_encode(['corporation'=>$str_corporation,'bd'=>$str]);  
        
    }
    
    public function actionSubsidyExport() {
        $start_time= microtime(true);
        $searchModel = new ClouldSubsidySearch();
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
