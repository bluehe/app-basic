<?php

namespace project\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use project\models\Corporation;
use project\components\CommonHelper;

/**
 * CorporationSearch represents the model behind the search form about `rky\models\Corporation`.
 */
class CorporationSearch extends Corporation
{
    
    public $base_industry;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','base_bd', 'stat', 'intent_set', 'contact_park', 'created_at', 'updated_at'], 'integer'],
            [['base_company_name', 'base_main_business', 'huawei_account', 'note', 'contact_address', 'contact_location', 'contact_business_name', 'contact_business_job', 'contact_business_tel', 'contact_technology_name', 'contact_technology_job', 'contact_technology_tel', 'develop_pattern', 'develop_scenario', 'develop_science', 'develop_language', 'develop_IDE', 'develop_current_situation', 'develop_weakness','base_industry','base_bd', 'base_registered_capital','base_last_income','base_company_scale','intent_number','intent_amount', 'develop_scale', 'base_registered_time'], 'safe'],
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
    public function search($params,$pageSize = '')
    {
        $query = Corporation::find();

        // add conditions that should always apply here

      
         if ($pageSize > 0) {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                    'pagination' => [
                    'pageSize' => $pageSize,
                ],
                'sort' => ['defaultOrder' => [
                    'id' => SORT_DESC,
                ]],
            ]);
        }else{
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => [
                    'id' => SORT_DESC,
                ]],
            ]);
        }

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
            'stat' => $this->stat,
            'intent_set' => $this->intent_set,
            'contact_park' => $this->contact_park,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'develop_pattern' => $this->develop_pattern,
            'develop_scenario' => $this->develop_scenario,
            'develop_science' => $this->develop_science,
            'develop_IDE' => $this->develop_IDE,
        ]);

        $query->andFilterWhere(['like', 'base_company_name', explode('|', trim($this->base_company_name))])
            ->andFilterWhere(['like', 'base_main_business', $this->base_main_business])
            ->andFilterWhere(['like', 'huawei_account', $this->huawei_account])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'contact_address', $this->contact_address])
            ->andFilterWhere(['like', 'contact_business_name', $this->contact_business_name])
            ->andFilterWhere(['like', 'contact_business_job', $this->contact_business_job])
            ->andFilterWhere(['like', 'contact_business_tel', $this->contact_business_tel])
            ->andFilterWhere(['like', 'contact_technology_name', $this->contact_technology_name])
            ->andFilterWhere(['like', 'contact_technology_job', $this->contact_technology_job])
            ->andFilterWhere(['like', 'contact_technology_tel', $this->contact_technology_tel])         
            ->andFilterWhere(['like', 'develop_current_situation', $this->develop_current_situation])
            ->andFilterWhere(['like', 'develop_weakness', $this->develop_weakness]);
        
        if($this->base_industry){
            $industry= Industry::find()->where(['parent_id'=>$this->base_industry])->select(['id'])->column();
            $industry[]=$this->base_industry;
            $corporation= CorporationIndustry::find()->where(['industry_id'=>$industry])->select(['corporation_id'])->column();
            $query->andWhere([Corporation::tableName(). '.id'=>$corporation]);
        }
        
        if($this->contact_location==1){
            $query->andWhere(['not',['contact_location'=>NULL]]);
        }elseif($this->contact_location==2){
            $query->andWhere(['contact_location'=>NULL]);
        }
        
        if($this->develop_language){
            $query->andWhere('FIND_IN_SET(:develop_language, develop_language)')->addParams([':develop_language' => $this->develop_language]);
        }
        
        if($this->base_company_scale){
            CommonHelper::searchNumber($query, 'base_company_scale', $this->base_company_scale);
        }
        
        if($this->base_last_income){
            CommonHelper::searchNumber($query, 'base_last_income', $this->base_last_income);
        }
        
        if($this->base_registered_capital){
            CommonHelper::searchNumber($query, 'base_registered_capital', $this->base_registered_capital);
        }
        
        if($this->intent_number){
            CommonHelper::searchNumber($query, 'intent_number', $this->intent_number);
        }
        
        if($this->intent_amount){
            CommonHelper::searchNumber($query, 'intent_amount', $this->intent_amount);
        }
        
        if($this->develop_scale){
            CommonHelper::searchNumber($query, 'develop_scale', $this->develop_scale);
        }
        
        if ($this->base_registered_time) {
            $range = explode('~', $this->base_registered_time);
            $start = strtotime($range[0]);
            $end = strtotime($range[1]) + 86399;
            $query->andFilterWhere(['>=', 'base_registered_time', $start])->andFilterWhere(['<=', 'base_registered_time', $end]);
        }

        return $dataProvider;
    }
}
