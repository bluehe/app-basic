<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\ActivityChange;

/* @var $this yii\web\View */
/* @var $model dms\models\RepairOrder */
?>
<div class="row">
    <div class="col-md-12">

        <?php
        $form = ActiveForm::begin(['id' => 'column-form',
                    'options' => ['class' => 'form-horizontal'],
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-md-10\">{input}</div>\n<div class=\"col-md-0\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-2 control-label'],
                    ],
        ]);
        ?>
        
        <?= $form->field($model, 'content')->checkboxList(array_merge(ActivityChange::$List['column_usual'],ActivityChange::$List['column_activity'],ActivityChange::$List['column_data']), ['itemOptions' => ['labelOptions' => ['class' => 'checkbox-inline']]]) ?>
     

        <div class="col-md-6 col-xs-6 text-right">

            <?= Html::submitButton('确定', ['class' => 'btn btn-primary']) ?>

        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
        </div>


        <?php ActiveForm::end(); ?>


    </div>
</div>