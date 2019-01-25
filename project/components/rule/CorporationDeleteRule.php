<?php

namespace project\components\rule;

use Yii;
use yii\rbac\Rule;
use project\models\Corporation;
use project\models\User;
use project\models\UserGroup;

class CorporationDeleteRule extends Rule {

    /**
     * @param string|integer $user 当前登录用户的uid
     * @param Item $item 所属规则rule，也就是我们后面要进行的新增规则
     * @param array $params 当前请求携带的参数.
     * @return true或false.true用户可访问 false用户不可访问
     */
    public function execute($user, $item, $params) {
        $id = isset($params['id']) ? $params['id'] : null;
        if (!$id) {
            return false;
        }

        $model = Corporation::findOne($id);
        if (!$model) {
            return false;
        }
        
        if(!UserGroup::auth_group($model->group_id)){
            return false;
        }

        $auth = Yii::$app->authManager;
        $Role_admin=$auth->getRole('superadmin');
        $uid = Yii::$app->user->identity->id;
        $role = Yii::$app->user->identity->role;
        if (!$model->base_bd||$uid == $model->base_bd||$role== User::ROLE_PM||$auth->getAssignment($Role_admin->name, $uid)) {
            return true;
        }
        return false;
    }

}
