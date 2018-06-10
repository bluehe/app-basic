<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use dh\models\Crontab;
use kartik\datetime\DateTimePicker;

/* @var $this yii\web\View */
/* @var $model dh\models\Crontab */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <?php
            $form = ActiveForm::begin(['id' => 'crontab-form',
                        'options' => ['class' => 'form-horizontal'],
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-md-4\">{input}</div>\n<div class=\"col-md-6\">{error}</div>",
                            'labelOptions' => ['class' => 'col-md-2 control-label'],
                        ],
            ]);
            ?>
            <div class="box-body">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

                <?=
                $form->field($model, 'start_at')->widget(DateTimePicker::classname(), [
                    'type' => 3,
                    'layout' => '{picker}{input}',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true
                    ]
                ]);
                ?>

                <?=
                $form->field($model, 'end_at')->widget(DateTimePicker::classname(), [
                    'type' => 3,
                    'layout' => '{picker}{input}',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true
                    ]
                ]);
                ?>

                <?= $form->field($model, 'interval_time')->dropDownList(Crontab::$List['interval_time'], ['prompt' => '无']) ?>

                <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>

                <?= $form->field($model, 'stat')->radioList(Crontab::$List['stat'], ['itemOptions' => ['labelOptions' => ['class' => 'radio-inline']]]) ?>

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