<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '头像设置';
$this->params['breadcrumbs'][] = ['label' => '账号信息', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <?php
            $form = ActiveForm::begin(['id' => 'avatar-form',
                        'options' => ['class' => 'form-horizontal'],
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-md-4\">{input}</div>\n<div class=\"col-md-6\">{error}</div>",
                            'labelOptions' => ['class' => 'col-md-2 control-label'],
                        ],
            ]);
            ?>
            <div class="box-body">

                <div class="form-group">
                    <label class="col-md-2 control-label" for="avatar">头像</label>
                    <div class="col-md-2 text-center">
                        <?= Html::img($user->avatar ? $user->avatar : '@web/image/user.png', ['alt' => '头像', 'height' => 100, 'width' => 100, 'class' => 'img-rounded']) ?>
                    </div>
                    <div class="col-md-8"><p class="help-block help-block-error"></p></div>
                </div>
                <div class="form-group"><div class="col-md-2 col-md-offset-2 text-center">
                        <?=
                        FileInput::widget([
                            'name' => 'files[]',
                            'pluginOptions' => [
                                'language' => 'zh',
                                //关闭按钮
                                'showPreview' => false,
                                'showCancel' => false,
                                'showCaption' => false,
                                'showRemove' => false,
                                'showUpload' => false,
                                //浏览按钮样式
                                'browseClass' => 'btn btn-primary',
                                'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                                'browseLabel' => '上传图片',
                                'buttonLabelClass' => '',
                                //错误提示
                                'elErrorContainer' => '#kv-fileinput-error',
                                //进度条
                                'progressUploadThreshold' => 90,
                                //上传
                                'uploadAsync' => true,
                                'uploadUrl' => Url::toRoute(['account/upload-thumb']),
                                'uploadExtraData' => ['dir' => 'tmp'],
                                'maxFileSize' => $maxsize,
                            ],
                            'options' => ['accept' => 'image/*'],
                            'pluginEvents' => [
                                //选择后直接上传
                                'change' => 'function() {$(this).fileinput("upload");}',
                                //完成后隐藏进度条
                                'filebatchuploadcomplete' => 'function() {$(".kv-upload-progress").addClass("hide");}',
                                //上传成功
                                'fileuploaded' => 'function(event, data) {$("#avatar-crop .modal-body").html("");$.post("/account/thumb",{url:data.response.urls[0]},function(data){$("#avatar-modal").html(data);});}',
                            ],
                        ]);
                        ?></div><div class="col-md-8"><p id="kv-fileinput-error" class="help-block help-block-error"></p></div></div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div id="avatar-modal"></div>
</div>