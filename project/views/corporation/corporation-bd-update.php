<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\User;
use kartik\widgets\DatePicker;
use project\models\CorporationBd;

/* @var $this yii\web\View */
/* @var $model project\models\Corporation */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
        
        <?php $form = ActiveForm::begin(['id' => 'bd-form',
            'enableAjaxValidation' => true, 
            'enableClientValidation' => true,
            'options' => [ 'class' => 'form-horizontal'],
            'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                        ],
            ]); ?>

        
        <?= $form->field($model, 'bd_id')->dropDownList(User::get_bd(User::STATUS_ACTIVE), ['prompt' => '']) ?>

        <?= $form->field($model, 'start_time')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => '','autocomplete'=>'off'],
                'removeButton' => false,
                'pluginOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'format' => 'yyyy-mm-dd',
                    'startDate'=> CorporationBd::get_pre_date($model->id),
                    'endDate'=> CorporationBd::get_next_date($model->id)
                 ]
                ]) ?>
                     
        <div class="col-md-6 col-xs-6 text-right">

        <?= Html::submitButton('确定', ['class' => 'btn btn-primary']) ?>

        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
        </div>
        <?php ActiveForm::end(); ?>

      
    </div>
</div>