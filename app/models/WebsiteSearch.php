<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Website;

/**
 * WebsiteSearch represents the model behind the search form about `dh\models\Website`.
 */
class WebsiteSearch extends Website {

    public $uid;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'cid', 'sort_order', 'share_status', 'share_id', 'collect_num', 'click_num', 'created_at', 'updated_at', 'is_open', 'stat'], 'integer'],
            [['title', 'username', 'url', 'host'], 'safe'],
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
        $query = Website::find()->joinWith(['u', 'c']);

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
            'sort_order' => $this->sort_order,
            'share_status' => $this->share_status,
            'share_id' => $this->share_id,
            'click_num' => $this->click_num,
            Website::tableName() . '.is_open' => $this->is_open,
            Website::tableName() . '.stat' => $this->stat,
        ]);

        $query->andFilterWhere(['like', Website::tableName() . '.title', $this->title])
                ->andFilterWhere(['like', 'url', $this->url])
                ->andFilterWhere(['like', 'host', $this->host]);

        return $dataProvider;
    }

}
