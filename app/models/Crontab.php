<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%crontab}}".
 *
 * @property integer $id
 * @property integer $start_at
 * @property integer $end_at
 * @property integer $interval_time
 * @property string $content
 * @property integer $exc_at
 * @property integer $stat
 */
class Crontab extends ActiveRecord {

    const STAT_OPEN = 1;
    const STAT_CLOSE = 2;
    const INTERVAL_1S = 1;
    const INTERVAL_1M = 60;
    const INTERVAL_1H = 3600;
    const INTERVAL_1D = 86400;
    const INTERVAL_3D = 259200;
    const INTERVAL_7D = 604800;
    const INTERVAL_30D = 2592000;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%crontab}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'start_at', 'content'], 'required'],
            [['interval_time', 'exc_at', 'stat'], 'integer'],
            [['content'], 'string'],
            [['name', 'title'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => '名称',
            'title' => '标题',
            'start_at' => '开始时间',
            'end_at' => '结束时间',
            'interval_time' => '间隔',
            'content' => '内容',
            'exc_at' => '上次执行',
            'stat' => '状态',
        ];
    }

    public static $List = [
        'stat' => [
            self::STAT_OPEN => "启用",
            self::STAT_CLOSE => "关闭"
        ],
        'interval_time' => [
            self::INTERVAL_1S => "一秒",
            self::INTERVAL_1M => "一分钟",
            self::INTERVAL_1H => "一小时",
            self::INTERVAL_1D => "一天",
            self::INTERVAL_3D => "三天",
            self::INTERVAL_7D => "一周",
            self::INTERVAL_30D => "一个月"
        ],
        'interval' => [
            self::INTERVAL_1S => "1 SECOND",
            self::INTERVAL_1M => "1 MINUTE",
            self::INTERVAL_1H => "1 HOUR",
            self::INTERVAL_1D => "1 DAY",
            self::INTERVAL_3D => "3 DAY",
            self::INTERVAL_7D => "1 WEEK",
            self::INTERVAL_30D => "1 MONTH"
        ]
    ];

    public function getStat() {

        $stat = self::$List['stat'][$this->stat];
        return isset($stat) ? $stat : null;
    }

    public function getIntervalTime() {

        $interval = self::$List['interval_time'][$this->interval_time];
        return isset($interval) ? $interval : null;
    }

    public function getInterval($interval) {

        $inter1 = self::$List['interval'][$interval];
        return isset($inter1) ? $inter1 : null;
    }

}
