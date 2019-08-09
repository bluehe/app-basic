<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\helpers\Url;
use project\models\CorporationAccount;
use project\models\CorporationProject;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '用户管理';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['health/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id'=>'corporation_user']); ?> 
<div class="user-index">

        <div class="box-body">

            <p>
                <?= CorporationAccount::get_corporationaccount_exist($corporation_id, CorporationAccount::ADMIN_YES)?Html::button('创建用户', ['data-id'=>$corporation_id,'class' => 'btn btn-warning account-create']):Html::button('添加账号', ['data-id'=>$corporation_id,'class' => 'btn btn-success account-add',]) ?>
                <?= CorporationProject::get_corporationproject_exist($corporation_id)?Html::button('成员管理', ['data-id'=>$corporation_id,'class' => 'btn btn-danger pull-right member-list',]):'' ?>
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
                    'password',
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
                        'template' => '{update} {delete}', //只需要展示删除和更新
                        'buttons' => [
                            'update' => function($url, $model, $key) {
                               return CorporationAccount::get_corporationaccount_exist($model->corporation_id, CorporationAccount::ADMIN_YES)?'':Html::button('<i class="fa fa-paper-plane"></i> 提升', ['class' => 'btn btn-primary btn-xs account-update',]);
                            },
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
   
    $('.user-index').on('click', '.account-add', function () {
        $('#list-modal .modal-body').html('');
        $.get('<?= Url::toRoute('health/account-add') ?>',{corporation_id: $(this).data('id')},
                function (data) {
                    $('#list-modal .modal-body').html(data);
                }
        );
    });
    
    $('.user-index').on('click', '.account-update', function () {
        $('#list-modal .modal-body').html('');
        $.get('<?= Url::toRoute('health/account-update') ?>',{id:$(this).parents('tr').data('key')},
                function (data) {
                    $('#list-modal .modal-body').html(data);
                }
        );
    });
    
    $('.user-index').on('click', '.account-create', function () { 
        var $id=$(this).data('id');
        $.getJSON('<?= Url::toRoute('health/account-create') ?>',{corporation_id: $id},
                function (data) {                  
                    if(data.stat=='success'){
                        $.get('<?= Url::toRoute('health/corporation-user') ?>',{id: $id},
                            function (data1) {
                                $('#list-modal .modal-body').html(data1);
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
    
    $('.user-index').on('click', '.member-list', function () {
        $('.modal-title').html('成员管理');
        $('#list-modal .modal-body').html('');
        $.get('<?= Url::toRoute('health/member-list') ?>',{corporation_id: $(this).data('id')},
                function (data) {
                    $('#list-modal .modal-body').html(data);
                }
        );
    });

<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['user'], \yii\web\View::POS_END); ?>
<?php Pjax::end(); ?>