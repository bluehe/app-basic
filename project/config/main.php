<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-project',
    'name' => '管理系统',
    'version' => '1.0 Beta',
    'basePath' => dirname(__DIR__),
    'charset' => 'utf-8',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'controllerNamespace' => 'project\controllers',
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
            'imageAllowExtensions' => ['jpg', 'png', 'gif']
        ],
    ],
    'aliases' => [
        '@mdm/admin' => '@vendor/mdmsoft/yii2-admin',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
            //'class' => 'yii\redis\Cache',
            'keyPrefix' => 'project',
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
                project\assets\AppAsset::className() => [
                    'sourcePath' => '@project/web',
                    'baseUrl' => '@web',
                    'css' => [
                        'a' => 'css/site.css',
                    ]
                ],
                project\assets\CookieAsset::className() => [
                    'sourcePath' => '@project/web',
                    'baseUrl' => '@web',
                    'js' => [
                        'a' => 'js/sendcookie.js',
                    ]
                ],
                project\assets\ParticlesAsset::className() => [
                    'sourcePath' => '@project/web',
                    'baseUrl' => '@web',
                    'js' => [
                        'a' => 'js/jquery.particleground.min.js',
                    ]
                ],
                project\assets\SupersizedAsset::className() => [
                    'sourcePath' => '@project/web',
                    'baseUrl' => '@web',
                    'css' => [
                        'a' => 'css/supersized.css',
                    ],
                    'js' => [
                        'a' => 'js/supersized.3.2.7.min.js',
                    ]
                ],
                project\assets\ColorAsset::className() => [
                    'sourcePath' => '@project/web',
                    'baseUrl' => '@web',
                    'css' => [
                        'a' => 'css/colpick.css',
                    ],
                    'js' => [
                        'a' => 'js/colpick.js',
                    ]
                ],
                project\assets\CommonAsset::className() => [
                    'sourcePath' => '@vendor/almasaeed2010/adminlte/bower_components',
                    'js' => [
                        'a' => 'jquery-slimscroll/jquery.slimscroll.min.js',
                        'b' => 'fastclick/lib/fastclick.js',
                    ]
                ],
                project\assets\Select2Asset::className() => [
                    'sourcePath' => '@vendor/almasaeed2010/adminlte/bower_components',
                    'css' => [
                        'a' => 'select2/dist/css/select2.min.css',
                    ],
                    'js' => [
                        'a' => 'select2/dist/js/select2.full.min.js',
                    ]
                ],
                project\assets\SparklineAsset::className() => [
                    'sourcePath' => '@vendor/almasaeed2010/adminlte/bower_components',
                    'js' => [
                        'a' => 'jquery-sparkline/dist/jquery.sparkline.min.js',
                    ]
                ],
                project\assets\KanBanAsset::className() => [
                    'sourcePath' => '@project/web',
                    'baseUrl' => '@web',
                    'css' => [
                        'a' => 'css/dataview_common.css',
                    ]
                ],
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager', //这里记得用单引号而不是双引号
            'defaultRoles' => ['guest'],
        ],
        'request' => [
            'csrfParam' => '_csrf-project',
        ],
        'user' => [
            'identityClass' => 'project\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-project', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the app
            'name' => 'docker-project',
            //            'timeout' => 1440,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => yii\log\EmailTarget::className(), //当触发levels配置的错误级别时，发送到message to配置的邮箱中（请改成自己的邮箱）
                    'levels' => ['error', 'warning'],
                    /*'categories' => [//默认匹配所有分类。启用此项后，仅匹配数组中的分类信息会触发邮件提醒（白名单）
                        'yii\db\*',
                        'yii\web\HttpException:*',
                    ],*/
                    'except' => [ //以下配置，除了匹配数组中的分类信息都会触发邮件提醒（黑名单）
                        'yii\web\HttpException:404',
                        'yii\web\HttpException:403',
                        'yii\debug\Module::checkAccess',
                    ],
                    'message' => [
                        'to' => ['179611207@qq.com'], //此处修改成自己接收错误的邮箱
                        'subject' => '来自 APP 的新日志消息',
                    ],
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
                '//kb.hwyzx.com' => 'site/kanban'
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
            'class' => project\components\SiteConfig::className(),
        ],
    ],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            //这里是允许访问的action
            'common/*',
            'site/*',
            'data-view/*',
            //            'api/*',
            'debug/*',
            'admin/*',
            'gii/*'
        ]
    ],
    'on beforeRequest' => [project\components\SiteConfig::className(), 'configInit'],
    'on beforeAction' => ['project\events\initSiteConfig', 'assign'],
    'params' => $params,
];
