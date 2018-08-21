<?php

namespace app\models;

use Yii;

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
            [['name', 'region', 'amount', 'content', 'stat'], 'required'],
            [['amount'], 'number'],
            [['order_sort', 'stat'], 'integer'],
            [['name', 'region'], 'string', 'max' => 32],
            [['order_sort'], 'default','value'=>10],
            ['stat', 'default', 'value' => self::STAT_ACTIVE],
            ['stat', 'in', 'range' => [self::STAT_ACTIVE, self::STAT_DISABLED]],
        ];
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
            'amount' => '金额（元）',
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
    
}
