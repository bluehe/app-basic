<?php

namespace project\assets;

use yii\web\AssetBundle;

/**
 * Main rky application asset bundle.
 */
class SparklineAsset extends AssetBundle {

    public $sourcePath = '@vendor/almasaeed2010/adminlte/bower_components'; //路径
    public $css = [
    ];
    public $js = [
        'jquery-sparkline/dist/jquery.sparkline.min.js',
    ];
    public $depends = [
    ];

}
