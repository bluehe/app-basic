<?php
/* @var $this yii\web\View */

use yii\widgets\Pjax;
use miloschuman\highcharts\Highcharts;
use kartik\daterange\DateRangePicker;
use yii\helpers\Url;
use yii\web\JsExpression;
use project\models\UserGroup;
use project\models\Group;
use kartik\widgets\Select2;
use daixianceng\echarts\ECharts;
use kartik\widgets\SwitchInput;

$this->title = '健康度统计';
$this->params['breadcrumbs'][] = ['label' => '数据统计', 'url' => ['health']];
$this->params['breadcrumbs'][] = $this->title;
?>
<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <div class="col-md-12">
        <?php Pjax::begin(); ?>
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="active"><a href="#activity_line" data-toggle="tab">健康度统计</a></li>
               

                <li class="pull-right header">
                    <?= count(UserGroup::get_user_groupid(Yii::$app->user->identity->id))>1?Select2::widget([
                        'name' => 'group',                        
                        'data' => Group::get_user_group(Yii::$app->user->identity->id),
                        'value'=>$group,
                        'options' => [
                            'placeholder' => '项目',
                            'id'=>'group',
                        ],
                        'pluginEvents' => [
                            "change" => "function(start,end,label) {var v=$('.range-value').val();var g=$('#group').length?$('#group').val():'';t=$('.total').is(':checked')?1:0;allocate=$('.allocate').is(':checked')?1:0; self.location='".Url::to(['statistics/health'])."?range='+v+'&group='+g+'&allocate='+allocate+'&total='+t;}",
                        ]
                    ]):'';?>
                    
                </li>
 
                <li class="pull-right">
