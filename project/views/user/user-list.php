<?php

use yii\helpers\Html;
use yii\grid\GridView;
use project\models\User;
use project\models\Group;

/* @var $this yii\web\View */
/* @var $searchModel dh\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '用户管理';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['user/user-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-index">

    <div class="box box-primary">
        <div class="box-body">
            <?php // echo $this->render('_search', ['model' => $searchModel]);  ?>



            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'filterModel' => $searchModel,
                'columns' => [
                    
                    [
                        'label' => '头像',
                        'value' =>
                        function($model) {
                            return Html::img($model->avatar ? $model->avatar : '@web/image/user.png', ['class' => 'img-rounded', 'width' => 23, 'height' => 23]);
                        },
                        'format' => 'raw',
                    ],
                    'username',
                    
                    'nickname',
                    'email',
                    'tel',
                    [
                        'attribute' => 'role',                       
                        'value' =>
                        function($model) {
                           return $model->Role;
                        },
                        'filter' => User::$List['role'],
                      
                    ],
                    [
                        'attribute' => 'user_color',
                        'value' =>
                        function($model) {
                            return $model->user_color?Html::tag('span',$model->user_color,['class'=>'label', 'style'=>['background'=>'#'.$model->user_color,'padding'=>'4px 6px']]):'';
                        },
                        'format' => 'raw',
                    ],
                    [
                        'label' => '项目',
                        'format' => 'raw',
                        'value' =>
                        function($model) {
                            $group = Group::get_user_group($model->id);
                            return implode(',', $group);
                        },
                        'headerOptions' => ['width' => '60'],
                    ],
                  
                    [
                        'attribute' => 'last_login',
                        'value' =>
                        function($model) {
                            return $model->last_login > 0 ? date('Y-m-d H:i', $model->last_login) : '';
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' =>
                        function($model) {
                            return date('Y-m-d H:i', $model->created_at);   //主要通过此种方式实现
                        },
                        'filter' =>false,
                    ],
              
                    [
                        'attribute' => 'status',
                        'value' =>
                        function($model, $key) {
                            return Html::a($model->Status, ['user/user-change', 'id' => $key], ['class' => 'btn btn-xs ' . ($model->status == User::STATUS_ACTIVE ? 'btn-success' : 'btn-danger')]);
                        },
                        'format' => 'raw',
                        'filter' => User::$List['status'],
                        'headerOptions' => ['width' => '80'],
                    ],
                    // 'updated_at',
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update}', //只需要展示删除和更新
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                                $auth = Yii::$app->authManager;
                                $Role_admin=$auth->getRole('superadmin');
                                $disabled =($auth->getAssignment($Role_admin->name, $model->id)||$model->role== User::ROLE_PM)&&!$auth->getAssignment($Role_admin->name, Yii::$app->user->identity->id)&&$model->id!=Yii::$app->user->identity->id;
                                return $disabled?'':Html::a('<i class="fa fa-pencil"></i> 修改', ['user-update', 'id' => $key], ['class' => 'btn btn-primary btn-xs',]);
                            },
                        ],
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>