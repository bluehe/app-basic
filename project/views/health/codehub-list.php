<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\helpers\Url;
use project\models\CorporationCodehub;
use project\models\CorporationProject;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '仓库管理';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['health/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id'=>'codehub-list']); ?> 
<div class="codehub-index">

        <div class="box-body">

            <p>
                <?= CorporationProject::get_corporationproject_exist($corporation_id)?Html::button('添加仓库', ['data-id'=>$corporation_id,'class' => 'btn btn-success codehub-create',]):'' ?>
            </p>
           
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'https_url',
                        'value' =>function($model) {
                            return substr($model->https_url, strrpos($model->https_url,'/')+1);
                        },
                        'format' => 'raw',
                        
                    ],
                    'username',
//                    'password',
                    [
                        'attribute' => 'ci',
                        'value' =>function($model) {
                            return $model->Ci;
                        },
                        'format' => 'raw',
                        
                    ],                  
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{update} {delete} {commit}', //只需要展示删除和更新
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                                return Html::button('<i class="fa fa-pencil"></i> 修改', ['class' => 'btn btn-primary btn-xs codehub-update',]);
                            },
                            'delete' => function($url, $model, $key) {
                                return Html::button('<i class="fa fa-trash-o"></i> 删除', ['class' => 'btn btn-danger btn-xs codehub-delete']);
                            },
                            'commit'=>function($url, $model, $key) {
                                return Html::button('<i class="fa fa-retweet"></i> 代码提交', ['class' => 'btn btn-warning btn-xs codehub-exec',]);
                            },
                        ],
                    ],
                ],
            ]);
            ?>
        </div>
</div>

<script>
<?php $this->beginBlock('codehub') ?>
   
    $('.codehub-index').on('click', '.codehub-create', function () {
        $('.modal-title').html('添加仓库');
        $('#item-modal .modal-body').html('');
        $.get('<?= Url::toRoute('health/codehub-create') ?>',{corporation_id: $(this).data('id')},
                function (data) {
                    $('#item-modal .modal-body').html(data);
                }
        );
    });
    
    $('.codehub-index').on('click', '.codehub-update', function () {
        $('.modal-title').html('修改仓库');
        $('#item-modal .modal-body').html('');
        $.get('<?= Url::toRoute('health/codehub-update') ?>',{id: $(this).parents('tr').data('key')},
                function (data) {
                    $('#item-modal .modal-body').html(data);
                }
        );
    });
    
    $('.codehub-index').on('click', '.codehub-delete', function () {
        var _this = $(this).parents('tr');
        if(!confirm('确定删除么？')){return false;}
        $.getJSON('<?= Url::toRoute('health/codehub-delete') ?>',{id: _this.data('key')},
                function (data) {
                    if (data.stat == 'success') {
                        _this.remove();        
                    } 
                }
        );
        return false;
    });
    
    $('.codehub-index').on('click', '.codehub-exec', function () {
        var _this=$(this);
        _this.addClass('disabled').removeClass('codehub-exec');
        $.getJSON('<?= Url::toRoute('health/codehub-exec') ?>',{id: _this.parents('tr').data('key')},
            function (data) {
                if(data.stat=='success'){
                    _this.addClass('codehub-exec').removeClass('disabled');
                }else{
                    alert(data.message);
                }
            }
        );
    });

<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['codehub'], \yii\web\View::POS_END); ?>
<?php Pjax::end(); ?>