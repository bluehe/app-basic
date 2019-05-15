<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-08-13 01:00
 */

namespace project\actions;

use Closure;

class IndexAction extends \yii\base\Action
{

    public $data;

    /** @var $viewFile string 模板路径，默认为action id  */
    public $viewFile = null;

    public $ajax = false;

    public function run()
    {
        $data = $this->data;
        if( $data instanceof Closure){
            $data = call_user_func( $this->data );
        }
        $this->viewFile === null && $this->viewFile = $this->id;
        if($this->ajax){
            return $this->controller->renderAjax($this->viewFile, $data);
        }else{
            return $this->controller->render($this->viewFile, $data);
        }
        
    }

}
