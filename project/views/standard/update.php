<?php
/* @var $this yii\web\View */

$this->title = '更新条件';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['standard/index']];
$this->params['breadcrumbs'][] = ['label' => '数据标准', 'url' => ['standard/index']];
?>
<div class="standard-update">

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
