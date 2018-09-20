<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\Field;
use project\models\ActivityData;

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
        
        <?= $form->field($model, 'parent')->dropDownList($model->get_parents_id($model->id), ['prompt' => '无']) ?>
        
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
       
        <?= $form->field($model, 'code')->dropDownList(ActivityData::get_code(), ['prompt' => '无']) ?>
        
        <?= $form->field($model, 'type')->radioList(Field::$List['type'], ['itemOptions' => ['labelOptions' => ['class' => 'radio-inline']]]) ?>


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