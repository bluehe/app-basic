<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "corporation_bd".
 *
 * @property int $id
 * @property int $corporation_id
 * @property int $bd_id
 * @property int $start_time
 * @property int $end_time
 *
 * @property Corporation $corporation
 * @property User $bd
 */
class CorporationBd extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'corporation_bd';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporation_id', 'bd_id'], 'required'],
            [['corporation_id', 'bd_id', 'start_time', 'end_time'], 'integer'],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
            [['bd_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['bd_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'corporation_id' => 'Corporation ID',
            'bd_id' => 'Bd ID',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
        ];
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
    public function getBd()
    {
        return $this->hasOne(User::className(), ['id' => 'bd_id']);
    }
}