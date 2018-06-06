<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main rky application asset bundle.
 */
class CommonAsset extends AssetBundle {

    public $sourcePath = '@vendor/almasaeed2010/adminlte/bower_components'; //路径
    public $css = [
//        'pace/pace.min.css',
    ];
    public $js = [
//        'pace/pace.min.js',
        'jquery-slimscroll/jquery.slimscroll.min.js',
        'fastclick/lib/fastclick.js',
    ];
    public $depends = [
        'dmstr\web\AdminLteAsset', //依赖关系
    ];

}
