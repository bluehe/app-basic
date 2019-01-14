<?php
/* @var $this yii\web\View */

use yii\widgets\Pjax;
use miloschuman\highcharts\Highcharts;
use kartik\daterange\DateRangePicker;
use yii\helpers\Url;
use kartik\widgets\SwitchInput;
use yii\web\JsExpression;

$this->title = '活跃统计';
$this->params['breadcrumbs'][] = ['label' => '数据统计', 'url' => ['activity']];
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
                <li class="active"><a href="#activity_line" data-toggle="tab">活跃统计</a></li>
                <li><a href="#item_pie" data-toggle="tab">活跃项目</a></li>

 
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
                            "apply.daterangepicker" => "function(start,end,label) {var v=$('.range-value').val();s=$('.sum').is(':checked')?1:0;g=$('.group').is(':checked')?1:0; self.location='".Url::to(['statistics/activity'])."?range='+v+'&sum='+s+'&group='+g;}",
                    ]
                    ]);
                    ?>
                </li>
                
                <li class="pull-right activity">
                    <?=
                    SwitchInput::widget([
                        'name' => 'sum',
                        'value'=>$sum,
                        'options'=>['class'=>'sum'],
                        'pluginOptions'=>[
                            'onText'=>'次',
                            'offText'=>'月',
                            'onColor' => 'success',
                            'offColor' => 'danger',       
                        ],
                        'pluginEvents' => [
                            'switchChange.bootstrapSwitch' => "function(e,data) {var v=$('.range-value').val();s=$('.sum').is(':checked')?1:0;g=$('.group').is(':checked')?1:0; self.location='".Url::to(['statistics/activity'])."?range='+v+'&sum='+s+'&group='+g;}",
                    ]
                    ]);
                    ?>
                </li>
                
                <li class="pull-right activity">
                    <?=
                    SwitchInput::widget([
                        'name' => 'group',
//                        'type' => SwitchInput::RADIO,
                        'value'=>$group,
//                        'items' => [
//                            ['label' => '总和', 'value' => 1],
//                            ['label' => '个人', 'value' => 2],
//                        ],
                        'options'=>['class'=>'group'],
                        'pluginOptions'=>[
                            'onText'=>'全员',
                            'offText'=>'个人',
                            'onColor' => 'success',
                            'offColor' => 'danger', 
//                            'size' => 'mini'
                        ],
                        'labelOptions' => ['style' => 'font-size: 12px'],
                        'pluginEvents' => [
                            'switchChange.bootstrapSwitch' => "function(e,data) {var v=$('.range-value').val();s=$('.sum').is(':checked')?1:0;g=$('.group').is(':checked')?1:0; self.location='".Url::to(['statistics/activity'])."?range='+v+'&sum='+s+'&group='+g;}",
                    ]
                    ]);
                    ?>
                </li>
            </ul>
            <div class="tab-content no-padding">
                <div class="tab-pane active" id="activity_line">
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
                                'text' => '活跃企业趋势统计',
                            ],
                            'legend'=>['itemWidth'=>120],
                            'xAxis' => [
                                'type' => 'category'
                            ],
                            'yAxis' => [['title' => ['text' => '数量'],'min'=>0],
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
                                    'dataLabels' => ['enabled' => true,'style'=>['textShadow'=>false]]
                                ],
                            ],
                            'series' => $series['activity'],
                    ]
                    ]);
                    ?>
                </div>
                <div class="tab-pane row" id="item_pie">
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
                                    'text' => '活跃项目',
                                ],
                                'legend'=>['itemWidth'=>100],
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
                                            'format' => '{point.name} : {point.y}家 , {point.percentage:.1f} %',
                                        ],
                                        'showInLegend' => true,
                                    ]
                                ],
                                'series' => $series['item'],
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
<?php $this->beginBlock('activity-show') ?>
$('.nav a').on('shown.bs.tab',function (e) {
    
    var $id=$(this).attr('href');
    if($id=='#item_pie'){
        $('.nav .activity').hide();
    }else{
        $('.nav .activity').show();
    }
    $($id).find('[data-highcharts-chart]').each(function(){
        $('#'+$(this).attr('id')).highcharts().reflow();
    })
         
})
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['activity-show'], \yii\web\View::POS_END); ?>
