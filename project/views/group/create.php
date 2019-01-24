<?php
/* @var $this yii\web\View */

$this->title = '添加项目';
$this->params['breadcrumbs'][] = ['label' => '系统设置', 'url' => ['group/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="group-create">

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
