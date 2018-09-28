<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%train_user}}".
 *
 * @property int $train_id
 * @property int $user_id
 *
 * @property Train $train
 * @property User $user
 */
class TrainUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%train_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['train_id', 'user_id'], 'required'],
            [['train_id', 'user_id'], 'integer'],
            [['train_id', 'user_id'], 'unique', 'targetAttribute' => ['train_id', 'user_id']],
            [['train_id'], 'exist', 'skipOnError' => true, 'targetClass' => Train::className(), 'targetAttribute' => ['train_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'train_id' => '培训ID',
            'user_id' => '人员ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrain()
    {
        return $this->hasOne(Train::className(), ['id' => 'train_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public static function get_userid($id,$role='sa') {
        $query=static::find()->joinWith(['user'])->where(['train_id' => $id]);
        if($role=='other'){
            $query->andWhere(['not',['role'=>'sa']]);
        }else{
            $query->andFilterWhere(['role'=>$role]);
        }
        return $query->select(['user_id'])->orderBy(['tuser_sort'=>SORT_ASC])->column();
    }
}
