<?php

namespace project\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use project\models\ActivityChange;
use project\components\CommonHelper;


/**
 * ActivityChangeSearch represents the model behind the search form about `rky\models\ActivityChange`.
 */
class ActivitySearch extends ActivityChange
{
    
    public $corporation;
    public $base_bd;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'corporation_id','type','is_act','act_trend', 'projectman_usercount', 'projectman_projectcount', 'projectman_membercount', 'projectman_versioncount', 'projectman_issuecount', 'codehub_all_usercount', 'codehub_repositorycount', 'codehub_commitcount', 'pipeline_usercount', 'pipeline_pipecount', 'pipeline_executecount', 'codecheck_usercount', 'codecheck_taskcount', 'codecheck_codelinecount', 'codecheck_issuecount', 'codecheck_execount', 'codeci_usercount', 'codeci_buildcount', 'codeci_allbuildcount', 'testman_usercount', 'testman_casecount', 'testman_totalexecasecount', 'deploy_usercount', 'deploy_envcount', 'deploy_execount'], 'integer'],
            [['projectman_storagecount', 'codehub_repositorysize', 'pipeline_elapse_time', 'codeci_buildtotaltime', 'deploy_vmcount'], 'number'],
            [[ 'start_time', 'end_time','corporation','base_bd'],'safe'],
        ];
    }
    
    public static $List = [
        'column'=>[
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
        ]
    ];

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$start=0,$end=0,$sum=1, $pageSize = '')
    {
        $query = ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end]])->joinWith(['corporation']);
        if($sum){
            $query->select(['start_time'=>'MIN(start_time)',
            'end_time'=>'MAX(end_time)',
            'base_bd',
            'corporation_id',
            'is_act'=>'MAX(is_act)',
            'act_trend'=>'SUM(CASE WHEN is_act='.ActivityChange::ACT_Y.' THEN 1  ELSE 0 END)/count(*)',
            'projectman_usercount'=>'SUM(projectman_usercount)',
            'projectman_projectcount'=>'SUM(projectman_projectcount)',
            'projectman_membercount'=>'SUM(projectman_membercount)',
            'projectman_versioncount'=>'SUM(projectman_versioncount)',
            'projectman_issuecount'=>'SUM(projectman_issuecount)',
            'projectman_storagecount'=>'SUM(projectman_storagecount)',
            'codehub_all_usercount' => 'SUM(codehub_all_usercount)',
            'codehub_repositorycount' => 'SUM(codehub_repositorycount)',
            'codehub_commitcount' => 'SUM(codehub_commitcount)',
            'codehub_repositorysize' => 'SUM(codehub_repositorysize)',
            'pipeline_usercount' => 'SUM(pipeline_usercount)',
            'pipeline_pipecount' => 'SUM(pipeline_pipecount)',
            'pipeline_executecount' => 'SUM(pipeline_executecount)',
            'pipeline_elapse_time' => 'SUM(pipeline_elapse_time)',
            'codecheck_usercount' => 'SUM(codecheck_usercount)',
            'codecheck_taskcount' => 'SUM(codecheck_taskcount)',
            'codecheck_codelinecount' => 'SUM(codecheck_codelinecount)',
            'codecheck_issuecount' => 'SUM(codecheck_issuecount)',
            'codecheck_execount' => 'SUM(codecheck_execount)',
            'codeci_usercount' => 'SUM(codeci_usercount)',
            'codeci_buildcount' => 'SUM(codeci_buildcount)',
            'codeci_allbuildcount' => 'SUM(codeci_allbuildcount)',
            'codeci_buildtotaltime' => 'SUM(codeci_buildtotaltime)',
            'testman_usercount' => 'SUM(testman_usercount)',
            'testman_casecount' => 'SUM(testman_casecount)',
            'testman_totalexecasecount' => 'SUM(testman_totalexecasecount)',
            'deploy_usercount' => 'SUM(deploy_usercount)',
            'deploy_envcount' => 'SUM(deploy_envcount)',
            'deploy_execount' => 'SUM(deploy_execount)',
            'deploy_vmcount' => 'SUM(deploy_vmcount)'])->groupBy('corporation_id');
        }

        // add conditions that should always apply here
       if ($pageSize > 0) {
                 $dataProvider = new ActiveDataProvider([
            'query' => $query,
                      'pagination' => [
                    'pageSize' => $pageSize,
                ],
             'sort' => ['defaultOrder' => [
                    'start_time' => SORT_ASC,
                ]],
        ]);
      }else{
           $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => [
                    'start_time' => SORT_ASC,
                ]],
        ]);
         }
  
        
        $sort = $dataProvider->getSort();
        $sort->attributes['corporation'] = [
            'asc' => ['corporation_id' => SORT_ASC,'end_time'=>SORT_ASC],
            'desc' => ['corporation_id' => SORT_DESC,'end_time'=>SORT_ASC],          
        ];
        $sort->attributes['base_bd'] = [
            'asc' => ['base_bd' => SORT_ASC,'corporation_id' => SORT_ASC,'end_time'=>SORT_ASC],
            'desc' => ['base_bd' => SORT_DESC,'corporation_id' => SORT_DESC,'end_time'=>SORT_ASC],          
        ];
        $sort->attributes['start_time'] = [
            'asc' => ['start_time' => SORT_ASC,'corporation_id'=>SORT_ASC],
            'desc' => ['start_time' => SORT_DESC,'corporation_id'=>SORT_ASC],          
        ];
        $sort->attributes['act_trend'] = [
            'asc' => ['act_trend' => SORT_ASC,'corporation_id'=>SORT_ASC],
            'desc' => ['act_trend' => SORT_DESC,'corporation_id'=>SORT_ASC],          
        ];
        $dataProvider->setSort($sort);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'base_bd' => $this->base_bd,
            'act_trend'=>$this->act_trend,
            'corporation_id' => $this->corporation_id,
        ]);
        
        $query->andFilterWhere(['or like', 'base_company_name', explode('|', trim($this->corporation))]);
        
        if($sum){
            $ids = ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end],['is_act' => ActivityChange::ACT_Y]])->select(['corporation_id'])->distinct()->column();
            if($this->is_act== ActivityChange::ACT_Y){
                $query->andFilterWhere(['corporation_id' => $ids]);
            }elseif($this->is_act== ActivityChange::ACT_N){
                $query->andFilterWhere(['not',['corporation_id' => $ids]]);
            }
            
        }else{
            $query->andFilterWhere([ 'is_act' => $this->is_act]);
        }
        

        if($this->projectman_usercount){
            CommonHelper::searchNumber($query, 'projectman_usercount', $this->projectman_usercount);
        }
        if($this->projectman_projectcount){
            CommonHelper::searchNumber($query, 'projectman_projectcount', $this->projectman_projectcount);
        }
        if($this->projectman_membercount){
            CommonHelper::searchNumber($query, 'projectman_membercount', $this->projectman_membercount);
        }
        if($this->projectman_versioncount){
            CommonHelper::searchNumber($query, 'projectman_versioncount', $this->projectman_versioncount);
        }
        if($this->projectman_issuecount){
            CommonHelper::searchNumber($query, 'projectman_issuecount', $this->projectman_issuecount);
        }
        if($this->projectman_storagecount){
            CommonHelper::searchNumber($query, 'projectman_storagecount', $this->projectman_storagecount);
        }
        if($this->codehub_all_usercount){
            CommonHelper::searchNumber($query, 'codehub_all_usercount', $this->codehub_all_usercount);
        }
        if($this->codehub_repositorycount){
            CommonHelper::searchNumber($query, 'codehub_repositorycount', $this->codehub_repositorycount);
        }
        if($this->codehub_commitcount){
            CommonHelper::searchNumber($query, 'codehub_commitcount', $this->codehub_commitcount);
        }
        if($this->codehub_repositorysize){
            CommonHelper::searchNumber($query, 'codehub_repositorysize', $this->codehub_repositorysize);
        }
        if($this->pipeline_usercount){
            CommonHelper::searchNumber($query, 'pipeline_usercount', $this->pipeline_usercount);
        }
        if($this->pipeline_pipecount){
            CommonHelper::searchNumber($query, 'pipeline_pipecount', $this->pipeline_pipecount);
        }
        if($this->pipeline_executecount){
            CommonHelper::searchNumber($query, 'pipeline_executecount', $this->pipeline_executecount);
        }
        if($this->pipeline_elapse_time){
            CommonHelper::searchNumber($query, 'pipeline_elapse_time', $this->pipeline_elapse_time);
        }
        if($this->codecheck_usercount){
            CommonHelper::searchNumber($query, 'codecheck_usercount', $this->codecheck_usercount);
        }
        if($this->codecheck_taskcount){
            CommonHelper::searchNumber($query, 'codecheck_taskcount', $this->codecheck_taskcount);
        }
        if($this->codecheck_codelinecount){
            CommonHelper::searchNumber($query, 'codecheck_codelinecount', $this->codecheck_codelinecount);
        }
        if($this->codecheck_issuecount){
            CommonHelper::searchNumber($query, 'codecheck_issuecount', $this->codecheck_issuecount);
        }
        if($this->codecheck_execount){
            CommonHelper::searchNumber($query, 'codecheck_execount', $this->codecheck_execount);
        }
        if($this->codeci_usercount){
            CommonHelper::searchNumber($query, 'codeci_usercount', $this->codeci_usercount);
        }
        if($this->codeci_buildcount){
            CommonHelper::searchNumber($query, 'codeci_buildcount', $this->codeci_buildcount);
        }
        if($this->codeci_allbuildcount){
            CommonHelper::searchNumber($query, 'codeci_allbuildcount', $this->codeci_allbuildcount);
        }
        if($this->codeci_buildtotaltime){
            CommonHelper::searchNumber($query, 'codeci_buildtotaltime', $this->codeci_buildtotaltime);
        }
        if($this->testman_usercount){
            CommonHelper::searchNumber($query, 'testman_usercount', $this->testman_usercount);
        }
        if($this->testman_casecount){
            CommonHelper::searchNumber($query, 'testman_casecount', $this->testman_casecount);
        }
        if($this->testman_totalexecasecount){
            CommonHelper::searchNumber($query, 'testman_totalexecasecount', $this->testman_totalexecasecount);
        }
        if($this->deploy_usercount){
            CommonHelper::searchNumber($query, 'deploy_usercount', $this->deploy_usercount);
        }
        if($this->deploy_envcount){
            CommonHelper::searchNumber($query, 'deploy_envcount', $this->deploy_envcount);
        }
        if($this->deploy_execount){
            CommonHelper::searchNumber($query, 'deploy_execount', $this->deploy_execount);
        }
        if($this->deploy_vmcount){
            CommonHelper::searchNumber($query, 'deploy_vmcount', $this->deploy_vmcount);
        }
        
        
      
        return $dataProvider;
    }
}
