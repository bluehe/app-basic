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
    public $bd_id;
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
            [['id','bd_id', 'corporation_id','type','is_act','act_trend'], 'integer'],
            [[ 'projectman_usercount', 'projectman_projectcount', 'projectman_membercount', 'projectman_versioncount', 'projectman_issuecount', 'codehub_all_usercount', 'codehub_repositorycount', 'codehub_commitcount', 'pipeline_usercount', 'pipeline_pipecount', 'pipeline_executecount', 'codecheck_usercount', 'codecheck_taskcount', 'codecheck_codelinecount', 'codecheck_issuecount', 'codecheck_execount', 'codeci_usercount', 'codeci_buildcount', 'codeci_allbuildcount', 'testman_usercount', 'testman_casecount', 'testman_totalexecasecount', 'deploy_usercount', 'deploy_envcount', 'deploy_execount','projectman_storagecount', 'codehub_repositorysize', 'pipeline_elapse_time', 'codeci_buildtotaltime', 'deploy_vmcount', 'projectman_usercount_d', 'projectman_projectcount_d', 'projectman_membercount_d', 'projectman_versioncount_d', 'projectman_issuecount_d', 'codehub_all_usercount_d', 'codehub_repositorycount_d', 'codehub_commitcount_d', 'pipeline_usercount_d', 'pipeline_pipecount_d', 'pipeline_executecount_d', 'codecheck_usercount_d', 'codecheck_taskcount_d', 'codecheck_codelinecount_d', 'codecheck_issuecount_d', 'codecheck_execount_d', 'codeci_usercount_d', 'codeci_buildcount_d', 'codeci_allbuildcount_d', 'testman_usercount_d', 'testman_casecount_d', 'testman_totalexecasecount_d', 'deploy_usercount_d', 'deploy_envcount_d', 'deploy_execount_d','projectman_storagecount_d', 'codehub_repositorysize_d', 'pipeline_elapse_time_d', 'codeci_buildtotaltime_d', 'deploy_vmcount_d'], 'safe'],
            [[ 'start_time', 'end_time','corporation'],'safe'],
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
        $subQuery = ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end]])->orderBy(['start_time'=>SORT_DESC])->limit(ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end]])->count());
        $query = ActivityChange::find()->from(['c'=>$subQuery])->joinWith(['corporation','data d']);
        if($sum){
            $query->select([
                'start_time'=>'MIN(start_time)',
                'end_time'=>'MAX(end_time)',
                'bd_id',
                'corporation_id'=>'c.corporation_id',
                'is_act'=>'MAX(is_act)',
                'act_trend'=>'SUM(CASE WHEN is_act='.ActivityChange::ACT_Y.' THEN 1  ELSE 0 END)/count(*)',
                'projectman_usercount' => 'SUM(c.projectman_usercount)',
                'projectman_projectcount' => 'SUM(c.projectman_projectcount)',
                'projectman_membercount' => 'SUM(c.projectman_membercount)',
                'projectman_versioncount' => 'SUM(c.projectman_versioncount)',
                'projectman_issuecount' => 'SUM(c.projectman_issuecount)',
                'projectman_storagecount' => 'SUM(c.projectman_storagecount)',
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
                'deploy_vmcount' => 'SUM(c.deploy_vmcount)',
                'projectman_usercount_d'=>'d.projectman_usercount',
                'projectman_projectcount_d' => 'd.projectman_projectcount',
                'projectman_membercount_d' => 'd.projectman_membercount',
                'projectman_versioncount_d' => 'd.projectman_versioncount',
                'projectman_issuecount_d' => 'd.projectman_issuecount',
                'projectman_storagecount_d' => 'd.projectman_storagecount',
                'codehub_all_usercount_d' => 'd.codehub_all_usercount',
                'codehub_repositorycount_d' => 'd.codehub_repositorycount',
                'codehub_commitcount_d' => 'd.codehub_commitcount',
                'codehub_repositorysize_d' => 'd.codehub_repositorysize',
                'pipeline_usercount_d' => 'd.pipeline_usercount',
                'pipeline_pipecount_d' => 'd.pipeline_pipecount',
                'pipeline_executecount_d' => 'd.pipeline_executecount',
                'pipeline_elapse_time_d' => 'd.pipeline_elapse_time',
                'codecheck_usercount_d' => 'd.codecheck_usercount',
                'codecheck_taskcount_d' => 'd.codecheck_taskcount',
                'codecheck_codelinecount_d' => 'd.codecheck_codelinecount',
                'codecheck_issuecount_d' => 'd.codecheck_issuecount',
                'codecheck_execount_d' => 'd.codecheck_execount',
                'codeci_usercount_d' => 'd.codeci_usercount',
                'codeci_buildcount_d' => 'd.codeci_buildcount',
                'codeci_allbuildcount_d' => 'd.codeci_allbuildcount',
                'codeci_buildtotaltime_d' => 'd.codeci_buildtotaltime',
                'testman_usercount_d' => 'd.testman_usercount',
                'testman_casecount_d' => 'd.testman_casecount',
                'testman_totalexecasecount_d' => 'd.testman_totalexecasecount',
                'deploy_usercount_d' => 'd.deploy_usercount',
                'deploy_envcount_d' => 'd.deploy_envcount',
                'deploy_execount_d' => 'd.deploy_execount',
                'deploy_vmcount_d' => 'd.deploy_vmcount',
            ])->groupBy('corporation_id');
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
//        $sort->attributes['base_bd'] = [
//            'asc' => ['base_bd' => SORT_ASC,'corporation_id' => SORT_ASC,'end_time'=>SORT_ASC],
//            'desc' => ['base_bd' => SORT_DESC,'corporation_id' => SORT_DESC,'end_time'=>SORT_ASC],          
//        ];
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
        

        
        
        $business_activity_search=(System::getValue('business_activity_search')==2)&&$sum;//搜索方式
        
        if($business_activity_search&&$this->bd_id){
            $query->andHaving(['bd_id'=>$this->bd_id]);
        }else{
            $query->andFilterWhere(['bd_id'=>$this->bd_id]);
        }
        
        foreach(ActivityChange::$List['column_activity'] as $key=>$v){
            if($this->$key){
                CommonHelper::searchNumber($query, $business_activity_search?$key:"c.$key", $this->$key,$business_activity_search);
            }
            if($this->{$key.'_d'}){
                CommonHelper::searchNumber($query, $business_activity_search?$key.'_d':"d.$key", $this->{$key.'_d'},$business_activity_search);
            }
        }
        
//        if($this->projectman_usercount){
//            CommonHelper::searchNumber($query, $business_activity_search?'projectman_usercount':'c.projectman_usercount', $this->projectman_usercount,$business_activity_search);
//        }
//        if($this->projectman_projectcount){
//            CommonHelper::searchNumber($query, $business_activity_search?'projectman_projectcount':'c.projectman_projectcount', $this->projectman_projectcount,$business_activity_search);
//        }
//        if($this->projectman_membercount){
//            CommonHelper::searchNumber($query, $business_activity_search?'projectman_membercount':'c.projectman_membercount', $this->projectman_membercount,$business_activity_search);
//        }
//        if($this->projectman_versioncount){
//            CommonHelper::searchNumber($query, $business_activity_search?'projectman_versioncount':'c.projectman_versioncount', $this->projectman_versioncount,$business_activity_search);
//        }
//        if($this->projectman_issuecount){
//            CommonHelper::searchNumber($query, $business_activity_search?'projectman_issuecount':'c.projectman_issuecount', $this->projectman_issuecount,$business_activity_search);
//        }
//        if($this->projectman_storagecount){
//            CommonHelper::searchNumber($query, $business_activity_search?'projectman_storagecount':'c.projectman_storagecount', $this->projectman_storagecount,$business_activity_search);
//        }
//        if($this->codehub_all_usercount){
//            CommonHelper::searchNumber($query, $business_activity_search?'codehub_all_usercount':'c.codehub_all_usercount', $this->codehub_all_usercount,$business_activity_search);
//        }
//        if($this->codehub_repositorycount){
//            CommonHelper::searchNumber($query, $business_activity_search?'codehub_repositorycount':'c.codehub_repositorycount', $this->codehub_repositorycount,$business_activity_search);
//        }
//        if($this->codehub_commitcount){
//            CommonHelper::searchNumber($query, $business_activity_search?'codehub_commitcount':'c.codehub_commitcount', $this->codehub_commitcount,$business_activity_search);
//        }
//        if($this->codehub_repositorysize){
//            CommonHelper::searchNumber($query, $business_activity_search?'codehub_repositorysize':'c.codehub_repositorysize', $this->codehub_repositorysize,$business_activity_search);
//        }
//        if($this->pipeline_usercount){
//            CommonHelper::searchNumber($query, $business_activity_search?'pipeline_usercount':'c.pipeline_usercount', $this->pipeline_usercount,$business_activity_search);
//        }
//        if($this->pipeline_pipecount){
//            CommonHelper::searchNumber($query, $business_activity_search?'pipeline_pipecount':'c.pipeline_pipecount', $this->pipeline_pipecount,$business_activity_search);
//        }
//        if($this->pipeline_executecount){
//            CommonHelper::searchNumber($query, $business_activity_search?'pipeline_executecount':'c.pipeline_executecount', $this->pipeline_executecount,$business_activity_search);
//        }
//        if($this->pipeline_elapse_time){
//            CommonHelper::searchNumber($query, $business_activity_search?'pipeline_elapse_time':'c.pipeline_elapse_time', $this->pipeline_elapse_time,$business_activity_search);
//        }
//        if($this->codecheck_usercount){
//            CommonHelper::searchNumber($query, $business_activity_search?'codecheck_usercount':'c.codecheck_usercount', $this->codecheck_usercount,$business_activity_search);
//        }
//        if($this->codecheck_taskcount){
//            CommonHelper::searchNumber($query, $business_activity_search?'codecheck_taskcount':'c.codecheck_taskcount', $this->codecheck_taskcount,$business_activity_search);
//        }
//        if($this->codecheck_codelinecount){
//            CommonHelper::searchNumber($query,$business_activity_search?'codecheck_codelinecount':'c.codecheck_codelinecount', $this->codecheck_codelinecount,$business_activity_search);
//        }
//        if($this->codecheck_issuecount){
//            CommonHelper::searchNumber($query, $business_activity_search?'codecheck_issuecount':'c.codecheck_issuecount', $this->codecheck_issuecount,$business_activity_search);
//        }
//        if($this->codecheck_execount){
//            CommonHelper::searchNumber($query, $business_activity_search?'codecheck_execount':'c.codecheck_execount', $this->codecheck_execount,$business_activity_search);
//        }
//        if($this->codeci_usercount){
//            CommonHelper::searchNumber($query, $business_activity_search?'codeci_usercount':'c.codeci_usercount', $this->codeci_usercount,$business_activity_search);
//        }
//        if($this->codeci_buildcount){
//            CommonHelper::searchNumber($query, $business_activity_search?'codeci_buildcount':'c.codeci_buildcount', $this->codeci_buildcount,$business_activity_search);
//        }
//        if($this->codeci_allbuildcount){
//            CommonHelper::searchNumber($query, $business_activity_search?'codeci_allbuildcount':'c.codeci_allbuildcount', $this->codeci_allbuildcount,$business_activity_search);
//        }
//        if($this->codeci_buildtotaltime){
//            CommonHelper::searchNumber($query, $business_activity_search?'codeci_buildtotaltime':'c.codeci_buildtotaltime', $this->codeci_buildtotaltime,$business_activity_search);
//        }
//        if($this->testman_usercount){
//            CommonHelper::searchNumber($query, $business_activity_search?'testman_usercount':'c.testman_usercount', $this->testman_usercount,$business_activity_search);
//        }
//        if($this->testman_casecount){
//            CommonHelper::searchNumber($query, $business_activity_search?'testman_casecount':'c.testman_casecount', $this->testman_casecount,$business_activity_search);
//        }
//        if($this->testman_totalexecasecount){
//            CommonHelper::searchNumber($query, $business_activity_search?'testman_totalexecasecount':'c.testman_totalexecasecount', $this->testman_totalexecasecount,$business_activity_search);
//        }
//        if($this->deploy_usercount){
//            CommonHelper::searchNumber($query, $business_activity_search?'deploy_usercount':'c.deploy_usercount', $this->deploy_usercount,$business_activity_search);
//        }
//        if($this->deploy_envcount){
//            CommonHelper::searchNumber($query, $business_activity_search?'deploy_envcount':'c.deploy_envcount', $this->deploy_envcount,$business_activity_search);
//        }
//        if($this->deploy_execount){
//            CommonHelper::searchNumber($query, $business_activity_search?'deploy_execount':'c.deploy_execount', $this->deploy_execount,$business_activity_search);
//        }
//        if($this->deploy_vmcount){
//            CommonHelper::searchNumber($query, $business_activity_search?'deploy_vmcount':'c.deploy_vmcount', $this->deploy_vmcount,$business_activity_search);
//        }
        
//        if($this->projectman_usercount_d){
//            CommonHelper::searchNumber($query, $business_activity_search?'projectman_usercount_d':'d.projectman_usercount', $this->projectman_usercount_d,$business_activity_search);
//        }
//        if($this->projectman_projectcount_d){
//            CommonHelper::searchNumber($query, 'd.projectman_projectcount', $this->projectman_projectcount_d,$business_activity_search);
//        }
//        if($this->projectman_membercount_d){
//            CommonHelper::searchNumber($query, 'd.projectman_membercount', $this->projectman_membercount_d,$business_activity_search);
//        }
//        if($this->projectman_versioncount_d){
//            CommonHelper::searchNumber($query, 'd.projectman_versioncount', $this->projectman_versioncount_d,$business_activity_search);
//        }
//        if($this->projectman_issuecount_d){
//            CommonHelper::searchNumber($query, 'd.projectman_issuecount', $this->projectman_issuecount_d,$business_activity_search);
//        }
//        if($this->projectman_storagecount_d){
//            CommonHelper::searchNumber($query, 'd.projectman_storagecount', $this->projectman_storagecount_d,$business_activity_search);
//        }
//        if($this->codehub_all_usercount_d){
//            CommonHelper::searchNumber($query, 'd.codehub_all_usercount', $this->codehub_all_usercount_d,$business_activity_search);
//        }
//        if($this->codehub_repositorycount_d){
//            CommonHelper::searchNumber($query, 'd.codehub_repositorycount', $this->codehub_repositorycount_d,$business_activity_search);
//        }
//        if($this->codehub_commitcount_d){
//            CommonHelper::searchNumber($query, 'd.codehub_commitcount', $this->codehub_commitcount_d,$business_activity_search);
//        }
//        if($this->codehub_repositorysize_d){
//            CommonHelper::searchNumber($query, 'd.codehub_repositorysize', $this->codehub_repositorysize_d,$business_activity_search);
//        }
//        if($this->pipeline_usercount_d){
//            CommonHelper::searchNumber($query, 'd.pipeline_usercount', $this->pipeline_usercount_d,$business_activity_search);
//        }
//        if($this->pipeline_pipecount_d){
//            CommonHelper::searchNumber($query, 'd.pipeline_pipecount', $this->pipeline_pipecount_d,$business_activity_search);
//        }
//        if($this->pipeline_executecount_d){
//            CommonHelper::searchNumber($query, 'd.pipeline_executecount', $this->pipeline_executecount_d,$business_activity_search);
//        }
//        if($this->pipeline_elapse_time_d){
//            CommonHelper::searchNumber($query, 'd.pipeline_elapse_time', $this->pipeline_elapse_time_d,$business_activity_search);
//        }
//        if($this->codecheck_usercount_d){
//            CommonHelper::searchNumber($query, 'd.codecheck_usercount', $this->codecheck_usercount_d,$business_activity_search);
//        }
//        if($this->codecheck_taskcount_d){
//            CommonHelper::searchNumber($query, 'd.codecheck_taskcount', $this->codecheck_taskcount_d,$business_activity_search);
//        }
//        if($this->codecheck_codelinecount_d){
//            CommonHelper::searchNumber($query,'d.codecheck_codelinecount', $this->codecheck_codelinecount_d,$business_activity_search);
//        }
//        if($this->codecheck_issuecount_d){
//            CommonHelper::searchNumber($query, 'd.codecheck_issuecount', $this->codecheck_issuecount_d,$business_activity_search);
//        }
//        if($this->codecheck_execount_d){
//            CommonHelper::searchNumber($query, 'd.codecheck_execount', $this->codecheck_execount_d,$business_activity_search);
//        }
//        if($this->codeci_usercount_d){
//            CommonHelper::searchNumber($query, 'd.codeci_usercount', $this->codeci_usercount_d,$business_activity_search);
//        }
//        if($this->codeci_buildcount_d){
//            CommonHelper::searchNumber($query, 'd.codeci_buildcount', $this->codeci_buildcount_d,$business_activity_search);
//        }
//        if($this->codeci_allbuildcount_d){
//            CommonHelper::searchNumber($query, 'd.codeci_allbuildcount', $this->codeci_allbuildcount_d,$business_activity_search);
//        }
//        if($this->codeci_buildtotaltime_d){
//            CommonHelper::searchNumber($query, 'd.codeci_buildtotaltime', $this->codeci_buildtotaltime_d,$business_activity_search);
//        }
//        if($this->testman_usercount_d){
//            CommonHelper::searchNumber($query, 'd.testman_usercount', $this->testman_usercount_d,$business_activity_search);
//        }
//        if($this->testman_casecount_d){
//            CommonHelper::searchNumber($query, 'd.testman_casecount', $this->testman_casecount_d,$business_activity_search);
//        }
//        if($this->testman_totalexecasecount_d){
//            CommonHelper::searchNumber($query, 'd.testman_totalexecasecount', $this->testman_totalexecasecount_d,$business_activity_search);
//        }
//        if($this->deploy_usercount_d){
//            CommonHelper::searchNumber($query, 'd.deploy_usercount', $this->deploy_usercount_d,$business_activity_search);
//        }
//        if($this->deploy_envcount_d){
//            CommonHelper::searchNumber($query, 'd.deploy_envcount', $this->deploy_envcount_d,$business_activity_search);
//        }
//        if($this->deploy_execount_d){
//            CommonHelper::searchNumber($query, 'd.deploy_execount', $this->deploy_execount_d,$business_activity_search);
//        }
//        if($this->deploy_vmcount_d){
//            CommonHelper::searchNumber($query, 'd.deploy_vmcount', $this->deploy_vmcount_d,$business_activity_search);
//        }        
        
      
        return $dataProvider;
    }
}
