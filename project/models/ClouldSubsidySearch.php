<?php

namespace project\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use project\models\ClouldSubsidy;
use project\components\CommonHelper;

/**
 * ClouldSubsidySearch represents the model behind the search form of `project\models\ClouldSubsidy`.
 */
class ClouldSubsidySearch extends ClouldSubsidy
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','group_id', 'corporation_id', 'subsidy_bd'], 'integer'],
            [['corporation_name', 'subsidy_note', 'subsidy_time','subsidy_amount','annual'], 'safe'],
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
        $query = ClouldSubsidy::find()->joinWith(['subsidyBd']);

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
            'group_id' => $this->group_id,
            'corporation_id' => $this->corporation_id,
            'subsidy_bd' => $this->subsidy_bd,
            'annual' => $this->annual,
        ]);

        $query->andFilterWhere(['or like', 'corporation_name', explode('|', trim($this->corporation_name))])
            ->andFilterWhere(['like', 'subsidy_note', $this->subsidy_note]);
        
        if($this->subsidy_amount){
            CommonHelper::searchNumber($query, 'subsidy_amount', $this->subsidy_amount);
        }
        
        if ($this->subsidy_time) {
            $range = explode('~', $this->subsidy_time);
            $start = strtotime($range[0]);
            $end = strtotime($range[1]) + 86399;
            $query->andFilterWhere(['>=', 'subsidy_time', $start])->andFilterWhere(['<=', 'subsidy_time', $end]);
        }
        
        $query->andWhere(['or',['group_id'=> UserGroup::get_user_groupid(Yii::$app->user->identity->id)],['group_id'=>NULL]]);

        return $dataProvider;
    }
}
