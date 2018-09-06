<?php
/* @var $this yii\web\View */

$this->title = '更新套餐';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['meal/meal-list']];
$this->params['breadcrumbs'][] = ['label' => '套餐管理', 'url' => ['meal/meal-list']];
?>
<div class="meal-update">

    <?=
    $this->render('_form-meal', [
        'model' => $model,
    ])
    ?>

</div>
