<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "health_text".
 *
 * @property int $id
 * @property int $log_id
 * @property string $data
 *
 * @property ImportLog $log
 */
class HealthText extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'health_text';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['log_id'], 'integer'],
            [['data'], 'string'],
            [['log_id'], 'exist', 'skipOnError' => true, 'targetClass' => ImportLog::className(), 'targetAttribute' => ['log_id' => 'id']],
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
}
