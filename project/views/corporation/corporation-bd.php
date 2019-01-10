<?php

use project\models\User;
//use yii\widgets\DetailView;

/* @var $this yii\web\View */
?>
<div class="row bd-list">
    <div class="col-md-12">
        <!-- The time line -->
        <ul class="timeline">
            <!-- timeline time label -->
<!--            <li class="time-label">
                <span class="bg-blue">11</span>
            </li>-->
            <!-- /.timeline-label -->
            <!-- timeline item -->
            <?php foreach($model as $bd){?>
<!--            <li class="time-label">
                <span class="bg-blue"><?= date('Y-m-d',$bd->start_time)?></span>
            </li>-->
            <li>
                <i class="fa fa-user bg-teal"></i>
                <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i><?= date('Y-m-d',$bd->start_time)?></span>

                    <h3 class="timeline-header"><?= $bd->bd_id?User::get_nickname($bd->bd_id):'<span class="not-set">(未设置)</span>'?></h3>

<!--                    <div class="timeline-body">
                        <dl class="dl-horizontal">
                            <dt>11</dt><dd>22</dd>
        
                        </dl>
                    </div>-->

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
