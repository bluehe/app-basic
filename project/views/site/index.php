<?php

/* @var $this yii\web\View */

use project\assets\AppAsset;
use project\components\CommonHelper;

project\assets\DataViewAsset::register($this);

$this->title = '首页';
?>
<div class="dataview" id="dataview" style="background:#000d4a url(<?= CommonHelper::getImage('/image/dataview/bg.jpg') ?>)  center top">

    <div class="canvas" style="opacity: .2"><iframe frameborder="0" src="/js/index.html" style="width: 100%; height: 100%"></iframe></div>
    <div class="loading" style="display: none;">
        <div class="loadbox"><img src="<?= CommonHelper::getImage('/image/dataview/loading.gif') ?>">页面加载中...</div>
    </div>
    <div class="head" style="background: url(<?= CommonHelper::getImage('/image/dataview/head_bg.png') ?>) no-repeat center center;">
        <h1>中软国际-华为云创新中心运营看板</h1>
        <div class="weather"><span id="showTime"></span></div>
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
                    <div class="alltitle">活跃项目</div>
                    <div class="allnav" id="echart6"></div>
                    <div class="boxfoot"></div>
                </div>
            </li>
            <li>
                <div class="bar">
                    <div class="barbox">
                        <ul class="clearfix">
                            <li class="pulll_left counter"><?= $allocate_num ?></li>
                            <li class="pulll_left counter"><?= $allocate_amount ?></li>
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
                    <div class="map4" id="map_1"></div>
                </div>
            </li>
            <li>
                <div class="boxall" style="height: 3.2rem">
                    <div class="alltitle">企业用户数</div>
                    <div class="allnav" id="echart3"></div>
                    <div class="boxfoot"></div>
                </div>
                <div class="boxall" style="height:3.4rem">
                    <div class="alltitle">企业补贴</div>
                    <div class="allnav" id="echart4"></div>
                    <div class="boxfoot"></div>
                </div>
                <div class="boxall" style="height: 3rem">
                    <div class="alltitle">下拨套餐</div>
                    <div class="allnav" id="echart5"></div>
                    <div class="boxfoot"></div>
                </div>

            </li>
        </ul>
    </div>
    <div class="back"></div>
</div>
<script>
    <?php $this->beginBlock('dataview') ?>
    $(window).on('load', function() {
        $(".loading").fadeOut()
    });
    $(document).ready(function() {
        var whei = $(window).width();
        $("html").css({
            fontSize: whei / 20
        });
        $(window).resize(function() {
            var whei = $(window).width();
            $("html").css({
                fontSize: whei / 20
            })
        });

    });

    var t = null;
    t = setTimeout(time, 1000);

    function time() {
        clearTimeout(t);
        dt = new Date();
        var y = dt.getFullYear();
        var mt = dt.getMonth() + 1;
        var day = dt.getDate();
        var h = dt.getHours();
        var m = dt.getMinutes();
        var s = dt.getSeconds();
        document.getElementById("showTime").innerHTML = y + "年" + mt + "月" + day + "日 " + h + "时" + m + "分" + s + "秒";
        t = setTimeout(time, 1000);
    }

    $(function() {

        $("#fullScreen").on("click", function() {
            var isFull = !!(document.webkitIsFullScreen || document.mozFullScreen ||
                document.msFullscreenElement || document.fullscreenElement
            ); //!document.webkitIsFullScreen都为true。因此用!!
            if (isFull == false) {
                //全屏
                fullScreen();

            } else {
                //退出全屏
                exitFullscreen();

            }

        })
    })

    //fullScreen()和exitScreen()有多种实现方式，此处只使用了其中一种
    //全屏
    function fullScreen() {
        var element = document.getElementById("dataview");
        if (element.requestFullscreen) {
            element.requestFullscreen();
        } else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if (element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen();
        }
    }

    //退出全屏 
    function exitFullscreen() {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    }

    //监听window是否全屏，并进行相应的操作,支持esc键退出
    window.addEventListener("resize", function() {
        //全屏
        var isFull = !!(document.webkitIsFullScreen || document.mozFullScreen ||
            document.msFullscreenElement || document.fullscreenElement
        ); //!document.webkitIsFullScreen都为true。因此用!!
        if (isFull == false) {
            $("#fullScreen span").attr("class", "glyphicon glyphicon-fullscreen");
        } else {
            $("#fullScreen span").attr("class", "glyphicon glyphicon-resize-small");
        }
    })



    $(function() {
        var echart1 = echarts.init(document.getElementById("echart1"));
        echart1.setOption(i());
        echart1.setOption({
            series: <?= json_encode($series['health']) ?>
        });

        var echart2 = echarts.init(document.getElementById("echart2"));
        echart2.setOption(i());
        echart2.setOption({
            series: <?= json_encode($series['activity']) ?>
        });

        var echart3 = echarts.init(document.getElementById("echart3"));
        echart3.setOption(i());
        echart3.setOption({
            series: <?= json_encode($series['user']) ?>
        });

        var echart4 = echarts.init(document.getElementById("echart4"));
        echart4.setOption(n());
        echart4.setOption({
            series: <?= json_encode($series['amount']) ?>
        });

        var echart5 = echarts.init(document.getElementById("echart5"));
        echart5.setOption(p());
        echart5.setOption({
            series: <?= json_encode($series['allocate_num']) ?>
        });

        var echart6 = echarts.init(document.getElementById("echart6"));
        echart6.setOption(m());
        echart6.setOption({
            series: <?= json_encode($series['item']) ?>
        });

        var map = echarts.init(document.getElementById("map_1"));
        map.setOption(b());
        map.setOption({
            series: <?= json_encode($series['geo']) ?>
        });

        window.addEventListener("resize", function() {
            echart1.resize();
            echart2.resize();
            echart3.resize();
            echart4.resize();
            echart5.resize();
            echart6.resize();
            map.resize();
        })
    })

    <?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['dataview'], \yii\web\View::POS_END); ?>