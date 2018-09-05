<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2016-03-21 19:55
 */

namespace project\grid;

use Yii;
use Closure;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @inheritdoc
 */
class ActionColumn extends \yii\grid\ActionColumn
{

    public $header = 'Action';

    public $queryParams = [];

    public $width = '30px';

    public $template = '{view-layer} {update} {delete}';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->header = yii::t('app', $this->header);
        if (! isset($this->headerOptions['width'])) {
            $this->headerOptions['width'] = $this->width;
        }

        $this->contentOptions = ['class' => 'da-icon-column', 'style' => 'width:' . $this->width . ';'];
    }

    /**
     * @inheritdoc
     */
    protected function initDefaultButtons()
    {
        if (! isset($this->buttons['view'])) {
            $this->buttons['view'] = function ($url, $model, $key, $index, $gridView) {
                return Html::a('<i class="fa fa-eye"></i> 查看', $url, [
                    'title' => '查看',
                    'data-pjax' => '0',
                    'class' => 'btn btn-white btn-sm',
                ]);
            };
        }
        if (! isset($this->buttons['view-layer'])) {
            $this->buttons['view-layer'] = function ($url, $model, $key, $index, $gridView) {
                //$url = str_replace('viewLayer', 'view', $url);
                return Html::a('<i class="fa fa-eye"></i> 查看', 'javascript:void(0)', [
                    'title' => '查看',
                    'onclick' => "viewLayer('" . $url . "',$(this))",
                    'data-pjax' => '0',
                    'class' => 'btn btn-white btn-sm',
                ]);
            };
        }
        if (! isset($this->buttons['update'])) {
            $this->buttons['update'] = function ($url, $model, $key, $index, $gridView) {
                return Html::a('<i class="fa fa-pencil"></i> 编辑', $url, [
                    'title' => '编辑',
                    'data-pjax' => '0',
                    'class' => 'btn btn-white btn-sm',
                ]);
            };
        }
        if (! isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function ($url, $model, $key, $index, $gridView) {
                return Html::a('<i class="glyphicon glyphicon-trash" aria-hidden="true"></i> 删除', $url, [
                    'title' => '删除',
                    'data-confirm' => '确定删除此项？',
                    'data-method' => 'post',
                    'data-pjax' => '0',
                    'class' => 'btn btn-white btn-sm',
                ]);
            };
        }
    }

    /**
     * @inheritdoc
     */
    public function createUrl($action, $model, $key, $index)
    {
        if ($this->urlCreator instanceof Closure) {
            return call_user_func($this->urlCreator, $action, $model, $key, $index, $this);
        } else {
            $params = \Yii::$app->request->queryParams;
            if (is_array($key)) {
                $params = array_merge($params, $key);
            } else {
                $params['id'] = (string)$key;
            }
            if (isset($this->queryParams[$action])) {
                $params = array_merge($params, $this->queryParams[$action]);
            }
            $params[0] = $this->controller ? $this->controller . '/' . $action : $action;

            return Url::toRoute($params);
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return call_user_func($this->content, $model, $key, $index, $this);
        }

        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
            $name = $matches[1];
            if (isset($this->buttons[$name])) {
                $url = $this->createUrl($name, $model, $key, $index);

                return call_user_func($this->buttons[$name], $url, $model, $key, $index, $this);
            } else {
                return '';
            }
        }, $this->template);
    }
}