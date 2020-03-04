<?php

namespace project\assets;

use yii\web\AssetBundle;

/**
 * Main rky application asset bundle.
 */
class KanBanAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/dataview_common.css', //css
    ];
    public $js = [
        'js/jquery.js',
        'js/echarts.min.js',
        'js/china.js',
        'js/kanban.js',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
    ];
}
