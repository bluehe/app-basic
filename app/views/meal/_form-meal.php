<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Meal;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <?php
            $form = ActiveForm::begin(['id' => 'meal-form',
                        'options' => ['class' => 'form-horizontal'],
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-md-4\">{input}</div>\n<div class=\"col-md-6\">{error}</div>",
                            'labelOptions' => ['class' => 'col-md-2 control-label'],
                        ],
            ]);
            ?>
            <div class="box-body">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'region')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'amount')->textInput(['maxlength' => true,'disabled'=> Meal::get_meal_exist($model->id)]) ?>

                <?= $form->field($model, 'content')->widget(\yii\redactor\widgets\Redactor::className(),['clientOptions'=>['lang'=>'zh_cn','maxHeight'=>'400px']]) ?>

                <?= $form->field($model, 'order_sort')->textInput() ?>

                <?= $form->field($model, 'stat')->radioList(Meal::$List['stat'], ['itemOptions' => ['labelOptions' => ['class' => 'radio-inline']]]) ?>
                
               
            </div>
            <div class="box-footer">
                <div class="col-md-1 col-lg-offset-2 col-xs-6 text-right">

                    <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

                </div>
                <div class="col-md-1 col-xs-6 text-left">
                    <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
                </div>

            </div>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
