<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use common\models\Crontab;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '任务管理';
$this->params['breadcrumbs'][] = ['label' => '系统设置', 'url' => ['crontab/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="crontab-index">

    <div class="box box-primary">
        <div class="box-body">

            <p>
                <?= Html::a('添加任务', ['#'], ['data-toggle' => 'modal', 'data-target' => '#crontab-modal','class' => 'btn btn-success crontab-create']) ?>
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
                    'name',
                    'route',
                    'crontab_str',
                    'last_rundate',
                    'next_rundate',
                   
                    [
                        'attribute' => 'switch',
                        'value' =>
                        function($model) {
                            return $model->Switch;   //主要通过此种方式实现
                        },
                        'format' => 'raw',
                        'filter' => Crontab::$List['switch'], 
                       
                    ],
                  
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {delete}', //只需要展示删除和更新
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                               return Html::a('<i class="fa fa-pencil"></i> 修改', ['#'], ['data-toggle' => 'modal', 'data-target' => '#crontab-modal', 'class' => 'btn btn-primary btn-xs crontab-update',]);
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
    'id' => 'crontab-modal',
    'header' => '<h4 class="modal-title"></h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('crontab') ?>
    
    $('.crontab-index').on('click', '.crontab-create', function () {
        $('#crontab-modal .modal-title').html('添加');
        $('#crontab-modal .modal-body').html('');
        $.get('<?= Url::toRoute('create') ?>',
                function (data) {
                    $('#crontab-modal .modal-body').html(data);
                }
        );
    });
    
    $('.crontab-index').on('click', '.crontab-update', function () {
        $('#crontab-modal .modal-title').html('修改');
        $('#crontab-modal .modal-body').html('');
        $.get('<?= Url::toRoute('update') ?>',{id: $(this).closest('tr').data('key')},
                function (data) {
                    $('#crontab-modal .modal-body').html(data);
                }
        );
    });
    
    


<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['crontab'], \yii\web\View::POS_END); ?>