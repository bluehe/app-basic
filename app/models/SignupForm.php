<?php
namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
//    public $nickname;
//    public $email;
//    public $tel;
    public $password;
    public $password1;
    public $verifyCode;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username'], 'trim'],
            [['username'], 'filter','filter'=>'strtolower'],
            ['username', 'required'],
            [['username'], 'unique', 'targetClass' => '\app\models\User', 'message' => '{attribute}已存在'],
            ['username', 'string', 'min' => 5, 'max' => 255],
            [['username'], 'match','pattern'=>'/^[A-Za-z0-9]{4,16}$/','message'=>'用户名必须为4-16个字母或数字，不区分大小写'],
            [['username'], 'match','not'=>true,'pattern'=>'/^[0-9]{1,}$/','message'=>'用户名不能全为数字'],
            [['password', 'password1'], 'required'],
            ['password', 'string', 'min' => 4,],
            ['password1', 'compare', 'compareAttribute' => 'password', 'message' => '两次密码不一致'],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captcha', 'message' => '{attribute}不正确', 'on' => 'captchaRequired'],
        ];
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'username' => '用户名',
//            'email' => '电子邮件',
//            'tel' => '联系电话',
//            'nickname' => '昵称',
            'password' => '密码',
            'password1' => '确认密码',
            'verifyCode' => '验证码'
        );
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $user = new User();
        $user->username = $this->username;
//        $user->nickname = $this->nickname;
//        $user->email = $this->email;
//        $user->tel = $this->tel;
        $user->last_login=time();
        $user->setPassword($this->password);
        $user->generateAuthKey();
        
        if ($user->save()) {
            $auth = Yii::$app->authManager;
            $role = $auth->getRole('member');
            $auth->assign($role, $user->id);
            return $user;
        } else {
            return null;
    }
}

}
