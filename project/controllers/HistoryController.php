<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use project\actions\IndexAction;
use project\actions\CreateAction;
use project\actions\UpdateAction;
use project\actions\DeleteAction;


class HistoryController extends Controller { 
    
    public function actions()
    {
        return [
            'history-list' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $dataProvider = new ActiveDataProvider([
                        'query' => Meal::find(),
                        'sort' => ['defaultOrder' => [
                            'order_sort' => SORT_ASC,
                            'id' => SORT_DESC,
                        ]],
                    ]);
                    return [
                        'dataProvider' => $dataProvider,
                    ];
                }
            ],
            'history-create' => [
                'class' => CreateAction::className(),
                'modelClass' => Meal::className(),
                'successRedirect'=>'field-list',
                'ajax'=>true,
            ],
            'history-update' => [
                'class' => UpdateAction::className(),
                'modelClass' => Meal::className(),
                'successRedirect'=>Yii::$app->request->referrer,
                'ajax'=>true,
            ],
            'history-delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => Meal::className(),
            ],

        ];
    }
    
}
