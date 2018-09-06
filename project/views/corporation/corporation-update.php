<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model rky\models\Corporation */

$this->title = '更新企业: ' . $model->base_company_name;
$this->params['breadcrumbs'][] = ['label' => '工作中心', 'url' => ['corporation/corporation-list']];
$this->params['breadcrumbs'][] = ['label' => '企业管理', 'url' => ['corporation/corporation-list']];
?>
<div class="corporation-update">

    <?= $this->render('_form-corporation', [
    'model' => $model,
        'allocate'=>$allocate,
    ]) ?>

</div>
