<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\Industry;

/* @var $this yii\web\View */
/* @var $model dms\models\RepairOrder */
?>
<div class="row">
    <div class="col-md-12">

        <?php
        $form = ActiveForm::begin(['id' => 'industry-form',
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
                    'options' => ['class' => 'form-horizontal'],
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                    ],
        ]);
        ?>
        
        <?= $form->field($model, 'parent_id')->dropDownList(Industry::get_parents_id($model->id), ['prompt' => '无']) ?>
        
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        
        <?= $form->field($model, 'industry_sort')->textInput() ?>
        
        <div class="col-md-6 col-xs-6 text-right">

            <?= Html::submitButton('确定', ['class' => 'btn btn-primary']) ?>

        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
        </div>


        <?php ActiveForm::end(); ?>


    </div>
</div>