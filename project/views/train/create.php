<?php
/* @var $this yii\web\View */

$this->title = '添加记录';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['train/index']];
$this->params['breadcrumbs'][] = ['label' => '培训咨询', 'url' => ['train/index']];
?>
<div class="create">

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
