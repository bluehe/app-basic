<?php

namespace project\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_auth}}".
 *
 * @property integer $id
 * @property integer $uid
 * @property string $type
 * @property string $open_id
 * @property integer $created_at
 *
 */
class UserAuth extends ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user_auth}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['uid', 'open_id', 'type'], 'required'],
            [['uid'], 'integer'],
            [['type'], 'string', 'max' => 10],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'uid' => '用户ID',
            'type' => '类型',
            'open_id' => '第三方ID',
            'created_at' => '创建时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }
    
    /**
     *是否已经认证
     * 
     * @param type $type
     * @param type $uid
     * @return boolean
     */
    public static function isAuth($type,$uid= null) {
        $user_id= $uid===null?Yii::$app->user->identity->id:$uid;
        return static::find()->where(['type'=>$type,'uid'=>$user_id])->exists();
    }


}
