<?php
/* @var $this yii\web\View */

$this->title = '更新下拨';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['allocate/allocate-list']];
$this->params['breadcrumbs'][] = ['label' => '下拨管理', 'url' => ['allocate/allocate-list']];
?>
<div class="meal-update">

    <?=
    $this->render('_form-allocate', [
        'model' => $model,
    ])
    ?>

</div>
