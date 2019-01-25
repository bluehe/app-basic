<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\Meal;
use kartik\widgets\DatePicker;
use project\models\CorporationMeal;
use project\models\Parameter;

/* @var $this yii\web\View */
/* @var $model project\models\Corporation */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
        
        <?php $form = ActiveForm::begin(['id' => 'allocate-form',
            'enableAjaxValidation' => true, 
            'enableClientValidation' => true,
            'options' => [ 'class' => 'form-horizontal'],
            'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                        ],
            ]); ?>
       
        <?= $form->field($model, 'huawei_account')->textInput(['maxlength' => true]) ?>
        
        <?= $form->field($model, 'annual')->dropDownList(Parameter::get_type('allocate_annual'), ['prompt' => '']) ?>
        
        <?= $form->field($model, 'meal_id')->dropDownList(Meal::get_meal(Meal::STAT_ACTIVE,$model->group_id), ['prompt' => '其他']) ?>

        <?= $form->field($model, 'number')->textInput() ?>

        <?= $form->field($model, 'devcloud_count')->textInput(['maxlength' => true]) ?>
        
        <?= $form->field($model, 'devcloud_amount')->textInput(['maxlength' => true]) ?>
        
        <?= $form->field($model, 'cloud_amount')->textInput(['maxlength' => true]) ?>
        
        <?= $form->field($model, 'start_time')->widget(DatePicker::classname(), [
            'options' => ['placeholder' => '','autocomplete'=>'off'],
            'removeButton' => false,
            'pluginOptions' => [
                'autoclose' => true,
                'todayHighlight' => true,
                'format' => 'yyyy-mm-dd',
                'startDate'=> CorporationMeal::get_last_start_date($model->corporation_id)
            ],
            'pluginEvents'=>[
                'hide'=>"function(event){var startTime = $('#corporationmeal-start_time').val();var endTime = $('#corporationmeal-end_time').val();if(startTime){ var date=new Date(startTime);date.setFullYear(date.getFullYear()+1); date.setDate(date.getDate()-1); var m=date.getMonth() + 1; var d=date.getDate(); $('#corporationmeal-end_time').val(date.getFullYear() + '-' + (String(m).length < 2?'0':'')+m + '-' +(String(d).length < 2?'0':'')+d );$('#corporationmeal-end_time-kvdate').kvDatepicker('setStartDate',new Date(startTime));}}"
            ]
            ]) ?>
        
        <?= $form->field($model, 'end_time')->widget(DatePicker::classname(), [
            'options' => ['placeholder' => '','autocomplete'=>'off'],
            'removeButton' => false,
            'pluginOptions' => [
                'autoclose' => true,
                'todayHighlight' => true,
                'format' => 'yyyy-mm-dd',
                'startDate'=> $model->start_time              
            ]           
            ]) ?>  
                     
        <div class="col-md-6 col-xs-6 text-right">

        <?= Html::submitButton('确定', ['class' => 'btn btn-primary']) ?>

        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
        </div>
        <?php ActiveForm::end(); ?>

      
    </div>
</div>
<script>
<?php $this->beginBlock('form-allocate') ?>
    
    function change_allocate_set(){
        var v=$('#corporationmeal-meal_id').val();
        if(v){
            $('.field-corporationmeal-devcloud_count').hide();
            $('.field-corporationmeal-devcloud_amount').hide();
            $('.field-corporationmeal-cloud_amount').hide();
            $('.field-corporationmeal-number').show();
        }else{
            $('.field-corporationmeal-devcloud_count').show();
            $('.field-corporationmeal-devcloud_amount').show();
            $('.field-corporationmeal-cloud_amount').show();
            $('.field-corporationmeal-number').hide();
        }
    }
    
    $(function () {
        change_allocate_set();
                
        $('#corporationmeal-meal_id').change(function(){
            change_allocate_set();
        });
        
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['form-allocate'], \yii\web\View::POS_END); ?>