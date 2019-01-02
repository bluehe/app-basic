<?php

use yii\helpers\Html;
use yii\grid\GridView;
use project\models\CorporationMeal;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use project\models\User;
use project\models\Meal;
use kartik\daterange\DateRangePicker;
use kartik\file\FileInput;
use project\components\CommonHelper;
use project\models\Parameter;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '下拨管理';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['allocate/allocate-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="allocate-index">

    <div class="box box-primary">
        <div class="box-body">

             <div class="clearfix" style="margin-bottom:10px;">
            <?= Html::a('<i class="fa fa-share-square-o"></i>全部导出', ['allocate-export?'.Yii::$app->request->queryString], ['class' => 'btn btn-warning pull-right']) ?>
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
                            'uploadUrl' => Url::toRoute(['allocate-import']),
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
                <?= Html::a('<i class="fa fa-download"></i>下载模板', ['allocate-temple'], ['class' => 'pull-right','style'=>'margin-top:10px']) ?>
              <?php endif;?>
            </div>
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
                        'attribute' => 'annual',
                        'value' => 
                            function($model) {
                                return implode(',', Parameter::get_para_value('allocate_annual',$model->annual));
                            },
                        'filter' => Parameter::get_type('allocate_annual'),
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
    'id' => 'allocate-modal',
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
<?php $this->beginBlock('allocate') ?>
    
    $('.allocate-index').on('click', '.corporation-view', function () {
        //$('.modal-title').html('企业查看');
        $('#corporation-modal .modal-body').html('');
        $.get('<?= Url::toRoute('corporation/corporation-view') ?>',{id: $(this).data('id')},
                function (data) {
                    $('#corporation-modal .modal-body').html(data);
                }
        );
    });
    
    $('.allocate-index').on('click', '.allocate-update', function () {
        $('#allocate-modal .modal-title').html('修改');
        $('#allocate-modal .modal-body').html('');
        $.get('<?= Url::toRoute('allocate-update') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#allocate-modal .modal-body').html(data);
                }
        );
    });
    


<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['allocate'], \yii\web\View::POS_END); ?>