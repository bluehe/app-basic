<?php
/* @var $this yii\web\View */

$this->title = '添加参数';
$this->params['breadcrumbs'][] = ['label' => '业务中心', 'url' => ['parameter/parameter-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="parameter-create">

    <?=
    $this->render('_form-parameter', [
        'model' => $model,
    ])
    ?>

</div>
