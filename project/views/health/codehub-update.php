<?php
/* @var $this yii\web\View */

$this->title = '更新仓库';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['health/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-update">

    <?=
    $this->render('_form-codehub', [
        'model' => $model,
    ])
    ?>

</div>
