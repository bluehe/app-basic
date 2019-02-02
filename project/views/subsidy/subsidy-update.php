<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model project\models\CloudSubsidy */

$this->title = '修改补贴';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['subsidy/subsidy-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cloud-subsidy-update">

    <?= $this->render('_form-subsidy', [
        'model' => $model,
    ]) ?>

</div>
