<?php
/* @var $this yii\web\View */
/* @var $model dh\models\Users */

$this->title = '更新用户' . $model->username;
$this->params['breadcrumbs'][] = ['label' => '用户管理', 'url' => ['user-list']];
$this->params['breadcrumbs'][] = ['label' => '用户管理', 'url' => ['user-list']];
?>
<div class="users-update">

    <?=
    $this->render('_form-users', [
        'model' => $model,
    ])
    ?>

</div>
