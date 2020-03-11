<?php

/* @var $this yii\web\View */

use yii\helpers\Url;
use project\components\CommonHelper;

$this->title = '看板';
?>
<div class="dataview" id="dataview" style="background:#000d4a url(<?= CommonHelper::getImage('/image/dataview/bg.jpg') ?>)  center top">

    <div class="canvas" style="opacity: .2"><iframe frameborder="0" src="<?= CommonHelper::getImage('/js/index.html') ?>" style="width: 100%; height: 100%"></iframe></div>
    <div class="loading" style="display: none;">
        <div class="loadbox"><img src="<?= CommonHelper::getImage('/image/dataview/loading.gif') ?>">页面加载中...</div>
    </div>
    <div class="head" style="background: url(<?= CommonHelper::getImage('/image/dataview/head_bg.png') ?>) no-repeat center center;">
        <h1>中软国际-华为云创新中心运营看板</h1><span id="showTime"></span>
        <div class="weather"></div>
        <div class="fullscreen" id="fullScreen"><span class="glyphicon glyphicon-fullscreen" title="全屏"></span></div>

    </div>
    <div class="mainbox">
        <ul class="clearfix">
            <li>
                <div class="boxall" style="height: 3.2rem">
                    <div class="alltitle">企业健康度</div>
                    <div class="allnav" id="echart1"></div>
                    <div class="boxfoot"></div>
                </div>
                <div class="boxall" style="height: 3.2rem">
                    <div class="alltitle">企业活跃度</div>
                    <div class="allnav" id="echart2"></div>
                    <div class="boxfoot"></div>
                </div>

                <div class="boxall" style="height: 3.2rem">
                    <div class="alltitle">使用项目</div>
                    <div class="allnav" id="echart3"></div>
                    <div class="boxfoot"></div>
                </div>
            </li>
            <li>
                <div class="bar">
                    <div class="barbox">
                        <ul class="clearfix">
                            <li class="pulll_left counter" id="cloud_num">0</li>
                            <li class="pulll_left counter" id="cloud_amount">0</li>
                        </ul>
                    </div>
                    <div class="barbox2">
                        <ul class="clearfix">
                            <li class="pulll_left">累计补贴企业（家）</li>
                            <li class="pulll_left">累计补贴金额（万元）</li>
                        </ul>
                    </div>
                </div>
                <div class="map">
                    <div class="map1"><img src="<?= CommonHelper::getImage('/image/dataview/lbx.png') ?>"></div>
                    <div class="map2"><img src="<?= CommonHelper::getImage('/image/dataview/jt.png') ?>"></div>
                    <div class="map3"><img src="<?= CommonHelper::getImage('/image/dataview/map.png') ?>"></div>
                    <div class="map4" id="map1"></div>
                </div>
                <div class="boxall" style="height:3.2rem">
                    <div class="alltitle">企业下拨</div>
                    <div class="allnav" id="echart7"></div>
                    <div class="boxfoot"></div>
                </div>
            </li>
            <li>
                <div class="boxall" style="height: 3.2rem">
                    <div class="alltitle">企业项目成员数</div>
                    <div class="allnav" id="echart4"></div>
                    <div class="boxfoot"></div>
                </div>
                <div class="boxall" style="height:3.2rem">
                    <div class="alltitle">企业补贴</div>
                    <div class="allnav" id="echart5"></div>
                    <div class="boxfoot"></div>
                </div>
                <div class="boxall" style="height: 3.2rem">
                    <div class="alltitle">下拨套餐占比</div>
                    <div class="allnav" id="echart6"></div>
                    <div class="boxfoot"></div>
                </div>

            </li>
        </ul>
    </div>
    <div class="back"></div>
