<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%standard}}".
 *
 * @property int $type
 * @property string $field
 * @property string $value
 */
class Standard extends \yii\db\ActiveRecord
{
    
    const TYPE_ALL = 1;
    const TYPE_ADD = 2;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%standard}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'field','value'], 'required'],
            [['type'], 'integer'],
            [['field', 'value'], 'string', 'max' => 32],
            [['type', 'field'], 'unique', 'targetAttribute' => ['type', 'field'],'message' => '{attribute}已经存在'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'type' => '类型',
            'field' => '字段',
            'value' => '条件',
        ];
    }
    
    public static $List = [
        'type' => [
            self::TYPE_ALL => "原始数据",
            self::TYPE_ADD => "变化数据"
        ],
    ];

    public function getType() {
        $type = isset(self::$List['type'][$this->type]) ? self::$List['type'][$this->type] : null;
        return $type;
    }
}
