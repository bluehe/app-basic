<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

?>
<div class="password-reset">
    <p>亲爱的会员：您好！</p>

    <p>您正在重置密码，请在验证码输入框中输入： <?= Html::encode($num) ?>，以完成操作。</p>

</div>
