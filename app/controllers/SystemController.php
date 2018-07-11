<?php

namespace app\controllers;

use Yii;
use app\models\System;
use app\models\Crontab;
use yii\web\Controller;
use yii\data\ActiveDataProvider;

/**
 * SystemController
 */
class SystemController extends Controller {

    /**
     * 系统信息

     */
    public function actionIndex() {

        if (Yii::$app->request->post()) {
            $system = Yii::$app->request->post('System');
            $res = System::setSystem($system);
            if ($res) {
                Yii::$app->cache->delete('system_info');
                Yii::$app->session->setFlash('success', '更新成功。');
            } elseif ($res === false) {
                Yii::$app->session->setFlash('error', '更新失败。');
            }
        }

        return $this->render('index', [
                    'model' => System::getChildren('system'),
        ]);
    }

    /**
     * 邮件设置
     */
    public function actionSmtp() {

        if (Yii::$app->request->post()) {
            $system = Yii::$app->request->post('System');

            $res = System::setSystem($system);
            if ($res) {
                Yii::$app->cache->delete('system_smtp');
                Yii::$app->session->setFlash('success', '更新成功。');
            } elseif ($res === false) {
                Yii::$app->session->setFlash('error', '更新失败。');
            }
        }

        return $this->render('smtp', [
                    'model' => System::getChildren('smtp'),
        ]);
    }
    
    /**
     * 短信设置
     */
    public function actionSms() {

        if (Yii::$app->request->post()) {
            $system = Yii::$app->request->post('System');

            $res = System::setSystem($system);
            if ($res) {
                Yii::$app->cache->delete('system_sms');
                Yii::$app->session->setFlash('success', '更新成功。');
            } elseif ($res === false) {
                Yii::$app->session->setFlash('error', '更新失败。');
            }
        }

        return $this->render('sms', [
                    'model' => System::getChildren('sms'),
        ]);
    }

    /**
     * 验证码设置
     */
    public function actionCaptcha() {

        if (Yii::$app->request->post()) {
            $system = Yii::$app->request->post('System');
            $system['captcha_open'] = isset($system['captcha_open']) ? implode(',', $system['captcha_open']) : '';
            $res = System::setSystem($system);
            if ($res) {
                Yii::$app->session->setFlash('success', '更新成功。');
            } elseif ($res === false) {
                Yii::$app->session->setFlash('error', '更新失败。');
            }
        }

        return $this->render('captcha', [
                    'model' => System::getChildren('captcha'),
        ]);
    }


