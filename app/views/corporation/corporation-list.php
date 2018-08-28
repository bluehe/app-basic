<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\User;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\models\Corporation;
use app\models\Parameter;
use kartik\file\FileInput;
use app\components\CommonHelper;
use app\models\Industry;
/* @var $this yii\web\View */
/* @var $searchModel rky\models\CorporationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '企业管理';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['corporation/corporation-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="corporation-index">

    <div class="box box-primary">
        <div class="box-body">
            <?php Pjax::begin(); ?>
            <div style="margin-bottom:10px;">
                <?= Html::a('添加企业', ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','class' => 'btn btn-success corporation-create']) ?>
                <?= Html::a('<i class="fa fa-share-square-o"></i>全部导出', ['corporation-export?'.Yii::$app->request->queryString], ['class' => 'btn btn-warning pull-right']) ?>
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
                            return $model->base_bd?($model->baseBd->nickname?$model->baseBd->nickname:$model->baseBd->username):'';   //主要通过此种方式实现
                        },
                        'filter' => User::get_bd(null, Corporation::get_existbd()), //此处我们可以将筛选项组合成key-value形式
                ],
                [
                        'attribute' => 'base_industry',
                        'value' =>
                        function($model) {
                            return $model->get_industry($model->id);   //主要通过此种方式实现
                        },
                        'filter' => Industry::getIndustriesName(), //此处我们可以将筛选项组合成key-value形式
                ],
                [
                        'attribute' => 'contact_park',
                        'value' =>
                        function($model) {
                            return implode(',', Parameter::get_para_value('contact_park',$model->contact_park));   //主要通过此种方式实现
                        },
                        'filter' => Parameter::get_type('contact_park'), //此处我们可以将筛选项组合成key-value形式
                ],
                [
                        'attribute' => 'contact_location',
                        'value' =>
                        function($model) {
                            return $model->contact_location?'<span class="text-green">是</span>':'<span class="text-red">否</span>';   //主要通过此种方式实现
                        },
                        'format' => 'raw',
                        'filter' => [1=>'是',2=>'否'], //此处我们可以将筛选项组合成key-value形式
                ],
                [
                        'attribute' => 'stat',
                        'value' =>
                            function($model) {
                                return Html::tag('span', $model->Stat,['class' => ($model->stat== Corporation::STAT_OVERDUE ? 'text-red' : ($model->stat== Corporation::STAT_CHECK ? 'text-yellow' : ($model->stat== Corporation::STAT_ALLOCATE ? 'text-green' : '')))]);
                            },
                        'format' => 'raw',
                        'filter' => Corporation::$List['stat'],    
                ],
               
                ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {check} {delete}',
                        'buttons' => [                           
                            'update' => function($url, $model, $key) {
                                return Yii::$app->user->can('公司修改',['id'=>$key])?Html::a('<i class="fa fa-pencil"></i> 修改', ['#'], ['data-toggle' => 'modal', 'data-target' => '#corporation-modal','class' => 'btn btn-warning btn-xs corporation-update',]):'';
                            },
                            'check' => function($url, $model, $key) {
                                return $model->stat== Corporation::STAT_APPLY&&Yii::$app->user->identity->role=='pm'?Html::a('<i class="fa fa-check"></i> 审核通过', ['corporation-check', 'id' => $key], ['class' => 'btn btn-info btn-xs']):'';
                            },
                            'delete' => function($url, $model, $key) {
                                return Yii::$app->user->can('公司删除',['id'=>$key])?Html::a('<i class="fa fa-trash-o"></i> 删除', ['corporation-delete', 'id' => $key], ['class' => 'btn btn-danger btn-xs', 'data' => ['confirm' => '删除企业将会影响相关活跃记录，此操作不能恢复，你确定要删除企业吗？',]]):'';
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
    'id' => 'corporation-modal',
    'header' => null,
    'closeButton'=>false,    
    'options' => [
        'tabindex' => false,
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
        'data-keyboard'=>false,
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('corporation') ?>
    $('.corporation-index').on('click', '.corporation-view', function () {
        //$('.modal-title').html('企业查看');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-view') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.corporation-index').on('click', '.corporation-create', function () {
        //$('.modal-title').html('企业添加');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-create') ?>',
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
        $('.corporation-index').on('click', '.corporation-update', function () {
        $('.modal-title').html('企业更新');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-update') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });

<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['corporation'], \yii\web\View::POS_END); ?>