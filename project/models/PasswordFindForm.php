<?php
namespace project\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use project\components\CommonHelper;

/**
 * Password reset form
 */
class PasswordFindForm extends Model
{
    public $type;
    public $password;
    public $password1;
    public $verifyCode;

    /**
     * @var \common\models\User
     */
    private $_user;


    /**
     * Creates a form model given a token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('新密码不能为空。');
        }
        $this->_user = User::findByPasswordResetToken($token);
        if (!$this->_user) {
            throw new InvalidParamException('密码重置令牌错误。');
        }
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type','password', 'password1','verifyCode'], 'required'],
            ['password', 'string', 'min' => 4,],
            ['password1', 'compare', 'compareAttribute' => 'password', 'message' => '两次密码不一致'],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captchaCompare'],
        ];
    }
    
    public function captchaCompare($attribute, $params)
    {
        $code=Yii::$app->session->get('verifyCode');
        if ($this->type!=$code['type']||$this->verifyCode!=$code['code']){
            $this->addError($attribute,'验证码错误。');            
        }
    }
    
    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'password' => '新密码',
            'password1' => '确认密码',
            'verifyCode' => '验证码'
        );
    }
    
    public function getName($type='email'){
        $user = $this->_user;
        if($type=='email'){
            $note=$user['email']?CommonHelper::hideName($user['email']):'未设置邮箱';            
        }else{
            $note=$user['tel']?CommonHelper::hideName($user['tel']):'未设置手机号';
        }
        return $note;
    }

    /**
     * Resets password.
     *
     * @return bool if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();
        Yii::$app->session->remove('find_password_token');
        return $user->save(false);
    }
}
