<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use project\models\User;
use project\models\Industry;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;
use project\models\Parameter;
use project\models\Corporation;
use project\models\Meal;
use project\models\CorporationMeal;
use project\models\Group;
use yii\helpers\Url;
use project\models\UserGroup;

/* @var $this yii\web\View */
/* @var $model rky\models\Corporation */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12"> 
            <?php $form = ActiveForm::begin(['id' => 'corporation-form',
                'enableAjaxValidation' => true, 
                'enableClientValidation' => true,
            'options' => [ 'class' => 'form-horizontal'],
            'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                        ],
            ]); ?>
 
                
                 <ul class="nav nav-tabs">
                 <li class="active"><a href="#base" data-toggle="tab">基础信息</a></li>
                 <li><a href="#contact" data-toggle="tab">联系信息</a></li>
                 <li><a href="#develop" data-toggle="tab">开发信息</a></li>
                 <li class="pull-right header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button></li>
                
                 </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="base">
                    
                <?= count(Group::get_user_group(Yii::$app->user->identity->id))>1?$form->field($model, 'group_id')->dropDownList(Group::get_user_group(Yii::$app->user->identity->id), ['prompt' => '']):'' ?>
                    
                <?= $form->field($model, 'base_company_name')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'base_bd')->dropDownList($model->group_id?User::get_bd(User::STATUS_ACTIVE,UserGroup::get_group_userid($model->group_id)):[], ['prompt' => '']) ?>

                <?= $form->field($model, 'base_industry')->widget(Select2::classname(), ['data' => Industry::get_industry_id(),'options' => ['prompt' => '','multiple'=>false],'showToggleAll'=>false]); ?>

                <?= $form->field($model, 'base_company_scale')->textInput() ?>

                <?= $form->field($model, 'base_registered_capital')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'base_registered_time')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => '','autocomplete'=>'off'],
                'removeButton' => false,
                'pluginOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'format' => 'yyyy-mm-dd'
                 ]
                ]) ?>
              
                <?= $form->field($model, 'base_main_business')->textarea(['rows' => 3]) ?>

                <?= $form->field($model, 'base_last_income')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'stat')->dropDownList(Corporation::get_stat_list($model->stat), ['prompt' => '','disabled'=>true]) ?>

                <div class="stat_c stat_intent"> 
                <?= $form->field($model, 'huawei_account')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'intent_set')->dropDownList(Meal::get_meal(Meal::STAT_ACTIVE,$model->group_id), ['prompt' => '']) ?>

                <?= $form->field($model, 'intent_number')->textInput() ?>
                </div>

                <?php if($allocate){?>
                <?= $form->field($allocate, 'huawei_account')->textInput(['maxlength' => true]) ?>
                    
                <?= $form->field($allocate, 'bd')->dropDownList($allocate->group_id?User::get_bd(User::STATUS_ACTIVE,UserGroup::get_group_userid($allocate->group_id)):[], ['prompt' => '']) ?>
                    
                <?= $form->field($allocate, 'annual')->dropDownList(Parameter::get_type('allocate_annual'), ['prompt' => '']) ?>

                <?= $form->field($allocate, 'meal_id')->dropDownList(Meal::get_meal(Meal::STAT_ACTIVE,$model->group_id), ['prompt' => '其他']) ?>

                <?= $form->field($allocate, 'number')->textInput() ?>

                <?= $form->field($allocate, 'devcloud_count')->textInput(['maxlength' => true]) ?>
        
                <?= $form->field($allocate, 'devcloud_amount')->textInput(['maxlength' => true]) ?>

                <?= $form->field($allocate, 'cloud_amount')->textInput(['maxlength' => true]) ?>

                <?= $form->field($allocate, 'start_time')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => '','autocomplete'=>'off'],
                    'removeButton' => false,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                        'startDate'=> CorporationMeal::get_pre_date($allocate->id),
                        'endDate'=> CorporationMeal::get_next_date($allocate->id)
                    ],
                    'pluginEvents'=>[
                     'hide'=>"function(event){var startTime = $('#corporationmeal-start_time').val();var endTime = $('#corporationmeal-end_time').val(); if(startTime){var date=new Date(startTime);date.setFullYear(date.getFullYear()+1); date.setDate(date.getDate()-1); var m=date.getMonth() + 1; var d=date.getDate(); $('#corporationmeal-end_time').val(date.getFullYear() + '-' + (String(m).length < 2?'0':'')+m + '-' +(String(d).length < 2?'0':'')+d );$('#corporationmeal-end_time-kvdate').kvDatepicker('setStartDate',new Date(startTime));}}"
                ]
                    ]) ?>  
                    
                <?= $form->field($allocate, 'end_time')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => '','autocomplete'=>'off'],
                    'removeButton' => false,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                        'startDate'=> $allocate->start_time              
                    ]           
                    ]) ?>  
                <script>
                <?php $this->beginBlock('form-allocate') ?>

                    function change_allocate_set(){
                        var v=$('#corporationmeal-meal_id').val();
                        if(v){
                            $('.field-corporationmeal-devcloud_count').hide();
                            $('.field-corporationmeal-devcloud_amount').hide();
                            $('.field-corporationmeal-cloud_amount').hide();
                            $('.field-corporationmeal-number').show();
                        }else{
                            $('.field-corporationmeal-devcloud_count').show();
                            $('.field-corporationmeal-devcloud_amount').show();
                            $('.field-corporationmeal-cloud_amount').show();
                            $('.field-corporationmeal-number').hide();
                        }
                    }

                    $(function () {
                        change_allocate_set();

                        $('#corporationmeal-meal_id').change(function(){
                            change_allocate_set();
                        });

                    });
                <?php $this->endBlock() ?>
                </script>
                <?php $this->registerJs($this->blocks['form-allocate'], \yii\web\View::POS_END); ?>
                <?php }?>
  
            
            </div>
            <div class="tab-pane" id="contact">
            
            <?= $form->field($model, 'contact_park')->dropDownList(Parameter::get_type('contact_park'), ['prompt' => '']) ?>

            <?= $form->field($model, 'contact_address')->textInput(['maxlength' => true]) ?>
                
            <?= $form->field($model, 'contact_business_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'contact_business_job')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'contact_business_tel')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'contact_technology_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'contact_technology_job')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'contact_technology_tel')->textInput(['maxlength' => true]) ?>        
            </div>
            <div class="tab-pane" id="develop">
            <?= $form->field($model, 'develop_scale')->textInput() ?>

            <?= $form->field($model, 'develop_pattern')->dropDownList(Parameter::get_type('develop_pattern'), ['prompt' => '']) ?>

            <?= $form->field($model, 'develop_scenario')->dropDownList(Parameter::get_type('develop_scenario'), ['prompt' => '']) ?>
                
            <?= $form->field($model, 'develop_science')->dropDownList(Parameter::get_type('develop_science'), ['prompt' => '']) ?>

            <?= $form->field($model, 'develop_language')->checkboxList(Parameter::get_type('develop_language'), ['prompt' => '','multiple'=>true]) ?>

            <?= $form->field($model, 'develop_IDE')->dropDownList(Parameter::get_type('develop_IDE'), ['prompt' => '']) ?>

            <?= $form->field($model, 'develop_current_situation')->textarea(['rows' => 3]) ?>

            <?= $form->field($model, 'develop_weakness')->textarea(['rows' => 3]) ?>
            </div>

               
          
            <div class="box-footer">
               <div class="col-md-6 col-xs-6 text-right"><?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?></div>
               <div class="col-md-6 col-xs-6 text-left"><?= Html::resetButton('取消', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]) ?></div>

            </div>
        </div>
                   
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$cssString = 'div.required label:before {content: "*";color: red;}.nav-tabs{margin-bottom:15px}';  
$this->registerCss($cssString); 
?>
<script>
<?php $this->beginBlock('form-corporation') ?>
    
    function change_stat(){
        var s=$('#corporation-stat').val();
        if(s==<?= Corporation::STAT_APPLY?>||s==<?= Corporation::STAT_CHECK?>){
            $('.stat_c').hide();
            $('.stat_intent').show();

        }else if(s==<?= Corporation::STAT_ALLOCATE?>){
            $('.stat_c').hide();
            $('.stat_allocate').show();
        }else{
            $('.stat_c').hide();
        }
    }
    
    $(function () {
        
        change_stat();
        
        $('#corporation-stat').change(function(){
           change_stat();
        });
        
        $('#corporation-group_id').change(function(){
            var v=$('#corporation-group_id').val();
            if(v){
                $.get("<?= Url::toRoute(['group-bd','bd_id'=>$model->base_bd]) ?>", {id: v}, function (data) {$("select#corporation-base_bd").html(data);});
            }else{
                $("select#corporation-base_bd").html('');
            }
        });
    });
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['form-corporation'], \yii\web\View::POS_END); ?>