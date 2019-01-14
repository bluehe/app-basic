<?php
/* @var $this yii\web\View */

use yii\widgets\Pjax;
use miloschuman\highcharts\Highcharts;
use kartik\daterange\DateRangePicker;
use yii\helpers\Url;
use kartik\widgets\SwitchInput;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use project\models\Parameter;

$this->title = '企业统计';
$this->params['breadcrumbs'][] = ['label' => '数据统计', 'url' => ['corporation']];
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
                <li class="active"><a href="#amount_line" data-toggle="tab">下拨消耗</a></li>
                <li><a href="#industry_line" data-toggle="tab">行业规模</a></li>
                
                <li class="pull-right headers header">
                    <?= Select2::widget([
                        'name' => 'annual',                        
                        'data' => Parameter::get_type('allocate_annual'),
                        'value'=>$annual,
                        'options' => [
                            'placeholder' => '下拨年度',
                            'id'=>'annual',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '110%'
                        ],
                        'pluginEvents' => [
                            "change" => "function() {var v=$('.range-value').val();var s=$('input[name=sum]:checked').val();var a=$('#annual').val();self.location='".Url::to(['statistics/corporation'])."?range='+v+'&sum='+s+'&annual='+a;}",
                        ]
                    ]);?>
                    
                </li>
                <li class="pull-right headers">
                    <?=
                    DateRangePicker::widget([
                        'name' => 'daterange',
                        'useWithAddon' => true,
                        'presetDropdown' => true,
                        'convertFormat' => true,
                        'value' => date('Y-m-d', $start) . '~' . date('Y-m-d', $end),
                        'pluginOptions' => [
                            'timePicker' => false,
                            'locale' => [
                                'format' => 'Y-m-d',
                                'separator' => '~'
                            ],
                            'linkedCalendars' => false,
                        ],
                        'pluginEvents' => [
                            "apply.daterangepicker" => "function(start,end,label) {var v=$('.range-value').val();var s=$('input[name=sum]:checked').val();var a=$('#annual').val(); self.location='".Url::to(['statistics/corporation'])."?range='+v+'&sum='+s+'&annual='+a;}",
                    ]
                    ]);
                    ?>
                </li>
                
                <li class="pull-right headers header">
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
                        'switchChange.bootstrapSwitch' => "function(e,data) {var v=$('.range-value').val();s=$('input[name=sum]:checked').val();var a=$('#annual').val(); self.location='".Url::to(['statistics/corporation'])."?range='+v+'&sum='+s+'&annual='+a;}",
                    ]
                    ]);
                    ?>
                </li>
                
                

            </ul>
            <div class="tab-content no-padding">
                <div class="tab-pane row active" id="amount_line">
               
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
                                'text' => '下拨消耗统计',
                            ],
                            'xAxis' => [
                                'type' => 'category'
                            ],
                            'yAxis' => [
                                ['title' => ['text' => '金额（万元）'],'min'=>0,],
                                ['title' => ['text' => '数量'],'opposite'=>true,'min'=>0]
                            ],
                            'tooltip' => [
                                'shared' => true,
                                'crosshairs' => true
                            ],
                            'plotOptions' => [
                                'area' => [
                                    'dataLabels' => [
                                        'enabled' => true,
//                                        'format' => '{y:.2f}',
                                        'formatter'=>new JsExpression("function () {if(this.y>0){return this.y;}}"),
                                        'shadow'=>false,
                                        
                                        'style'=>['textShadow'=>false]
                                    ],
                                    'showInLegend' => true,
                                    'marker'=>[
                                        'enabled'=>false,
					'symbol'=>'circle',
					'radius'=>2,
                                    ],
                                ],
                                'areaspline' => [
                                    'dataLabels' => [
                                        'enabled' => true,
//                                        'format' => '{y:.2f}',
                                        'formatter'=>new JsExpression("function () {if(this.y>0){return this.y;}}"),
                                        'shadow'=>false,
                                        
                                        'style'=>['textShadow'=>false]
                                    ],
                                    'showInLegend' => true,
                                    'marker'=>[
                                        'enabled'=>false,
					'symbol'=>'circle',
					'radius'=>2,
                                    ],
                                ],
                                'column' => [
                                    'cursor' => 'pointer',
                                    'dataLabels' => [
                                        'enabled' => true,
                                        'formatter'=>new JsExpression("function () {if(this.y>0){return this.y;}}"),
                                        'style'=>['textShadow'=>false]
                                    ]
                                ],
                                'line' => [
                                    'cursor' => 'pointer',
                                    'dataLabels' => [
                                        'enabled' => true,                                      
                                        'formatter'=>new JsExpression("function () {if(this.y>0){return this.y;}}"),
                                        'shadow'=>false,
                                        'style'=>['textShadow'=>false]
                                    ],
                                    'showInLegend' => true,
                                ],
                                'spline' => [
                                    'cursor' => 'pointer',
                                    'dataLabels' => [
                                        'enabled' => true,                                      
                                        'formatter'=>new JsExpression("function () {if(this.y>0){return this.y;}}"),
                                        'shadow'=>false,
                                        'style'=>['textShadow'=>false]
                                    ],
                                    'showInLegend' => true,
                                ],
                            ],
                            'series' => $series['amount'],
                    ]
                    ]);
                    ?>
                     </section>
                    <section class="col-md-6">
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
                                    'text' => '企业下拨金额',
                                ],
                                'legend'=>['itemWidth'=>80,'width'=>240],
                                'tooltip' => [
                                    'formatter' => new JsExpression("function () {return '<b>' + this.point.name + '</b><br/>' +this.series.name + ' : ' + this.y;
                                }")
                                ],
                                'plotOptions' => [
                                    'pie' => [
                                        'allowPointSelect' => true,
                                        'cursor' => 'pointer',
                                        'dataLabels' => [
                                            'enabled' => true,                                        
                                            'format' => '{point.y}家 , {point.percentage:.1f} %',
                                            
                                        ],
                                        'showInLegend' => true,
                                    ]
                                ],
                                'series' => $series['allocate_num'],
                            ]
                        ]);
                        ?>
                        </section>
                    <section class="col-md-6">
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
                                'text' => '下拨金额',
                            ],
                            'xAxis' => [
                                'type' => 'category'
                            ],
                            'yAxis' => [
                                'title' => ['text' => '金额（万元）'],'min'=>0,'reversedStacks'=>false,
                                'stackLabels'=>['enabled' => true],
                            ],
                           
                            'tooltip' => [
                                'shared' => true,
                                'crosshairs' => true
                            ],
                            'plotOptions' => [
                                'bar' => [
                                    'cursor' => 'pointer',
                                    'stacking'=>'normal',
                                    'dataLabels' => [
                                        'enabled' => false,
                                        'formatter'=>new JsExpression("function () {if(this.y>0){return this.y;}}"),
                                        'style'=>['textShadow'=>false]
                                    ]
                                ],
                   
                            ],
                            'series' => $series['allocate_bd'],
                    ]
                    ]);
                    ?>
                    </section>
                    
                </div>
                <div class="tab-pane row" id="industry_line">
                    <section class="col-md-6">
                    <?=
                    Highcharts::widget([
                        'scripts' => [
                            'highcharts-more',
                            'modules/exporting',
                            'modules/drilldown',
                            'themes/grid-light'
                        ],
                        'options' => [
                            'chart'=>['type'=>'pie'],
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
                                'text' => '企业行业分布',
                            ],
                           'subtitle'=>['text'=>'单击每个分类获得二级分类信息'],
                            'tooltip' => [
                                    'formatter' => new JsExpression("function () {return '<b>' + this .point.name+ ' : ' + this . y + '</b><br/>';
                                }")
                            ],
                            'legend'=>['itemWidth'=>100,'width'=>400],
                            'plotOptions' => [
                                'pie' => [
                                        'allowPointSelect' => true,
                                        'cursor' => 'pointer',
                                        'dataLabels' => [
                                            'enabled' => true,
                                            'format' => '<b>{point.name}</b>: {point.y}  , {point.percentage:.1f} %',
                                        ],
                                        'showInLegend' => true,
                                ]
                            ],
                            'series' => $series['industry'],
                            'drilldown'=>$drilldown['industry'],
                        ]
                    ]);
                    ?>
                    </section>
                    <section class="col-md-3">
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
                                    'text' => '企业注册资金',
                                ],
                                'legend'=>['itemWidth'=>120,'width'=>240],
                                'tooltip' => [
                                    'formatter' => new JsExpression("function () {return '<b>' + this .point.name + '</b><br/>' +
                                            this . series . name + ' : ' + this . y;
                                }")
                                ],
                                'plotOptions' => [
                                    'pie' => [
                                        'allowPointSelect' => true,
                                        'cursor' => 'pointer',
                                        'dataLabels' => [
                                            'enabled' => true,
                                        
                                            'format' => '<b>{point.percentage:.1f} %',
                                            
                                        ],
                                        'showInLegend' => true,
                                    ]
                                ],
                                'series' => $series['capital'],
                            ]
                        ]);
                        ?>
                    </section>
                    <section class="col-md-3">
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
                                    'text' => '企业研发规模',
                                ],
                                'legend'=>['itemWidth'=>100,'width'=>200],
                                'tooltip' => [
                                    'formatter' => new JsExpression("function () {return '<b>' + this .point.name + '</b><br/>' +
                                            this . series . name + ' : ' + this . y;
                                }")
                                ],
                                'plotOptions' => [
                                    'pie' => [
                                        'allowPointSelect' => true,
                                        'cursor' => 'pointer',
                                        'dataLabels' => [
                                            'enabled' => true,
                                            'format' => '{point.percentage:.1f} %',
                                        ],
                                        'showInLegend' => true,
                                    ]
                                ],
                                'series' => $series['scale'],
                            ]
                        ]);
                        ?>
                    </section>
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
<script>
<?php $this->beginBlock('corporation-show') ?>
$('.nav a').on('shown.bs.tab',function (e) {
    
    var $id=$(this).attr('href');
    if($id=='#industry_line'){
        $('.nav .headers').hide();
    }else{
        $('.nav .headers').show();
    }
    $($id).find('[data-highcharts-chart]').each(function(){
        $('#'+$(this).attr('id')).highcharts().reflow();
    })
         
})
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['corporation-show'], \yii\web\View::POS_END); ?>
