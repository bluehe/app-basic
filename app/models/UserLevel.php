<?php

namespace app\models;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%user_level}}".
 *
 * @property integer $id
 * @property string $level
 * @property integer $point
 */
class UserLevel extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user_level}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['level', 'point'], 'required'],
            [['level', 'point'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'level' => '等级',
            'point' => '积分',
        ];
    }

    //用户等级
    public static function get_level($point) {
        $model = static::find()->where(['<=', 'point', $point])->orderBy(['point' => SORT_DESC])->one();
        return $model === null ? 0 : $model->level;
    }

    //用户等级
    public static function get_user_level($user_id) {
        $user = User::findOne($user_id);
        $model = static::find()->where(['<=', 'point', $user->point])->orderBy(['point' => SORT_DESC])->one();
        return $model === null ? 0 : $model->level;
    }

//    public static function get_tab_userlevel($num = '') {
//        $query = User::find()->andWhere(['status' => User::STATUS_ACTIVE])->orderBy(['point' => SORT_DESC, 'id' => SORT_ASC]);
//        if ($num) {
//            $query->limit($num);
//        }
//        $data = [];
//        foreach ($query->each() as $user) {
//            $level = self::get_level($user->point);
//            $data[] = ['template_id' => 'user', 'url' => Url::toRoute(['site/people', 'id' => $user->id]), 'title' => $user->nickname ? $user->nickname : $user->username, 'label' => 'Lv.' . $level, 'label_class' => 'icon_level_c' . ceil($level / Yii::$app->params['level_c']), 'img' => Html::img($user->avatar ? $user->avatar : '@web/image/user.png', ['class' => 'img-circle'])];
//        }
//        return $data;
//    }

}
