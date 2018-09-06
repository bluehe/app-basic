<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model rky\models\Corporation */

$this->title = '添加企业';
$this->params['breadcrumbs'][] = ['label' => '工作中心', 'url' => ['corporation/corporation-list']];
$this->params['breadcrumbs'][] = ['label' => '企业管理', 'url' => ['corporation/corporation-list']];
?>
<div class="corporation-create">

    <?= $this->render('_form-corporation', [
    'model' => $model,
    ]) ?>

</div>
