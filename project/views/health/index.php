<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\daterange\DateRangePicker;
use yii\helpers\Url;
use project\models\ActivityChange;
use kartik\widgets\SwitchInput;
use yii\bootstrap\Modal;
use project\models\User;
use kartik\widgets\Select2;
use project\models\Parameter;
use project\models\UserGroup;
use project\models\Group;
use project\models\Corporation;
use project\models\CorporationMeal;
use project\models\CorporationAccount;
use project\models\CorporationProject;
use project\models\CorporationCodehub;

$this->title = '活跃数据';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['activity/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="activity-index">

    <div class="box box-primary">
        <div class="box-body">
              
            <ul class="nav nav-tabs" style="margin-bottom:10px;border-bottom:none">
               
<!--                <li class="header pull-right"> <div><?= Html::a('<i class="fa fa-share-square-o"></i>全部导出', ['export?'.Yii::$app->request->queryString], ['class' => 'btn btn-warning']) ?></div></li>-->
                
                <li>
<!--                    <button type="button" class="btn btn-default pull-right" id="daterange-btn"><span><i class="fa fa-calendar"></i> 时间选择</span><i class="fa fa-caret-down"></i></button>-->
                   <?=
                    DateRangePicker::widget([
                        'name' => 'daterange',
                        'useWithAddon' => true,
                        'presetDropdown' => true,
                        'convertFormat' => true,
                        'value' =>date('Y-m-d', $start) . '~' . date('Y-m-d', $end),
//                        'startAttribute' => 'from_date',
//                        'endAttribute' => 'to_date',
//                        'startInputOptions' => ['value' => '2017-06-11'],
//                        'endInputOptions' => ['value' => '2017-07-20'],
                        'pluginOptions' => [
                            'timePicker' => false,
                            'locale' => [
                                'format' => 'Y-m-d',
                                'separator' => '~'
                            ],
                            'linkedCalendars' => false,
                            'opens'=>'right',
                        ],
                        'pluginEvents' => [
                            "apply.daterangepicker" => "function(start,end,label) {var v=$('.range-value').val(); var a=$('#annual').val();s=$('.sum').is(':checked')?1:0;self.location='".Url::to(['health/index'])."?".Yii::$app->request->queryString."&range='+v+'&annual='+a+'&sum='+s;}",
                    ]
                    ]);
                    ?>
                    
                </li>
                <li style="margin-left: 10px;">
                    <?= Select2::widget([
                        'name' => 'annual',                        
                        'data' => Parameter::get_type('allocate_annual'),
                        'value'=>$annual,
                        'options' => [
                            'placeholder' => '下拨年度',
                            'id'=>'annual',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '110%'
                        ],
                        'pluginEvents' => [
                            "change" => "function() {var v=$('.range-value').val(); var a=$('#annual').val();s=$('.sum').is(':checked')?1:0;self.location='".Url::to(['health/index'])."?".Yii::$app->request->queryString."&range='+v+'&annual='+a+'&sum='+s;}",
                        ]
                    ]);?>
                    
                </li>
                 <li style="margin-left: 20px;">
                    <?=
                    SwitchInput::widget([
                        'name' => 'sum',
                        'value'=>$sum,
                        'options'=>['class'=>'sum'],
                        'pluginOptions'=>[
                            'onText'=>'是',
                            'offText'=>'否',
                            'onColor' => 'success',
                            'offColor' => 'danger',
                            'labelText'=>'统计'
                        ],
                        'pluginEvents' => [
                        'switchChange.bootstrapSwitch' => "function(e,data) {var v=$('.range-value').val();s=$('.sum').is(':checked')?1:0;var a=$('#annual').val();self.location='".Url::to(['health/index'])."?".Yii::$app->request->queryString."&range='+v+'&sum='+s;}",
                    ]
                    ]);
                    ?>
                </li>
               
                
            </ul>
               
           
                                  
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-bordered table-hover'],            
                'columns' => [
                     [
                        'attribute' => 'group_id',
                        'value' =>function($model) {
                            return ($model->group_id?$model->group->title:'<span class="not-set">(未设置)</span>');   //主要通过此种方式实现
                        },
                        'format' => 'raw',
                        'filter' => Group::get_user_group(Yii::$app->user->identity->id),
                        'visible'=> count(UserGroup::get_user_groupid(Yii::$app->user->identity->id))>1,
                    ],
                    [
                        'attribute' => 'start_time',
                        'label' => '时间段',
                        'value' => function($model) {
                            return date('Y-m-d',$model->start_time+86400).' ~ '.date('Y-m-d',$model->end_time);
                        },
                        'filter' => false,
                    ],
                    [
                        'attribute' => 'bd_id',
                        'value' => function($model) {
                            return $model->bd_id?($model->bd->nickname?$model->bd->nickname:$model->bd->username):'';
                        },
                        'filter' => User::get_bd(User::STATUS_ACTIVE,UserGroup::get_group_userid(array_keys(Group::get_user_group(Yii::$app->user->identity->id)))),
                    ],
                    [
                        'attribute' => 'corporation',
                        'label'=>'公司',
                        'value' =>function($model) {                           
                            return Html::tag('span',$model->corporation->base_company_name, ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','data-id'=>$model->corporation_id,'class' => 'corporation-view','style'=>'cursor:pointer']);
                        },
                        'format' => 'raw',
                        'contentOptions'=>function($model) {                            
                            return ['class' => ActivityChange::is_real_activity($model) ?'bg-green' : ''];                           
                        },
                    ],
                    [
                        'attribute' => 'is_allocate',
                        'value' => function($model) {                                
                            return Html::tag('span', $model->Allocate,['class' => ($model->is_allocate== ActivityChange::ALLOCATE_Y ? 'text-green' : ($model->is_allocate== ActivityChange::ALLOCATE_N ? 'text-red' : ''))]);                        
                        },
                        'format' => 'raw',
                        'filter' => ActivityChange::$List['is_allocate'],
                        
                    ],
                    [
                        'attribute' => 'is_act',
                        'value' => function($model) {                                
                            return Html::tag('span', $model->Act,['class' => ($model->is_act== ActivityChange::ACT_Y ? 'text-green' : ($model->is_act== ActivityChange::ACT_N ? 'text-red' : ''))]);                        
                        },
                        'format' => 'raw',
                        'filter' => ActivityChange::$List['is_act'],
                       
                    ],
                    [
                        'attribute' => 'act_trend',
                        'value' => function($model) use($start, $end) {                                
                            return Yii::$app->request->get('sum',1)?'<span class="sparktristate">'.ActivityChange::get_act_line($model->corporation_id,$start-86400, $end).'</span>':'<i class="fa fa-square '.($model->act_trend==ActivityChange::TREND_UC?'text-gray':($model->act_trend==ActivityChange::TREND_IN?'text-green':($model->act_trend==ActivityChange::TREND_DE?'text-red':'text-yellow'))).'"></i>';                        
                        },
                        'format' => 'raw',
                        'filter' => Yii::$app->request->get('sum',1)?false:ActivityChange::$List['act_trend'], 
                            
                    ],
                    [
                        'attribute' => 'health',
                        'value' => function($model) use($start, $end) {                                
                           return Yii::$app->request->get('sum',1)?'<span class="sparktristate_health">'.ActivityChange::get_health_line($model->corporation_id,$start-86400, $end).'</span>':'<span style="color:'.ActivityChange::$List['health_color'][$model->health].'">'.$model->Health.'</span>';                       
                        },
                        'format' => 'raw',
                        'filter' => Yii::$app->request->get('sum',1)?false:ActivityChange::$List['health'],
                       
                    ],                   
                            
                    [
                        'label'=>'用户数',
                        'value' => function($model) {
                            if(in_array($model->corporation->stat,[Corporation::STAT_ALLOCATE,Corporation::STAT_AGAIN, Corporation::STAT_OVERDUE])&&$meal=CorporationMeal::get_allocate($model->corporation_id)){
                                $m=$model->data&&$model->data->projectman_membercount?$model->data->projectman_membercount:0;
                                $u= CorporationAccount::get_corporation_account_num($model->corporation_id);//$model->data&&$model->data->projectman_usercount?$model->data->projectman_usercount:0;
                                $max= $meal->devcloud_count+5;
                                return '<span class='.($m<$max-5?'text-green':($m>=$max-5&&$m<=$max?'text-yellow':'text-red')).'>'.$m.'/'.$max.'</span> ('.$u.')';
                            }else{
                                return '';
                            }
                        },
                        'format' => 'raw',
                    ],
                                
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{user} {project} {codehub}',
                        'buttons' => [                           
                            'user' => function($url, $model, $key) {
                                return CorporationAccount::get_corporationaccount_exist($model->corporation_id)?Html::a('<i class="fa fa-users"></i> 用户管理', ['#'], ['data-toggle' => 'modal', 'data-target' => '#item-modal', 'data-id'=>$model->corporation_id,'class' => 'btn btn-xs corporation-user '.(CorporationAccount::get_corporationaccount_exist($model->corporation_id, CorporationAccount::ADMIN_YES)?'btn-danger':'btn-warning'),]):Html::a('<i class="fa fa-user"></i> 添加账号', ['#'], ['data-toggle' => 'modal', 'data-target' => '#item-modal','data-id'=>$model->corporation_id,'class' => 'btn btn-success btn-xs account-create',]);
                            },
                            'project' => function($url, $model, $key) {
                                return CorporationAccount::get_token($model->corporation_id)?(CorporationProject::get_corporationproject_exist($model->corporation_id)?Html::a('<i class="fa fa-user"></i> 成员管理', ['#'], ['data-toggle' => 'modal', 'data-target' => '#item-modal','data-id'=>$model->corporation_id,'class' => 'btn btn-warning btn-xs member-list',]):Html::a('<i class="fa fa-cubes"></i> 创建项目', ['project-create', 'id' => $model->corporation_id], ['class' => 'btn btn-success btn-xs','data-method' => 'post',])):'';
                            },
                            'codehub'=>function($url, $model, $key) {
                                return CorporationProject::get_corporationproject_exist($model->corporation_id)?(CorporationCodehub::get_codehub_exist($model->corporation_id)?Html::button('<i class="fa fa-retweet"></i> 代码提交', ['data-id'=>$model->corporation_id,'class' => 'btn btn-warning btn-xs codehub-exec',]):Html::a('<i class="fa fa-server"></i> 添加仓库', ['#'], ['data-toggle' => 'modal', 'data-target' => '#item-modal','data-id'=>$model->corporation_id,'class' => 'btn btn-success btn-xs codehub-create',])):'';
                            },
                        ],
                       
                    ],
                ],
                ]); ?>
                                </div>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'item-modal',
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
<?php project\assets\SparklineAsset::register($this);?>
<script>
<?php $this->beginBlock('health') ?>
    $('.sparktristate').sparkline('html', {type: 'tristate',colorMap:{'1':'#00a65a','-1':'#dd4b39'},tooltipValueLookups: {map: $.range_map({'-1': '不活跃', '1': '活跃','0':'未设置'})}});
    $('.sparktristate_health').sparkline('html', {type: 'tristate',colorMap:<?php echo json_encode(ActivityChange::$List['health_color'])?>,tooltipValueLookups: {map: $.range_map(<?php echo json_encode(ActivityChange::$List['health'])?>)}});
   
    $('.activity-index').on('click', '.account-create', function () {
        $('.modal-title').html('添加账号');
        $('#item-modal .modal-body').html('');
        $.get('<?= Url::toRoute('health/account-create') ?>',{id: $(this).data('id')},
                function (data) {
                    $('#item-modal .modal-body').html(data);
                }
        );
    });
    
    $('.activity-index').on('click', '.corporation-user', function () {
        $('.modal-title').html('用户管理');
        $('#item-modal .modal-body').html('');
        $.get('<?= Url::toRoute('health/corporation-user') ?>',{id: $(this).data('id')},
                function (data) {
                    $('#item-modal .modal-body').html(data);
                }
        );
    });
    
    $('.activity-index').on('click', '.member-list', function () {
        $('.modal-title').html('成员管理');
        $('#item-modal .modal-body').html('');
        $.get('<?= Url::toRoute('health/member-list') ?>',{id: $(this).data('id')},
                function (data) {
                    $('#item-modal .modal-body').html(data);
                }
        );
    });
    
    $('.activity-index').on('click', '.codehub-create', function () {
        $('.modal-title').html('添加仓库');
        $('#item-modal .modal-body').html('');
        $.get('<?= Url::toRoute('health/codehub-create') ?>',{id: $(this).data('id')},
                function (data) {
                    $('#item-modal .modal-body').html(data);
                }
        );
    });
    
    $('.activity-index').on('click', '.codehub-exec', function () {
        
        $.getJSON('<?= Url::toRoute('health/codehub-exec') ?>',{id: $(this).data('id')},
                function (data) {
                    if (data.stat == 'success') {
                               
                    } 
                }
        );
    });

<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['health'], \yii\web\View::POS_END); ?>