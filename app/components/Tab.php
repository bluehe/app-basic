<?php

namespace app\components;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * Class Menu
 * Theme menu widget.
 */
class Tab extends Widget {

    /**
     * @inheritdoc
     */
    public $items = [];
    public $template_id='url';
    public $urlTemplate = '<div class="list-group-item" data-id="{id}">{img} <a class="clickurl url" target="_blank" href="{url}" title="{title}">{title}</a> {label}</div>';
    public $userTemplate = '<div class="list-group-item">{img} <a class="url" href="{url}" title="{title}">{title}</a> {label}</div>';

    /**
     * Executes the widget.
     * This overrides the parent implementation by displaying the generated page buttons.
     */
    public function run() {
        echo $this->renderItems($this->items,$this->template_id);
    }

    /**
     * @inheritdoc
     */
    protected function renderItem($data,$template_id) {
        if ($template_id == 'user') {
            $item = ['url' => Url::toRoute(['site/people', 'id' => $data['id']]), 'title' => $data['title'], 'label' => $data['label'], 'img' => Html::img('@web/image/user.png', ['class' => 'lazyload img-circle', 'data-original' => $data['img']])];
            if(isset($data['label_class'])){
                $item['label_class']=$data['label_class'];
            }
            
            $template = ArrayHelper::getValue($item, 'template', $this->userTemplate);
        } else {
            $item = ['id' => $data['id'], 'url' => $data['url'], 'title' => $data['title'], 'label' => $data['label'], 'img' => Html::img('@web/image/default_e.png', ['class' => 'lazyload', 'data-original' =>Url::toRoute(['common/getfav','url'=>$data['host']])])];
            $template = ArrayHelper::getValue($item, 'template', $this->urlTemplate);
        }

        $replace = [
            '{id}' => isset($item['id']) ? $item['id'] : null,
            '{img}' => isset($item['img']) ? $item['img'] : null,
            '{url}' => Url::to($item['url']),
            '{title}' => $item['title'],
            '{label}' => isset($item['label']) ? '<span class="badge' . (isset($item['label_class']) ? ' ' . $item['label_class'] : '') . '">' . $item['label'] . '</span>' : null,
        ];
        return strtr($template, $replace);
    }

    /**
     * Recursively renders the menu items (without the container tag).
     * @param array $items the menu items to be rendered recursively
     * @return string the rendering result
     */
    protected function renderItems($items,$template_id) {
        $n = count($items);
        $lines = [];
        foreach ($items as $item) {
            $lines[] = $this->renderItem($item,$template_id);
        }
        return implode("\n", $lines);
    }

}
