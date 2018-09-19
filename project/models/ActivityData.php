<?php

namespace project\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%activity_data}}".
 *
 * @property int $id
 * @property int $corporation_id
 * @property int $statistics_time
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
class ActivityData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%activity_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporation_id', 'statistics_time'], 'required'],
            [['corporation_id', 'statistics_time', 'projectman_usercount', 'projectman_projectcount', 'projectman_membercount', 'projectman_versioncount', 'projectman_issuecount', 'codehub_all_usercount', 'codehub_repositorycount', 'codehub_commitcount', 'pipeline_usercount', 'pipeline_pipecount', 'pipeline_executecount', 'codecheck_usercount', 'codecheck_taskcount', 'codecheck_codelinecount', 'codecheck_issuecount', 'codecheck_execount', 'codeci_usercount', 'codeci_buildcount', 'codeci_allbuildcount', 'testman_usercount', 'testman_casecount', 'testman_totalexecasecount', 'deploy_usercount', 'deploy_envcount', 'deploy_execount'], 'integer'],
            [['projectman_storagecount', 'codehub_repositorysize', 'pipeline_elapse_time', 'codeci_buildtotaltime', 'deploy_vmcount'], 'number'],            
            [['corporation_id', 'statistics_time'], 'unique', 'targetAttribute' => ['corporation_id', 'statistics_time'],'message'=>'已经存在此项数据'], 
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
            [[ 'projectman_usercount', 'projectman_projectcount', 'projectman_membercount', 'projectman_versioncount', 'projectman_issuecount', 'codehub_all_usercount', 'codehub_repositorycount', 'codehub_commitcount', 'pipeline_usercount', 'pipeline_pipecount', 'pipeline_executecount', 'codecheck_usercount', 'codecheck_taskcount', 'codecheck_codelinecount', 'codecheck_issuecount', 'codecheck_execount', 'codeci_usercount', 'codeci_buildcount', 'codeci_allbuildcount', 'testman_usercount', 'testman_casecount', 'testman_totalexecasecount', 'deploy_usercount', 'deploy_envcount', 'deploy_execount','projectman_storagecount', 'codehub_repositorysize', 'pipeline_elapse_time', 'codeci_buildtotaltime', 'deploy_vmcount'],'default','value'=>0]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'corporation_id' => '公司',
            'statistics_time' => '统计时间',
            'projectman_usercount' => '项目用户数',
            'projectman_projectcount' => '当前项目数',
            'projectman_membercount' => '当前项目成员数',
            'projectman_versioncount' => '当前迭代数',
            'projectman_issuecount' => '工作项数',
            'projectman_storagecount' => '项目存储空间（M）',
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
            'codeci_buildtotaltime' => '构建时长（秒）',
            'testman_usercount' => '测试管理用户数',
            'testman_casecount' => '用例总数',
            'testman_totalexecasecount' => '用例执行次数',
            'deploy_usercount' => '部署用户数',
            'deploy_envcount' => '当前部署任务数',
            'deploy_execount' => '部署次数',
            'deploy_vmcount' => '节点数',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporation()
    {
        return $this->hasOne(Corporation::className(), ['id' => 'corporation_id']);
    }
    
    public static function get_code() {
       $table = self::tableName();
       $tableSchema = Yii::$app->db->schema->getTableSchema($table);
       $fields = ArrayHelper::getColumn($tableSchema->columns, 'name', 'name');
       unset($fields['id']);
       unset($fields['corporation_id']);
       unset($fields['statistics_time']);
       return $fields;
    }
    
        
//    public static function get_corporationid_by_time($statistics_time='') {   
//       return static::find()->andFilterWhere(['statistics_time'=>$statistics_time])->select(['corporation_id'])->column();
//
//    }
    
//    public static function get_pre_time($statistics_time='',$corporation_id='') {   
//       return static::find()->andFilterWhere(['<','statistics_time',$statistics_time])->andFilterWhere(['corporation_id'=>$corporation_id])->select(['statistics_time'])->orderBy(['statistics_time'=>SORT_DESC])->distinct()->scalar();
//
//    }
//    
//    public static function get_next_time($statistics_time='',$corporation_id='') {   
//       return static::find()->andFilterWhere(['>','statistics_time',$statistics_time])->andFilterWhere(['corporation_id'=>$corporation_id])->select(['statistics_time'])->orderBy(['statistics_time'=>SORT_ASC])->distinct()->scalar();
//
//    }
//    
//    public static function get_data_by_time($statistics_time='',$corporation_id='') {   
//       return static::find()->where(['statistics_time'=>$statistics_time])->andFilterWhere(['corporation_id'=>$corporation_id])->indexBy(['corporation_id'])->asArray()->all();
//
//    }
}
