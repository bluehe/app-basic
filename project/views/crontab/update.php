<?php
/* @var $this yii\web\View */

$this->title = '更新任务';
$this->params['breadcrumbs'][] = ['label' => '系统设置', 'url' => ['crontab/index']];
$this->params['breadcrumbs'][] = ['label' => '定时任务', 'url' => ['crontab/index']];
?>
<div class="crontab-update">

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
