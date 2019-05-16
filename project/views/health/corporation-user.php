<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use project\models\CorporationAccount;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '用户管理';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['health/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

        <div class="box-body">

            <p>
                <?= CorporationAccount::get_corporationaccount_exist($corporation_id, CorporationAccount::ADMIN_YES)?'':Html::button('添加账号', ['data-id'=>$corporation_id,'class' => 'btn btn-success account-create',]) ?>
                <?= CorporationAccount::get_corporationaccount_exist($corporation_id, CorporationAccount::ADMIN_YES)?Html::button('添加用户', ['data-id'=>$corporation_id,'class' => 'btn btn-warning pull-right account-add']):'' ?>
            </p>
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n<div class=table-responsive>{items}</div>\n{pager}",
                'summary' => "第{begin}-{end}条，共{totalCount}条",
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'account_name',
                    'user_name',
                    [
                        'attribute' => 'is_admin',
                        'value' =>function($model) {
                            return $model->Admin;
                        },
                        'format' => 'raw',
                        
                    ],
                    [
                        'attribute' => 'add_type',
                        'value' =>function($model) {
                            return $model->Type;
                        },
                        'format' => 'raw',
                        
                    ],
                   
                    ['class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'template' => '{delete}', //只需要展示删除和更新
                        'buttons' => [                          
                            'delete' => function($url, $model, $key) {
                                return $model->add_type== CorporationAccount::TYPE_CHECK?'':Html::button('<i class="fa fa-trash-o"></i> 删除', ['class' => 'btn btn-danger btn-xs account-delete']);
                            },
                        ],
                    ],
                ],
            ]);
            ?>
        </div>
</div>

<script>
<?php $this->beginBlock('user') ?>
   
    $('.user-index').on('click', '.account-create', function () {
        $('#item-modal .modal-body').html('');
        $.get('<?= Url::toRoute('health/account-create') ?>',{id: $(this).data('id')},
                function (data) {
                    $('#item-modal .modal-body').html(data);
                }
        );
    });
    
    $('.user-index').on('click', '.account-add', function () { 
        var $id=$(this).data('id');
        $.getJSON('<?= Url::toRoute('health/account-add') ?>',{id: $id},
                function (data) {                  
                    if(data.stat=='success'){
                        $.get('<?= Url::toRoute('health/corporation-user') ?>',{id: $id},
                            function (data1) {
                                $('#item-modal .modal-body').html(data1);
                            }
                    );
                    }
                   
                }
        );
    });
    
    $('.user-index').on('click', '.account-delete', function () {
        var _this = $(this).parents('tr');
        if(!confirm('确定删除么？')){return false;}
        $.getJSON('<?= Url::toRoute('health/account-delete') ?>',{id: _this.data('key')},
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
<?php $this->registerJs($this->blocks['user'], \yii\web\View::POS_END); ?>