<?php

use yii\helpers\Html;
use yii\grid\GridView;
use project\models\CorporationMeal;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use project\models\User;
use project\models\Meal;
use kartik\daterange\DateRangePicker;
use project\components\CommonHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '字段管理';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['field/field-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="field-index">

    <div class="box box-primary">
        <div class="box-body">

            <p>
                <?= Html::a('添加字段', ['#'], ['data-toggle' => 'modal', 'data-target' => '#field-modal','class' => 'btn btn-success field-create']) ?>
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
                        'attribute' => 'corporation_id',
                        'value' =>
                            function($model) {
                                return Html::a($model->corporation->base_company_name, ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','data-id'=>$model->corporation_id,'class' => 'corporation-view',]);
                            },
                        'format' => 'raw',
                    ],
                    'huawei_account',
                    [
                        'attribute' => 'bd',
                        'value' => 
                            function($model) {
                                return $model->bd?($model->bd0->nickname?$model->bd0->nickname:$model->bd0->username):'';
                            },
                        'filter' => User::get_bd(),
                    ],
                    [
                        'attribute' => 'meal_id',
                        'value' => 
                            function($model) {
                                return $model->meal_id?$model->meal->name:'其他';
                            },
                        'filter' => Meal::get_meal(null),
                    ],
                    'number',
                    'amount',
                    ['attribute' =>'start_time','value' =>function($model) {return $model->start_time>0?date('Y-m-d',$model->start_time):'';},
                    'filter' => DateRangePicker::widget([
                        'name' => 'CorporationMealSearch[start_time]',
                        'useWithAddon' => true,
                        'presetDropdown' => true,
                        'convertFormat' => true,
                        'value' => Yii::$app->request->get('CorporationMealSearch')['start_time'],
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
//                    'end_time:date',
                     
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {delete}', //只需要展示删除和更新
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                                return CommonHelper::corporationRule('update', $model->corporation_id)?Html::a('<i class="fa fa-pencil"></i> 修改', ['#'], ['data-toggle' => 'modal', 'data-target' => '#allocate-modal', 'class' => 'btn btn-primary btn-xs allocate-update',]):'';
                            },
                            'delete' => function($url, $model, $key) {
                                return CorporationMeal::get_end_time($model->corporation_id)==$model->end_time&&CommonHelper::corporationRule('delete', $model->corporation_id)?Html::a('<i class="fa fa-trash-o"></i> 删除', ['allocate-delete', 'id' => $key], ['class' => 'btn btn-danger btn-xs','data-confirm' =>'企业状态及记录会回退并不能恢复，确定删除吗？','data-method' => 'post',]):'';
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
    'id' => 'field-modal',
    'header' => '<h4 class="modal-title"></h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('field') ?>
    
    $('.field-index').on('click', '.field-create', function () {
        $('#field-modal .modal-title').html('添加');
        $('#field-modal .modal-body').html('');
        $.get('<?= Url::toRoute('field-create') ?>',
                function (data) {
                    $('#field-modal .modal-body').html(data);
                }
        );
    });
    
    $('.field-index').on('click', '.field-update', function () {
        $('#field-modal .modal-title').html('修改');
        $('#field-modal .modal-body').html('');
        $.get('<?= Url::toRoute('field-update') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#field-modal .modal-body').html(data);
                }
        );
    });
    


<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['field'], \yii\web\View::POS_END); ?>