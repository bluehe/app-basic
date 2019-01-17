<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'CDN设置';
$this->params['breadcrumbs'][] = ['label' => '系统设置', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <?php
            $form = ActiveForm::begin(['id' => 'cdn-form',
                        'options' => ['class' => 'form-horizontal'],
            ]);
            ?>
            <div class="box-body">
                <?php
                foreach ($model as $one) {
                    if ($one['type'] == 'text' || $one['type'] == 'password') {
                        ?>
                        <div id="<?= $one['code'] ?>" class="form-group field-cdn field-<?= $one['code'] ?>" style="display: none;">
                            <label class="col-md-2 control-label" for="<?= $one['code'] ?>"><?= $one['tag'] ?></label>
                            <div class="col-md-4"><input type="<?= $one['type'] ?>" id="<?= $one['code'] ?>" class="form-control" name="System[<?= $one['code'] ?>]" value="<?= $one['value'] ?>"></div>
                            <div class="col-md-6"><div class="help-block"><?= $one['hint'] ?></div></div>                               
                        </div>
                        <?php
                    } elseif ($one['type'] == 'radio' && $ranges = json_decode($one['store_range'])) {
                        ?>
                        <div id="<?= $one['code'] ?>" class="form-group field-cdn field-<?= $one['code'] ?>" <?=$one['code']=='cdn_service'?'':'style="display: none;"'?>><label class="col-md-2 control-label"><?= $one['tag'] ?></label>
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
<?php $this->beginBlock('cdn') ?>
    
    $(document).ready(function () {
        changeshow();
        $('.radio-cdn_service,.radio-cdn_platform').on('change', function () {
            changeshow();
        });

    });
    function changeshow() {
        var service = $('.radio-cdn_service:checked').val();
        if (service === '1') {
            $('.field-cdn:not(.field-cdn_service)').hide(); 
            $('.field-cdn_platform').show();
                       
            var platform = $('.radio-cdn_platform:checked').val();
            $('div[id^="'+platform+'"]').show();
//            if (platform === 'Qcloud') {
//                $('.field-cdn_appid').show();
//                $('.field-cdn_key label').html('secretId');
//                $('.field-cdn_secret label').html('secretKey');
//                $('.field-cdn_point label').html('region');
//            } else if (platform === 'Qiniu') {
//                $('.field-cdn_point').hide();
//                $('.field-cdn_secret label').html('secretKey');
//            }
        } else {
            $('.field-cdn:not(.field-cdn_service)').hide();                      
        }
        
        
    }
   
<?php $this->endBlock() ?>
</script>
<?php $this->registerJs($this->blocks['cdn'], \yii\web\View::POS_END); ?>
