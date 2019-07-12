<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%health_data}}".
 *
 * @property int $id
 * @property int $log_id
 * @property int $group_id 项目
 * @property int $corporation_id
 * @property int $statistics_time
 * @property int $activity_week
 * @property int $activity_month
 * @property int $level
 * @property double $H
 * @property double $D
 * @property double $C
 * @property double $I
 * @property double $A
 * @property double $R
 * @property int $act_trend
 * @property int $is_allocate 是否下拨
 *
 * @property HealthLog $log
 * @property Corporation $corporation
 * @property Group $group
 */
class HealthData extends \yii\db\ActiveRecord
{
    
    const ALLOCATE_D = 0;
    const ALLOCATE_N = 1;
    const ALLOCATE_Y = 2;
    const ACT_D = 0;
    const ACT_N = 1;
    const ACT_Y = 2;
    const TREND_WA = 0;
    const TREND_DE = 1;
    const TREND_UC = 2;
    const TREND_IN = 3;
    const HEALTH_WA = -1;
    const HEALTH_H1 = 1;
    const HEALTH_H2 = 2;
    const HEALTH_H3 = 3;
    const HEALTH_H4 = 4;
    const HEALTH_H5 = 5;
    
    public $start_time;
    public $end_time;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%health_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['log_id', 'group_id', 'corporation_id','bd_id', 'statistics_time', 'activity_week', 'activity_month', 'level', 'act_trend', 'health_trend','is_allocate','start_time','end_time'], 'integer'],
            [['corporation_id', 'statistics_time', 'level'], 'required'],
            [['H','V', 'D', 'C', 'I', 'A', 'R'], 'number'],
            [['corporation_id', 'statistics_time'], 'unique', 'targetAttribute' => ['corporation_id', 'statistics_time'],'message'=>'已经存在此项数据'], 
            [['log_id'], 'exist', 'skipOnError' => true, 'targetClass' => HealthLog::className(), 'targetAttribute' => ['log_id' => 'id']],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
            [['bd_id'], 'exist', 'skipOnError' => true, 'targetClass' => CorporationBd::className(), 'targetAttribute' => ['bd_id' => 'id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['group_id' => 'id']],
            [[ 'activity_week', 'activity_month','level','act_trend', 'health_trend','is_allocate','H','V', 'D', 'C', 'I', 'A', 'R'],'default','value'=>0]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'log_id' => 'Log ID',
            'group_id' => '项目',
            'corporation_id' => '企业',
            'bd_id' => '客户经理',
            'statistics_time' => '统计时间',
            'activity_week' => '周活',
            'activity_month' => '月活',
            'level' => '健康度',
            'H' => 'H',          
            'C' => 'C',
            'I' => 'I',
            'A' => 'A',
            'R' => 'R',
            'V' => 'V',
            'D' => 'D',
            'act_trend' => '活跃趋势',
            'health_trend' => '健康度趋势',
            'is_allocate' => '是否下拨',
        ];
    }
    
     public static $List = [
        'is_allocate' => [
            self::ALLOCATE_Y => "是",
            self::ALLOCATE_N => "否"
        ],
        'is_act' => [
            self::ACT_Y => "是",
            self::ACT_N => "否"
        ],
        'act_trend' => [
            self::TREND_DE => "下降",
            self::TREND_UC => "持平",
            self::TREND_IN => "上升"
        ],
        'health' => [
            self::HEALTH_WA => "未计算",
            self::HEALTH_H1 => "H1",
            self::HEALTH_H2 => "H2",
            self::HEALTH_H3 => "H3",
            self::HEALTH_H4 => "H4",
            self::HEALTH_H5 => "H5"
        ],
        'health_color' => [
            self::HEALTH_WA => "#909090",
            self::HEALTH_H1 => "#dd4b39",
            self::HEALTH_H2 => "#f6877b",
            self::HEALTH_H3 => "#dfba08",
            self::HEALTH_H4 => "#90ee7e",
            self::HEALTH_H5 => "#00a65a"
        ],
        'column'=>[
            'is_allocate' => '是否下拨',
            'activity_month' => '月活',
            'activity_week' => '周活',
            'act_trend' => '活跃趋势',
            'level' => '健康度',           
            'health_trend' => '健康度趋势',
            'H' => 'H',          
            'C' => 'C',
            'I' => 'I',
            'A' => 'A',
            'R' => 'R',
            'V' => 'V',
            'D' => 'D',
        ]
    ];
    
    public function getAllocate() {
        $is_allocate = isset(self::$List['is_allocate'][$this->is_allocate]) ? self::$List['is_allocate'][$this->is_allocate] : null;
        return $is_allocate;
    }
    
    public function getActWeek() {
        $act = isset(self::$List['is_act'][$this->activity_week]) ? self::$List['is_act'][$this->activity_week] : null;
        return $act;
    }
    
     public function getActMonth() {
        $act = isset(self::$List['is_act'][$this->activity_month]) ? self::$List['is_act'][$this->activity_month] : null;
        return $act;
    }
    
    public function getHealth() {
        $health = isset(self::$List['health'][$this->level]) ? self::$List['health'][$this->level] : null;
        return $health;
    }
    
    public function getHealthColor() {
        $health_color = isset(self::$List['health_color'][$this->health]) ? self::$List['health_color'][$this->health] : null;
        return $health_color;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLog()
    {
        return $this->hasOne(HealthLog::className(), ['id' => 'log_id']);
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
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }
    
    public function getBd()
    {
        return $this->hasOne(User::className(), ['id' => 'bd_id']);
    }
    
    //健康度趋势
    public static function get_health_line($corporation_id,$start, $end) {
        $data=static::find()->where(['corporation_id'=>$corporation_id])->andFilterWhere(['and',['>=', 'statistics_time', $start],['<=', 'statistics_time', $end]])->orderBy(['statistics_time'=>SORT_ASC])->select(['level'])->column();
        return implode(',', $data);
    }
    
    //活跃度趋势
    public static function get_act_line($corporation_id,$start, $end) {
        $data=static::find()->where(['corporation_id'=>$corporation_id])->andFilterWhere(['and',['>=', 'statistics_time', $start],['<=', 'statistics_time', $end]])->orderBy(['statistics_time'=>SORT_ASC])->select(["(CASE WHEN activity_week=2 THEN 1 WHEN activity_week=1 THEN -1 ELSE 0 END)"])->column();
        return implode(',', $data);
    }
    
    public static function get_pre_time($statistics_time='',$group_id=null,$corporation_id='') {   
       return static::find()->andFilterWhere(['<','statistics_time',$statistics_time])->andFilterWhere(['corporation_id'=>$corporation_id,'group_id'=>$group_id])->select(['statistics_time'])->orderBy(['statistics_time'=>SORT_DESC])->distinct()->scalar();

    }
    
    public static function get_next_time($statistics_time='',$group_id=null,$corporation_id='') {   
       return static::find()->andFilterWhere(['>','statistics_time',$statistics_time])->andFilterWhere(['corporation_id'=>$corporation_id,'group_id'=>$group_id])->select(['statistics_time'])->orderBy(['statistics_time'=>SORT_ASC])->distinct()->scalar();

    }
    
    //设定趋势
    public static function set_activity_trend() {
        
        $datas = static::find()->where(['act_trend'=>self::TREND_WA])->all();
        foreach($datas as $data){
            $model_old= static::find()->where(['corporation_id'=>$data->corporation_id])->andWhere(['<','statistics_time',$data->statistics_time])->orderBy(['statistics_time'=>SORT_DESC])->one();
            $old=$model_old==null?self::ACT_N:$model_old->activity_week;
            $v=$data->activity_week-$old;
            switch ($v){
                case 0:$data->act_trend=self::TREND_UC;break;
                case 1:$data->act_trend=self::TREND_IN;break;
                case -1:$data->act_trend=self::TREND_DE;break;
                default:$data->act_trend=self::TREND_WA;
            }
            static::updateAll(['act_trend'=>$data->act_trend], ['id'=>$data->id]);
           // $data->save();
            
        }
        return true;
    }
    
    //设定趋势
    public static function set_health_trend() {
        
        $datas = static::find()->where(['health_trend'=>self::TREND_WA])->all();
        foreach($datas as $data){
            $model_old= static::find()->where(['corporation_id'=>$data->corporation_id])->andWhere(['<','statistics_time',$data->statistics_time])->orderBy(['statistics_time'=>SORT_DESC])->one();
            $old=$model_old==null?self::ACT_N:$model_old->level;
            $v=$data->level-$old;
            if($v==0){
                $data->health_trend=self::TREND_UC;
            }elseif($v>0){
                $data->health_trend=self::TREND_IN;
            }elseif($v<0){
                $data->health_trend=self::TREND_DE;
            }else{
                $data->health_trend=self::TREND_WA;
            }
            static::updateAll(['health_trend'=>$data->health_trend], ['id'=>$data->id]);
           // $data->save();
            
        }
        return true;
    }
    
    //设定下拨
    public static function set_allocate() {
        $ids=static::find()->alias('a')->andWhere(['is_allocate'=> self::ALLOCATE_D])->andWhere(['not exists', CorporationMeal::find()->alias('b')->where('b.corporation_id=a.corporation_id AND a.statistics_time>=b.start_time AND a.statistics_time<=b.end_time')])->select(['id'])->column();
          
        static::updateAll(['is_allocate'=> self::ALLOCATE_N], ['id'=>$ids]);
        static::updateAll(['is_allocate'=> self::ALLOCATE_Y],['is_allocate'=> self::ALLOCATE_D]);
        return true;
    }
    
    public static function get_health($start, $end,$group_id=null,$allocate=null,$total=1) {
        $query = static::find()->andWhere(['group_id'=>$group_id])->andFilterWhere(['is_allocate'=>$allocate])->andFilterWhere(['and',['>=', 'statistics_time', $start],['<=', 'statistics_time', $end]])->orderBy(['statistics_time'=>SORT_ASC,'level'=>SORT_ASC])->select(['statistics_time','num'=>'count(level)','health'=>'MAX(level)','bd_id'=>'MAX(bd_id)'])->groupBy(['statistics_time','level']);
        if(!$total){
            $query->addGroupBy(['bd_id']);
        }
        return $query->asArray()->all();
    }
    
}
