<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main app application asset bundle.
 */
class CookieAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
    ];
    public $js = [
        'js/sendcookie.js',
    ];
}
