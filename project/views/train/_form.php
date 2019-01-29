<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\Corporation;
use project\models\User;
use kartik\widgets\Select2;
use project\models\Train;
use yii\helpers\Url;
use kartik\widgets\DateTimePicker;
use project\models\Group;
use project\models\UserGroup;

/* @var $this yii\web\View */
/* @var $model rky\models\Visit */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
        
        <?php $form = ActiveForm::begin(['id' => 'train-form',
            'enableAjaxValidation' => true, 
            'enableClientValidation' => true,
            'options' => ['class' => 'form-horizontal'],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                'labelOptions' => ['class' => 'col-md-3 control-label'],
            ],
        ]); ?>

        <?= count(Group::get_user_group(Yii::$app->user->identity->id))>1&&$model->train_stat!= Train::STAT_ORDER?$form->field($model, 'group_id')->dropDownList(Group::get_user_group(Yii::$app->user->identity->id), ['prompt' => '']):'' ?>
        
        <?= $form->field($model, 'train_start')->widget(DateTimePicker::classname(),['options' => ['placeholder' => '','autocomplete'=>'off'],
            'removeButton' => false,
            'pluginOptions' => [
                'autoclose' => true,
                'todayHighlight' => true,
                'format' => 'yyyy-mm-dd hh:ii',
                'minuteStep'=>15,
             ],
            'pluginEvents'=>[
                'hide'=>"function(event){var startTime = $('#train-train_start').val();var endTime = $('#train-train_end').val();if(!endTime){ $('#train-train_end').val(startTime)};$('#train-train_end-datetime').datetimepicker('setStartDate',startTime);}"
            ]
            ]) ?>

        <?= $form->field($model, 'train_end')->widget(DateTimePicker::classname(),['options' => ['placeholder' => '','autocomplete'=>'off'],
            'removeButton' => false,
            'pluginOptions' => [
                'autoclose' => true,
                'todayHighlight' => true,
                'format' => 'yyyy-mm-dd hh:ii',
                'minuteStep'=>15,
                'startView'=>'day',
            ],
            'pluginEvents'=>[
                'hide'=>"function(event){var endTime = $('#train-train_end').val();$('#train-train_start-datetime').datetimepicker('setEndDate',endTime);}"
            ]
            ]) ?>
        
        <?= $form->field($model, 'train_type')->dropDownList(Train::$List['train_type'],['prompt' => '']) ?>
           
                
        <?= $form->field($model, 'corporation_id')->dropDownList(Corporation::get_corporation_id($model->group_id),['prompt' => '其他', 'class' => 'form-control selectcss2']); ?>                 
                
        <?= $form->field($model, 'train_name')->textInput(['maxlength' => true])->label('') ?>  
        
        <?= $form->field($model, 'train_address')->textInput(['maxlength' => true]) ?> 
                       
        <?= $form->field($model, 'sa')->widget(Select2::classname(),['data' => $model->group_id?User::get_role('sa',null,UserGroup::get_group_userid($model->group_id)):[],'options' =>['prompt' => '','multiple'=>true,],'maintainOrder' => true]); ?>
                
        <?= $form->field($model, 'other')->widget(Select2::classname(),['data' => $model->group_id?User::get_role('other',null,UserGroup::get_group_userid($model->group_id)):[],'options' =>['prompt' => '','multiple'=>true,],'maintainOrder' => true]); ?>
        
        <?= $form->field($model, 'other_people')->textInput(['maxlength' => true])->label('') ?>  
        

        <?php if ($model->scenario == 'trainEnd'): ?>       
                           
        <?= $form->field($model, 'train_result')->textarea(['rows' => 3]) ?>
        
        <?= $form->field($model, 'train_num')->textInput() ?> 
   
        <?php endif; ?>
        
        <?= $form->field($model, 'train_note')->textarea(['rows' => 3]) ?>
                     
        <div class="col-md-6 col-xs-6 text-right">

        <?= Html::submitButton($model->isNewRecord ? '创建' : '确定', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
        </div>
        <?php ActiveForm::end(); ?>

      
    </div>
</div>
<?php project\assets\Select2Asset::register($this);?>
<script>
<?php $this->beginBlock('train') ?>
    $(function () {
        
        if($('#train-corporation_id').val()){
            $('.field-train-train_name').hide();
        }else{
            $('.field-train-train_name').show();
        }
            
        //Initialize Select2 Elements
        $(".selectcss2").select2();
        
        $('#train-corporation_id').change(function(){
            var v=$('#train-corporation_id').val();
            if(v){
                $('.field-train-train_name').hide();
                $.getJSON("<?= Url::toRoute('common/corporation-info') ?>", {id: v}, function (data) {$('#train-other').val(data.bd).trigger('change');$('#train-train_address').val(data.address);});
            }else{
                $('.field-train-train_name').show();
                $('#train-train_address').val('');
            }
        });
        $('#train-group_id').change(function(){
            var v=$('#train-group_id').val();
            if(v){
                $.getJSON("<?= Url::toRoute(['common/group-corporation']) ?>", {id: v}, function (data) {$("select#train-corporation_id").html(data.corporation).trigger('change');$("select#train-sa").html(data.sa).trigger('change');$("select#train-other").html(data.other).trigger('change');});
            }else{
                $("select#train-corporation_id").html('<option value="">其他</option>');
                $("select#train-sa").html('<option value=""></option>');
                $("select#train-other").html('<option value=""></option>');
            }
        });
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['train'], \yii\web\View::POS_END); ?>