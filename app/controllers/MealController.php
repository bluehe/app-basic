<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\actions\IndexAction;
use yii\data\ActiveDataProvider;
use app\models\Meal;


class MealController extends Controller { 
    
    public function actions()
    {
        return [
            'meal-list' => [
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

        ];
    }
    
}
