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
 */
class Meal extends \yii\db\ActiveRecord
{
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
            [['name', 'region', 'amount', 'content'], 'required'],
            [['amount'], 'number'],
            [['order_sort'], 'integer'],
            [['name', 'region', 'content'], 'string', 'max' => 32],
            [['order_sort'], 'default','value'=>10],
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
        ];
    }
}
