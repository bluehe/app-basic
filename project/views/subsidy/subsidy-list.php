<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use project\models\User;
use kartik\daterange\DateRangePicker;
use project\models\Parameter;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '补贴管理';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['subsidy/subsidy-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="subsidy-index">

    <div class="box box-primary">
        <div class="box-body">

            <p>
            <?= Html::a('增加补贴', ['#'], ['data-toggle' => 'modal', 'data-target' => '#subsidy-modal','class' => 'btn btn-success subsidy-create']) ?>
            <?= Html::a('<i class="fa fa-share-square-o"></i>全部导出', ['subsidy-export?'.Yii::$app->request->queryString], ['class' => 'btn btn-warning pull-right']) ?>
            </p>
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'corporation_name',
                        'value' =>
                            function($model) {
                                return $model->corporation_id?Html::a($model->corporation_name, ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','data-id'=>$model->corporation_id,'class' => 'corporation-view',]):$model->corporation_name;                               
                            },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'subsidy_bd',
                        'value' => 
                            function($model) {
                                return $model->subsidy_bd?($model->subsidyBd->nickname?$model->subsidyBd->nickname:$model->subsidyBd->username):'';
                            },
                        'filter' => User::get_bd(),
                    ],
                    [
                        'attribute' => 'annual',
                        'value' => 
                            function($model) {
                                return implode(',', Parameter::get_para_value('allocate_annual',$model->annual));
                            },
                        'filter' => Parameter::get_type('allocate_annual'),
//                        'visible'=> is_array($column)&&in_array('annual',$column),
                    ],
                    ['attribute' =>'subsidy_time',
                    'filter' => DateRangePicker::widget([
                        'name' => 'ClouldSubsidySearch[subsidy_time]',
                        'useWithAddon' => true,
                        'presetDropdown' => true,
                        'convertFormat' => true,
                        'value' => Yii::$app->request->get('ClouldSubsidySearch')['subsidy_time'],
                        'pluginOptions' => [
                            'timePicker' => false,
                            'locale' => [
                                'format' => 'Y-m-d',
                                'separator' => '~'
                            ],
                            'linkedCalendars' => false,
                            'opens'=>'right'
                        ],
                    ]),
                    ],
                    'subsidy_amount',
                    'subsidy_note:ntext',
                     
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {delete}', //只需要展示删除和更新
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                                return in_array(Yii::$app->user->identity->role, [User::ROLE_OB_DATA,User::ROLE_PM])||Yii::$app->authManager->getAssignment(Yii::$app->authManager->getRole('superadmin')->name, Yii::$app->user->identity->id)||$model->subsidy_bd==Yii::$app->user->identity->id?Html::a('<i class="fa fa-pencil"></i> 修改', ['#'], ['data-toggle' => 'modal', 'data-target' => '#subsidy-modal', 'class' => 'btn btn-primary btn-xs subsidy-update',]):'';
                            },
                            'delete' => function($url, $model, $key) {
                                return in_array(Yii::$app->user->identity->role, [User::ROLE_PM])||Yii::$app->authManager->getAssignment(Yii::$app->authManager->getRole('superadmin')->name, Yii::$app->user->identity->id)||$model->subsidy_bd==Yii::$app->user->identity->id?Html::a('<i class="fa fa-trash-o"></i> 删除', ['subsidy-delete', 'id' => $key], ['class' => 'btn btn-danger btn-xs','data-confirm' =>'确定删除吗？','data-method' => 'post',]):'';
                            },
                        ],
                        'visible'=> in_array(Yii::$app->user->identity->role, [User::ROLE_OB_DATA,User::ROLE_BD,User::ROLE_PM])||Yii::$app->authManager->getAssignment(Yii::$app->authManager->getRole('superadmin')->name, Yii::$app->user->identity->id),
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'subsidy-modal',
    'header' => '<h4 class="modal-title"></h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
Modal::begin([
    'id' => 'corporation-modal',
    'header' => null,
    'closeButton'=>false,    
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('subsidy') ?>
    
    $('.subsidy-index').on('click', '.corporation-view', function () {
        //$('.modal-title').html('企业查看');
        $('#corporation-modal .modal-body').html('');
        $.get('<?= Url::toRoute('corporation/corporation-view') ?>',{id: $(this).data('id')},
                function (data) {
                    $('#corporation-modal .modal-body').html(data);
                }
        );
    });
    
    $('.subsidy-index').on('click', '.subsidy-create', function () {
        $('#subsidy-modal .modal-title').html('增加');
        $('#subsidy-modal .modal-body').html('');
        $.get('<?= Url::toRoute('subsidy-create') ?>',
                function (data) {
                    $('#subsidy-modal .modal-body').html(data);
                }
        );
    });
    
    $('.subsidy-index').on('click', '.subsidy-update', function () {
        $('#subsidy-modal .modal-title').html('修改');
        $('#subsidy-modal .modal-body').html('');
        $.get('<?= Url::toRoute('subsidy-update') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#subsidy-modal .modal-body').html(data);
                }
        );
    });
    


<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['subsidy'], \yii\web\View::POS_END); ?>