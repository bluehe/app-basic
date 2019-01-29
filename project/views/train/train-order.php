<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\Select2;
use project\models\User;
use project\models\UserGroup;


/* @var $this yii\web\View */
/* @var $model rky\models\Visit */

?>
<div class="row">
    <div class="col-md-12">

           <?php $form = ActiveForm::begin(['id' => 'train-form',
       'enableAjaxValidation' => true, 
                'enableClientValidation' => true,
                        'options' => ['class' => 'form-horizontal'],
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                        ],
            ]); ?>
        
        <div class="form-group">
            <label class="col-md-3 control-label">时间</label>
            <div class="col-md-6" style="padding-top: 7px;"><?= date('Y-m-d H:i', $model->train_start). ' ~ '. (date('Y-m-d', $model->train_start)==date('Y-m-d', $model->train_end)?date('H:i', $model->train_end):date('Y-m-d H:i', $model->trains_end)) ?></div>
            <div class="col-md-3"><div class="help-block"></div></div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?= $model->getAttributeLabel('train_type') ?></label>
            <div class="col-md-6" style="padding-top: 7px;"><?= $model->TrainType ?></div>
            <div class="col-md-3"><div class="help-block"></div></div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?= $model->getAttributeLabel('train_name') ?></label>
            <div class="col-md-6" style="padding-top: 7px;"><?= $model->train_name ?></div>
            <div class="col-md-3"><div class="help-block"></div></div>
        </div>
        
         <div class="form-group">
            <label class="col-md-3 control-label"><?= $model->getAttributeLabel('train_address') ?></label>
            <div class="col-md-6" style="padding-top: 7px;"><?= $model->train_address ?></div>
            <div class="col-md-3"><div class="help-block"></div></div>
        </div>
      
        <?= $form->field($model, 'sa')->widget(Select2::classname(),['data' => $model->group_id?User::get_role('sa',null,UserGroup::get_group_userid($model->group_id)):[],'options' =>['prompt' => '','multiple'=>true,],'maintainOrder' => true]); ?>
        <div class="form-group">
            <label class="col-md-3 control-label"><?= $model->getAttributeLabel('other') ?></label>
            <div class="col-md-6" style="padding-top: 7px;"><?= $model->get_username($model->id,'other') ?></div>
            <div class="col-md-3"><div class="help-block"></div></div>
        </div>       
       
      
        
        <?= $form->field($model, 'train_note')->textarea(['rows' => 3]) ?>

        <div class="col-md-6 col-xs-6 text-right">

           <?= Html::submitButton('确定', ['class' => 'btn btn-primary']) ?>

        </div>
        <div class="col-md-6 col-xs-6 text-left">
            <?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
        </div>
            <?php ActiveForm::end(); ?>

    </div>
</div>
