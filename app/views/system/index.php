<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '系统信息';
$this->params['breadcrumbs'][] = ['label' => '系统设置', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <?php
            $form = ActiveForm::begin(['id' => 'system-info-form',
                        'options' => [ 'class' => 'form-horizontal'],
//                        'fieldConfig' => [
//                            'template' => "{label}\n<div class=\"col-md-4\">{input}</div>\n<div class=\"col-md-6\">{error}</div>",
//                            'labelOptions' => ['class' => 'col-md-2 control-label'],
//                        ],
            ]);
            ?>
            <div class="box-body">
                <?php
//                foreach ($settings as $index => $one) {
//                    if ($one['type'] == 'text') {
//                        echo $form->field($one, "[$index]value")->textInput()->label($one->tag);
//                    } elseif ($one['type'] == 'textarea') {
//                        echo $form->field($one, "[$index]value")->textarea()->label($one->tag);
//                    }
//                }
                ?>
                <?php
                foreach ($model as $one) {
                    if ($one['type'] == 'text') {
                        ?>
                        <div class="form-group"><label class="col-md-2 control-label" for="<?= $one['code'] ?>"><?= $one['tag'] ?></label><div class="col-md-4"><input type="text" id="<?= $one['code'] ?>" class="form-control" name="System[<?= $one['code'] ?>]" value="<?= $one['value'] ?>"></div></div>
                        <?php
                    } elseif ($one['type'] == 'textarea') {
                        ?>
                        <div class="form-group"><label class="col-md-2 control-label" for="<?= $one['code'] ?>"><?= $one['tag'] ?></label><div class="col-md-4"><textarea id="<?= $one['code'] ?>" class="form-control" name="System[<?= $one['code'] ?>]" rows="<?= $one['store_range'] ?>"><?= $one['value'] ?></textarea></div></div>
                        <?php
                    }
                }
                ?>

            </div>
            <div class="box-footer">
                <div class="col-md-1 col-md-offset-2 col-xs-6 text-right">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary', 'name' => 'update-button']) ?>

                </div>
                <div class="col-md-1 col-xs-6 text-left">
                    <?= Html::resetButton('重置', ['class' => 'btn btn-default', 'name' => 'update-button']) ?>
                </div>

            </div>



            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
