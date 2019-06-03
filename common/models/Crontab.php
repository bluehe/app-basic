<?php
namespace common\models;

use Yii;
use common\helpers\CronParser;

/**
 * This is the model class for table "{{%crontab_list}}".
 *
 * @property int $id
 * @property string $name 定时任务名称
 * @property string $route 任务路由
 * @property string $crontab_str crontab格式
 * @property int $switch 任务开关
 * @property int $status 任务运行状态
 * @property string $last_rundate 上次运行时
 * @property string $next_rundate 下次运行时间
 * @property string $execmemory 任务执行消耗内存(单位/byte)
 * @property string $exectime 任务执行消耗时间
 */
class Crontab extends \yii\db\ActiveRecord
{

    /**
     * switch字段的文字映射
     * @var array
     */
    private $switchTextMap = [
            0 => '关闭',
            1 => '开启',
    ];

    /**
     * status字段的文字映射
     * @var array
     */
    private $statusTextMap = [
            0 => '正常',
            1 => '任务保存',
    ];
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%crontab_list}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['switch', 'status'], 'integer'],
            [['last_rundate', 'next_rundate'], 'safe'],
            [['execmemory', 'exectime'], 'number'],
            [['name', 'route', 'crontab_str'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '定时任务名称',
            'route' => '任务路由',
            'crontab_str' => 'crontab格式',
            'switch' => '任务开关',
            'status' => '任务运行状态',
            'last_rundate' => '上次运行时',
            'next_rundate' => '下次运行时间',
            'execmemory' => '任务执行消耗内存(单位/byte)',
            'exectime' => '任务执行消耗时间',
        ];
    }

    /**
     * 获取switch字段对应的文字
     * @author jlb
     * @return ''|string
     */
    public function getSwitchText()
    {
    	if(!isset($this->switchTextMap[$this->switch])) {
    		return '';
    	}
    	return $this->switchTextMap[$this->switch];
    }

    /**
     * 获取status字段对应的文字
     * @author jlb
     * @return ''|string
     */
    public function getStatusText()
    {
    	if(!isset($this->statusTextMap[$this->status])) {
    		return '';
    	}
    	return $this->statusTextMap[$this->status];
    }

    /**
     * 计算下次运行时间
     * @author jlb
     */
    public function getNextRunDate()
    {
    	if (!CronParser::check($this->crontab_str)) {
    		throw new \Exception("格式校验失败: {$this->crontab_str}", 1);
    	}
    	return CronParser::formatToDate($this->crontab_str, 1)[0];
    }

}