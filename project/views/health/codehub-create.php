<?php
/* @var $this yii\web\View */

$this->title = '添加仓库';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['health/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-create">

    <?=
    $this->render('_form-codehub', [
        'model' => $model,
    ])
    ?>

</div>
