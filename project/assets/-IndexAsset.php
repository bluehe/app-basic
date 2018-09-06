<?php

namespace project\assets;

use yii\web\AssetBundle;

/**
 * Main rky application asset bundle.
 */
class IndexAsset extends AssetBundle {

    public $sourcePath = '@vendor/almasaeed2010/adminlte/bower_components'; //路径
    public $css = [
    ];
    public $js = [
         'jquery-ui/jquery-ui.min.js',
    ];
    public $depends = [
        'project\assets\CommonAsset', //依赖关系
    ];

}
