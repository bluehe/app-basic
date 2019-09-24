<?php

namespace project\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use project\models\CorporationMeal;
use project\models\UserGroup;

/**
 * CorporationMealSearch represents the model behind the search form of `project\models\CorporationMeal`.
 */
class CorporationMealSearch extends CorporationMeal
{
    public $region;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','group_id','meal_id', 'number', 'bd', 'user_id', 'created_at','stat'], 'integer'],
            [['amount'], 'number'],
            [['corporation_id','huawei_account', 'start_time', 'end_time','annual','region'], 'safe'],
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
    public function search($params,$pageSize = '')
    {
        $query = CorporationMeal::find()->joinWith(['corporation','bd0','meal','group']);

        // add conditions that should always apply here

        if ($pageSize > 0) {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                    'pagination' => [
                    'pageSize' => $pageSize,
                ],
               'sort' => [
                    'defaultOrder' => [
                        'start_time' => SORT_DESC,
                        'id' => SORT_DESC,
                    ]],
            ]);
        }else{
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'start_time' => SORT_DESC,
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
            CorporationMeal::tableName().'.group_id' => $this->group_id,
            'meal_id' => $this->meal_id,
            'number' => $this->number,
            'amount' => $this->amount,
            'bd' => $this->bd,
            CorporationMeal::tableName().'.stat' => $this->stat,
            'annual' => $this->annual,
            'user_id' => $this->user_id,
            CorporationMeal::tableName().'.created_at' => $this->created_at,
            Meal::tableName(). '.region'=>$this->region,
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

        $query->andWhere(['or',[CorporationMeal::tableName().'.group_id'=> UserGroup::get_user_groupid(Yii::$app->user->identity->id)],[CorporationMeal::tableName().'.group_id'=>NULL]]);
        
        return $dataProvider;
    }
}
