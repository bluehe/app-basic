<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use project\actions\IndexAction;
use project\actions\DeleteAction;
use project\actions\CreateAction;
use project\actions\UpdateAction;
use common\models\Crontab;


class CrontabController extends Controller { 
    
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $dataProvider = new ActiveDataProvider([
                        'query' => Crontab::find(),
                        'sort' => ['defaultOrder' => [
                            'id'=>SORT_DESC,
                        ]],
                    ]);
                    return [
                        'dataProvider' => $dataProvider,
                    ];
                }
            ],
            'create' => [
                'class' => CreateAction::className(),
                'modelClass' => Crontab::className(),
                'successRedirect'=>'index',
                'ajax'=>true,
            ],
            'update' => [
                'class' => UpdateAction::className(),
                'modelClass' => Crontab::className(),
                'successRedirect'=>Yii::$app->request->referrer,
                'ajax'=>true,
            ],
            'delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => Crontab::className(),
            ],

        ];
    }
    
    
}
