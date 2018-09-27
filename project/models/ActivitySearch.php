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
    public $projectman_usercount_d;
    public $projectman_projectcount_d;
    public $projectman_membercount_d;
    public $projectman_versioncount_d;
    public $projectman_issuecount_d;
    public $codehub_all_usercount_d;
    public $codehub_repositorycount_d;
    public $codehub_commitcount_d;
    public $pipeline_usercount_d;
    public $pipeline_pipecount_d;
    public $pipeline_executecount_d;
    public $codecheck_usercount_d;
    public $codecheck_taskcount_d;
    public $codecheck_codelinecount_d;
    public $codecheck_issuecount_d;
    public $codecheck_execount_d;
    public $codeci_usercount_d;
    public $codeci_buildcount_d;
    public $codeci_allbuildcount_d;
    public $testman_usercount_d;
    public $testman_casecount_d;
    public $testman_totalexecasecount_d;
    public $deploy_usercount_d;
    public $deploy_envcount_d;
    public $deploy_execount_d;
    public $projectman_storagecount_d;
    public $codehub_repositorysize_d;
    public $pipeline_elapse_time_d;
    public $codeci_buildtotaltime_d;
    public $deploy_vmcount_d;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'corporation_id','type','is_act','act_trend'], 'integer'],
            [[ 'projectman_usercount', 'projectman_projectcount', 'projectman_membercount', 'projectman_versioncount', 'projectman_issuecount', 'codehub_all_usercount', 'codehub_repositorycount', 'codehub_commitcount', 'pipeline_usercount', 'pipeline_pipecount', 'pipeline_executecount', 'codecheck_usercount', 'codecheck_taskcount', 'codecheck_codelinecount', 'codecheck_issuecount', 'codecheck_execount', 'codeci_usercount', 'codeci_buildcount', 'codeci_allbuildcount', 'testman_usercount', 'testman_casecount', 'testman_totalexecasecount', 'deploy_usercount', 'deploy_envcount', 'deploy_execount','projectman_storagecount', 'codehub_repositorysize', 'pipeline_elapse_time', 'codeci_buildtotaltime', 'deploy_vmcount', 'projectman_usercount_d', 'projectman_projectcount_d', 'projectman_membercount_d', 'projectman_versioncount_d', 'projectman_issuecount_d', 'codehub_all_usercount_d', 'codehub_repositorycount_d', 'codehub_commitcount_d', 'pipeline_usercount_d', 'pipeline_pipecount_d', 'pipeline_executecount_d', 'codecheck_usercount_d', 'codecheck_taskcount_d', 'codecheck_codelinecount_d', 'codecheck_issuecount_d', 'codecheck_execount_d', 'codeci_usercount_d', 'codeci_buildcount_d', 'codeci_allbuildcount_d', 'testman_usercount_d', 'testman_casecount_d', 'testman_totalexecasecount_d', 'deploy_usercount_d', 'deploy_envcount_d', 'deploy_execount_d','projectman_storagecount_d', 'codehub_repositorysize_d', 'pipeline_elapse_time_d', 'codeci_buildtotaltime_d', 'deploy_vmcount_d'], 'safe'],
            [[ 'start_time', 'end_time','corporation','base_bd'],'safe'],
        ];
    }
    
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
        $query = ActivityChange::find()->alias('c')->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end]])->joinWith(['corporation','data d']);
        if($sum){
            $query->select([
                'start_time'=>'MIN(start_time)',
                'end_time'=>'MAX(end_time)',
                'base_bd',
                'corporation_id'=>'c.corporation_id',
                'is_act'=>'MAX(is_act)',
                'act_trend'=>'SUM(CASE WHEN is_act='.ActivityChange::ACT_Y.' THEN 1  ELSE 0 END)/count(*)',
                'projectman_usercount'=>'SUM(c.projectman_usercount)',
                'projectman_projectcount'=>'SUM(c.projectman_projectcount)',
                'projectman_membercount'=>'SUM(c.projectman_membercount)',
                'projectman_versioncount'=>'SUM(c.projectman_versioncount)',
                'projectman_issuecount'=>'SUM(c.projectman_issuecount)',
                'projectman_storagecount'=>'SUM(c.projectman_storagecount)',
                'codehub_all_usercount' => 'SUM(c.codehub_all_usercount)',
                'codehub_repositorycount' => 'SUM(c.codehub_repositorycount)',
                'codehub_commitcount' => 'SUM(c.codehub_commitcount)',
                'codehub_repositorysize' => 'SUM(c.codehub_repositorysize)',
                'pipeline_usercount' => 'SUM(c.pipeline_usercount)',
                'pipeline_pipecount' => 'SUM(c.pipeline_pipecount)',
                'pipeline_executecount' => 'SUM(c.pipeline_executecount)',
                'pipeline_elapse_time' => 'SUM(c.pipeline_elapse_time)',
                'codecheck_usercount' => 'SUM(c.codecheck_usercount)',
                'codecheck_taskcount' => 'SUM(c.codecheck_taskcount)',
                'codecheck_codelinecount' => 'SUM(c.codecheck_codelinecount)',
                'codecheck_issuecount' => 'SUM(c.codecheck_issuecount)',
                'codecheck_execount' => 'SUM(c.codecheck_execount)',
                'codeci_usercount' => 'SUM(c.codeci_usercount)',
                'codeci_buildcount' => 'SUM(c.codeci_buildcount)',
                'codeci_allbuildcount' => 'SUM(c.codeci_allbuildcount)',
                'codeci_buildtotaltime' => 'SUM(c.codeci_buildtotaltime)',
                'testman_usercount' => 'SUM(c.testman_usercount)',
                'testman_casecount' => 'SUM(c.testman_casecount)',
                'testman_totalexecasecount' => 'SUM(c.testman_totalexecasecount)',
                'deploy_usercount' => 'SUM(c.deploy_usercount)',
                'deploy_envcount' => 'SUM(c.deploy_envcount)',
                'deploy_execount' => 'SUM(c.deploy_execount)',
                'deploy_vmcount' => 'SUM(c.deploy_vmcount)'])->groupBy('corporation_id');
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
        
//        $sort->attributes['projectman_usercount_d'] = [
//            'asc' => ['d.projectman_usercount' => SORT_ASC],
//            'desc' => ['d.projectman_usercount' => SORT_DESC],          
//        ];
        $dataProvider->setSort($sort);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'base_bd' => $this->base_bd,
            'act_trend'=>$this->act_trend,
        ]);
        
        $query->andFilterWhere(['or like', 'base_company_name', explode('|', trim($this->corporation))]);
        
        if($sum){
            $ids = ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end],['is_act' => ActivityChange::ACT_Y]])->select(['corporation_id'])->distinct()->column();
            if($this->is_act== ActivityChange::ACT_Y){
                $query->andFilterWhere(['c.corporation_id' => $ids]);
            }elseif($this->is_act== ActivityChange::ACT_N){
                $query->andFilterWhere(['not',['c.corporation_id' => $ids]]);
            }
            
        }else{
            $query->andFilterWhere([ 'is_act' => $this->is_act]);
        }
        

        if($this->projectman_usercount){
            CommonHelper::searchNumber($query, 'c.projectman_usercount', $this->projectman_usercount);
        }
        if($this->projectman_projectcount){
            CommonHelper::searchNumber($query, 'c.projectman_projectcount', $this->projectman_projectcount);
        }
        if($this->projectman_membercount){
            CommonHelper::searchNumber($query, 'c.projectman_membercount', $this->projectman_membercount);
        }
        if($this->projectman_versioncount){
            CommonHelper::searchNumber($query, 'c.projectman_versioncount', $this->projectman_versioncount);
        }
        if($this->projectman_issuecount){
            CommonHelper::searchNumber($query, 'c.projectman_issuecount', $this->projectman_issuecount);
        }
        if($this->projectman_storagecount){
            CommonHelper::searchNumber($query, 'c.projectman_storagecount', $this->projectman_storagecount);
        }
        if($this->codehub_all_usercount){
            CommonHelper::searchNumber($query, 'c.codehub_all_usercount', $this->codehub_all_usercount);
        }
        if($this->codehub_repositorycount){
            CommonHelper::searchNumber($query, 'c.codehub_repositorycount', $this->codehub_repositorycount);
        }
        if($this->codehub_commitcount){
            CommonHelper::searchNumber($query, 'c.codehub_commitcount', $this->codehub_commitcount);
        }
        if($this->codehub_repositorysize){
            CommonHelper::searchNumber($query, 'c.codehub_repositorysize', $this->codehub_repositorysize);
        }
        if($this->pipeline_usercount){
            CommonHelper::searchNumber($query, 'c.pipeline_usercount', $this->pipeline_usercount);
        }
        if($this->pipeline_pipecount){
            CommonHelper::searchNumber($query, 'c.pipeline_pipecount', $this->pipeline_pipecount);
        }
        if($this->pipeline_executecount){
            CommonHelper::searchNumber($query, 'c.pipeline_executecount', $this->pipeline_executecount);
        }
        if($this->pipeline_elapse_time){
            CommonHelper::searchNumber($query, 'c.pipeline_elapse_time', $this->pipeline_elapse_time);
        }
        if($this->codecheck_usercount){
            CommonHelper::searchNumber($query, 'c.codecheck_usercount', $this->codecheck_usercount);
        }
        if($this->codecheck_taskcount){
            CommonHelper::searchNumber($query, 'c.codecheck_taskcount', $this->codecheck_taskcount);
        }
        if($this->codecheck_codelinecount){
            CommonHelper::searchNumber($query,'c.codecheck_codelinecount', $this->codecheck_codelinecount);
        }
        if($this->codecheck_issuecount){
            CommonHelper::searchNumber($query, 'c.codecheck_issuecount', $this->codecheck_issuecount);
        }
        if($this->codecheck_execount){
            CommonHelper::searchNumber($query, 'c.codecheck_execount', $this->codecheck_execount);
        }
        if($this->codeci_usercount){
            CommonHelper::searchNumber($query, 'c.codeci_usercount', $this->codeci_usercount);
        }
        if($this->codeci_buildcount){
            CommonHelper::searchNumber($query, 'c.codeci_buildcount', $this->codeci_buildcount);
        }
        if($this->codeci_allbuildcount){
            CommonHelper::searchNumber($query, 'c.codeci_allbuildcount', $this->codeci_allbuildcount);
        }
        if($this->codeci_buildtotaltime){
            CommonHelper::searchNumber($query, 'c.codeci_buildtotaltime', $this->codeci_buildtotaltime);
        }
        if($this->testman_usercount){
            CommonHelper::searchNumber($query, 'c.testman_usercount', $this->testman_usercount);
        }
        if($this->testman_casecount){
            CommonHelper::searchNumber($query, 'c.testman_casecount', $this->testman_casecount);
        }
        if($this->testman_totalexecasecount){
            CommonHelper::searchNumber($query, 'c.testman_totalexecasecount', $this->testman_totalexecasecount);
        }
        if($this->deploy_usercount){
            CommonHelper::searchNumber($query, 'c.deploy_usercount', $this->deploy_usercount);
        }
        if($this->deploy_envcount){
            CommonHelper::searchNumber($query, 'c.deploy_envcount', $this->deploy_envcount);
        }
        if($this->deploy_execount){
            CommonHelper::searchNumber($query, 'c.deploy_execount', $this->deploy_execount);
        }
        if($this->deploy_vmcount){
            CommonHelper::searchNumber($query, 'c.deploy_vmcount', $this->deploy_vmcount);
        }
        
        if($this->projectman_usercount_d){
            CommonHelper::searchNumber($query, 'd.projectman_usercount', $this->projectman_usercount_d);
        }
        if($this->projectman_projectcount_d){
            CommonHelper::searchNumber($query, 'd.projectman_projectcount', $this->projectman_projectcount_d);
        }
        if($this->projectman_membercount_d){
            CommonHelper::searchNumber($query, 'd.projectman_membercount', $this->projectman_membercount_d);
        }
        if($this->projectman_versioncount_d){
            CommonHelper::searchNumber($query, 'd.projectman_versioncount', $this->projectman_versioncount_d);
        }
        if($this->projectman_issuecount_d){
            CommonHelper::searchNumber($query, 'd.projectman_issuecount', $this->projectman_issuecount_d);
        }
        if($this->projectman_storagecount_d){
            CommonHelper::searchNumber($query, 'd.projectman_storagecount', $this->projectman_storagecount_d);
        }
        if($this->codehub_all_usercount_d){
            CommonHelper::searchNumber($query, 'd.codehub_all_usercount', $this->codehub_all_usercount_d);
        }
        if($this->codehub_repositorycount_d){
            CommonHelper::searchNumber($query, 'd.codehub_repositorycount', $this->codehub_repositorycount_d);
        }
        if($this->codehub_commitcount_d){
            CommonHelper::searchNumber($query, 'd.codehub_commitcount', $this->codehub_commitcount_d);
        }
        if($this->codehub_repositorysize_d){
            CommonHelper::searchNumber($query, 'd.codehub_repositorysize', $this->codehub_repositorysize_d);
        }
        if($this->pipeline_usercount_d){
            CommonHelper::searchNumber($query, 'd.pipeline_usercount', $this->pipeline_usercount_d);
        }
        if($this->pipeline_pipecount_d){
            CommonHelper::searchNumber($query, 'd.pipeline_pipecount', $this->pipeline_pipecount_d);
        }
        if($this->pipeline_executecount_d){
            CommonHelper::searchNumber($query, 'd.pipeline_executecount', $this->pipeline_executecount_d);
        }
        if($this->pipeline_elapse_time_d){
            CommonHelper::searchNumber($query, 'd.pipeline_elapse_time', $this->pipeline_elapse_time_d);
        }
        if($this->codecheck_usercount_d){
            CommonHelper::searchNumber($query, 'd.codecheck_usercount', $this->codecheck_usercount_d);
        }
        if($this->codecheck_taskcount_d){
            CommonHelper::searchNumber($query, 'd.codecheck_taskcount', $this->codecheck_taskcount_d);
        }
        if($this->codecheck_codelinecount_d){
            CommonHelper::searchNumber($query,'d.codecheck_codelinecount', $this->codecheck_codelinecount_d);
        }
        if($this->codecheck_issuecount_d){
            CommonHelper::searchNumber($query, 'd.codecheck_issuecount', $this->codecheck_issuecount_d);
        }
        if($this->codecheck_execount_d){
            CommonHelper::searchNumber($query, 'd.codecheck_execount', $this->codecheck_execount_d);
        }
        if($this->codeci_usercount_d){
            CommonHelper::searchNumber($query, 'd.codeci_usercount', $this->codeci_usercount_d);
        }
        if($this->codeci_buildcount_d){
            CommonHelper::searchNumber($query, 'd.codeci_buildcount', $this->codeci_buildcount_d);
        }
        if($this->codeci_allbuildcount_d){
            CommonHelper::searchNumber($query, 'd.codeci_allbuildcount', $this->codeci_allbuildcount_d);
        }
        if($this->codeci_buildtotaltime_d){
            CommonHelper::searchNumber($query, 'd.codeci_buildtotaltime', $this->codeci_buildtotaltime_d);
        }
        if($this->testman_usercount_d){
            CommonHelper::searchNumber($query, 'd.testman_usercount', $this->testman_usercount_d);
        }
        if($this->testman_casecount_d){
            CommonHelper::searchNumber($query, 'd.testman_casecount', $this->testman_casecount_d);
        }
        if($this->testman_totalexecasecount_d){
            CommonHelper::searchNumber($query, 'd.testman_totalexecasecount', $this->testman_totalexecasecount_d);
        }
        if($this->deploy_usercount_d){
            CommonHelper::searchNumber($query, 'd.deploy_usercount', $this->deploy_usercount_d);
        }
        if($this->deploy_envcount_d){
            CommonHelper::searchNumber($query, 'd.deploy_envcount', $this->deploy_envcount_d);
        }
        if($this->deploy_execount_d){
            CommonHelper::searchNumber($query, 'd.deploy_execount', $this->deploy_execount_d);
        }
        if($this->deploy_vmcount_d){
            CommonHelper::searchNumber($query, 'd.deploy_vmcount', $this->deploy_vmcount_d);
        }        
        
      
        return $dataProvider;
    }
}
