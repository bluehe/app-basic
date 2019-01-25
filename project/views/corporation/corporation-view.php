<?php

use project\models\Parameter;
use project\models\Corporation;
use project\models\CorporationMeal;
use project\models\User;

/* @var $this yii\web\View */
/* @var $model rky\models\Corporation */


?>
<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#base" data-toggle="tab">基础信息</a></li>
            <?php if(in_array($model->stat,[Corporation::STAT_APPLY,Corporation::STAT_CHECK])): ?>
            <li><a href="#apply" data-toggle="tab">申请信息</a></li>
            <?php endif;?>
            <?php if(in_array($model->stat,[Corporation::STAT_ALLOCATE,Corporation::STAT_AGAIN,Corporation::STAT_OVERDUE])&&CorporationMeal::get_allocate($model->id)):?>
            <li><a href="#allocate" data-toggle="tab">下拨信息</a></li>
            <?php endif;?>
            <li><a href="#contact" data-toggle="tab">联系信息</a></li>
            <li><a href="#develop" data-toggle="tab">开发信息</a></li>
            <li class="pull-right header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button></li>
                
        </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="base">
                    <dl class="dl-horizontal">
                    <dt><?= $model->getAttributeLabel('group_id') ?></dt><dd><?= $model->group_id?$model->group->title:$model->group_id ?></dd>
                    <dt><?= $model->getAttributeLabel('base_company_name') ?></dt><dd><?= $model->base_company_name ?></dd>
                    <dt><?= $model->getAttributeLabel('base_bd') ?></dt><dd><?= $model->base_bd?($model->baseBd->nickname?$model->baseBd->nickname:$model->baseBd->username):'' ?></dd>
                    <dt><?= $model->getAttributeLabel('base_industry') ?></dt><dd><?= $model->get_industry($model->id) ?></dd>                  
                    <dt><?= $model->getAttributeLabel('base_company_scale') ?></dt><dd><?= $model->base_company_scale ?></dd>
                    <dt><?= $model->getAttributeLabel('base_registered_capital') ?></dt><dd><?= $model->base_registered_capital>0?floatval($model->base_registered_capital):'' ?></dd>
                    <dt><?= $model->getAttributeLabel('base_registered_time') ?></dt><dd><?= $model->base_registered_time>0?date('Y-m-d',$model->base_registered_time):'' ?></dd>
                    <dt><?= $model->getAttributeLabel('base_main_business') ?></dt><dd><?= $model->base_main_business ?></dd>
                    <dt><?= $model->getAttributeLabel('base_last_income') ?></dt><dd><?= $model->base_last_income>0?floatval($model->base_last_income):'' ?></dd>
                    
                    <dt><?= $model->getAttributeLabel('stat') ?></dt><dd><?= $model->Stat ?></dd>
                    </dl>
                </div>
                <?php if(in_array($model->stat,[Corporation::STAT_APPLY,Corporation::STAT_CHECK])): ?>
                <div class="tab-pane" id="apply">
                <dl class="dl-horizontal">
                    <dt><?= $model->getAttributeLabel('huawei_account') ?></dt><dd><?= $model->huawei_account ?></dd>
                    <dt><?= $model->getAttributeLabel('intent_set') ?></dt><dd><?= $model->intentSet->name ?></dd>
                    <dt><?= $model->getAttributeLabel('intent_number') ?></dt><dd><?= $model->intent_number ?></dd>
                    <dt><?= $model->getAttributeLabel('intent_amount') ?></dt><dd><?= $model->intent_amount ?></dd>         
                   
                </dl>
                </div>
                 <?php endif;?>
                <?php if(in_array($model->stat,[Corporation::STAT_ALLOCATE,Corporation::STAT_AGAIN,Corporation::STAT_OVERDUE])&&$allocate=CorporationMeal::get_allocate($model->id)):?>
                <div class="tab-pane" id="allocate">
                <dl class="dl-horizontal">
                    <dt><?= $allocate->getAttributeLabel('group_id') ?></dt><dd><?= $allocate->group_id?$allocate->group->title:$allocate->group_id ?></dd>
                    <dt><?= $allocate->getAttributeLabel('huawei_account') ?></dt><dd><?= $allocate->huawei_account ?></dd>
                    <dt><?= $allocate->getAttributeLabel('bd') ?></dt><dd><?= $allocate->bd?User::get_nickname($allocate->bd):'<span class="not-set">系统</span>' ?></dd>
                    <dt><?= $allocate->getAttributeLabel('meal_id') ?></dt><dd><?= $allocate->meal_id?$allocate->meal->name:'其他' ?></dd>
                    <dt><?= $allocate->getAttributeLabel('number') ?></dt><dd><?= $allocate->number ?></dd>
                    <dt><?= $allocate->getAttributeLabel('amount') ?></dt><dd><?= $allocate->amount ?></dd>
                    <dt><?= $allocate->getAttributeLabel('start_time') ?></dt><dd><?= date('Y-m-d',$allocate->start_time) ?></dd>
                    <dt><?= $allocate->getAttributeLabel('end_time') ?></dt><dd><?= date('Y-m-d',$allocate->end_time) ?></dd>
                </dl>
                </div>
                <?php endif;?>
                <div class="tab-pane" id="contact">
                <dl class="dl-horizontal">
                    <dt><?= $model->getAttributeLabel('contact_park') ?></dt><dd><?= implode(',', Parameter::get_para_value('contact_park',$model->contact_park)) ?></dd>
                    <dt><?= $model->getAttributeLabel('contact_address') ?></dt><dd><?= $model->contact_address ?></dd>
                    <dt><?= $model->getAttributeLabel('contact_business_name') ?></dt><dd><?= $model->contact_business_name ?></dd>
                    <dt><?= $model->getAttributeLabel('contact_business_job') ?></dt><dd><?= $model->contact_business_job ?></dd>
                    <dt><?= $model->getAttributeLabel('contact_business_tel') ?></dt><dd><?= $model->contact_business_tel ?></dd>
                    <dt><?= $model->getAttributeLabel('contact_technology_name') ?></dt><dd><?= $model->contact_technology_name ?></dd>
                    <dt><?= $model->getAttributeLabel('contact_technology_job') ?></dt><dd><?= $model->contact_technology_job ?></dd>
                    <dt><?= $model->getAttributeLabel('contact_technology_tel') ?></dt><dd><?= $model->contact_technology_tel ?></dd>                                      
                </dl>
            </div>
            <div class="tab-pane" id="develop">
                <dl class="dl-horizontal">
                    <dt><?= $model->getAttributeLabel('develop_scale') ?></dt><dd><?= $model->develop_scale ?></dd>
                    <dt><?= $model->getAttributeLabel('develop_pattern') ?></dt><dd><?= implode(',', Parameter::get_para_value('develop_pattern',$model->develop_pattern)) ?></dd>
                    <dt><?= $model->getAttributeLabel('develop_scenario') ?></dt><dd><?= implode(',', Parameter::get_para_value('develop_scenario',$model->develop_scenario)) ?></dd>
                    <dt><?= $model->getAttributeLabel('develop_science') ?></dt><dd><?= implode(',', Parameter::get_para_value('develop_science',$model->develop_science)) ?></dd>
                    <dt><?= $model->getAttributeLabel('develop_language') ?></dt><dd><?= implode(',', Parameter::get_para_value('develop_language',explode(',',$model->develop_language))) ?></dd>
                    <dt><?= $model->getAttributeLabel('develop_IDE') ?></dt><dd><?= implode(',', Parameter::get_para_value('develop_IDE',$model->develop_IDE)) ?></dd>
                    <dt><?= $model->getAttributeLabel('develop_current_situation') ?></dt><dd><?= $model->develop_current_situation ?></dd>
                    <dt><?= $model->getAttributeLabel('develop_weakness') ?></dt><dd><?= $model->develop_weakness ?></dd>
                </dl>
 
            </div>
            
                <div class="tab-pane" id="visit">
                    <ul class="timeline">
                    <!-- timeline time label -->
                    <li class="time-label">
                        <span class="bg-blue" style="margin-left: 3px"><?= date('Y-m-d',$model->created_at) ?></span>
                    </li>
                    <!-- /.timeline-label -->
                    <!-- timeline item -->
<!--                    <li>
                        <i class="fa bg-teal">何文斌</i>
                        <div class="timeline-item">

                            <span class="time"><i class="fa fa-clock-o"></i> 322</span>
                    <h3 class="timeline-header">2122</h3>

                    <div class="timeline-body">
                        <dl class="dl-horizontal">

                            <dt>122</dt><dd>212</dd>

                        </dl>
                    </div>

                        </div>
                    </li>-->
                    <!-- END timeline item -->

                    <li>
                        <i class="fa fa-clock-o bg-gray"></i>
                    </li>
                    </ul>
                </div>

    </div>
</div>
<?php
$cssString = '.nav-tabs{margin-bottom:15px}.dl-horizontal dt,.dl-horizontal dd {line-height: 35px;}
.timeline>li>.timeline-item {
    border:1px solid #ddd;
}
.timeline:before {
    left:43px;
}
.timeline>li>.fa {left:20px;width:50px;font-size: 14px;}
.timeline>li>.timeline-item{margin-left: 72px;}';  
$this->registerCss($cssString); 
?>
