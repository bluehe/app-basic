<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\components\Aliyunoss;

?>
<div class="container">

    <div class="row content">
        
        <section class="col-lg-9">

            <div class="row">
                <div class="website plate-<?= Yii::$app->params['plate'] ?> col-lg-12">
                    <?php foreach ($cates as $cate) { ?>
                        <div class="category" data-id="<?= $cate['id'] ?>">
                            <div class="website-header">
                                <b><?= $cate['title'] ?></b>
                                <span class="header-icon pull-right"><i class="fa fa-star-o category-collect" title="收藏分类"></i></span>
                            </div>
                            <div class="website-content list-group">

                                <?php foreach ($cate['website'] as $website) { ?>
                                    <div class="list-group-item" data-id="<?= $website['id'] ?>">
                                        <?php
                                        if($img=Aliyunoss::getfav($website['host'])){
                                            echo Html::img('@web/image/default_e.png', ['class' => 'lazyload', 'data-original' => $img]);
                                        }else{
                                            echo Html::img('@web/image/default_ico.png', ['class' => 'lazyload', 'data-original' => Url::to(Yii::$app->params['img_url'] . '/api/getfav?url=' . $website['host'])]);
                                        }
                                        ?>
                                       
                                        <a class="clickurl" target="_blank" href="<?= $website['url'] ?>" title="<?= $website['title'] ?>"><?= $website['title'] ?></a>
                                        <div class="content-icon index-icon pull-right" >
                                            <i class="fa fa-gavel website-report" title="举报"></i>
                                            <i class="fa fa-heart-o website-collect" title="收藏网址"></i>
                                        </div>
                                    </div>
                                <?php } ?>

                            </div>
                        </div>
                    <?php } ?>

                </div>

            </div>

        </section>
        <section class="col-lg-3 hidden-xs">
            <?=
            $this->render('left.php')
            ?>

        </section>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'collect-modal',
    'header' => '<h4 class="modal-title"></h4>',
    'options' => [
        'tabindex' => false
    ],
]);
Modal::end();
?>
<script>
<?php $this->beginBlock('collect') ?>
    $('.category-collect').on('click', function () {
        $.get('<?= Url::toRoute('ajax/category-collect') ?>', {id: $(this).parents('.category').data('id')},
                function (data) {
                    $('#collect-modal .modal-title').html('收藏分类');
                    $('#collect-modal .modal-body').html(data);
                    $('#collect-modal').modal('show');
                }
        );
    });

    $('.website-collect').on('click', function () {
        $.get('<?= Url::toRoute('ajax/website-collect') ?>', {id: $(this).parents('.list-group-item').data('id')},
                function (data) {

                    $('#collect-modal .modal-title').html('收藏网址');
                    $('#collect-modal .modal-body').html(data);
                    $('#collect-modal').modal('show');
                }
        );
    });

    $('.website-report').on('click', function () {
        $.get('<?= Url::toRoute('ajax/website-report') ?>', {id: $(this).parents('.list-group-item').data('id')},
                function (data) {

                    $('#collect-modal .modal-title').html('举报');
                    $('#collect-modal .modal-body').html(data);
                    $('#collect-modal').modal('show');
                }
        );
    });

<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['collect'], \yii\web\View::POS_END); ?>