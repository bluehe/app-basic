<?php
//use domain\assets\AppAsset;
use yii\helpers\Html;
use common\widgets\Alert;
/* @var $this \yii\web\View */
/* @var $content string */

app\assets\AppAsset::register($this);

//dmstr\web\AdminLteAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?>_<?= Yii::$app->name ?></title>
        <?php $this->head() ?>
    </head>
    <body class="login-page">
        
        <?php $this->beginBody() ?>
        <?= Alert::widget() ?>

        <?= $content ?>
        
<div id="particles" style="width: 100%;height: 100%;position: absolute;left: 0;top: 0;z-index:-1"></div>
<?php app\assets\AppAsset::addScript($this, '/js/jquery.particleground.min.js'); ?> 
<script>
<?php $this->beginBlock('particles') ?>
 $('#particles').particleground({
    dotColor: 'rgba(20,140,230,0.15)',
    lineColor: 'rgba(85,175,230,0.15)'
  });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['particles'], \yii\web\View::POS_END); ?>
        
        <?php $this->endBody() ?>        
    </body>
</html>
<?php $this->endPage() ?>
