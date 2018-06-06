<?php

//use dmstr\widgets\Menu;
use app\components\Menu;
use mdm\admin\components\MenuHelper;

$callback = function($menu) {
    $data = json_decode($menu['data'], true);
    $items = $menu['children'];
    $return = [
        'label' => $menu['name'],
        'url' => [$menu['route']],
    ];
    //处理我们的配置
    if ($data) {
        //visible
        isset($data['visible']) && $return['visible'] = $data['visible'];
        //icon
        isset($data['icon']) && $data['icon'] && $return['icon'] = $data['icon'];
        //other attribute e.g. class...
        $return['options'] = $data;
    }
    //没配置图标的显示默认图标
    (!isset($return['icon']) || !$return['icon']) && $return['icon'] = 'fa fa-circle-o';
    $items && $return['items'] = $items;
    return $return;
};
?><aside class="main-sidebar">

    <section class="sidebar">

        <?=
        Menu::widget(
                [
                    'options' => ['class' => 'sidebar-menu'],
                    'items' => MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback),
                ]
        )
        ?>
    </section>

</aside>
