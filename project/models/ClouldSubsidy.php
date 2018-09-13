<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%clould_subsidy}}".
 *
 * @property int $id
 * @property int $corporation_id 补贴企业 
 * @property string $corporation_name 企业名称 
 * @property int $subsidy_bd 客户经理 
 * @property int $subsidy_time 补贴时间
 * @property string $subsidy_amount 补贴金额
 * @property string $subsidy_note
 * 
 * @property Corporation $corporation 
 * @property User $subsidyBd 
 */
class ClouldSubsidy extends \yii\db\ActiveRecord
{
    /**
     *  {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%clould_subsidy}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
           [['subsidy_bd','subsidy_time','subsidy_amount'], 'required'],
           [['corporation_id', 'subsidy_bd'], 'integer'],
           [['subsidy_amount'], 'number'],
           [['subsidy_note'], 'string'],
           [['corporation_name'], 'string', 'max' => 255], 
           [['subsidy_time'],'safe'],
           [['corporation_name'],'requiredByCorporationid','skipOnEmpty' => false],
           [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']], 
           [['subsidy_bd'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['subsidy_bd' => 'id']], 
        ];
    }
    
    public function beforeSave($insert) {
        // 注意，重载之后要调用父类同名函数
        if (parent::beforeSave($insert)) {
            if($this->corporation_id){
                $this->corporation_name = $this->corporation->base_company_name;              
            }
            $this->subsidy_time=strtotime($this->subsidy_time);
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
        $this->subsidy_time=date('Y-m-d',$this->subsidy_time);
    }
    
    public function requiredByCorporationid($attribute, $params)
    {
        if (!$this->corporation_id&&!$this->$attribute){
                $this->addError($attribute,'补贴企业不能为空');            
        }        
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
           'id' => 'ID',
           'corporation_id' => '补贴企业', 
           'corporation_name' => '补贴企业', 
           'subsidy_bd' => '客户经理', 
           'subsidy_time' => '补贴时间',
           'subsidy_amount' => '补贴金额',
            'subsidy_note' => '备注',
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
    public function getSubsidyBd()
    {
        return $this->hasOne(User::className(), ['id' => 'subsidy_bd']);
    }
    
//    public static function get_amount_base($start='') {
//        return static::find()->andFilterWhere(['<','subsidy_time', $start])->sum('subsidy_amount');     
//    }
//    
//    public static function get_amount_total($start='', $end='',$sum=1) {
//         $query= static::find()->andFilterWhere(['and',['>=', 'subsidy_time', $start],['<=', 'subsidy_time', $end]])->orderBy(['MAX(subsidy_time)'=>SORT_ASC]);
//        if($sum==1){
//            //天
//            $query->groupBy(["FROM_UNIXTIME(subsidy_time, '%Y-%m-%d')"])->select(['amount'=>'SUM(subsidy_amount)','num'=>'count(*)','time'=>"FROM_UNIXTIME(subsidy_time, '%Y-%m-%d')"])->indexBy(['time']);
//        }elseif($sum==2){
//            //周
//            $query->groupBy(["FROM_UNIXTIME(subsidy_time, '%Y-W%u')"])->select(['amount'=>'SUM(subsidy_amount)','num'=>'count(*)','time'=>"FROM_UNIXTIME(subsidy_time, '%Y-W%u')"])->indexBy(['time']);      
//        }else{
//            //月
//            $query->groupBy(["FROM_UNIXTIME(subsidy_time, '%Y-%m')"])->select(['amount'=>'SUM(subsidy_amount)','num'=>'count(*)','time'=>"FROM_UNIXTIME(subsidy_time, '%Y-%m')"])->indexBy(['time']);
//        }
//        return $query->asArray()->all();
//    }
}
