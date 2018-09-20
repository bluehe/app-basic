<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use kartik\file\FileInput;
use yii\bootstrap\Modal;
use project\components\CommonHelper;
use project\models\ImportLog;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '数据导入';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['import/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="import-index">

    <div class="box box-primary">
        <div class="box-body">

            <div class="clearfix" style="margin-bottom:10px;">
                <div style="display: inline-block;margin:0 15px;" class="pull-right">
                <?=
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
                            'browseLabel' => '上传文件',
                            //错误提示
                            'elErrorContainer' => false,
                            //进度条
                            //'progressClass' => 'hide',
                            //'progressUploadThreshold' => 'hide',
                            //上传
                            'uploadAsync' => true,
                            'uploadUrl' => Url::toRoute(['import']),
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
            </div>
            <?php Pjax::begin(); ?>
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
//                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'created_at:datetime',
                    'name',                  
                    [
                        'attribute' => 'statistics_at',
                        'value' =>
                        function($model) {
                            return ($model->statistics_at>0?date('Y-m-d',$model->statistics_at):'未设置').' '.($model->stat == ImportLog::STAT_INDUCE?'':Html::a('设置日期', '#', [
                                        'data-toggle' => 'modal',
                                        'data-target' => '#import-modal',
                                        'class' => 'btn btn-success btn-xs bind',
                                        'data-id' => $model->id,
                              
                            ]));
                            //主要通过此种方式实现
                        },
                        'format' => 'raw',
                        
                    ],
                    [
                        'attribute' => 'stat',
                        'value' =>
                        function($model) {
                            return Html::tag('span', $model->Stat, ['class' => ($model->stat == ImportLog::STAT_UPLOAD? 'text-aqua' : ($model->stat == ImportLog::STAT_INDUCE ? 'text-green' : 'text-red') )]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{induce} {clean}',
                        'buttons' => [
                            'induce' => function($url, $model, $key) {
                                        return $model->stat!=ImportLog::STAT_INDUCE?Html::tag('button', '<i class="fa fa-hourglass-half"></i> 生成数据', ['class' => 'btn btn-primary btn-xs induce', 'data-id' => $key]):'';
                            },
                            'clean' => function($url, $model, $key) {
                                        return $model->stat==ImportLog::STAT_INDUCE?Html::tag('button', '<i class="fa fa-refresh"></i> 清除数据', ['class' => 'btn btn-warning btn-xs clean', 'data-id' => $key]):'';
                            },
                        ],
                    ],
                ],
            ]);
            ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'import-modal',
    'header' => '<h4 class="modal-title">设置日期</h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('import') ?>
    
    $('.import-index').on('click', '.bind', function () {
        $.get('<?= Url::toRoute('bind') ?>', {id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#import-modal .modal-body').html(data);
                }
        );
    });
    
    //数据生成
     $('.import-index').on('click', '.induce',function () {
        var _this=$(this);
        _this.addClass('disabled').removeClass('induce').find('i').addClass('fa-spin');
        $.getJSON("<?= Url::toRoute('induce') ?>", {id: _this.data('id')}, function (data) {});

    });
    
    //数据清除
     $('.import-index').on('click', '.clean',function () {
        var _this=$(this);
        _this.addClass('disabled').removeClass('clean').find('i').addClass('fa-spin');
        $.getJSON("<?= Url::toRoute('clean') ?>", {id: _this.data('id')}, function (data) {});

    });


<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['import'], \yii\web\View::POS_END); ?>