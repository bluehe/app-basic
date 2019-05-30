<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%activity_change}}".
 *
 * @property int $id
 * @property int $start_time
 * @property int $end_time
 * @property int $bd_id
 * @property int $corporation_id
 * @property int $type
 * @property int $is_allocate
 * @property int $is_act
 * @property int $act_trend
 * @property int $projectman_usercount
 * @property int $projectman_projectcount
 * @property int $projectman_membercount
 * @property int $projectman_versioncount
 * @property int $projectman_issuecount
 * @property double $projectman_storagecount
 * @property int $codehub_all_usercount
 * @property int $codehub_repositorycount
 * @property int $codehub_commitcount
 * @property double $codehub_repositorysize
 * @property int $pipeline_usercount
 * @property int $pipeline_pipecount
 * @property int $pipeline_executecount
 * @property double $pipeline_elapse_time
 * @property int $codecheck_usercount
 * @property int $codecheck_taskcount
 * @property int $codecheck_codelinecount
 * @property int $codecheck_issuecount
 * @property int $codecheck_execount
 * @property int $codeci_usercount
 * @property int $codeci_buildcount
 * @property int $codeci_allbuildcount
 * @property double $codeci_buildtotaltime
 * @property int $testman_usercount
 * @property int $testman_casecount
 * @property int $testman_totalexecasecount
 * @property int $deploy_usercount
 * @property int $deploy_envcount
 * @property int $deploy_execount
 * @property double $deploy_vmcount
 *
 * @property Corporation $corporation
 */
