<?php
/* @var $this yii\web\View */

$this->title = '更新项目';
$this->params['breadcrumbs'][] = ['label' => '系统设置', 'url' => ['group/index']];
$this->params['breadcrumbs'][] = ['label' => '项目管理', 'url' => ['group/index']];
?>
<div class="group-update">

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
