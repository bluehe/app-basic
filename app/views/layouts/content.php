<?php

use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;

$system = Yii::$app->cache->get('system_info');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php if (isset($this->blocks['content-header'])) { ?>
            <h1><?= $this->blocks['content-header'] ?></h1>
        <?php } else { ?>
            <h1>
                <?php
                if ($this->title !== null) {
                    echo \yii\helpers\Html::encode($this->title);
                } else {
                    echo \yii\helpers\Inflector::camel2words(
                            \yii\helpers\Inflector::id2camel($this->context->module->id)
                    );
                    echo ($this->context->module->id !== \Yii::$app->id) ? '<small>Module</small>' : '';
                }
                ?>
            </h1>
        <?php } ?>

        <?=
        Breadcrumbs::widget(
                [
                    'homeLink' => [
                        'label' => '主页',
                        'url' => Yii::$app->homeUrl,
                        'template' => '<li><i class="fa fa-home"></i> {link}</li>'
                    ],
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ]
        )
        ?>
    </section>

    <section class="content">
        <?= Alert::widget() ?>
        <?= $content ?>
    </section>
</div>

<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Version</b> <?= Yii::$app->version ?>
    </div>
    <strong>Copyright &copy; 2011-<?= date('Y', time()) ?> <a href="<?= Yii::$app->homeUrl ?>">何文斌</a>.</strong> All rights reserved.
    <a href = "http://www.miibeian.gov.cn/" target = "_blank"><?= $system['system_icp']; ?></a>
    <?= $system['system_statcode']; ?>
</footer>
