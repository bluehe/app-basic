<?php

namespace project\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%meal}}".
 *
 * @property int $id
 * @property string $group_id 项目
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
    // const REGION_NORTHEAST_1 = 'cn-northeast-1';
    const REGION_NORTH_1 = 'cn-north-1';
    const REGION_NORTH_4 = 'cn-north-4';
    const REGION_EAST_3 = 'cn-east-3';
    const REGION_EAST_2 = 'cn-east-2';
    const REGION_SOUTH_1 = 'cn-south-1';

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
            [['group_id', 'name', 'region', 'cloud_amount', 'devcloud_amount', 'devcloud_count', 'content', 'stat'], 'required'],
            [['amount', 'cloud_amount', 'devcloud_amount'], 'number'],
            [['group_id', 'order_sort', 'devcloud_count', 'stat'], 'integer'],
            [['name', 'region'], 'string', 'max' => 32],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['group_id' => 'id']],
            [['order_sort'], 'default', 'value' => 10],
            ['stat', 'default', 'value' => self::STAT_ACTIVE],
            ['stat', 'in', 'range' => [self::STAT_ACTIVE, self::STAT_DISABLED]],
        ];
    }

    public function beforeSave($insert)
    {
        // 注意，重载之后要调用父类同名函数
        if (parent::beforeSave($insert)) {
            //下拨金额
            $this->amount = $this->cloud_amount + $this->devcloud_amount;

            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporations()
    {
        return $this->hasMany(Corporation::className(), ['intent_set' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporationMeals()
    {
        return $this->hasMany(CorporationMeal::className(), ['meal_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => '项目',
            'name' => '规格',
            'region' => '区域',
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
        'region' => [
            // self::REGION_NORTHEAST_1 => "东北-大连",
            self::REGION_NORTH_1 => "华北-北京一",
            self::REGION_NORTH_4 => "华北-北京四",
            self::REGION_EAST_3 => "华东-上海一",
            self::REGION_EAST_2 => "华东-上海二",
            self::REGION_SOUTH_1 => "华南-广州"
        ]
    ];

    public function getStat()
    {
        $stat = isset(self::$List['stat'][$this->stat]) ? self::$List['stat'][$this->stat] : null;
        return $stat;
    }
    public function getRegion()
    {
        $region = isset(self::$List['region'][$this->region]) ? self::$List['region'][$this->region] : null;
        return $region;
    }

    public static function get_meal($stat = self::STAT_ACTIVE, $group = '')
    {
        $m = [];
        $meals = static::find()->filterWhere(['stat' => $stat, 'group_id' => $group])->orderBy(['order_sort' => SORT_ASC, 'id' => SORT_ASC])->all();
        foreach ($meals as $meal) {
            $m[$meal->id] = self::$List['region'][$meal->region] . ' ' . $meal->name;
        }
        return $m;
    }

    public static function get_corporationmeal_exist($id)
    {
        return CorporationMeal::find()->where(['meal_id' => $id])->exists();
    }

    public static function get_meal_devcount($id)
    {
        return static::find()->where(['id' => $id])->select(['devcloud_count'])->scalar();
    }

    public static function get_meal_devamount($id)
    {
        return static::find()->where(['id' => $id])->select(['devcloud_amount'])->scalar();
    }

    public static function get_meal_cloudamount($id)
    {
        return static::find()->where(['id' => $id])->select(['cloud_amount'])->scalar();
    }

    public static function get_meal_amount($id)
    {
        return static::find()->where(['id' => $id])->select(['amount'])->scalar();
    }
}
