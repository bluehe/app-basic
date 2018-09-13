<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model project\models\ClouldSubsidy */

$this->title = '增加补贴';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['subsidy/subsidy-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="clould-subsidy-create">

    <?= $this->render('_form-subsidy', [
        'model' => $model,
    ]) ?>

</div>
