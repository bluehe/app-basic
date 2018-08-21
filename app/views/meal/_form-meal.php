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

                <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'content')->widget(\yii\redactor\widgets\Redactor::className()) ?>

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
<script>
<?php $this->beginBlock('group') ?>
    var color= $('#group-groupcolor').val();
    color=color?'#'+color:'#000000'
    $('#group-groupcolor').css('border-right','34px solid '+color);
    
    
$('#group-groupcolor').colpick({
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
<?php $this->registerJs($this->blocks['group'], \yii\web\View::POS_END); ?>