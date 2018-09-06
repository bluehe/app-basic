<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel dh\models\WebsiteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '行业管理';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['industry/industry-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="industry-index">

    <div class="box box-primary">
        <div class="box-body">
          

            <p>
                <?= Html::a('添加行业', ['#'], ['data-toggle' => 'modal', 'data-target' => '#industry-modal', 'class' => 'btn btn-success industry-create']) ?>
            </p>
                                       <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-bordered table-hover'],
                'rowOptions' => function($model) {
                    return ['class' => $model['parent_id'] ? '' : 'success'];
                 },
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                            'attribute' => 'name',
                            'label' =>'名称',
                            'value' => function ($model, $key, $index, $column) {
                                return str_repeat('——', $model['level']-1) . $model['name'];
                            }
                        ],
                             
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {delete}',
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                               return Html::a('<i class="fa fa-pencil"></i> 修改', ['#'], ['data-toggle' => 'modal', 'data-target' => '#industry-modal', 'class' => 'btn btn-primary btn-xs industry-update',]);
                            },
                            'delete' => function($url, $model, $key) {
                                return Html::a('<i class="fa fa-trash-o"></i> 删除', ['industry-delete', 'id' => $key], ['class' => 'btn btn-danger btn-xs','data-confirm' =>'确定要删除此项吗？','data-method' => 'post',]);
                            },
                            
                        ],
                    ],
                ],
            ]);
            ?>
                    </div>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'industry-modal',
    'header' => '<h4 class="modal-title"></h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('industry') ?>
    $('.industry-index').on('click', '.industry-create', function () {
        $('.modal-title').html('添加行业');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('industry-create') ?>',
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });

    $('.industry-index').on('click', '.industry-update', function () {
        $('.modal-title').html('修改行业');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('industry-update') ?>', {id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });

<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['industry'], \yii\web\View::POS_END); ?>