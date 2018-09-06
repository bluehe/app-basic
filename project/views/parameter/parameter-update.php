<?php
/* @var $this yii\web\View */

$this->title = '更新参数';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['parameter/parameter-list']];
$this->params['breadcrumbs'][] = ['label' => '参数管理', 'url' => ['parameter/parameter-list']];
?>
<div class="parameter-update">

    <?=
    $this->render('_form-parameter', [
        'model' => $model,
    ])
    ?>

</div>
