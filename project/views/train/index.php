<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;
use project\models\Train;
use project\models\User;
use project\models\TrainUser;
use project\models\Group;
use project\models\UserGroup;
/* @var $this yii\web\View */
/* @var $searchModel rky\models\VisitSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '培训咨询';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['train/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="train-index">

    <div class="box box-primary">
        <div class="box-body">
                             
            <?php Pjax::begin(); ?>
            
            <p class="text-right">
                <?= count(Group::get_user_group(Yii::$app->user->identity->id))?Html::a('添加记录', ['#'], ['data-toggle' => 'modal', 'data-target' => '#train-modal','class' => 'btn btn-success pull-left train-create']):'' ?>
                
                 <?= Html::a('<i class="fa fa-share-square-o"></i>全部导出', ['export?'.Yii::$app->request->queryString], ['class' => 'btn btn-warning']) ?>
            </p>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'group_id',
                        'value' =>function($model) {
                            return $model->group_id?$model->group->title:$model->group_id;   //主要通过此种方式实现
                        },
                        'format' => 'raw',
                        'filter' => Group::get_user_group(Yii::$app->user->identity->id),   
                        'visible'=> count(UserGroup::get_user_groupid(Yii::$app->user->identity->id))>1,
                    ],
                    [
                        'attribute' => 'train_start',
                        'label'=>'时间',
                        'value' =>
                        function($model) {
                            return date('Y-m-d H:i', $model->train_start). ' ~ '. (date('Y-m-d', $model->train_start)==date('Y-m-d', $model->train_end)?date('H:i', $model->train_end):date('Y-m-d H:i', $model->train_end));   //主要通过此种方式实现
                        },
                        'filter' => DateRangePicker::widget([
                            'name' => 'TrainSearch[train_start]',
                            'useWithAddon' => true,
                            'presetDropdown' => true,
                            'convertFormat' => true,
                            'value' => Yii::$app->request->get('TrainSearch')['train_start'],
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
                        'headerOptions' => ['width' => '235'],
                    ],
                    [
                        'attribute' => 'train_type',
                        'value' =>
                            function($model) {
                                return $model->TrainType;
                            },
                        'filter' => Train::$List['train_type'], 
                    ],
                    [
                        'attribute' => 'train_name',
                        'value' =>
                            function($model) {
                                return $model->corporation_id?Html::a($model->train_name, ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','data-id'=>$model->corporation_id,'class' => 'corporation-view',]):$model->train_name;
                            },
                        'format' => 'raw',
                    ],          

        //            'train_address',
                    //'train_num',
                    [
                        'attribute' => 'sa',
                        'value' =>
                                function($model) {
                                    return $model->get_username($model->id,'sa');   //主要通过此种方式实现
                                },
                        'filter' => User::get_role('sa',User::STATUS_ACTIVE,UserGroup::get_group_userid(array_keys(Group::get_user_group(Yii::$app->user->identity->id)))), 
                    ],
                    [
                        'attribute' => 'other',
                        'value' =>
                                function($model) {
                                    return $model->get_username($model->id,'other');   //主要通过此种方式实现
                                },
                        'filter' => User::get_role('other',User::STATUS_ACTIVE,UserGroup::get_group_userid(array_keys(Group::get_user_group(Yii::$app->user->identity->id)))), 
                    ],
                    //'other',
                    // 'note:ntext',
                    // 'reply_uid',
                    // 'reply_at',
                    [
                        'attribute' => 'train_stat',
                        'value' =>
                            function($model) {
                                return Html::tag('span', $model->TrainStat,['class' => ($model->train_stat== Train::STAT_CREATED ? 'text-red' : ($model->train_stat== Train::STAT_ORDER ? 'text-yellow' : ''))]);
                            },
                        'format' => 'raw',
                        'filter' => Train::$List['train_stat'],    
                    ],

                    ['class' => 'yii\grid\ActionColumn',
                    'header' => '操作',
                    'template' => '{view} {update} {end} {cancel} {order} {refuse} {delete}', //只需要展示删除和更新
                    'buttons' => [
                        'view' => function($url, $model, $key) {
                                    return Html::a('<i class="fa fa-eye"></i> 查看', ['#'], ['data-toggle' => 'modal', 'data-target' => '#train-modal','class' => 'btn btn-success btn-xs train-view',]);
                        },
                        'update' => function($url, $model, $key) {
                                    return $model->uid==Yii::$app->user->identity->id&&in_array($model->train_stat,array(Train::STAT_CREATED, Train::STAT_ORDER))?Html::a('<i class="fa fa-pencil"></i> 修改', ['#'], ['data-toggle' => 'modal', 'data-target' => '#train-modal','class' => 'btn btn-primary btn-xs train-update',]):'';
                        },
                        'end' => function($url, $model, $key) {
                                    return in_array($model->train_stat,[Train::STAT_ORDER, Train::STAT_END])&&in_array(Yii::$app->user->identity->id,TrainUser::get_userid($model->id,'sa'))?Html::a('<i class="fa fa-hand-peace-o"></i> '.($model->train_stat==Train::STAT_END?'变更':'完成'), ['#'], ['data-toggle' => 'modal', 'data-target' => '#train-modal','class' => 'btn btn-warning btn-xs train-end',]):'';
                        },
                        'cancel' => function($url, $model, $key) {
                                    return $model->uid==Yii::$app->user->identity->id&&in_array($model->train_stat,array(Train::STAT_CREATED, Train::STAT_ORDER))||($model->train_stat==Train::STAT_ORDER&&in_array(Yii::$app->user->identity->id,TrainUser::get_userid($model->id,'sa')))?Html::a('<i class="fa fa-trash-o"></i> 取消', ['cancel', 'id' => $key], ['class' => 'btn btn-danger btn-xs']):'';
                        },
                        'order' => function($url, $model, $key) {
                                    return $model->train_stat==Train::STAT_CREATED&&((!TrainUser::get_userid($model->id,User::ROLE_SA)&&Yii::$app->user->identity->role== User::ROLE_SA)||in_array(Yii::$app->user->identity->id,TrainUser::get_userid($model->id,User::ROLE_SA)))?Html::a('<i class="fa fa-check"></i> 接受', ['#'], ['data-toggle' => 'modal', 'data-target' => '#train-modal','class' => 'btn btn-info btn-xs train-order',]):'';
                        },
                        'refuse' => function($url, $model, $key) {
                                    return $model->train_stat==Train::STAT_CREATED&&((!TrainUser::get_userid($model->id,User::ROLE_SA)&&Yii::$app->user->identity->role== User::ROLE_SA)||in_array(Yii::$app->user->identity->id,TrainUser::get_userid($model->id,User::ROLE_SA)))?Html::a('<i class="fa fa-remove"></i> 拒绝', ['refuse', 'id' => $key], ['class' => 'btn btn-danger btn-xs']):'';
                        },
                        'delete' => function($url, $model, $key) {
                                    return $model->uid==Yii::$app->user->identity->id&&in_array($model->train_stat,array(Train::STAT_REFUSE, Train::STAT_CANCEL))?Html::a('<i class="fa fa-trash-o"></i> 删除', ['delete', 'id' => $key], ['class' => 'btn btn-danger btn-xs','data-confirm' =>'确定要删除此项吗？','data-method' => 'post',]):'';
                        },


                    ],
                ],
                ],
                ]); ?>
                        <?php Pjax::end(); ?>        </div>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'train-modal',
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
<?php $this->beginBlock('train') ?>
    
    $('.train-index').on('click', '.corporation-view', function () {
        //$('.modal-title').html('企业查看');
        $('#corporation-modal .modal-body').html('');
        $.get('<?= Url::toRoute('corporation/corporation-view') ?>',{id: $(this).data('id')},
                function (data) {
                    $('#corporation-modal .modal-body').html(data);
                }
        );
    });
    
    $('.train-index').on('click', '.train-view', function () {
        $('#train-modal .modal-title').html('查看');
        $('#train-modal .modal-body').html('');
        $.get('<?= Url::toRoute('view') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#train-modal .modal-body').html(data);
                }
        );
    });
    
    $('.train-index').on('click', '.train-create', function () {
        $('#train-modal .modal-title').html('添加记录');
        $('#train-modal .modal-body').html('');
        $.get('<?= Url::toRoute('create') ?>',
                function (data) {
                    $('#train-modal .modal-body').html(data);
                }
        );
    });
    
    $('.train-index').on('click', '.train-update', function () {
        $('#train-modal .modal-title').html('修改');
        $('#train-modal .modal-body').html('');
        $.get('<?= Url::toRoute('update') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#train-modal .modal-body').html(data);
                }
        );
    });
    
    $('.train-index').on('click', '.train-end', function () {
        $('#train-modal .modal-title').html('完成');
        $('#train-modal .modal-body').html('');
        $.get('<?= Url::toRoute('end') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#train-modal .modal-body').html(data);
                }
        );
    });
    
    $('.train-index').on('click', '.train-order', function () {
        $('#train-modal .modal-title').html('确认');
        $('#train-modal .modal-body').html('');
        $.get('<?= Url::toRoute('order') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#train-modal .modal-body').html(data);
                }
        );
    });
    


<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['train'], \yii\web\View::POS_END); ?>