<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%corporation_codehub}}".
 *
 * @property int $id
 * @property int $corporation_id 企业ID
 * @property string $name 名称
 * @property string $project_uuid 项目UUID
 * @property string $repository_uuid 仓库UUID
 * @property string $https_url 仓库URL
 * @property string $username 用户名
 * @property string $password 密码
 * @property string $updated_at 更新时间
 *
 * @property Corporation $corporation
 */
class CorporationCodehub extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%corporation_codehub}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporation_id','username','password','https_url'], 'required'],
            [['corporation_id'], 'integer'],
            [['username', 'password'], 'trim'],
            [['name', 'project_uuid', 'repository_uuid', 'username', 'password', 'updated_at'], 'string', 'max' => 32],
            [['https_url'], 'string', 'max' => 128],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
        ];
    }
    
     public function beforeSave($insert) {
        // 注意，重载之后要调用父类同名函数
        if (parent::beforeSave($insert)) {
            if($this->password){
                $this->password = base64_encode($this->password);              
            }          
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->password=$this->password?base64_decode($this->password):'';
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
            'project_uuid' => '项目UUID',
            'repository_uuid' => '仓库UUID',
            'https_url' => '仓库URL',
            'username' => '用户名',
            'password' => '密码',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporation()
    {
        return $this->hasOne(Corporation::className(), ['id' => 'corporation_id']);
    }
    
    public static function get_codehub_exist($id) {
        return static::find()->where(['corporation_id'=>$id])->exists();
    }
}
