<?php

namespace project\assets;

use yii\web\AssetBundle;

/**
 * Main app application asset bundle.
 */
class ParticlesAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
    ];
    public $js = [
        'js/jquery.particleground.min.js',
    ];
}
