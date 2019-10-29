<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\actions\IndexAction;
use project\models\ActivitySearch;
use project\models\ColumnSetting;
use project\components\ExcelHelper;
use project\models\ActivityChange;
use project\models\Group;


class ActivityController extends Controller { 
    
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $end = strtotime('today');
                    $start = strtotime('-1 months +1 days',$end);
                    $sum=Yii::$app->request->get('sum',1);
                    $dev=Yii::$app->request->get('dev',0);
                    $annual=Yii::$app->request->get('annual');
                    
                    if (Yii::$app->request->get('range')) {
                        $range = explode('~', Yii::$app->request->get('range'));
                        $start = isset($range[0])? strtotime($range[0]) : $start;
                        $end = isset($range[1])&& (strtotime($range[1]) < $end)? strtotime($range[1]): $end;
                    }

                    $searchModel = new ActivitySearch();
                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$start-86400,$end,$sum,$annual);

                    $column= ColumnSetting::get_column_content(Yii::$app->user->identity->id,'activity');

                    return [  
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'start' => $start,
                        'end' => $end,
                        'sum'=>$sum,
                        'dev'=>$dev,
                        'annual'=>$annual,
                        'column'=>$column,
                    ];
                              
                }
            ],
           

        ];
    }
    
    public function actionColumn() {

        $model=ColumnSetting::get_column(Yii::$app->user->identity->id,'activity');
        if($model==null){
            $model=new ColumnSetting();
            $model->uid=Yii::$app->user->identity->id;
            $model->type='activity';
            
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
    
    public function actionExport() {
        $end = strtotime('today');
        $start = strtotime('-1 months +1 days',$end);
        $sum=Yii::$app->request->get('sum',1);
        $annual=Yii::$app->request->get('annual');
        
        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $start = isset($range[0]) ? strtotime($range[0]) : $start;
            $end = isset($range[1])&& (strtotime($range[1]) < $end) ? strtotime($range[1]): $end;
        }
        
        $start_time= microtime(true);
        $searchModel = new ActivitySearch();
        $models = $searchModel->search(Yii::$app->request->queryParams,$start-86400,$end,$sum,$annual,1000)->getModels();
        $column= ColumnSetting::get_column_content(Yii::$app->user->identity->id,'activity');

        
        $fileName= Yii::getAlias('@webroot').'/excel/activity_temple.xlsx';
        $format = \PHPExcel_IOFactory::identify($fileName);
        $objectreader = \PHPExcel_IOFactory::createReader($format);
        $objectPhpExcel = $objectreader->load($fileName);
        $objSheet = $objectPhpExcel->getActiveSheet();
        
        $group_count=count(Group::get_user_group(Yii::$app->user->identity->id));
        $r='A';
        if($group_count>1){
            $objSheet->insertNewColumnBefore('A');
            $objSheet->setCellValue( 'A1', $searchModel->getAttributeLabel('group_id'))->getStyle( 'A1')->getFont()->setBold(true);
            $r++;
        }
        
        $objSheet->setCellValue( $r++.'1', '时间段')
            ->setCellValue( $r++.'1', '客户经理')
            ->setCellValue( $r++.'1', '公司')
            ->setCellValue( $r++.'1', $searchModel->getAttributeLabel('is_act'));
        
        if($column){
            foreach($column as $c){
                $objSheet->setCellValue( $r.'1', $searchModel->getAttributeLabel($c));
                $r++;
            }
        }
                
      
        foreach($models as $key=>$model){
            $k=$key+2;
            $r='A';
            if($group_count>1){
                $objSheet->setCellValue( 'A'.$k, $model->group_id?$model->group->title:$model->group_id);
                $r++;
            }
            $objSheet->setCellValue( $r++.$k, date('Y-m-d',$model->start_time+86400).' ~ '.date('Y-m-d',$model->end_time))
                ->setCellValue( $r++.$k, $model->bd_id?($model->bd->nickname?$model->bd->nickname:$model->bd->username):'')
                ->setCellValue( $r++.$k, $model->corporation->base_company_name)
                ->setCellValue( $r++.$k, $model->Act);                       
            
            foreach($column as $c){
                if(preg_match("/\S+_d$/",$c)){
                    $str=substr($c,0,-2);
                    $c_v= !empty($model->data->$str)?$model->data->$str:'';                    
                }else{
                    $c_v=$model->$c;
                    if($c_v!=0){
                        //字体颜色-活跃单元格-活跃/不活跃
                        $font_color=$c_v>0 ? 'FF00A65A' : 'FFDD4B39';
                        $objSheet->getStyle($r.$k)->getFont()->getColor()->setARGB($font_color);
                    }    
                }
                $objSheet->setCellValue( $r.$k, $c_v?$c_v:'');
                           
                $r++;
            }
            
            //填充色-时间段单元格-新增/减少
            if(!Yii::$app->request->get('sum',1)&&in_array($model->type,[ActivityChange::TYPE_ADD,ActivityChange::TYPE_DELETE])){
                $objSheet->getStyle('A'.$k)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $fill_color_a =$model->type== ActivityChange::TYPE_ADD ?'FF39CCCC':'FFFF851B';
                $objSheet->getStyle('A'.$k)->getFill()->getStartColor()->setARGB($fill_color_a);
            }
            //填充色-公司单元格-活跃/不活跃
            if(ActivityChange::is_activity($model)){
                $objSheet->getStyle('C'.$k)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objSheet->getStyle('C'.$k)->getFill()->getStartColor()->setARGB('FF00A65A');
            }
            //字体颜色-活跃单元格-活跃/不活跃
            $font_color_d=$model->is_act== ActivityChange::ACT_Y ? 'FF00A65A' : ($model->is_act== ActivityChange::ACT_N ? 'FFDD4B39' : 'FF000000');
            $objSheet->getStyle('D'.$k)->getFont()->getColor()->setARGB($font_color_d);
            
            //设置行样式
            $objSheet->getRowDimension($k)->setRowHeight(15);
        }
        
        
        $end_time= microtime(true);
        if($end_time-$start_time<1){
            sleep(1);
        }

        ExcelHelper::excel_set_headers($format,'活跃数据(' . date('Y-m-d', time()) . ')');
        
        $objectwriter = \PHPExcel_IOFactory::createWriter($objectPhpExcel, $format);
        $path = 'php://output';
        $objectwriter->save($path);        
        exit();        
    }
}
