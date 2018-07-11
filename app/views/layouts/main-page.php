<?php
//use domain\assets\AppAsset;
use yii\helpers\Html;
use app\models\System;
/* @var $this \yii\web\View */
/* @var $content string */

app\assets\PageAsset::register($this);

$directoryAsset = Yii::$app->assetManager->getPublishedUrl('app/web');
?>
<?php $this->beginPage() ?>
 <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
        <head>
            <meta charset="<?= Yii::$app->charset ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <?= Html::csrfMetaTags() ?>
            <title><?= $this->title ? Html::encode($this->title) . ' - ' : '' ?><?= System::getValue('system_title') ? System::getValue('system_title') : Yii::$app->name ?></title>
            <meta name="keywords" content="<?= System::getValue('system_keywords') ?>">
            <meta name="description" content="<?= System::getValue('system_desc') ?>">
            <?php $this->head() ?>
        </head>
        <body class="skin-<?= Yii::$app->params['skin'] ?>">
            <?php $this->beginBody() ?>

            <?=
            $this->render(
                    'site-header.php', ['directoryAsset' => $directoryAsset]
            )
            ?>
            <?=
            $this->render(
                    'site-content.php', ['content' => $content, 'directoryAsset' => $directoryAsset]
            )
            ?>
            <?=
            $this->render(
                    'site-footer.php', ['directoryAsset' => $directoryAsset]
            )
            ?>


            <?php $this->endBody() ?>
        </body>
    </html>
<?php $this->endPage() ?>
