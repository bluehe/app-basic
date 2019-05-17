<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%corporation_project}}".
 *
 * @property int $id
 * @property int $corporation_id 企业ID
 * @property string $name 名称
 * @property string $description 项目描述
 * @property string $project_uuid 项目UUID
 * @property int $add_type 添加方式
 *
 * @property Corporation $corporation
 */
class CorporationProject extends \yii\db\ActiveRecord
{
    public $member;
    
    const TYPE_ADD = 1;
    const TYPE_SYSTEM = 2;
    const TYPE_CHECK = 3;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%corporation_project}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporation_id','project_uuid','name'], 'required'],
            [['corporation_id', 'add_type'], 'integer'],
            [['name', 'description', 'project_uuid'], 'string', 'max' => 32],
            [['member'], 'safe'],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'corporation_id' => '企业ID',
            'name' => '名称',
            'description' => '项目描述',
            'project_uuid' => '项目UUID',
            'add_type' => '添加方式',
            'member'=>'项目成员',
        ];
    }
    
    public static $List = [       
        'type'=>[
            self::TYPE_ADD=>'手动',
            self::TYPE_CHECK=>'检测',            
            self::TYPE_SYSTEM=>'系统',       
        ],
       
    ];
    
    public function getType() {
        $stat = isset(self::$List['type'][$this->add_type]) ? self::$List['type'][$this->add_type] : null;
        return $stat;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporation()
    {
        return $this->hasOne(Corporation::className(), ['id' => 'corporation_id']);
    }
    
    public static function get_corporationproject_exist($id) {
        return static::find()->where(['corporation_id'=>$id])->exists();
    }
}
