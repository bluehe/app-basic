<?php

namespace app\events;

use Yii;
use yii\base\Event;
use app\models\System;
use app\models\Crontab;
use app\models\UserLog;
use app\models\User;

class initSiteConfig extends Event {

    public static function assign() {

        $cache = Yii::$app->cache;
        $smtp = $cache->get('system_smtp');
        if ($smtp === false) {
            $smtp = System::getChildrenValue('smtp');
            $range = System::find()->where(['code' => 'smtp_charset'])->select('store_range')->one();
            $charsets = json_decode($range['store_range'], true);
            $smtp['smtpcharset'] = $charsets[$smtp['smtp_charset']];
            $cache->set('system_smtp', $smtp);
        }
        if ($smtp['smtp_service']) {
            Yii::$app->set('mailer', [
                'class' => 'yii\swiftmailer\Mailer',
                'viewPath' => 'app/mail',
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
                    'host' => $smtp['smtp_host'],
                    'username' => $smtp['smtp_username'],
                    'password' => $smtp['smtp_password'],
                    'port' => $smtp['smtp_port'],
                    'encryption' => $smtp['smtp_ssl'] ? 'ssl' : 'tls',
                ],
                'messageConfig' => [
                    'charset' => $smtp['smtpcharset'], //改变
                    'from' => [$smtp['smtp_from'] => Yii::$app->name]
                ],
            ]);
        }
        $system = $cache->get('system_info');
        if ($system === false) {
            $system = System::getChildrenValue('system');
            if (!$system['system_name']) {
                $system['system_name'] = Yii::$app->name;
            }
            $cache->set('system_info', $system);
        }
        Yii::$app->name = $system['system_name'];

        //定时任务
        $event_scheduler = $cache->get('event_scheduler');

        if ($event_scheduler === false) {
            $event_scheduler = Yii::$app->db->createCommand("SELECT @@event_scheduler;")->queryScalar();
//            if ($event_scheduler != 'ON') {
//                Yii::$app->db->createCommand("set GLOBAL event_scheduler = ON;")->execute();
//                $event_scheduler = Yii::$app->db->createCommand("SELECT @@event_scheduler;")->queryScalar();
//            }
            $cache->set('event_scheduler', $event_scheduler);
        }

        if ($event_scheduler != 'ON') {
            //未成功，不能通过mysql-event执行定时任务
            self::crontab();
        }
        
        //登录记录
        $user_ip = Yii::$app->request->userIP;
        if (Yii::$app->request->cookies->getValue('login', false) != $user_ip && !Yii::$app->user->isGuest) {
            $time = strtotime(date('Y-m-d', time()));
            $exists = UserLog::find()->where(['and', ['>', 'created_at', $time], ['uid' => Yii::$app->user->identity->id, 'ip' => $user_ip]])->exists();
            if (!$exists) {
                //设置记录
                $content = @file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=" . $user_ip);
                $ipinfo = json_decode($content, true);
                $log = new UserLog();
                if ($ipinfo['code'] === 0) {
                    $log->setAttributes($ipinfo['data']);
                }
                $log->created_at = time();
                $log->uid = Yii::$app->user->identity->id;
                if ($log->save()) {
                    User::updateAll(['last_login' => time()], ['id' => Yii::$app->user->identity->id]);
                    $cookie = new Cookie([
                        'name' => 'login',
                        'expire' => $time + 86400,
                        'value' => $user_ip,
                        'httpOnly' => true
                    ]);

                    Yii::$app->response->cookies->add($cookie);
                }
            }
        }

        return true;
    }

    public static function crontab() {
        $crontabs = Crontab::find()->where(['AND', ['stat' => Crontab::STAT_OPEN], ['<=', 'start_at', time()], ['OR', ['end_at' => null], ['>', 'end_at', time()]]])->andWhere(['OR', ['AND', ['interval_time' => null], ['exc_at' => null]], ['AND', ['NOT', ['interval_time' => null]], ['OR', ['exc_at' => null], 'exc_at+interval_time<=unix_timestamp(now())']]])->all();

        foreach ($crontabs as $crontab) {
            $sqls = explode(';', $crontab->content);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($sqls as $sql) {
                    //执行操作
                    Yii::$app->db->createCommand($sql)->execute();
                }
                $NOW = time();
                if ($crontab->interval_time == null) {
                    $crontab->exc_at = $NOW;
                } else {
                    $crontab->exc_at = $NOW - ($NOW - $crontab->start_at) % $crontab->interval_time;
                }
                if (!$crontab->save()) {
                    throw new \Exception("操作失败");
                }
                $transaction->commit();
            } catch (\Exception $e) {

                $transaction->rollBack();
//                throw $e;
            }
        }
    }

}
