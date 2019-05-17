<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\CorporationAccount;

?>

<div class="row">
    <div class="col-md-12">
        <?php
        $form = ActiveForm::begin(['id' => 'member-form',
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
                    'options' => ['class' => 'form-horizontal'],
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-md-10\">{input}</div>\n<div class=\"col-md-0\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-2 control-label'],
                    ],
        ]);
        ?>
        


        <?= $form->field($model, 'member')->checkboxList(CorporationAccount::get_corporation_member($model->corporation_id), ['itemOptions' => ['labelOptions' => ['class' => 'checkbox-inline']]]) ?>                           
        
        <div class="col-md-6 col-xs-6 text-right">

            <?= Html::submitButton('确定', ['class' => 'btn btn-primary']) ?>

        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
        </div>


        <?php ActiveForm::end(); ?>
    </div>
</div>
