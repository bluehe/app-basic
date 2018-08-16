<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use rky\models\User;
use rky\models\Group;

/* @var $this yii\web\View */
/* @var $model dh\models\Users */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <?php
            $form = ActiveForm::begin(['id' => 'users-form',
                        'enableAjaxValidation' => true,
                        'enableClientValidation' => true,
                        'options' => ['class' => 'form-horizontal'],
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-md-4\">{input}</div>\n<div class=\"col-md-6\">{error}</div>",
                            'labelOptions' => ['class' => 'col-md-2 control-label'],
                        ],
            ]);
            ?>
            <div class="box-body">
                <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'nickname')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'tel')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($model, 'user_color')->textInput() ?>
                
                <?= $form->field($model, 'role')->dropDownList(User::$List['role'], ['prompt' => '']) ?>
                                               
                <?= $form->field($model, 'group')->checkboxList(Group::get_group(), ['itemOptions' => ['labelOptions' => ['class' => 'checkbox-inline']]]) ?>

                <?= $form->field($model, 'status')->radioList(User::$List['status'], ['itemOptions' => ['labelOptions' => ['class' => 'radio-inline']]]) ?>

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
<script>
<?php $this->beginBlock('user') ?>
    var color= $('#user-user_color').val();
    color=color?'#'+color:'#000000'
    $('#user-user_color').css('border-right','34px solid '+color);
    
    
$('#user-user_color').colpick({
	layout:'hex',
	submit:0,
	//colorScheme:'dark',
	onChange:function(hsb,hex,rgb,el,bySetColor) {
		$(el).css('border-color','#'+hex);
		// Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
		if(!bySetColor) $(el).val(hex);
	}
}).keyup(function(){

	$(this).colpickSetColor(this.value);

});
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['user'], \yii\web\View::POS_END); ?>