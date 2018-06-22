<?php
//use domain\assets\AppAsset;
use yii\helpers\Html;
use common\widgets\Alert;
use app\models\System;
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

<?php if(System::getValue('system_loginimg')):?> 
 
<?php app\assets\AppAsset::addCss($this, '/css/supersized.css'); ?> 
<?php app\assets\AppAsset::addScript($this, '/js/supersized.3.2.7.min.js'); ?> 
<script>
<?php $this->beginBlock('supersized') ?>
jQuery(function($){

    $.supersized({

        // Functionality
        slide_interval     : 6000,    // Length between transitions
        transition         : 1,    // 0-None, 1-Fade, 2-Slide Top, 3-Slide Right, 4-Slide Bottom, 5-Slide Left, 6-Carousel Right, 7-Carousel Left
        transition_speed   : 3000,    // Speed of transition
        performance        : 1,    // 0-Normal, 1-Hybrid speed/quality, 2-Optimizes image quality, 3-Optimizes transition speed // (Only works for Firefox/IE, not Webkit)

        // Size & Position
        min_width          : 0,    // Min width allowed (in pixels)
        min_height         : 0,    // Min height allowed (in pixels)
        vertical_center    : 1,    // Vertically center background
        horizontal_center  : 1,    // Horizontally center background
        fit_always         : 0,    // Image will never exceed browser width or height (Ignores min. dimensions)
        fit_portrait       : 1,    // Portrait images will not exceed browser height
        fit_landscape      : 0,    // Landscape images will not exceed browser width

        // Components
        slide_links        : 'blank',    // Individual links for each slide (Options: false, 'num', 'name', 'blank')
        slides             : [    // Slideshow Images
                                 {image : '../image/login/1.jpg'},
                                 {image : '../image/login/2.jpg'},
                                 {image : '../image/login/3.jpg'}
                             ]

    });

});
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['supersized'], \yii\web\View::POS_END); ?>

<?php else:?>        
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
<?php endif;?>

        <?php $this->endBody() ?>        
    </body>
</html>
<?php $this->endPage() ?>
