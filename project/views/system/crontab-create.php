<?php
/* @var $this yii\web\View */
/* @var $model dh\models\Crontab */

$this->title = '添加任务';
$this->params['breadcrumbs'][] = ['label' => '系统设置', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '计划任务', 'url' => ['crontab']];
?>
<div class="crontab-create">

    <?=
    $this->render('_form-crontab', [
        'model' => $model,
    ])
    ?>

</div>