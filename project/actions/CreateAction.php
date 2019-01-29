<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-08-13 00:06
 */
namespace project\actions;

use Closure;
use Yii;
use project\models\UserGroup;
use project\models\Group;

class CreateAction extends \yii\base\Action
{

    public $modelClass;

    public $scenario = 'default';

    public $data = [];

    /** @var string 模板路径，默认为action id  */
    public $viewFile = null;

    /** @var  string|array 编辑成功后跳转地址,此参数直接传给yii::$app->controller->redirect() */
    public $successRedirect = ['index'];
    
    public $ajax = false;
    
    public $default_group=false;//认证用户组权限

    /**
     * create创建页
     *
     * @return string|\yii\web\Response
     */
    public function run()
    {
        /* @var $model yii\db\ActiveRecord */
        $model = new $this->modelClass;
        $model->setScenario( $this->scenario );
        
        //用户组权限
        $group = Group::get_user_group(Yii::$app->user->identity->id);
        if($this->default_group&&count($group)==1){
            $model->group_id= key($group);   
        }
        
        if (Yii::$app->getRequest()->getIsPost()&&$model->load(Yii::$app->getRequest()->post())) {
            
            if( Yii::$app->getRequest()->getIsAjax() ){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
                            
            if ($model->save()) {
                Yii::$app->getSession()->setFlash('success', '操作成功');
                return $this->controller->redirect($this->successRedirect);
            } else {
                $errorReasons = $model->getErrors();
                $err = '';
                foreach ($errorReasons as $errorReason) {
                    $err .= $errorReason[0] . '<br>';
                }
                $err = rtrim($err, '<br>');
                Yii::$app->getSession()->setFlash('error', $err);
            }
        }
        $model->loadDefaultValues();
        $data = [
            'model' => $model,
        ];
        if( is_array($this->data) ){
            $data = array_merge($data, $this->data);
        }elseif ($this->data instanceof Closure){
            $data = call_user_func_array($this->data, [$model, $this]);
        }
        $this->viewFile === null && $this->viewFile = $this->id;
        if($this->ajax){
            return $this->controller->renderAjax($this->viewFile, $data);
        }else{
            return $this->controller->render($this->viewFile, $data);
        }
    }

}
