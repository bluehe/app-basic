<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '系统信息';
$this->params['breadcrumbs'][] = ['label' => '系统设置', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <?php
            $form = ActiveForm::begin(['id' => 'system-info-form',
                        'options' => [ 'class' => 'form-horizontal'],
//                        'fieldConfig' => [
//                            'template' => "{label}\n<div class=\"col-md-4\">{input}</div>\n<div class=\"col-md-6\">{error}</div>",
//                            'labelOptions' => ['class' => 'col-md-2 control-label'],
//                        ],
            ]);
            ?>
            <div class="box-body">
                <?php
//                foreach ($settings as $index => $one) {
//                    if ($one['type'] == 'text') {
//                        echo $form->field($one, "[$index]value")->textInput()->label($one->tag);
//                    } elseif ($one['type'] == 'textarea') {
//                        echo $form->field($one, "[$index]value")->textarea()->label($one->tag);
//                    }
//                }
                ?>
                <?php
                foreach ($model as $one) {
                    if ($one['type'] == 'text'|| $one['type'] == 'password') {
                        ?>
                        <div class="form-group field-all field-<?= $one['code'] ?>">
                            <label class="col-md-2 control-label" for="<?= $one['code'] ?>"><?= $one['tag'] ?></label>
                            <div class="col-md-4"><input type="<?= $one['type'] ?>" id="<?= $one['code'] ?>" class="form-control" name="System[<?= $one['code'] ?>]" value="<?= $one['value'] ?>"></div>
                            <div class="col-md-6"><div class="help-block"><?= $one['hint'] ?></div></div>
                        </div>
                        <?php
                    } elseif ($one['type'] == 'radio' && $ranges = json_decode($one['store_range'])) {
                        ?>
                        <div class="form-group field-all field-<?= $one['code'] ?>">
                            <label class="col-md-2 control-label" for="<?= $one['code'] ?>"><?= $one['tag'] ?></label>
                            <div class="col-md-4">
                                <div id="<?= $one['code'] ?>">
                                    <?php
                                    foreach ($ranges as $key => $range) {
                                        ?>
                                        <label class="radio-inline"><input type="radio" class="radio-<?= $one['code'] ?>" name="System[<?= $one['code'] ?>]" value="<?= $key ?>" <?= "$key" == $one['value'] ? 'checked="checked"' : '' ?>> <?= $range ?></label>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6"><div class="help-block"><?= $one['hint'] ?></div></div>
                        </div>
                        <?php
                    } elseif ($one['type'] == 'checkbox' && $ranges = json_decode($one['store_range'])) {
                        ?>
                        <div class="form-group field-all field-<?= $one['code'] ?>">
                            <label class="col-md-2 control-label" for="<?= $one['code'] ?>"><?= $one['tag'] ?></label>
                            <div class="col-md-4">
                                <div id="<?= $one['code'] ?>">
                                    <?php
                                    $values = explode(',', $one['value']);
                                    foreach ($ranges as $key => $range) {
                                        ?>
                                        <label class="checkbox-inline"><input type="checkbox" class="checkbox-<?= $one['code'] ?>" name="System[<?= $one['code'] ?>][]" value="<?= $key ?>" <?= in_array("$key", $values) ? 'checked="checked"' : '' ?>> <?= $range ?></label>
                                        <?php
                                    }
                                    ?>

                                </div>
                            </div>
                            <div class="col-md-6"><div class="help-block"><?= $one['hint'] ?></div></div>
                        </div>
                        <?php
                    } elseif ($one['type'] == 'textarea') {
                        ?>
                        <div class="form-group field-all field-<?= $one['code'] ?>">
                            <label class="col-md-2 control-label" for="<?= $one['code'] ?>"><?= $one['tag'] ?></label>
                            <div class="col-md-4"><textarea id="<?= $one['code'] ?>" class="form-control" name="System[<?= $one['code'] ?>]" rows="<?= $one['store_range'] ?>"><?= $one['value'] ?></textarea></div>
                            <div class="col-md-6"><div class="help-block"><?= $one['hint'] ?></div></div>
                        </div>
                    <?php
                    }elseif ($one['type'] == 'editor') {
                        ?>
                        <div class="form-group field-all field-<?= $one['code'] ?>">
                            <label class="col-md-2 control-label" for="<?= $one['code'] ?>"><?= $one['tag'] ?></label>
                            <div class="col-md-4"><?= \yii\redactor\widgets\Redactor::widget(['name' => 'System['.$one['code'].']', 'value' => $one['value'],'clientOptions'=>['lang'=>'zh_cn','maxHeight'=>'500px'],]) ?></div>
                            <div class="col-md-6"><div class="help-block"><?= $one['hint'] ?></div></div>
                        </div>    
                    
                        <?php
                    }
                }
                ?>

            </div>
            <div class="box-footer">
                <div class="col-md-1 col-md-offset-2 col-xs-6 text-right">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary', 'name' => 'update-button']) ?>

                </div>
                <div class="col-md-1 col-xs-6 text-left">
                    <?= Html::resetButton('重置', ['class' => 'btn btn-default', 'name' => 'update-button']) ?>
                </div>

            </div>



            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<script>
<?php $this->beginBlock('system') ?>
    
    $(document).ready(function () {
        changeshow();
        $('.radio-system_stat').on('change', function () {
            changeshow();
        });

    });
    function changeshow() {
        var service = $('.radio-system_stat:checked').val();
        if (service === '1') {
            $('.field-system_close').hide();        
        } else {
            $('.field-system_close').show();                      
        }
        
        
    }
    
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['system'], \yii\web\View::POS_END); ?>
