<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Description of ChangePassword
 *
 */
class ChangePassword extends Model {

    public $oldPassword;
    public $newPassword;
    public $retypePassword;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['oldPassword', 'newPassword', 'retypePassword'], 'required', 'message' => '{attribute}不能为空'],
            [['oldPassword'], 'validatePassword'],
            [['newPassword'], 'string'],
            [['retypePassword'], 'compare', 'compareAttribute' => 'newPassword', 'message' => '两次密码不一致'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'oldPassword' => '原密码',
            'newPassword' => '新密码',
            'retypePassword' => '确认密码',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword() {
        /* @var $user User */
        $user = Yii::$app->user->identity;
        if (!$user || !$user->validatePassword($this->oldPassword)) {
            $this->addError('oldPassword', '密码不正确');
        }
    }

    /**
     * Change password.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function change() {
        if ($this->validate()) {
            /* @var $user User */
            $user = Yii::$app->user->identity;
            $user->setPassword($this->newPassword);
            $user->generateAuthKey();
            if ($user->save()) {
                return true;
            }
        }

        return false;
    }

}
