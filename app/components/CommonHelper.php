<?php

namespace dms\components;

use Yii;
use common\models\UserAuth;

class CommonHelper {

    public function sendWechatTemplate($uid, $template, $param, $model) {

        $wechat = Yii::$app->wechat;
        $tousers = UserAuth::getTouser($uid);
        $result = true;
        if ($template == 'repaire_user') {
            $data = [
                'template_id' => 'px-_23ZPiLj9PSKO-Vz2Vn2heXw11djEzZACxxVNjJg',
                'url' => $param['url'],
                'data' => [
                    'first' => ['value' => $param['first'],],
                    'serial' => ['value' => $model->serial,],
                    'stat' => ['value' => $model->Stat,],
                    'created_at' => ['value' => date('Y-m-d H:i:s', $model->created_at),],
                    'user' => ['value' => $model->name,],
                    'address' => ['value' => ($model->repair_area ? \dms\models\Forum::get_forum_allname($model->repair_area) : '') . '-' . $model->address],
                    'type' => ['value' => $model->repair_type ? $model->type->v : $model->repair_type],
                    'content' => ['value' => $model->content,],
                    'remark' => ['value' => '点击查看详情！',],
            ]];

            foreach ($tousers as $touser) {
                $data['touser'] = $touser;
                $wechat->sendTemplateMessage($data);
            }
        } elseif ($template == 'suggest_user') {
            $data = [
                'template_id' => 'foaqeedv7YpJ0Y1E7ft1GNOWxG4YQIk12TV1KXS7uxE',
                'url' => $param['url'],
                'data' => [
                    'first' => ['value' => $param['first'],],
                    'keyword1' => ['value' => date('Y-m-d H:i:s', $model->created_at),],
                    'keyword2' => ['value' => $model->content,],
                    'remark' => ['value' => '点击查看详情！',],
            ]];

            foreach ($tousers as $touser) {
                $data['touser'] = $touser;
                $wechat->sendTemplateMessage($data);
            }
        }
        return $result;
    }

}
