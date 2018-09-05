<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%column_setting}}".
 *
 * @property int $id
 * @property string $type
 * @property int $uid
 * @property string $content
 *
 * @property User $u
 */
class ColumnSetting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%column_setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'integer'],
            [['content'], 'string'],
            [['type'], 'string', 'max' => 255],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型',
            'uid' => '用户',
            'content' => '内容',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getU()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }
    
    //得到ID-name 键值数组
    public static function get_column($user,$type) {
        $data=null;
        $column = self::find()->where(['uid'=>$user,'type'=>$type])->one();
        if($column!==null){
            $data=json_decode($column['content']);
        }
        return $data;
    }
}
