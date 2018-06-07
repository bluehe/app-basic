<?php
namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;
    public $verifyCode;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required', 'message' => 'E-mail不能为空'],
            ['email', 'email', 'message' => 'E-mail格式不正确'],
            ['email', 'exist',
                'targetClass' => '\rky\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'E-mail不存在.'
            ],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captcha', 'message' => '验证码不正确', 'on' => 'captchaRequired'],
        ];
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'email' => '电子邮件',
            'verifyCode' => '验证码'
        );
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail() {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if (!$user) {
            return false;
        }
        
        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }
        return Yii::$app->mailer
                        ->compose(['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'], ['user' => $user])
                        //->setFrom([Yii::$app->params['supportEmail']['transport']['username'] => Yii::$app->name])
            ->setTo($this->email)
                        ->setSubject(Yii::$app->name . ' 密码重置邮件')
            ->send();
    }
}
