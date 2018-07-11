<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%recommend}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $url
 * @property string $img
 * @property integer $creared_at
 * @property integer $update_at
 * @property integer $status
 */
class Recommend extends \yii\db\ActiveRecord {

    const STAT_OPEN = 1;
    const STAT_CLOSE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%recommend}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'url', 'img'], 'required', 'message' => '{attribute}不能为空'],
            [['created_at', 'updated_at', 'sort_order', 'stat', 'click_num'], 'integer'],
            [['name'], 'string', 'max' => 4, 'message' => '{attribute}最多4个字符'],
            [['url', 'img'], 'string', 'max' => 255],
            ['url', 'url', 'defaultScheme' => 'http'],
            [['click_num', 'sort_order'], 'default', 'value' => 0],
            [['stat'], 'default', 'value' => self::STAT_OPEN],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => '名称',
            'url' => '链接',
            'img' => '图片',
            'click_num' => '点击数',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'sort_order' => '排序',
            'stat' => '状态',
        ];
    }

    public static $List = [
        'stat' => [
            self::STAT_OPEN => "启用",
            self::STAT_CLOSE => "关闭"
        ]
    ];

    public function getStat() {

        $stat = isset(self::$List['stat'][$this->stat]) ? self::$List['stat'][$this->stat] : null;
        return $stat;
    }

    public static function get_recommend($stat = '', $limit = '') {
        $query = static::find()->andFilterWhere(['stat' => $stat])->select(['id', 'name', 'url', 'img', 'stat'])->orderBy(['sort_order' => SORT_DESC]);
        if ($limit) {
            $query->limit($limit);
        }
        $recommends = $query->indexBy('id')->asArray()->all();
        return $recommends;
    }

}
