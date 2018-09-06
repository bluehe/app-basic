<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Meal;
use app\grid\StatusColumn;
use yii\helpers\Url;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '参数管理';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['parameter/parameter-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="parameter-index">

    <div class="box box-primary">
        <div class="box-body">

            <p>
                <?= Html::a('添加参数', ['#'], ['data-toggle' => 'modal', 'data-target' => '#parameter-modal', 'class' => 'btn btn-success parameter-create']) ?>
            </p>
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'type',
                    'code',
                    'title',
                    'description',
                                       
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {delete}', //只需要展示删除和更新
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                                return Html::a('<i class="fa fa-pencil"></i> 修改', ['#'], ['data-toggle' => 'modal', 'data-type'=>$model->type,'data-code'=>$model->code, 'data-target' => '#parameter-modal', 'class' => 'btn btn-primary btn-xs parameter-update',]);
                            },
                            'delete' => function($url, $model, $key) {
                                return Html::a('<i class="fa fa-trash-o"></i> 删除', ['#'], ['class' => 'btn btn-danger btn-xs parameter-delete','data-type'=>$model->type,'data-code'=>$model->code]);
                               
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
    'id' => 'parameter-modal',
    'header' => '<h4 class="modal-title"></h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('parameter') ?>
    $('.corporation-index').on('click', '.corporation-view', function () {
        //$('.modal-title').html('企业查看');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('corporation-view') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.parameter-index').on('click', '.parameter-create', function () {
        $('.modal-title').html('添加参数');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('parameter-create') ?>',
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.parameter-index').on('click', '.parameter-update', function () {
        $('.modal-title').html('更新参数');
        $('.modal-body').html('');
        $.get('<?= Url::toRoute('parameter-update') ?>'+'?id[type]='+$(this).data('type')+'&id[code]='+$(this).data('code'),
                function (data) {
                    $('.modal-body').html(data);
                }
        );
    });
    
    $('.parameter-index').on('click', '.parameter-delete', function () {
        var _this = $(this).parents('tr');
        if(!confirm('确定删除么？')){return false;}
        $.getJSON('<?= Url::toRoute('parameter-delete') ?>',{type: $(this).data('type'),code:$(this).data('code')},
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
<?php $this->registerJs($this->blocks['parameter'], \yii\web\View::POS_END); ?>