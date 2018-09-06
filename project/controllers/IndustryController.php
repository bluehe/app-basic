<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\actions\IndexAction;
use yii\data\ArrayDataProvider;
use app\models\Industry;
use app\actions\CreateAction;
use app\actions\UpdateAction;
use app\actions\DeleteAction;


class IndustryController extends Controller { 
    
    public function actions()
    {
        return [
            'industry-list' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $data = Industry::getIndustries();
                    $dataProvider = new ArrayDataProvider([
                        'allModels' => $data,
                        'pagination' => [
                            'pageSize' => -1
                        ]
                    ]);
                    return [
                        'dataProvider' => $dataProvider,
                    ];
                }
            ],
            'industry-create' => [
                'class' => CreateAction::className(),
                'modelClass' => Industry::className(),
                'viewFile'=>'industry-update',
                'successRedirect'=>'industry-list',
                'ajax'=>true,
            ],
            'industry-update' => [
                'class' => UpdateAction::className(),
                'modelClass' => Industry::className(),
                'successRedirect'=>'industry-list',
                'ajax'=>true,
            ],
            'industry-delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => Industry::className(),
            ],

        ];
    }
    
}
