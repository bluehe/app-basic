<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%import_data}}".
 *
 * @property int $id
 * @property int $log_id
 * @property int $corporation_id
 * @property int $field_id
 * @property int $data
 *
 * @property ImportLog $log
 * @property Corporation $corporation
 * @property Field $field
 */
class ImportData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%import_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['log_id', 'corporation_id', 'field_id', 'data'], 'integer'],
            [['log_id'], 'exist', 'skipOnError' => true, 'targetClass' => ImportLog::className(), 'targetAttribute' => ['log_id' => 'id']],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
            [['field_id'], 'exist', 'skipOnError' => true, 'targetClass' => Field::className(), 'targetAttribute' => ['field_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'log_id' => '上传记录',
            'corporation_id' => '公司',
            'field_id' => '字段',
            'data' => '数据',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLog()
    {
        return $this->hasOne(ImportLog::className(), ['id' => 'log_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporation()
    {
        return $this->hasOne(Corporation::className(), ['id' => 'corporation_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getField()
    {
        return $this->hasOne(Field::className(), ['id' => 'field_id']);
    }
    
    public static function get_field_by_log($log_id) {
       $field = static::find()->where(['log_id'=>$log_id])->distinct()->select(['field_id'])->column();      
       return $field;
    }
    
    //已公司为键输出导入数据
    public static function get_data_indexcorporation($log_id='',$field_id=array()) {
       $data=array();
       $fields = static::find()->filterWhere(['log_id'=>$log_id,'field_id'=>$field_id])->all();
       foreach($fields as $field){
           $data[$field['corporation_id']][$field['field_id']]=$field['data'];
       }
       return $data;
    }
}
