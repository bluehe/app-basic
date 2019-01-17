<?php

namespace project\models;

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
        return '{{%corporation_bd}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporation_id','start_time'], 'required'],
            [['corporation_id', 'bd_id'], 'integer'],
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
            'corporation_id' => '企业',
            'bd_id' => '客户经理',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
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
    
    public static function get_bd_by_time($time='',$corporation_id='') {   
       return static::find()->alias('a')->andFilterWhere(['<=','start_time',$time])->andFilterWhere(['corporation_id'=>$corporation_id])->andWhere(['not exists', static::find()->alias('b')->where('b.corporation_id=a.corporation_id AND b.start_time>a.start_time')])->select(['bd_id','corporation_id','start_time'])->indexBy('corporation_id')->column();
    }
    
    public static function get_pre_date($id) {
        $model= static::findOne($id);
        $time= static::find()->where(['corporation_id'=>$model->corporation_id])->andWhere(['<','start_time',$model->start_time])->select(['start_time'])->orderBy(['start_time'=>SORT_DESC])->scalar();
        return $time>0?date('Y-m-d',$time+86400):null;       
    }
    
    public static function get_next_date($id) {
        $model= static::findOne($id);
        $time= static::find()->where(['corporation_id'=>$model->corporation_id])->andWhere(['>','start_time',$model->start_time])->select(['start_time'])->orderBy(['start_time'=>SORT_ASC])->scalar();
        return $time>0?date('Y-m-d',$time-1):null;          
    }
}
