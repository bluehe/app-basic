<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "corporation_stat".
 *
 * @property int $id
 * @property int $corporation_id
 * @property int $user_id
 * @property int $created_at
 *
 * @property Corporation $corporation
 * @property User $user
 */
class CorporationStat extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'corporation_stat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporation_id'], 'required'],
            [['corporation_id', 'user_id', 'created_at'], 'integer'],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
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
            'corporation_id' => 'Corporation ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
