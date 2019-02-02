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
/* @var $model project\models\CloudSubsidy */
/* @var $form yii\widgets\ActiveForm */

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
        
        <?= count(Group::get_user_group(Yii::$app->user->identity->id))>1?$form->field($model, 'group_id')->dropDownList(Group::get_user_group(Yii::$app->user->identity->id), ['prompt' => '']):'' ?>

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
        var v=$('#cloudsubsidy-corporation_id').val();
        if(v){
            $('.field-cloudsubsidy-corporation_name').hide();
        }else{
            $('.field-cloudsubsidy-corporation_name').show();
        }
    }
    
    $(function () {
        change_corporation();       
        $('#cloudsubsidy-corporation_id').change(function(){
            change_corporation();
            var v=$('#cloudsubsidy-corporation_id').val();
            if(v){
                $.getJSON("<?= Url::toRoute('common/corporation-info') ?>", {id: v}, function (data) {$('#cloudsubsidy-subsidy_bd').val(data.bd).trigger('change');});
            }else{
                $('#cloudsubsidy-subsidy_bd').val('').trigger('change');
            }
        });
                    
        //Initialize Select2 Elements
        $(".selectcss2").select2();
        
        $('#cloudsubsidy-group_id').change(function(){
            var v=$('#cloudsubsidy-group_id').val();
            if(v){
                $.getJSON("<?= Url::toRoute(['common/group-corporation']) ?>", {id: v}, function (data) {$("select#cloudsubsidy-corporation_id").html(data.corporation).trigger('change');$("select#cloudsubsidy-subsidy_bd").html(data.bd).trigger('change');});
            }else{
                $("select#cloudsubsidy-corporation_id").html('<option value="">其他</option>');
                $("select#cloudsubsidy-subsidy_bd").html('<option value=""></option>');
            }
        });
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['subsidy-form'], \yii\web\View::POS_END); ?>