class ActivityChange extends \yii\db\ActiveRecord
{   
    const TYPE_UPDATE = 1;
    const TYPE_ADD = 2;
    const TYPE_DELETE = 3;
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
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%activity_change}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id','start_time', 'end_time', 'corporation_id', 'type'], 'required'],
            [['group_id','start_time', 'end_time', 'bd_id','corporation_id', 'type','is_allocate', 'is_act', 'act_trend','health', 'projectman_usercount', 'projectman_projectcount', 'projectman_membercount', 'projectman_versioncount', 'projectman_issuecount', 'codehub_all_usercount', 'codehub_repositorycount', 'codehub_commitcount', 'pipeline_usercount', 'pipeline_pipecount', 'pipeline_executecount', 'codecheck_usercount', 'codecheck_taskcount', 'codecheck_codelinecount', 'codecheck_issuecount', 'codecheck_execount', 'codeci_usercount', 'codeci_buildcount', 'codeci_allbuildcount', 'testman_usercount', 'testman_casecount', 'testman_totalexecasecount', 'deploy_usercount', 'deploy_envcount', 'deploy_execount'], 'integer'],
            [['projectman_storagecount', 'codehub_repositorysize', 'pipeline_elapse_time', 'codeci_buildtotaltime', 'deploy_vmcount'], 'number'],
            [['corporation_id', 'start_time', 'end_time'], 'unique', 'targetAttribute' => ['corporation_id', 'start_time', 'end_time'],'message'=>'已经存在此项数据'],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
            [[ 'projectman_usercount', 'projectman_projectcount', 'projectman_membercount', 'projectman_versioncount', 'projectman_issuecount', 'codehub_all_usercount', 'codehub_repositorycount', 'codehub_commitcount', 'pipeline_usercount', 'pipeline_pipecount', 'pipeline_executecount', 'codecheck_usercount', 'codecheck_taskcount', 'codecheck_codelinecount', 'codecheck_issuecount', 'codecheck_execount', 'codeci_usercount', 'codeci_buildcount', 'codeci_allbuildcount', 'testman_usercount', 'testman_casecount', 'testman_totalexecasecount', 'deploy_usercount', 'deploy_envcount', 'deploy_execount','projectman_storagecount', 'codehub_repositorysize', 'pipeline_elapse_time', 'codeci_buildtotaltime', 'deploy_vmcount','h_h','h_c','h_i','h_a','h_r','h_v','h_d'],'default','value'=>0],
            ['health', 'default', 'value' => self::HEALTH_WA],
            ['type', 'default', 'value' => self::TYPE_UPDATE],
            ['type', 'in', 'range' => [self::TYPE_UPDATE, self::TYPE_ADD, self::TYPE_DELETE]],
            ['is_allocate', 'default', 'value' => self::ALLOCATE_D],
            ['is_allocate', 'in', 'range' => [self::ALLOCATE_D, self::ALLOCATE_N, self::ALLOCATE_Y]],
            ['is_act', 'default', 'value' => self::ACT_D],
            ['is_act', 'in', 'range' => [self::ACT_D, self::ACT_Y, self::ACT_N]],
            ['act_trend', 'default', 'value' => self::TREND_WA],
            ['act_trend', 'in', 'range' => [self::TREND_WA, self::TREND_DE, self::TREND_UC, self::TREND_IN]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge([
            'id'=>'ID',
            'group_id' => '项目',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'bd_id' => '客户经理',
            'corporation_id' => '公司',
            'type' => '类型',
            ],
            self::$List['column_usual'],
            [
            'act_trend' => '趋势',
            'h_h' => '健康度h',
            'h_c' => '健康度c',
            'h_i' => '健康度i',
            'h_a' => '健康度a',
            'h_r' => '健康度r',
            'h_v' => '健康度v',
            'h_d' => '健康度d',
            ],
            self::$List['column_activity'],
            self::$List['column_data']
            );
    }
    
    public static $List = [
        'type' => [
            self::TYPE_UPDATE => "正常",
            self::TYPE_ADD => "新增",
            self::TYPE_DELETE => "减少"
        ],
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
            self::HEALTH_WA => "#dd4b39",
            self::HEALTH_H1 => "#909090",
            self::HEALTH_H2 => "#f7a35c",
            self::HEALTH_H3 => "#7cb5ec",
            self::HEALTH_H4 => "#90ee7e",
            self::HEALTH_H5 => "#00a65a"
        ],
        'column_usual'=>[
            'is_allocate' => '下拨',
            'is_act' => '活跃',           
            'health' => '健康度',
            ],
        'column_activity'=>[
            'projectman_usercount' => '项目用户数',
            'projectman_projectcount' => '当前项目数',
            'projectman_membercount' => '当前项目成员数',
            'projectman_versioncount' => '当前迭代数',
            'projectman_issuecount' => '工作项数',
            'projectman_storagecount' => '项目存储空间',
            'codehub_all_usercount' => '配置管理用户数',
            'codehub_repositorycount' => '当前代码仓库数',
            'codehub_commitcount' => '提交次数',
            'codehub_repositorysize' => '存储空间',
            'pipeline_usercount' => '流水线用户数',
            'pipeline_pipecount' => '当前流水线条数',
            'pipeline_executecount' => '流水线执行次数',
            'pipeline_elapse_time' => '流水线执行时长',
            'codecheck_usercount' => '代码检查用户数',
            'codecheck_taskcount' => '当前检查任务数',
            'codecheck_codelinecount' => '检查代码行数',
            'codecheck_issuecount' => '检查发现问题总数',
            'codecheck_execount' => '检查次数',
            'codeci_usercount' => '编译构建用户数',
            'codeci_buildcount' => '当前构建任务数',
            'codeci_allbuildcount' => '构建次数',
            'codeci_buildtotaltime' => '构建时长',
            'testman_usercount' => '测试管理用户数',
            'testman_casecount' => '用例总数',
            'testman_totalexecasecount' => '用例执行次数',
            'deploy_usercount' => '部署用户数',
            'deploy_envcount' => '当前部署任务数',
            'deploy_execount' => '部署次数',
            'deploy_vmcount' => '节点数',
            ],
        'column_data'=>[
            'projectman_usercount_d' => '项目用户数(A)',
            'projectman_projectcount_d' => '当前项目数(A)',
            'projectman_membercount_d' => '当前项目成员数(A)',
            'projectman_versioncount_d' => '当前迭代数(A)',
            'projectman_issuecount_d' => '工作项数(A)',
            'projectman_storagecount_d' => '项目存储空间(A)',
            'codehub_all_usercount_d' => '配置管理用户数(A)',
            'codehub_repositorycount_d' => '当前代码仓库数(A)',
            'codehub_commitcount_d' => '提交次数(A)',
            'codehub_repositorysize_d' => '存储空间(A)',
            'pipeline_usercount_d' => '流水线用户数(A)',
            'pipeline_pipecount_d' => '当前流水线条数(A)',
            'pipeline_executecount_d' => '流水线执行次数(A)',
            'pipeline_elapse_time_d' => '流水线执行时长(A)',
            'codecheck_usercount_d' => '代码检查用户数(A)',
            'codecheck_taskcount_d' => '当前检查任务数(A)',
            'codecheck_codelinecount_d' => '检查代码行数(A)',
            'codecheck_issuecount_d' => '检查发现问题总数(A)',
            'codecheck_execount_d' => '检查次数(A)',
            'codeci_usercount_d' => '编译构建用户数(A)',
            'codeci_buildcount_d' => '当前构建任务数(A)',
            'codeci_allbuildcount_d' => '构建次数(A)',
            'codeci_buildtotaltime_d' => '构建时长(A)',
            'testman_usercount_d' => '测试管理用户数(A)',
            'testman_casecount_d' => '用例总数(A)',
            'testman_totalexecasecount_d' => '用例执行次数(A)',
            'deploy_usercount_d' => '部署用户数(A)',
            'deploy_envcount_d' => '当前部署任务数(A)',
            'deploy_execount_d' => '部署次数(A)',
            'deploy_vmcount_d' => '节点数(A)',
        ]
    ];
    
    public function getType() {
        $type = isset(self::$List['type'][$this->type]) ? self::$List['type'][$this->type] : null;
        return $type;
    }
    
    public function getAllocate() {
        $is_allocate = isset(self::$List['is_allocate'][$this->is_allocate]) ? self::$List['is_allocate'][$this->is_allocate] : null;
        return $is_allocate;
    }
    
    public function getAct() {
        $act = isset(self::$List['is_act'][$this->is_act]) ? self::$List['is_act'][$this->is_act] : null;
        return $act;
    }
    
    public function getHealth() {
        $health = isset(self::$List['health'][$this->health]) ? self::$List['health'][$this->health] : null;
        return $health;
    }
    
    public function getHealthColor() {
        $health_color = isset(self::$List['health_color'][$this->health]) ? self::$List['health_color'][$this->health] : null;
        return $health_color;
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
    
    public function getData()
    {
        return $this->hasOne(ActivityData::className(), ['corporation_id' => 'corporation_id','statistics_time'=>'end_time']);
    }
    
    //生成数据
    public static function induce_data($start_time,$end_time,$group_id) {
        $corporation_bd= CorporationBd::get_bd_by_time($end_time);
        $model_change=new ActivityChange();
        $model_change->loadDefaultValues();
        $model_change->start_time=$start_time;
        $model_change->end_time=$end_time;
        $model_change->group_id=$group_id;
                        
        $codes= self::$List['column_activity'];//计算字段
        $typeadd_codes= Field::get_typeadd_code($end_time);//排除字段
        $news= ActivityData::get_data_by_time($end_time,$group_id);
        $olds= ActivityData::get_data_by_time($start_time,$group_id);
        $new_keys= array_keys($news);
        $old_keys= array_keys($olds);
        //新增
        $c_add= array_diff($new_keys, $old_keys);
        foreach($c_add as $c_a){
            $_model_ca= clone $model_change;
            $_model_ca->type= ActivityChange::TYPE_ADD;            
            $_model_ca->corporation_id=$c_a;
            $_model_ca->bd_id=isset($corporation_bd[$c_a])?$corporation_bd[$c_a]:null;
            foreach($codes as $code=>$v){
                $_model_ca->$code=$news[$c_a][$code];
            }
            $_model_ca->save();
        }
                        
        //更新
        $c_update= array_intersect($new_keys, $old_keys);
        foreach($c_update as $c_u){
            $_model_cu= clone $model_change;
            $_model_cu->type= ActivityChange::TYPE_UPDATE;
            $_model_cu->corporation_id=$c_u;
            $_model_cu->bd_id=isset($corporation_bd[$c_u])?$corporation_bd[$c_u]:null;
            foreach($codes as $code=>$v){
                if(in_array($code,$typeadd_codes)){
                    $_model_cu->$code=$news[$c_u][$code];
                }else{
                    $_model_cu->$code=$news[$c_u][$code]-$olds[$c_u][$code];                   
                }
                
            }
            $_model_cu->save();
        }
                        
        //减少
        $c_delete= array_diff($old_keys, $new_keys);
        foreach($c_delete as $c_d){
            $_model_cd= clone $model_change;
            $_model_cd->type= ActivityChange::TYPE_DELETE;
            $_model_cd->corporation_id=$c_d;
            $_model_cd->bd_id=isset($corporation_bd[$c_d])?$corporation_bd[$c_d]:null;
            foreach($codes as $code=>$v){
                if(in_array($code,$typeadd_codes)){
                    $_model_cd->$code=0;
                }else{
                    $_model_cd->$code=0-$olds[$c_d][$code];                   
                }
                
            }
            $_model_cd->save();
        }
        return true;

    }
    
    //设定活跃有效
    public static function set_allocate() {
        $ids=static::find()->alias('a')->andWhere(['is_allocate'=> self::ALLOCATE_D])->andWhere(['not exists', CorporationMeal::find()->alias('b')->where('b.corporation_id=a.corporation_id AND a.end_time>=b.start_time AND a.end_time<=b.end_time')])->select(['id'])->column();
          
        static::updateAll(['is_allocate'=> self::ALLOCATE_N], ['id'=>$ids]);
        static::updateAll(['is_allocate'=> self::ALLOCATE_Y],['is_allocate'=> self::ALLOCATE_D]);
        return true;
    }
           
    //设定活跃
    public static function set_activity() {
        $query=static::find()->alias('c')->joinWith(['data d'])->andWhere(['is_act'=> self::ACT_D]);
        
        $condition_and = Standard::find()->where(['connect'=> Standard::CONNECT_AND])->all();
        foreach($condition_and as $and){
            $query->andFilterWhere(self::get_condition($and));
        }
        
        $or_condition=[];       
        $condition_or = Standard::find()->where(['connect'=> Standard::CONNECT_OR])->all();
        foreach($condition_or as $or){
            $or_condition[]= self::get_condition($or);
        }
        if(count($or_condition)>1){
            $query->andFilterWhere(array_merge(['or'],$or_condition));
        }else{
            $query->andFilterWhere($or_condition);
        }
        $ids = $query->select(['c.id'])->column();
           
        static::updateAll(['is_act'=> self::ACT_Y], ['id'=>$ids]);
        static::updateAll(['is_act'=> self::ACT_N],['is_act'=> self::ACT_D]);
        return true;
    }
    
    //设定趋势
    public static function set_trend() {
        
        $datas = static::find()->where(['act_trend'=>self::TREND_WA])->all();
        foreach($datas as $data){
            $model_old= static::find()->where(['corporation_id'=>$data->corporation_id])->andWhere(['<','start_time',$data->start_time])->orderBy(['start_time'=>SORT_DESC])->one();
            $old=$model_old==null?self::ACT_N:$model_old->is_act;
            $v=$data->is_act-$old;
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
    
    //设定健康度
    public static function set_health($group_id=null) {
        
        $end_times = static::find()->where(['health'=>self::HEALTH_WA])->andFilterWhere(['group_id'=>$group_id])->select(['end_time','group_id'])->orderBy(['end_time'=>SORT_DESC])->groupBy(['end_time','group_id'])->limit(5)->all();
        if($end_times){
            
            foreach ($end_times as $end_time){
                $s= static::find()->where(['<=','end_time',$end_time->end_time])->andFilterWhere(['group_id'=>$end_time->group_id])->select(['end_time'])->distinct()->limit(4)->orderBy(['end_time'=>SORT_DESC])->column();//最近四次时间
                $start_time=min($s);
//                $datas=$activity_data=$activity_num=$corporation_allocate=null;
        
                //每个时间段计算
                $datas=static::find()->andFilterWhere(['health'=>self::HEALTH_WA,'end_time'=>$end_time->end_time,'group_id'=>$end_time->group_id])->all();
                //当期历史数据
                $activity_data=ActivityData::find()->andFilterWhere(['statistics_time'=>$end_time->end_time])->select(['corporation_id','projectman_projectcount','projectman_issuecount','codehub_commitcount','codehub_repositorysize','pipeline_executecount','codecheck_execount','codeci_buildtotaltime','testman_totalexecasecount','deploy_execount','projectman_membercount','projectman_storagecount','codehub_all_usercount','pipeline_pipecount','codecheck_usercount','deploy_envcount'])->indexBy('corporation_id')->all();
                //当期活跃周数
                $activity_num=static::find()->where(['is_act'=>self::ACT_Y])->andWhere(['between','end_time',$start_time-1,$end_time->end_time+1])->select(['corporation_id','num'=>'count(corporation_id)'])->groupBy(['corporation_id'])->indexBy('corporation_id')->asArray()->all();
                //企业下拨额
                $corporation_allocate= CorporationMeal::find()->andWhere(['<=','start_time',$end_time->end_time])->andWhere(['>=','end_time',$end_time->end_time])->select(['amount'=>'SUM(amount)','devcloud_count'=>'SUM(devcloud_count)','devcloud_amount'=>'SUM(devcloud_amount)','cloud_amount'=>'SUM(cloud_amount)','corporation_id'])->indexBy('corporation_id')->groupBy(['corporation_id'])->asArray()->all();
                
                
                foreach($datas as $data){
                                        
                    $c=0;
                    $i=0;
                    $m=0;
                    if(isset($activity_data[$data->corporation_id])){
                        $acd=$activity_data[$data->corporation_id];//企业历史数据
                        
                        //设定c
                        if(abs($acd->projectman_projectcount)>20||abs($acd->projectman_issuecount)>30||abs($acd->codehub_commitcount)>20||abs($acd->codehub_repositorysize)>100||abs($acd->pipeline_executecount)>20||abs($acd->codecheck_execount)>20||abs($acd->codeci_buildtotaltime)>200||abs($acd->testman_totalexecasecount)>20||abs($acd->deploy_execount)>20){
                            $c=3;
                        }elseif(abs($acd->projectman_projectcount)>5||abs($acd->projectman_issuecount)>10||abs($acd->codehub_commitcount)>5||abs($acd->codehub_repositorysize)>10||abs($acd->pipeline_executecount)>10||abs($acd->codecheck_execount)>10||abs($acd->codeci_buildtotaltime)>100||abs($acd->testman_totalexecasecount)>10||abs($acd->deploy_execount)>10){
                            $c=2;
                        }elseif(abs($acd->projectman_projectcount)>1||abs($acd->projectman_issuecount)>1||abs($acd->codehub_commitcount)>0||abs($acd->codehub_repositorysize)>0||abs($acd->pipeline_executecount)>0||abs($acd->codecheck_execount)>0||abs($acd->codeci_buildtotaltime)>0||abs($acd->testman_totalexecasecount)>0||abs($acd->deploy_execount)>0){
                            $c=1;
                        }
                        
                        //设定i
                        if(abs($acd->projectman_projectcount)>0||abs($acd->projectman_membercount)>0||abs($acd->projectman_issuecount)>0){
                            $i=$i+0.5;           
                        }
                        if(abs($acd->codehub_repositorycount)>0||abs($acd->codehub_commitcount)>0||abs($acd->codehub_repositorysize)>0){
                            $i=$i+0.5;
                        }                       
                        if(abs($acd->codecheck_codelinecount)>0||abs($acd->codecheck_execount)>0||abs($acd->codecheck_taskcount)>0){
                            $i=$i+0.5;
                        }
                        if(abs($acd->testman_totalexecasecount)>0||abs($acd->testman_casecount)>0){
                            $i=$i+0.5;
                        }
                        if(abs($acd->codeci_buildcount)>0||abs($acd->codeci_buildtotaltime)>0){
                            $i=$i+0.5;
                        }
                        if(abs($acd->deploy_envcount)>0||abs($acd->deploy_execount)>0){
                            $i=$i+0.5;
                        }
                        if(abs($acd->pipeline_pipecount)>0||abs($acd->pipeline_elapse_time)>0){
                            $i=$i+0.5;
                        }
                                               
//                        $m=$acd->projectman_membercount?$acd->projectman_membercount:0;
 
                    }
                    
                    //设定a
                    $a=isset($activity_num[$data->corporation_id])&&$activity_num[$data->corporation_id]['num']>0?$activity_num[$data->corporation_id]['num']:0;
                    
                    $r=0;
                    $v=0;
                    $d=0;
                    if(isset($corporation_allocate[$data->corporation_id])){
                        $set=$corporation_allocate[$data->corporation_id];
                        //设定r
                        $r_num=$set['devcloud_count'];
                        $r= $r_num&&isset($activity_data[$data->corporation_id])?$activity_data[$data->corporation_id]->projectman_membercount/$r_num:1;
                        
                        //设定v
                        if($set['cloud_amount']==0){
                            $v=0;
                        }elseif($set['cloud_amount']>0&&$set['cloud_amount']<=5000){
                            $v=1;
                        }elseif($set['cloud_amount']>5000&&$set['cloud_amount']<=50000){
                            $v=2;
                        }elseif($set['cloud_amount']>50000&&$set['cloud_amount']<=300000){
                            $v=3;
                        }elseif($set['cloud_amount']>300000&&$set['cloud_amount']<=1200000){
                            $v=4;
                        }else{
                            $v=5;
                        }
                        
                        //设定d
                         if($set['devcloud_amount']==0){
                            $d=0;
                        }elseif($set['devcloud_amount']>0&&$set['devcloud_amount']<=1000){
                            $d=1;
                        }elseif($set['devcloud_amount']>1000&&$set['devcloud_amount']<=10000){
                            $d=2;
                        }elseif($set['devcloud_amount']>10000&&$set['devcloud_amount']<=100000){
                            $d=3;
                        }elseif($set['devcloud_amount']>100000&&$set['devcloud_amount']<=200000){
                            $d=4;
                        }else{
                            $d=5;
                        }
                        
                    }
        
                    $data->h_c=$c;
                    $data->h_i=$i;
                    $data->h_a=$a;
                    $data->h_r=$r;
                    $data->h_v=$v;
                    $data->h_d=$d;
                    //$data->h_membercount=$m;
                    
                    $data->h_h=0.2*(0.2*$data->h_v+0.8*$data->h_d)+0.8*(0.2*$data->h_c+0.3*$data->h_i+0.5*$data->h_a)*$data->h_r;
                    
                    if($data->h_h>=2.5){
                        $data->health= self::HEALTH_H5;
                    }elseif($data->h_h>=1.5&&$data->h_h<2.5){
                        $data->health= self::HEALTH_H4;
                    }elseif($data->h_h>=1&&$data->h_h<1.5){
                        $data->health= self::HEALTH_H3;
                    }elseif($data->h_h>=0.5&&$data->h_h<1){
                        $data->health= self::HEALTH_H2;
                    }else{
                        $data->health= self::HEALTH_H1;
                    }     
                    
                    $data->save();
         
                }
            }
        }
        
    }
    
    public static function get_condition($model) {
        $f=$model->type== Standard::TYPE_ADD?'c.'.$model->field:'d.'.$model->field;
        if(preg_match('/[~|-]{1}/',$model->value)){
            $v= explode('~', $model->value);
            if(count($v)<2){
                $v= explode('-', $model->value);
            }              
            $condition=['between',$f,$v[0],$v[1]];
        }elseif (preg_match('/^(<>|>=|>|<=|<|=)/', $model->value, $matches)) {
            $operator=$matches[1];
            $value = substr($model->value, strlen($operator));              
            $condition=[$operator, $f, $value];
        } else {
            $condition=[$f=>$model->value];
        }
        return $condition;
    }
    
    //真实活跃-固定规则
    public static function is_real_activity($model) {
        if(!$model->data){
            return false;
        }
        return ($model->projectman_usercount>0&&$model->projectman_usercount!=$model->projectman_membercount)||$model->projectman_issuecount>0||$model->testman_totalexecasecount>0||$model->codehub_commitcount>0||($model->codehub_commitcount>0&&($model->codecheck_execount>0||$model->codeci_allbuildcount>0||$model->codeci_buildtotaltime>0||$model->deploy_execount>0));
    }
    
    //是否活跃
    public static function is_activity($model) {
        
        if(!$model->data){
            return false;
        }
        $res_or=false;
        $condition_or = Standard::find()->where(['connect'=> Standard::CONNECT_OR])->all();    
        foreach($condition_or as $or){
            $f=$or->type== Standard::TYPE_ADD?$model->{$or->field}:$model->data->{$or->field};
            if(preg_match('/[~|-]{1}/',$or->value)){
                $v= explode('~', $or->value);
                if(count($v)<2){
                    $v= explode('-', $or->value);
                }              
                $res_or=$res_or||($f>=$v[0]&&$f<=$v[1]);
            }elseif (preg_match('/^(<>|>=|>|<=|<|=)/', $or->value, $matches)) {
                $operator=$matches[1];
                $value = substr($or->value, strlen($operator));              
                switch($operator){
                    case '<>':$res_or= $res_or||($f!=$value);break;
                    case '>=':$res_or= $res_or||($f>=$value);break;
                    case '>':$res_or= $res_or||($f>$value);break;
                    case '<=':$res_or= $res_or||($f<=$value);break;
                    case '<':$res_or= $res_or||($f<$value);break;
                    case '=':$res_or= $res_or||($f==$value);break;
                    default:;
                }
            } else {
                $res_or=$res_or||($f==$or->value);
            }
        }
        $res_and=true;
        $condition_and = Standard::find()->where(['connect'=> Standard::CONNECT_AND])->all();    
        foreach($condition_and as $and){
            $f=$and->type == Standard::TYPE_ADD?$model->{$and->field}:$model->data->{$and->field};
            if(preg_match('/[~|-]{1}/',$and->value)){
                $v= explode('~', $and->value);
                if(count($v)<2){
                    $v= explode('-', $and->value);
                }               
                $res_and=$res_and&&($f>=$v[0]&&$f<=$v[1]);
            }elseif (preg_match('/^(<>|>=|>|<=|<|=)/', $and->value, $matches)) {
                $operator=$matches[1];
                $value = substr($and->value, strlen($operator));               
                switch($operator){
                    case '<>':$res_and= $res_and&&($f!=$value);break;
                    case '>=':$res_and= $res_and&&($f>=$value);break;
                    case '>':$res_and= $res_and&&($f>$value);break;
                    case '<=':$res_and= $res_and&&($f<=$value);break;
                    case '<':$res_and= $res_and&&($f<$value);break;
                    case '=':$res_and= $res_and&&($f==$value);break;
                    default:;
                }
            } else {
                $res_and=$res_and&&($f==$and->value);
            }
        }
        return $res_and&&$res_or;
    }
    
    //活跃度趋势
    public static function get_act_line($corporation_id,$start, $end) {
        $data=static::find()->where(['corporation_id'=>$corporation_id])->andFilterWhere(['and',['>=', 'start_time', $start],['<=', 'end_time', $end]])->orderBy(['start_time'=>SORT_ASC])->select(["(CASE WHEN is_act=2 THEN 1 WHEN is_act=1 THEN -1 ELSE 0 END)"])->column();
        return implode(',', $data);
    }
    
    //健康度趋势
    public static function get_health_line($corporation_id,$start, $end) {
        $data=static::find()->where(['corporation_id'=>$corporation_id])->andFilterWhere(['and',['>=', 'start_time', $start],['<=', 'end_time', $end]])->orderBy(['start_time'=>SORT_ASC])->select(['health'])->column();
        return implode(',', $data);
    }
    
    public static function get_health($start, $end,$group_id=null,$allocate=null) {
        return static::find()->andWhere(['group_id'=>$group_id])->andFilterWhere(['is_allocate'=>$allocate])->andFilterWhere(['and',['>=', 'start_time', $start],['<=', 'end_time', $end]])->orderBy(['end_time'=>SORT_ASC,'health'=>SORT_ASC])->select(['start_time'=>'MIN(start_time)','end_time'=>'MAX(end_time)','num'=>'count(health)','health'=>'MAX(health)'])->groupBy(['end_time','health'])->asArray()->all();        
    }
       
    //数据分析，标准差
    public static function deviation_data($column,$start=0,$end=0) {
        $cache = Yii::$app->cache;
        $deviation = $cache->get('deviation');
        if ($deviation === false||!isset($deviation[$column.'_'.$start.'_'.$end])) {
           
            $datas= static::find()->select($column)->where(['>',$column,0])->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end]])->asArray()->column();            
            $num=count($datas);
            if($num>0){
                $sum=0;
                foreach($datas as $data){
                    $sum+=$data;                    
                }
                $avg=$sum/$num;
                $psum=0;
                foreach($datas as $data){
                    $psum+=pow($data-$avg,2);                   
                }
                $min=$avg-sqrt($psum);
                $max=$avg+sqrt($psum);
            }else{
                $min=$max=0;               
            }
            $deviation[$column.'_'.$start.'_'.$end]=['min'=>$min,'max'=>$max];
            $cache->set('deviation', $deviation, null);
        }
        return $deviation[$column.'_'.$start.'_'.$end];
    
    }
 
    public static function get_activity_total($start, $end,$sum=1,$total=1,$annual='',$activity=false,$group_id=null,$allocate=null) {
              
        $query = static::find()->alias('c')->joinWith(['data d'])->andWhere(['c.group_id'=>$group_id])->andFilterWhere(['and',['>=', 'start_time', $start],['<=', 'end_time', $end],['not',['type'=> self::TYPE_DELETE]]])->joinWith(['corporation'])->orderBy(['end_time'=>SORT_ASC,'bd_id'=>SORT_ASC]);
        if($annual=='all'){
            
            
        }elseif($annual){
            $corporation_id= CorporationMeal::find()->where(['annual'=>$annual])->select(['corporation_id'])->distinct()->column();
            $query->andFilterWhere(['c.corporation_id'=>$corporation_id]);
        }
        if($activity){
            if(System::getValue('business_activity_statistics')==2){              
                //最新标准
                $condition_and = Standard::find()->where(['connect'=> Standard::CONNECT_AND])->all();
                foreach($condition_and as $and){
                    $query->andFilterWhere(self::get_condition($and));
                }

                $or_condition=[];       
                $condition_or = Standard::find()->where(['connect'=> Standard::CONNECT_OR])->all();
                foreach($condition_or as $or){
                    $or_condition[]= self::get_condition($or);
                }
                if(count($or_condition)>1){
                    $query->andFilterWhere(array_merge(['or'],$or_condition));
                }else{
                    $query->andFilterWhere($or_condition);
                }
                          
            }else{
                //历史标准
                $query->andWhere(['is_act'=>self::ACT_Y]);  
            }
        }
        if($allocate){
            $query->andWhere(['is_allocate'=>$allocate]);  
        }
        $query->select(['start_time'=>'MIN(start_time)','end_time'=>'MAX(end_time)','num'=>'count(distinct c.corporation_id)','corporation_id'=>'c.corporation_id','bd_id']);
        if($sum){
            //周
            $query->groupBy(['end_time']);       
        }else{
            //月
            $query->groupBy(["FROM_UNIXTIME(end_time, '%Y-%m')"]);
        }
        if(!$total){
            $query->addGroupBy(['bd_id']);
        }
        return $query->asArray()->all();
    }
    
    public static function get_activity_item($start, $end,$items,$annual='',$activity=true,$group_id=null,$allocate=null) {
        $query = static::find()->andWhere(['group_id'=>$group_id])->andFilterWhere(['and',['>=', 'start_time', $start],['<=', 'end_time', $end],['not',['type'=> self::TYPE_DELETE]]]);
        if($annual=='all'){
            
            
        }elseif($annual){
            $corporation_id= CorporationMeal::find()->where(['annual'=>$annual])->select(['corporation_id'])->distinct()->column();
            $query->andFilterWhere(['corporation_id'=>$corporation_id]);
        }
        if($activity){
            $items=is_array($items)?$items:explode(',', $items);
            if(count($items)>1){
                $w[]='or';
                foreach($items as $item){
                    $w[]=['>',$item,0];                  
                }
                $query->andWhere($w);
            }else{
                $query->andWhere(['>',$items[0],0]);
            }
            
        }else{
           $ids=static::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end],['not',['type'=> self::TYPE_DELETE]],['is_act' => ActivityChange::ACT_Y]])->select(['corporation_id'])->distinct()->column();
           $query->andFilterWhere(['not',['corporation_id' => $ids]]);
        }
        if($allocate){
            $query->andWhere(['is_allocate'=>$allocate]);  
        }
        return $query->select(['num'=>'count(distinct corporation_id)'])->scalar();
    }
}
