<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Category;

/**
 * CategorySearch represents the model behind the search form about `dh\models\Category`.
 */
class CategorySearch extends Category {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'cid', 'collect_num', 'sort_order', 'created_at', 'updated_at', 'is_open', 'stat'], 'integer'],
            [['title', 'uid'], 'safe'],
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
        $query = Category::find();

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
            'cid' => $this->cid,
            'collect_num' => $this->collect_num,
            'sort_order' => $this->sort_order,
            'is_open' => $this->is_open,
            'stat' => $this->stat,
        ]);
        if ($this->uid) {
            if ($this->uid == '系统') {
                $u = NULL;
            } else {
                $u = User::find()->select(['id'])->andFilterWhere(['like', 'username', $this->uid])->column();
            }
            $query->andWhere(['uid' => $u]);
        }

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }

}
