<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\User;
use project\models\Meal;
use kartik\widgets\DatePicker;
use project\models\CorporationMeal;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">

        <?php
        $form = ActiveForm::begin(['id' => 'allocate-form',
                    'enableAjaxValidation' => true,
                    'enableClientValidation' => true,
                    'options' => ['class' => 'form-horizontal'],
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                    ],
        ]);
        ?>

        <div class="form-group">
            <label class="col-md-3 control-label"><?= $model->getAttributeLabel('corporation_id') ?></label>
            <div class="col-md-6" style="padding-top: 7px;"><?= $model->corporation->base_company_name ?></div>
            <div class="col-md-3"><div class="help-block"></div></div>
        </div>
        
        <?= $form->field($model, 'huawei_account')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'bd')->dropDownList(User::get_bd(User::STATUS_ACTIVE), ['prompt' => '']) ?>

        <?= $form->field($model, 'meal_id')->dropDownList(Meal::get_meal(), ['prompt' => '其他']) ?>

        <?= $form->field($model, 'number')->textInput() ?>

        <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>

        <?php if(0&&CorporationMeal::get_end_time($model->corporation_id)==$model->end_time):?>
        <?= $form->field($model, 'start_time')->widget(DatePicker::classname(), [
            'options' => ['placeholder' => '','autocomplete'=>'off'],
            'removeButton' => false,
            'pluginOptions' => [
                'autoclose' => true,
                'todayHighlight' => true,
                'format' => 'yyyy-mm-dd',
                'startDate'=> CorporationMeal::get_end_date($model->corporation_id,$model->id)
            ]
            ]) ?>  

        <?php endif;?>


        <div class="col-md-6 col-xs-6 text-right">
            <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
        </div>
        <?php ActiveForm::end(); ?>

        
    </div>
</div>
<script>
<?php $this->beginBlock('form-allocate') ?>

    function change_allocate_set(){
        var v=$('#corporationmeal-meal_id').val();
        if(v){
            $('.field-corporationmeal-amount').hide();
            $('.field-corporationmeal-number').show();
        }else{
            $('.field-corporationmeal-amount').show();
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