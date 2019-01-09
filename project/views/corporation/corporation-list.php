<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use project\models\User;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use project\models\Corporation;
use project\models\Parameter;
use kartik\file\FileInput;
use project\components\CommonHelper;
use project\models\Industry;
use project\models\CorporationMeal;
use project\models\Meal;
use kartik\daterange\DateRangePicker;
/* @var $this yii\web\View */
/* @var $searchModel rky\models\CorporationSearch */


$this->title = '企业管理';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['corporation/corporation-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="corporation-index">

    <div class="box box-primary">
        <div class="box-body">
            <?php Pjax::begin(); ?>
            <div class="clearfix" style="margin-bottom:10px;">
                
                <?= in_array(Yii::$app->user->identity->role, [User::ROLE_OB_DATA,User::ROLE_BD,User::ROLE_PM])||Yii::$app->authManager->getAssignment(Yii::$app->authManager->getRole('superadmin')->name, Yii::$app->user->identity->id)?Html::a('添加企业', ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','class' => 'btn btn-success corporation-create']):'' ?>
                <?= Html::a('<i class="fa fa-filter" title="选择需要显示的列"></i>', ['#'], ['data-toggle' => 'modal', 'data-target' => '#list-modal', 'class' => 'btn btn-danger pull-right column-change','style'=>'margin-left:15px']) ?>
                <?= Html::a('<i class="fa fa-share-square-o"></i>全部导出', ['corporation-export?'.Yii::$app->request->queryString], ['class' => 'btn btn-warning pull-right']) ?>
                <?php if(in_array(Yii::$app->user->identity->role, [User::ROLE_OB_DATA,User::ROLE_BD,User::ROLE_PM])||Yii::$app->authManager->getAssignment(Yii::$app->authManager->getRole('superadmin')->name, Yii::$app->user->identity->id)):?>
                 <div style="display: inline-block;margin:0 15px;" class="pull-right"><?=
                    FileInput::widget([
                        'name' => 'files[]',
                        'pluginOptions' => [
                            'language' => 'zh',
                            'layoutTemplates' => ['progress' => ''],
                            //关闭按钮
                            'showPreview' => false,
                            'showCancel' => false,
                            'showCaption' => false,
                            'showRemove' => false,
                            'showUpload' => false,
                            //浏览按钮样式
                            'browseClass' => 'btn btn-primary',
                            'browseLabel' => '导入数据',
                            //错误提示
                            'elErrorContainer' => false,
                            //进度条
                            //'progressClass' => 'hide',
                            //'progressUploadThreshold' => 'hide',
                            //上传
                            'uploadAsync' => true,
                            'uploadUrl' => Url::toRoute(['corporation-import']),
                            'maxFileSize' => CommonHelper::maxSize(),
                        ],
                        'options' => ['accept' => 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                        'pluginEvents' => [
                            //选择后直接上传
                            'change' => 'function() {$(this).fileinput("upload");}',
                            //上传成功
                            'fileuploaded' => 'function(event, data) {window.location.reload();}',
                        ],
                    ]);
                    ?>
                </div>
                <?= Html::a('<i class="fa fa-download"></i>下载模板', ['corporation-temple'], ['class' => 'pull-right','style'=>'margin-top:10px']) ?>
              <?php endif;?>
              
            </div>
             
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                    'attribute' => 'base_company_name',
                    'value' =>
                        function($model) {
                            return Html::a($model->base_company_name, ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','class' => 'corporation-view',]);
                        },
                    'format' => 'raw',
                    ],
                    [
                            'attribute' => 'base_bd',
                            'value' =>
                            function($model) {
                                return '<span class="bd-list" data-toggle="modal" data-target="#list-modal">'.($model->base_bd?($model->baseBd->nickname?$model->baseBd->nickname:$model->baseBd->username):'<span class="not-set">(未设置)</span>').'</span>';
                            },
                            'format'=>'raw',
                            'filter' => User::get_bd(null, Corporation::get_existbd()), 
                    ],
                    ['attribute' =>'huawei_account','visible'=> is_array($column)&&in_array('huawei_account',$column),],  
                    [
                        'attribute' => 'base_industry',
                        'value' =>
                        function($model) {
                            return $model->get_industry($model->id);   //主要通过此种方式实现
                        },
                        'filter' => Industry::getIndustriesName(), //此处我们可以将筛选项组合成key-value形式
                        'visible'=> is_array($column)&&in_array('base_industry',$column),
                    ],
                    [
                        'attribute' => 'contact_park',
                        'value' =>
                        function($model) {
                            return implode(',', Parameter::get_para_value('contact_park',$model->contact_park));   //主要通过此种方式实现
                        },
                        'filter' => Parameter::get_type('contact_park'),
                        'visible'=> is_array($column)&&in_array('contact_park',$column),
                    ],
                    ['attribute' =>'contact_address','visible'=> is_array($column)&&in_array('contact_address',$column),],
                    [
                        'attribute' => 'contact_location',
                        'value' =>
                        function($model) {
                            return $model->contact_location?'<span class="text-green">是</span>':'<span class="text-red">否</span>';   //主要通过此种方式实现
                        },
                        'format' => 'raw',
                        'filter' => [1=>'是',2=>'否'], 
                        'visible'=> is_array($column)&&in_array('contact_location',$column),
                    ],
                    ['attribute' =>'intent_set','value' =>function($model) {return $model->intent_set?$model->intentSet->name:$model->intent_set;},'filter' => Meal::get_meal(),'visible'=> is_array($column)&&in_array('intent_set',$column),],
                    ['attribute' =>'intent_number','visible'=> is_array($column)&&in_array('intent_number',$column),],
                    ['attribute' =>'intent_amount','visible'=> is_array($column)&&in_array('intent_amount',$column),],
                    ['attribute' =>'base_company_scale','visible'=> is_array($column)&&in_array('base_company_scale',$column),],  
                    ['attribute' =>'base_registered_capital','value' =>function($model) {return $model->base_registered_capital?floatval($model->base_registered_capital):$model->base_registered_capital;},'visible'=> is_array($column)&&in_array('base_registered_capital',$column),],
                    ['attribute' =>'base_registered_time','value' =>function($model) {return $model->base_registered_time>0?date('Y-m-d',$model->base_registered_time):'';},
                    'filter' => DateRangePicker::widget([
                        'name' => 'CorporationSearch[base_registered_time]',
                        'useWithAddon' => true,
                        'presetDropdown' => true,
                        'convertFormat' => true,
                        'value' => isset(Yii::$app->request->get('CorporationSearch')['base_registered_time'])?Yii::$app->request->get('CorporationSearch')['base_registered_time']:'',
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
                    'visible'=> is_array($column)&&in_array('base_registered_time',$column), ],
                    ['attribute' =>'base_main_business','visible'=> is_array($column)&&in_array('base_main_business',$column),],
                    ['attribute' =>'base_last_income','value' =>function($model) {return $model->base_last_income?floatval($model->base_last_income):$model->base_last_income;},'visible'=> is_array($column)&&in_array('base_last_income',$column),],
                    ['attribute' =>'contact_business_name','visible'=> is_array($column)&&in_array('contact_business_name',$column),],
                    ['attribute' =>'contact_business_job','visible'=> is_array($column)&&in_array('contact_business_job',$column),],
                    ['attribute' =>'contact_business_tel','visible'=> is_array($column)&&in_array('contact_business_tel',$column),],
                    ['attribute' =>'contact_technology_name','visible'=> is_array($column)&&in_array('contact_technology_name',$column),],
                    ['attribute' =>'contact_technology_job','visible'=> is_array($column)&&in_array('contact_technology_job',$column),],
                    ['attribute' =>'contact_technology_tel','visible'=> is_array($column)&&in_array('contact_technology_tel',$column),],
                    ['attribute' =>'develop_scale','visible'=> is_array($column)&&in_array('develop_scale',$column),],
                    ['attribute' =>'develop_pattern','value' =>function($model) {return implode(',', Parameter::get_para_value('develop_pattern',$model->develop_pattern));},'filter' => Parameter::get_type('develop_pattern'),'visible'=> is_array($column)&&in_array('develop_pattern',$column),],
                    ['attribute' =>'develop_scenario','value' =>function($model) {return implode(',', Parameter::get_para_value('develop_scenario',$model->develop_scenario));},'filter' => Parameter::get_type('develop_scenario'),'visible'=> is_array($column)&&in_array('develop_scenario',$column),],
                    ['attribute' =>'develop_science','value' =>function($model) {return implode(',', Parameter::get_para_value('develop_science',$model->develop_science));},'filter' => Parameter::get_type('develop_science'),'visible'=> is_array($column)&&in_array('develop_science',$column),],
                    ['attribute' =>'develop_language','value' =>function($model) {return implode(',', Parameter::get_para_value('develop_language',$model->develop_language));},'filter' => Parameter::get_type('develop_language'),'visible'=> is_array($column)&&in_array('develop_language',$column),],
                    ['attribute' =>'develop_IDE','value' =>function($model) {return implode(',', Parameter::get_para_value('develop_IDE',$model->develop_IDE));},'filter' => Parameter::get_type('develop_IDE'),'visible'=> is_array($column)&&in_array('develop_IDE',$column),],
                    ['attribute' =>'develop_current_situation','visible'=> is_array($column)&&in_array('develop_current_situation',$column),],
                    ['attribute' =>'develop_weakness','visible'=> is_array($column)&&in_array('develop_weakness',$column),],
                    [
                        'attribute' => 'stat',
                        'value' =>
                            function($model) {
                                switch($model->stat){
                                    case Corporation::STAT_CREATED:$color='text-green';break;
                                    case Corporation::STAT_FOLLOW:$color='text-aqua';break;
                                    case Corporation::STAT_REFUSE:$color='text-gray';break;
                                    case Corporation::STAT_APPLY:$color='text-blue';break;
                                    case Corporation::STAT_CHECK:$color='text-yellow';break;
                                    case Corporation::STAT_ALLOCATE:$color='text-maroon';break;
                                    case Corporation::STAT_AGAIN:$color='text-purple';break;
                                    default: $color='';
                                }
                                return Html::tag('span', $model->Stat,['data-toggle'=>'modal','data-target'=>'#list-modal', 'class' =>'stat-list '.$color]);
                            },
                        'format' => 'raw',
                        'filter' => Corporation::$List['stat'],    
                    ],
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '',
                        'template' => '{follow} {apply} {check} {allocate} {again} {refuse}',
                        'buttons' => [                                                     
                            'follow' => function($url, $model, $key) {
                                return in_array($model->stat,[Corporation::STAT_CREATED,Corporation::STAT_REFUSE])&&CommonHelper::corporationRule('update', $key)?Html::a('<i class="fa fa-hourglass-2"></i> 跟进', ['corporation-update-stat', 'id' => $key,'stat'=> Corporation::STAT_FOLLOW], ['class' => 'btn bg-aqua btn-xs','data-method' => 'post',]):'';
                            }, 
                            'refuse' => function($url, $model, $key) {
                                return in_array($model->stat,[Corporation::STAT_FOLLOW,Corporation::STAT_OVERDUE])&&CommonHelper::corporationRule('update', $key)?Html::a('<i class="fa fa-times"></i> 无意愿', ['corporation-update-stat', 'id' => $key,'stat'=> Corporation::STAT_REFUSE], ['class' => 'btn bg-gray btn-xs','data-method' => 'post',]):'';
                            },
                            'apply' => function($url, $model, $key) {
                                return in_array($model->stat,[Corporation::STAT_FOLLOW])&&CommonHelper::corporationRule('update', $key)?Html::a('<i class="fa fa-check"></i> 申请', ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','class' => 'btn bg-light-blue btn-xs corporation-apply',]):'';
                            },
                            'check' => function($url, $model, $key) {
                                return in_array($model->stat,[Corporation::STAT_APPLY])&&CommonHelper::corporationRule('update', $key)?Html::a('<i class="fa fa-check-square-o"></i> 审核', ['corporation-update-stat', 'id' => $key,'stat'=> Corporation::STAT_CHECK], ['class' => 'btn bg-yellow btn-xs','data-method' => 'post','data-confirm' =>'确定完成审核？',]):'';
                            },
                            'allocate'=>function($url, $model, $key) {
                                return in_array($model->stat,[Corporation::STAT_CHECK])&&CommonHelper::corporationRule('update', $key)?Html::a('<i class="fa fa-trophy"></i> 下拨', ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','class' => 'btn bg-maroon btn-xs corporation-allocate',]):'';
                            },
                            'again'=>function($url, $model, $key) {
                                return in_array($model->stat,[Corporation::STAT_ALLOCATE,Corporation::STAT_OVERDUE,Corporation::STAT_AGAIN])&&CommonHelper::corporationRule('update', $key)?Html::a('<i class="fa fa-refresh"></i> 续拨', ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','class' => 'btn bg-purple btn-xs corporation-again',]):'';
                            },

                        ],
                        'visible'=> in_array(Yii::$app->user->identity->role, [User::ROLE_OB_DATA,User::ROLE_BD,User::ROLE_PM])||Yii::$app->authManager->getAssignment(Yii::$app->authManager->getRole('superadmin')->name, Yii::$app->user->identity->id),
                    ],
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {delete}',
                        'buttons' => [                           
                            'update' => function($url, $model, $key) {
                                return CommonHelper::corporationRule('update', $key)?Html::a('<i class="fa fa-pencil"></i> 修改', ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','class' => 'btn btn-warning btn-xs corporation-update',]):'';
                            },
                            'delete' => function($url, $model, $key) {
                                return !CorporationMeal::get_allocate($model->id)&&CommonHelper::corporationRule('delete', $key)?Html::a('<i class="fa fa-trash-o"></i> 删除', ['corporation-delete', 'id' => $key], ['class' => 'btn btn-danger btn-xs','data-confirm' =>'删除企业将会影响相关活跃记录，此操作不能恢复，你确定要删除企业吗？','data-method' => 'post',]):'';
                            },                           
                        ],
                        'visible'=> in_array(Yii::$app->user->identity->role, [User::ROLE_OB_DATA,User::ROLE_BD,User::ROLE_PM])||Yii::$app->authManager->getAssignment(Yii::$app->authManager->getRole('superadmin')->name, Yii::$app->user->identity->id),
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'corporation-modal',
    'header' => '<h4 class="modal-title"></h4>',  
    'options' => [
        'tabindex' => false,
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
        'data-keyboard'=>false,
    ],
]);
Modal::end();
Modal::begin([
    'id' => 'list-modal',
    'header' => '<h4 class="modal-title"></h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('corporation') ?>
    $('.corporation-index').on('click', '.corporation-view', function () {
        $('#corporation-modal .modal-header').hide();
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-view') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.corporation-index').on('click', '.corporation-create', function () {
        $('#corporation-modal .modal-header').hide();
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-create') ?>',
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.corporation-index').on('click', '.corporation-update', function () {
        $('#corporation-modal .modal-header').hide();
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-update') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.corporation-index').on('click', '.corporation-apply', function () {
        $('#corporation-modal .modal-title').html('企业申请');
        $('#corporation-modal .modal-header').show();
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-apply') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.corporation-index').on('click', '.corporation-allocate', function () {
        $('#corporation-modal .modal-title').html('企业下拨');
        $('#corporation-modal .modal-header').show();
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-allocate') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.corporation-index').on('click', '.corporation-again', function () {
        $('#corporation-modal .modal-title').html('企业续拨');
        $('#corporation-modal .modal-header').show();
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-allocate') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.corporation-index').on('click', '.bd-list', function () {
        $('.modal-title').html('历史记录');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-bd') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    $('.corporation-index').on('click', '.stat-list', function () {
        $('.modal-title').html('状态记录');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-stat') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.corporation-index').on('click', '.column-change', function () {
        $('.modal-title').html('显示项选择');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-column') ?>',
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['corporation'], \yii\web\View::POS_END); ?>