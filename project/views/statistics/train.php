<?php
/* @var $this yii\web\View */

use yii\widgets\Pjax;
use miloschuman\highcharts\Highcharts;
use kartik\daterange\DateRangePicker;
use yii\helpers\Url;
use kartik\widgets\SwitchInput;
use project\models\UserGroup;
use project\models\Group;
use kartik\widgets\Select2;
use daixianceng\echarts\ECharts;

$this->title = '培训统计';
$this->params['breadcrumbs'][] = ['label' => '数据统计', 'url' => ['train']];
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
                <li class="active"><a href="#num_line" data-toggle="tab">统计</a></li>

                <li class="pull-right header">
                    <?= count(UserGroup::get_user_groupid(Yii::$app->user->identity->id))>1?Select2::widget([
                        'name' => 'group',                        
                        'data' => Group::get_user_group(Yii::$app->user->identity->id),
                        'value'=>$group,
                        'options' => [
                            'placeholder' => '项目',
                            'id'=>'group',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '110%'
                        ],
                        'pluginEvents' => [
                            "change" => "function() {var v=$('.range-value').val();var s=$('input[name=sum]:checked').val();var t=$('#total').is(':checked')?1:0;var g=$('#group').length?$('#group').val():''; self.location='".Url::to(['statistics/train'])."?range='+v+'&sum='+s+'&total='+t+'&group='+g;}",
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
                            "apply.daterangepicker" => "function(start,end,label) {var v=$('.range-value').val();var s=$('input[name=sum]:checked').val();var t=$('#total').is(':checked')?1:0;var g=$('#group').length?$('#group').val():''; self.location='".Url::to(['statistics/train'])."?range='+v+'&sum='+s+'&total='+t+'&group='+g;}",
                    ]
                    ]);
                    ?>
                </li>
                
                <li class="pull-right">
                    <?=
                    SwitchInput::widget([
                        'name' => 'total',
//                        'type' => SwitchInput::RADIO,
                        'value'=>$total,
//                        'items' => [
//                            ['label' => '所有', 'value' => 1],
//                    //        ['label' => '角色', 'value' => 2],
//                            ['label' => '个人', 'value' => 3],
//                        ],
                        'options'=>['id'=>'total'],
                        'pluginOptions'=>[
                            'onText'=>'所有',
                            'offText'=>'个人',
                            'onColor' => 'success',
                            'offColor' => 'danger', 
//                            'size' => 'mini'
                        ],
                        'labelOptions' => ['style' => 'font-size: 12px'],
                        'pluginEvents' => [
                        'switchChange.bootstrapSwitch' => "function(e,data) {var v=$('.range-value').val();var s=$('input[name=sum]:checked').val();var t=$('#total').is(':checked')?1:0;var g=$('#group').length?$('#group').val():''; self.location='".Url::to(['statistics/train'])."?range='+v+'&sum='+s+'&total='+t+'&group='+g;}",
                    ]
                    ]);
                    ?>
                </li>
                                
                <li class="pull-right header">
                    <?=
                    SwitchInput::widget([
                        'name' => 'sum',
                        'type' => SwitchInput::RADIO,
                        'value'=>$sum,
                        'items' => [
                            ['label' => '天', 'value' => 1],
                            ['label' => '周', 'value' => 2],
                            ['label' => '月', 'value' => 3],
                        ],
                        'pluginOptions'=>[
                            'onText'=>'是',
                            'offText'=>'否',
                            'onColor' => 'success',
                            'offColor' => 'danger', 
                            'size' => 'mini'
                        ],
                        'labelOptions' => ['style' => 'font-size: 12px'],
                        'pluginEvents' => [
                        'switchChange.bootstrapSwitch' => "function(e,data) {var v=$('.range-value').val();var s=$('input[name=sum]:checked').val();var t=$('#total').is(':checked')?1:0;var g=$('#group').length?$('#group').val():''; self.location='".Url::to(['statistics/train'])."?range='+v+'&sum='+s+'&total='+t+'&group='+g;}",
                    ]
                    ]);
                    ?>
                </li>              
            </ul>
            <div class="tab-content no-padding">
                <div class="tab-pane active row" id="num_line">
                    <?php if($chart==1):?>
                    <section class="col-md-8">
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
                                'text' => '培训咨询次数',
                        ],
                            'xAxis' => [
                                'type' => 'category'
                            ],
                            'yAxis' => [
                                'title' => ['text' => '数量'],
                                'min'=>0,
                                'allowDecimals'=>false,
//                                'stackLabels' => [
//                                    'enabled' => true,
//                                    'style' => [
//                                        'fontWeight' => 'bold',
//                                    ]
//                                ]
                            ],
                            'tooltip' => [
                                'shared' => true,
                                'crosshairs' => true
                            ],
                            'plotOptions' => [
                                'line' => [
                                    'dataLabels' => [
                                        'enabled' => true,
                                    ],
                                    'showInLegend' => true,
                                ]
                            ],
                            'series' => $series['num'],
                    ]
                    ]);
                    ?>
                    </section>
                    <section class="col-md-4">
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
                                'text' => '培训咨询类型',
                            ],
                            'xAxis' => [
                                'type' => 'category'
                            ],
                            'yAxis' => ['title' => ['text' => '数量'],'min'=>0,'allowDecimals'=>false,],
                                
                            'tooltip' => [
                                'shared' => true,
                                'crosshairs' => true
                            ],
                            'plotOptions' => [
                                'column' => [
                                    'cursor' => 'pointer',
                                    'dataLabels' => ['enabled' => true,'style'=>['textShadow'=>false]]
                                ],
                            ],
                            'series' => $series['type'],
                    ]
                    ]);
                    ?>
                    </section>
                    <?php else:?>
                    <section class="col-md-8">
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
                                        'text' => '培训咨询次数',
                                        'left'=>'center',
                                        'top'=>'10px',
                                    ],
                                    'toolbox'=>['right'=>20, 'feature'=>['saveAsImage'=>[],'dataView'=>[]]],
                                    'legend'=>['show'=>true,'bottom'=>'10px'],
                                    'grid'=>['containLabel'=>true,'left'=>'3%','right'=>'3%','top'=>'15%'],
                                    'xAxis' => [                
                                        'type' => 'category',
                                        'boundaryGap'=>true,
                                        'axisLabel'=>['rotate'=>45],
                                        'splitLine'=>['show'=>true]
                                    ],
                                    'yAxis' => [
                                        'type' => 'value',
                                        'name' => '数量',
                                        'min'=>0
                                    ],
                                    'tooltip' => [
                                        'trigger' => 'axis',

                                    ],
                                    'series' => $series['num']
                                ]
                            ]
                            
                        ])
                    ?>
                    </section>
                    <section class="col-md-4">
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
                                        'text' => '培训咨询类型',
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
                                       'type' => 'value','name' => '数量','min'=>0
                                    ],
                                    
                                    'tooltip' => [
                                        'trigger' => 'axis',

                                    ],
                                    'series' => $series['type']
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
