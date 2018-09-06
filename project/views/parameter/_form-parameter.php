<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
            <?php
            $form = ActiveForm::begin(['id' => 'parameter-form',
                        'enableAjaxValidation' => true,
                        'enableClientValidation' => true,
                        'options' => ['class' => 'form-horizontal'],
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                            'labelOptions' => ['class' => 'col-md-3 control-label'],
                        ],
            ]);
            ?>
          
                <?= $form->field($model, 'type')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'description')->textarea(['row'=>3]) ?>
             
           
                <div class="col-md-6 col-xs-6 text-right">

                    <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

                </div>
                <div class="col-md-6 col-xs-6 text-left">
                    <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
                </div>

          
            <?php ActiveForm::end(); ?>

      
    </div>
</div>
