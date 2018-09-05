<?php

use bluehe\jcrop\jCrop;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;

$form = ActiveForm::begin(['id' => 'jcrop-form']);
Modal::begin([
    'id' => 'avatar-crop',
    'size' => 'modal-sm',
    'header' => '头像编辑',
    'footer' => '<button type="submit" class="btn btn-primary">确定</button><a href="#" class="btn btn-default" data-dismiss="modal">取消</a>',
    'clientOptions' => ['show' => true]
]);



echo jCrop::widget([
// Image URL
    'url' => $url,
    // options for the IMG element
    'imageOptions' => [
        'id' => 'image',
        'alt' => '头像',
    ],
    // Jcrop options (see Jcrop documentation [http://blog.csdn.net/lybwwp/article/details/17307583])
    'jsOptions' => array(
        'allowSelect' => false,
        'aspectRatio' => 1,
        'setSelect' => [0, 0, 270, 270],
        'bgOpacity' => 0.4,
        'selection' => true,
        'boxWidth' => 270,
        'boxHeight' => 360,
    )
]);
?>
<input type="hidden" name="image[url]" value="<?= $url ?>">
<?php
Modal::end();
ActiveForm::end();
?>



