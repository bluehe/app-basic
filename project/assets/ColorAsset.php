<?php

namespace project\assets;

use yii\web\AssetBundle;

/**
 * Main rky application asset bundle.
 */
class ColorAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    //public $sourcePath = '@webroot';
    public $css = [
        'css/colpick.css', //css
    ];
    public $js = [
        'js/colpick.js',
    ];

}