<!--                    <button type="button" class="btn btn-default pull-right" id="daterange-btn"><span><i class="fa fa-calendar"></i> 时间选择</span><i class="fa fa-caret-down"></i></button>-->
                    <?=
                    DateRangePicker::widget([
                        'name' => 'daterange',
                        'useWithAddon' => true,
                        'presetDropdown' => true,
                        'convertFormat' => true,
                        'value' => date('Y-m-d', $start) . '~' . date('Y-m-d', $end),
//                        'startAttribute' => 'from_date',
//                        'endAttribute' => 'to_date',
//                        'startInputOptions' => ['value' => '2017-06-11'],
//                        'endInputOptions' => ['value' => '2017-07-20'],
                        'pluginOptions' => [
                            'timePicker' => false,
                            'locale' => [
                                'format' => 'Y-m-d',
                                'separator' => '~'
                            ],
                            'linkedCalendars' => false,
                        ],
                        'pluginEvents' => [
                            "apply.daterangepicker" => "function(start,end,label) {var v=$('.range-value').val();var g=$('#group').length?$('#group').val():'';t=$('.total').is(':checked')?1:0;allocate=$('.allocate').is(':checked')?1:0; self.location='".Url::to(['statistics/health'])."?range='+v+'&group='+g+'&allocate='+allocate+'&total='+t;}",
                    ]
                    ]);
                    ?>
                </li>
                
                <li class="pull-right activity">
                    <?=
                    SwitchInput::widget([
                        'name' => 'allocate',
                        'value'=>$allocate,
                        'options'=>['class'=>'allocate'],
                        'pluginOptions'=>[
                            'onText'=>'下拨',
                            'offText'=>'全部',
                            'onColor' => 'success',
                            'offColor' => 'danger', 
//                            'size' => 'mini'
                        ],
                        'labelOptions' => ['style' => 'font-size: 12px'],
                        'pluginEvents' => [
                            'switchChange.bootstrapSwitch' => "function(start,end,label) {var v=$('.range-value').val();var g=$('#group').length?$('#group').val():'';t=$('.total').is(':checked')?1:0;allocate=$('.allocate').is(':checked')?1:0; self.location='".Url::to(['statistics/health'])."?range='+v+'&group='+g+'&allocate='+allocate+'&total='+t;}",
                    ]
                    ]);
                    ?>
                </li>
                
                <li class="pull-right activity">
                    <?=
                    SwitchInput::widget([
                        'name' => 'total',
//                        'type' => SwitchInput::RADIO,
                        'value'=>$total,
//                        'items' => [
//                            ['label' => '总和', 'value' => 1],
//                            ['label' => '个人', 'value' => 2],
//                        ],
                        'options'=>['class'=>'total'],
                        'pluginOptions'=>[
                            'onText'=>'全员',
                            'offText'=>'个人',
                            'onColor' => 'success',
                            'offColor' => 'danger', 
//                            'size' => 'mini'
                        ],
                        'labelOptions' => ['style' => 'font-size: 12px'],
                        'pluginEvents' => [
                            'switchChange.bootstrapSwitch' => "function(start,end,label) {var v=$('.range-value').val();var g=$('#group').length?$('#group').val():'';t=$('.total').is(':checked')?1:0;allocate=$('.allocate').is(':checked')?1:0; self.location='".Url::to(['statistics/health'])."?range='+v+'&group='+g+'&allocate='+allocate+'&total='+t;}",
                           
                    ]
                    ]);
                    ?>
                </li>
                
                
                
                
            </ul>
            <div class="tab-content no-padding">
                <div class="tab-pane row active" id="activity_line">
                    <?php if($chart==1):?>
                    <section class="col-md-12">
                    <?=
                    Highcharts::widget([
                        'scripts' => [
                            'highcharts-more',
                            'modules/exporting',
                            'themes/grid-light'
                        ],
                        'options' => [
                            'lang' => [
                                'printChart' => "打印图表",
                                'downloadJPEG' => "下载JPEG 图片",
                                'downloadPDF' => "下载PDF文档",
                                'downloadPNG' => "下载PNG 图片",
                                'downloadSVG' => "下载SVG 矢量图",
                                'exportButtonTitle' => "导出图片"
                            ],
                            'credits' => ['enabled' => true, 'text' => Yii::$app->request->hostInfo, 'href' => Yii::$app->request->hostInfo],
                            'title' => [
                                'text' => '健康度',
                            ],
                            'legend'=>['itemWidth'=>120],
                            'xAxis' => [
                                'type' => 'category'
                            ],
                            'yAxis' => [
                                ['title' => ['text' => '数量'],'min'=>0,'reversedStacks'=>false,'stackLabels'=>['enabled' => true]],
                                ['title' => ['text' => '活跃率'],'labels'=>['format'=>'{value} %'],'opposite'=>true,'min'=>0,'max'=>100]
                            ],
                           
                            'tooltip' => [
                                'shared' => true,
                                'crosshairs' => true
                            ],
                            'plotOptions' => [
                                'spline' => [
                                    'cursor' => 'pointer',
                                    'dataLabels' => [
                                        'enabled' => true,                                      
                                        'format' => '{y:.2f} %',
                                        'shadow'=>false,
                                        'style'=>['textShadow'=>false]
                                    ],
                                    'showInLegend' => true,
                                ],
                                'column' => [
                                    'cursor' => 'pointer',                                   
                                    'stacking'=>'normal',
                                    'dataLabels' => [
                                        'enabled' => true,
                                        'formatter'=>new JsExpression("function () {if(this.y>0){return this.y;}}"),
                                        'style'=>['textShadow'=>false]
                                    ]
                                ],
                   
                            ],
                            'series' => $series['health'],
                    ]
                    ]);
                    ?>
                    </section>
                    <?php else:?>
                    <section class="col-md-12">
                    <?=
                        ECharts::widget([
                            'theme'=>'light',
                            'responsive'=>true,
                            'options' => [
                                'style'=>'height:400px'
                            ],
                            'pluginOptions' => [
                                'option' => [
                                    'title' => [
                                        'text' => '健康度',
                                        'left'=>'center',
                                        'top'=>'10px',
                                    ],
                                    'legend'=>['show'=>true,'bottom'=>'10px'],
                                    'grid'=>['containLabel'=>true,'left'=>'3%','right'=>'3%','top'=>'15%'],
                                    'toolbox'=>['right'=>20, 'feature'=>['saveAsImage'=>[],'dataView'=>[],'magicType'=>['type'=>['stack', 'tiled']]]],                         
                                    'xAxis' => [
                                        'type' => 'category',
                                        'boundaryGap'=>true,                                  
                                        'splitLine'=>['show'=>true]
                                    ],
                                    'yAxis' => [                
                                        ['type' => 'value','name' => '数量','min'=>0],
                                        ['type' => 'value','name' => '活跃率','min'=>0,'max'=>100,'splitLine'=>['show'=>false],'axisLabel'=>['formatter'=>'{value}%']]                                    
                                    ],
                                    
                                    'tooltip' => [
                                        'trigger' => 'axis',

                                    ],
                                    'series' => $series['health']
                                ]
                            ]
                            
                        ])
                    ?> 
                </section>
                <?php endif;?>
                </div>
               
                
            </div>
        </div>
        <?php Pjax::end(); ?>
        <!-- /.box -->
    </div>
</div>
<!-- /.row (main row) -->
<?php
$cssString = '.header .form-group {margin-bottom:0;}';  
$this->registerCss($cssString); 
?>
