<?php

namespace project\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use project\models\Train;

/**
 * TrainSearch represents the model behind the search form about `rky\models\Train`.
 */
class TrainSearch extends Train
{
    
    public $sa;
    public $other;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'uid', 'created_at', 'updated_at', 'corporation_id', 'train_num', 'reply_uid', 'reply_at', 'train_stat'], 'integer'],
            [['train_type', 'train_name', 'train_address', 'other_people', 'train_result', 'train_note','sa', 'other','train_start'], 'safe'],
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
        $query = Train::find()->joinWith(['corporation']);

        // add conditions that should always apply here

        if ($pageSize > 0) {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                    'pagination' => [
                    'pageSize' => $pageSize,
                ],
                'sort' => ['defaultOrder' => [
                    'train_start' => SORT_DESC,
                ]],
            ]);
        }else{
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => [
                        'train_start' => SORT_DESC,
                        //'id'=>SORT_DESC,
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
            'uid' => $this->uid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'corporation_id' => $this->corporation_id,
            'train_num' => $this->train_num,
            'reply_uid' => $this->reply_uid,
            'reply_at' => $this->reply_at,
             'train_type' => $this->train_type,
            'train_stat' => $this->train_stat,
        ]);

        $query->andFilterWhere(['like', 'train_name', explode('|', trim($this->train_name))])
            ->andFilterWhere(['like', 'train_address', $this->train_address])
            ->andFilterWhere(['like', 'other_people', $this->other_people])
            ->andFilterWhere(['like', 'train_result', $this->train_result])
            ->andFilterWhere(['like', 'train_note', $this->train_note]);
        
        if ($this->train_start) {
            $range = explode('~', $this->train_start);
            $start = strtotime($range[0]);
            $end = strtotime($range[1]) + 86399;
            $query->andFilterWhere(['>=', 'train_start', $start])->andFilterWhere(['<=', 'train_start', $end]);
        }
        
       
        if($this->sa){
            $train= TrainUser::find()->where(['user_id'=>$this->sa])->select(['train_id'])->column();
            $query->andWhere([Train::tableName().'.id'=>$train]);
        }
        if($this->other){
            $train= TrainUser::find()->where(['user_id'=>$this->other])->select(['train_id'])->column();
            $query->andWhere([Train::tableName().'.id'=>$train]);
        }

        return $dataProvider;
    }
}
