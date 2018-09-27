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
    
    const TYPE_ADD = 1;
    const TYPE_ALL = 2;   
    const CONNECT_OR = 1;
    const CONNECT_AND = 2;
    
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
            [['type', 'field','value','connect'], 'required'],
            [['type','connect'], 'integer'],
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
            'connect' => '连接符',
            'value' => '条件',
        ];
    }
    
    public static $List = [
        'type' => [
            self::TYPE_ADD => "变化数据",
            self::TYPE_ALL => "原始数据"            
        ],
        'connect' => [
            self::CONNECT_OR => "OR",
            self::CONNECT_AND => "AND"
        ],
    ];

    public function getType() {
        $type = isset(self::$List['type'][$this->type]) ? self::$List['type'][$this->type] : null;
        return $type;
    }
    
    public function getConnect() {
        $connect = isset(self::$List['connect'][$this->connect]) ? self::$List['connect'][$this->connect] : null;
        return $connect;
    }
}
