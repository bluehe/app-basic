<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use app\components\CommonHelper;

/**
 * Password reset form
 */
class ChangeAuth extends Model
{
    public $type;
    public $email;
    public $tel;
    public $verifyCode;
    
    const TYPE_EMAIL = 'email';
    const TYPE_TEL = 'tel';

    /**
     * @var \common\models\User
     */
    private $_user;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verifyCode', 'email', 'tel'], 'trim'],
            [['verifyCode', 'email', 'tel'], 'filter','filter'=>'strtolower'],
            [['type', 'verifyCode'], 'required'],
            ['email', 'email', 'message' => '{attribute}格式不正确'],
            ['email', 'string', 'max' => 255],
            ['tel', 'match', 'pattern'=>'/^1[34578]{1}\d{9}$/','message' => '{attribute}格式不正确'],
            ['email', 'required', 'when' => function($model){return $model->type==self::TYPE_EMAIL;}, 'whenClient' => "function (attribute, value) {return $('#changeauth-type').val() == '".self::TYPE_EMAIL."';}"],
            ['tel', 'required', 'when' => function($model){return $model->type==self::TYPE_TEL;}, 'whenClient' => "function (attribute, value) {return $('#changeauth-type').val() == '".self::TYPE_TEL."';}"],
            [['email', 'tel'], 'unique', 'targetClass' => User::className(),'filter'=>['not','uid'=>Yii::$app->user->identity->id], 'message' => '{attribute}已存在'],
            ['type', 'in', 'range' => [self::TYPE_EMAIL, self::TYPE_TEL]],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captchaCompare'],
        ];
    }
    
    public function captchaCompare($attribute, $params)
    {
        $code=Yii::$app->session->get('auth_verifyCode');
        if ($this->type!=$code['type']||$this->verifyCode!=$code['code']||$this->{$this->type}!=$code['to']){
            $this->addError($attribute,'验证码错误。');            
        }
    }
    
    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'type' => '类型',
            'email' => '电子邮箱',
            'tel' => '手机号',
            'verifyCode' => '验证码'
        );
    }
    
}
