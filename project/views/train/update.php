<?php
/* @var $this yii\web\View */

$this->title = '更新记录';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['train/index']];
$this->params['breadcrumbs'][] = ['label' => '培训咨询', 'url' => ['train/index']];
?>
<div class="update">

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
