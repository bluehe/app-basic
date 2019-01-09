<?php

namespace project\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use project\models\CorporationMeal;

/**
 * CorporationMealSearch represents the model behind the search form of `project\models\CorporationMeal`.
 */
class CorporationMealSearch extends CorporationMeal
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','meal_id', 'number', 'bd', 'user_id', 'created_at','stat'], 'integer'],
            [['amount'], 'number'],
            [['corporation_id','huawei_account', 'start_time', 'end_time','annual'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
    public function search($params)
    {
        $query = CorporationMeal::find()->joinWith(['corporation','bd0','meal']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'start_time' => SORT_DESC,
                    'id' => SORT_DESC,
                ]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,           
            'meal_id' => $this->meal_id,
            'number' => $this->number,
            'amount' => $this->amount,
            'bd' => $this->bd,
            CorporationMeal::tableName().'.stat' => $this->stat,
            'annual' => $this->annual,
            'user_id' => $this->user_id,
            CorporationMeal::tableName().'.created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', CorporationMeal::tableName(). '.huawei_account', $this->huawei_account]);
        
        $query->andFilterWhere(['or like', 'base_company_name', explode('|', trim($this->corporation_id))]);
        
        if ($this->start_time) {
            $range = explode('~', $this->start_time);
            $start = strtotime($range[0]);
            $end = strtotime($range[1]) + 86399;
            $query->andFilterWhere(['>=', 'start_time', $start])->andFilterWhere(['<=', 'start_time', $end]);
        }
        
        if ($this->end_time) {
            $range = explode('~', $this->end_time);
            $start = strtotime($range[0]);
            $end = strtotime($range[1]) + 86399;
            $query->andFilterWhere(['>=', 'end_time', $start])->andFilterWhere(['<=', 'end_time', $end]);
        }

        return $dataProvider;
    }
}
