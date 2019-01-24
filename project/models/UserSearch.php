<?php

namespace project\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use project\models\User;

/**
 * UserSearch represents the model behind the search form about `project\models\Users`.
 */
class UserSearch extends User {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'point', 'status'], 'integer'],
            [['username', 'created_at', 'nickname', 'email', 'tel', 'avatar', 'role'], 'safe'],
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
            'role' => $this->role,
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
        
        $auth = Yii::$app->authManager;
        $Role_admin=$auth->getRole('superadmin');
        if(!$auth->getAssignment($Role_admin->name, Yii::$app->user->identity->id)){
            //非超级管理员增加用户组筛选
            $pm_group= UserGroup::get_user_groupid(Yii::$app->user->identity->id);
            $group_user= UserGroup::get_group_userid($pm_group);
            $nogroup_user=UserGroup::get_nogroup_userid();
            $query->andWhere(['id'=> array_merge($group_user,$nogroup_user)]);
        }

        return $dataProvider;
    }

}
