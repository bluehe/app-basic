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
            'username' => 'rky',
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
                    'clientId' => '441b767ffa92ee879471',
                    'clientSecret' => 'b16da9ef4cf8d97a12e035dd4dff413c6198ad0d',
                ],
                'weibo' => [
                    'class' => 'common\widgets\WeiboClient',
                    'clientId' => '1514136892',
                    'clientSecret' => 'e8a0366aa72f5a584452963addec20c9',
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
