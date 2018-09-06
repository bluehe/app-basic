<?php
namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    public $verifyCode;
    private $_user;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username'], 'trim'],
            [['username'], 'filter','filter'=>'strtolower'],
            // username and password are both required
            ['username', 'required', 'message' => '用户名不能为空'],
            ['password', 'required', 'message' => '密码不能为空'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword', 'message' => '密码不正确'],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captcha', 'message' => '验证码不正确', 'on' => 'captchaRequired'],
        ];
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'username' => '用户名',
            'password' => '密码',
            'rememberMe' => '记住我',
            'verifyCode' => '验证码'
        );
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '用户名或密码错误');
            }elseif( $user->status != User::STATUS_ACTIVE){
                $this->addError($attribute, '用户被冻结');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 7 : 0);
        } else {
        return false;
        
        }
    }
    
        

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByLoginname($this->username);
        }

        return $this->_user;
    }
}
