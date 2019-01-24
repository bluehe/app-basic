<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use project\actions\IndexAction;
use project\models\Meal;
use project\actions\CreateAction;
use project\actions\UpdateAction;
use project\actions\DeleteAction;
use project\models\UserGroup;


class MealController extends Controller { 
    
    public function actions()
    {
        return [
            'meal-list' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $dataProvider = new ActiveDataProvider([
                        'query' => Meal::find()->andWhere(['group_id'=> UserGroup::get_user_groupid(Yii::$app->user->identity->id)])->orWhere(['group_id'=>NULL]),
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
            'meal-create' => [
                'class' => CreateAction::className(),
                'modelClass' => Meal::className(),
                'successRedirect'=>'meal-list',
            ],
            'meal-update' => [
                'class' => UpdateAction::className(),
                'modelClass' => Meal::className(),
                'successRedirect'=>'meal-list',
                'auth_group'=>true,
            ],
            'meal-delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => Meal::className(),
                'auth_group'=>true,
            ],

        ];
    }
    
}