</div>
<?php
$this->registerCssFile('/css/kanban.css', ['depends' => ['project\assets\KanBanAsset']]);
$this->registerJsFile(CommonHelper::getImage('/js/jquery.js'), ['depends' => ['project\assets\KanBanAsset']]);
$this->registerJsFile(CommonHelper::getImage('/js/echarts.min.js'), ['depends' => ['project\assets\KanBanAsset']]);
$this->registerJsFile(CommonHelper::getImage('/js/china.js'), ['depends' => ['project\assets\KanBanAsset']]);
$this->registerJsFile('/js/kanban.js', ['depends' => ['project\assets\KanBanAsset']]);
?>
<script>
    <?php $this->beginBlock('dataview') ?>

    $(function() {

        $url_cloud = '<?= Url::toRoute(['data-view/cloud']) ?>';
        $url_health = '<?= Url::toRoute(['data-view/health']) ?>';
        $url_activity = '<?= Url::toRoute(['data-view/activity']) ?>';
        $url_item = '<?= Url::toRoute(['data-view/item']) ?>';
        $url_user = '<?= Url::toRoute(['data-view/user']) ?>';
        $url_subsidy = '<?= Url::toRoute(['data-view/subsidy']) ?>';
        $url_meal = '<?= Url::toRoute(['data-view/meal']) ?>';
        $url_allocate = '<?= Url::toRoute(['data-view/allocate']) ?>';
        $url_map = '<?= Url::toRoute(['data-view/map']) ?>';

        //企业健康度
        var echart1 = echarts.init(document.getElementById("echart1"));
        echart1.setOption(bar());

        //企业月活跃率
        var echart2 = echarts.init(document.getElementById("echart2"));
        echart2.setOption(bar());

        //使用项目
        var echart3 = echarts.init(document.getElementById("echart3"));
        echart3.setOption(pie());

        //企业项目成员数
        var echart4 = echarts.init(document.getElementById("echart4"));
        echart4.setOption(bar());

        //企业补贴
        var echart5 = echarts.init(document.getElementById("echart5"));
        echart5.setOption(line());

        //下拨套餐
        var echart6 = echarts.init(document.getElementById("echart6"));
        echart6.setOption(pie());

        //企业下拨
        var echart7 = echarts.init(document.getElementById("echart7"));
        echart7.setOption(bar2());

        //地图
        var map = echarts.init(document.getElementById("map1"));
        map.setOption(bmap());

        window.addEventListener("resize", function() {
            echart1.resize();
            echart2.resize();
            echart3.resize();
            echart4.resize();
            echart5.resize();
            echart6.resize();
            echart7.resize();
            map.resize();
        })
        data_flush();

        function data_flush() {
            //补贴数据
            $.get($url_cloud, function(result) {
                $('#cloud_num').html(result.data.cloud_num);
                $('#cloud_amount').html(result.data.cloud_amount);
            }, 'json');
            $.get($url_health, function(result) {
                echart1.setOption({
                    series: result.data
                });
            }, 'json');
            $.get($url_activity, function(result) {
                echart2.setOption({
                    series: result.data
                });
            }, 'json');
            $.get($url_item, function(result) {
                echart3.setOption({
                    series: result.data
                });
            }, 'json');
            $.get($url_user, function(result) {
                echart4.setOption({
                    series: result.data
                });
            }, 'json');
            $.get($url_subsidy, function(result) {
                echart5.setOption({
                    series: result.data
                });
            }, 'json');
            $.get($url_meal, function(result) {
                echart6.setOption({
                    series: result.data
                });
            }, 'json');
            $.get($url_allocate, function(result) {
                result.data[0].label.formatter = function(params) {
                    if (params.value[1] > 0) {
                        return params.value[1];
                    } else {
                        return ''
                    }
                };
                result.data[1].label.formatter = function(params) {
                    if (params.value[1] > 0) {
                        return params.value[1];
                    } else {
                        return ''
                    }
                };
                echart7.setOption({
                    series: result.data
                });
            }, 'json');
            $.get($url_map, function(result) {
                map.setOption({
                    series: result.data
                });
            }, 'json');
        }

        setInterval(data_flush, 1000 * 60);

    })



    <?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['dataview'], \yii\web\View::POS_END); ?>