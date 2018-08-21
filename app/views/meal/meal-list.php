<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '套餐管理';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['meal/meal-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="meal-index">

    <div class="box box-primary">
        <div class="box-body">

            <p>
                <?= Html::a('添加套餐', ['meal-create'], ['class' => 'btn btn-success']) ?>
            </p>
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'name',
                    'region',
                    'amount',
                    'content',
//                    'order_sort',                                      
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {delete}', //只需要展示删除和更新
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                                return Html::a('<i class="fa fa-pencil"></i> 修改', ['meal-update', 'id' => $key], ['class' => 'btn btn-primary btn-xs',]);
                            },
                            'delete' => function($url, $model, $key) {
                                return Html::a('<i class="fa fa-trash-o"></i> 删除', ['meal-delete', 'id' => $key], ['class' => 'btn btn-danger btn-xs', 'data' => ['confirm' => '你确定要删除吗？',]]);
                            },
                        ],
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>