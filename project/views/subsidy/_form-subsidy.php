<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\Corporation;
use yii\helpers\Url;
use kartik\widgets\DatePicker;
use project\models\User;
use project\models\Parameter;
use project\models\Group;
use project\models\UserGroup;

/* @var $this yii\web\View */
/* @var $model project\models\ClouldSubsidy */
/* @var $form yii\widgets\ActiveForm */

$group=Group::get_user_group(Yii::$app->user->identity->id);
?>

<div class="row">
    <div class="col-md-12">

     <?php
        $form = ActiveForm::begin(['id' => 'subsidy-form',
                    'enableAjaxValidation' => true,
                    'enableClientValidation' => true,
                    'options' => ['class' => 'form-horizontal'],
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                    ],
        ]);
        ?>
        
        <?= count($group)>1?$form->field($model, 'group_id')->dropDownList(Group::get_user_group(Yii::$app->user->identity->id), ['prompt' => '']):'' ?>

        <?= $form->field($model, 'corporation_id')->dropDownList(Corporation::get_corporation_id($model->group_id),['prompt' => '其他', 'class' => 'form-control selectcss2']); ?>                 
                
        <?= $form->field($model, 'corporation_name')->textInput(['maxlength' => true])->label('') ?> 

        <?= $form->field($model, 'subsidy_bd')->dropDownList($model->group_id?User::get_bd(null, UserGroup::get_group_userid($model->group_id)):[],['prompt' => '']) ?>
        
        <?= $form->field($model, 'annual')->dropDownList(Parameter::get_type('allocate_annual'), ['prompt' => '']) ?>

        <?= $form->field($model, 'subsidy_time')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => '','autocomplete'=>'off'],
                    'removeButton' => false,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                    ]) ?>

        <?= $form->field($model, 'subsidy_amount')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'subsidy_note')->textarea(['rows' => 3]) ?>

        <div class="col-md-6 col-xs-6 text-right">
            <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
        </div>
        <?php ActiveForm::end(); ?>

        
    </div>
</div>
<?php project\assets\Select2Asset::register($this);?>
<script>
<?php $this->beginBlock('subsidy-form') ?>
    function change_corporation(){
        var v=$('#clouldsubsidy-corporation_id').val();
        if(v){
            $('.field-clouldsubsidy-corporation_name').hide();
        }else{
            $('.field-clouldsubsidy-corporation_name').show();
        }
    }
    
    $(function () {
        change_corporation();       
        $('#clouldsubsidy-corporation_id').change(function(){
            change_corporation();
            var v=$('#clouldsubsidy-corporation_id').val();
            if(v){
                $.getJSON("<?= Url::toRoute('common/corporation-info') ?>", {id: v}, function (data) {$('#clouldsubsidy-subsidy_bd').val(data.bd).trigger('change');});
            }else{
                $('#clouldsubsidy-subsidy_bd').val('').trigger('change');
            }
        });
                    
        //Initialize Select2 Elements
        $(".selectcss2").select2();
        
        $('#clouldsubsidy-group_id').change(function(){
            var v=$('#clouldsubsidy-group_id').val();
            if(v){
                $.getJSON("<?= Url::toRoute(['group-corporation']) ?>", {id: v}, function (data) {$("select#clouldsubsidy-corporation_id").html(data.corporation).trigger('change');$("select#clouldsubsidy-subsidy_bd").html(data.bd).trigger('change');});
            }else{
                $("select#clouldsubsidy-corporation_id").html('<option value="">其他</option>');
                $("select#clouldsubsidy-subsidy_bd").html('<option value=""></option>');
            }
        });
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['subsidy-form'], \yii\web\View::POS_END); ?>