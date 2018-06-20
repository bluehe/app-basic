<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\imagine\Image;
use app\models\ChangePassword;
use app\models\User;


class AccountController extends Controller {

    //账号信息
    public function actionIndex() {
        return $this->render('index', [
                    'model' => Yii::$app->user->identity,
        ]);
    }

    /**
     * Reset password
     * @return string
     */
    public function actionChangePassword() {
        $model = new ChangePassword();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->change()) {
                Yii::$app->session->setFlash('success', '修改成功。');
            } else {
                Yii::$app->session->setFlash('error', '修改失败。');
            }
            $model = new ChangePassword();
        }

        return $this->render('change-password', [
                    'model' => $model,
        ]);
    }
    
    public function actionChangeNickname() {

        if (!Yii::$app->user->isGuest) {
            $model = User::findOne(Yii::$app->user->identity->id);
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return json_encode(['stat' => 'success', 'nickname' => $model->nickname]);
            } else {
                return $this->renderAjax('change-nickname', [
                            'model' => $model,
                ]);
            }
        } else {
            return false;
        }
    }
    
     public function actionChangeAuth($type) {

        if (!Yii::$app->user->isGuest) {
            $model = User::findOne(Yii::$app->user->identity->id);
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return json_encode(['stat' => 'success', 'email' => $model->email]);
            } else {
                return $this->renderAjax('change-auth', [
                            'model' => $model,
                            'type'=>$type,
                ]);
            }
        } else {
            return false;
        }
    }

    /**
     * 修改头像
     * @return
     */
    public function actionThumb() {
        if (Yii::$app->request->post()) {

            $image = Yii::$app->request->post('image');
            if ($image) {
                $file = Yii::getAlias('@webroot') . $image['url'];
                //图片处理
                Image::crop($file, $image['w'], $image['h'], [$image['x'], $image['y']])->save($file);
                Image::resize($file, 200, 200, true, true)->save($file);
                $file_name = basename($file);
                $targetPath = Yii::getAlias('@webroot') . '/data/avatar';
                if (!file_exists($targetPath)) {
                    @mkdir($targetPath, 0777, true);
                }
                $filename = $targetPath . DIRECTORY_SEPARATOR . $file_name;
                if (@copy($file, $filename)) {
                    //删除临时文件
                    @unlink($file);
                    //删除原来头像
                    @unlink(Yii::getAlias('@webroot') . Yii::$app->user->identity->avatar);
                    //修改数据库
                    $user = User::findIdentity(Yii::$app->user->identity->id);
                    $user->avatar = '/data/avatar/' . $file_name;
                    $user->save(false);
                    Yii::$app->session->setFlash('success', '头像设置成功。');
                } else {
                    Yii::$app->session->setFlash('error', '头像设置失败。');
                }

                return $this->redirect(['account/thumb']);
            } else {
                $url = Yii::$app->request->post('url');
                return $this->renderAjax('_jcrop', ['url' => $url]);
            }
        }
        $postMaxSize = ini_get('post_max_size');
        $fileMaxSize = ini_get('upload_max_filesize');
        $displayMaxSize = $postMaxSize < $fileMaxSize ? $postMaxSize : $fileMaxSize;
        switch (substr($displayMaxSize, -1)) {
            case 'G':
                $displayMaxSize = $displayMaxSize * 1024 * 1024;
                break;
            case 'M':
                $displayMaxSize = $displayMaxSize * 1024;
                break;
            case 'K':
                $displayMaxSize = $displayMaxSize;
                break;
            default:;
        }

        return $this->render('thumb', ['user' => Yii::$app->user->identity, 'maxsize' => $displayMaxSize]);
    }

    /*
     * ajax方式文件上传
     * 输入$_FILES['files']和dir
     * @return json_encode(['error'=>'']）
     */

    public function actionUploadThumb() {

        //判断是否Ajax
        if (Yii::$app->request->isAjax) {

            if (empty($_FILES['files'])) {
                $postMaxSize = ini_get('post_max_size');
                $fileMaxSize = ini_get('upload_max_filesize');
                $displayMaxSize = $postMaxSize < $fileMaxSize ? $postMaxSize : $fileMaxSize;

                return json_encode(['error' => '没有文件上传,文件最大为' . $displayMaxSize], JSON_UNESCAPED_UNICODE);
                // or you can throw an exception
            }

            //获得文件夹
            $dir = Yii::$app->request->post('dir', 'tmp');

            //目标文件夹，不存在则创建
            $targetFolder = '/data/' . $dir;
            $targetPath = Yii::getAlias('@webroot') . $targetFolder;
            $targetUrl = Yii::getAlias('@web') . $targetFolder;

            if (!file_exists($targetPath)) {
                @mkdir($targetPath, 0777, true);
            }

            $files = $_FILES['files'];
            // a flag to see if everything is ok
            $success = null;

            // file paths to store
            $paths = [];
            //访问路径
            $urls = [];
            //文件名
            $file_names = [];

            // get file names
            $filenames = $files['name'];

            // loop and process files
            for ($i = 0; $i < count($filenames); $i++) {
                $ext = explode('.', basename($filenames[$i]));
                $f_name = md5(uniqid()) . "." . strtolower(array_pop($ext));
                $filename = $targetPath . DIRECTORY_SEPARATOR . $f_name;
                $url = $targetUrl . '/' . $f_name;

                //文件存在则删除
                if (file_exists($filename)) {
                    @unlink($filename);
                }
                if (@move_uploaded_file($files['tmp_name'][$i], $filename)) {
                    Image::resize($filename, 200, 200, true, true)->save($filename);
                    $success = true;
                    $paths[] = $filename;
                    $urls[] = $url;
                    $file_names[] = $f_name;
                } else {
                    $success = false;
                    break;
                }
            }
            if ($success === true) {
                $output = ['urls' => $urls, 'paths' => $paths, 'file_names' => $file_names];
            } elseif ($success === false) {
                $output = ['error' => '文件上传出错！'];
                // delete any uploaded files
                foreach ($paths as $file) {
                    @unlink($file);
                }
            }
            return json_encode($output, JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(['error' => '上传错误！'], JSON_UNESCAPED_UNICODE);
        }
    }

}
