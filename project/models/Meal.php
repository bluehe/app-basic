<?php

namespace project\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%meal}}".
 *
 * @property int $id
 * @property string $name 规格
 * @property string $region 地区
 * @property string $amount 金额（元）
 * @property string $content 内容
 * @property int $order_sort
 * @property int $stat
 */
class Meal extends \yii\db\ActiveRecord
{
    
    const STAT_ACTIVE = 1;
    const STAT_DISABLED = 0;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%meal}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'region','cloud_amount','devcloud_amount','devcloud_count', 'content', 'stat'], 'required'],
            [['amount','cloud_amount','devcloud_amount'], 'number'],
            [['order_sort','devcloud_count', 'stat'], 'integer'],
            [['name', 'region'], 'string', 'max' => 32],
            [['order_sort'], 'default','value'=>10],
            ['stat', 'default', 'value' => self::STAT_ACTIVE],
            ['stat', 'in', 'range' => [self::STAT_ACTIVE, self::STAT_DISABLED]],
        ];
    }
    
    public function beforeSave($insert) {
        // 注意，重载之后要调用父类同名函数
        if (parent::beforeSave($insert)) {           
            //下拨金额
            $this->amount = $this->cloud_amount+$this->devcloud_amount;
                     
            return true;
        } else {
            return false;
        }
    }
    
    /**
    * @return \yii\db\ActiveQuery
    */
   public function getCorporationMeals()
   {
       return $this->hasMany(CorporationMeal::className(), ['meal_id' => 'id']);
   }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '规格',
            'region' => '地区',
            'devcloud_count' => '软开云人数',
            'devcloud_amount' => '软开云金额（元）',
            'cloud_amount' => '公有云金额（元）',
            'amount' => '总金额（元）',            
            'content' => '内容',
            'order_sort' => '排序',
            'stat' => '状态',
        ];
    }
    
     public static $List = [
        'stat' => [
            self::STAT_ACTIVE => "正常",
            self::STAT_DISABLED => "不可用"
        ],      
    ];

    public function getStat() {
        $stat = isset(self::$List['stat'][$this->stat]) ? self::$List['stat'][$this->stat] : null;
        return $stat;
    }
    
    public static function get_meal($stat=self::STAT_ACTIVE) {
        $meals = static::find()->filterWhere(['stat'=> $stat])->orderBy(['order_sort'=>SORT_ASC,'id'=>SORT_ASC])->all();
        return ArrayHelper::map($meals, 'id', 'name');
    }
    
    public static function get_corporationmeal_exist($id) {
        return CorporationMeal::find()->where(['meal_id'=>$id])->exists();
    }
    
    public static function get_meal_devcount($id) {
        return static::find()->where(['id'=>$id])->select(['devcloud_count'])->scalar();
    }
    
    public static function get_meal_devamount($id) {
        return static::find()->where(['id'=>$id])->select(['devcloud_amount'])->scalar();
    }
    
    public static function get_meal_cloudamount($id) {
        return static::find()->where(['id'=>$id])->select(['cloud_amount'])->scalar();
    }
    
    public static function get_meal_amount($id) {
        return static::find()->where(['id'=>$id])->select(['amount'])->scalar();
    }
    
}
