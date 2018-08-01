<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;

/**
 * UserSearch represents the model behind the search form about `dh\models\Users`.
 */
class UserSearch extends User {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'point', 'status'], 'integer'],
            [['username', 'created_at', 'nickname', 'email', 'tel', 'avatar'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
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
    public function search($params) {
        $query = User::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => [
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
            'point' => $this->point,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
                ->andFilterWhere(['like', 'nickname', $this->nickname])
                ->andFilterWhere(['like', 'email', $this->email])
                ->andFilterWhere(['like', 'tel', $this->tel]);

        if ($this->created_at) {
            $range = explode('~', $this->created_at);
            $start = strtotime($range[0]);
            $end = strtotime($range[1]) + 86399;
            $query->andFilterWhere(['>=', 'created_at', $start])->andFilterWhere(['<=', 'created_at', $end]);
        }

        return $dataProvider;
    }

}
