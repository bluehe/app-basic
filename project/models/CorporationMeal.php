<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%corporation_meal}}".
 *
 * @property int $id
 * @property int $corporation_id
 * @property int $meal_id
 * @property int $start_time
 * @property int $end_time
 * @property int $number
 * @property string $amount
 * @property int $bd
 * @property int $user_id
 * @property int $created_at
 *
 * @property Corporation $corporation
 * @property Meal $meal
 * @property User $user
 * @property User $bd0
 */
class CorporationMeal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%corporation_meal}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporation_id', 'start_time', 'number','huawei_account'], 'required'],
            [['corporation_id', 'meal_id', 'number', 'bd', 'user_id', 'created_at'], 'integer'],
            [['amount'], 'number'],
            [['amount'],'requiredBySetid','skipOnEmpty' => false],
            [['huawei_account'], 'string', 'max' => 32],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
            [['meal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meal::className(), 'targetAttribute' => ['meal_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['bd'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['bd' => 'id']],
            [['number'], 'default', 'value' => 1],
        ];
    }
    
     public function beforeSave($insert) {
        // 注意，重载之后要调用父类同名函数
        if (parent::beforeSave($insert)) {           
            //下拨金额
            if($this->meal_id&&$this->number){
                 $this->amount = $this->number*Meal::get_meal_amount($this->meal_id);
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function requiredBySetid($attribute, $params)
    {
        if (!$this->meal_id&&!$this->$attribute){
                $this->addError($attribute,'金额不能为空。');            
        }        
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'corporation_id' => '企业',
            'meal_id' => '下拨套餐',
            'start_time' => '下拨时间',
            'end_time' => '到期时间',
            'number' => '下拨数量',
            'amount' => '下拨金额',
            'huawei_account'=>'华为云账号',
            'bd' => '下拨经理',
            'user_id' => '操作人',
            'created_at' => '操作时间',
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
    public function getMeal()
    {
        return $this->hasOne(Meal::className(), ['id' => 'meal_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBd0()
    {
        return $this->hasOne(User::className(), ['id' => 'bd']);
    }
    
    public static function get_allocate($corporation_id,$time=null) {
        $model= static::find()->where(['corporation_id'=>$corporation_id]);
        if($time){
            $model->andWhere(['between','created_at',$time-2,$time+2]);
        }else{
            $model->orderBy(['created_at'=>SORT_DESC]);
        }
        return  $model->one();  
    }
}
