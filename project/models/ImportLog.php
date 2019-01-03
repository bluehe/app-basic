<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%import_log}}".
 *
 * @property int $id
 * @property string $name
 * @property string $patch
 * @property int $statistics_at
 * @property int $created_at
 * @property int $stat
 *
 * @property ImportData[] $importDatas
 */
class ImportLog extends \yii\db\ActiveRecord
{
    const STAT_UPLOAD = 1;
    const STAT_START = 2;
    const STAT_INDUCE = 3;
    const STAT_COVER = 4;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%import_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['stat'], 'integer'],
            [['created_at'], 'required'],
            [['name', 'patch'], 'string', 'max' => 64],
            [['statistics_at'],'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'patch' => '路径',
            'statistics_at' => '统计日期',
            'created_at' => '导入时间',
            'stat' => '状态',
        ];
    }
    
    public static $List = [
        'stat' => [
            self::STAT_UPLOAD => "已上传",
            self::STAT_START => "已初始化",
            self::STAT_INDUCE => "已生成",
            self::STAT_COVER => "被覆盖"
        ]
    ];
    
     public function getStat() {
        $stat = isset(self::$List['stat'][$this->stat]) ? self::$List['stat'][$this->stat] : null;
        return $stat;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImportDatas()
    {
        return $this->hasMany(ImportData::className(), ['log_id' => 'id']);
    }
}
