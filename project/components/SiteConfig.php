<?php

/**
 * Author: blue
 * Created at: 2017-03-15 21:16
 */

namespace project\components;

use Yii;
use yii\base\Component;
use common\helpers\FileDependencyHelper;
use yii\caching\FileDependency;
use project\models\System;

class SiteConfig extends Component
{

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : '';
    }


    public function init()
    {
        parent::init();

        $cache = Yii::$app->cache;
        $data = $cache->get('system');
        if ($data === false) {
            $data = System::find()->asArray()->select(['code', 'value', 'store_range'])->indexBy("code")->all();
            $cacheDependencyObject = Yii::createObject([
                'class' => FileDependencyHelper::className(),
                'rootDir' => '@project/runtime/cache/file_dependency/',
                'fileName' => 'system.txt',
            ]);
            $fileName = $cacheDependencyObject->createFile();
            $dependency = new FileDependency(['fileName' => $fileName]);
            $cache->set('system', $data, 0, $dependency);
        }

        foreach ($data as $v) {
            $this->{$v['code']} = $v['value'];
        }
    }


    public static function configInit()
    {

        if (Yii::$app->siteConfig->smtp_service) {

            $data = Yii::$app->cache->get('system');
            $charsets = json_decode($data['smtp_charset']['store_range'], true);
            $smtpcharset = $charsets[Yii::$app->siteConfig->smtp_charset];

            Yii::$app->set('mailer', [
                'class' => 'yii\swiftmailer\Mailer',
                'useFileTransport' => false,
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
                    'host' => Yii::$app->siteConfig->smtp_host,
                    'username' => Yii::$app->siteConfig->smtp_username,
                    'password' => Yii::$app->siteConfig->smtp_password,
                    'port' => Yii::$app->siteConfig->smtp_port,
                    'encryption' => Yii::$app->siteConfig->smtp_ssl ? 'ssl' : 'tls',
                ],
                'messageConfig' => [
                    'charset' => $smtpcharset, //改变
                    'from' => [Yii::$app->siteConfig->smtp_from => Yii::$app->name]
                ],
            ]);
        }

        if (Yii::$app->siteConfig->cdn_service) {

            if (Yii::$app->siteConfig->cdn_platform == 'Alioss') {
                Yii::$app->set('cdn', [
                    'class' => \feehi\cdn\AliossTarget::className(),
                    'bucket' => Yii::$app->siteConfig->Alioss_cdn_bucket,
                    'accessKey' => Yii::$app->siteConfig->Alioss_cdn_key,
                    'accessSecret' => Yii::$app->siteConfig->Alioss_cdn_secret,
                    'endPoint' => Yii::$app->siteConfig->Alioss_cdn_point,
                    'host' => Yii::$app->siteConfig->Alioss_cdn_host
                ]);
            } elseif (Yii::$app->siteConfig->cdn_platform == 'Qiniu') {
                Yii::$app->set('cdn', [
                    'class' => \feehi\cdn\QiniuTarget::className(),
                    'accessKey' => Yii::$app->siteConfig->Qiniu_cdn_key,
                    'secretKey' => Yii::$app->siteConfig->Qiniu_cdn_secret,
                    'bucket' => Yii::$app->siteConfig->Qiniu_cdn_bucket,
                    'host' => Yii::$app->siteConfig->Qiniu_cdn_host
                ]);
            } elseif (Yii::$app->siteConfig->cdn_platform == 'Qcloud') {
                Yii::$app->set('cdn', [
                    'class' => \feehi\cdn\QcloudTarget::className(),
                    'appId' => Yii::$app->siteConfig->Qcloud_cdn_appid,
                    'secretId' => Yii::$app->siteConfig->Qcloud_cdn_key,
                    'secretKey' => Yii::$app->siteConfig->Qcloud_cdn_secret,
                    'region' => Yii::$app->siteConfig->Qcloud_cdn_point,
                    'bucket' => Yii::$app->siteConfig->Qcloud_cdn_bucket,
                    'host' => Yii::$app->siteConfig->Qcloud_cdn_host
                ]);
            } elseif (Yii::$app->siteConfig->cdn_platform == 'Netease') {
                Yii::$app->set('cdn', [
                    'class' => \feehi\cdn\NeteaseTarget::className(),
                    'bucket' => Yii::$app->siteConfig->Netease_cdn_bucket,
                    'accessKey' => Yii::$app->siteConfig->Netease_cdn_key,
                    'accessSecret' => Yii::$app->siteConfig->Netease_cdn_secret,
                    'endPoint' => Yii::$app->siteConfig->Netease_cdn_point,
                    'host' => Yii::$app->siteConfig->Netease_cdn_host
                ]);
            } else if (Yii::$app->siteConfig->cdn_platform == 'Huawei') {
                Yii::$app->set('cdn', [
                    'class' => \project\components\cdn\HuaweiTarget::className(),
                    'bucket' => Yii::$app->siteConfig->Huawei_cdn_bucket,
                    'key' => Yii::$app->siteConfig->Huawei_cdn_key,
                    'secret' => Yii::$app->siteConfig->Huawei_cdn_secret,
                    'endpoint' => Yii::$app->siteConfig->Huawei_cdn_point,
                    'host' => Yii::$app->siteConfig->Huawei_cdn_host
                ]);
            }
        }

        Yii::$app->name = Yii::$app->siteConfig->system_name ? Yii::$app->siteConfig->system_nam : Yii::$app->name;
    }
}
