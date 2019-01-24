<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use project\actions\IndexAction;
use project\actions\DeleteAction;
use project\actions\CreateAction;
use project\actions\UpdateAction;
use project\models\Group;


class GroupController extends Controller { 
    
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $dataProvider = new ActiveDataProvider([
                        'query' => Group::find(),
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
                'modelClass' => Group::className(),
                'successRedirect'=>'index',
                'ajax'=>true,
            ],
            'update' => [
                'class' => UpdateAction::className(),
                'modelClass' => Group::className(),
                'successRedirect'=>Yii::$app->request->referrer,
                'ajax'=>true,
            ],
            'delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => Group::className(),
            ],

        ];
    }
    
    
}
