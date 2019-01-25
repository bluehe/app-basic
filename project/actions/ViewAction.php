<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-08-13 10:10
 */

namespace project\actions;


use Yii;
use yii\web\BadRequestHttpException;
use project\models\UserGroup;

class ViewAction extends \yii\base\Action
{

    public $modelClass;

    public $scenario = 'default';

    /** @var string 模板路径，默认为action id  */
    public $viewFile = 'view';
    
    public $ajax = false;
    
    public $auth_group=false;//认证用户组权限

    /**
     * view详情页
     *
     * @param $id
     * @return string
     * @throws \yii\web\BadRequestHttpException
     */
    public function run($id)
    {
        /* @var $model \yii\db\ActiveRecord */
        $model = call_user_func([$this->modelClass, 'findOne'], $id);
        if (! $model) throw new BadRequestHttpException("Cannot find model by $id");
        $model->setScenario( $this->scenario );
        
        if($this->auth_group&&!UserGroup::auth_group($model->group_id)){
            Yii::$app->getSession()->setFlash('error', '权限不足');
            return $this->controller->redirect(Yii::$app->request->referrer);
        }
        
        if($this->ajax){
            return $this->controller->renderAjax($this->viewFile, [
                'model' => $model,
            ]);
        }else{
            return $this->controller->render($this->viewFile, [
                'model' => $model,
            ]);
        }
        
    }

}
