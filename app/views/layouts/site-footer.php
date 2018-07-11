<?php

use yii\bootstrap\Modal;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */
$system = Yii::$app->cache->get('system_info');
?>

<footer class="footer">
    <div class="main-footer">
        <div class="container">

            <div class="tongji row">
                <div class="col-lg-3 col-xs-6 text-center">本站共享 <b><?= Yii::$app->params['statistics']['category0'] ?></b> 个分类</div>
                <div class="col-lg-3 col-xs-6 text-center">本站共享 <b><?= Yii::$app->params['statistics']['website0'] ?></b> 个网址</div>
                <div class="col-lg-3 col-xs-6 text-center">用户收藏 <b><?= Yii::$app->params['statistics']['category1'] ?></b> 个分类</div>
                <div class="col-lg-3 col-xs-6 text-center">用户收藏 <b><?= Yii::$app->params['statistics']['website1'] ?></b> 个网址</div>
            </div>

            <div class="text-center copyright">
                <div>&copy; 2017-<?= date('Y', time()) ?> <a href="<?= Yii::$app->homeUrl ?>"><?= Yii::$app->name ?>.</a>  All rights reserved.</div>
                <div>
                    <a href = "http://www.miibeian.gov.cn/" target = "_blank"><?= $system['system_icp']; ?></a>
                    <?= $system['system_statcode']; ?>
                </div>
            </div>

        </div>
    </div>
</footer>
<div class="CornerButtons">
    <div class="CornerAnimayedFlex" id="suggest">
        <button class="Button CornerButton Button--plain" title="建议反馈" type="button"><i class="fa fa-comments"></i></button>
    </div>
    <div class="CornerAnimayedFlex" id="to-top">
        <button class="Button CornerButton Button--plain" title="回到顶部" type="button"><i class="fa fa-chevron-up"></i></button>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'suggest-modal',
    'header' => '<h4 class="modal-title">建议反馈</h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('footer') ?>
    //网址编辑
    $('#suggest').on('click', function () {
        $.get('<?= Url::toRoute('ajax/suggest') ?>', function (data) {
            $('#suggest-modal .modal-body').html(data);
            $('#suggest-modal').modal('show');
        }
        );
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['footer'], \yii\web\View::POS_END); ?>

