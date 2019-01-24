<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\models\User;
use project\models\UserSearch;
use project\actions\IndexAction;
use mdm\admin\components\Helper;
use project\models\UserGroup;

class UserController extends Controller { 
    
    public function actions()
    {
        return [
            'user-list' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $searchModel = new UserSearch();
                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                    return [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                    ];
                }
            ],
//            'create' => [
//                'class' => CreateAction::className(),
//                'modelClass' => Article::className(),
//                'scenario' => 'article',
//            ],
//            'update' => [
//                'class' => UpdateAction::className(),
//                'modelClass' => Article::className(),
//                'scenario' => 'article',
//            ],
//            'view-layer' => [
//                'class' => ViewAction::className(),
//                'modelClass' => Article::className(),
//            ],
//            'delete' => [
//                'class' => DeleteAction::className(),
//                'modelClass' => Article::className(),
//            ],
//            'sort' => [
//                'class' => SortAction::className(),
//                'modelClass' => Article::className(),
//                'scenario' => 'article',
//            ],
        ];
    }
    
//    /**
//     * Lists all User models.
//     * @return mixed
//     */
//    public function actionUserList() {
//        $searchModel = new UserSearch();
//        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
//
//        return $this->render('user-list', [
//                    'searchModel' => $searchModel,
//                    'dataProvider' => $dataProvider,
//        ]);
//    }
    
    public function actionUserChange($id) {
        $model = User::findOne($id);
        $auth = Yii::$app->authManager;
        $Role_admin = $auth->getRole('superadmin');
        $disabled =($auth->getAssignment($Role_admin->name, $model->id)||$model->role== User::ROLE_PM)&&!$auth->getAssignment($Role_admin->name, Yii::$app->user->identity->id)&&$model->id!=Yii::$app->user->identity->id;
        if (!$disabled) {
            
            $model->status = User::STATUS_ACTIVE == $model->status ? User::STATUS_DELETED : User::STATUS_ACTIVE;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', '修改成功。');
            } else {
                Yii::$app->session->setFlash('error', '修改失败。');
            }
        } else {
            Yii::$app->session->setFlash('error', '没有权限进行此操作。');
        }

        return $this->redirect(Yii::$app->request->referrer);
    }
    
    public function actionUserUpdate($id) {
        $model = User::findOne($id);        
        
        $auth = Yii::$app->authManager;
        $Role_admin=$auth->getRole('superadmin');
        $disabled =($auth->getAssignment($Role_admin->name, $model->id)||$model->role== User::ROLE_PM)&&!$auth->getAssignment($Role_admin->name, Yii::$app->user->identity->id)&&$model->id!=Yii::$app->user->identity->id;
        if($disabled){
            Yii::$app->session->setFlash('warning', '没有权限进行此操作');
            return $this->redirect(Yii::$app->request->referrer);
        }
        
        $model->group =$old_group= UserGroup::get_user_groupid($model->id,true);
        $role = $model->role;

        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
            
            $rw = Yii::$app->request->post('User');
            $groups = $rw['group'] ? $rw['group'] : array();             
            $group = new UserGroup();
            $group->user_id = $model->id;   
                      
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $auth = Yii::$app->authManager;
                $Role_admin=$auth->getRole('superadmin');
                if($model->status == User::STATUS_DELETED && $auth->getAssignment($Role_admin->name, $model->id)){
                    //超级管理员不准删除
                    $mode->status = User::STATUS_ACTIVE;
                }
                $model->save(false);

                if ($model->role != $role) {


                    if(!$auth->getAssignment($Role_admin->name, Yii::$app->user->identity->id)&&($model->role=='pm'||$role=='pm')){
                        Yii::$app->session->setFlash('warning', '项目经理不能设置和移除同级人员');
                        throw new \Exception("项目经理不能设置和移除同级人员");
                    }
                    if($role!=''){
                        $Role_old = $auth->getRole($role);                      
                        $auth->revoke($Role_old, $id);
                        Helper::invalidate();
                    }
                    if($model->role!=''){
                        $Role_new = $auth->getRole($model->role);
                        if (!$auth->getAssignment($Role_new->name, $id)) {
                            $auth->assign($Role_new, $id);
                            Helper::invalidate();

                        }
                    }
                    Yii::$app->cache->delete('corporation_update');
                    Yii::$app->cache->delete('corporation_delete');
                }
                   
                $t1 = array_diff($groups, $old_group); //新增
                $t2 = array_diff($old_group, $groups); //删除
                if (count($t1) > 0) {
                    foreach ($t1 as $t) {
                        $_group = clone $group;
                        $_group->group_id = $t;
                        if (!$_group->save()) {
                            throw new \Exception("修改失败");
                        }
                    }
                }
                if (count($t2) > 0) {
                    UserGroup::deleteAll(['user_id' => $model->id, 'group_id' => $t2]);
                }
                   

                $transaction->commit();
//                    $model->group = $groups;

                Yii::$app->session->setFlash('success', '修改成功。');
            } catch (\Exception $e) {

                $transaction->rollBack();
//                throw $e;
                Yii::$app->session->setFlash('error', '修改失败。');
            }
            return $this->redirect(Yii::$app->request->referrer);
 
        }
        return $this->render('user-update', [
                    'model' => $model,
        ]);
    }
}
