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
    public $devcloud_pv_d;
    
    public $projectman_projectcount_d;
    public $projectman_membercount_d;
    public $projectman_issuecount_d;
    public $projectman_wiki_d;
    public $projectman_docman_d;   
    public $projectman_versioncount_d;    
    public $projectman_usercount_d;
    public $projectman_storagecount_d;
    
    public $codehub_repositorycount_d;
    public $codehub_commitcount_d;
    public $codehub_repositorysize_d;
    public $codehub_all_usercount_d;
    
    public $pipeline_assignmentscount_d;
    public $pipeline_elapse_time_d;
    public $pipeline_usercount_d;
    public $pipeline_pipecount_d;
    public $pipeline_executecount_d;
        
    public $codecheck_taskcount_d;
    public $codecheck_codelinecount_d;    
    public $codecheck_execount_d;
    public $codecheck_usercount_d;
    public $codecheck_issuecount_d;
        
    public $codeci_buildcount_d;
    public $codeci_buildtotaltime_d;
    public $codeci_usercount_d;
    public $codeci_allbuildcount_d;
        
    public $testman_casecount_d;
    public $testman_execasecount_d;
    public $testman_usercount_d;
    public $testman_totalexecasecount_d;
      
    public $deploy_envcount_d;
    public $deploy_execount_d;
    public $deploy_usercount_d;
    public $deploy_vmcount_d;
    
    public $releaseman_uploadcount_d;
    public $releaseman_downloadcount_d;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','group_id','bd_id', 'corporation_id','type','is_allocate','is_act','act_trend','health'], 'integer'],
            [['devcloud_pv','projectman_projectcount', 'projectman_membercount', 'projectman_issuecount','projectman_wiki','projectman_docman', 'codehub_repositorycount', 'codehub_commitcount','pipeline_assignmentscount','codecheck_taskcount', 'codecheck_codelinecount','codecheck_execount','codeci_buildcount','testman_casecount','testman_execasecount', 'deploy_envcount', 'deploy_execount','releaseman_uploadcount','releaseman_downloadcount', 'projectman_usercount', 'projectman_versioncount', 'codehub_all_usercount', 'pipeline_usercount', 'pipeline_pipecount', 'pipeline_executecount', 'codecheck_usercount',  'codecheck_issuecount',  'codeci_usercount',  'codeci_allbuildcount', 'testman_usercount',  'testman_totalexecasecount', 'deploy_usercount','codehub_repositorysize', 'pipeline_elapse_time', 'codeci_buildtotaltime','projectman_storagecount',  'deploy_vmcount','devcloud_pv_d','projectman_projectcount_d', 'projectman_membercount_d', 'projectman_issuecount_d','projectman_wiki_d','projectman_docman_d', 'codehub_repositorycount_d', 'codehub_commitcount_d','pipeline_assignmentscount_d','codecheck_taskcount_d', 'codecheck_codelinecount_d','codecheck_execount_d','codeci_buildcount_d','testman_casecount_d','testman_execasecount_d', 'deploy_envcount_d', 'deploy_execount_d','releaseman_uploadcount_d','releaseman_downloadcount_d', 'projectman_usercount_d', 'projectman_versioncount_d', 'codehub_all_usercount_d', 'pipeline_usercount_d', 'pipeline_pipecount_d', 'pipeline_executecount_d', 'codecheck_usercount_d',  'codecheck_issuecount_d',  'codeci_usercount_d',  'codeci_allbuildcount_d', 'testman_usercount_d',  'testman_totalexecasecount_d', 'deploy_usercount_d','codehub_repositorysize_d', 'pipeline_elapse_time_d', 'codeci_buildtotaltime_d','projectman_storagecount_d',  'deploy_vmcount_d'], 'safe'],
            [[ 'start_time', 'end_time','corporation','h_h','h_c','h_i','h_a','h_r','h_v','h_d'],'safe'],
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
    public function search($params,$start=0,$end=0,$sum=1,$annual='', $pageSize = '')
    {
        $subQuery = ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end]])->orderBy(['start_time'=>SORT_DESC])->limit(ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end]])->count());
        $query = ActivityChange::find()->from(['c'=>$subQuery])->joinWith(['corporation','data d','group']);
        if($annual=='all'){
            
            
        }elseif($annual){
            $corporation_id= CorporationMeal::find()->where(['annual'=>$annual])->select(['corporation_id'])->distinct()->column();
            $query->andFilterWhere(['c.corporation_id'=>$corporation_id]);
        }
        if($sum){
            $query->select([
                'start_time'=>'MIN(start_time)',
                'end_time'=>'MAX(end_time)',
                'bd_id',
                'group_id'=>'c.group_id',
                'corporation_id'=>'c.corporation_id',
                'is_allocate'=>'MAX(is_allocate)',
                'is_act'=>'MAX(is_act)',
                'act_trend'=>'SUM(CASE WHEN is_act='.ActivityChange::ACT_Y.' THEN 1  ELSE 0 END)/count(*)',
//                'health'=>'SUM(CASE WHEN health!='.ActivityChange::HEALTH_WA.' THEN health  ELSE 0 END)/count(*)',
//                'h_h'=>'AVG(h_h)',
//                'h_c'=>'AVG(h_c)',
//                'h_i'=>'AVG(h_i)',
//                'h_a'=>'AVG(h_a)',
//                'h_r'=>'AVG(h_r)',
//                'h_v'=>'AVG(h_v)',
//                'h_d'=>'AVG(h_d)',
                'devcloud_pv' => 'SUM(c.devcloud_pv)',

                'projectman_projectcount' => 'SUM(c.projectman_projectcount)',
                'projectman_membercount' => 'SUM(c.projectman_membercount)',
                'projectman_issuecount' => 'SUM(c.projectman_issuecount)',
                'projectman_wiki' => 'SUM(c.projectman_wiki)',
                'projectman_docman' => 'SUM(c.projectman_docman)',

                'codehub_repositorycount' => 'SUM(c.codehub_repositorycount)',
                'codehub_commitcount' => 'SUM(c.codehub_commitcount)',
                'codehub_repositorysize' => 'SUM(c.codehub_repositorysize)',

                'pipeline_assignmentscount' => 'SUM(c.pipeline_assignmentscount)',
                'pipeline_elapse_time' => 'SUM(c.pipeline_elapse_time)',
                
                'codecheck_taskcount' => 'SUM(c.codecheck_taskcount)',
                'codecheck_codelinecount' => 'SUM(c.codecheck_codelinecount)',
                'codecheck_execount' => 'SUM(c.codecheck_execount)',
                
                'codeci_buildcount' => 'SUM(c.codeci_buildcount)',
                'codeci_buildtotaltime' => 'SUM(c.codeci_buildtotaltime)',
                
                'testman_casecount' => 'SUM(c.testman_casecount)',
                'testman_execasecount' => 'SUM(c.testman_execasecount)',
                               
                'deploy_envcount' => 'SUM(c.deploy_envcount)',
                'deploy_execount' => 'SUM(c.deploy_execount)',
                
                'releaseman_uploadcount' => 'SUM(c.releaseman_uploadcount)',
                'releaseman_downloadcount' => 'SUM(c.releaseman_downloadcount)',
                
                'devcloud_pv_d'=>'d.devcloud_pv',
                'projectman_projectcount_d' => 'd.projectman_projectcount',
                'projectman_membercount_d' => 'd.projectman_membercount',
                'projectman_issuecount_d' => 'd.projectman_issuecount',
                'projectman_wiki_d' => 'd.projectman_wiki',
                'projectman_docman_d' => 'd.projectman_docman',

                'codehub_repositorycount_d' => 'd.codehub_repositorycount',
                'codehub_commitcount_d' => 'd.codehub_commitcount',
                'codehub_repositorysize_d' => 'd.codehub_repositorysize',

                'pipeline_assignmentscount_d' => 'd.pipeline_assignmentscount',
                'pipeline_elapse_time_d' => 'd.pipeline_elapse_time',

                'codecheck_taskcount_d' => 'd.codecheck_taskcount',
                'codecheck_codelinecount_d' => 'd.codecheck_codelinecount',
                'codecheck_execount_d' => 'd.codecheck_execount',

                'codeci_buildcount_d' => 'd.codeci_buildcount',
                'codeci_buildtotaltime_d' => 'd.codeci_buildtotaltime',

                'testman_casecount_d' => 'd.testman_casecount',
                'testman_execasecount_d' => 'd.testman_execasecount',
                               
                'deploy_envcount_d' => 'd.deploy_envcount',
                'deploy_execount_d' => 'd.deploy_execount',
                
                'releaseman_uploadcount_d' => 'd.releaseman_uploadcount',
                'releaseman_downloadcount_d' => 'd.releaseman_downloadcount',
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
        $sort->attributes['health'] = [
            'asc' => ['health' => SORT_ASC,'corporation_id'=>SORT_ASC],
            'desc' => ['health' => SORT_DESC,'corporation_id'=>SORT_ASC],          
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
            'health'=>$this->health,
            'c.group_id'=>$this->group_id,
        ]);
        
        
        
        $query->andFilterWhere(['or like', 'base_company_name', explode('|', trim($this->corporation))]);
        
        if($sum){
            //历史标准汇总
            $ids = ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end],['is_act' => ActivityChange::ACT_Y]])->andFilterWhere(['bd_id'=>$this->bd_id])->select(['corporation_id'])->distinct()->column();
            if($this->is_act== ActivityChange::ACT_Y){
                $query->andFilterWhere(['c.corporation_id' => $ids]);
            }elseif($this->is_act== ActivityChange::ACT_N){
                $query->andFilterWhere(['not',['c.corporation_id' => $ids]]);
            }
            
            $ids1=ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end],['is_allocate' => ActivityChange::ALLOCATE_Y]])->andFilterWhere(['bd_id'=>$this->bd_id])->select(['corporation_id'])->distinct()->column();
            if($this->is_allocate== ActivityChange::ALLOCATE_Y){
                $query->andFilterWhere(['c.corporation_id' => $ids1]);
            }elseif($this->is_allocate== ActivityChange::ALLOCATE_N){
                $query->andFilterWhere(['not',['c.corporation_id' => $ids1]]);
            }
            
        }else{
            //历史标准分次
            $query->andFilterWhere([ 'is_act' => $this->is_act,'is_allocate'=> $this->is_allocate]);
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
        
        //健康度
        CommonHelper::searchNumber($query, $business_activity_search?'h_h':"c.h_h", $this->h_h,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'h_c':"c.h_c", $this->h_c,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'h_i':"c.h_i", $this->h_i,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'h_a':"c.h_a", $this->h_a,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'h_r':"c.h_r", $this->h_r,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'h_v':"c.h_v", $this->h_v,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'h_d':"c.h_d", $this->h_d,$business_activity_search);
        
        $query->andWhere(['or',['c.group_id'=> UserGroup::get_user_groupid(Yii::$app->user->identity->id)],['c.group_id'=>NULL]]);
          
        
      
        return $dataProvider;
    }
}
