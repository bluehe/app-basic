<?php

use project\models\User;
use yii\helpers\Url;
use project\components\CommonHelper;
//use yii\widgets\DetailView;

/* @var $this yii\web\View */
?>
<div class="row bd-timeline">
    <div class="col-md-12">
        <!-- The time line -->
        <ul class="timeline">
            <!-- timeline time label -->
<!--            <li class="time-label">
                <span class="bg-blue">11</span>
            </li>-->
            <!-- /.timeline-label -->
            <!-- timeline item -->
            <?php foreach($model as $bd){?>
<!--            <li class="time-label">
                <span class="bg-blue"><?= date('Y-m-d',$bd->start_time)?></span>
            </li>-->
            <li data-key="<?=$bd->id ?>">
                <i class="fa fa-user bg-teal"></i>
                <div class="timeline-item">
                    <?php if(CommonHelper::corporationRule('update', $bd->corporation_id)){?>
                    <span class="time text-blue bd-update" style="cursor: pointer">更新</span>
                    <span class="time text-red bd-delete" style="cursor: pointer">删除</span>
                    <?php }?>
                    <span class="time"><i class="fa fa-clock-o"></i><?= date('Y-m-d',$bd->start_time)?></span>

                    <h3 class="timeline-header"><?= $bd->bd_id?User::get_nickname($bd->bd_id):'<span class="not-set">(未设置)</span>'?></h3>

<!--                    <div class="timeline-body">
                        <dl class="dl-horizontal">
                            <dt>11</dt><dd>22</dd>
        
                        </dl>
                    </div>-->

                </div>
            </li>
            <?php }?>
           
            <li>
                <i class="fa fa-clock-o bg-gray"></i>
            </li>
        </ul>
    </div>
    <!-- /.col -->
</div>
<script>
<?php $this->beginBlock('corporation-bd') ?>
    function hide_bdrow(){
        var $l=$('#list-modal .bd-delete').length;
        if($l<=1){
            $('#list-modal .bd-delete').hide();
        }
    }
    hide_bdrow();
    
    $('.bd-timeline').on('click', '.bd-delete', function () {
        var _this = $(this).closest('li');
        $.getJSON('<?= Url::toRoute('corporation-bd-delete') ?>',{id: _this.data('key')},
                function (data) {                   
                    if (data.stat == 'success') {
                        _this.remove(); 
                        hide_bdrow();
                    } 
                    if(data.bd!=null){
                        $("tr[data-key="+data.bd.corporation_id+"] .bd-list").html(data.bd.name);
                    }
                }
        );
    });
    
    $('.bd-timeline').on('click', '.bd-update', function () {

        $('#list-modal .modal-body').html('');

        $.get('<?= Url::toRoute('corporation-bd-update') ?>',{id: $(this).closest('li').data('key')},
                function (data) {
                    $('#list-modal .modal-body').html(data);
                }
        );
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['corporation-bd'], \yii\web\View::POS_END); ?>
