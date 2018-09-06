<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-08-13 00:31
 */

namespace project\actions;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\UnprocessableEntityHttpException;

class UpdateAction extends \yii\base\Action
{

    public $modelClass;

    public $scenario = 'default';

    public $paramSign = "id";

    /** @var string 模板路径，默认为action id  */
    public $viewFile = null;

    /** @var array|\Closure 分配到模板中去的变量 */
    public $data;

    /** @var  string|array 编辑成功后跳转地址,此参数直接传给Yii::$app->controller->redirect() */
    public $successRedirect;

    public $ajax = false;

    /**
     * update修改
     *
     * @return array|string|\yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\UnprocessableEntityHttpException
     */
    public function run()
    {
        $id = Yii::$app->getRequest()->get($this->paramSign, null);
        if (! $id) throw new BadRequestHttpException("{$this->paramSign} doesn't exist");
        /* @var $model yii\db\ActiveRecord */
        $model = call_user_func([$this->modelClass, 'findOne'], $id);
        if (! $model) throw new BadRequestHttpException("Cannot find model by $id");
        $model->setScenario( $this->scenario );

        if (Yii::$app->getRequest()->getIsPost()&&$model->load(Yii::$app->getRequest()->post())) {
            
            if( Yii::$app->getRequest()->getIsAjax() ){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
            
            if ($model->save()) {
                if( Yii::$app->getRequest()->getIsAjax() ){
                    return [];
                }else {
                    Yii::$app->getSession()->setFlash('操作成功');
                    if( $this->successRedirect ) return $this->controller->redirect($this->successRedirect);
                    return $this->controller->refresh();
                }
            } else {
                $errors = $model->getErrors();
                $err = '';
                foreach ($errors as $v) {
                    $err .= $v[0] . '<br>';
                }
                if( Yii::$app->getRequest()->getIsAjax() ){
                    throw new UnprocessableEntityHttpException($err);
                }else {
                    Yii::$app->getSession()->setFlash('error', $err);
                }
                $model = call_user_func([$this->modelClass, 'findOne'], $id);
            }
        }

        $this->viewFile === null && $this->viewFile = $this->id;
        $data = [
            'model' => $model,
        ];
        if( is_array($this->data) ){
            $data = array_merge($data, $this->data);
        }elseif ($this->data instanceof \Closure){
            $data = call_user_func_array($this->data, [$model, $this]);
        }
        if($this->ajax){
            return $this->controller->renderAjax($this->viewFile, $data);
        }else{
            return $this->controller->render($this->viewFile, $data);
        }
    }

}
