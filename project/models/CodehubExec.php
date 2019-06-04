<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%codehub_exec}}".
 *
 * @property int $id
 * @property int $codehub_id 仓库ID
 * @property int $user_id
 * @property int $updated_at 执行时间
 * @property int $type 执行类型
 *
 * @property CorporationCodehub $codehub
 * @property User $user
 */
class CodehubExec extends \yii\db\ActiveRecord
{
    
    const TYPE_ADD = 1;
    const TYPE_SYSTEM = 2;
    
    const STAT_YES = 1;
    const STAT_NO = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%codehub_exec}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codehub_id', 'user_id', 'updated_at', 'type','stat'], 'integer'],
            [['codehub_id'], 'exist', 'skipOnError' => true, 'targetClass' => CorporationCodehub::className(), 'targetAttribute' => ['codehub_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codehub_id' => '仓库ID',
            'user_id' => '用户 ID',
            'updated_at' => '执行时间',
            'type' => '执行类型',
            'stat' => '执行结果',
        ];
    }
    
    public static $List = [  
        'type'=>[
            self::TYPE_ADD=>'手动',            
            self::TYPE_SYSTEM=>'系统',       
        ],
        'stat'=>[
            self::STAT_YES=>'是',
            self::STAT_NO=>'否',      
        ],
       
    ];
    
    public function getType() {
        $stat = isset(self::$List['type'][$this->type]) ? self::$List['type'][$this->type] : null;
        return $stat;
    }
    
    public function getStat() {
        $stat = isset(self::$List['stat'][$this->stat]) ? self::$List['stat'][$this->stat] : null;
        return $stat;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCodehub()
    {
        return $this->hasOne(CorporationCodehub::className(), ['id' => 'codehub_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public static function get_last_exec($codehub_id,$stat=null) {
        return static::find()->where(['codehub_id'=>$codehub_id])->andFilterWhere(['stat'=>$stat])->orderBy(['updated_at'=>SORT_DESC])->select(['updated_at'])->scalar();
    }
}
