<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main app application asset bundle.
 */
class SupersizedAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/supersized.css',
    ];
    public $js = [
        'js/supersized.3.2.7.min.js',
    ];
}
