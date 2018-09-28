<?php

use project\models\Train;


/* @var $this yii\web\View */
/* @var $model rky\models\Visit */

?>
<div class="row">
    <div class="col-md-12">

            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>时间</dt><dd><?= date('Y-m-d H:i', $model->train_start). ' ~ '. (date('Y-m-d', $model->train_start)==date('Y-m-d', $model->train_end)?date('H:i', $model->train_end):date('Y-m-d H:i', $model->train_end)); ?></dd>
                    <dt><?= $model->getAttributeLabel('train_type') ?></dt><dd><?= $model->TrainType ?></dd>
                    <dt><?= $model->getAttributeLabel('train_name') ?></dt><dd><?= $model->train_name ?></dd>  
                    <dt><?= $model->getAttributeLabel('train_address') ?></dt><dd><?= $model->train_address ?></dd>
                    <dt><?= $model->getAttributeLabel('sa') ?></dt><dd><?= $model->get_username($model->id,'sa') ?></dd>
                    <dt><?= $model->getAttributeLabel('other') ?></dt><dd><?= $model->get_username($model->id,'other') ?></dd>
                    
                    <?php if ($model->train_stat == Train::STAT_END ): ?>
                    <dt><?= $model->getAttributeLabel('train_result') ?></dt><dd><?= $model->train_result ?></dd>
                    <dt><?= $model->getAttributeLabel('train_num') ?></dt><dd><?= $model->train_num ?></dd>
                    <?php endif; ?>
                    <dt><?= $model->getAttributeLabel('train_note') ?></dt><dd><?= $model->train_note ?></dd>               
                </dl>
            </div>


    </div>
</div>
