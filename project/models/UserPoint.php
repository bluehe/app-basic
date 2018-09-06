<?php

namespace project\models;


/**
 * This is the model class for table "{{%user_point}}".
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $direct
 * @property integer $num
 * @property string $content
 * @property string $note
 * @property integer $created_at
 *
 * @property User $u
 */
class UserPoint extends \yii\db\ActiveRecord {

    const DIRECT_PLUS = 1;
    const DIRECT_MINUS = 2;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user_point}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['uid', 'content', 'created_at'], 'required'],
            [['uid', 'direct', 'num', 'created_at'], 'integer'],
            [['content', 'note'], 'string', 'max' => 255],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
            [['direct'], 'default', 'value' => self::DIRECT_PLUS],
            ['direct', 'in', 'range' => [self::DIRECT_PLUS, self::DIRECT_MINUS]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'uid' => '用户',
            'direct' => '增减',
            'num' => '数量',
            'content' => '内容',
            'note' => '备注',
            'created_at' => '签到时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getU() {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    public static $List = [
        'direct' => [
            self::DIRECT_PLUS => "增加",
            self::DIRECT_MINUS => "减少"
        ]
    ];

    public function getDirect() {
        $direct = isset(self::$List['direct'][$this->direct]) ? self::$List['direct'][$this->direct] : null;
        return $direct;
    }

}
