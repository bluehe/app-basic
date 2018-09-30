<?php
return [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
        ],
         'db' => [
            'class' => 'yii\db\Connection',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 24 * 3600,
            'charset' => 'utf8',
            'tablePrefix' => '',
            'dsn' => 'mysql:host=localhost;dbname=rky_test',
            'username' => 'rky_test',
            'password' => 'rky_test',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            //false：非测试状态，发送真实邮件而非存储为文件
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.exmail.qq.com',
                'username' => 'dh@gxgygl.com',
                'password' => 'Dh19881006',
                'port' => '465',
                'encryption' => 'ssl',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => ['dh@gxgygl.com' => '管理系统']
            ],
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'github' => [
                    'class' => 'yii\authclient\clients\GitHub',
                    'clientId' => '38f5f49deae15ed4bc68',
                    'clientSecret' => '83dd3bfbb7d458b4e1ff5aeaa74d88a868f68131',
                ],
                'weibo' => [
                    'class' => 'common\widgets\WeiboClient',
                    'clientId' => '1908461907',
                    'clientSecret' => 'ee01aaf40a55769dd866650ad754ed2d',
                ],
//                'qq' => [
//                    'class' => 'common\widgets\QQClient',
//                    'clientId' => '101389884',
//                    'clientSecret' => '0f7af7103526adaff8904219831b101f',
//                ],
            ],
        ],
    ],
];
