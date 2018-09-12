<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use project\actions\IndexAction;
use project\models\CorporationMeal;
use project\models\CorporationMealSearch;
use project\actions\CreateAction;
use project\actions\UpdateAction;
use project\actions\DeleteAction;


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
   
    
//    public function actionAllocateUpdate($id) {
//        $model = CorporationMeal::findOne($id);
//          
//        if ($model->load(Yii::$app->request->post())) {
//
//            if (Yii::$app->request->isAjax) {
//                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
//                return \yii\widgets\ActiveForm::validate($model);
//            }
//            
//            if($model->save()){
//                Yii::$app->session->setFlash('success', '修改成功。');
//            }else{
//                Yii::$app->session->setFlash('error', '修改失败。');
//            }
//            return $this->redirect(Yii::$app->request->referrer);
//        }else{
//            $model->start_time=$model->start_time>0?date('Y-m-d',$model->start_time):'';
//        }
//        return $this->renderAjax('allocate-update', [
//                'model' => $model,
//        ]);
//                
//    }
    
}
