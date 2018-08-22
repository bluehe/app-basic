<?php

namespace rky\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use rky\models\Corporation;

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
            [['id', 'base_bd', 'base_company_scale', 'base_registered_time', 'stat', 'intent_set', 'allocate_set', 'allocate_time', 'contact_park', 'develop_scale', 'created_at', 'updated_at'], 'integer'],
            [['base_company_name', 'base_main_business', 'huawei_account', 'note', 'contact_address', 'contact_location', 'contact_business_name', 'contact_business_job', 'contact_business_tel', 'contact_technology_name', 'contact_technology_job', 'contact_technology_tel', 'develop_pattern', 'develop_scenario', 'develop_language', 'develop_IDE', 'develop_current_situation', 'develop_weakness','base_industry'], 'safe'],
            [['base_registered_capital', 'base_last_income', 'allocate_amount'], 'number'],
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
        $query = Corporation::find()->joinWith(['baseBd']);

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
            'base_company_scale' => $this->base_company_scale,
            'base_registered_capital' => $this->base_registered_capital,
            'base_registered_time' => $this->base_registered_time,
            'base_last_income' => $this->base_last_income,
            'stat' => $this->stat,
            'intent_set' => $this->intent_set,
            'allocate_set' => $this->allocate_set,
            'allocate_amount' => $this->allocate_amount,
            'allocate_time' => $this->allocate_time,
            'contact_park' => $this->contact_park,
            'develop_scale' => $this->develop_scale,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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
            ->andFilterWhere(['like', 'develop_pattern', $this->develop_pattern])
            ->andFilterWhere(['like', 'develop_scenario', $this->develop_scenario])
            ->andFilterWhere(['like', 'develop_language', $this->develop_language])
            ->andFilterWhere(['like', 'develop_IDE', $this->develop_IDE])
            ->andFilterWhere(['like', 'develop_current_situation', $this->develop_current_situation])
            ->andFilterWhere(['like', 'develop_weakness', $this->develop_weakness]);
        
        if($this->base_industry){
            $corporation= CorporationIndustry::find()->where(['industry_id'=>$this->base_industry])->select(['corporation_id'])->column();
            $query->andWhere([Corporation::tableName(). '.id'=>$corporation]);
        }
        
        if($this->contact_location==1){
            $query->andWhere(['not',['contact_location'=>NULL]]);
        }elseif($this->contact_location==2){
            $query->andWhere(['contact_location'=>NULL]);
        }

        return $dataProvider;
    }
}
