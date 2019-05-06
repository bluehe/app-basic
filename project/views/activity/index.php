<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
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

$this->title = '活跃数据';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['activity/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(); ?> 
<div class="activity-index">

    <div class="box box-primary">
        <div class="box-body">
              
            <ul class="nav nav-tabs" style="margin-bottom:10px;border-bottom:none">
                <li class="header pull-right" style="margin-left: 20px;"> <div><?= Html::a('<i class="fa fa-filter" title="选择需要显示的列"></i>', ['#'], ['data-toggle' => 'modal', 'data-target' => '#item-modal', 'class' => 'btn btn-danger column-change']) ?></div></li>
                <li class="header pull-right"> <div><?= Html::a('<i class="fa fa-share-square-o"></i>全部导出', ['export?'.Yii::$app->request->queryString], ['class' => 'btn btn-warning']) ?></div></li>
                
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
                            "apply.daterangepicker" => "function(start,end,label) {var v=$('.range-value').val();s=$('.sum').is(':checked')?1:0;d=$('.dev').is(':checked')?1:0; var a=$('#annual').val();self.location='".Url::to(['activity/index'])."?".Yii::$app->request->queryString."&range='+v+'&sum='+s+'&dev='+d;}",
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
                            "change" => "function() {var v=$('.range-value').val();s=$('.sum').is(':checked')?1:0;d=$('.dev').is(':checked')?1:0; var a=$('#annual').val();self.location='".Url::to(['activity/index'])."?".Yii::$app->request->queryString."&range='+v+'&sum='+s+'&dev='+d+'&annual='+a;}",
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
                        'switchChange.bootstrapSwitch' => "function(e,data) {var v=$('.range-value').val();s=$('.sum').is(':checked')?1:0;d=$('.dev').is(':checked')?1:0; var a=$('#annual').val();self.location='".Url::to(['activity/index'])."?".Yii::$app->request->queryString."&range='+v+'&sum='+s+'&dev='+d;}",
                    ]
                    ]);
                    ?>
                </li>
                
                <li style="margin-left: 10px;">
                    <?=
                    SwitchInput::widget([
                        'name' => 'dev',
                        'value'=>$dev,
                        'options'=>['class'=>'dev'],
                        'pluginOptions'=>[
                            'onText'=>'是',
                            'offText'=>'否',
                            'onColor' => 'success',
                            'offColor' => 'danger',
                            'labelText'=>'数据分析'
                        ],
                        'pluginEvents' => [
                        'switchChange.bootstrapSwitch' => "function(e,data) {var v=$('.range-value').val();s=$('.sum').is(':checked')?1:0;d=$('.dev').is(':checked')?1:0; var a=$('#annual').val();self.location='".Url::to(['activity/index'])."?".Yii::$app->request->queryString."&range='+v+'&sum='+s+'&dev='+d;}",
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
                'rowOptions'=>function($model, $key, $index, $grid) {return Yii::$app->request->get('sum',1)?'':["class" => $model->type== ActivityChange::TYPE_ADD ?'bg-teal-gradient':($model->type== ActivityChange::TYPE_DELETE?'bg-orange' :'')  ];},
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
                        'visible'=> is_array($column)&&in_array('is_allocate',$column),
                    ],
                    [
                        'attribute' => 'is_act',
                        'value' => function($model) {                                
                            return Html::tag('span', $model->Act,['class' => ($model->is_act== ActivityChange::ACT_Y ? 'text-green' : ($model->is_act== ActivityChange::ACT_N ? 'text-red' : ''))]);                        
                        },
                        'format' => 'raw',
                        'filter' => ActivityChange::$List['is_act'],
                        'visible'=> is_array($column)&&in_array('is_act',$column),
                    ],
                    [
                        'attribute' => 'act_trend',
                        'value' => function($model) use($start, $end) {                                
                            return Yii::$app->request->get('sum',1)?'<span class="sparktristate">'.ActivityChange::get_act_line($model->corporation_id,$start-86400, $end).'</span>':'<i class="fa fa-square '.($model->act_trend==ActivityChange::TREND_UC?'text-gray':($model->act_trend==ActivityChange::TREND_IN?'text-green':($model->act_trend==ActivityChange::TREND_DE?'text-red':'text-yellow'))).'"></i>';                        
                        },
                        'format' => 'raw',
                        'filter' => Yii::$app->request->get('sum',1)?false:ActivityChange::$List['act_trend'], 
                        'visible'=> Yii::$app->request->get('dev',0)&&is_array($column)&&in_array('is_act',$column),      
                    ],
                    [
                        'attribute' => 'health',
                        'value' => function($model) use($start, $end) {                                
                           return Yii::$app->request->get('sum',1)?'<span class="sparktristate_health">'.ActivityChange::get_health_line($model->corporation_id,$start-86400, $end).'</span>':'<span style="color:'.ActivityChange::$List['health_color'][$model->health].'">'.$model->Health.'</span>';                       
                        },
                        'format' => 'raw',
                        'filter' => Yii::$app->request->get('sum',1)?false:ActivityChange::$List['health'],
                        'visible'=> is_array($column)&&in_array('health',$column),
                    ],   
                    [
                    'attribute' => 'projectman_usercount',
                    'value' => function($model) {                                
                        return Html::tag('span', $model->projectman_usercount?$model->projectman_usercount:'',['class' => ($model->projectman_usercount==0 ? '' : ($model->projectman_usercount >0 ? 'text-green' : 'text-red'))]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('projectman_usercount', $model->start_time, $model->end_time);
                            $value=$model->projectman_usercount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}
                    },
                    'visible'=> is_array($column)&&in_array('projectman_usercount',$column),
                    ],

                    [
                    'attribute' => 'projectman_projectcount',
                    'value' => function($model) {
                        return Html::tag('span', $model->projectman_projectcount?$model->projectman_projectcount:'',['class' =>($model->projectman_projectcount==0 ? '' : ($model->projectman_projectcount >0 ? 'text-green' : 'text-red'))]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('projectman_projectcount', $model->start_time, $model->end_time);
                            $value=$model->projectman_projectcount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}   
                    }, 
                    'visible'=> is_array($column)&&in_array('projectman_projectcount',$column),
                    ],

                    [
                    'attribute' => 'projectman_membercount',
                    'value' => function($model) {
                        return Html::tag('span', $model->projectman_membercount?$model->projectman_membercount:'', ['class' => ($model->projectman_membercount==0 ? '' : ($model->projectman_membercount >0 ? 'text-green' : 'text-red'))]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('projectman_membercount', $model->start_time, $model->end_time);
                            $value=$model->projectman_membercount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}     
                    },
                    'visible'=> is_array($column)&&in_array('projectman_membercount',$column),
                    ],

                    [
                    'attribute' => 'projectman_versioncount',
                    'value' => function($model) {
                        return Html::tag('span', $model->projectman_versioncount?$model->projectman_versioncount:'',['class' => ($model->projectman_versioncount==0 ? '' : ($model->projectman_versioncount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('projectman_versioncount', $model->start_time, $model->end_time);
                            $value=$model->projectman_versioncount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}        
                    },
                    'visible'=> is_array($column)&&in_array('projectman_versioncount',$column),
                    ],

                    [
                    'attribute' => 'projectman_issuecount',
                    'value' => function($model) {
                        return Html::tag('span', $model->projectman_issuecount?$model->projectman_issuecount:'', ['class' => ($model->projectman_issuecount==0 ? '' : ($model->projectman_issuecount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('projectman_issuecount', $model->start_time, $model->end_time);
                            $value=$model->projectman_issuecount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}         
                    },
                    'visible'=> is_array($column)&&in_array('projectman_issuecount',$column),
                    ],

                    [
                    'attribute' => 'projectman_storagecount',
                    'value' => function($model) {
                        return Html::tag('span', $model->projectman_storagecount?$model->projectman_storagecount:'',['class' => ($model->projectman_storagecount==0 ? '' : ($model->projectman_storagecount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('projectman_storagecount', $model->start_time, $model->end_time);
                            $value=$model->projectman_storagecount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}         
                    },
                    'visible'=> is_array($column)&&in_array('projectman_storagecount',$column),
                    ],

                    [
                    'attribute' => 'codehub_all_usercount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codehub_all_usercount?$model->codehub_all_usercount:'',['class' => ($model->codehub_all_usercount==0 ? '' : ($model->codehub_all_usercount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codehub_all_usercount', $model->start_time, $model->end_time);
                            $value=$model->codehub_all_usercount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    },
                    'visible'=> is_array($column)&&in_array('codehub_all_usercount',$column),
                    ],

                    [
                    'attribute' => 'codehub_repositorycount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codehub_repositorycount?$model->codehub_repositorycount:'',['class' => ($model->codehub_repositorycount==0 ? '' : ($model->codehub_repositorycount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codehub_repositorycount', $model->start_time, $model->end_time);
                            $value=$model->codehub_repositorycount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}         
                    },
                    'visible'=> is_array($column)&&in_array('codehub_repositorycount',$column),
                    ],

                    [
                    'attribute' => 'codehub_commitcount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codehub_commitcount?$model->codehub_commitcount:'',['class' => ($model->codehub_commitcount==0 ? '' : ($model->codehub_commitcount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codehub_commitcount', $model->start_time, $model->end_time);
                            $value=$model->codehub_commitcount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}         
                    },
                    'visible'=> is_array($column)&&in_array('codehub_commitcount',$column),
                    ],

                    [
                    'attribute' => 'codehub_repositorysize',
                    'value' => function($model) {
                        return Html::tag('span', $model->codehub_repositorysize?$model->codehub_repositorysize:'',['class' => ($model->codehub_repositorysize==0 ? '' : ($model->codehub_repositorysize >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codehub_repositorysize', $model->start_time, $model->end_time);
                            $value=$model->codehub_repositorysize;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];} 
                    }, 
                    'visible'=> is_array($column)&&in_array('codehub_repositorysize',$column),
                    ],

                    [
                    'attribute' => 'pipeline_usercount',
                    'value' => function($model) {
                        return Html::tag('span', $model->pipeline_usercount?$model->pipeline_usercount:'',['class' => ($model->pipeline_usercount==0 ? '' : ($model->pipeline_usercount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('pipeline_usercount', $model->start_time, $model->end_time);
                            $value=$model->pipeline_usercount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];} 
                    },
                    'visible'=> is_array($column)&&in_array('pipeline_usercount',$column),
                    ],
            
                    [
                    'attribute' => 'pipeline_pipecount',
                    'value' => function($model) {
                        return Html::tag('span', $model->pipeline_pipecount?$model->pipeline_pipecount:'',['class' => ($model->pipeline_pipecount==0 ? '' : ($model->pipeline_pipecount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('pipeline_pipecount', $model->start_time, $model->end_time);
                            $value=$model->pipeline_pipecount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    },
                    'visible'=> is_array($column)&&in_array('pipeline_pipecount',$column),
                    ],

                    [
                    'attribute' => 'pipeline_executecount',
                    'value' => function($model) {
                        return Html::tag('span', $model->pipeline_executecount?$model->pipeline_executecount:'',['class' => ($model->pipeline_executecount==0 ? '' : ($model->pipeline_executecount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('pipeline_executecount', $model->start_time, $model->end_time);
                            $value=$model->pipeline_executecount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}         
                    }, 
                    'visible'=> is_array($column)&&in_array('pipeline_executecount',$column),
                    ],

                    [
                    'attribute' => 'pipeline_elapse_time',
                    'value' => function($model) {
                        return Html::tag('span', $model->pipeline_elapse_time?$model->pipeline_elapse_time:'',['class' => ($model->pipeline_elapse_time==0 ? '' : ($model->pipeline_elapse_time >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('pipeline_elapse_time', $model->start_time, $model->end_time);
                            $value=$model->pipeline_elapse_time;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    },
                    'visible'=> is_array($column)&&in_array('pipeline_elapse_time',$column),
                    ],

                    [
                    'attribute' => 'codecheck_usercount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codecheck_usercount?$model->codecheck_usercount:'',['class' => ($model->codecheck_usercount==0 ? '' : ($model->codecheck_usercount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codecheck_usercount', $model->start_time, $model->end_time);
                            $value=$model->codecheck_usercount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    },
                    'visible'=> is_array($column)&&in_array('codecheck_usercount',$column),
                    ],

                    [
                    'attribute' => 'codecheck_taskcount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codecheck_taskcount?$model->codecheck_taskcount:'',['class' => ($model->codecheck_taskcount==0 ? '' : ($model->codecheck_taskcount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codecheck_taskcount', $model->start_time, $model->end_time);
                            $value=$model->codecheck_taskcount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}         
                    }, 
                    'visible'=> is_array($column)&&in_array('codecheck_taskcount',$column),
                    ],

                    [
                    'attribute' => 'codecheck_codelinecount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codecheck_codelinecount?$model->codecheck_codelinecount:'',['class' => ($model->codecheck_codelinecount==0 ? '' : ($model->codecheck_codelinecount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codecheck_codelinecount', $model->start_time, $model->end_time);
                            $value=$model->codecheck_codelinecount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    },
                    'visible'=> is_array($column)&&in_array('codecheck_codelinecount',$column),
                    ],

                    [
                    'attribute' => 'codecheck_issuecount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codecheck_issuecount?$model->codecheck_issuecount:'',['class' => ($model->codecheck_issuecount==0 ? '' : ($model->codecheck_issuecount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codecheck_issuecount', $model->start_time, $model->end_time);
                            $value=$model->codecheck_issuecount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}
                    },
                    'visible'=> is_array($column)&&in_array('codecheck_issuecount',$column),
                    ],

                    [
                    'attribute' => 'codecheck_execount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codecheck_execount?$model->codecheck_execount:'',['class' => ($model->codecheck_execount==0 ? '' : ($model->codecheck_execount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codecheck_execount', $model->start_time, $model->end_time);
                            $value=$model->codecheck_execount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}         
                    }, 
                    'visible'=> is_array($column)&&in_array('codecheck_execount',$column),
                    ],

                    [
                    'attribute' => 'codeci_usercount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codeci_usercount?$model->codeci_usercount:'',['class' => ($model->codeci_usercount==0 ? '' : ($model->codeci_usercount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codeci_usercount', $model->start_time, $model->end_time);
                            $value=$model->codeci_usercount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}        
                    },
                    'visible'=> is_array($column)&&in_array('codeci_usercount',$column),
                    ],

                    [
                    'attribute' => 'codeci_buildcount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codeci_buildcount?$model->codeci_buildcount:'',['class' => ($model->codeci_buildcount==0 ? '' : ($model->codeci_buildcount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codeci_buildcount', $model->start_time, $model->end_time);
                            $value=$model->codeci_buildcount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    },
                    'visible'=> is_array($column)&&in_array('codeci_buildcount',$column),
                    ],
                     
                    [
                    'attribute' => 'codeci_allbuildcount',
                    'value' => function($model) {
                        return Html::tag('span', $model->codeci_allbuildcount?$model->codeci_allbuildcount:'',['class' => ($model->codeci_allbuildcount==0 ? '' : ($model->codeci_allbuildcount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codeci_allbuildcount', $model->start_time, $model->end_time);
                            $value=$model->codeci_allbuildcount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}           
                    },
                    'visible'=> is_array($column)&&in_array('codeci_allbuildcount',$column),
                    ],

                    [
                    'attribute' => 'codeci_buildtotaltime',
                    'value' => function($model) {
                        return Html::tag('span', $model->codeci_buildtotaltime?$model->codeci_buildtotaltime:'',['class' => ($model->codeci_buildtotaltime==0 ? '' : ($model->codeci_buildtotaltime >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('codeci_buildtotaltime', $model->start_time, $model->end_time);
                            $value=$model->codeci_buildtotaltime;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    },
                    'visible'=> is_array($column)&&in_array('codeci_buildtotaltime',$column),
                    ],

                    [
                    'attribute' => 'testman_usercount',
                    'value' => function($model) {
                        return Html::tag('span', $model->testman_usercount?$model->testman_usercount:'',['class' => ($model->testman_usercount==0 ? '' : ($model->testman_usercount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('testman_usercount', $model->start_time, $model->end_time);
                            $value=$model->testman_usercount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                       }else{return [];}           
                    }, 
                    'visible'=> is_array($column)&&in_array('testman_usercount',$column),
                    ],

                    [
                    'attribute' => 'testman_casecount',
                    'value' => function($model) {
                        return Html::tag('span', $model->testman_casecount?$model->testman_casecount:'',['class' => ($model->testman_casecount==0 ? '' : ($model->testman_casecount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('testman_casecount', $model->start_time, $model->end_time);
                            $value=$model->testman_casecount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    }, 
                    'visible'=> is_array($column)&&in_array('testman_casecount',$column),
                    ],

                    [
                    'attribute' => 'testman_totalexecasecount',
                    'value' => function($model) {
                        return Html::tag('span', $model->testman_totalexecasecount?$model->testman_totalexecasecount:'',['class' => ($model->testman_totalexecasecount==0 ? '' : ($model->testman_totalexecasecount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('testman_totalexecasecount', $model->start_time, $model->end_time);
                            $value=$model->testman_totalexecasecount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    },
                    'visible'=> is_array($column)&&in_array('testman_totalexecasecount',$column),
                    ],

                    [
                    'attribute' => 'deploy_usercount',
                    'value' => function($model) {
                        return Html::tag('span', $model->deploy_usercount?$model->deploy_usercount:'',['class' => ($model->deploy_usercount==0 ? '' : ($model->deploy_usercount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('deploy_usercount', $model->start_time, $model->end_time);
                            $value=$model->deploy_usercount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    },
                    'visible'=> is_array($column)&&in_array('deploy_usercount',$column),
                    ],

                    [
                    'attribute' => 'deploy_envcount',
                    'value' => function($model) {
                        return Html::tag('span', $model->deploy_envcount?$model->deploy_envcount:'',['class' => ($model->deploy_envcount==0 ? '' : ($model->deploy_envcount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('deploy_envcount', $model->start_time, $model->end_time);
                            $value=$model->deploy_envcount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    },
                    'visible'=> is_array($column)&&in_array('deploy_envcount',$column),
                    ],

                    [
                    'attribute' => 'deploy_execount',
                    'value' => function($model) {
                        return Html::tag('span', $model->deploy_execount?$model->deploy_execount:'',['class' => ($model->deploy_execount==0 ? '' : ($model->deploy_execount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('deploy_execount', $model->start_time, $model->end_time);
                            $value=$model->deploy_execount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}          
                    }, 
                    'visible'=> is_array($column)&&in_array('deploy_execount',$column),
                    ],

                    [
                    'attribute' => 'deploy_vmcount',
                    'value' => function($model) {
                        return Html::tag('span', $model->deploy_vmcount?$model->deploy_vmcount:'',['class' => ($model->deploy_vmcount==0 ? '' : ($model->deploy_vmcount >0 ? 'text-green' : 'text-red') )]);

                    },
                    'format' => 'raw',
                    'contentOptions'=>function($model) {
                        if(Yii::$app->request->get('dev',0)){
                            $dev= ActivityChange::deviation_data('deploy_vmcount', $model->start_time, $model->end_time);
                            $value=$model->deploy_vmcount;
                            return  ['class' =>($value>$dev['max']?'bg-teal-gradient':($value>0&&$value<$dev['min']?'bg-orange':''))];
                        }else{return [];}            
                    },
                    'visible'=> is_array($column)&&in_array('deploy_vmcount',$column),
                    ],
                            
                    [
                    'attribute' => 'projectman_usercount_d',
                    'value' => function($model) {                                
                        return $model->data&&$model->data->projectman_usercount?$model->data->projectman_usercount:'';
                    },
                    'visible'=> is_array($column)&&in_array('projectman_usercount_d',$column),
                    ],
                            
                    [
                    'attribute' => 'projectman_projectcount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->projectman_projectcount?$model->data->projectman_projectcount:'';

                    },
                    'visible'=> is_array($column)&&in_array('projectman_projectcount_d',$column),
                    ],
                            
                    [
                    'attribute' => 'projectman_membercount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->projectman_membercount?$model->data->projectman_membercount:'';

                    },                
                    'visible'=> is_array($column)&&in_array('projectman_membercount_d',$column),
                    ],
                            
                    [
                    'attribute' => 'projectman_versioncount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->projectman_versioncount?$model->data->projectman_versioncount:'';

                    },                               
                    'visible'=> is_array($column)&&in_array('projectman_versioncount_d',$column),
                    ],

                    [
                    'attribute' => 'projectman_issuecount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->projectman_issuecount?$model->data->projectman_issuecount:'';
                    },
                    'visible'=> is_array($column)&&in_array('projectman_issuecount_d',$column),
                    ],

                    [
                    'attribute' => 'projectman_storagecount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->projectman_storagecount?$model->data->projectman_storagecount:'';
                    },                  
                    'visible'=> is_array($column)&&in_array('projectman_storagecount_d',$column),
                    ],

                    [
                    'attribute' => 'codehub_all_usercount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->codehub_all_usercount?$model->data->codehub_all_usercount:'';
                    },                  
                    'visible'=> is_array($column)&&in_array('codehub_all_usercount_d',$column),
                    ],

                    [
                    'attribute' => 'codehub_repositorycount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->codehub_repositorycount?$model->data->codehub_repositorycount:'';
                    },                  
                    'visible'=> is_array($column)&&in_array('codehub_repositorycount_d',$column),
                    ],

                    [
                    'attribute' => 'codehub_commitcount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->codehub_commitcount?$model->data->codehub_commitcount:'';
                    },                 
                    'visible'=> is_array($column)&&in_array('codehub_commitcount_d',$column),
                    ],

                    [
                    'attribute' => 'codehub_repositorysize_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->codehub_repositorysize?$model->data->codehub_repositorysize:'';
                    },                 
                    'visible'=> is_array($column)&&in_array('codehub_repositorysize_d',$column),
                    ],
                            
                    [
                    'attribute' => 'pipeline_usercount_d',
                    'value' => function($model) {
                       return $model->data&&$model->data->pipeline_usercount?$model->data->pipeline_usercount:'';
                    },                   
                    'visible'=> is_array($column)&&in_array('pipeline_usercount_d',$column),
                    ],
            
                    [
                    'attribute' => 'pipeline_pipecount_d',
                    'value' => function($model) {
                       return $model->data&&$model->data->pipeline_pipecount?$model->data->pipeline_pipecount:'';
                    },                  
                    'visible'=> is_array($column)&&in_array('pipeline_pipecount_d',$column),
                    ],

                    [
                    'attribute' => 'pipeline_executecount_d',
                    'value' => function($model) {
                       return $model->data&&$model->data->pipeline_executecount?$model->data->pipeline_executecount:'';
                    },                  
                    'visible'=> is_array($column)&&in_array('pipeline_executecount_d',$column),
                    ],

                    [
                    'attribute' => 'pipeline_elapse_time_d',
                    'value' => function($model) {
                       return $model->data&&$model->data->pipeline_elapse_time?$model->data->pipeline_elapse_time:'';
                    },
                   
                    'visible'=> is_array($column)&&in_array('pipeline_elapse_time_d',$column),
                    ],

                    [
                    'attribute' => 'codecheck_usercount_d',
                    'value' => function($model) {
                       return $model->data&&$model->data->codecheck_usercount?$model->data->codecheck_usercount:'';
                    },                   
                    'visible'=> is_array($column)&&in_array('codecheck_usercount_d',$column),
                    ],

                    [
                    'attribute' => 'codecheck_taskcount_d',
                    'value' => function($model) {
                       return $model->data&&$model->data->codecheck_taskcount?$model->data->codecheck_taskcount:'';
                    },                  
                    'visible'=> is_array($column)&&in_array('codecheck_taskcount_d',$column),
                    ],

                    [
                    'attribute' => 'codecheck_codelinecount_d',
                    'value' => function($model) {
                       return $model->data&&$model->data->codecheck_codelinecount?$model->data->codecheck_codelinecount:'';
                    },                  
                    'visible'=> is_array($column)&&in_array('codecheck_codelinecount_d',$column),
                    ],

                    [
                    'attribute' => 'codecheck_issuecount_d',
                    'value' => function($model) {
                       return $model->data&&$model->data->codecheck_issuecount?$model->data->codecheck_issuecount:'';
                    },                   
                    'visible'=> is_array($column)&&in_array('codecheck_issuecount_d',$column),
                    ],

                    [
                    'attribute' => 'codecheck_execount_d',
                    'value' => function($model) {
                       return $model->data&&$model->data->codecheck_execount?$model->data->codecheck_execount:'';
                    },                 
                    'visible'=> is_array($column)&&in_array('codecheck_execount_d',$column),
                    ],

                    [
                    'attribute' => 'codeci_usercount_d',
                    'value' => function($model) {
                       return $model->data&&$model->data->codeci_usercount?$model->data->codeci_usercount:'';
                    },                 
                    'visible'=> is_array($column)&&in_array('codeci_usercount_d',$column),
                    ],

                    [
                    'attribute' => 'codeci_buildcount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->codeci_buildcount?$model->data->codeci_buildcount:'';
                    },                 
                    'visible'=> is_array($column)&&in_array('codeci_buildcount_d',$column),
                    ],
                     
                    [
                    'attribute' => 'codeci_allbuildcount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->codeci_allbuildcount?$model->data->codeci_allbuildcount:'';
                    },                  
                    'visible'=> is_array($column)&&in_array('codeci_allbuildcount_d',$column),
                    ],

                    [
                    'attribute' => 'codeci_buildtotaltime_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->codeci_buildtotaltime?$model->data->codeci_buildtotaltime:'';
                    },                 
                    'visible'=> is_array($column)&&in_array('codeci_buildtotaltime_d',$column),
                    ],

                    [
                    'attribute' => 'testman_usercount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->testman_usercount?$model->data->testman_usercount:'';
                    },                  
                    'visible'=> is_array($column)&&in_array('testman_usercount_d',$column),
                    ],

                    [
                    'attribute' => 'testman_casecount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->testman_casecount?$model->data->testman_casecount:'';
                    },                  
                    'visible'=> is_array($column)&&in_array('testman_casecount_d',$column),
                    ],

                    [
                    'attribute' => 'testman_totalexecasecount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->testman_totalexecasecount?$model->data->testman_totalexecasecount:'';
                    },                   
                    'visible'=> is_array($column)&&in_array('testman_totalexecasecount_d',$column),
                    ],

                    [
                    'attribute' => 'deploy_usercount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->deploy_usercount?$model->data->deploy_usercount:'';
                    },                   
                    'visible'=> is_array($column)&&in_array('deploy_usercount_d',$column),
                    ],

                    [
                    'attribute' => 'deploy_envcount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->deploy_envcount?$model->data->deploy_envcount:'';
                    },                 
                    'visible'=> is_array($column)&&in_array('deploy_envcount_d',$column),
                    ],

                    [
                    'attribute' => 'deploy_execount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->deploy_execount?$model->data->deploy_execount:'';
                    },                   
                    'visible'=> is_array($column)&&in_array('deploy_execount_d',$column),
                    ],

                    [
                    'attribute' => 'deploy_vmcount_d',
                    'value' => function($model) {
                        return $model->data&&$model->data->deploy_vmcount?$model->data->deploy_vmcount:'';
                    },                   
                    'visible'=> is_array($column)&&in_array('deploy_vmcount_d',$column),
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
<?php $this->beginBlock('activity-change') ?>
    $('.sparktristate').sparkline('html', {type: 'tristate',colorMap:{'1':'#00a65a','-1':'#dd4b39'},tooltipValueLookups: {map: $.range_map({'-1': '不活跃', '1': '活跃','0':'未设置'})}});
    $('.sparktristate_health').sparkline('html', {type: 'tristate',colorMap:<?php echo json_encode(ActivityChange::$List['health_color'])?>,tooltipValueLookups: {map: $.range_map(<?php echo json_encode(ActivityChange::$List['health'])?>)}});
    $('.activity-index').on('click', '.column-change', function () {
        $('#item-modal .modal-title').html('显示项选择');
        $('#item-modal .modal-body').html('');
        $.get('<?= Url::toRoute('column') ?>',
                function (data) {
                    $('#item-modal .modal-body').html(data);
                }
        );
    });
    $('.activity-index').on('click', '.corporation-view', function () {
        //$('.modal-title').html('企业查看');
        $('#corporation-modal .modal-body').html('');
        $.get('<?= Url::toRoute('corporation/corporation-view') ?>',{id: $(this).data('id')},
                function (data) {
                    $('#corporation-modal .modal-body').html(data);
                }
        );
    });

<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['activity-change'], \yii\web\View::POS_END); ?>
<?php Pjax::end(); ?>