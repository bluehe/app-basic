<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main app application asset bundle.
 */
class PageAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
       'css/page.css',
    ];
    public $js = [
        'js/jquery.lazyload.min.js',
        'js/page.js',
    ];
    public $depends = [
        'rmrevin\yii\fontawesome\AssetBundle',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
    
    //定义按需加载JS方法，注意加载顺序在最后
    public static function addScript($view, $jsfile) {
        $view->registerJsFile($jsfile, [PageAsset::className(), 'depends' => 'app\assets\PageAsset']);
    }

    //定义按需加载css方法，注意加载顺序在最后
    public static function addCss($view, $cssfile) {
        $view->registerCssFile($cssfile, [PageAsset::className(), 'depends' => 'app\assets\PageAsset']);
    }
    
    public function init() {
        $controller_id = \Yii::$app->controller->id;
        $action_id = \Yii::$app->controller->action->id;
        if ($controller_id == 'site' && $action_id == 'user') {
            $this->depends[] = 'app\assets\Select2Asset';
        }

        parent::init();
    }
}
