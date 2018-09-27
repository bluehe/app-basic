<?php
/* @var $this yii\web\View */

$this->title = '添加条件';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['standard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="standard-create">

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
