<?php
/* @var $this yii\web\View */

$this->title = '添加字段';
$this->params['breadcrumbs'][] = ['label' => '数据中心', 'url' => ['field/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="field-create">

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
