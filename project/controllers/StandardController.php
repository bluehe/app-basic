<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use project\actions\IndexAction;
use project\actions\CreateAction;
use project\actions\UpdateAction;
use project\models\Standard;


class StandardController extends Controller { 
    
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $dataProvider = new ActiveDataProvider([
                        'query' => Standard::find(),
                        'sort' => ['defaultOrder' => [                            
                            'field' => SORT_ASC,
                            'type'=>SORT_DESC,
                        ]],
                    ]);
                    return [
                        'dataProvider' => $dataProvider,
                    ];
                }
            ],
            'create' => [
                'class' => CreateAction::className(),
                'modelClass' => Standard::className(),
                'successRedirect'=>'index',
                'ajax'=>true,
            ],
            'update' => [
                'class' => UpdateAction::className(),
                'modelClass' => Standard::className(),
                'successRedirect'=>Yii::$app->request->referrer,
                'ajax'=>true,
            ],

        ];
    }
    
    public function actionDelete($type,$field)
    {
        $model = Standard::findOne(['type'=>$type,'field'=>$field]);
        $stat='error';
        if ($model !== null) {         
            if($model->delete()){
                $stat='success';
            }else{
                $stat='fail';
            }
        }        
        return json_encode(['stat' => $stat]);
    }
    
}
