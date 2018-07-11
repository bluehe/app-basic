<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
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

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
