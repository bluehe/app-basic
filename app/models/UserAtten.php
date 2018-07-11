<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%user_atten}}".
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $user
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $stat
 *
 * @property User $u
 * @property User $user0
 */
class UserAtten extends \yii\db\ActiveRecord {

    const STAT_OPEN = 1;
    const STAT_CLOSE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user_atten}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['uid', 'user', 'stat'], 'integer'],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
            [['user'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user' => 'id']],
            [['stat'], 'default', 'value' => self::STAT_OPEN],
            ['stat', 'in', 'range' => [self::STAT_OPEN, self::STAT_CLOSE]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'uid' => '关注人',
            'user' => '被关注人',
            'created_at' => '关注时间',
            'updated_at' => '取消时间',
            'stat' => '状态',
        ];
    }

    public static $List = [
        'stat' => [
            self::STAT_OPEN => "关注",
            self::STAT_CLOSE => "取消"
        ]
    ];

    public function getStat() {
        $stat = isset(self::$List['stat'][$this->stat]) ? self::$List['stat'][$this->stat] : null;
        return $stat;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getU() {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser0() {
        return $this->hasOne(User::className(), ['id' => 'user']);
    }

    //是否关注
    public static function is_atten($from, $to) {
        return static::find()->where(['uid' => $from, 'user' => $to, 'stat' => self::STAT_OPEN])->exists();
    }

    //关注用户数
    public static function get_num($uid = NULL, $type = 'follow', $stat = self::STAT_OPEN) {
        $query = static::find()->andFilterWhere(['stat' => $stat]);
        if ($type == 'follow') {
            $query->joinWith(['user0'])->andWhere([User::tableName() . '.status' => User::STATUS_ACTIVE])->andWhere(['uid' => $uid]);
        } else {
            $query->joinWith('u')->andWhere([User::tableName() . '.status' => User::STATUS_ACTIVE])->andWhere(['user' => $uid]);
        }
        $num = $query->count();
        return $num;
    }

    //用户关注排行
    public static function get_tab_userfans($num = '') {
        $user = User::find()->select(['id'])->where(['status' => User::STATUS_ACTIVE])->column();
        $query = static::find()->select(['user', 'uid', 'num' => 'COUNT(*)'])->andWhere(['stat' => self::STAT_OPEN, 'user' => $user])->joinWith(['u'])->andWhere([User::tableName() . '.status' => User::STATUS_ACTIVE])->groupBy(['user'])->orderBy(['num' => SORT_DESC]);
        if ($num) {
            $query->limit($num);
        }
        $attens = $query->asArray()->all();
        $data = [];
        foreach ($attens as $atten) {
            $user = User::findOne($atten['user']);
            $data[] = ['id' => $user->id, 'title' => $user->nickname ? $user->nickname : $user->username, 'label' => $atten['num'], 'img' => $user->avatar];
        }
        return $data;
    }

}
