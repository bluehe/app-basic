<?php
namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetForm extends Model
{
    public $username;
    public $verifyCode;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            [['username'], 'filter','filter'=>'strtolower'],
            ['username', 'required', 'message' => '账号不能为空'],          
            ['username', 'existLoginname'],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captcha', 'message' => '验证码不正确', 'on' => 'captchaRequired'],
        ];
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'username' => '账号',
            'verifyCode' => '验证码'
        );
    }
    
    public function existLoginname($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = User::findByLoginname($this->username);
            if ($user===null) {
                $this->addError($attribute, '账号不存在');
            }
        }
    }

    public function setSession() {
        /* @var $user User */
        $user = User::findByLoginname($this->username);

        if (!$user) {
            return false;
        }
        
        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }
        Yii::$app->session->set('find_password_token',$user->password_reset_token);
        return true;
    }
}
