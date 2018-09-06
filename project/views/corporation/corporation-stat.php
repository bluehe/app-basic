<?php

use project\models\User;
use project\models\Corporation;
use project\models\CorporationMeal;

/* @var $this yii\web\View */
?>
<div class="row stat-list">
    <div class="col-md-12">
        <!-- The time line -->
        <ul class="timeline">
            <!-- timeline time label -->
<!--            <li class="time-label">
                <span class="bg-blue">11</span>
            </li>-->
            <!-- /.timeline-label -->
            <!-- timeline item -->
            <?php foreach($model as $stat){?>
            <?php switch($stat->stat){
                case Corporation::STAT_CREATED:$color='bg-green';break;
                case Corporation::STAT_FOLLOW:$color='bg-aqua';break;
                case Corporation::STAT_REFUSE:$color='bg-gray';break;
                case Corporation::STAT_APPLY:$color='bg-blue';break;
                case Corporation::STAT_CHECK:$color='bg-yellow';break;
                case Corporation::STAT_ALLOCATE:$color='bg-maroon';break;
                case Corporation::STAT_AGAIN:$color='bg-purple';break;
                default: $color='bg-gray';
                    
            }?>
            <li class="time-label">
                <span class="<?= $color?>"><?= Corporation::$List['stat'][$stat->stat]?></span>
            </li>
            <li>
               
                <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i><?= date('Y-m-d H:i',$stat->created_at)?></span>

                    <h3 class="timeline-header"><?= $stat->user_id?User::get_nickname($stat->user_id):'<span class="not-set">系统</span>'?></h3>

                    <?php if($stat->stat==Corporation::STAT_APPLY){
                        ?>
                    <div class="timeline-body">
                        <dl class="dl-horizontal">
                            <dt><?= $stat->corporation->getAttributeLabel('huawei_account') ?></dt><dd><?= $stat->corporation->huawei_account ?></dd>
                            <dt><?= $stat->corporation->getAttributeLabel('intent_set') ?></dt><dd><?= $stat->corporation->intentSet->name ?></dd>
                            <dt><?= $stat->corporation->getAttributeLabel('intent_number') ?></dt><dd><?= $stat->corporation->intent_number ?></dd>
                            <dt><?= $stat->corporation->getAttributeLabel('intent_amount') ?></dt><dd><?= $stat->corporation->intent_amount ?></dd>
        
                        </dl>
                    </div>
                         <?php
                    }elseif(in_array($stat->stat,[Corporation::STAT_ALLOCATE, Corporation::STAT_AGAIN])&&$allocate= CorporationMeal::get_allocate($stat->corporation_id, $stat->created_at)){
                        ?>
                    <div class="timeline-body">
                        <dl class="dl-horizontal">
                            <dt><?= $allocate->getAttributeLabel('huawei_account') ?></dt><dd><?= $allocate->huawei_account ?></dd>
                            <dt><?= $allocate->getAttributeLabel('bd') ?></dt><dd><?= $allocate->bd?User::get_nickname($allocate->bd):'<span class="not-set">系统</span>' ?></dd>
                            <dt><?= $allocate->getAttributeLabel('meal_id') ?></dt><dd><?= $allocate->meal_id?$allocate->meal->name:'其他' ?></dd>
                            <dt><?= $allocate->getAttributeLabel('number') ?></dt><dd><?= $allocate->number ?></dd>
                            <dt><?= $allocate->getAttributeLabel('amount') ?></dt><dd><?= $allocate->amount ?></dd>
                            <dt><?= $allocate->getAttributeLabel('start_time') ?></dt><dd><?= date('Y-m-d',$allocate->start_time) ?></dd>
                            <dt><?= $allocate->getAttributeLabel('end_time') ?></dt><dd><?= date('Y-m-d',$allocate->end_time) ?></dd>
        
                        </dl>
                    </div>  
                    <?php
                    }?>


                </div>
            </li>
            <?php }?>
           
            <li>
                <i class="fa fa-clock-o bg-gray"></i>
            </li>
        </ul>
    </div>
    <!-- /.col -->
</div>