    /**
     * 发送测试邮件
     */
    public function actionSendEmail() {
        if (Yii::$app->request->post()) {
            $emailto = Yii::$app->request->post('email');
            $validator = new \yii\validators\EmailValidator();
            if (!$validator->validate($emailto, $error)) {
                return json_encode(['stat'=>'fail','message'=>$error]);
            }
            $system = Yii::$app->request->post('System');
            
            if($system['smtp_service']==1){
                $range = System::find()->where(['code' => 'smtp_charset'])->select('store_range')->one();
                $charsets = json_decode($range['store_range'], true);
                $system['smtpcharset'] = $charsets[$system['smtp_charset']];
                Yii::$app->set('mailer', [
                'class' => 'yii\swiftmailer\Mailer',
                'useFileTransport' => false,
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
                    'host' => $system['smtp_host'],
                    'username' => $system['smtp_username'],
                    'password' => $system['smtp_password'],
                    'port' => $system['smtp_port'],
                    'encryption' => $system['smtp_ssl'] ? 'ssl' : 'tls',
                ],
                'messageConfig' => [
                    'charset' => $system['smtpcharset'], //改变
                    'from' => [$system['smtp_from'] => Yii::$app->name]
                ],
            ]);
            }
            $mail = Yii::$app->mailer->compose()
                    ->setTo($emailto)
                    ->setSubject(Yii::$app->name . '测试邮件')
                    ->setTextBody('测试邮件')
                    ->setHtmlBody('<b>测试邮件</b>');

            
            if ($mail->send()) {
                return json_encode(['stat'=>'success','message'=>'测试邮件发送成功']);
            } else {
                return json_encode(['stat'=>'fail','message'=>'测试邮件发送失败']);
            }
        }
    }
    
    /**
     * 发送测试短信
     */
    public function actionSendSms() {
        if (Yii::$app->request->post()) {
            $smsto = Yii::$app->request->post('tel');
            if (!preg_match("/^1[34578]{1}\d{9}$/",$smsto)) {
                return json_encode(['stat'=>'fail','message'=>'请输入有效手机号']);
            }
            $system = Yii::$app->request->post('System');
           
            if($system['sms_platform']=='aliyun'){
                Yii::$app->set('aliyun', [
                    'class' => 'saviorlv\aliyun\Sms',
                    'accessKeyId' => $system['sms_key'],
                    'accessKeySecret' => $system['sms_secret'],                
                ]);
                $response_r = Yii::$app->aliyun->sendSms(
                            $system['sms_sign'], // 短信签名
                            $system['sms_captcha'], // 短信模板编号
                            $smsto, // 短信接收者
                            ["code"=>"123456"]
                            
                        );
                $response= json_decode($response_r,TRUE);
                $result =$response['code']==200?'success':$response['message'];
            }elseif($system['sms_platform']=='cloudsmser'){           
                require_once(Yii::getAlias('@common').'/vendor/smser/cloudsmser/smsapi.fun.php');
                $response=sendSMS($system['sms_key'],$system['sms_secret'],$smsto,array_to_json(['code'=>123456]),$system['sms_captcha']);
                $result =$response['stat']==100?'success':$response['message'];
            }elseif($system['sms_platform']=='submail'){
                $server='https://api.mysubmail.com/';
                $message_configs['appid']=$system['sms_key'];
                $message_configs['appkey']=$system['sms_secret'];
                $message_configs['sign_type']='normal';
                $message_configs['server']=$server;
                
                require_once(Yii::getAlias('@common').'/vendor/smser/submail/SUBMAILAutoload.php');
                $submail=new \MESSAGEXsend($message_configs);
                $submail->setTo($smsto);
                $submail->SetProject($system['sms_captcha']);
                $submail->AddVar('code',123456); 
                $xsend=$submail->xsend();
                $result =$xsend['status']=='success'?'success':$xsend['msg'];
            }
            return $result=='success'?json_encode(['stat'=>'success','message'=>'测试短信发送成功']):json_encode(['stat'=>'fail','message'=>$result]);
            
        }
    }
    
    /**
     * 协议设置
     */
    public function actionAgreement() {

        if (Yii::$app->request->post()) {
            $system = Yii::$app->request->post('System');

            $res = System::setSystem($system);
            if ($res) {
                Yii::$app->session->setFlash('success', '更新成功。');
            } elseif ($res === false) {
                Yii::$app->session->setFlash('error', '更新失败。');
            }
        }

        return $this->render('agreement', [
                    'model' => System::getChildren('agreement'),
        ]);
    }

    /**
     * Lists all Crontab models.
     * @return mixed
     */
    public function actionCrontab() {

        $dataProvider = new ActiveDataProvider([
            'query' => Crontab::find(),
        ]);

        return $this->render('crontab', [
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Crontab model.
     * If creation is successful, the browser will be redirected to the 'crontab' page.
     * @return mixed
     */
    public function actionCrontabCreate() {
        $model = new Crontab();

        $model->stat = Crontab::STAT_OPEN;
        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $sql = "CREATE EVENT `{$model->name}` ON SCHEDULE";
                if ($model->interval_time) {
                    $sql .= " EVERY " . $model->getInterval($model->interval_time) . " STARTS '{$model->start_at}'";
                    if ($model->end_at) {
                        $sql .= " ENDS '{$model->end_at}'";
                    }
                } else {
                    $sql .= " AT '{$model->start_at}'";
                }
                $sql .= $model->stat == Crontab::STAT_OPEN ? " ENABLE " : " DISABLE ";
                $sql .= "
                        DO
                        BEGIN
                            {$model->content};
                            UPDATE {{%crontab}} SET exc_at=unix_timestamp(now()) WHERE name='{$model->name}';
                        END";

                Yii::$app->db->createCommand("DROP EVENT IF EXISTS `{$model->name}`;")->execute();
                Yii::$app->db->createCommand($sql)->execute();

                $model->start_at = strtotime($model->start_at);
                if ($model->end_at) {
                    $model->end_at = strtotime($model->end_at);
                }
                if (!$model->save()) {
                    throw new yii\db\Exception("操作失败");
                }
                $transaction->commit();
                Yii::$app->session->setFlash('success', '添加成功。');
                return $this->redirect(['crontab-update', 'id' => $model->id]);
            } catch (yii\db\Exception $e) {

                $transaction->rollBack();
//                throw $e;
                Yii::$app->session->setFlash('error', '添加失败。');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            return $this->render('crontab-create', [
                        'model' => $model,
            ]);
        }
    }
    
    public function actionCrontabOpen() {
        $event_scheduler = Yii::$app->db->createCommand("SELECT @@event_scheduler;")->queryScalar();
        if ($event_scheduler != 'ON') {
            Yii::$app->db->createCommand("set GLOBAL event_scheduler = ON;")->execute();
            $event_scheduler = Yii::$app->db->createCommand("SELECT @@event_scheduler;")->queryScalar();
        }
        if ($event_scheduler != 'ON') {
            Yii::$app->session->setFlash('danger', '开启失败。');
        }else{
            Yii::$app->session->setFlash('success', '开启成功。');
            Yii::$app->cache->delete('event_scheduler');
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Updates an existing Crontab model.
     * If update is successful, the browser will be redirected to the 'cronteb-update' page.
     * @param integer $id
     * @return mixed
     */
    public function actionCrontabUpdate($id) {
        $model = Crontab::findOne($id);

        if ($model !== null) {
            if ($model->load(Yii::$app->request->post())) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $sql = "CREATE EVENT `{$model->name}` ON SCHEDULE";
                    if ($model->interval_time) {
                        $sql .= " EVERY " . $model->getInterval($model->interval_time) . " STARTS '{$model->start_at}'";
                        if ($model->end_at) {
                            $sql .= " ENDS '{$model->end_at}'";
                        }
                    } else {
                        $sql .= " AT '{$model->start_at}'";
                    }
                    $sql .= $model->stat == Crontab::STAT_OPEN ? " ENABLE " : " DISABLE ";
                    $sql .= "
                        DO
                        BEGIN
                            {$model->content};
                            UPDATE {{%crontab}} SET exc_at=unix_timestamp(now()) WHERE name='{$model->name}';
                        END";

                    Yii::$app->db->createCommand("DROP EVENT IF EXISTS `{$model->name}`;")->execute();
                    Yii::$app->db->createCommand($sql)->execute();

                    $model->start_at = strtotime($model->start_at);
                    if ($model->end_at) {
                        $model->end_at = strtotime($model->end_at);
                    }
                    if (!$model->save()) {
                        throw new yii\db\Exception("操作失败");
                    }
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', '操作成功。');
                } catch (yii\db\Exception $e) {

                    $transaction->rollBack();
//                throw $e;
                    Yii::$app->session->setFlash('error', '操作失败。');
                }
            }
            $model->start_at = $model->start_at ? date('Y-m-d H:i:s', $model->start_at) : null;
            $model->end_at = $model->end_at ? date('Y-m-d H:i:s', $model->end_at) : null;
            return $this->render('crontab-update', [
                        'model' => $model,
            ]);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionCrontabDelete($id) {
        $model = Crontab::findOne($id);

        if ($model !== null) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                Yii::$app->db->createCommand("DROP EVENT IF EXISTS `{$model->name}`;")->execute();
                $model->delete();
            } catch (\Exception $e) {

                $transaction->rollBack();
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

}
