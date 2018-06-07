<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use Da\QrCode\QrCode;

/**
 * Common controller
 */
class CommonController extends Controller {
    
    public function actionQrcode() {
        $url= Yii::$app->request->referrer;
        $qrCode = (new QrCode($url))->setSize(150)->setMargin(10)->useLogo(Yii::getAlias('@webroot') .'/image/logo.jpg')->setLogoWidth(30);
        header('Content-Type: '.$qrCode->getContentType());
        return  $qrCode->writeString();
    }
}
