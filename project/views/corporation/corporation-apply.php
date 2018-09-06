<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\Meal;

/* @var $this yii\web\View */
/* @var $model project\models\Corporation */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
        
        <?php $form = ActiveForm::begin(['id' => 'apply-form',
            'enableAjaxValidation' => true, 
            'enableClientValidation' => true,
            'options' => [ 'class' => 'form-horizontal'],
            'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                        ],
            ]); ?>

        

        <?= $form->field($model, 'intent_set')->dropDownList(Meal::get_meal(), ['prompt' => '']) ?>

        <?= $form->field($model, 'intent_number')->textInput() ?>
                     
        <div class="col-md-6 col-xs-6 text-right">

        <?= Html::submitButton('确定', ['class' => 'btn btn-primary']) ?>

        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
        </div>
        <?php ActiveForm::end(); ?>

      
    </div>
</div>