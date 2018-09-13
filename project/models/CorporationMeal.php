<?php

namespace project\models;

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
            [['corporation_id', 'start_time','huawei_account'], 'required'],
            [['corporation_id', 'meal_id', 'number', 'bd', 'user_id', 'created_at'], 'integer'],
            [['amount'], 'number'],
            [['amount'],'requiredByNoSetid','skipOnEmpty' => false],
            [['number'],'requiredBySetid','skipOnEmpty' => false],
            [['huawei_account'], 'string', 'max' => 32],
            [['huawei_account'], 'unique','filter'=>['not',['corporation_id'=>$this->corporation_id]], 'message' => '{attribute}已存在'],
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
            if($this->meal_id){
                if($this->number){
                    $this->amount = $this->number*Meal::get_meal_amount($this->meal_id);
                }
            }else{
                $this->number=1;
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function beforeDelete() {
        
        if(self::get_end_time($this->corporation_id)==$this->end_time){
            $stat = CorporationStat::find()->where(['corporation_id'=>$this->corporation_id])->andWhere(['<','created_at',$this->created_at])->orderBy(['id'=>SORT_DESC])->one();
            if($stat){
                $corporation=Corporation::findOne($this->corporation_id);
                $corporation->stat=$stat->stat;
                $corporation->save();
                CorporationStat::deleteAll(['AND',['corporation_id'=>$this->corporation_id],['>','id',$stat->id]]);
            }
            return true;
            
        }else{
            return false;
        }
       
    }
    
    public function requiredByNoSetid($attribute, $params)
    {
        if (!$this->meal_id&&!$this->$attribute){
                $this->addError($attribute,'金额不能为空。');            
        }        
    }
    
    public function requiredBySetid($attribute, $params)
    {
        if ($this->meal_id&&!$this->$attribute){
                $this->addError($attribute,'数量不能为空。');            
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
            'start_time' => '下拨日期',
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
            $model->andWhere(['created_at'=>$time]);
        }else{
            $model->orderBy(['end_time'=>SORT_DESC]);
        }
        return  $model->one();  
    }
    
    public static function get_end_date($corporation_id,$id=null) {
        $time= static::find()->where(['corporation_id'=>$corporation_id])->andFilterWhere(['not',['id'=>$id]])->select(['end_time'])->orderBy(['end_time'=>SORT_DESC])->scalar();
        return $time>0?date('Y-m-d',$time+1):null;       
    }
    
    public static function get_end_time($corporation_id,$id=null) {
        $time= static::find()->where(['corporation_id'=>$corporation_id])->andFilterWhere(['not',['id'=>$id]])->select(['end_time'])->orderBy(['end_time'=>SORT_DESC])->scalar();
        return $time>0?$time:null;       
    }
    
    public static function get_created_time($corporation_id,$id=null) {
        $time= static::find()->where(['corporation_id'=>$corporation_id])->andFilterWhere(['not',['id'=>$id]])->select(['created_at'])->orderBy(['id'=>SORT_DESC])->scalar();
        return $time>0?$time:null;       
    }
    
        public static function get_amount_total($start='', $end='',$sum=1,$group=0) {
        $query= static::find()->andFilterWhere(['and',['>=', 'start_time', $start],['<=', 'start_time', $end]])->orderBy(['MAX(start_time)'=>SORT_ASC]);
        if($sum==1){
            //天
            $query->select(['amount'=>'SUM(amount)','num'=>'count(*)','time'=>"FROM_UNIXTIME(start_time, '%Y-%m-%d')"])->groupBy(["FROM_UNIXTIME(start_time, '%Y-%m-%d')"])->indexBy(['time']);
        }elseif($sum==2){
            //周
            $query->select(['amount'=>'SUM(amount)','num'=>'count(*)','time'=>"FROM_UNIXTIME(start_time, '%Y-W%u')"])->groupBy(["FROM_UNIXTIME(start_time, '%Y-W%u')"])->indexBy(['time']);      
        }else{
            //月
            if($group==1){
                $query->select(['amount'=>'SUM(amount)','num'=>'count(*)','time'=>"FROM_UNIXTIME(start_time, '%Y-%m')",'bd'])->groupBy(["FROM_UNIXTIME(start_time, '%Y-%m')",'bd']);
            }else{
                $query->select(['amount'=>'SUM(amount)','num'=>'count(*)','time'=>"FROM_UNIXTIME(start_time, '%Y-%m')"])->groupBy(["FROM_UNIXTIME(start_time, '%Y-%m')"])->indexBy(['time']);
            }
        }
        return $query->asArray()->all();
    }

    public static function get_amount_base($start='') {
        return static::find()->andFilterWhere(['<','start_time', $start])->sum('amount');     
    }    
    
    public static function get_cost_total($time) {
        return static::find()->andFilterWhere(['<','start_time', $time])->sum("(CASE WHEN ($time-start_time)/(end_time+1-start_time)<1 THEN amount*($time-start_time)/(end_time+1-start_time) ELSE amount END)");     
    }
    
    public static function get_allocate_num($start='', $end='') {
        return static::find()->andFilterWhere(['and',['>=', 'start_time', $start],['<=', 'start_time', $end]])->select(['amount','num'=>'count(*)'])->orderBy(['num'=>SORT_DESC])->groupBy(['amount'])->asArray()->all();
    }
    
}
