<?php
/* @var $this yii\web\View */

use yii\widgets\Pjax;
use miloschuman\highcharts\Highcharts;
use kartik\daterange\DateRangePicker;
use yii\helpers\Url;
use daixianceng\echarts\ECharts;

$this->title = '用户统计';
$this->params['breadcrumbs'][] = ['label' => '数据统计', 'url' => ['user']];
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
                <li class="active"><a href="#signup_line" data-toggle="tab">用户统计</a></li>


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
                            "apply.daterangepicker" => "function(start,end,label) {var v=$('.range-value').val(); self.location='".Url::to(['statistics/user'])."?range='+v;}",
                    ]
                    ]);
                    ?>
                </li>
            </ul>
            <div class="tab-content no-padding">
                <div class="tab-pane active" id="signup_line">
                    <?php if($chart==1):?>
                    
                    
                    <?=
                    Highcharts::widget([
                        'scripts' => [
                            'highcharts-more',
//                            'modules/exporting',
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
                                'text' => '用户趋势统计',
                            ],
                            'xAxis' => [
                                'type' => 'category'
                            ],
                            'yAxis' => [
                                'title' => ['text' => '数量'],
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
                            'series' => $series['day'],
                        ]
                    ]);
                    ?>
                    <?php else:?>
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
                                        'text' => '用户趋势统计',
                                        'left'=>'center',
                                        'top'=>'10px',
                                    ],
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
                                    ],
                                    'tooltip' => [
                                        'trigger' => 'axis',

                                    ],
                                    'series' => $series['day']
                                ]
                            ]
                            
                        ])
                    ?>
                    <?php endif;?>
                </div>

            </div>
        </div>
        <?php Pjax::end(); ?>
        <!-- /.box -->
    </div>
</div>
<!-- /.row (main row) -->
