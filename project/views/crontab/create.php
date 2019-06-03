<?php
/* @var $this yii\web\View */

$this->title = '添加任务';
$this->params['breadcrumbs'][] = ['label' => '系统设置', 'url' => ['crontab/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="crontab-create">

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
