<?php
/* @var $this yii\web\View */

$this->title = '添加套餐';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['meal/meal-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="meal-create">

    <?=
    $this->render('_form-meal', [
        'model' => $model,
    ])
    ?>

</div>
