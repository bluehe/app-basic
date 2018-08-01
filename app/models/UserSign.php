<?php

namespace app\models;


/**
 * This is the model class for table "{{%user_sign}}".
 *
 * @property integer $id
 * @property integer $uid
 * @property string $y
 * @property string $m
 * @property string $d
 * @property integer $series
 * @property string $note
 * @property integer $created_at
 *
 * @property User $u
 */
class UserSign extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user_sign}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['uid', 'sign_at', 'created_at'], 'required'],
            [['uid', 'series', 'sign_at', 'created_at'], 'integer'],
            [['note'], 'string', 'max' => 255],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'uid' => '用户',
            'series' => '连续天数',
            'note' => '备注',
            'sign_at' => '签到日期',
            'created_at' => '签到时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getU() {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    //是否签到
    public static function exist_sign($uid, $time = '') {
        if (!$time) {
            $time = strtotime(date('Y-m-d', time()));
        }
        return static::find()->where(['uid' => $uid, 'sign_at' => $time])->exists();
    }

    public static function get_day_total($a = 'sign_at', $start = '', $end = '') {
        $query = static::find()->andFilterWhere(['>=', $a, $start])->andFilterWhere(['<=', $a, $end]);
        return $query->groupBy(["FROM_UNIXTIME($a, '%Y-%m-%d')"])->select(['count(*)', "FROM_UNIXTIME($a, '%Y-%m-%d')"])->indexBy("FROM_UNIXTIME($a, '%Y-%m-%d')")->column();
    }

}
