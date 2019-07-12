<?php

namespace project\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use project\models\HealthData;
use project\components\CommonHelper;


/**
 * ActivityChangeSearch represents the model behind the search form about `rky\models\ActivityChange`.
 */
class HealthSearch extends HealthData
{
    
    public $corporation;
    public $start_time;
    public $end_time;



    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','group_id','bd_id', 'corporation_id','level','activity_day','activity_week','activity_month','act_trend','health_trend','is_allocate'], 'integer'],
            [['corporation','H','C','I','A','R','V','D','start_time','end_time'],'safe'],
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
        $query = HealthData::find()->alias('h')->andFilterWhere(['and',['>=','statistics_time',$start],['<=','statistics_time',$end]])->orderBy(['statistics_time'=>SORT_DESC])->joinWith(['corporation','group']);
        if($annual=='all'){
            
            
        }elseif($annual){
            $corporation_id= CorporationMeal::find()->where(['annual'=>$annual])->select(['corporation_id'])->distinct()->column();
            $query->andFilterWhere(['corporation_id'=>$corporation_id]);
        }
        if($sum){
            $query->select([
                'start_time'=>'MIN(statistics_time)',
                'end_time'=>'MAX(statistics_time)',
                'bd_id',
                'group_id'=>'h.group_id',
                'corporation_id'=>'corporation_id',
                'activity_day',
                'activity_week',
                'activity_month',
                'level',
                'is_allocate'=>'MAX(is_allocate)',
//                'is_act'=>'MAX(is_act)',
                'act_trend'=>'SUM(CASE WHEN activity_week='. HealthData::ACT_Y.' THEN 1  ELSE 0 END)/count(*)',
                'health_trend'=>'SUM(H)/count(*)',
//                'health'=>'SUM(CASE WHEN health!='.ActivityChange::HEALTH_WA.' THEN health  ELSE 0 END)/count(*)',
                'H',
                'C',
                'I',
                'A',
                'R',
                'V',
                'D',
               
            ])->groupBy('corporation_id');
        }else{
            $query->select([
                'start_time'=>'statistics_time',
                'end_time'=>'statistics_time',
                'bd_id',
                'group_id'=>'h.group_id',
                'corporation_id',
                'level',
                'activity_day',
                'activity_week',
                'activity_month',
                'is_allocate',
                'act_trend',
                'health_trend',
                'H',
                'C',
                'I',
                'A',
                'R',
                'V',
                'D',
            ]);
        }

        // add conditions that should always apply here
        if ($pageSize > 0) {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => $pageSize,
                ],
                'sort' => ['defaultOrder' => [
                    'statistics_time' => SORT_DESC,
                ]],
            ]);
        }else{
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => [
                        'statistics_time' => SORT_DESC,
                    ]],
            ]);
        }
  
        
        $sort = $dataProvider->getSort();
        $sort->attributes['corporation'] = [
            'asc' => ['corporation_id' => SORT_ASC,'statistics_time'=>SORT_ASC],
            'desc' => ['corporation_id' => SORT_DESC,'statistics_time'=>SORT_ASC],          
        ];
//        $sort->attributes['base_bd'] = [
//            'asc' => ['base_bd' => SORT_ASC,'corporation_id' => SORT_ASC,'end_time'=>SORT_ASC],
//            'desc' => ['base_bd' => SORT_DESC,'corporation_id' => SORT_DESC,'end_time'=>SORT_ASC],          
//        ];
        $sort->attributes['statistics_time'] = [
            'asc' => ['statistics_time' => SORT_ASC,'corporation_id'=>SORT_ASC],
            'desc' => ['statistics_time' => SORT_DESC,'corporation_id'=>SORT_ASC],          
        ];
        $sort->attributes['act_trend'] = [
            'asc' => ['act_trend' => SORT_ASC,'corporation_id'=>SORT_ASC],
            'desc' => ['act_trend' => SORT_DESC,'corporation_id'=>SORT_ASC],          
        ];
        $sort->attributes['health_trend'] = [
            'asc' => ['health_trend' => SORT_ASC,'corporation_id'=>SORT_ASC],
            'desc' => ['health_trend' => SORT_DESC,'corporation_id'=>SORT_ASC],          
        ];
        $sort->attributes['level'] = [
            'asc' => ['level' => SORT_ASC,'corporation_id'=>SORT_ASC],
            'desc' => ['level' => SORT_DESC,'corporation_id'=>SORT_ASC],          
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
            'level'=>$this->level,
            'group_id'=>$this->group_id,
            'activity_day'=>$this->activity_day,
            'activity_week'=>$this->activity_week,
            'activity_month'=>$this->activity_month,
            'is_allocate'=>$this->is_allocate,
            'act_trend'=>$this->act_trend,
            'health_trend'=>$this->health_trend,
        ]);        
        
        
        $query->andFilterWhere(['or like', 'base_company_name', explode('|', trim($this->corporation))]);
        
//        if($sum){
//            //历史标准汇总
//            $ids = ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end],['is_act' => ActivityChange::ACT_Y]])->andFilterWhere(['bd_id'=>$this->bd_id])->select(['corporation_id'])->distinct()->column();
//            if($this->is_act== ActivityChange::ACT_Y){
//                $query->andFilterWhere(['c.corporation_id' => $ids]);
//            }elseif($this->is_act== ActivityChange::ACT_N){
//                $query->andFilterWhere(['not',['c.corporation_id' => $ids]]);
//            }
//            
//            $ids1=ActivityChange::find()->andFilterWhere(['and',['>=','start_time',$start],['<=','end_time',$end],['is_allocate' => ActivityChange::ALLOCATE_Y]])->andFilterWhere(['bd_id'=>$this->bd_id])->select(['corporation_id'])->distinct()->column();
//            if($this->is_allocate== ActivityChange::ALLOCATE_Y){
//                $query->andFilterWhere(['c.corporation_id' => $ids1]);
//            }elseif($this->is_allocate== ActivityChange::ALLOCATE_N){
//                $query->andFilterWhere(['not',['c.corporation_id' => $ids1]]);
//            }
//            
//        }else{
//            //历史标准分次
//            $query->andFilterWhere([ 'is_act' => $this->is_act,'is_allocate'=> $this->is_allocate]);
//        }
  
        $business_activity_search=(System::getValue('business_activity_search')==2)&&$sum;//搜索方式
        
        if($business_activity_search&&$this->bd_id){
            $query->andHaving(['bd_id'=>$this->bd_id]);
        }else{
            $query->andFilterWhere(['bd_id'=>$this->bd_id]);
        }
              
        
        //健康度
        CommonHelper::searchNumber($query, $business_activity_search?'H':"H", $this->H,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'C':"C", $this->C,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'I':"I", $this->I,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'A':"A", $this->A,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'R':"R", $this->R,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'V':"V", $this->V,$business_activity_search);
        CommonHelper::searchNumber($query, $business_activity_search?'D':"D", $this->D,$business_activity_search);
        
        $query->andWhere(['or',['h.group_id'=> UserGroup::get_user_groupid(Yii::$app->user->identity->id)],['h.group_id'=>NULL]]);
          
        
      
        return $dataProvider;
    }
}
