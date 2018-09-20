<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '字段管理';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['field/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="field-index">

    <div class="box box-primary">
        <div class="box-body">

            <p>
                <?= Html::a('添加字段', ['#'], ['data-toggle' => 'modal', 'data-target' => '#field-modal','class' => 'btn btn-success field-create']) ?>
            </p>
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
                    [
                        'attribute' => 'parent',
                        'value' =>
                        function($model) {
                            return $model->parent ? $model->parent0->name : '';
                        },
                    ],
                    'name',
                    'code',
                    [
                        'attribute' => 'type',
                        'value' =>
                        function($model) {
                            return $model->Type;
                        },
                    ],
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {delete}', //只需要展示删除和更新
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                               return Html::a('<i class="fa fa-pencil"></i> 修改', ['#'], ['data-toggle' => 'modal', 'data-target' => '#field-modal', 'class' => 'btn btn-primary btn-xs field-update',]);
                            },
                            'delete' => function($url, $model, $key) {
                                return Html::a('<i class="fa fa-trash-o"></i> 删除', ['delete', 'id' => $key], ['class' => 'btn btn-danger btn-xs','data-confirm' =>'确定删除吗？','data-method' => 'post',]);
                               
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
        $.get('<?= Url::toRoute('create') ?>',
                function (data) {
                    $('#field-modal .modal-body').html(data);
                }
        );
    });
    
    $('.field-index').on('click', '.field-update', function () {
        $('#field-modal .modal-title').html('修改');
        $('#field-modal .modal-body').html('');
        $.get('<?= Url::toRoute('update') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#field-modal .modal-body').html(data);
                }
        );
    });
    


<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['field'], \yii\web\View::POS_END); ?>