<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\actions\IndexAction;
use project\models\ActivitySearch;
use project\models\ColumnSetting;


class ActivityController extends Controller { 
    
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $end = strtotime('today');
                    $start = strtotime('-30 days',$end);
                    $sum=Yii::$app->request->get('sum',1);
                    $dev=Yii::$app->request->get('dev',0);

                    if (Yii::$app->request->get('range')) {
                        $range = explode('~', Yii::$app->request->get('range'));
                        $start = isset($range[0])? strtotime($range[0]) : $start;
                        $end = isset($range[1])&& (strtotime($range[1]) < $end)? strtotime($range[1]): $end;
                    }

                    $searchModel = new ActivitySearch();
                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$start-86400,$end,$sum);

                    $column= ColumnSetting::get_column_content(Yii::$app->user->identity->id,'activity');

                    return [  
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'start' => $start,
                        'end' => $end,
                        'sum'=>$sum,
                        'dev'=>$dev,
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
}
