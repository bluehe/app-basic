<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
        <?= $model?>
    </div>
    <div class="col-md-12 col-xs-12 text-center">
        <?= Html::resetButton('关闭', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?>
    </div>
</div>