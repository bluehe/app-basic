<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-03-15 21:16
 */

namespace app\components;

use Yii;
use yii\base\Component;
use common\helpers\FileDependencyHelper;
use yii\caching\FileDependency;
use app\models\System;

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

        $cache = Yii::$app->getCache();
        $key = 'system';
        if (($data = $cache->get($key)) === false) {
            $data = System::find()->asArray()->select(['code','value','store_range'])->indexBy("code")->all();
            $cacheDependencyObject = Yii::createObject([
                'class' => FileDependencyHelper::className(),
                'rootDir' => '@app/runtime/cache/file_dependency/',
                'fileName' => 'system.txt',
            ]);
            $fileName = $cacheDependencyObject->createFile();
            $dependency = new FileDependency(['fileName' => $fileName]);
            $cache->set($key, $data, 0, $dependency);
        }

        foreach ($data as $v) {
            $this->{$v['code']} = $v['value'];
        }
    }


    public static function configInit()
    {
        
        if (Yii::$app->siteConfig->smtp_service) {

            $data = Yii::$app->cach->get('system');
            $charsets = json_decode($data['smtp_charset']['store_range'], true);
            $smtpcharset = $charsets[Yii::$app->siteConfig->smtp_charset];
            
            Yii::configure(yii::$app->mailer, [
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
        
        Yii::$app->name = Yii::$app->siteConfig->system_name?Yii::$app->siteConfig->system_nam:Yii::$app->name;
        
    }

}