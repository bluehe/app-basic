<?php
/* @var $this yii\web\View */

$this->title = '更新字段';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['field/field-list']];
$this->params['breadcrumbs'][] = ['label' => '字段管理', 'url' => ['field/field-list']];
?>
<div class="field-update">

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
