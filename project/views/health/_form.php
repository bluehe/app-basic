<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="row">
    <div class="col-md-12">
        <?php
        $form = ActiveForm::begin(['id' => 'account-form',
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
                    'options' => ['class' => 'form-horizontal'],
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                    ],
        ]);
        ?>
        
        <?= $form->field($model, 'account_name')->textInput(['maxlength' => true,'disabled'=>true]) ?>

        <?= $form->field($model, 'user_name')->textInput(['maxlength' => true]) ?>
                
        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>                            
        
        <div class="col-md-6 col-xs-6 text-right">

            <?= Html::submitButton('确定', ['class' => 'btn btn-primary']) ?>

        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
        </div>


        <?php ActiveForm::end(); ?>
    </div>
</div>
