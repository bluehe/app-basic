<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-app',
    'name' => '管理系统',
    'version' => '1.0 Beta',
    'basePath' => dirname(__DIR__),
    'charset' => 'utf-8',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'controllerNamespace' => 'app\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'admin' => [
            'class' => 'mdm\admin\Module',
            "layout" => "left-menu",
        ],
        'gridview' => [
            'class' => 'kartik\grid\Module'
        ],
        'redactor' => [
            'class' => 'yii\redactor\RedactorModule',
            'uploadDir' => '@webroot/upload',
            'uploadUrl' => '@web/upload',            
            'imageAllowExtensions'=>['jpg','png','gif']
        ],
    ],
    'aliases' => [
        '@mdm/admin' => '@vendor/mdmsoft/yii2-admin',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
            //'class' => 'yii\redis\Cache',
            'keyPrefix' => 'app',
        ],
        'cdn' => [
            'class' => feehi\cdn\DummyTarget::className(),
        ],
        'assetManager' => [
            'appendTimestamp' => true,
            'linkAssets' => false,
            'bundles' => [
                'dmstr\web\AdminLteAsset' => [
                    'skin' => 'skin-blue',
                ],
                app\assets\AppAsset::className() =>[
                    'sourcePath' => '@app/web',
                    'baseUrl'=>defined('APP_STATIC')?APP_STATIC:'@web',
                    'css'=>[
                        'a'=>'css/site.css',
                    ]                   
                ],
                app\assets\CookieAsset::className() =>[
                    'sourcePath' => '@app/web',
                    'baseUrl'=>defined('APP_STATIC')?APP_STATIC:'@web',
                    'js'=>[
                        'a'=>'js/sendcookie.js',
                    ]                   
                ],
                app\assets\ParticlesAsset::className() =>[
                    'sourcePath' => '@app/web',
                    'baseUrl'=>defined('APP_STATIC')?APP_STATIC:'@web',
                    'js'=>[
                        'a'=>'js/jquery.particleground.min.js',
                    ]                   
                ],
                app\assets\SupersizedAsset::className() =>[
                    'sourcePath' => '@app/web',
                    'baseUrl'=>defined('APP_STATIC')?APP_STATIC:'@web',
                    'css'=>[
                        'a'=>'css/supersized.css',
                    ],
                    'js'=>[
                        'a'=>'js/supersized.3.2.7.min.js',
                    ]                   
                ],
                app\assets\CommonAsset::className() =>[
                    'sourcePath' => '@vendor/almasaeed2010/adminlte/bower_components',
                    'js'=>[
                        'a'=>'jquery-slimscroll/jquery.slimscroll.min.js',
                        'b'=>'fastclick/lib/fastclick.js',
                    ]                   
                ],
                
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager', //这里记得用单引号而不是双引号
            'defaultRoles' => ['guest'],
        ],
        'request' => [
            'csrfParam' => '_csrf-app',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-app', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the app
            'name' => 'advanced-app',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,          
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
            'decimalSeparator' => '.',
            'thousandSeparator' => ',',
            'currencyCode' => 'CNY',
        ],
        'siteConfig' => [
            'class' => app\components\SiteConfig::className(),
        ],
    ],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            //这里是允许访问的action
            'common/*',
            'site/*',
            'page/*',
            'debug/*',
//            'gii/*'
        ]
    ],
    'on beforeRequest' => [app\components\SiteConfig::className(), 'configInit'],
    'on beforeAction' => ['app\events\initSiteConfig', 'assign'],
    'params' => $params,
];
