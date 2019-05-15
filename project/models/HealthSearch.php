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
class HealthSearch extends ActivityChange
{
    
    public $corporation;
    public $bd_id;
    public $projectman_usercount_d;
    public $projectman_membercount_d;  
   


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','group_id','bd_id', 'corporation_id','type','is_allocate','is_act','act_trend','health'], 'integer'],
            [['projectman_usercount_d','projectman_membercount_d'], 'safe'],
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
                'health'=>'SUM(CASE WHEN health!='.ActivityChange::HEALTH_WA.' THEN health  ELSE 0 END)/count(*)',
                'h_h'=>'AVG(h_h)',
                'h_c'=>'AVG(h_c)',
                'h_i'=>'AVG(h_i)',
                'h_a'=>'AVG(h_a)',
                'h_r'=>'AVG(h_r)',
                'h_v'=>'AVG(h_v)',
                'h_d'=>'AVG(h_d)',                
                'projectman_usercount_d'=>'d.projectman_usercount',
                'projectman_membercount_d' => 'd.projectman_membercount',
               
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
