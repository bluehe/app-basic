<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use project\models\ActivityData;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '活跃标准';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['standard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="standard-index">

    <div class="box box-primary">
        <div class="box-body">

            <p>
                <?= Html::a('添加条件', ['#'], ['data-toggle' => 'modal', 'data-target' => '#standard-modal','class' => 'btn btn-success standard-create']) ?>
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
                        'attribute' => 'field',
                        'value' =>
                        function($model) {
                            return ActivityData::get_code_name($model->field);
                        },
                    ],
                    [
                        'attribute' => 'type',
                        'value' =>
                        function($model) {
                            return $model->Type;
                        },
                    ],
                    'value',
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {delete}', //只需要展示删除和更新
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                               return Html::a('<i class="fa fa-pencil"></i> 修改', ['#'], ['data-toggle' => 'modal', 'data-target' => '#standard-modal', 'class' => 'btn btn-primary btn-xs standard-update',]);
                            },
                            'delete' => function($url, $model, $key) {
                                return Html::a('<i class="fa fa-trash-o"></i> 删除', ['#'], ['class' => 'btn btn-danger btn-xs standard-delete','data-type'=>$model->type,'data-field'=>$model->field]);
                               
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
    'id' => 'standard-modal',
    'header' => '<h4 class="modal-title"></h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('standard') ?>
    
    $('.standard-index').on('click', '.standard-create', function () {
        $('#standard-modal .modal-title').html('添加');
        $('#standard-modal .modal-body').html('');
        $.get('<?= Url::toRoute('create') ?>',
                function (data) {
                    $('#standard-modal .modal-body').html(data);
                }
        );
    });
    
    $('.standard-index').on('click', '.standard-update', function () {
        $('#standard-modal .modal-title').html('修改');
        $('#standard-modal .modal-body').html('');
        $.get('<?= Url::toRoute('update') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#standard-modal .modal-body').html(data);
                }
        );
    });
    
    $('.standard-index').on('click', '.standard-delete', function () {
        var _this = $(this).parents('tr');
        if(!confirm('确定删除么？')){return false;}
        $.getJSON('<?= Url::toRoute('delete') ?>',{type: $(this).data('type'),field:$(this).data('field')},
                function (data) {
                    if (data.stat == 'success') {
                        _this.remove();        
                     } 
                }
        );
        return false;
    });
    


<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['standard'], \yii\web\View::POS_END); ?>